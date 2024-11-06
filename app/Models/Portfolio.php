<?php

namespace App\Models;

use App\Models\Scopes\PortfolioScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Portfolio extends Model
{
    use HasFactory;

    public $table = 'portfolios';

    protected $fillable = [
        'name',
        'market_value',
        'description',
        'logo',
        'color',
        'active',
        'user_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new PortfolioScope());

        static::creating(function (Portfolio $portfolio) {
            // Only needed in importer and seeder
            if (is_null($portfolio->color)) {
                $portfolio->color = strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
            }

            // Only needed in importer and web
            if (is_null($portfolio->user_id)) {
                $portfolio->user_id = auth()->user()->id;
            }

            $portfolio->name = trim($portfolio->name);
            $portfolio->description = trim($portfolio->description) ?? null;
        });

        static::updating(function (Portfolio $portfolio) {
            $portfolio->name = trim($portfolio->name);
            $portfolio->description = trim($portfolio->description) ?? null;
        });

        static::updated(function (Portfolio $portfolio) {
            $logo = $portfolio->getOriginal('logo');
            if (is_null($portfolio->logo) && !is_null($logo) && Storage::disk('public')->exists($logo)) {
                Storage::disk('public')->delete($logo);
            }
        });

        static::deleted(function (Portfolio $portfolio) {
            if (!is_null($portfolio->logo) && Storage::disk('public')->exists($portfolio->logo)) {
                Storage::disk('public')->delete($portfolio->logo);
            }
        });
    }



    public function securities(): HasMany
    {
        return $this->hasMany(Security::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
