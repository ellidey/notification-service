<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateNotificationReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNotificationReportRequest;
use App\Http\Requests\ListNotificationReportsRequest;
use App\Http\Resources\NotificationReportResource;
use App\Models\NotificationReport;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class UserNotificationReportController extends Controller
{
    #[OA\Get(
        path: '/api/users/{userId}/notifications/reports',
        summary: 'Получить отчёты пользователя по уведомлениям',
        tags: ['Notification reports'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/ReportStatus'),
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
                description: 'Список отчётов по уведомлениям',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedNotificationReportsResponse'),
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации фильтров'),
        ],
    )]
    public function index(ListNotificationReportsRequest $request, int $userId): AnonymousResourceCollection
    {
        return NotificationReportResource::collection(
            NotificationReport::query()
                ->where('user_id', $userId)
                ->when(
                    $request->validated('status'),
                    fn ($query, string $status) => $query->where('status', $status),
                )
                ->latest()
                ->paginate($request->integer('per_page', 15)),
        );
    }

    #[OA\Post(
        path: '/api/users/{userId}/notifications/reports',
        summary: 'Создать отчёт пользователя по уведомлениям',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateNotificationReportRequest'),
        ),
        tags: ['Notification reports'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 202,
                description: 'Отчёт по уведомлениям принят в обработку',
                content: new OA\JsonContent(ref: '#/components/schemas/NotificationReportResponse'),
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    public function store(
        CreateNotificationReportRequest $request,
        CreateNotificationReportAction $createNotificationReport,
        int $userId,
    ): JsonResponse {
        $report = $createNotificationReport->handle(
            userId: $userId,
            periodFrom: CarbonImmutable::parse($request->validated('period_from')),
            periodTo: CarbonImmutable::parse($request->validated('period_to')),
        );

        return new NotificationReportResource($report)
            ->response()
            ->setStatusCode(202);
    }
}
