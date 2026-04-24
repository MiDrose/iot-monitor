<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Auth Routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

// Protected Routes (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::get('/devices', [DashboardController::class, 'devices'])->name('devices');
    Route::post('/devices', [DashboardController::class, 'addDevice'])->name('devices.add');
    Route::post('/devices/claim', [DashboardController::class, 'claimDevice'])->name('devices.claim');
    Route::put('/devices/{device}', [DashboardController::class, 'updateDevice'])->name('devices.update');
    Route::post('/devices/{device}/regenerate-key', [DashboardController::class, 'regenerateApiKey'])->name('devices.regenerateKey');
    Route::get('/export', [DashboardController::class, 'export'])->name('export');
});
