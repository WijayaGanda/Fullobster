<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IoTController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// IoT API Routes (no CSRF protection)
Route::prefix('iot')->name('iot.')->group(function () {
    // Untuk menerima data dari sensor IoT
    Route::post('/data', [IoTController::class, 'receiveData'])->name('receive');
    
    // Untuk mengambil data terbaru (untuk dashboard)
    Route::get('/latest', [IoTController::class, 'getLatestData'])->name('latest');
    
    // Untuk mengambil semua data (dengan pagination)
    Route::get('/all', [IoTController::class, 'getAllData'])->name('all');
    
    // Test endpoint untuk mengirim data sample
    Route::post('/test', [IoTController::class, 'sendTestData'])->name('test');
});