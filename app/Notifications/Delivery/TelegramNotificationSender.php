<?php

namespace App\Notifications\Delivery;

use App\Models\NotificationDelivery;

class TelegramNotificationSender implements NotificationSender
{
    public function send(NotificationDelivery $delivery): void
    {
    }
}
