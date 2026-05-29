<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateNotificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    #[OA\Post(
        path: '/api/notifications',
        summary: 'Создать уведомление',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateNotificationRequest'),
        ),
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Уведомление создано',
                content: new OA\JsonContent(ref: '#/components/schemas/NotificationResponse'),
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
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

    #[OA\Get(
        path: '/api/notifications/{notification}',
        summary: 'Получить уведомление',
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'notification',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Уведомление найдено',
                content: new OA\JsonContent(ref: '#/components/schemas/NotificationResponse'),
            ),
            new OA\Response(response: 404, description: 'Уведомление не найдено'),
        ],
    )]
    public function show(Notification $notification): NotificationResource
    {
        return new NotificationResource($notification->load('deliveries'));
    }
}
