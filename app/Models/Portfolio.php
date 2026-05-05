<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasDeletableFiles;
use App\Enums\Currency;
use App\Models\Scopes\UserScope;
use App\Observers\FileCleanupObserver;
use App\Observers\PortfolioObserver;
use Carbon\CarbonInterface;
use Database\Factories\PortfolioFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property-read float $market_value
 * @property Currency $currency
 * @property string|null $description
 * @property string|null $logo
 * @property string $color
 * @property bool $is_active
 * @property string $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $user
 * @property-read Collection<int, Trade> $trades
 * @property-read string $marketValueColor
 */
#[ObservedBy([PortfolioObserver::class, FileCleanupObserver::class])]
#[ScopedBy(UserScope::class)]
final class Portfolio extends Model implements HasDeletableFiles
{
    /** @use HasFactory<PortfolioFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'market_value' => 0.0,
        'is_active' => true,
    ];

    /**
     * Returns the sum of all active portfolios market values.
     */
    public static function getActiveMarketValueSum(): float
    {
        return (float) self::query()->where('is_active', true)->sum('market_value');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'currency' => Currency::class,
            'market_value' => 'float',
            'is_active' => 'bool',
        ];
    }

    /**
     * Owner of the portfolio.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsToMany<Security, $this>
     */
    public function securities(): BelongsToMany
    {
        return $this->belongsToMany(Security::class, 'trades', 'portfolio_id', 'security_id')
            ->withoutGlobalScopes();
    }

    /**
     * Trades of this portfolio.
     *
     * @return HasMany<Trade, $this>
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class, 'portfolio_id');
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

    /**
     * @return Attribute<string, never>
     */
    protected function marketValueColor(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match (true) {
                $this->market_value === 0.0 => 'gray',
                $this->market_value < 0 => 'danger',
                default => 'success'
            }
        );
    }
}
