<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParentAuthController;

use App\Http\Controllers\Api\MonitorSyncController;
use App\Http\Controllers\Api\AttendanceSyncController;

use App\Http\Controllers\Api\KioskSetupController;
use App\Http\Controllers\Api\AdminController;

Route::post('/auth/parent/google', [ParentAuthController::class, 'loginWithGoogle']);
Route::post('/setup/kiosk/activate', [KioskSetupController::class, 'activate']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/parent/students', function (Request $request) {
        return response()->json(['message' => 'Pronto listaremos los hijos del usuario: ' . $request->user()->name]);
    });

    // Rutas para el Kiosco / Monitor
    Route::get('/sync/monitor/pull', [MonitorSyncController::class, 'pullData']);
    Route::post('/sync/monitor/push', [AttendanceSyncController::class, 'pushData']);

    // Rutas para el Panel de Administración (Vue Frontend)
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'dashboardStats']);
        Route::get('/schools', [AdminController::class, 'getSchools']);
        Route::get('/users', [AdminController::class, 'getUsers']);
    });
});
