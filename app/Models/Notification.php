<?php

namespace App\Models;

use App\Models\Builders\NotificationBuilder;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'message',
    ];

    public function newEloquentBuilder($query): NotificationBuilder
    {
        /** @var Builder<Notification> $query */
        return new NotificationBuilder($query);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }
}
