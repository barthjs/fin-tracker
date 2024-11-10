<?php

namespace App\Models;

use App\Models\Scopes\SecurityTypeScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityType extends Model
{
    use HasFactory;

    protected $table = 'security_types';

    protected $fillable = [
        'name',
        'color',
        'active',
        'user_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SecurityTypeScope());

        static::creating(function (SecurityType $securityType) {
            // Only needed in importer and seeder
            if (is_null($securityType->color)) {
                $securityType->color = strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
            }

            // Only needed in importer and web
            if (is_null($securityType->user_id)) {
                $securityType->user_id = auth()->user()->id;
            }

            $securityType->name = trim($securityType->name);
        });
    }

    public function activeSecurities(): HasMany
    {
        return $this->hasMany(Security::class)->where('active', true);
    }

    public function securities(): HasMany
    {
        return $this->hasMany(Security::class, 'type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
