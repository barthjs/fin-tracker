<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SecurityType;
use App\Models\Scopes\UserScope;
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
        'market_value',
        'description',
        'logo',
        'color',
        'type',
        'active',
        'user_id',
    ];

    protected $casts = [
        'price' => 'decimal:6',
        'total_quantity' => 'float',
        'market_value' => 'decimal:6',
        'active' => 'boolean',
        'type' => SecurityType::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);

        static::creating(function (Security $security) {
            // Only needed in importer and seeder
            if (is_null($security->color)) {
                $security->color = mb_strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
            }

            // Only needed in importer
            if (is_null($security->type)) {
                $security->type = SecurityType::STOCK;
            }

            // Only needed in importer and web
            if (is_null($security->user_id)) {
                $security->user_id = auth()->user()->id;
            }

            $security->name = mb_trim($security->name);
            $security->isin = mb_trim($security->isin ?? '');
            $security->symbol = mb_trim($security->symbol ?? '');
            $security->market_value = $security->price * $security->total_quantity;
            $security->description = mb_trim($security->description ?? '');
        });

        static::updating(function (Security $security) {
            $security->name = mb_trim($security->name);
            $security->isin = mb_trim($security->isin ?? '');
            $security->symbol = mb_trim($security->symbol ?? '');
            $security->market_value = $security->price * $security->total_quantity;
            $security->description = mb_trim($security->description ?? '');
        });

        static::updated(function (Security $security) {
            $logo = $security->getOriginal('logo');
            if (is_null($security->logo) && ! is_null($logo) && Storage::disk('public')->exists($logo)) {
                Storage::disk('public')->delete($logo);
            }
        });

        static::deleted(function (Security $security) {
            if (! is_null($security->logo) && Storage::disk('public')->exists($security->logo)) {
                Storage::disk('public')->delete($security->logo);
            }
        });
    }

    /**
     * Recalculate and update the total quantity for the associated security
     */
    public static function updateSecurityQuantity(int $securityId): void
    {
        $totalQuantity = Trade::whereSecurityId($securityId)->sum('quantity');
        $security = self::whereId($securityId)->first();

        $security->update([
            'total_quantity' => $totalQuantity,
            'market_value' => $totalQuantity * $security->price,
        ]);
    }

    public function portfolios(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'trades', 'security_id', 'portfolio_id');
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
