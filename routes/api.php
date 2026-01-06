<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AttendanceController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'profile']);

    Route::post('/register-face', [UserController::class, 'registerFace']);
    Route::post('/verify-face', [AttendanceController::class, 'verifyFace']);
    Route::get('/attendances/today', [AttendanceController::class, 'today']);
    Route::get('/attendances', [AttendanceController::class, 'history']);
});


