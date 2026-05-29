<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserNotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/notifications', [NotificationController::class, 'store']);
Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
Route::get('/users/{userId}/notifications', UserNotificationController::class);
