<?php

namespace App\Models;

use App\Enums\Currency;
use App\Models\Scopes\AccountScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Storage;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'name',
        'balance',
        'currency',
        'description',
        'logo',
        'color',
        'active',
        'user_id'
    ];

    protected $casts = [
        'balance' => 'decimal:4',
        'currency' => Currency::class,
        'active' => 'boolean',
    ];

    /**
     * Set up global scopes and event listeners
     *
     * Adds a global scope for filtering only the authenticated users accounts.
     * Ensures defaults for 'currency' and that the 'user_id' is assigned
     * to the authenticated.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new AccountScope());

        static::creating(function (Account $account) {
            // Only needed in seeder
            if (is_null($account->currency)) {
                $account->currency = self::getCurrency();
            }

            // Only needed in importer and web
            if (is_null($account->user_id)) {
                $account->user_id = auth()->user()->id;
            }

            // Only needed in importer and seeder
            if (is_null($account->color)) {
                $account->color = strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF)));
            }

            $account->name = trim($account->name);
        });

        static::updating(function (Account $account) {
            $account->name = trim($account->name);
        });

        static::updated(function (Account $account) {
            $logo = $account->getOriginal('logo');
            if (is_null($account->logo) && !is_null($logo) && Storage::disk('public')->exists($logo)) {
                Storage::disk('public')->delete($logo);
            }
        });

        static::deleted(function (Account $account) {
            if (!is_null($account->logo) && Storage::disk('public')->exists($account->logo)) {
                Storage::disk('public')->delete($account->logo);
            }
        });

    }

    /**
     * Get the currency name based on the provided input or application configuration.
     *
     * This method attempts to retrieve a valid currency name. If an input currency
     * is provided, and it is valid, that currency name is returned. If the input currency
     * is null or invalid, it falls back to the default currency configured in the application.
     * If that is also invalid, it defaults to USD.
     *
     * @param string|null $inputCurrency The currency code to check against available currencies.
     * @return string The name of the valid currency.
     */
    public static function getCurrency(?string $inputCurrency = null): string
    {
        if (!is_null($inputCurrency)) {
            $currency = Currency::tryFrom($inputCurrency);
            if (!is_null($currency)) {
                return $currency->name;
            }
        }

        $currency = Currency::tryFrom(config('app.currency'));
        if (!is_null($currency)) {
            return $currency->name;
        }

        return Currency::USD->name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }
}
