<?php

namespace App\Notifications\Delivery;

use App\Models\NotificationDelivery;

class EmailNotificationSender implements NotificationSender
{
    public function send(NotificationDelivery $delivery): void
    {
    }
}
