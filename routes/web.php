<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Voter\VoterController;
use App\Http\Controllers\Candidate\CandidateController;
use App\Http\Controllers\Candidate\PositionController;
use App\Http\Controllers\Vote\VoteController;
use App\Http\Controllers\Voter\VoterStatusController;
use App\Http\Controllers\Log\LogController;
use App\Http\Controllers\Report\ReportController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {   
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/voters', [VoterController::class, 'index'])->name('voters.index');

    Route::get('/voters/status', [VoterStatusController::class, 'index'])->name('status.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');

    Route::get('/candidates/positions', [PositionController::class, 'index'])->name('positions.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reports/result', [ReportController::class, 'index'])->name('candidates.index');

    Route::get('/reports/log', [ReportController::class, 'index'])->name('positions.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
