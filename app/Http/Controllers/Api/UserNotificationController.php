<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListUserNotificationsRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class UserNotificationController extends Controller
{
    #[OA\Get(
        path: '/api/users/{userId}/notifications',
        summary: 'Get user notification history',
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
            new OA\Parameter(
                name: 'channel',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/NotificationChannel'),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/NotificationStatus'),
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', maximum: 100, minimum: 1, default: 15),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification list',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedNotificationsResponse'),
            ),
            new OA\Response(response: 422, description: 'Filter validation error'),
        ],
    )]
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
