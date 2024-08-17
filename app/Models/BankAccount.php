<?php

namespace App\Models;

use App\Enums\Currency;
use App\Models\Scopes\BankAccountScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'name',
        'balance',
        'currency',
        'description',
        'active',
        'user_id'
    ];

    protected $casts = [
        'currency' => Currency::class,
        'active' => 'boolean',
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankAccountTransaction::class, 'bank_account_id');
    }
}
