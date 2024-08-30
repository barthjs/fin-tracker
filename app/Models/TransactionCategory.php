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
        'group',
        'type',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'group' => TransactionGroup::class,
        'type' => TransactionType::class
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TransactionCategoryScope());

        static::creating(function ($model) {
            if (is_null($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });

        static::created(function ($model) {
            if (!is_null($model->group)) {
                $model->type = match ($model->group->name) {
                    'fix_expenses', 'var_expenses' => 'expense',
                    'fix_revenues', 'var_revenues' => 'revenue',
                    default => 'transfer'
                };
                $model->save();
            }
        });

        static::updating(function ($model) {
            $model->type = match ($model->group->name) {
                'fix_expenses', 'var_expenses' => 'expense',
                'fix_revenues', 'var_revenues' => 'revenue',
                default => 'transfer'
            };
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

    public function statistics(): HasMany
    {
        return $this->hasMany(TransactionCategoryStatistic::class, 'category_id');
    }
}
