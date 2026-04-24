<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SensorController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// IoT Sensor API Routes (no auth - uses API key in payload)
Route::post('/sensor-data', [SensorController::class, 'store']);
Route::get('/sensor-data/latest', [SensorController::class, 'latest']);
Route::get('/sensor-data/history', [SensorController::class, 'history']);
Route::get('/devices', [SensorController::class, 'devices']);
Route::get('/statistics', [SensorController::class, 'statistics']);
