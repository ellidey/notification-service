<?php

namespace App\Http\Requests;

use App\Enums\ReportStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListNotificationReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::enum(ReportStatus::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
