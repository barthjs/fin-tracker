<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PersonalAccessTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Table(name: 'sys_personal_access_tokens')]
final class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    /** @use HasFactory<PersonalAccessTokenFactory> */
    use HasFactory;

    use HasUlids;

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
