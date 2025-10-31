<?php

namespace App\Services;

use Closure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\CrudException;
use App\Interfaces\BaseInterface;
use Illuminate\Auth\Access\AuthorizationException;

use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseService implements BaseInterface
{
    /**
     * The model instance.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Handle the execution with centralized exception handling.
     */
    protected function handle(Closure $callback, ?string $notFoundMessage = null)
    {
        try {
            return $callback();
        } catch (ModelNotFoundException $e) {
            $modelName = class_basename($this->model);
            throw new CrudException($notFoundMessage ?? "{$modelName} not found", 404);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new CrudException('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all records with optional filters.
     *
     * @param array $filters
     * @return iterable<Model>
     */
    public function getAll(array $filters = []): iterable
    {
        return $this->handle(function () use ($filters) {
            $query = $this->model->newQuery();

            // نقوم أولًا بتنظيف الفلاتر (whitelist + suffixes)
            $filters = $this->sanitizeFilters($filters);

            // ثم نطبق الفلاتر على الاستعلام
            $this->applyFilters($query, $filters);

            return $query->get();
        });
    }


    /**
     * Get a specific model.
     *
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        return $model;
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->handle(fn() => $this->model->create($data));
    }

    /**
     * Update an existing record.
     *
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model
    {
        return $this->handle(function () use ($data, $model) {
            $model->update($data);
            return $model;
        });
    }

    /**
     * Delete a record.
     *
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool
    {
        return $this->handle(fn() => $model->delete());
    }


    protected array $filterable = [];

    public function getFilterable(): array
    {
        return $this->filterable;
    }


    protected function sanitizeFilters(array $filters): array
    {
        $allowed = $this->getFilterable();
        $out = [];
        $special = ['with', 'search', 'sort', 'limit', 'offset', 'page', 'per_page'];

        foreach ($filters as $k => $v) {
            if (in_array($k, $special, true)) {
                if ($k === 'search' && is_array($v) && !empty($v['term']) && !empty($v['fields'])) {
                    $fields = array_values(array_intersect($v['fields'], $allowed));
                    if (!empty($fields)) $out['search'] = ['term' => $v['term'], 'fields' => $fields];
                } else {
                    $out[$k] = $v;
                }
                continue;
            }

            // احصل على الحقل الأساسي بدون اللاحقات (__like, __in, ...)
            $base = $k;
            foreach (['__like', '__in', '__gt', '__gte', '__lt', '__lte', '__null', '__between'] as $suf) {
                if (str_ends_with($k, $suf)) {
                    $base = substr($k, 0, -strlen($suf));
                    break;
                }
            }

            if (in_array($base, $allowed, true)) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    /**
     * Summary of applyFilters
     * @param \Illuminate\Database\Eloquent\Builder $q
     * @param array $filters
     * @return void
     */
    protected function applyFilters(Builder $q, array $filters): void
    {
        if (!empty($filters['with']) && is_array($filters['with'])) $q->with($filters['with']);

        if (!empty($filters['search']['term']) && !empty($filters['search']['fields'])) {
            $term = $filters['search']['term'];
            $q->where(function ($sub) use ($term, $filters) {
                foreach ($filters['search']['fields'] as $f) $sub->orWhere($f, 'like', "%{$term}%");
            });
        }

        foreach ($filters as $k => $v) {
            if (in_array($k, ['with', 'search', 'sort', 'limit', 'offset', 'page', 'per_page'], true)) continue;

            if (str_ends_with($k, '__like')) {
                $f = str_replace('__like', '', $k);
                if ($v !== null && $v !== '') $q->where($f, 'like', "%{$v}%");
                continue;
            }

            if (str_ends_with($k, '__in')) {
                $f = str_replace('__in', '', $k);
                if (is_array($v)) $q->whereIn($f, $v);
                continue;
            }

            foreach (['__gt' => '>', '__gte' => '>=', '__lt' => '<', '__lte' => '<='] as $suf => $op) {
                if (str_ends_with($k, $suf)) {
                    $f = str_replace($suf, '', $k);
                    if ($v !== null && $v !== '') $q->where($f, $op, $v);
                    continue 2;
                }
            }

            if (str_ends_with($k, '__null')) {
                $f = str_replace('__null', '', $k);
                $v ? $q->whereNull($f) : $q->whereNotNull($f);
                continue;
            }

            if (str_ends_with($k, '__between')) {
                $f = str_replace('__between', '', $k);
                if (is_array($v) && count($v) === 2) $q->whereBetween($f, [$v[0], $v[1]]);
                continue;
            }

            // default exact or whereIn
            if (is_array($v)) $q->whereIn($k, $v);
            else if ($v !== null && $v !== '') $q->where($k, $v);
        }

        if (!empty($filters['sort']) && is_array($filters['sort'])) {
            foreach ($filters['sort'] as $s) {
                if (is_string($s) && str_starts_with($s, '-')) $q->orderBy(ltrim($s, '-'), 'desc');
                else $q->orderBy($s, 'asc');
            }
        }

        if (!empty($filters['limit'])) $q->limit((int)$filters['limit']);
        if (!empty($filters['offset'])) $q->offset((int)$filters['offset']);
    }
}
