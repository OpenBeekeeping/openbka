<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Support\Mode;
use Illuminate\Support\Facades\Route;

// The hosted service shows the openbka.org home page; self-hosted installs go straight to sign-in.
Route::get('/', fn () => Mode::hosted() ? view('welcome') : redirect('/app'))->name('home');

Route::get('/login/link/{token}', [MagicLinkController::class, 'show'])
    ->name('magic-link.show');
Route::post('/login/link/{token}', [MagicLinkController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('magic-link.store');

// Filament links to a route named "login" when a guest hits a protected page.
Route::redirect('/login', '/app/login')->name('login');
