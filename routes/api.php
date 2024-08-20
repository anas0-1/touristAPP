<?php

use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::get('password/reset/{token}', function () {
    return view('auth.reset');
})->name('password.reset');

Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.update');