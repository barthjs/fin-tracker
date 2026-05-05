<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Chartable;
use App\Contracts\HasDeletableFiles;
use App\Enums\Currency;
use App\Models\Scopes\UserScope;
use App\Models\Traits\HasChartDefaults;
use App\Observers\AccountObserver;
use App\Observers\FileCleanupObserver;
use Carbon\CarbonInterface;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read Collection<int, Trade> $trades
 * @property-read Collection<int, Transaction> $transactions
 * @property-read string $balanceColor
 */
#[ObservedBy([AccountObserver::class, FileCleanupObserver::class])]
#[ScopedBy(UserScope::class)]
final class Account extends Model implements Chartable, HasDeletableFiles
{
    use HasChartDefaults;

    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    use HasUlids;

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
     * Returns the sum of all active accounts' balances.
     */
    public static function getActiveBalanceSum(): float
    {
        return (float) self::query()->where('is_active', true)->sum('balance');
    }

    /**
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
     * Subscriptions belonging to this account.
     *
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'account_id');
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
