<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SleepEntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Sleep entry routes
    Route::apiResource('sleep-entries', SleepEntryController::class);
    Route::get('sleep-statistics', [SleepEntryController::class, 'statistics']);
});
