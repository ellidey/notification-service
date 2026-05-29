<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationChannel',
    type: 'string',
    enum: ['email', 'telegram'],
)]
enum NotificationChannel: string
{
    case Email = 'email';
    case Telegram = 'telegram';
}
