<?php

use Illuminate\Support\Facades\Route;
use Modules\OrderManagement\Http\Controllers\Api\V1\Discount\DiscountController;
use Modules\OrderManagement\Http\Controllers\Api\V1\Order\OrderController;
use Modules\OrderManagement\Http\Controllers\OrderManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    /**
     * -------------------------------
     * Discount Management Routes
     * -------------------------------
     * This route group handles all operations related to discounts.
     * Only users with the 'admin-job' permission can access these endpoints.
     * Endpoints:
     *   - GET /discounts          => List all discounts
     *   - POST /discounts         => Create a new discount
     *   - GET /discounts/{id}     => Retrieve a single discount by ID
     *   - POST /discounts/{id}    => Update an existing discount
     *   - DELETE /discounts/{id}  => Delete a discount
     */
    Route::middleware(['can:admin-job'])->prefix('/discounts')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])
            ->name('discount.all');
        Route::post('/', [DiscountController::class, 'store'])
            ->name('discount.store');
        Route::get('/{discount}', [DiscountController::class, 'show'])
            ->name('discount.show');
        Route::post('/{discount}', [DiscountController::class, 'update'])
            ->name('discount.update');
        Route::delete('/{discount}', [DiscountController::class, 'destroy'])
            ->name('discount.delete');
    });

    /**
     * -------------------------------
     * Order Management Routes
     * -------------------------------
     * This route group handles all operations related to orders.
     * Endpoints:
     *   - GET /orders          => List all orders
     *   - POST /orders         => Create a new order
     *   - GET /orders/{id}     => Retrieve a single order by ID
     *   - POST /orders/{id}    => Update an existing order
     *   - DELETE /orders/{id}  => Delete an order
     */
    Route::prefix('/orders')->group(function () {
        Route::get('/', [OrderController::class,'index'])->name('orders.all');
        Route::post('/', [OrderController::class,'store'])->name('order.store');
        Route::get('/{order}',[OrderController::class,'show'])->name('orders.show');
        Route::post('/{order}',[OrderController::class,'update'])->name('order.update');
        Route::delete('/{order}',[OrderController::class,'destroy'])->name('order.detroy');
        Route::get('/history/{order}',[OrderController::class,'getOrderHistory'])->name('order.discounts');
    });
});
