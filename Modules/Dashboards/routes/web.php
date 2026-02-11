<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboards\Http\Controllers\DashboardsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('dashboards', DashboardsController::class)->names('dashboards');
});
