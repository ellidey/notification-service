<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationReportResource;
use App\Models\NotificationReport;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationReportController extends Controller
{
    #[OA\Get(
        path: '/api/notifications/reports/{notificationReport}',
        summary: 'Получить отчёт по уведомлениям',
        tags: ['Notification reports'],
        parameters: [
            new OA\Parameter(
                name: 'notificationReport',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Отчёт по уведомлениям найден',
                content: new OA\JsonContent(ref: '#/components/schemas/NotificationReportResponse'),
            ),
            new OA\Response(response: 404, description: 'Отчёт по уведомлениям не найден'),
        ],
    )]
    public function show(NotificationReport $notificationReport): NotificationReportResource
    {
        return new NotificationReportResource($notificationReport);
    }

    #[OA\Get(
        path: '/api/notifications/reports/{notificationReport}/download',
        summary: 'Скачать файл отчёта по уведомлениям',
        tags: ['Notification reports'],
        parameters: [
            new OA\Parameter(
                name: 'notificationReport',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'CSV-файл отчёта',
                content: new OA\MediaType(
                    mediaType: 'text/csv',
                    schema: new OA\Schema(type: 'string', format: 'binary'),
                ),
            ),
            new OA\Response(response: 409, description: 'Отчёт ещё не готов к скачиванию'),
            new OA\Response(response: 404, description: 'Отчёт или файл отчёта не найден'),
        ],
    )]
    public function download(NotificationReport $notificationReport): StreamedResponse
    {
        if ($notificationReport->status !== ReportStatus::Ready || ! $notificationReport->file_path) {
            abort(409, 'Отчёт ещё не готов к скачиванию.');
        }

        if (! Storage::disk('local')->exists($notificationReport->file_path)) {
            abort(404, 'Файл отчёта не найден.');
        }

        return Storage::disk('local')->download(
            $notificationReport->file_path,
            "notification-report-{$notificationReport->id}.csv",
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
