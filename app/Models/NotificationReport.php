<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Database\Factories\NotificationReportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationReport extends Model
{
    /** @use HasFactory<NotificationReportFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'status',
        'period_from',
        'period_to',
        'file_path',
        'error_message',
        'completed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'period_from' => 'datetime',
            'period_to' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
