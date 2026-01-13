<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductManagement\Http\Controllers\ProductManagementController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('productmanagements', ProductManagementController::class)->names('productmanagement');
});
