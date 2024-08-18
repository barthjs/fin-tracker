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
        'date',
        'amount',
        'destination',
        'notes',
        'bank_account_id',
        'category_id'
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new BankAccountTransactionScope());
        static::created(callback: function (BankAccountTransaction $transaction) {
            $sum = BankAccountTransaction::whereBankAccountId($transaction->bank_account_id)->withoutGlobalScopes([BankAccountTransactionScope::class])->sum('amount');
            BankAccount::whereId($transaction->bank_account_id)->withoutGlobalScopes([BankAccountScope::class])->update(['balance' => $sum]);
        });

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
