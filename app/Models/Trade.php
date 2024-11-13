<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\TradeType;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    use HasFactory;

    public $table = 'trades';
    public $timestamps = false;

    protected $fillable = [
        'date_time',
        'total_amount',
        'quantity',
        'price',
        'tax',
        'fee',
        'notes',
        'type',
        'account_id',
        'portfolio_id',
        'security_id',
        'user_id',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'total_amount' => MoneyCast::class,
        'quantity' => 'decimal:6',
        'price' => 'decimal:6',
        'tax' => 'decimal:2',
        'fee' => 'decimal:2',
        'type' => TradeType::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope());

        static::creating(function (Trade $trade) {
            // Only needed in importer
            if (is_null($trade->account_id)) {
                $trade->account_id = Account::getDefaultAccountId();
            }

            // Only needed in importer
            if (is_null($trade->portfolio_id)) {
                $trade->portfolio_id = self::getDefaultPortfolioId();
            }

            // Only needed in importer
            if (is_null($trade->security_id)) {
                $trade->portfolio_id = self::getDefaultSecurityId();
            }

            // Only needed in importer and web
            if (is_null($trade->user_id)) {
                $trade->user_id = auth()->user()->id;
            }

            $trade->notes = trim($trade->notes) ?? null;

            // Set the type factor based on trade type
            $amountSign = 1;
            $quantitySign = 1;
            $feeSign = 1;

            if ($trade->type == TradeType::BUY) {
                $amountSign = -1;
            } else {
                $quantitySign = -1;
                $feeSign = -1;
            }

            $trade->total_amount = round(
                ($trade->price * $trade->quantity + ($trade->tax + $trade->fee) * $feeSign) * $amountSign,
                2
            );

            $trade->quantity *= $quantitySign;
        });

        static::created(function (Trade $trade) {
            Account::updateAccountBalance($trade->account_id);
            Portfolio::updatePortfolioMarketValue($trade->portfolio_id);
            Security::updateSecurityQuantity($trade->security_id);
        });

        static::updating(function (Trade $trade) {
            $trade->notes = trim($trade->notes) ?? null;

            // Set the type factor based on trade type
            $amountSign = 1;
            $quantitySign = 1;
            $feeSign = 1;

            if ($trade->type == TradeType::BUY) {
                $amountSign = -1;
            } else {
                $quantitySign = -1;
                $feeSign = -1;
            }

            $trade->total_amount = round(
                ($trade->price * $trade->quantity + ($trade->tax + $trade->fee) * $feeSign) * $amountSign,
                2
            );

            $trade->quantity *= $quantitySign;
        });
    }

    /**
     * Get the default portfolio ID for the current user.
     *
     * Retrieves the first portfolio with the name 'Demo' for the currently
     * authenticated user. If it does not exist, it creates one with that name.
     *
     * @return int The ID of the default portfolio.
     */
    private static function getDefaultPortfolioId(): int
    {
        $portfolio = Portfolio::whereName('Demo')->first();
        if (!$portfolio) {
            $portfolio = Portfolio::firstOrCreate(['name' => 'Demo', 'user_id' => auth()->id()]);
        }
        return $portfolio->id;
    }

    /**
     * Get the default security ID for the current user.
     *
     * Retrieves the first security with the name 'Demo' for the currently
     * authenticated user. If it does not exist, it creates one with that name.
     *
     * @return int The ID of the default security.
     */
    private static function getDefaultSecurityId(): int
    {
        $portfolio = Security::whereName('Demo')->first();
        if (!$portfolio) {
            $portfolio = Security::firstOrCreate(['name' => 'Demo', 'user_id' => auth()->id()]);
        }
        return $portfolio->id;
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class, 'portfolio_id');
    }

    public function security(): BelongsTo
    {
        return $this->belongsTo(Security::class, 'security_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
