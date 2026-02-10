<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationProviderType;
use App\Models\Scopes\UserScope;
use App\Services\Notifications\Configs\NotificationConfig;
use App\Services\Notifications\NotificationConfigFactory;
use Carbon\CarbonImmutable;
use Database\Factories\NotificationTargetFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property string $user_id
 * @property string $name
 * @property NotificationProviderType $type
 * @property array<string, mixed> $configuration
 * @property bool $is_default
 * @property bool $is_active
 * @property-read User $user
 * @property-read Collection<int, NotificationAssignment> $assignments
 */
final class NotificationTarget extends Model
{
    /** @use HasFactory<NotificationTargetFactory> */
    use HasFactory, HasUlids;

    protected $table = 'notification_targets';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_default' => false,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'type' => NotificationProviderType::class,
            'configuration' => 'encrypted:array',
            'is_default' => 'bool',
            'is_active' => 'bool',
        ];
    }

    public function getConfig(): NotificationConfig
    {
        return NotificationConfigFactory::make($this->type, $this->configuration);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<NotificationAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(NotificationAssignment::class);
    }

    protected static function booted(): void
    {
        self::addGlobalScope(new UserScope());

        self::creating(function (self $model): void {
            /** @phpstan-ignore-next-line */
            if ($model->user_id === null) {
                $model->user_id = auth()->user()->id;
            }
        });
    }
}
