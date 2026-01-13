<?php

namespace App\Services;

use Closure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\CrudException;
use App\Interfaces\BaseInterface;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use League\Config\Exception\ValidationException;
use PDOException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            $modelName = class_basename($this->model ?? $e->getModel() ?? 'Resource');
            $message = $notFoundMessage ?? "{$modelName} not found";
            throw new NotFoundHttpException($message, $e);
        } catch (AuthenticationException $e) {
            throw new HttpException(401, $e->getMessage(), $e);
        } catch (AuthorizationException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        } catch (ValidationException $e) {
            $validationMessage = $e->getMessage();
            throw new HttpException(422, $validationMessage, $e);
        } catch (TokenMismatchException $e) {
            // 419 Page Expired (Laravel uses 419 for CSRF/token mismatch)
            throw new HttpException(419, 'Page expired. Please try again.', $e);
        } catch (BadRequestHttpException $e) {
            throw $e;
        } catch (MethodNotAllowedHttpException $e) {
            throw $e;
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (QueryException $e) {
            Log::error('Database QueryException: ' . $e->getMessage(), ['exception' => $e]);
            throw new HttpException(500, 'Database error occurred.', $e);
        } catch (PDOException $e) {
            Log::error('PDOException: ' . $e->getMessage(), ['exception' => $e]);
            throw new HttpException(500, 'Database connection error.', $e);
        } catch (\BadMethodCallException $e) {
            Log::error('BadMethodCallException: ' . $e->getMessage(), ['exception' => $e]);
            throw new HttpException(500, 'Server error (bad method call).', $e);
        } catch (\Throwable $e) {
            Log::error('Unexpected error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            $message = config('app.debug') ? 'Unexpected error: ' . $e->getMessage() : 'Unexpected server error.';
            throw new HttpException(500, $message, $e);
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
            if ($filters != []) {
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
