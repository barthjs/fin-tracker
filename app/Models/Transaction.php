<?php

namespace App\Models;


use App\Casts\MoneyCast;
use App\Models\Scopes\UserScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'transactions';

    protected $fillable = [
        'date_time',
        'amount',
        'destination',
        'notes',
        'account_id',
        'category_id',
        'user_id',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'amount' => MoneyCast::class,
    ];

    /**
     * Set up global scopes and event listeners
     *
     * Adds a global scope for filtering only the authenticated users transactions.
     * Sets default values for 'account_id' and 'category_id'
     * and associates the authenticated user with the transaction.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope());

        static::creating(function (Transaction $transaction) {
            // Only needed in importer
            if (is_null($transaction->account_id)) {
                $transaction->account_id = Account::getDefaultAccountId();
            }

            // Only needed in importer
            if (is_null($transaction->category_id)) {
                $transaction->category_id = self::getDefaultCategoryId();
            }

            // Only needed in importer and web
            if (is_null($transaction->user_id)) {
                $transaction->user_id = auth()->user()->id;
            }

            $transaction->destination = trim($transaction->destination) ?? null;
        });

        static::created(function (Transaction $transaction) {
            // Recalculate and update the balance for the associated account and all statistics
            Account::updateAccountBalance($transaction->account_id);
            self::updateCategoryStatistics($transaction->category_id, $transaction->date_time);
        });

        static::updating(function (Transaction $transaction) {
            $transaction->destination = trim($transaction->destination) ?? null;
        });
    }

    /**
     * Get the default transaction category ID for the current user.
     *
     * Retrieves the category with the name 'Demo' for the currently
     * authenticated user. If it does not exist, it creates one with that name.
     *
     * @return int The ID of the default transaction category.
     */
    private static function getDefaultCategoryId(): int
    {
        $category = Category::whereName('Demo')->first();
        if (!$category) {
            $category = Category::firstOrCreate(['name' => 'Demo', 'user_id' => auth()->id()]);
        }
        return $category->id;
    }

    /**
     * Updates the transaction statistics
     *
     * @param int $categoryId
     * @param string $date
     * @return void
     */
    public static function updateCategoryStatistics(int $categoryId, string $date): void
    {
        $year = Carbon::parse($date)->year;
        $month = Carbon::parse($date)->month;
        $monthColumn = strtolower(Carbon::create(null, $month)->format('M'));
        $sumPerMonth = Transaction::whereCategoryId($categoryId)
            ->whereYear('date_time', $year)
            ->whereMonth('date_time', $month)
            ->sum('amount');

        CategoryStatistic::updateOrCreate(['category_id' => $categoryId, 'year' => $year], [$monthColumn => $sumPerMonth]);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
