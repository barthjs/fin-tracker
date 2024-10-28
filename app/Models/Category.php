<?php

namespace App\Models;


use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use App\Models\Scopes\CategoryScope;
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
        'active',
        'user_id'
    ];

    protected $casts = [
        'active' => 'boolean',
        'group' => TransactionGroup::class,
        'type' => TransactionType::class
    ];

    /**
     * Boot the model and set up global scopes and event listeners.
     *
     * This method is called when the model is being booted. It adds a global
     * scope for category queries and sets up event listeners for creating,
     * created, and updating events to manage category attributes.
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Add a global scope to the Category model, applying the CategoryScope to all queries.
        static::addGlobalScope(new CategoryScope());

        // Listen for the creating event to set default values for the model before saving.
        static::creating(function (Category $category) {
            // Only in seeder and importer
            $category->type = match ($category->group->name ?? $category->group = TransactionGroup::transfers) {
                'fix_expenses', 'var_expenses' => 'expense',  // Map expense groups to 'expense' type
                'fix_revenues', 'var_revenues' => 'revenue',  // Map revenue groups to 'revenue' type
                default => 'transfer'  // Default to 'transfer' if no matches found
            };

            // Only in web and importer
            if (is_null($category->user_id)) {
                $category->user_id = auth()->user()->id;
            }

            // Trim whitespace from the category name to ensure no leading or trailing spaces.
            $category->name = trim($category->name);
        });

        // Listen for the updating event to set the type and trim the name before saving.
        static::updating(function (Category $category) {
            // Determine the category type based on the group name during an update.
            $category->type = match ($category->group->name) {
                'fix_expenses', 'var_expenses' => 'expense',  // Map expense groups to 'expense' type
                'fix_revenues', 'var_revenues' => 'revenue',  // Map revenue groups to 'revenue' type
                default => 'transfer'  // Default to 'transfer' if no matches found
            };

            // Trim whitespace from the category name to maintain data consistency.
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
