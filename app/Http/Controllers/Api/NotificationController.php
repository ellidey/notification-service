<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateNotificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function store(StoreNotificationRequest $request, CreateNotificationAction $createNotification): JsonResponse
    {
        $notification = $createNotification->handle(
            userId: $request->integer('user_id'),
            message: $request->string('message')->toString(),
            channels: $request->array('channels'),
        );

        return new NotificationResource($notification)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Notification $notification): NotificationResource
    {
        return new NotificationResource($notification->load('deliveries'));
    }
}
