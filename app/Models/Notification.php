<?php

namespace App\Models;

use App\Models\Builders\NotificationBuilder;
use Carbon\CarbonInterface;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

/**
 * @property string $id
 * @property int $user_id
 * @property string $message
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Collection<int, NotificationDelivery> $deliveries
 */
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
        /** @var Builder $query */
        return new NotificationBuilder($query);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }
}
