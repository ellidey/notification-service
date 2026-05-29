<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateNotificationReportRequest',
    required: ['period_from', 'period_to'],
    properties: [
        new OA\Property(property: 'period_from', type: 'string', format: 'date-time', example: '2026-05-01T00:00:00Z'),
        new OA\Property(property: 'period_to', type: 'string', format: 'date-time', example: '2026-05-31T23:59:59Z'),
    ],
    type: 'object',
)]
class CreateNotificationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
        ];
    }
}
