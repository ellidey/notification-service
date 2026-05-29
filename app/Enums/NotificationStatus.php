<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationStatus',
    type: 'string',
    enum: ['processing', 'sent', 'error'],
)]
enum NotificationStatus: string
{
    case Processing = 'processing';
    case Sent = 'sent';
    case Error = 'error';
}
