<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:web')->group(function () {
    Route::view('/', 'welcome')->name('home');

    // Placeholder to exercise the contact rate limiter until the real controller exists.
    Route::post('/contact', fn () => response()->noContent())
        ->middleware('throttle:contact')
        ->name('contact.store');
});
