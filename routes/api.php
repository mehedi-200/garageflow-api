<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceItemController;
use App\Http\Controllers\ServiceJobController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/search', [SearchController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // All authenticated users share full access (owner's decision, 2026-07-06).
    Route::apiResource('mechanics', UserController::class)->except(['show']);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('vehicles', VehicleController::class);

    Route::apiResource('service-jobs', ServiceJobController::class)->except(['destroy']);
    Route::patch('/service-jobs/{service_job}/status', [ServiceJobController::class, 'updateStatus']);
    Route::post('/service-jobs/{service_job}/items', [ServiceItemController::class, 'store']);
    Route::delete('/service-jobs/{service_job}/items/{item}', [ServiceItemController::class, 'destroy']);

    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show', 'update']);
    Route::patch('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
});
