<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Scopes\AccountScope;
use App\Models\Scopes\PortfolioScope;
use App\Models\Scopes\SecurityScope;
use App\Models\Scopes\TradeScope;
use App\Models\Scopes\TransactionScope;
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
        'account_id',
        'portfolio_id',
        'security_id',
        'user_id',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'total_amount' => 'decimal:18',
        'quantity' => 'decimal:18',
        'price' => 'decimal:18',
        'tax' => MoneyCast::class,
        'fee' => MoneyCast::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TradeScope());

        static::creating(function (Trade $trade) {
            // Only needed in importer
            if (is_null($trade->account_id)) {
                $trade->account_id = Account::getDefaultAccountId();
            }

            // Only needed in importer
            if (is_null($trade->portfolio_id)) {
                $trade->portfolio_id = self::getDefaultPortfolioId();
            }

            // Only needed in importer and web
            if (is_null($trade->user_id)) {
                $trade->user_id = auth()->user()->id;
            }

            $trade->notes = trim($trade->notes) ?? null;
        });

        static::created(function (Trade $trade) {
            // Recalculates and updates the account balance for the associated account
            $balance = Transaction::whereAccountId($trade->account_id)
                ->withoutGlobalScopes([TransactionScope::class])
                ->sum('amount');
            Account::whereId($trade->account_id)
                ->withoutGlobalScopes([AccountScope::class])
                ->update(['balance' => $balance]);

            // Recalculates and updates the total quantity for the associated security
            $totalQuantity = Trade::whereSecurityId($trade->security_id)
                ->withoutGlobalScopes([TradeScope::class])
                ->sum('quantity');
            Security::whereId($trade->security_id)
                ->withoutGlobalScopes([SecurityScope::class])
                ->update(['total_quantity' => $totalQuantity]);

            // Recalculates and updates the market value for the associated portfolio
            $marketValue = Trade::wherePortfolioId($trade->portfolio_id)
                ->withoutGlobalScopes([TradeScope::class])
                ->sum('total_amount');
            Portfolio::whereId($trade->security_id)
                ->withoutGlobalScopes([PortfolioScope::class])
                ->update(['market_value' => $marketValue]);
        });

        static::updating(function (Trade $trade) {
            $trade->notes = trim($trade->notes) ?? null;
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
