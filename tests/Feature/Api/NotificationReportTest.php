<?php

namespace Tests\Feature\Api;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\ReportStatus;
use App\Jobs\GenerateNotificationReportJob;
use App\Models\Notification;
use App\Models\NotificationReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_report_can_be_queued(): void
    {
        Queue::fake();

        $periodFrom = now()->subDays(3);
        $periodTo = now()->endOfDay();

        $response = $this->postJson('/api/users/1001/notifications/reports', [
            'period_from' => $periodFrom->toISOString(),
            'period_to' => $periodTo->toISOString(),
        ]);

        $response
            ->assertAccepted()
            ->assertJsonPath('data.user_id', 1001)
            ->assertJsonPath('data.status', ReportStatus::Pending->value)
            ->assertJsonPath('data.file_path', null)
            ->assertJsonPath('data.error_message', null);

        Queue::assertPushed(GenerateNotificationReportJob::class, 1);
    }

    public function test_notification_report_job_generates_report_file(): void
    {
        Storage::fake('local');

        $periodFrom = now()->subDays(3);
        $periodTo = now()->endOfDay();

        $notification = Notification::factory()->create([
            'user_id' => 1001,
            'created_at' => now()->subDay(),
        ]);

        $notification->deliveries()->createMany([
            [
                'channel' => NotificationChannel::Email,
                'status' => NotificationStatus::Sent,
                'attempts' => 1,
                'sent_at' => now(),
            ],
            [
                'channel' => NotificationChannel::Telegram,
                'status' => NotificationStatus::Error,
                'attempts' => 3,
                'last_error' => 'Chat not found',
                'failed_at' => now(),
            ],
        ]);

        Notification::factory()->create([
            'user_id' => 1002,
            'created_at' => now()->subDay(),
        ])->deliveries()->create([
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Sent,
            'attempts' => 1,
        ]);

        $report = NotificationReport::factory()->create([
            'user_id' => 1001,
            'status' => ReportStatus::Pending,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
        ]);

        app(GenerateNotificationReportJob::class, ['reportId' => $report->id])->handle();

        $report->refresh();

        $this->assertSame(ReportStatus::Ready, $report->status);
        $this->assertNull($report->error_message);
        $this->assertNotNull($report->completed_at);

        $filePath = $report->file_path;

        Storage::disk('local')->assertExists($filePath);

        $contents = Storage::disk('local')->get($filePath);

        $this->assertStringContainsString('"Всего уведомлений","1"', $contents);
        $this->assertStringContainsString('"Всего доставок","2"', $contents);
        $this->assertStringContainsString('"Успешных доставок","1"', $contents);
        $this->assertStringContainsString('"Ошибок доставки","1"', $contents);
        $this->assertStringContainsString($notification->created_at->format('d.m.Y H:i:s'), $contents);
        $this->assertStringContainsString($notification->id, $contents);
    }

    public function test_notification_report_creation_requires_valid_period(): void
    {
        $response = $this->postJson('/api/users/1001/notifications/reports', [
            'period_from' => now()->toISOString(),
            'period_to' => now()->subDay()->toISOString(),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['period_to']);
    }

    public function test_user_notification_reports_can_be_filtered_by_status(): void
    {
        $readyReport = NotificationReport::factory()->create([
            'user_id' => 1001,
            'status' => ReportStatus::Ready,
            'completed_at' => now(),
        ]);

        NotificationReport::factory()->create([
            'user_id' => 1001,
            'status' => ReportStatus::Processing,
        ]);

        NotificationReport::factory()->create([
            'user_id' => 1002,
            'status' => ReportStatus::Ready,
            'completed_at' => now(),
        ]);

        $this->getJson('/api/users/1001/notifications/reports?status=ready')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $readyReport->id)
            ->assertJsonPath('data.0.status', ReportStatus::Ready->value);
    }

    public function test_notification_report_can_be_shown(): void
    {
        $report = NotificationReport::factory()->create([
            'user_id' => 1001,
            'status' => ReportStatus::Ready,
            'file_path' => 'reports/user-1001/report.csv',
            'completed_at' => now(),
        ]);

        $this->getJson("/api/notifications/reports/{$report->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $report->id)
            ->assertJsonPath('data.user_id', 1001)
            ->assertJsonPath('data.status', ReportStatus::Ready->value)
            ->assertJsonPath('data.file_path', 'reports/user-1001/report.csv');
    }

    public function test_ready_notification_report_can_be_downloaded(): void
    {
        Storage::fake('local');

        $report = NotificationReport::factory()->create([
            'user_id' => 1001,
            'status' => ReportStatus::Ready,
            'file_path' => 'reports/user-1001/report.csv',
            'completed_at' => now(),
        ]);

        Storage::disk('local')->put($report->file_path, 'report contents');

        $this->get("/api/notifications/reports/{$report->id}/download")
            ->assertOk()
            ->assertDownload("notification-report-{$report->id}.csv");
    }

    public function test_pending_notification_report_cannot_be_downloaded(): void
    {
        $report = NotificationReport::factory()->create([
            'user_id' => 1001,
            'status' => ReportStatus::Pending,
        ]);

        $this->getJson("/api/notifications/reports/{$report->id}/download")
            ->assertStatus(409)
            ->assertJsonPath('message', 'Отчёт ещё не готов к скачиванию.');
    }
}
