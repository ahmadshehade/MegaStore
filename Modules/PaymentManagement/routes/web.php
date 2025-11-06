<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentManagement\Http\Controllers\PaymentManagementController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('paymentmanagements', PaymentManagementController::class)->names('paymentmanagement');
});
