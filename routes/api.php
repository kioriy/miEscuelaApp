<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParentAuthController;

use App\Http\Controllers\Api\MonitorSyncController;
use App\Http\Controllers\Api\AttendanceSyncController;

use App\Http\Controllers\Api\KioskSetupController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\Admin\TeacherDashboardController;

Route::post('/auth/parent/google', [ParentAuthController::class, 'loginWithGoogle']);
Route::post('/setup/kiosk/activate', [KioskSetupController::class, 'activate']);
Route::get('/setup/kiosk/schools', [KioskSetupController::class, 'getSchoolsForActivation']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/setup/kiosk/status', [KioskSetupController::class, 'getStatus']);
    // Parent Portal
    Route::get('/parent/dashboard', [\App\Http\Controllers\Api\ParentDashboardController::class, 'index']);
    Route::get('/parent/students', [\App\Http\Controllers\Api\ParentDashboardController::class, 'getStudents']);
    Route::post('/parent/authorized-persons', [\App\Http\Controllers\Api\ParentDashboardController::class, 'storeAuthorizedPerson']);
    Route::get('/parent/history', [\App\Http\Controllers\Api\ParentDashboardController::class, 'getHistory']);

    // Rutas para el Kiosco / Monitor
    Route::get('/sync/monitor/school', [MonitorSyncController::class, 'getSchoolInfo']);
    Route::get('/sync/monitor/pull', [MonitorSyncController::class, 'pullData']);
    Route::post('/sync/monitor/push', [AttendanceSyncController::class, 'pushData']);
    Route::get('/sync/monitor/stats', [AttendanceSyncController::class, 'getKioskStats']);
    Route::post('/sync/monitor/apply-time-offset', [KioskSetupController::class, 'applyTimeOffset']);

    // Rutas para el Panel de Administración (Vue Frontend)
    Route::prefix('admin')->group(function () {
        Route::get('/school/time-sync-token', [KioskSetupController::class, 'getTimeSyncToken']);

        Route::get('/classrooms', [\App\Http\Controllers\Api\ClassroomController::class, 'index']);

        Route::get('/stats', [AdminController::class, 'dashboardStats']);
        // Schools Management
        Route::get('/schools', [AdminController::class, 'getSchools']);
        Route::post('/schools', [AdminController::class, 'storeSchool']);
        Route::get('/schools/{id}', [AdminController::class, 'showSchool']);
        Route::put('/schools/{id}', [AdminController::class, 'updateSchool']);
        Route::post('/schools/{id}/students/import', [AdminController::class, 'importStudents']);

        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'showUser']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
        Route::post('/users/{id}/resend-welcome', [AdminController::class, 'resendWelcomeEmail']);

        // Teachers Management
        Route::get('/teachers', [\App\Http\Controllers\Api\Admin\TeacherController::class, 'index']);
        Route::get('/teachers/{id}', [\App\Http\Controllers\Api\Admin\TeacherController::class, 'show']);
        Route::post('/teachers', [\App\Http\Controllers\Api\Admin\TeacherController::class, 'store']);
        Route::put('/teachers/{id}', [\App\Http\Controllers\Api\Admin\TeacherController::class, 'update']);
        Route::delete('/teachers/{id}', [\App\Http\Controllers\Api\Admin\TeacherController::class, 'destroy']);

        // Students Management
        Route::get('/schools/{id}/students', [AdminController::class, 'getStudents']);
        Route::get('/students/{id}', [AdminController::class, 'showStudent']);
        Route::get('/schools/{id}/leaderboard', [AdminController::class, 'getLeaderboard']);

        // Kioscos Management
        Route::get('/kioscos', [\App\Http\Controllers\Api\Admin\KioskController::class, 'index']);
        Route::get('/kioscos/{id}', [\App\Http\Controllers\Api\Admin\KioskController::class, 'show']);
        Route::post('/kioscos', [\App\Http\Controllers\Api\Admin\KioskController::class, 'store']);
        Route::put('/kioscos/{id}', [\App\Http\Controllers\Api\Admin\KioskController::class, 'update']);
        Route::delete('/kioscos/{id}', [\App\Http\Controllers\Api\Admin\KioskController::class, 'destroy']);
        Route::post('/kioscos/{id}/link-school', [\App\Http\Controllers\Api\Admin\KioskController::class, 'linkSchool']);
        Route::post('/kioscos/{id}/unlink-school', [\App\Http\Controllers\Api\Admin\KioskController::class, 'unlinkSchool']);
        Route::post('/kioscos/{id}/reset', [\App\Http\Controllers\Api\Admin\KioskController::class, 'reset']);
        // Estudiantes e Imágenes
        Route::get('/students', [AdminController::class, 'getStudents']);
        Route::post('/students', [AdminController::class, 'storeStudent']);
        Route::get('/students/{id}', [AdminController::class, 'showStudent']);
        Route::put('/students/{id}', [AdminController::class, 'updateStudent']);
        Route::delete('/students/{id}', [AdminController::class, 'destroyStudent']);
        Route::post('/students/photos/bulk', [AdminController::class, 'bulkUploadPhotos']);

        Route::get('/reports/unclosed', [AdminController::class, 'getUnclosedAttendance']);
        Route::get('/director/stats', [AdminController::class, 'directorDashboardStats']);
        Route::get('/director/messaging/context', [\App\Http\Controllers\Api\Admin\DirectorMessagingController::class, 'getContext']);
        Route::post('/director/messaging/send', [\App\Http\Controllers\Api\Admin\DirectorMessagingController::class, 'sendMessage']);
        Route::get('/director/messaging/history', [\App\Http\Controllers\Api\Admin\DirectorMessagingController::class, 'getSentMessages']);

        // Teacher Portal
        Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'getDashboardInfo']);
        Route::get('/teacher/attendance/{classroomId}/pending', [\App\Http\Controllers\Api\Admin\TeacherAttendanceController::class, 'getPending']);
        Route::post('/teacher/attendance/{classroomId}/mark', [\App\Http\Controllers\Api\Admin\TeacherAttendanceController::class, 'markAttendance']);
        Route::post('/teacher/attendance/{classroomId}/finalize', [\App\Http\Controllers\Api\Admin\TeacherAttendanceController::class, 'finalizeAttendance']);

        // Teacher Messaging
        Route::get('/teacher/messaging/context', [\App\Http\Controllers\Api\Teacher\MessagingController::class, 'getTeacherContext']);
        Route::get('/teacher/messaging/history', [\App\Http\Controllers\Api\Teacher\MessagingController::class, 'getSentMessages']);
        Route::post('/teacher/messaging/send', [\App\Http\Controllers\Api\Teacher\MessagingController::class, 'sendMessage']);
    });
});
