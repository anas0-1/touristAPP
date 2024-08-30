<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;



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
});

Route::middleware('auth:api')->get('/user-role', [AuthController::class, 'getUserRole']);

<<<<<<< HEAD
Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
=======
Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
>>>>>>> 3f55ba82d508d831a57ca7f8fec0449b41e19245
