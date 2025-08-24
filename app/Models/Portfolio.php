<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use App\Enums\TradeType;
use App\Models\Scopes\UserScope;
use Carbon\CarbonInterface;
use Database\Factories\PortfolioFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
final class Portfolio extends Model
{
    /** @use HasFactory<PortfolioFactory> */
    use HasFactory, HasUlids;

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
     * Retrieve or create the default portfolio for the current user.
     *
     * Attempts to find a portfolio named 'Demo' for the authenticated user.
     * If no such portfolio exists, a new one is created with that name
     * and a randomly generated color.
     */
    public static function getOrCreateDefaultPortfolio(?User $user = null): self
    {
        $user ??= auth()->user();

        return self::where('user_id', $user->id)->where('name', 'Demo')->first() ??
            self::create([
                'name' => 'Demo',
                'currency' => Currency::getCurrency(),
                'color' => mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => $user->id,
            ]);
    }

    /**
     * Recalculate and update the market value for the portfolio.
     *
     * The market value is derived from all trades in the portfolio:
     * - BUY trades increase position (positive quantity)
     * - SELL trades decrease position (negative quantity)
     * - Market value is calculated as 'sum(quantity * current security price)'
     */
    public static function updatePortfolioMarketValue(string $portfolioId): void
    {
        // Aggregate quantities per security
        $quantities = Trade::where('portfolio_id', $portfolioId)
            ->selectRaw(
                'security_id, SUM(CASE WHEN type = ? THEN quantity ELSE -quantity END) as total_quantity',
                [TradeType::Buy->value]
            )
            ->groupBy(['security_id'])
            ->get();

        if ($quantities->isEmpty()) {
            self::whereKey($portfolioId)->update(['market_value' => 0.0]);

            return;
        }

        $securities = Security::whereIn('id', $quantities->pluck('security_id')->all())
            ->get()
            ->keyBy('id');

        $marketValue = 0.0;

        /** @var Collection<int, Trade> $quantities */
        foreach ($quantities as $item) {
            /** @var Security $security */
            $security = $securities->get($item->security_id);
            /** @var string $totalQuantity */
            $totalQuantity = $item['total_quantity'];
            $marketValue += (float) $totalQuantity * $security->price;
        }

        self::whereKey($portfolioId)->update(['market_value' => $marketValue]);
    }

    /**
     * Returns the sum of all active portfolios market values.
     */
    public static function getActiveMarketValueSum(): float
    {
        return (float) self::where('is_active', true)->sum('market_value');
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

    protected static function booted(): void
    {
        self::addGlobalScope(new UserScope);

        self::creating(function (Portfolio $portfolio): void {
            $portfolio->name = mb_trim($portfolio->name);

            if ($portfolio->user_id === null) {
                $portfolio->user_id = auth()->user()->id;
            }
        });

        self::updating(function (Portfolio $portfolio): void {
            $portfolio->name = mb_trim($portfolio->name);
        });

        self::updated(function (Portfolio $portfolio): void {
            /** @var string|null $originalLogo */
            $originalLogo = $portfolio->getOriginal('logo');

            if ($originalLogo !== null && $originalLogo !== $portfolio->logo) {
                if (Storage::disk('public')->exists($originalLogo)) {
                    Storage::disk('public')->delete($originalLogo);
                }
            }
        });

        self::deleted(function (Portfolio $portfolio): void {
            if ($portfolio->logo !== null && Storage::disk('public')->exists($portfolio->logo)) {
                Storage::disk('public')->delete($portfolio->logo);
            }
        });
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
