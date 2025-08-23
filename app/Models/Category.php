<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Models\Scopes\UserScope;
use Carbon\CarbonInterface;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property-read string $id
 * @property string $name
 * @property CategoryGroup $group
 * @property-read TransactionType $type
 * @property string $color
 * @property bool $is_active
 * @property string $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $user
 * @property-read Collection<int, CategoryStatistic> $statistics
 * @property-read Collection<int, Transaction> $transactions
 */
final class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasUlids;

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
     * @return array{labels: list<string>, series: list<float>, colors: list<string|null>}
     */
    public static function getChartData(TransactionType $type): array
    {
        $categories = self::where('is_active', true)->where('type', $type)->get();

        $monthColumn = mb_strtolower(Carbon::createFromDate(null, Carbon::today()->month)->format('M'));
        $year = Carbon::now()->year;

        $data = [];

        foreach ($categories as $category) {
            /** @var float $sum */
            $sum = CategoryStatistic::where('category_id', $category->id)
                ->where('year', $year)
                ->value($monthColumn) ?? 0.0;

            $data[] = [
                'label' => $category->name,
                'sum' => $sum,
                'color' => $category->color,
            ];
        }

        // Sort descending by sum
        usort($data,
            function (array $a, array $b): int {
                return $b['sum'] <=> $a['sum'];
            }
        );

        return [
            'labels' => array_column($data, 'label'),
            'series' => array_column($data, 'sum'),
            'colors' => array_column($data, 'color'),
        ];
    }

    /**
     * Retrieve or create the default category for the current user.
     *
     * Attempts to find a category named 'Demo' for the authenticated user.
     * If no such category exists, a new one is created with that name
     * and a randomly generated color.
     */
    public static function getOrCreateDefaultCategory(?User $user = null): self
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
     * Get the attributes that should be cast.
     *
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
     * Transactions in this category.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    protected static function booted(): void
    {
        self::addGlobalScope(new UserScope);

        self::creating(function (Category $category): void {
            $category->name = mb_trim($category->name);
            /** @phpstan-ignore-next-line */
            $category->type = self::getType($category->group);

            if ($category->user_id === null) {
                $category->user_id = auth()->user()->id;
            }
        });

        // Create an empty entry for the statistic after category creation
        self::created(function (Category $category): void {
            if ($category->type !== TransactionType::Transfer) {
                CategoryStatistic::create(['category_id' => $category->id, 'year' => Carbon::now()->year]);
            }
        });

        // Listen for the updating event to trim the name and set the type before saving.
        self::updating(function (Category $category): void {
            $category->name = mb_trim($category->name);
            /** @phpstan-ignore-next-line */
            $category->type = self::getType($category->group);
        });
    }

    /**
     * Set the type based on the group.
     */
    private static function getType(CategoryGroup $group): TransactionType
    {
        return match ($group) {
            CategoryGroup::FixExpenses, CategoryGroup::VarExpenses => TransactionType::Expense,
            CategoryGroup::FixRevenues, CategoryGroup::VarRevenues => TransactionType::Revenue,
            CategoryGroup::Transfers => TransactionType::Transfer,
        };
    }
}
