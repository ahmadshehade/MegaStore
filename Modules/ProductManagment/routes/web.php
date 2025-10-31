<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductManagment\Http\Controllers\ProductManagmentController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('productmanagments', ProductManagmentController::class)->names('productmanagment');
});
