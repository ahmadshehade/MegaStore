<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentManagement\Http\Controllers\Api\V1\PaymentMethodController;
use Modules\PaymentManagement\Http\Controllers\PaymentManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1/paymentMethods')->group(function () {


    /**
     * PaymentMethodController
     */
    Route::get('/', [PaymentMethodController::class, 'index'])->name('payment_method.all');
    Route::post('/', [PaymentMethodController::class, 'store'])->name('payment_method.add');
    Route::get('/{paymentMethod}', [PaymentMethodController::class, 'show'])->name('payment_method.get');
    Route::post('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('payment_method.update');
    Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment_method.delete');
});
