<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IoTController;

Route::get('/', function () {
    return view('landing');
});

// Dashboard Routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/api/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
Route::get('/api/dashboard/data/{index}', [DashboardController::class, 'getDataByIndex'])->name('dashboard.data.index');
Route::post('/api/dashboard/classify', [DashboardController::class, 'classify'])->name('dashboard.classify');

// IoT API Routes (without CSRF protection)
Route::prefix('api/iot')->name('iot.')->withoutMiddleware(['web'])->group(function () {
    // Untuk menerima data dari sensor IoT
    Route::post('/data', [IoTController::class, 'receiveData'])->name('receive');
    
    // Untuk mengambil data terbaru (untuk dashboard)
    Route::get('/latest', [IoTController::class, 'getLatestData'])->name('latest');
    
    // Untuk mengambil semua data (dengan pagination)
    Route::get('/all', [IoTController::class, 'getAllData'])->name('all');
    
    // Test endpoint untuk mengirim data sample
    Route::post('/test', [IoTController::class, 'sendTestData'])->name('test');
});
