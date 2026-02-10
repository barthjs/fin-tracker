<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationEventType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read string $id
 * @property string $notification_target_id
 * @property string $notifiable_type
 * @property string $notifiable_id
 * @property NotificationEventType $event_type
 * @property-read NotificationTarget $target
 * @property-read Model $notifiable
 */
final class NotificationAssignment extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $table = 'notification_assignments';

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'event_type' => NotificationEventType::class,
        ];
    }

    /**
     * @return BelongsTo<NotificationTarget, $this>
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(NotificationTarget::class, 'notification_target_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
