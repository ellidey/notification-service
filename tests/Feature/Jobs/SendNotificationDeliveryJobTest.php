<?php

namespace Tests\Feature\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationDeliveryJob;
use App\Models\NotificationDelivery;
use App\Notifications\Delivery\NotificationSender;
use App\Notifications\Delivery\NotificationSenderRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SendNotificationDeliveryJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_job_marks_delivery_as_sent(): void
    {
        $delivery = NotificationDelivery::factory()->create([
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Processing,
            'attempts' => 0,
            'max_attempts' => 3,
        ]);

        app(SendNotificationDeliveryJob::class, ['deliveryId' => $delivery->id])
            ->handle(app(NotificationSenderRegistry::class));

        $this->assertDatabaseHas('notification_deliveries', [
            'id' => $delivery->id,
            'status' => NotificationStatus::Sent->value,
            'attempts' => 1,
            'last_error' => null,
        ]);

        $this->assertNotNull($delivery->refresh()->sent_at);
    }

    public function test_delivery_job_saves_error_and_keeps_delivery_for_retry(): void
    {
        $delivery = NotificationDelivery::factory()->create([
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Processing,
            'attempts' => 0,
            'max_attempts' => 2,
        ]);

        $this->instance(NotificationSenderRegistry::class, $this->failingSenderRegistry());

        app(SendNotificationDeliveryJob::class, ['deliveryId' => $delivery->id])
            ->handle(app(NotificationSenderRegistry::class));

        $delivery->refresh();

        $this->assertSame(NotificationStatus::Processing, $delivery->status);
        $this->assertSame(1, $delivery->attempts);
        $this->assertSame('Delivery failed', $delivery->last_error);
        $this->assertNotNull($delivery->available_at);
        $this->assertNull($delivery->failed_at);
    }

    public function test_delivery_job_marks_delivery_as_error_after_max_attempts(): void
    {
        $delivery = NotificationDelivery::factory()->create([
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Processing,
            'attempts' => 1,
            'max_attempts' => 2,
        ]);

        $this->instance(NotificationSenderRegistry::class, $this->failingSenderRegistry());

        app(SendNotificationDeliveryJob::class, ['deliveryId' => $delivery->id])
            ->handle(app(NotificationSenderRegistry::class));

        $delivery->refresh();

        $this->assertSame(NotificationStatus::Error, $delivery->status);
        $this->assertSame(2, $delivery->attempts);
        $this->assertSame('Delivery failed', $delivery->last_error);
        $this->assertNull($delivery->available_at);
        $this->assertNotNull($delivery->failed_at);
    }

    private function failingSenderRegistry(): NotificationSenderRegistry
    {
        return new NotificationSenderRegistry([
            NotificationChannel::Email->value => new class () implements NotificationSender {
                public function send(NotificationDelivery $delivery): void
                {
                    throw new RuntimeException('Delivery failed');
                }
            },
        ]);
    }
}
