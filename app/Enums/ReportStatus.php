<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReportStatus',
    type: 'string',
    enum: ['pending', 'processing', 'ready', 'error'],
)]
enum ReportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Error = 'error';
}
