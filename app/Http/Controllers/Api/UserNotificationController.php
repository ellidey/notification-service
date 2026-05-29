<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListUserNotificationsRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserNotificationController extends Controller
{
    public function __invoke(
        ListUserNotificationsRequest $request,
        int $userId,
    ): AnonymousResourceCollection {
        $channel = $request->validated('channel');
        $status = $request->validated('status');

        return NotificationResource::collection(
            Notification::query()
                ->forUser($userId)
                ->withFilteredDeliveries($channel, $status)
                ->whereHasDelivery($channel, $status)
                ->latest()
                ->paginate($request->integer('per_page', 15)),
        );
    }
}
