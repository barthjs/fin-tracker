<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasDeletableFiles;
use App\Enums\PeriodUnit;
use App\Models\Scopes\UserRelationScope;
use App\Observers\FileCleanupObserver;
use Carbon\CarbonImmutable;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property string $account_id
 * @property string $category_id
 * @property string $name
 * @property string|null $description
 * @property float $amount
 * @property PeriodUnit $period_unit
 * @property int $period_frequency
 * @property-read int $day_of_month
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable $next_payment_date
 * @property CarbonImmutable|null $ended_at
 * @property bool $auto_generate_transaction
 * @property CarbonImmutable|null $last_generated_at
 * @property bool $remind_before_payment
 * @property int $reminder_days_before
 * @property CarbonImmutable|null $last_reminded_at
 * @property string|null $logo
 * @property string $color
 * @property bool $is_active
 * @property-read Account $account
 * @property-read Category $category
 * @property-read Collection<int, Transaction> $transactions
 * @property-read User $user
 */
final class Subscription extends Model implements HasDeletableFiles
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory, HasUlids;

    protected $table = 'subscriptions';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'amount' => 0.0,
        'period_frequency' => 1,
        'auto_generate_transaction' => true,
        'remind_before_payment' => false,
        'reminder_days_before' => 3,
        'is_active' => true,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'day_of_month',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'amount' => 'float',
            'period_unit' => PeriodUnit::class,
            'period_frequency' => 'int',
            'day_of_month' => 'int',
            'started_at' => 'date',
            'next_payment_date' => 'date',
            'ended_at' => 'date',
            'auto_generate_transaction' => 'bool',
            'last_generated_at' => 'timestamp',
            'remind_before_payment' => 'bool',
            'reminder_days_before' => 'int',
            'last_reminded_at' => 'timestamp',
            'is_active' => 'bool',
        ];
    }

    /**
     * Account this subscription belongs to.
     *
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Category of this subscription.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Transactions of this subscription.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Owner of this subscription.
     *
     * @return HasOneThrough<User, Account, $this>
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Account::class,
            'id',
            'id',
            'account_id',
            'user_id'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFileFields(): array
    {
        return ['logo'];
    }

    /**
     * {@inheritDoc}
     */
    public function getFileDisk(): string
    {
        return 'public';
    }

    protected static function booted(): void
    {
        self::addGlobalScope(new UserRelationScope());

        self::saving(function (self $model): void {
            $model->name = mb_trim($model->name);
        });

        self::observe(FileCleanupObserver::class);
    }
}
