<?php

namespace App\Models;

use App\Models\Scopes\AccountScope;
use App\Models\Scopes\CategoryScope;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable;

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

    protected static function booted(): void
    {
        static::created(function (User $user) {
            Account::withoutGlobalScopes([AccountScope::class])->firstOrCreate(['name' => 'Demo', 'user_id' => $user->id]);
            Category::withoutGlobalScopes([CategoryScope::class])->firstOrCreate(['name' => 'Demo', 'user_id' => $user->id]);
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

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active == true;
    }

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
