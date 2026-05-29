<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'message' => $this->message,
            'status' => $this->aggregateStatus(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
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
