<?php

use App\Http\Controllers\Auth\MagicLinkController;
use Illuminate\Support\Facades\Route;

// The marketing site lives in the private openbka-site repo (openbka.org).
Route::redirect('/', '/app')->name('home');

Route::get('/login/link/{token}', [MagicLinkController::class, 'show'])
    ->name('magic-link.show');
Route::post('/login/link/{token}', [MagicLinkController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('magic-link.store');

// Filament links to a route named "login" when a guest hits a protected page.
Route::redirect('/login', '/app/login')->name('login');
