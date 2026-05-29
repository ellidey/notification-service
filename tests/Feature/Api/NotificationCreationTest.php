<?php

namespace Tests\Feature\Api;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationDeliveryJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created(): void
    {
        Queue::fake();

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

        Queue::assertPushed(SendNotificationDeliveryJob::class, 2);
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
}
