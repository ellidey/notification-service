<?php

namespace Tests\Feature\Api;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_status_is_aggregated_from_deliveries(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => 1001,
        ]);

        $notification->deliveries()->createMany([
            [
                'channel' => NotificationChannel::Email,
                'status' => NotificationStatus::Sent,
                'attempts' => 1,
                'sent_at' => now(),
            ],
            [
                'channel' => NotificationChannel::Telegram,
                'status' => NotificationStatus::Sent,
                'attempts' => 1,
                'sent_at' => now(),
            ],
        ]);

        $this->getJson("/api/notifications/{$notification->id}")
            ->assertOk()
            ->assertJsonPath('data.status', NotificationStatus::Sent->value)
            ->assertJsonCount(2, 'data.deliveries');
    }

    public function test_user_notification_history_can_be_filtered_by_channel_and_status(): void
    {
        $matchedNotification = Notification::factory()->create([
            'user_id' => 1001,
        ]);

        $matchedNotification->deliveries()->createMany([
            [
                'channel' => NotificationChannel::Email,
                'status' => NotificationStatus::Sent,
                'attempts' => 1,
            ],
            [
                'channel' => NotificationChannel::Telegram,
                'status' => NotificationStatus::Error,
                'attempts' => 3,
                'last_error' => 'Chat not found',
                'failed_at' => now(),
            ],
        ]);

        $processingNotification = Notification::factory()->create([
            'user_id' => 1001,
        ]);

        $processingNotification->deliveries()->create([
            'channel' => NotificationChannel::Telegram,
            'status' => NotificationStatus::Processing,
            'attempts' => 1,
        ]);

        $otherUserNotification = Notification::factory()->create([
            'user_id' => 1002,
        ]);

        $otherUserNotification->deliveries()->create([
            'channel' => NotificationChannel::Telegram,
            'status' => NotificationStatus::Error,
            'attempts' => 3,
        ]);

        $this->getJson('/api/users/1001/notifications?channel=telegram&status=error')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchedNotification->id)
            ->assertJsonCount(1, 'data.0.deliveries')
            ->assertJsonPath('data.0.deliveries.0.channel', NotificationChannel::Telegram->value)
            ->assertJsonPath('data.0.deliveries.0.status', NotificationStatus::Error->value);
    }
}
