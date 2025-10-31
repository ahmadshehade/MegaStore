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
             if($filters!=[]){
                $this->applyFilters($query, $filters);
             }
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



    /**
     * Summary of applyFilters
     * @param \Illuminate\Database\Eloquent\Builder $q
     * @param array $filters
     * @return void
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (empty($filters)) {
            return;
        }

        foreach ($filters as $field => $value) {

            if ($value === null) {
                continue;
            }
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    continue;
                }
            }


            $query->where($field, 'like', '%' . $value . '%');
        }
    }
}
