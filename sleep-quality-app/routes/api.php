<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SleepEntryController;
use App\Http\Controllers\SleepAssessmentController;

// API Routes with authentication middleware
Route::middleware(['auth:sanctum'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Sleep Entries
    Route::prefix('sleep-entries')->group(function () {
        Route::get('/', [SleepEntryController::class, 'index']);
        Route::post('/', [SleepEntryController::class, 'store']);
        Route::get('/{sleepEntry}', [SleepEntryController::class, 'show']);
        Route::put('/{sleepEntry}', [SleepEntryController::class, 'update']);
        Route::delete('/{sleepEntry}', [SleepEntryController::class, 'destroy']);
    });

    // Sleep Assessments
    Route::prefix('sleep-assessments')->group(function () {
        Route::get('/', [SleepAssessmentController::class, 'index']);
        Route::post('/', [SleepAssessmentController::class, 'store']);
        Route::get('/{sleepAssessment}', [SleepAssessmentController::class, 'show']);
        Route::put('/{sleepAssessment}', [SleepAssessmentController::class, 'update']);
        Route::delete('/{sleepAssessment}', [SleepAssessmentController::class, 'destroy']);
    });
});
