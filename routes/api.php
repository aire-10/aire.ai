<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GroundingController;
use App\Http\Controllers\MoodBoosterController;
use App\Http\Controllers\MindResetController;
use App\Http\Controllers\MiniTaskController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\BreathingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\API\AireDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// PUBLIC
Route::post('/breathing', [BreathingController::class, 'store']);

// GROUP ALL API ROUTES
Route::group([], function () {

    // ======================
    // AIRE DATA (MOOD)
    // ======================
    Route::get('/mood-log', [AireDataController::class, 'getMoodLog']);
    Route::post('/log-mood', [AireDataController::class, 'logMood']);

    Route::get('/streak', [AireDataController::class, 'getStreak']);
    Route::get('/days-tracked', [AireDataController::class, 'getDaysTracked']);
    Route::get('/today-checkins', [AireDataController::class, 'getTodayCheckInCount']);
    Route::get('/latest-mood', [AireDataController::class, 'getLatestMood']);
    Route::get('/mood-meta', [AireDataController::class, 'getMoodMeta']);
    Route::get('/positive-moods', [AireDataController::class, 'getPositiveMoods']);

    // ======================
    // GROUNDING
    // ======================
    Route::prefix('grounding')->group(function () {
        Route::post('/progress', [GroundingController::class, 'saveProgress']);
        Route::get('/progress', [GroundingController::class, 'getProgress']);
        Route::get('/today-completion', [GroundingController::class, 'checkTodayCompletion']);
        Route::get('/stats', [GroundingController::class, 'getStats']);
        Route::post('/step-inputs', [GroundingController::class, 'saveStepInputs']);
        Route::delete('/reset', [GroundingController::class, 'resetProgress']);
        Route::get('/history', [GroundingController::class, 'getHistory']);
    });

    // ======================
    // MOOD BOOSTER
    // ======================
    Route::prefix('moodbooster')->group(function () {
        Route::post('/update-mood', [MoodBoosterController::class, 'updateMood']);
        Route::post('/complete-activity', [MoodBoosterController::class, 'completeActivity']);
        Route::get('/today-status', [MoodBoosterController::class, 'getTodayStatus']);
        Route::get('/streak', [MoodBoosterController::class, 'getStreak']);
        Route::get('/weekly-progress', [MoodBoosterController::class, 'getWeeklyProgress']);
        Route::get('/history', [MoodBoosterController::class, 'getHistory']);
        Route::get('/stats', [MoodBoosterController::class, 'getStats']);
        Route::post('/moodlifting-progress', [MoodBoosterController::class, 'saveMoodLiftingProgress']);
        Route::get('/moodlifting-progress', [MoodBoosterController::class, 'getMoodLiftingProgress']);
    });

    // ======================
    // MIND RESET
    // ======================
    Route::prefix('mindreset')->group(function () {
        Route::post('/progress', [MindResetController::class, 'saveProgress']);
        Route::get('/progress', [MindResetController::class, 'getProgress']);
        Route::get('/today-completion', [MindResetController::class, 'checkTodayCompletion']);
        Route::get('/stats', [MindResetController::class, 'getStats']);
        Route::get('/items', [MindResetController::class, 'getItems']);
        Route::delete('/reset', [MindResetController::class, 'resetProgress']);
        Route::get('/weekly-data', [MindResetController::class, 'getWeeklyData']);
        Route::get('/encouragement-messages', [MindResetController::class, 'getEncouragementMessages']);
        Route::post('/session/start', [MindResetController::class, 'startSession']);
        Route::post('/session/complete', [MindResetController::class, 'completeSession']);
        Route::get('/sessions', [MindResetController::class, 'getSessionHistory']);
    });

    // ======================
    // MINI TASK
    // ======================
    Route::prefix('minitask')->group(function () {
        Route::post('/progress', [MiniTaskController::class, 'saveProgress']);
        Route::get('/progress', [MiniTaskController::class, 'getProgress']);
        Route::get('/today-completion', [MiniTaskController::class, 'checkTodayCompletion']);
        Route::get('/stats', [MiniTaskController::class, 'getStats']);
        Route::get('/tasks', [MiniTaskController::class, 'getTasks']);
        Route::delete('/reset', [MiniTaskController::class, 'resetProgress']);
        Route::get('/weekly-data', [MiniTaskController::class, 'getWeeklyData']);
        Route::get('/encouragement-messages', [MiniTaskController::class, 'getEncouragementMessages']);
    });

    // ======================
    // GROWTH
    // ======================
    Route::prefix('growth')->group(function () {
        Route::post('/metrics', [GrowthController::class, 'updateMetrics']);
        Route::get('/timeline', [GrowthController::class, 'getTimeline']);
        Route::get('/achievements', [GrowthController::class, 'achievements']);
    });

    // ======================
    // HOME
    // ======================
    Route::prefix('home')->group(function () {
        Route::get('/dashboard-data', [HomeController::class, 'getDashboardData']);
        Route::post('/log-mood', [HomeController::class, 'logMood']);
        Route::get('/pet-stats', [HomeController::class, 'getPetStats']);
        Route::get('/affirmation', [HomeController::class, 'getAffirmation']);
        Route::get('/affirmations', [HomeController::class, 'getAffirmations']);
    });

}); // ✅ ONLY ONE closing bracket