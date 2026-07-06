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

    Route::apiResource('customers', CustomerController::class)->only(['index', 'show']);
    Route::apiResource('vehicles', VehicleController::class)->only(['index', 'show']);

    Route::apiResource('service-jobs', ServiceJobController::class)->only(['index', 'show']);
    Route::patch('/service-jobs/{service_job}/status', [ServiceJobController::class, 'updateStatus']);
    Route::post('/service-jobs/{service_job}/items', [ServiceItemController::class, 'store']);
    Route::delete('/service-jobs/{service_job}/items/{item}', [ServiceItemController::class, 'destroy']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('mechanics', UserController::class)->except(['show']);
        Route::apiResource('customers', CustomerController::class)->except(['index', 'show']);
        Route::apiResource('vehicles', VehicleController::class)->except(['index', 'show']);
        Route::apiResource('service-jobs', ServiceJobController::class)->only(['store', 'update']);

        Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show', 'update']);
        Route::patch('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
    });
});
