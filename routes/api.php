<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
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

    // Feature access is permission-based; super admins (is_admin) bypass all checks.
    // Users/roles indexes stay open so assignment dropdowns work for any permitted user.
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/roles', [RoleController::class, 'index']);

    Route::middleware('permission:users')->group(function () {
        Route::apiResource('users', UserController::class)->only(['store', 'update', 'destroy']);
    });

    Route::middleware('permission:roles')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::apiResource('roles', RoleController::class)->except(['index']);
    });

    Route::middleware('permission:customers')->group(function () {
        Route::apiResource('customers', CustomerController::class);
    });

    Route::middleware('permission:vehicles')->group(function () {
        Route::apiResource('vehicles', VehicleController::class);
    });

    Route::middleware('permission:service_jobs')->group(function () {
        Route::apiResource('service-jobs', ServiceJobController::class)->except(['destroy']);
        Route::patch('/service-jobs/{service_job}/status', [ServiceJobController::class, 'updateStatus']);
        Route::post('/service-jobs/{service_job}/items', [ServiceItemController::class, 'store']);
        Route::delete('/service-jobs/{service_job}/items/{item}', [ServiceItemController::class, 'destroy']);
    });

    Route::middleware('permission:invoices')->group(function () {
        Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show', 'update']);
        Route::patch('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
    });
});
