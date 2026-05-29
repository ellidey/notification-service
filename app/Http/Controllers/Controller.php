<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Notification Service API',
    description: 'REST API для создания уведомлений, просмотра истории доставок и формирования отчётов.',
)]
#[OA\Server(url: 'http://localhost')]
abstract class Controller
{
}
