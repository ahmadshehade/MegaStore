<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductManagement\Http\Controllers\Api\V1\Attributes\AttributeController;
use Modules\ProductManagement\Http\Controllers\Api\V1\Attributes\AttributeValueController;
use Modules\ProductManagement\Http\Controllers\Api\V1\Category\CategoryController;
use Modules\ProductManagement\Http\Controllers\Api\V1\Product\ProductController;
use Modules\ProductManagement\Http\Controllers\Api\V1\Product\ProductVariantController;

/*
|--------------------------------------------------------------------------
| API V1 Routes — Product Management Module
|--------------------------------------------------------------------------
| This file defines all API endpoints related to categories and products
| inside the ProductManagement module. Routes are versioned (v1) and
| protected using Sanctum authentication middleware.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])
    ->prefix('v1')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Category Routes
        |--------------------------------------------------------------------------
        | Endpoints responsible for:
        | - Listing all categories
        | - Creating a category
        | - Showing a single category
        | - Updating an existing category
        | - Deleting a category
        |--------------------------------------------------------------------------T
        */
        Route::prefix('/categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])
                ->name('category.all');
            Route::post('/', [CategoryController::class, 'store'])
                ->name('category.store');
            Route::get('/{category}', [CategoryController::class, 'show'])
                ->name('category.get');
            Route::post('/{category}', [CategoryController::class, 'update'])
                ->name('category.update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])
                ->name('category.destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Product Routes
        |--------------------------------------------------------------------------
        | Endpoints responsible for:
        | - Listing all products
        | - Creating a product
        | - Showing a specific product
        | - Updating an existing product
        | - Deleting a product
        |--------------------------------------------------------------------------T
        */
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index'])
                ->name('product.all');
            Route::post('/', [ProductController::class, 'store'])
                ->name('product.store');
            Route::get('/{product}', [ProductController::class, 'show'])
                ->name('product.get');
            Route::post('/{product}', [ProductController::class, 'update'])
                ->name('product.update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])
                ->name('product.destroy');
        });

        /*
        |--------------------------------------------------------------------------T
        | Product Variant  Routes
        |--------------------------------------------------------------------------
        | Endpoints responsible for:
        | - Listing all Variant
        | - Creating a Variant
        | - Showing a specific Variant
        | - Updating an existing Variant
        | - Deleting a Variant
        |--------------------------------------------------------------------------
        */
        Route::prefix('productvariants')->group(function () {
            Route::get('/', [ProductVariantController::class, 'index'])
                ->name('variant.all');
            Route::post('/', [ProductVariantController::class, 'store'])
                ->name('variant.store');
            Route::get('/{productvariant}', [ProductVariantController::class, 'show'])
                ->name('variant.show');
            Route::post('/{productvariant}', [ProductVariantController::class, 'update'])
                ->name('variant.update');
            Route::delete('/{productvariant}', [ProductVariantController::class, 'destroy'])
                ->name('variant.delete');
        });

        /*
        |--------------------------------------------------------------------------
        | Attributes  Routes
        |--------------------------------------------------------------------------
        | Endpoints responsible for:
        | - Listing all Variant
        | - Creating a Variant
        | - Showing a specific Variant
        | - Updating an existing Variant
        | - Deleting a Variant
        |--------------------------------------------------------------------------F
        */
        Route::prefix('attributes')->group(function () {
            Route::get('/', [AttributeController::class, 'index'])
                ->name('attributes.index');
            Route::post('/', [AttributeController::class, 'store'])
                ->name('attributes.store');
            Route::get('/{attribute}', [AttributeController::class, 'show'])
                ->name('attributes.show');
            Route::post('/{attribute}', [AttributeController::class, 'update'])
                ->name('attributes.update');
            Route::delete('/{attribute}', [AttributeController::class, 'destroy'])
                ->name('attributes.destroy');
        });

        /*
        |--------------------------------------------------------------------------F
        | Attribute Value  Routes
        |--------------------------------------------------------------------------
        | Endpoints responsible for:
        | - Listing all Variant
        | - Creating a Variant
        | - Showing a specific Variant
        | - Updating an existing Variant
        | - Deleting a Variant
        |--------------------------------------------------------------------------
        */
        Route::prefix('attributeValues')->group(function () {
            Route::get('/', [AttributeValueController::class, 'index'])
                ->name('values.index');
            Route::post('/', [AttributeValueController::class, 'store'])
                ->name('values.store');
            Route::get('/{attributeValue}', [AttributeValueController::class, 'show'])
                ->name('values.show');
            Route::post('/{attributeValue}', [AttributeValueController::class, 'update'])
                ->name('values.update');
            Route::delete('/{attributeValue}', [AttributeValueController::class, 'destroy'])
                ->name('values.destroy');
        });
    });
