<?php

declare(strict_types=1);

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class, WithCachedConfig::class, WithCachedRoutes::class)
    ->beforeEach(function (): void {
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();
        Str::createRandomStringsNormally();
        Str::createUlidsNormally();
        Str::createUuidsNormally();

        /** @phpstan-ignore-next-line */
        $this->freezeTime();
    })
    ->in('Feature', 'Unit');

/**
 * @param  array<int, string>  $abilities
 */
function actingAsWithAbilities(User $user, array $abilities = []): void
{
    $token = new PersonalAccessToken([
        'name' => 'Token',
        'token' => hash('sha256', 'token'),
        'abilities' => $abilities,
    ]);

    $user->withAccessToken($token);

    actingAs($user, 'sanctum');
}

function asUser(): void
{
    actingAs(User::factory()->verified()->create());
}

function asAdmin(): void
{
    actingAs(User::factory()->verified()->admin()->create());
}
