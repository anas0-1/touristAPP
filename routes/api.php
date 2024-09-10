<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ApplicationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');

Route::middleware('auth:api')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    Route::resource('programs', ProgramController::class);
    Route::get('programs/{program}/activities', [ActivityController::class, 'index']);
    Route::post('programs/{program}/activities', [ActivityController::class, 'store']);
    Route::get('programs/{program}/activities/{activity}', [ActivityController::class, 'show']);
    Route::put('programs/{program}/activities/{activity}', [ActivityController::class, 'update']); 
    Route::delete('programs/{program}/activities/{activity}', [ActivityController::class, 'destroy']);
    Route::post('programs/{program}/applications', [ApplicationController::class, 'store']);
    Route::delete('applications/{application}', [ApplicationController::class, 'destroy']);
});

Route::middleware('auth:api')->get('/user-role', [AuthController::class, 'getUserRole']);
Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
