<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GroundingController;
use App\Http\Controllers\MoodBoosterController;
use App\Http\Controllers\MoodLiftingController;
use App\Http\Controllers\MindResetController;
use App\Http\Controllers\MiniTaskController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BreathingController;
use App\Http\Controllers\BoosterController;

// ==============================================
// PUBLIC ROUTES (No authentication required)
// ==============================================

// Landing page
Route::get('/', [HomeController::class, 'landing'])->name('landing');

// Gemini API key endpoint
Route::get('/get-gemini-key', function () {
    return response()->json([
        'key' => env('GEMINI_API_KEY')
    ]);
});

// Authentication Routes (NO middleware)
Route::get('/signup', [RegisterController::class, 'showRegistrationForm'])->name('signup');
Route::post('/signup', [RegisterController::class, 'register']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'reset'])->name('password.email');


// ==============================================
// AUTHENTICATED ROUTES (Require login)
// ==============================================

Route::middleware(['auth'])->group(function () {
    // =====================
    // HOME / DASHBOARD
    // =====================
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // =====================
    // CHAT
    // =====================
    Route::view('/chat', 'chat')->name('chat');
    Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');

    // =====================
    // JOURNAL
    // =====================
    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
    Route::post('/journal', [JournalController::class, 'store'])->name('journal.store');
    Route::get('/journal-history', [JournalController::class, 'history'])->name('journal.history');
    Route::get('/journal/{id}', [JournalController::class, 'show'])->name('journal.show');

    // =====================
    // MOOD
    // =====================
    Route::post('/mood', [MoodController::class, 'store'])->name('mood.store');
    Route::get('/mood-history', [MoodController::class, 'history'])->name('mood.history');

    // =====================
    // PROFILE
    // =====================
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // =====================
    // BREATHING
    // =====================
    Route::post('/breathing', [BreathingController::class, 'store'])->name('breathing.store');
    Route::get('/breathing-history', [BreathingController::class, 'history'])->name('breathing.history');
    Route::get('/breathing-mt', [BreathingController::class, 'history'])->name('breathing-mt');

    // =====================
    // SELF CARE (YOUR ROUTES)
    // =====================
    Route::get('/grounding', [GroundingController::class, 'index'])->name('grounding');
    Route::get('/moodbooster', [MoodBoosterController::class, 'index'])->name('moodbooster');
    Route::view('/moodlifting', 'moodlifting')->name('moodlifting');
    Route::get('/mindreset', [MindResetController::class, 'index'])->name('mindreset');
    Route::get('/minitask', [MiniTaskController::class, 'index'])->name('minitask');

    // =====================
    // BODYBOOSTER (NEW)
    // =====================
    Route::get('/bodybooster', function () {
        return view('bodybooster');
    })->name('bodybooster');

    // =====================
    // BOOSTERS
    // =====================

    Route::get('/booster/progress/{type}', [BoosterController::class, 'get']);
    Route::post('/booster/toggle', [BoosterController::class, 'toggle']);
    Route::post('/booster/reset/{type}', [BoosterController::class, 'reset']);
    Route::get('/booster/check/{type}', [BoosterController::class, 'check']);

    // =====================
    // GROWTH (BUTTERFLY)
    // =====================
    Route::get('/growth', [GrowthController::class, 'index'])->name('growth');
    Route::get('/growth/data', [GrowthController::class, 'getData'])->name('growth.data');
    Route::post('/growth/action', [GrowthController::class, 'recordAction'])->name('growth.action');
    Route::get('/growth/mood-chart', [GrowthController::class, 'getMoodChartData'])->name('growth.mood-chart');

    // =====================
    // CHAT HISTORY
    // =====================
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/history/sessions', [HistoryController::class, 'getSessions'])->name('history.sessions');
    Route::get('/history/session/{id}', [HistoryController::class, 'getSession'])
        ->where('id', '[A-Za-z0-9\-_]+')
        ->name('history.session');
    Route::post('/history/session', [HistoryController::class, 'createSession'])->name('history.session.create');
    Route::post('/history/session/{id}/rename', [HistoryController::class, 'renameSession'])->name('history.session.rename');
    Route::delete('/history/session/{id}', [HistoryController::class, 'deleteSession'])->name('history.session.delete');
    Route::get('/history/search', [HistoryController::class, 'search'])->name('history.search');
    Route::get('/history/stats', [HistoryController::class, 'getStats'])->name('history.stats');
    Route::post('/history/message', [HistoryController::class, 'saveMessage'])->name('history.message');

});

require __DIR__.'/settings.php';
