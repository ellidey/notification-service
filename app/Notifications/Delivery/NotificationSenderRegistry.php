<?php

namespace App\Notifications\Delivery;

use App\Enums\NotificationChannel;
use InvalidArgumentException;

class NotificationSenderRegistry
{
    /**
     * @param  array<string, NotificationSender>  $senders
     */
    public function __construct(private readonly array $senders)
    {
    }

    public function senderFor(NotificationChannel $channel): NotificationSender
    {
        return $this->senders[$channel->value]
            ?? throw new InvalidArgumentException("Notification sender [{$channel->value}] is not registered.");
    }
}
