<?php

namespace Database\Seeders;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->notifications() as $notificationData) {
            $notification = Notification::query()->create([
                'user_id' => $notificationData['user_id'],
                'message' => $notificationData['message'],
                'created_at' => $notificationData['created_at'],
                'updated_at' => $notificationData['created_at'],
            ]);

            foreach ($notificationData['deliveries'] as $deliveryData) {
                $notification->deliveries()->create([
                    'channel' => $deliveryData['channel'],
                    'status' => $deliveryData['status'],
                    'attempts' => $deliveryData['attempts'],
                    'max_attempts' => 3,
                    'last_error' => $deliveryData['last_error'] ?? null,
                    'available_at' => $deliveryData['available_at'] ?? null,
                    'sent_at' => $deliveryData['sent_at'] ?? null,
                    'failed_at' => $deliveryData['failed_at'] ?? null,
                    'created_at' => $notificationData['created_at'],
                    'updated_at' => $deliveryData['updated_at'] ?? $notificationData['created_at'],
                ]);
            }
        }
    }

    private function notifications(): array
    {
        $now = now();

        return [
            [
                'user_id' => 1001,
                'message' => 'Добро пожаловать в сервис уведомлений.',
                'created_at' => $now->copy()->subDays(14),
                'deliveries' => [
                    [
                        'channel' => NotificationChannel::Email,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 1,
                        'sent_at' => $now->copy()->subDays(14)->addMinute(),
                    ],
                    [
                        'channel' => NotificationChannel::Telegram,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 1,
                        'sent_at' => $now->copy()->subDays(14)->addMinutes(2),
                    ],
                ],
            ],
            [
                'user_id' => 1001,
                'message' => 'Ваш еженедельный отчёт готов к скачиванию.',
                'created_at' => $now->copy()->subDays(10),
                'deliveries' => [
                    [
                        'channel' => NotificationChannel::Email,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 1,
                        'sent_at' => $now->copy()->subDays(10)->addMinutes(3),
                    ],
                    [
                        'channel' => NotificationChannel::Telegram,
                        'status' => NotificationStatus::Error,
                        'attempts' => 3,
                        'last_error' => 'Telegram API timeout',
                        'failed_at' => $now->copy()->subDays(10)->addMinutes(15),
                    ],
                ],
            ],
            [
                'user_id' => 1001,
                'message' => 'Попытка входа с нового устройства.',
                'created_at' => $now->copy()->subDays(5),
                'deliveries' => [
                    [
                        'channel' => NotificationChannel::Email,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 2,
                        'sent_at' => $now->copy()->subDays(5)->addMinutes(7),
                    ],
                    [
                        'channel' => NotificationChannel::Telegram,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 1,
                        'sent_at' => $now->copy()->subDays(5)->addMinute(),
                    ],
                ],
            ],
            [
                'user_id' => 1001,
                'message' => 'Плановое техническое обслуживание начнётся сегодня в 23:00.',
                'created_at' => $now->copy()->subDay(),
                'deliveries' => [
                    [
                        'channel' => NotificationChannel::Email,
                        'status' => NotificationStatus::Processing,
                        'attempts' => 1,
                        'available_at' => $now->copy()->addMinutes(10),
                        'last_error' => 'SMTP temporary unavailable',
                    ],
                    [
                        'channel' => NotificationChannel::Telegram,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 1,
                        'sent_at' => $now->copy()->subDay()->addMinutes(2),
                    ],
                ],
            ],
            [
                'user_id' => 1002,
                'message' => 'Срок действия подписки истекает через 3 дня.',
                'created_at' => $now->copy()->subDays(8),
                'deliveries' => [
                    [
                        'channel' => NotificationChannel::Email,
                        'status' => NotificationStatus::Error,
                        'attempts' => 3,
                        'last_error' => 'Mailbox unavailable',
                        'failed_at' => $now->copy()->subDays(8)->addMinutes(20),
                    ],
                    [
                        'channel' => NotificationChannel::Telegram,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 1,
                        'sent_at' => $now->copy()->subDays(8)->addMinutes(2),
                    ],
                ],
            ],
            [
                'user_id' => 1002,
                'message' => 'Ваш платёж успешно обработан.',
                'created_at' => $now->copy()->subDays(2),
                'deliveries' => [
                    [
                        'channel' => NotificationChannel::Email,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 1,
                        'sent_at' => $now->copy()->subDays(2)->addMinute(),
                    ],
                    [
                        'channel' => NotificationChannel::Telegram,
                        'status' => NotificationStatus::Processing,
                        'attempts' => 2,
                        'available_at' => $now->copy()->addMinutes(5),
                        'last_error' => 'Rate limit exceeded',
                    ],
                ],
            ],
            [
                'user_id' => 1003,
                'message' => 'Новая функция доступна в вашем личном кабинете.',
                'created_at' => $now->copy()->subDays(20),
                'deliveries' => [
                    [
                        'channel' => NotificationChannel::Email,
                        'status' => NotificationStatus::Sent,
                        'attempts' => 1,
                        'sent_at' => $now->copy()->subDays(20)->addMinutes(4),
                    ],
                ],
            ],
            [
                'user_id' => 1003,
                'message' => 'Не удалось сформировать выгрузку данных. Мы повторим попытку автоматически.',
                'created_at' => $now->copy()->subHours(6),
                'deliveries' => [
                    [
                        'channel' => NotificationChannel::Email,
                        'status' => NotificationStatus::Processing,
                        'attempts' => 2,
                        'available_at' => $now->copy()->addMinutes(15),
                        'last_error' => 'Provider returned 503',
                    ],
                    [
                        'channel' => NotificationChannel::Telegram,
                        'status' => NotificationStatus::Error,
                        'attempts' => 3,
                        'last_error' => 'Chat not found',
                        'failed_at' => $now->copy()->subHours(5),
                    ],
                ],
            ],
        ];
    }
}
