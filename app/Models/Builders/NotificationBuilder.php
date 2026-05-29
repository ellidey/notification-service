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
            'deliveries' => function ($query) use ($channel, $status): void {
                /** @var HasMany $query */
                $this->applyDeliveryRelationFilters($query, $channel, $status);
            },
        ]);
    }

    public function whereHasDelivery(?string $channel, ?string $status): self
    {
        if (! $channel && ! $status) {
            return $this;
        }

        return $this->whereHas(
            'deliveries',
            fn (Builder $query): Builder => $this->applyDeliveryBuilderFilters($query, $channel, $status),
        );
    }

    private function applyDeliveryBuilderFilters(Builder $query, ?string $channel, ?string $status): Builder
    {
        return $query
            ->when($channel, fn (Builder $filteredQuery): Builder => $filteredQuery->where('channel', $channel))
            ->when($status, fn (Builder $filteredQuery): Builder => $filteredQuery->where('status', $status));
    }

    private function applyDeliveryRelationFilters(HasMany $query, ?string $channel, ?string $status): void
    {
        if ($channel) {
            $query->where('channel', $channel);
        }

        if ($status) {
            $query->where('status', $status);
        }
    }
}
