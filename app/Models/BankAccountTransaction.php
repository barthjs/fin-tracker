<?php

namespace App\Models;


use App\Models\Scopes\BankAccountTransactionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccountTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'bank_account_transactions';
    protected $primaryKey = 'transaction_id';

    protected static function booted(): void
    {
        static::addGlobalScope(new BankAccountTransactionScope());
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
