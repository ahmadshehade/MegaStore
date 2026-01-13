<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface BaseInterface
{
    /**
     * Get all records with optional filters.
     *
     * @param array $filters
     * @return iterable<Model>
     */
    public function getAll(array $filters = []): iterable;

    /**
     * Get a specific model.
     *
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model;

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model;

    /**
     * Delete a record.
     *
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool;
}
