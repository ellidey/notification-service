<?php

namespace App\Actions;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class CreateNotificationAction
{
    public function handle(int $userId, string $message, array $channels): Notification
    {
        return DB::transaction(function () use ($userId, $message, $channels): Notification {
            $notification = Notification::query()->create([
                'user_id' => $userId,
                'message' => $message,
            ]);

            foreach (array_unique($channels) as $channel) {
                $notification->deliveries()->create([
                    'channel' => NotificationChannel::from($channel),
                    'status' => NotificationStatus::Processing,
                    'attempts' => 0,
                    'max_attempts' => 3,
                    'available_at' => now(),
                ]);
            }

            return $notification->load('deliveries');
        });
    }
}
