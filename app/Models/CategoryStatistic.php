<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
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
        'jan' => MoneyCast::class,
        'feb' => MoneyCast::class,
        'mar' => MoneyCast::class,
        'apr' => MoneyCast::class,
        'may' => MoneyCast::class,
        'jun' => MoneyCast::class,
        'jul' => MoneyCast::class,
        'aug' => MoneyCast::class,
        'sep' => MoneyCast::class,
        'oct' => MoneyCast::class,
        'nov' => MoneyCast::class,
        'dec' => MoneyCast::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CategoryStatisticScope);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
