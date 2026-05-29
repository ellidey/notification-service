<?php

namespace App\Http\Resources;

use App\Models\NotificationDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationDelivery',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'channel', ref: '#/components/schemas/NotificationChannel'),
        new OA\Property(property: 'status', ref: '#/components/schemas/NotificationStatus'),
        new OA\Property(property: 'attempts', type: 'integer'),
        new OA\Property(property: 'max_attempts', type: 'integer'),
        new OA\Property(property: 'last_error', type: 'string', nullable: true),
        new OA\Property(property: 'available_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'failed_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object',
)]
class NotificationDeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var NotificationDelivery $delivery */
        $delivery = $this->resource;

        return [
            'id' => $delivery->id,
            'channel' => $delivery->channel->value,
            'status' => $delivery->status->value,
            'attempts' => $delivery->attempts,
            'max_attempts' => $delivery->max_attempts,
            'last_error' => $delivery->last_error,
            'available_at' => $delivery->available_at?->toISOString(),
            'sent_at' => $delivery->sent_at?->toISOString(),
            'failed_at' => $delivery->failed_at?->toISOString(),
            'created_at' => $delivery->created_at?->toISOString(),
            'updated_at' => $delivery->updated_at?->toISOString(),
        ];
    }
}
