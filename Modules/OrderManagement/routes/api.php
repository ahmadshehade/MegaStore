<?php

use Illuminate\Support\Facades\Route;
use Modules\OrderManagement\Http\Controllers\Api\V1\OrderController;
use Modules\OrderManagement\Http\Controllers\Api\V1\ShippingController;
use Modules\OrderManagement\Http\Controllers\OrderManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {


    /**
     *  ShippingController
     */
    Route::prefix('/shippings')->group(function () {

        Route::get('/', [ShippingController::class, 'index'])->name('shipping.all');
        Route::post('/', [ShippingController::class, 'store'])->name('shapping.make');
        Route::get('/{shipping}', [ShippingController::class, 'show'])->name('shipping.get');
        Route::post('/{shipping}', [ShippingController::class, 'update'])->name('shipping.update');
        Route::delete('/{shipping}', [ShippingController::class, 'destroy'])->name('shipping.delete');
    });


    /**
     *  OrderController
     */
    Route::prefix('/orders')->group(function () {
        Route::get('/', [OrderController::class,'index'])->name('orders.index');
        Route::post('/', [OrderController::class,'store'])->name('orders.store');
        Route::get('/{order}',[OrderController::class,'show'])->name('orders.show');
        Route::post('/{order}',[OrderController::class,'update'])->name('order.update');
        Route::delete('/{order}',[OrderController::class,'destroy'])->name('order.delete');
    });

});
