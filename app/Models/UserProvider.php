<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\UserProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

/**
 * @property-read string $id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property string $user_id
 * @property string $provider_name
 * @property string $provider_id
 * @property-read User $user
 */
#[Table(name: 'sys_user_providers')]
final class UserProvider extends Model
{
    /** @use HasFactory<UserProviderFactory> */
    use HasFactory;

    use HasUlids;

    public static function findForProvider(string $provider, SocialiteUserContract $oauthUser): ?self
    {
        return self::query()
            ->where('provider_name', $provider)
            ->where('provider_id', $oauthUser->getId())
            ->first();
    }

    public static function createForProvider(string $provider, SocialiteUserContract $oauthUser, User $user): self
    {
        return self::query()
            ->create([
                'user_id' => $user->id,
                'provider_name' => $provider,
                'provider_id' => $oauthUser->getId(),
            ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
