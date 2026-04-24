<?php

use App\Http\Controllers\GroundingController;
use App\Http\Controllers\MoodBoosterController;
use App\Http\Controllers\MoodLiftingController;
use App\Http\Controllers\MindResetController;
use App\Http\Controllers\MiniTaskController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\Api\AireDataController;

Route::middleware('auth:sanctum')->group(function () {
    
    // AireData API endpoints
    Route::get('/mood-log', [AireDataController::class, 'getMoodLog']);
    Route::get('/streak', [AireDataController::class, 'getStreak']);
    Route::get('/days-tracked', [AireDataController::class, 'getDaysTracked']);
    Route::get('/today-checkins', [AireDataController::class, 'getTodayCheckInCount']);
    Route::get('/latest-mood', [AireDataController::class, 'getLatestMood']);
    Route::get('/mood-meta', [AireDataController::class, 'getMoodMeta']);
    Route::get('/positive-moods', [AireDataController::class, 'getPositiveMoods']);
    Route::post('/log-mood', [AireDataController::class, 'logMood']);
    
    // Grounding API endpoints
    Route::prefix('grounding')->group(function () {
        Route::post('/progress', [GroundingController::class, 'saveProgress']);
        Route::get('/progress', [GroundingController::class, 'getProgress']);
        Route::get('/today-completion', [GroundingController::class, 'checkTodayCompletion']);
        Route::get('/stats', [GroundingController::class, 'getStats']);
        Route::post('/step-inputs', [GroundingController::class, 'saveStepInputs']);
        Route::delete('/reset', [GroundingController::class, 'resetProgress']);
        Route::get('/history', [GroundingController::class, 'getHistory']);
    });
    
    // Mood Booster API endpoints
    Route::prefix('moodbooster')->group(function () {
        Route::post('/complete', [MoodBoosterController::class, 'complete']);
        Route::get('/history', [MoodBoosterController::class, 'history']);
        Route::get('/stats', [MoodBoosterController::class, 'getStats']);
    });
    
    // Mood Lifting API endpoints
    Route::prefix('moodlifting')->group(function () {
        Route::post('/activity', [MoodLiftingController::class, 'completeActivity']);
        Route::post('/track-mood', [MoodLiftingController::class, 'trackMood']);
        Route::get('/activities', [MoodLiftingController::class, 'getActivities']);
    });
    
    // Mind Reset API endpoints
    Route::prefix('mindreset')->group(function () {
        Route::post('/start', [MindResetController::class, 'start']);
        Route::post('/complete', [MindResetController::class, 'complete']);
        Route::get('/history', [MindResetController::class, 'history']);
        Route::get('/stats', [MindResetController::class, 'getStats']);
        Route::get('/technique/{technique}', [MindResetController::class, 'getTechnique']);
    });
    
    // Mini Task API endpoints
    Route::prefix('minitask')->group(function () {
        Route::post('/store', [MiniTaskController::class, 'store']);
        Route::put('/{id}', [MiniTaskController::class, 'update']);
        Route::patch('/{id}/complete', [MiniTaskController::class, 'complete']);
        Route::delete('/{id}', [MiniTaskController::class, 'destroy']);
        Route::post('/reorder', [MiniTaskController::class, 'reorder']);
        Route::get('/suggestions', [MiniTaskController::class, 'getSuggestions']);
        Route::get('/analytics', [MiniTaskController::class, 'analytics']);
    });
    
    // Growth API endpoints
    Route::prefix('growth')->group(function () {
        Route::post('/metrics', [GrowthController::class, 'updateMetrics']);
        Route::get('/timeline', [GrowthController::class, 'getTimeline']);
        Route::get('/achievements', [GrowthController::class, 'achievements']);
    });
        // Mood Booster API Routes
    Route::middleware('auth:sanctum')->prefix('moodbooster')->group(function () {
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
        // Mood Lifting API Routes
    Route::middleware('auth:sanctum')->prefix('moodlifting')->group(function () {
        Route::post('/progress', [MoodLiftingController::class, 'saveProgress']);
        Route::get('/progress', [MoodLiftingController::class, 'getProgress']);
        Route::get('/today-completion', [MoodLiftingController::class, 'checkTodayCompletion']);
        Route::get('/stats', [MoodLiftingController::class, 'getStats']);
        Route::get('/thoughts', [MoodLiftingController::class, 'getThoughts']);
        Route::delete('/reset', [MoodLiftingController::class, 'resetProgress']);
        Route::get('/weekly-data', [MoodLiftingController::class, 'getWeeklyData']);
        Route::get('/encouragement-messages', [MoodLiftingController::class, 'getEncouragementMessages']);
    });
    // Mind Reset API Routes
    Route::middleware('auth:sanctum')->prefix('mindreset')->group(function () {
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
    // Mini Task API Routes
    Route::middleware('auth:sanctum')->prefix('minitask')->group(function () {
        Route::post('/progress', [MiniTaskController::class, 'saveProgress']);
        Route::get('/progress', [MiniTaskController::class, 'getProgress']);
        Route::get('/today-completion', [MiniTaskController::class, 'checkTodayCompletion']);
        Route::get('/stats', [MiniTaskController::class, 'getStats']);
        Route::get('/tasks', [MiniTaskController::class, 'getTasks']);
        Route::delete('/reset', [MiniTaskController::class, 'resetProgress']);
        Route::get('/weekly-data', [MiniTaskController::class, 'getWeeklyData']);
        Route::get('/encouragement-messages', [MiniTaskController::class, 'getEncouragementMessages']);
    });
    // Home Dashboard API Routes
    Route::middleware('auth:sanctum')->prefix('home')->group(function () {
        Route::get('/dashboard-data', [HomeController::class, 'getDashboardData']);
        Route::post('/log-mood', [HomeController::class, 'logMood']);
        Route::get('/pet-stats', [HomeController::class, 'getPetStats']);
        Route::get('/affirmation', [HomeController::class, 'getAffirmation']);
        Route::get('/affirmations', [HomeController::class, 'getAffirmations']);
    });
});