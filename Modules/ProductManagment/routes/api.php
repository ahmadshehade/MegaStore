<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductManagment\Http\Controllers\Api\V1\CategoryController;
use Modules\ProductManagment\Http\Controllers\ProductManagmentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::post('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

});
