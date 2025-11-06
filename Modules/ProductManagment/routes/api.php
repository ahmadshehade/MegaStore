<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductManagment\Http\Controllers\Api\V1\CategoryController;
use Modules\ProductManagment\Http\Controllers\Api\V1\ProductController;

Route::middleware(['auth:sanctum'])
    ->prefix('v1')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Category Routes
        |--------------------------------------------------------------------------
        */
        Route::controller(CategoryController::class)->prefix('categories')->name('categories.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{category}', 'show')->name('show');
            Route::post('/{category}', 'update')->name('update');
            Route::delete('/{category}', 'destroy')->name('destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Product Routes
        |--------------------------------------------------------------------------
        */
        Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{product}', 'show')->name('show');
            Route::post('/{product}', 'update')->name('update');
            Route::delete('/{product}', 'destroy')->name('destroy');
        });
    });
