<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'sys_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'verified',
        'is_admin',
        'active',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'verified' => 'boolean',
        'is_admin' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Automatically creates a default 'Demo' account, category and portfolio
     * for each new user after they are created.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            Account::withoutGlobalScopes()->firstOrCreate(['name' => 'Demo', 'user_id' => $user->id]);
            Category::withoutGlobalScopes()->firstOrCreate(['name' => 'Demo', 'user_id' => $user->id]);
            Portfolio::withoutGlobalScopes()->firstOrCreate(['name' => 'Demo', 'user_id' => $user->id]);
            Security::withoutGlobalScopes()->firstOrCreate(['name' => 'Demo', 'user_id' => $user->id]);
        });

        static::updated(function (User $user) {
            $avatar = $user->getOriginal('avatar');
            if (is_null($user->avatar) && ! is_null($avatar) && Storage::disk('public')->exists($avatar)) {
                Storage::disk('public')->delete($avatar);
            }
        });

        static::deleted(function (User $user) {
            if (! is_null($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
        });
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'user_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'user_id');
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class, 'user_id');
    }

    public function securities(): HasMany
    {
        return $this->hasMany(Security::class, 'user_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active == true;
    }

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }
}
