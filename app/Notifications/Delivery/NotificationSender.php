<?php

namespace App\Notifications\Delivery;

use App\Models\NotificationDelivery;

interface NotificationSender
{
    public function send(NotificationDelivery $delivery): void;
}
