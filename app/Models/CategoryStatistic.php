<?php

namespace App\Models;

use App\Models\Scopes\CategoryStatisticScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryStatistic extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'category_statistics';

    protected $fillable = [
        'year',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'dec',
        'category_id',
    ];

    protected $casts = [
        'jan' => 'decimal:4',
        'feb' => 'decimal:4',
        'mar' => 'decimal:4',
        'apr' => 'decimal:4',
        'may' => 'decimal:4',
        'jun' => 'decimal:4',
        'jul' => 'decimal:4',
        'aug' => 'decimal:4',
        'sep' => 'decimal:4',
        'oct' => 'decimal:4',
        'nov' => 'decimal:4',
        'dec' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CategoryStatisticScope());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}