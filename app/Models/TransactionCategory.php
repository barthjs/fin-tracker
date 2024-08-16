<?php

namespace App\Models;


use App\Models\Scopes\TransactionCategoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionCategory extends Model
{
    use HasFactory;

    protected $table = 'transaction_categories';

    protected $fillable = [
        'name',
        'type',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TransactionCategoryScope());

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

    public function bankAccountTransaction(): HasMany
    {
        return $this->hasMany(BankAccountTransaction::class, 'category_id');
    }
}
