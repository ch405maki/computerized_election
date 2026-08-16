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
use App\Http\Controllers\Election\ElectionController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Middleware\PreventBackHistory;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Voting page (restricted to logged-in voters AND prevents back button caching)
Route::middleware(['auth:voter', PreventBackHistory::class])->group(function () {
    Route::get('/vote', [VoteController::class, 'index'])->name('vote.index');
    Route::get('/voting', [VoteController::class, 'votingPage'])->name('vote.voting');
});

// Main Application Routes (Requires standard 'web' login)
Route::middleware(['auth:web', 'verified'])->group(function () {
    
    // ==========================================
    // SHARED ROUTES (Handled by Permissions)
    // ==========================================
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:showDashboardTab')
        ->name('dashboard');

    // Reports
    Route::middleware('permission:showReportsTab')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/results', [ReportController::class, 'index'])->name('results.index');
        Route::get('/reports/log', [LogController::class, 'index'])->name('log.index');
        Route::get('/reports/log/{electionId}/turnout-by-year', [LogController::class, 'getTurnoutByYear'])->name('log.turnout-by-year');
        
        Route::post('/results/{election}/verify', [ReportController::class, 'verify'])
            ->name('results.verify')
            ->withTrashed();

        Route::get('/results/{election}', [ReportController::class, 'show'])
            ->name('results.show')
            ->withTrashed();

        Route::get('/results/{election}/export', [ReportController::class, 'export'])
            ->name('results.export')
            ->withTrashed();
            
        Route::post('/signature/upload', [ReportController::class, 'uploadSignature'])->name('signature.upload');
        Route::delete('/signature/remove', [ReportController::class, 'destroySignature'])->name('signature.destroy');

    });

    // Voters Management
    Route::middleware('permission:showVoterTab')->group(function () {
        Route::get('/voters', [VoterController::class, 'index'])->name('voters.index');
        Route::get('/voters/status', [VoterStatusController::class, 'index'])->name('status.index');
    });

    // Candidate Management
    Route::middleware('permission:showCandidateTab')->group(function () {
        Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
        Route::get('/candidates/positions', [PositionController::class, 'index'])->name('positions.index');
    });

    // Election Configuration
    Route::middleware('permission:showElectionTab')->group(function () {
        Route::get('/elections', [ElectionController::class, 'index'])->name('elections.index');
        Route::get('/elections/{election}/edit', [ElectionController::class, 'edit'])->name('elections.edit');
        Route::post('/elections/verify-password', [ElectionController::class, 'verifyPassword'])->name('elections.verify-password');
    });

    // ==========================================
    // ADMIN-ONLY ROUTES
    // ==========================================
    Route::middleware('admin')->group(function () {
        
        // User Management (Admin Only)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';