<?php

use Illuminate\Support\Facades\Route;
use Modules\OrderManagement\Http\Controllers\Api\V1\Discount\DiscountController;
use Modules\OrderManagement\Http\Controllers\Api\V1\Order\OrderController;
use Modules\OrderManagement\Http\Controllers\Api\V1\ProductReview\ProductReviewController;


Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    /**
     * -------------------------------
     * Discount Management Routes
     * -------------------------------
     * Group of routes to manage discounts.
     * Access restricted to users with 'admin-job' permission.
     * CRUD operations are supported:
     *   - index()   => List all discounts
     *   - store()   => Create a new discount
     *   - show()    => Retrieve a specific discount
     *   - update()  => Update an existing discount
     *   - destroy() => Delete a discount
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
     * Routes to handle all order-related operations.
     * Includes creating, updating, deleting, and viewing orders.
     * Additional endpoint for order history retrieval.
     */
    Route::prefix('/orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])
            ->name('orders.all');
        Route::post('/', [OrderController::class, 'store'])
            ->name('order.store');
        Route::get('/{order}', [OrderController::class, 'show'])
            ->name('orders.show');
        Route::post('/{order}', [OrderController::class, 'update'])
            ->name('order.update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])
            ->name('order.detroy');
        Route::get('/history/{order}', [OrderController::class, 'getOrderHistory'])
            ->name('order.discounts');
    });

    /**
     * -------------------------------
     * Product Review Routes
     * -------------------------------
     * Routes for managing product reviews.
     * Supports CRUD operations for product feedback.
     */
    Route::prefix('/productReviews')->group(function () {
        Route::get('/', [ProductReviewController::class, 'index'])
            ->name('reviews.all');
        Route::get('/{productReview}', [ProductReviewController::class, 'show'])
            ->name('review.get');
        Route::post('/', [ProductReviewController::class, 'store'])
            ->name('review.make');
        Route::put('/{productReview}', [ProductReviewController::class, 'update'])
            ->name('review.update');
        Route::delete('/{productReview}', [ProductReviewController::class, 'destroy'])
            ->name('review.delete');
    });
});
