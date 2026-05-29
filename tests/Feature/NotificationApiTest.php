<?php

namespace Tests\Feature;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        $response = $this->postJson('/api/notifications', [
            'user_id' => 1001,
            'message' => 'Test notification',
            'channels' => [
                NotificationChannel::Email->value,
                NotificationChannel::Telegram->value,
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user_id', 1001)
            ->assertJsonPath('data.message', 'Test notification')
            ->assertJsonPath('data.status', NotificationStatus::Processing->value)
            ->assertJsonCount(2, 'data.deliveries');

        $this->assertDatabaseHas('notifications', [
            'user_id' => 1001,
            'message' => 'Test notification',
        ]);

        $this->assertDatabaseCount('notification_deliveries', 2);
    }

    public function test_notification_creation_requires_valid_payload(): void
    {
        $response = $this->postJson('/api/notifications', [
            'user_id' => 0,
            'message' => str_repeat('a', 501),
            'channels' => [
                'sms',
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'user_id',
                'message',
                'channels.0',
            ]);
    }

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
