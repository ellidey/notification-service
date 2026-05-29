<?php

namespace Database\Seeders;

use App\Enums\ReportStatus;
use App\Models\NotificationReport;
use Illuminate\Database\Seeder;

class NotificationReportSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $reports = [
            [
                'user_id' => 1001,
                'status' => ReportStatus::Ready,
                'period_from' => $now->copy()->subDays(30)->startOfDay(),
                'period_to' => $now->copy()->endOfDay(),
                'file_path' => 'reports/user-1001/current-month.csv',
                'completed_at' => $now->copy()->subMinutes(30),
            ],
            [
                'user_id' => 1002,
                'status' => ReportStatus::Processing,
                'period_from' => $now->copy()->subDays(14)->startOfDay(),
                'period_to' => $now->copy()->endOfDay(),
            ],
            [
                'user_id' => 1003,
                'status' => ReportStatus::Error,
                'period_from' => $now->copy()->subDays(30)->startOfDay(),
                'period_to' => $now->copy()->endOfDay(),
                'error_message' => 'Report storage write failed',
                'failed_at' => $now->copy()->subHours(2),
            ],
        ];

        foreach ($reports as $report) {
            NotificationReport::query()->create($report);
        }
    }
}
