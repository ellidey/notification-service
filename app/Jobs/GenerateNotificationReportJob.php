<?php

namespace App\Jobs;

use App\Enums\ReportStatus;
use App\Exports\NotificationReportExport;
use App\Models\NotificationReport;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use RuntimeException;
use Throwable;

class GenerateNotificationReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $reportId)
    {
        $this->onQueue('reports');
    }

    public function handle(): void
    {
        $report = NotificationReport::query()->findOrFail($this->reportId);

        if ($report->status === ReportStatus::Ready) {
            return;
        }

        $report->forceFill([
            'status' => ReportStatus::Processing,
            'error_message' => null,
            'failed_at' => null,
        ])->save();

        try {
            $filePath = sprintf('reports/%s.csv', $report->id);

            $stored = Excel::store(
                new NotificationReportExport(
                    userId: $report->user_id,
                    periodFrom: CarbonImmutable::parse($report->period_from),
                    periodTo: CarbonImmutable::parse($report->period_to),
                ),
                $filePath,
                'local',
                ExcelWriter::CSV,
            );

            if ($stored !== true) {
                throw new RuntimeException('Report file could not be written.');
            }

            $report->forceFill([
                'status' => ReportStatus::Ready,
                'file_path' => $filePath,
                'completed_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $report->forceFill([
                'status' => ReportStatus::Error,
                'error_message' => $exception->getMessage(),
                'failed_at' => now(),
            ])->save();
        }
    }
}
