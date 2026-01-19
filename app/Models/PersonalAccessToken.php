<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

final class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    use HasUlids;

    protected $table = 'sys_personal_access_tokens';

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function lastUsedAt(): Attribute
    {
        return Attribute::make(
            set: function (mixed $value): void {},
        );
    }
}
