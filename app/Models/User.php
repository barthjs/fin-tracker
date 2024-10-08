<?php

namespace App\Models;

use App\Models\Scopes\BankAccountScope;
use App\Models\Scopes\TransactionCategoryScope;
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
        static::created(function ($model) {
            BankAccount::withoutGlobalScopes([BankAccountScope::class])->firstOrCreate(['name' => 'Demo', 'user_id' => $model->id]);
            TransactionCategory::withoutGlobalScopes([TransactionCategoryScope::class])->firstOrCreate(['name' => 'Demo', 'user_id' => $model->id]);
        });
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'user_id');
    }

    public function transactionCategory(): HasMany
    {
        return $this->hasMany(TransactionCategory::class, 'user_id');
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
