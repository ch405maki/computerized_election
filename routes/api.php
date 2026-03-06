<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Voter\VoterController;
use App\Http\Controllers\Candidate\CandidateController;
use App\Http\Controllers\Candidate\PositionController;
use App\Http\Controllers\Vote\VoteController;
use App\Http\Controllers\Voter\VoterStatusController;
use App\Http\Controllers\Election\ElectionController;
use App\Http\Controllers\Log\LogController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\VoteDataController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// User Routes
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/upload-users', [UserController::class, 'uploadUsers']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

// Voter Routes
Route::delete('/voters/{id}', [VoterController::class, 'destroy']);
Route::patch('/activate-voter/{id}', [VoterStatusController::class, 'activate']);
Route::post('/voters', [VoterController::class, 'store'])->name('voters.store');
Route::post('/upload-voters', [VoterController::class, 'uploadVoters']);
Route::post('/voters/status/activate/{id}', [VoterStatusController::class, 'activate']);
Route::post('/voters/status/activate-all', [VoterStatusController::class, 'activateAll']);
Route::patch('/voters/{voter}', [VoterController::class, 'update']);

// Candidate Routes
Route::post('/positions', [PositionController::class, 'store']);
Route::delete('/positions/{position}', [PositionController::class, 'destroy']);

// Election Routes
Route::post('/elections', [ElectionController::class, 'store']);
Route::delete('/elections/{election}', [ElectionController::class, 'destroy']);
Route::patch('/elections/{election}', [ElectionController::class, 'update']);

// Candidate Routes
Route::post('/candidates', [CandidateController::class, 'store']);
Route::delete('/candidates/{id}', [CandidateController::class, 'destroy']);

// Vote Routes
Route::post('/votes', [VoteController::class, 'store']);

Route::apiResource('logs', LogController::class);

// Ranking Route
Route::get('/vote-ranking', [VoteDataController::class, 'getVoteRanking']);