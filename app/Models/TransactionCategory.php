<?php

namespace App\Models;


use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
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
        'group',
        'active',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'group' => TransactionGroup::class,
        'active' => 'boolean',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(BankAccountTransaction::class, 'category_id');
    }
}
