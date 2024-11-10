<?php

namespace App\Models;

use App\Models\Scopes\SecurityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Security extends Model
{
    use HasFactory;

    public $table = 'securities';

    protected $fillable = [
        'name',
        'isin',
        'symbol',
        'price',
        'total_quantity',
        'description',
        'logo',
        'color',
        'active',
        'type_id',
        'user_id',
    ];

    protected $casts = [
        'price' => 'decimal:6',
        'total_quantity' => 'decimal:6',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SecurityScope());

        static::creating(function (Security $security) {
            // Only needed in importer and seeder
            if (is_null($security->color)) {
                $security->color = strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
            }

            // Only needed in importer and web
            if (is_null($security->user_id)) {
                $security->user_id = auth()->user()->id;
            }

            $security->name = trim($security->name);
            $security->isin = trim($security->isin);
            $security->symbol = trim($security->symbol) ?? null;
            $security->description = trim($security->description) ?? null;
        });

        static::updating(function (Security $security) {
            $security->name = trim($security->name);
            $security->isin = trim($security->isin) ?? null;
            $security->symbol = trim($security->symbol) ?? null;
            $security->description = trim($security->description) ?? null;
        });

        static::updated(function (Security $security) {
            $logo = $security->getOriginal('logo');
            if (is_null($security->logo) && !is_null($logo) && Storage::disk('public')->exists($logo)) {
                Storage::disk('public')->delete($logo);
            }
        });

        static::deleted(function (Security $security) {
            if (!is_null($security->logo) && Storage::disk('public')->exists($security->logo)) {
                Storage::disk('public')->delete($security->logo);
            }
        });
    }

    public function portfolios(): BelongsToMany
    {
        return $this->belongsToMany(Security::class, 'securities_portfolios_r', 'security_id', 'portfolio_id');
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SecurityType::class, 'type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
