<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('journal', 'journal')->name('journal');
    Route::view('history', 'history')->name('history');
    Route::view('profile', 'profile')->name('profile.show');
});

require __DIR__.'/settings.php';
