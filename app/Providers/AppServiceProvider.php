<?php

namespace App\Providers;

use App\Enums\NotificationChannel;
use App\Notifications\Delivery\EmailNotificationSender;
use App\Notifications\Delivery\NotificationSenderRegistry;
use App\Notifications\Delivery\TelegramNotificationSender;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationSenderRegistry::class, function (): NotificationSenderRegistry {
            return new NotificationSenderRegistry([
                NotificationChannel::Email->value => new EmailNotificationSender(),
                NotificationChannel::Telegram->value => new TelegramNotificationSender(),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
