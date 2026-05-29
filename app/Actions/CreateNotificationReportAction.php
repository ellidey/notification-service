<?php

namespace App\Actions;

use App\Enums\ReportStatus;
use App\Jobs\GenerateNotificationReportJob;
use App\Models\NotificationReport;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CreateNotificationReportAction
{
    public function handle(int $userId, CarbonImmutable $periodFrom, CarbonImmutable $periodTo): NotificationReport
    {
        $report = NotificationReport::query()->create([
            'user_id' => $userId,
            'status' => ReportStatus::Pending,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
        ]);

        DB::afterCommit(function () use ($report): void {
            GenerateNotificationReportJob::dispatch($report->id);
        });

        return $report;
    }
}
