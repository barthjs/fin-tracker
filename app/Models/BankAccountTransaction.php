<?php

namespace App\Models;


use App\Models\Scopes\BankAccountScope;
use App\Models\Scopes\BankAccountTransactionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccountTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'bank_account_transactions';

    protected $fillable = [
        'date_time',
        'amount',
        'destination',
        'notes',
        'bank_account_id',
        'category_id'
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'amount' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new BankAccountTransactionScope());

        static::creating(function ($model) {
            if (is_null($model->bank_account_id)) {
                $model->bank_account_id = self::getDefaultBankAccountId();
            }

            if (is_null($model->category_id)) {
                $model->category_id = self::getDefaultTransactionCategoryId();
            }
        });

        static::created(callback: function (BankAccountTransaction $transaction) {
            $sum = BankAccountTransaction::whereBankAccountId($transaction->bank_account_id)->withoutGlobalScopes([BankAccountTransactionScope::class])->sum('amount');
            BankAccount::whereId($transaction->bank_account_id)->withoutGlobalScopes([BankAccountScope::class])->update(['balance' => $sum]);
        });
    }

    public static function getDefaultBankAccountId(): int
    {
        $bankAccount = BankAccount::whereName('Demo')->first();
        if (!$bankAccount) {
            $bankAccount = BankAccount::firstOrCreate(['name' => 'Demo', 'user_id' => auth()->id()]);
        }
        return $bankAccount->id;
    }

    public static function getDefaultTransactionCategoryId(): int
    {
        $category = TransactionCategory::whereName('Demo')->first();
        if (!$category) {
            $category = TransactionCategory::firstOrCreate(['name' => 'Demo', 'user_id' => auth()->id()]);
        }
        return $category->id;
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }
}
