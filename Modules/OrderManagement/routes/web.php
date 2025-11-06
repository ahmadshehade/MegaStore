<?php

use Illuminate\Support\Facades\Route;
use Modules\OrderManagement\Http\Controllers\OrderManagementController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ordermanagements', OrderManagementController::class)->names('ordermanagement');
});
