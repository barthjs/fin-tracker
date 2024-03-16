<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccountTransaction extends Model
{
    use HasFactory;
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bank_account_transactions';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'transaction_id';

    /**
     * @return BelongsTo
     */
    public function BankAccount(): BelongsTo
    {
        return $this->belongsTo(related: BankAccount::class, foreignKey: 'bank_account_id');
    }

    /**
     * @return BelongsTo
     */
    public function TransactionCategory(): BelongsTo
    {
        return $this->belongsTo(related: TransactionCategory::class, foreignKey: 'category_id');
    }
}
