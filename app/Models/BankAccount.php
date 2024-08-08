<?php

namespace App\Models;

use App\Models\Scopes\BankAccountScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';
    protected $primaryKey = 'bank_account_id';

    protected $fillable = [
        'name'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new BankAccountScope());

        static::creating(function ($model) {
            if (is_null($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
    }

    public function bankAccountTransaction(): HasMany
    {
        return $this->hasMany(BankAccountTransaction::class, 'bank_account_id');
    }

    public function getBalanceAttribute(): mixed
    {
        return $this->bankAccountTransaction()->sum('amount');
    }
}
