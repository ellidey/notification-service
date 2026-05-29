<?php

namespace App\Exports;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class NotificationReportExport implements FromCollection
{
    public function __construct(
        private readonly int $userId,
        private readonly CarbonImmutable $periodFrom,
        private readonly CarbonImmutable $periodTo,
    ) {
    }

    public function collection(): Collection
    {
        $notifications = Notification::query()
            ->forUser($this->userId)
            ->whereBetween('created_at', [$this->periodFrom, $this->periodTo])
            ->with('deliveries')
            ->oldest()
            ->get();

        $deliveries = $notifications->flatMap->deliveries;

        return collect([
            ['Показатель', 'Значение'],
            ['ID пользователя', $this->userId],
            ['Период с', $this->formatDate($this->periodFrom)],
            ['Период по', $this->formatDate($this->periodTo)],
            ['Всего уведомлений', $notifications->count()],
            ['Всего доставок', $deliveries->count()],
            ['Успешных доставок', $deliveries->where('status', NotificationStatus::Sent)->count()],
            ['Доставок в обработке', $deliveries->where('status', NotificationStatus::Processing)->count()],
            ['Ошибок доставки', $deliveries->where('status', NotificationStatus::Error)->count()],
            [],
            ['ID уведомления', 'Дата создания', 'Канал', 'Статус', 'Попытки', 'Последняя ошибка'],
        ])->merge(
            $notifications->flatMap(fn (Notification $notification): Collection => $notification->deliveries->map(
                fn ($delivery): array => [
                    $notification->id,
                    $this->formatDate($notification->created_at),
                    $delivery->channel->value,
                    $delivery->status->value,
                    $delivery->attempts,
                    $delivery->last_error,
                ],
            )),
        );
    }

    private function formatDate(?CarbonInterface $date): ?string
    {
        return $date?->timezone(config('app.timezone'))->format('d.m.Y H:i:s');
    }
}
