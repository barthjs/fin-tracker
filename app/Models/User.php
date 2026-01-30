<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasDeletableFiles;
use App\Observers\FileCleanupObserver;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read string $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property-read string|null $full_name
 * @property string $username
 * @property string|null $email
 * @property string|null $avatar
 * @property string $locale
 * @property string|null $password
 * @property string|null $remember_token
 * @property string|null $app_authentication_secret
 * @property array<string>|null $app_authentication_recovery_codes
 * @property bool $is_active
 * @property bool $is_verified
 * @property bool $is_admin
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Collection<int, Account> $accounts
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, Portfolio> $portfolios
 * @property-read Collection<int, UserProvider> $providers
 * @property-read Collection<int, Security> $securities
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read Collection<int, Trade> $trades
 * @property-read Collection<int, Transaction> $transactions
 */
final class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar, HasDeletableFiles, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    protected $table = 'sys_users';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string|bool>
     */
    protected $attributes = [
        'locale' => 'en',
        'is_active' => true,
        'is_verified' => false,
        'is_admin' => false,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'app_authentication_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'password' => 'hashed',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
            'is_active' => 'bool',
            'is_verified' => 'bool',
            'is_admin' => 'bool',
        ];
    }

    /**
     * @return HasMany<Account, $this>
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'user_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'user_id');
    }

    /**
     * @return HasMany<Portfolio, $this>
     */
    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class, 'user_id');
    }

    /**
     * @return HasMany<UserProvider, $this>
     */
    public function providers(): HasMany
    {
        return $this->hasMany(UserProvider::class, 'user_id');
    }

    /**
     * @return HasMany<Security, $this>
     */
    public function securities(): HasMany
    {
        return $this->hasMany(Security::class, 'user_id');
    }

    /**
     * @return HasManyThrough<Subscription, Account, $this>
     */
    public function subscriptions(): HasManyThrough
    {
        return $this->hasManyThrough(Subscription::class, Account::class);
    }

    /**
     * @return HasManyThrough<Trade, Account, $this>
     */
    public function trades(): HasManyThrough
    {
        return $this->hasManyThrough(Trade::class, Account::class);
    }

    /**
     * @return HasManyThrough<Transaction, Account, $this>
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Account::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function getFilamentName(): string
    {
        return $this->full_name ?? $this->username;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        $this->save();
    }

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->getFilamentName();
    }

    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    /**
     * {@inheritDoc}
     */
    public function getFileFields(): array
    {
        return ['avatar'];
    }

    public function getFileDisk(): string
    {
        return 'public';
    }

    protected static function booted(): void
    {
        self::creating(function (User $user): void {
            $user->locale = app()->getLocale();
        });

        self::observe(FileCleanupObserver::class);
    }
}
