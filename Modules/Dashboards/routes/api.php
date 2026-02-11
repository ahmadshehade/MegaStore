<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboards\Http\Controllers\Api\V1\SuperAdminDashboardController;


Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('admin')->middleware(['can:admin-job'])
        ->get('/dashboard', [SuperAdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
});
