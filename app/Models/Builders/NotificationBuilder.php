<?php

namespace App\Models\Builders;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @extends Builder<Notification>
 */
class NotificationBuilder extends Builder
{
    public function forUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    public function withFilteredDeliveries(?string $channel, ?string $status): self
    {
        return $this->with([
            'deliveries' => fn ($query) => $this->applyDeliveryFilters($query, $channel, $status),
        ]);
    }

    public function whereHasDelivery(?string $channel, ?string $status): self
    {
        if (! $channel && ! $status) {
            return $this;
        }

        return $this->whereHas(
            'deliveries',
            fn ($query) => $this->applyDeliveryFilters($query, $channel, $status),
        );
    }

    private function applyDeliveryFilters(Builder|HasMany $query, ?string $channel, ?string $status): Builder|HasMany
    {
        return $query
            ->when($channel, fn (Builder|HasMany $filteredQuery): Builder|HasMany => $filteredQuery->where('channel', $channel))
            ->when($status, fn (Builder|HasMany $filteredQuery): Builder|HasMany => $filteredQuery->where('status', $status));
    }
}
