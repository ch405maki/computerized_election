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
Route::patch('/activate-voter/{id}', [VoterStatusController::class, 'activate']);
Route::post('/voters', [VoterController::class, 'store'])->name('voters.store');
Route::post('/voters/status/activate/{id}', [VoterStatusController::class, 'activate']);
Route::post('/voters/status/activate-all', [VoterStatusController::class, 'activateAll']);

// Candidate Routes
Route::post('/positions', [PositionController::class, 'store']);

// Election Routes
Route::post('/elections', [ElectionController::class, 'store']);

Route::apiResource('candidates', CandidateController::class);
Route::apiResource('votes', VoteController::class);
Route::apiResource('logs', LogController::class);