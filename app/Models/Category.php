<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Chartable;
use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Models\Scopes\UserScope;
use App\Models\Traits\HasChartDefaults;
use App\Observers\CategoryObserver;
use Carbon\CarbonInterface;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property string $name
 * @property CategoryGroup $group
 * @property TransactionType $type
 * @property string $color
 * @property bool $is_active
 * @property string $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $user
 * @property-read Collection<int, CategoryStatistic> $statistics
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read Collection<int, Transaction> $transactions
 */
#[ObservedBy([CategoryObserver::class])]
#[ScopedBy(UserScope::class)]
final class Category extends Model implements Chartable
{
    use HasChartDefaults;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'group' => CategoryGroup::VarExpenses->value,
        'type' => TransactionType::Expense->value,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'group' => CategoryGroup::class,
            'type' => TransactionType::class,
            'is_active' => 'bool',
        ];
    }

    /**
     * Owner of the category.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Category statistics for this category.
     *
     * @return HasMany<CategoryStatistic, $this>
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(CategoryStatistic::class, 'category_id');
    }

    /**
     * Subscriptions in this category.
     *
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'category_id');
    }

    /**
     * Transactions in this category.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }
}
