<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\NotificationEventType;
use App\Models\NotificationAssignment;
use App\Models\NotificationTarget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Str;

/**
 * @mixin Model
 *
 * @property-read Collection<int, NotificationAssignment> $notificationAssignments
 */
trait HasNotificationAssignments
{
    public static function bootHasNotificationAssignments(): void
    {
        static::deleting(function (self $model): void {
            $model->notificationAssignments()->delete();
        });
    }

    /**
     * @return MorphMany<NotificationAssignment, $this>
     */
    public function notificationAssignments(): MorphMany
    {
        return $this->morphMany(NotificationAssignment::class, 'notifiable');
    }

    /**
     * @return \Illuminate\Support\Collection<int, NotificationTarget>
     */
    public function getNotificationTargetsForEvent(NotificationEventType $event): \Illuminate\Support\Collection
    {
        return NotificationTarget::query()
            ->where('is_active', true)
            ->whereIn('id', $this->notificationAssignments()
                ->where('event_type', $event->value)
                ->select('notification_target_id')
            )
            ->get();
    }

    /**
     *  Extracts and unsets notification target IDs from the provided data array.
     *
     * @param  array<string, mixed>  $data
     * @return array<string>
     */
    public function extractNotificationTargets(array &$data, string $key): array
    {
        /** @var array<string> $targets */
        $targets = $data[$key] ?? [];
        unset($data[$key]);

        return $targets;
    }

    /**
     * @param  array<string>  $targetIds
     */
    public function syncNotificationAssignments(NotificationEventType $event, array $targetIds): void
    {
        $this->notificationAssignments()
            ->where('event_type', $event)
            ->delete();

        if (empty($targetIds)) {
            return;
        }

        $assignments = [];

        foreach ($targetIds as $targetId) {
            $assignments[] = [
                'id' => Str::ulid()->toString(),
                'notification_target_id' => $targetId,
                'notifiable_id' => $this->getKey(),
                'notifiable_type' => $this->getMorphClass(),
                'event_type' => $event->value,
            ];
        }

        NotificationAssignment::insert($assignments);
    }
}
