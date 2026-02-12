<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboards\Http\Controllers\Api\V1\CustomerDashboardController;
use Modules\Dashboards\Http\Controllers\Api\V1\SellerDashboardController;
use Modules\Dashboards\Http\Controllers\Api\V1\SuperAdminDashboardController;


/**
 * Super Admin Dashboard
 */
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('admin')->middleware(['can:admin-job'])
        ->get('/dashboard', [SuperAdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
});


/**
 * Seller Dashboard
 */
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('seller')->middleware(['can:seller-job'])
        ->get('/dashboard', [SellerDashboardController::class, 'dashboard'])->name('seller.dashboard');
});

/**
 *  Customer Dashboard
 */
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('customer')->middleware(['can:customer-job'])
        ->get('/dashboard', [CustomerDashboardController::class, 'dashboard'])->name('customer.dashboard');
});
