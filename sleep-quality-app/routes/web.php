<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SleepEntryController;
use App\Http\Controllers\SleepAssessmentController;

Route::get('/', function () {
    return view('welcome');
});

// API Routes (in a real app, these would be in routes/api.php with Sanctum authentication)
// For this demo, we'll use web routes with middleware

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Sleep Entries
    Route::prefix('sleep-entries')->group(function () {
        Route::get('/', [SleepEntryController::class, 'index'])->name('sleep-entries.index');
        Route::post('/', [SleepEntryController::class, 'store'])->name('sleep-entries.store');
        Route::get('/{sleepEntry}', [SleepEntryController::class, 'show'])->name('sleep-entries.show');
        Route::put('/{sleepEntry}', [SleepEntryController::class, 'update'])->name('sleep-entries.update');
        Route::delete('/{sleepEntry}', [SleepEntryController::class, 'destroy'])->name('sleep-entries.destroy');
    });
    
    // Sleep Assessments
    Route::prefix('sleep-assessments')->group(function () {
        Route::get('/', [SleepAssessmentController::class, 'index'])->name('sleep-assessments.index');
        Route::post('/', [SleepAssessmentController::class, 'store'])->name('sleep-assessments.store');
        Route::get('/{sleepAssessment}', [SleepAssessmentController::class, 'show'])->name('sleep-assessments.show');
        Route::put('/{sleepAssessment}', [SleepAssessmentController::class, 'update'])->name('sleep-assessments.update');
        Route::delete('/{sleepAssessment}', [SleepAssessmentController::class, 'destroy'])->name('sleep-assessments.destroy');
    });
});
