<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Carbon\CarbonInterface;
use Database\Factories\NotificationReportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $user_id
 * @property ReportStatus $status
 * @property CarbonInterface $period_from
 * @property CarbonInterface $period_to
 * @property string|null $file_path
 * @property string|null $error_message
 * @property CarbonInterface|null $completed_at
 * @property CarbonInterface|null $failed_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
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
