<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeType;
use App\Models\Scopes\UserRelationScope;
use Carbon\CarbonInterface;
use Database\Factories\TradeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property-read string $id
 * @property CarbonInterface $date_time
 * @property TradeType $type
 * @property-read float $total_amount
 * @property float $quantity
 * @property float $price
 * @property float $tax
 * @property float $fee
 * @property string|null $notes
 * @property string $account_id
 * @property string $portfolio_id
 * @property string $security_id
 * @property-read Account $account
 * @property-read Portfolio $portfolio
 * @property-read Security $security
 * @property-read User $user
 */
final class Trade extends Model
{
    /** @use HasFactory<TradeFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => TradeType::Buy->value,
        'quantity' => 0.0,
        'price' => 0.0,
        'fee' => 0.0,
        'tax' => 0.0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'date_time' => 'datetime',
            'type' => TradeType::class,
            'total_amount' => 'float',
            'quantity' => 'float',
            'price' => 'float',
            'tax' => 'float',
            'fee' => 'float',
        ];
    }

    /**
     * Account this trade belongs to.
     *
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Portfolio this trade belongs to.
     *
     * @return BelongsTo<Portfolio, $this>
     */
    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class, 'portfolio_id');
    }

    /**
     * Security this trade belongs to.
     *
     * @return BelongsTo<Security, $this>
     */
    public function security(): BelongsTo
    {
        return $this->belongsTo(Security::class, 'security_id');
    }

    /**
     * Owner of this trade.
     *
     * @return HasOneThrough<User, Account, $this>
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, Account::class);
    }

    protected static function booted(): void
    {
        self::addGlobalScope(new UserRelationScope());

        self::creating(function (Trade $trade): void {
            // Only needed in importer
            /** @phpstan-ignore-next-line */
            if ($trade->account_id === null) {
                $trade->account_id = Account::getOrCreateDefaultAccount()->id;
            }

            // Only needed in importer
            /** @phpstan-ignore-next-line */
            if ($trade->portfolio_id === null) {
                $trade->portfolio_id = Portfolio::getOrCreateDefaultPortfolio()->id;
            }

            // Only needed in importer
            /** @phpstan-ignore-next-line */
            if ($trade->security_id === null) {
                $trade->security_id = Security::getOrCreateDefaultSecurity()->id;
            }
        });
    }
}
