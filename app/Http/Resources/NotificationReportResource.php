<?php

namespace App\Http\Resources;

use App\Models\NotificationReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationReport',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'user_id', type: 'integer', example: 1001),
        new OA\Property(property: 'status', ref: '#/components/schemas/ReportStatus'),
        new OA\Property(property: 'period_from', type: 'string', format: 'date-time'),
        new OA\Property(property: 'period_to', type: 'string', format: 'date-time'),
        new OA\Property(property: 'file_path', type: 'string', nullable: true),
        new OA\Property(property: 'error_message', type: 'string', nullable: true),
        new OA\Property(property: 'completed_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'failed_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'NotificationReportResponse',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/NotificationReport'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PaginatedNotificationReportsResponse',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/NotificationReport'),
        ),
        new OA\Property(property: 'links', type: 'object'),
        new OA\Property(property: 'meta', type: 'object'),
    ],
    type: 'object',
)]
class NotificationReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var NotificationReport $report */
        $report = $this->resource;

        return [
            'id' => $report->id,
            'user_id' => $report->user_id,
            'status' => $report->status->value,
            'period_from' => $report->period_from->toISOString(),
            'period_to' => $report->period_to->toISOString(),
            'file_path' => $report->file_path,
            'error_message' => $report->error_message,
            'completed_at' => $report->completed_at?->toISOString(),
            'failed_at' => $report->failed_at?->toISOString(),
            'created_at' => $report->created_at?->toISOString(),
            'updated_at' => $report->updated_at?->toISOString(),
        ];
    }
}
