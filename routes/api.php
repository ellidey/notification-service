<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationReportController;
use App\Http\Controllers\Api\UserNotificationController;
use App\Http\Controllers\Api\UserNotificationReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->group(function (): void {
    Route::get('/reports/{notificationReport}/download', [NotificationReportController::class, 'download']);
    Route::get('/reports/{notificationReport}', [NotificationReportController::class, 'show']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::get('/{notification}', [NotificationController::class, 'show']);
});

Route::prefix('users/{userId}')->group(function (): void {
    Route::get('/notifications', UserNotificationController::class);

    Route::prefix('notifications/reports')->group(function (): void {
        Route::get('/', [UserNotificationReportController::class, 'index']);
        Route::post('/', [UserNotificationReportController::class, 'store']);
    });
});
