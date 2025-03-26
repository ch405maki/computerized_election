<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Voter\VoterController;
use App\Http\Controllers\Candidate\CandidateController;
use App\Http\Controllers\Candidate\PositionController;
use App\Http\Controllers\Vote\VoteController;
use App\Http\Controllers\Voter\VoterStatusController;
use App\Http\Controllers\Log\LogController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/upload-users', [UserController::class, 'uploadUsers']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);


Route::patch('/activate-voter/{id}', [VoterStatusController::class, 'activate']);
Route::apiResource('voters', VoterController::class);
Route::apiResource('voter-status', VoterStatusController::class);

Route::apiResource('candidates', CandidateController::class);
Route::apiResource('positions', PositionController::class);
Route::apiResource('votes', VoteController::class);
Route::apiResource('logs', LogController::class);