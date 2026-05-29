<?php

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Notification',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'user_id', type: 'integer', example: 1001),
        new OA\Property(property: 'message', type: 'string', example: 'Test notification'),
        new OA\Property(property: 'status', ref: '#/components/schemas/NotificationStatus'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'deliveries',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/NotificationDelivery'),
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'NotificationResponse',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/Notification'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PaginatedNotificationsResponse',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Notification'),
        ),
        new OA\Property(property: 'links', type: 'object'),
        new OA\Property(property: 'meta', type: 'object'),
    ],
    type: 'object',
)]
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Notification $notification */
        $notification = $this->resource;

        return [
            'id' => $notification->id,
            'user_id' => $notification->user_id,
            'message' => $notification->message,
            'status' => $this->aggregateStatus(),
            'created_at' => $notification->created_at?->toISOString(),
            'updated_at' => $notification->updated_at?->toISOString(),
            'deliveries' => NotificationDeliveryResource::collection($this->whenLoaded('deliveries')),
        ];
    }

    private function aggregateStatus(): string
    {
        if (! $this->resource->relationLoaded('deliveries')) {
            return 'processing';
        }

        $deliveries = $this->resource->deliveries;

        if ($deliveries->isEmpty()) {
            return 'processing';
        }

        if ($deliveries->contains(fn ($delivery): bool => $delivery->status->value === 'processing')) {
            return 'processing';
        }

        if ($deliveries->contains(fn ($delivery): bool => $delivery->status->value === 'error')) {
            return 'error';
        }

        return 'sent';
    }
}
