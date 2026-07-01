<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PeriodUnit;
use App\Models\Scopes\BudgetScope;
use App\Models\Traits\HasNotificationAssignments;
use Carbon\CarbonImmutable;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
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
 * @property string $category_id
 * @property float $amount
 * @property PeriodUnit $period_unit
 * @property int $period_frequency
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable|null $ends_at
 * @property int|null $warning_threshold
 * @property bool $notify_on_exceed
 * @property CarbonImmutable|null $last_warned_at
 * @property CarbonImmutable|null $last_exceeded_at
 * @property bool $is_active
 * @property-read Category $category
 * @property-read User $user
 * @property-read Collection<int, Transaction> $transactions
 */
#[ScopedBy(BudgetScope::class)]
#[Table(name: 'budgets')]
final class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory;

    use HasNotificationAssignments;
    use HasUlids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'amount' => 0.0,
        'period_unit' => PeriodUnit::Month->value,
        'period_frequency' => 1,
        'notify_on_exceed' => false,
        'is_active' => true,
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
            'starts_at' => 'date',
            'ends_at' => 'date',
            'warning_threshold' => 'int',
            'notify_on_exceed' => 'bool',
            'last_warned_at' => 'datetime',
            'last_exceeded_at' => 'datetime',
            'is_active' => 'bool',
        ];
    }

    /**
     * Category this budget belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Owner of this budget.
     *
     * @return HasOneThrough<User, Category, $this>
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Category::class,
            'id',
            'id',
            'category_id',
            'user_id'
        );
    }

    /**
     * Transactions booked on the budget's category, regardless of the period.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id', 'category_id');
    }
}
