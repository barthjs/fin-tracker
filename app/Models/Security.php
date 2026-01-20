<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasDeletableFiles;
use App\Enums\SecurityType;
use App\Enums\TradeType;
use App\Models\Scopes\UserScope;
use App\Observers\FileCleanupObserver;
use Carbon\CarbonInterface;
use Database\Factories\SecurityFactory;
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
final class Security extends Model implements HasDeletableFiles
{
    /** @use HasFactory<SecurityFactory> */
    use HasFactory, HasUlids;

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
     * Retrieve or create the default security for the current user.
     *
     * Attempts to find a security named 'Demo' for the authenticated user.
     * If no such security exists, a new one is created with that name
     * and a randomly generated color.
     */
    public static function getOrCreateDefaultSecurity(?User $user = null): self
    {
        $user ??= auth()->user();

        return self::where('user_id', $user->id)->where('name', 'Demo')->first() ??
            self::create([
                'name' => 'Demo',
                'color' => mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => $user->id,
            ]);
    }

    /**
     * Recalculate and update the total quantity for the security.
     */
    public static function updateSecurityQuantity(string $securityId): void
    {
        $security = self::find($securityId);
        if (! $security) {
            return;
        }

        $buys = (float) Trade::where('security_id', $securityId)
            ->where('type', TradeType::Buy)
            ->sum('quantity');

        $sells = (float) Trade::where('security_id', $securityId)
            ->where('type', TradeType::Sell)
            ->sum('quantity');

        $totalQuantity = $buys - $sells;

        $security->update(['total_quantity' => $totalQuantity]);
    }

    /**
     * Get the attributes that should be cast.
     *
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

    protected static function booted(): void
    {
        self::addGlobalScope(new UserScope);

        self::creating(function (Security $security): void {
            self::trimFields($security);

            /** @phpstan-ignore-next-line */
            if ($security->user_id === null) {
                $security->user_id = auth()->user()->id;
            }
        });

        self::updating(function (Security $security): void {
            self::trimFields($security);
        });

        self::observe(FileCleanupObserver::class);
    }

    private static function trimFields(self $security): void
    {
        $security->name = mb_trim($security->name);
        $security->isin = $security->isin === null ? null : mb_trim($security->isin);
        $security->symbol = $security->symbol === null ? null : mb_trim($security->symbol);
        $security->description = $security->description === null ? null : mb_trim($security->description);
    }
}
