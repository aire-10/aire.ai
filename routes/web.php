<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GroundingController;
use App\Http\Controllers\MoodBoosterController;
use App\Http\Controllers\MoodLiftingController;
use App\Http\Controllers\MindResetController;
use App\Http\Controllers\MiniTaskController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\HistoryController;

// ==============================================
// PUBLIC ROUTES
// ==============================================

// Landing page (choose ONE of these)
Route::get('/', [HomeController::class, 'landing'])->name('landing');

// Gemini API key endpoint
Route::get('/get-gemini-key', function () {
    return response()->json([
        'key' => env('GEMINI_API_KEY')
    ]);
});

// ==============================================
// AUTHENTICATED ROUTES
// ==============================================

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('journal', 'journal')->name('journal');
    Route::view('profile', 'profile')->name('profile.show');
    
    // Home
    Route::get('/home', [HomeController::class, 'index'])->name('home');
     
    // Grounding
    Route::get('/grounding', [GroundingController::class, 'index'])->name('grounding');
    
    // Mood Booster
    Route::get('/moodbooster', [MoodBoosterController::class, 'index'])->name('moodbooster');
    
    // Mood Lifting
    Route::get('/moodlifting', [MoodLiftingController::class, 'index'])->name('moodlifting');
    
    // Mind Reset
    Route::get('/mindreset', [MindResetController::class, 'index'])->name('mindreset');
    
    // Mini Tasks
    Route::get('/minitask', [MiniTaskController::class, 'index'])->name('minitask');
    
    // Growth
    Route::get('/growth', [GrowthController::class, 'index'])->name('growth');
    Route::get('/growth/data', [GrowthController::class, 'getData'])->name('growth.data');
    Route::post('/growth/action', [GrowthController::class, 'recordAction'])->name('growth.action');
    Route::get('/growth/mood-chart', [GrowthController::class, 'getMoodChartData'])->name('growth.mood-chart');
    
    // History
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/history/sessions', [HistoryController::class, 'getSessions']);
    Route::get('/history/session/{id}', [HistoryController::class, 'getSession']);
    Route::post('/history/session', [HistoryController::class, 'createSession']);
    Route::post('/history/session/{id}/rename', [HistoryController::class, 'renameSession']);
    Route::delete('/history/session/{id}', [HistoryController::class, 'deleteSession']);
    Route::get('/history/search', [HistoryController::class, 'search']);
    Route::get('/history/stats', [HistoryController::class, 'getStats']);
    Route::post('/history/message', [HistoryController::class, 'saveMessage']);
});

require __DIR__.'/settings.php';