<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Scopes\UserRelationScope;
use App\Observers\TransactionObserver;
use Carbon\CarbonInterface;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property-read string $id
 * @property CarbonInterface $date_time
 * @property TransactionType $type
 * @property float $amount
 * @property string|null $payee
 * @property string|null $notes
 * @property string $account_id
 * @property string|null $transfer_account_id
 * @property string $category_id
 * @property string|null $subscription_id
 * @property-read Account $account
 * @property-read Account|null $transferAccount
 * @property-read Category $category
 * @property-read Subscription|null $subscription
 * @property-read User $user
 */
#[ObservedBy([TransactionObserver::class])]
#[ScopedBy(UserRelationScope::class)]
#[WithoutTimestamps]
final class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => TransactionType::Expense->value,
        'amount' => 0.0,
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'date_time' => 'datetime',
            'type' => TransactionType::class,
            'amount' => 'float',
        ];
    }

    /**
     * Account this transaction belongs to.
     *
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Target account for transfers.
     *
     * @return BelongsTo<Account, $this>
     */
    public function transferAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_account_id');
    }

    /**
     * Category of the transaction.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Subscription this transaction belongs to.
     *
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    /**
     * Owner of this transaction.
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
}
