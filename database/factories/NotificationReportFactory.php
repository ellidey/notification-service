<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\NotificationReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationReport>
 */
class NotificationReportFactory extends Factory
{
    protected $model = NotificationReport::class;

    public function definition(): array
    {
        $periodTo = now();

        return [
            'user_id' => fake()->numberBetween(1, 10000),
            'status' => ReportStatus::Pending,
            'period_from' => $periodTo->copy()->subMonth(),
            'period_to' => $periodTo,
        ];
    }
}
