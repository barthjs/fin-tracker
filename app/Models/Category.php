<?php

namespace App\Models;


use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use App\Models\Scopes\CategoryScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'group',
        'type',
        'color',
        'active',
        'user_id'
    ];

    protected $casts = [
        'active' => 'boolean',
        'group' => TransactionGroup::class,
        'type' => TransactionType::class
    ];

    /**
     * Set up global scopes and event listeners
     *
     * Adds a global scope for filtering only the authenticated users categories.
     * Sets the 'type' based on the 'group' attribute and ensures
     * 'user_id' is assigned to the authenticated user.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CategoryScope());

        static::creating(function (Category $category) {
            // Needed for seeder, importer and in web
            $category->type = match ($category->group->name ?? $category->group = TransactionGroup::transfers) {
                'fix_expenses', 'var_expenses' => 'expense',
                'fix_revenues', 'var_revenues' => 'revenue',
                default => 'transfer'
            };

            // Only in importer and web
            if (is_null($category->user_id)) {
                $category->user_id = auth()->user()->id;
            }

            // Only needed in importer and seeder
            if (is_null($category->color)) {
                $category->color = strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
            }

            $category->name = trim($category->name);
        });

        static::created(function (Category $category) {
            CategoryStatistic::create(['year' => Carbon::now()->year, 'category_id' => $category->id]);
        });

        // Listen for the updating event to set the type and trim the name before saving.
        static::updating(function (Category $category) {
            $category->type = match ($category->group->name) {
                'fix_expenses', 'var_expenses' => 'expense',
                'fix_revenues', 'var_revenues' => 'revenue',
                default => 'transfer'
            };

            $category->name = trim($category->name);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(CategoryStatistic::class, 'category_id');
    }
}