<?php

declare(strict_types=1);

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

        $this->freezeTime();
    })
    ->in('Feature', 'Unit');

function asUser(): void
{
    actingAs(User::factory()->verified()->create());
}

function asAdmin(): void
{
    actingAs(User::factory()->verified()->admin()->create());
}
