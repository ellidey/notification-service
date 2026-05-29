<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Notification Service API',
    description: 'REST API for creating notifications and viewing delivery history.',
)]
#[OA\Server(url: 'http://localhost')]
abstract class Controller
{
}
