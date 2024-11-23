<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'market_value' => 'decimal:6',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope());

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
            $portfolio->description = trim($portfolio->description ?? "");
        });

        static::updating(function (Portfolio $portfolio) {
            $portfolio->name = trim($portfolio->name);
            $portfolio->description = trim($portfolio->description ?? "");
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

    /**
     * Recalculate and update the market value for the associated portfolio
     *
     * @param int $portfolioId
     * @return void
     */
    public static function updatePortfolioMarketValue(int $portfolioId): void
    {
        $securities = Trade::wherePortfolioId($portfolioId)
            ->pluck('security_id')
            ->unique()
            ->toArray();

        $marketValue = 0;
        foreach ($securities as $security) {
            $quantity = Trade::whereSecurityId($security)
                ->wherePortfolioId($portfolioId)
                ->sum('quantity');

            $price = Security::whereId($security)
                ->value('price');

            $marketValue += $price * $quantity;
        }

        Portfolio::whereId($portfolioId)
            ->update(['market_value' => $marketValue]);
    }

    public function securities(): BelongsToMany
    {
        return $this->belongsToMany(Security::class, 'trades', 'portfolio_id', 'security_id')
            ->withoutGlobalScopes();
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getActiveSum(): float
    {
        return floatval(Portfolio::whereActive(true)->sum('market_value'));
    }
}
