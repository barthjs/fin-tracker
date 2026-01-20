<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Scopes\UserRelationScope;
use Carbon\CarbonInterface;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property-read Account $account
 * @property-read Account|null $transferAccount
 * @property-read Category $category
 */
final class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

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
     * Updates the transaction statistics.
     */
    public static function updateCategoryStatistics(string $categoryId, CarbonInterface $date): void
    {
        $year = $date->year;
        $month = $date->month;
        $monthColumn = mb_strtolower($date->format('M'));

        $sumPerMonth = self::where('category_id', $categoryId)
            ->where('type', '!=', TransactionType::Transfer)
            ->whereYear('date_time', $year)
            ->whereMonth('date_time', $month)
            ->sum('amount');

        $stat = CategoryStatistic::updateOrCreate(['category_id' => $categoryId, 'year' => $year], [$monthColumn => $sumPerMonth]);

        if ($stat->yearlySum() === 0.0) {
            $stat->delete();
        }
    }

    /**
     * Get the attributes that should be cast.
     *
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

    protected static function booted(): void
    {
        self::addGlobalScope(new UserRelationScope());

        self::creating(function (Transaction $transaction): void {
            $transaction->payee = $transaction->payee === null ? null : mb_trim($transaction->payee);

            if ($transaction->type !== TransactionType::Transfer) {
                $transaction->transfer_account_id = null;
            }

            // Only needed in importer
            /** @phpstan-ignore-next-line */
            if ($transaction->account_id === null) {
                $transaction->account_id = Account::getOrCreateDefaultAccount()->id;
            }

            // Only needed in importer
            /** @phpstan-ignore-next-line */
            if ($transaction->category_id === null) {
                $transaction->category_id = Category::getOrCreateDefaultCategory()->id;
            }
        });

        self::updating(function (Transaction $transaction): void {
            $transaction->payee = $transaction->payee === null ? null : mb_trim($transaction->payee);

            if ($transaction->type !== TransactionType::Transfer) {
                $transaction->transfer_account_id = null;
            }
        });
    }
}
