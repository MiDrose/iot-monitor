<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Dashboard Routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/history', [DashboardController::class, 'history'])->name('history');
Route::get('/devices', [DashboardController::class, 'devices'])->name('devices');
Route::put('/devices/{device}', [DashboardController::class, 'updateDevice'])->name('devices.update');
Route::post('/devices/{device}/regenerate-key', [DashboardController::class, 'regenerateApiKey'])->name('devices.regenerateKey');
Route::get('/export', [DashboardController::class, 'export'])->name('export');
