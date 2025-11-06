<?php

use Illuminate\Support\Facades\Route;
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

});
