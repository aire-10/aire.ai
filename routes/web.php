<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BreathingController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('history', 'history')->name('history');
});

// show signup page
Route::get('/signup', [RegisterController::class, 'showRegistrationForm']);

// handle register
Route::post('/signup', [RegisterController::class, 'register']);

// show login page
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// handle login
Route::post('/login', [LoginController::class, 'login']);

// logout
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

// show page
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm']);

// handle reset
Route::post('/forgot-password', [ForgotPasswordController::class, 'reset']);

// show chat page
Route::view('/chat', 'chat')->middleware('auth');


Route::post('/chat', [ChatController::class, 'send'])->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/journal', [JournalController::class, 'index']);
    Route::post('/journal', [JournalController::class, 'store']);
    Route::get('/journal-history', [JournalController::class, 'history'])
    ->name('journal.history');
    Route::get('/journal/{id}', [JournalController::class, 'show'])->name('journal.show');
    Route::post('/mood', [MoodController::class, 'store']);
    Route::get('/mood-history', [MoodController::class, 'history']);
    Route::get('/profile', [ProfileController::class, 'index'])
    ->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/breathing', [BreathingController::class, 'store']);
    Route::get('/breathing-history', [BreathingController::class, 'history']);
});


require __DIR__.'/settings.php';
