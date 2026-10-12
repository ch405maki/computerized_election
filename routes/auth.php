<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VoterAuthController;
use Illuminate\Support\Facades\Route;

// ========== ADMIN AUTHENTICATION ROUTES ==========
Route::middleware(['guest', 'prevent.mixed.auth:web'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth:web')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// ========== VOTER AUTHENTICATION ROUTES ==========
Route::middleware(['guest', 'prevent.mixed.auth:voter'])->prefix('voter')->name('voter.')->group(function () {
    Route::get('login', [VoterAuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('login', [VoterAuthController::class, 'login'])
        ->name('login.submit');
});

Route::middleware('auth:voter')->prefix('voter')->name('voter.')->group(function () {
    Route::post('logout', [VoterAuthController::class, 'logout'])
        ->name('logout');
});