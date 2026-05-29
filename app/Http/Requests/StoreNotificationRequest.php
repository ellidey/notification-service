<?php

namespace App\Http\Requests;

use App\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateNotificationRequest',
    required: ['user_id', 'message', 'channels'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', minimum: 1, example: 1001),
        new OA\Property(property: 'message', type: 'string', maxLength: 500, example: 'Test notification'),
        new OA\Property(
            property: 'channels',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/NotificationChannel'),
            example: ['email', 'telegram'],
        ),
    ],
    type: 'object',
)]
class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'min:1'],
            'message' => ['required', 'string', 'max:500'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['required', 'string', 'distinct', Rule::enum(NotificationChannel::class)],
        ];
    }
}
