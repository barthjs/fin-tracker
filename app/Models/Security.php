<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasDeletableFiles;
use App\Enums\SecurityType;
use App\Models\Scopes\UserScope;
use App\Observers\FileCleanupObserver;
use App\Observers\SecurityObserver;
use Carbon\CarbonInterface;
use Database\Factories\SecurityFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property string $name
 * @property string|null $isin
 * @property string|null $symbol
 * @property SecurityType $type
 * @property float $price
 * @property-read float $total_quantity
 * @property-read float $market_value
 * @property string|null $description
 * @property string|null $logo
 * @property string $color
 * @property bool $is_active
 * @property string $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $user
 * @property-read Collection<int, Trade> $trades
 */
#[ObservedBy([SecurityObserver::class, FileCleanupObserver::class])]
#[ScopedBy(UserScope::class)]
final class Security extends Model implements HasDeletableFiles
{
    /** @use HasFactory<SecurityFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => SecurityType::Stock->value,
        'price' => 0.0,
        'total_quantity' => 0.0,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'type' => SecurityType::class,
            'price' => 'float',
            'total_quantity' => 'float',
            'market_value' => 'float',
            'is_active' => 'bool',
        ];
    }

    /**
     * Owner of the security.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsToMany<Portfolio, $this>
     */
    public function portfolios(): BelongsToMany
    {
        return $this->belongsToMany(Portfolio::class, 'trades', 'security_id', 'portfolio_id')
            ->withoutGlobalScopes();
    }

    /**
     * Trades of this security.
     *
     * @return HasMany<Trade, $this>
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class, 'security_id');
    }

    /**
     * {@inheritDoc}
     */
    public function getFileFields(): array
    {
        return ['logo'];
    }

    public function getFileDisk(): string
    {
        return 'public';
    }
}
