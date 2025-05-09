<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Auth\VoterAuthController;

use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Voter\VoterController;
use App\Http\Controllers\Candidate\CandidateController;
use App\Http\Controllers\Candidate\PositionController;
use App\Http\Controllers\Vote\VoteController;
use App\Http\Controllers\Voter\VoterStatusController;
use App\Http\Controllers\Log\LogController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Election\ElectionController;
use App\Http\Controllers\Dashboard\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


// Voter Login & Logout Routes
Route::get('/voter/login', [VoterAuthController::class, 'showLoginForm'])->name('voter.login');
Route::post('/voter/login', [VoterAuthController::class, 'login']);
Route::post('/', [VoterAuthController::class, 'logout'])->name('voter.logout');

// Voting page (restricted to logged-in voters)
Route::middleware('auth:voter')->group(function () {
    Route::get('/vote', [VoteController::class, 'index'])->name('vote.index');
});
// Dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// User Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

// Voters Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/voters', [VoterController::class, 'index'])->name('voters.index');

    Route::get('/voters/status', [VoterStatusController::class, 'index'])->name('status.index');
});

// Candidate Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');

    Route::get('/candidates/positions', [PositionController::class, 'index'])->name('positions.index');
});

// Election Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/election', [ElectionController::class, 'index'])->name('elections.index');
});

// Report Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reports/results', [ReportController::class, 'index'])->name('results.index');

    Route::get('/reports/log', [LogController::class, 'index'])->name('positions.index');
});

// Results Route
Route::get('/results/{election}', [ReportController::class, 'show'])->name('results.show');
Route::get('/results/{election}/export', [ReportController::class, 'export'])->name('results.export');


// Vote Routes
Route::get('/vote', [VoteController::class, 'index'])->name('vote.index');
Route::get('/voting', [VoteController::class, 'votingPage'])->name('vote.voting');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
