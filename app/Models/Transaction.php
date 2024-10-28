<?php

namespace App\Models;


use App\Enums\TransactionGroup;
use App\Models\Scopes\AccountScope;
use App\Models\Scopes\TransactionScope;
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
        'amount' => 'decimal:4',
    ];

    /**
     * Boot the model and set up global scopes and event listeners.
     *
     * This method is called when the model is being booted. It adds a global
     * scope for transaction queries and sets up event listeners for creating,
     * created, and updating events to manage transaction attributes.
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Add a global scope to the Transaction model, applying the TransactionScope to all queries.
        static::addGlobalScope(new TransactionScope());

        // Listen for the creating event to set default values for the model before saving.
        static::creating(function (Transaction $transaction) {
            // If bank_account_id is not set, assign the default bank account ID.
            // Only in importer
            if (is_null($transaction->account_id)) {
                $transaction->account_id = self::getDefaultAccountId();
            }

            // If category_id is not set, assign the default transaction category ID.
            // Only in importer
            if (is_null($transaction->category_id)) {
                $transaction->category_id = self::getDefaultCategoryId();
            }

            // Only in web and importer
            if (is_null($transaction->user_id)) {
                $transaction->user_id = auth()->user()->id;
            }

            // Trim whitespace from the destination attribute to ensure no leading or trailing spaces.
            $transaction->destination = trim($transaction->destination) ?? null;
        });

        // Listen for the created event, which is triggered after a transaction is created.
        static::created(function (Transaction $transaction) {
            // Calculate the total amount of transactions for the bank account, excluding global scopes.
            $sum = Transaction::whereAccountId($transaction->account_id)
                ->withoutGlobalScopes([TransactionScope::class])
                ->sum('amount');

            // Update the balance of the associated bank account with the new total amount.
            Account::whereId($transaction->account_id)
                ->withoutGlobalScopes([AccountScope::class])
                ->update(['balance' => $sum]);
        });

        // Listen for the updating event to trim the destination before saving.
        static::updating(function (Transaction $transaction) {
            $transaction->destination = trim($transaction->destination) ?? null;
        });
    }

    /**
     * Get the default bank account ID for the current user.
     *
     * This method retrieves the bank account with the name 'Demo' for the currently
     * authenticated user. If it does not exist, it creates one with that name.
     *
     * @return int The ID of the default bank account.
     */
    private static function getDefaultAccountId(): int
    {
        // Try to find a bank account with the name 'Demo'.
        $account = Account::whereName('Demo')->first();
        if (!$account) {
            // If it does not exist, create a new bank account with that name.
            $account = Account::firstOrCreate(['name' => 'Demo', 'user_id' => auth()->id()]);
        }
        return $account->id; // Return the ID of the bank account.
    }

    /**
     * Get the default transaction category ID for the current user.
     *
     * This method retrieves the category with the name 'Demo' for the currently
     * authenticated user. If it does not exist, it creates one with that name.
     *
     * @return int The ID of the default transaction category.
     */
    private static function getDefaultCategoryId(): int
    {
        // Try to find a category with the name 'Demo'.
        $category = Category::whereName('Demo')->first();
        if (!$category) {
            // If it does not exist, create a new category with that name.
            $category = Category::firstOrCreate(['name' => 'Demo', 'user_id' => auth()->id()]);
        }
        return $category->id; // Return the ID of the category.
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
