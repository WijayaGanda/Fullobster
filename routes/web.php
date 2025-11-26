<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('landing');
});

// Dashboard Routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/api/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
Route::get('/api/dashboard/data/{index}', [DashboardController::class, 'getDataByIndex'])->name('dashboard.data.index');
Route::post('/api/dashboard/classify', [DashboardController::class, 'classify'])->name('dashboard.classify');
