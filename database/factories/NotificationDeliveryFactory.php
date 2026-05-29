<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
{
    protected $model = NotificationDelivery::class;

    public function definition(): array
    {
        return [
            'notification_id' => Notification::factory(),
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'status' => NotificationStatus::Processing,
            'attempts' => 0,
            'max_attempts' => 3,
            'available_at' => now(),
        ];
    }
}
