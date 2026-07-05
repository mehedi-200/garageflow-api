<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::apiResource('customers', CustomerController::class)->only(['index', 'show']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('mechanics', UserController::class)->except(['show']);
        Route::apiResource('customers', CustomerController::class)->except(['index', 'show']);
    });
});
