<?php

use App\Application\Identity\Http\Controllers\Auth\LoginCodeController;
use App\Application\Identity\Http\Controllers\Auth\LoginController;
use App\Application\Identity\Http\Controllers\Auth\LoginEmailController;
use App\Application\Identity\Http\Controllers\Auth\LoginVerifyController;
use App\Application\Identity\Http\Controllers\Auth\MagicLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login/email', LoginEmailController::class)
        ->middleware('throttle:5,1')
        ->name('login.email');
    Route::get('login/verify', LoginVerifyController::class)->name('login.verify');
    Route::post('login/code', LoginCodeController::class)
        ->middleware('throttle:5,1')
        ->name('login.code');
    Route::get('login/magic/{token}', [MagicLinkController::class, 'show'])
        ->middleware('signed')
        ->name('login.magic.show');
    Route::post('login/magic/{token}', [MagicLinkController::class, 'store'])
        ->middleware('signed')
        ->name('login.magic.store');
    Route::get('register', fn () => redirect()->route('login'))->name('register');
    Route::post('register', fn () => redirect()->route('login'));
});
