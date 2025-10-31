<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductManagment\Http\Controllers\Api\V1\CategoryController;
use Modules\ProductManagment\Http\Controllers\ProductManagmentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    Route::apiResource('/categories',CategoryController::class);
});
