<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use App\Enums\TradeType;
use App\Enums\TransactionType;
use App\Models\Scopes\UserScope;
use Carbon\CarbonInterface;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read string $id
 * @property string $name
 * @property-read float $balance
 * @property Currency $currency
 * @property string|null $description
 * @property string|null $logo
 * @property string $color
 * @property bool $is_active
 * @property string $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $user
 * @property-read Collection<int, Transaction> $incomingTransfers
 * @property-read Collection<int, Trade> $trades
 * @property-read Collection<int, Transaction> $transactions
 * @property-read string $balanceColor
 */
final class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory, HasUlids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'balance' => 0.0,
        'is_active' => true,
    ];

    /**
     * Retrieve or create the default account for the current user.
     *
     * Attempts to find an account named 'Demo' for the authenticated user.
     * If no such account exists, a new one is created with that name
     * and a randomly generated color.
     */
    public static function getOrCreateDefaultAccount(?User $user = null): self
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
     * Recalculate and update the balance for the account.
     *
     * The balance is derived from all financial activities linked to the account:
     * - Revenues increase the balance.
     * - Expenses decrease the balance.
     * - Transfers decrease the balance if the account is the source
     *   and increase it if the account is the target.
     * - Trades decrease the balance when assets are bought,
     *   and increase it when assets are sold.
     */
    public static function updateAccountBalance(string $accountId): void
    {
        $revenue = (float) Transaction::where('account_id', $accountId)
            ->where('type', TransactionType::Revenue)
            ->sum('amount');

        $expense = (float) Transaction::where('account_id', $accountId)
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $outgoingTransfers = (float) Transaction::where('account_id', $accountId)
            ->where('type', TransactionType::Transfer)
            ->sum('amount');

        $incomingTransfers = (float) Transaction::where('transfer_account_id', $accountId)
            ->where('type', TransactionType::Transfer)
            ->sum('amount');

        $buyTrades = (float) Trade::where('account_id', $accountId)
            ->where('type', TradeType::Buy)
            ->sum('total_amount');

        $sellTrades = (float) Trade::where('account_id', $accountId)
            ->where('type', TradeType::Sell)
            ->sum('total_amount');

        $balance = $revenue - $expense - $outgoingTransfers + $incomingTransfers - $buyTrades + $sellTrades;

        self::whereKey($accountId)->update(['balance' => $balance]);
    }

    /**
     * Returns the sum of all active accounts' balances.
     */
    public static function getActiveBalanceSum(): float
    {
        return (float) self::where('is_active', true)->sum('balance');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'balance' => 'float',
            'currency' => Currency::class,
            'is_active' => 'bool',
        ];
    }

    /**
     * Owner of the account.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Transactions where this account is the transfer target.
     *
     * @return HasMany<Transaction, $this>
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transfer_account_id');
    }

    /**
     * Trades where this account is the main account.
     *
     * @return HasMany<Trade, $this>
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class, 'account_id');
    }

    /**
     * Transactions where this account is the main account.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    protected static function booted(): void
    {
        self::addGlobalScope(new UserScope);

        self::creating(function (Account $account): void {
            $account->name = mb_trim($account->name);

            /** @phpstan-ignore-next-line */
            if ($account->user_id === null) {
                $account->user_id = auth()->user()->id;
            }
        });

        self::updating(function (Account $account): void {
            $account->name = mb_trim($account->name);
        });

        self::updated(function (Account $account): void {
            /** @var string|null $originalLogo */
            $originalLogo = $account->getOriginal('logo');

            if ($originalLogo !== null && $originalLogo !== $account->logo) {
                if (Storage::disk('public')->exists($originalLogo)) {
                    Storage::disk('public')->delete($originalLogo);
                }
            }
        });

        self::deleted(function (Account $account): void {
            if ($account->logo !== null && Storage::disk('public')->exists($account->logo)) {
                Storage::disk('public')->delete($account->logo);
            }
        });
    }

    /**
     * @return Attribute<string, never>
     */
    protected function balanceColor(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match (true) {
                $this->balance === 0.0 => 'gray',
                $this->balance < 0 => 'danger',
                default => 'success'
            }
        );
    }
}
