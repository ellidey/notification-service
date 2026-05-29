<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Models\NotificationDelivery;
use App\Notifications\Delivery\NotificationSenderRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendNotificationDeliveryJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public readonly string $deliveryId)
    {
        $this->onQueue('notifications');
    }

    public function handle(NotificationSenderRegistry $senders): void
    {
        DB::transaction(function () use ($senders): void {
            $delivery = NotificationDelivery::query()
                ->whereKey($this->deliveryId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($delivery->status !== NotificationStatus::Processing) {
                return;
            }

            $delivery->forceFill([
                'attempts' => $delivery->attempts + 1,
                'available_at' => null,
            ])->save();

            try {
                $senders->senderFor($delivery->channel)->send($delivery->loadMissing('notification'));
            } catch (Throwable $exception) {
                $this->markFailedAttempt($delivery, $exception);

                return;
            }

            $delivery->forceFill([
                'status' => NotificationStatus::Sent,
                'last_error' => null,
                'sent_at' => now(),
                'failed_at' => null,
            ])->save();
        });
    }

    private function markFailedAttempt(NotificationDelivery $delivery, Throwable $exception): void
    {
        $hasAttemptsLeft = $delivery->attempts < $delivery->max_attempts;
        $availableAt = $hasAttemptsLeft ? now()->addSeconds($this->retryDelay()) : null;

        $delivery->forceFill([
            'status' => $hasAttemptsLeft ? NotificationStatus::Processing : NotificationStatus::Error,
            'last_error' => $exception->getMessage(),
            'available_at' => $availableAt,
            'failed_at' => $hasAttemptsLeft ? null : now(),
        ])->save();

        if ($hasAttemptsLeft) {
            $this->release($this->retryDelay());
        }
    }

    private function retryDelay(): int
    {
        return 10;
    }
}
