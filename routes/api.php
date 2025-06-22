<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComplaintController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Customer routes
    Route::middleware('role:customer')->group(function () {
        Route::post('/complaints', [ComplaintController::class, 'store']);
        Route::get('/my-complaints', [ComplaintController::class, 'customerComplaints']);
    });

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/complaints', [ComplaintController::class, 'index']);
        Route::post('/complaints/{complaint}/assign', [ComplaintController::class, 'assign']);
        Route::get('/technicians/available', [ComplaintController::class, 'availableTechnicians']);
    });

    // Technician routes
    Route::middleware('role:technician')->group(function () {        
        Route::get('/assigned-complaints', [ComplaintController::class, 'technicianComplaints']);
        Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus']);
    });

    // Common routes (accessible by multiple roles with policy checks)
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show']);
    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy']);
});
