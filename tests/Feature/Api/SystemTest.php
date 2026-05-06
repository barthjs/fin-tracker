<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('System API', function (): void {
    test('up route returns ok for everyone', function (): void {
        getJson(route('api.up'))
            ->assertOk()
            ->assertJson(['status' => 'ok'])
            ->assertJsonCount(1);
    });

    test('version route is protected and returns version', function (): void {
        getJson(route('api.version'))
            ->assertUnauthorized();

        $user = User::factory()->verified()->create();
        actingAsWithAbilities($user);

        getJson(route('api.version'))
            ->assertOk()
            ->assertJson(['version' => config('app.version')]);
    });

    test('health route is protected and returns health status', function (): void {
        getJson(route('api.health'))
            ->assertUnauthorized();

        $user = User::factory()->verified()->create();
        actingAsWithAbilities($user);

        getJson(route('api.health'))
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'environment',
            ])
            ->assertJson([
                'status' => 'ok',
                'environment' => 'testing',
            ]);
    });

    test('webhook is forbidden when debug mode is disabled', function (): void {
        config()->set('app.debug', false);

        postJson(route('api.webhook'), ['event' => 'ping'])
            ->assertForbidden();
    });

    test('webhook is forbidden without a valid signature', function (): void {
        config()->set('app.debug', true);

        postJson(route('api.webhook'), ['event' => 'ping'])
            ->assertForbidden();

        postJson(route('api.webhook'), ['event' => 'ping'], ['X-Signature-256' => 'sha256=invalid'])
            ->assertForbidden();
    });

    test('webhook returns the payload with a valid signature', function (): void {
        config()->set('app.debug', true);

        $payload = ['event' => 'ping'];
        $signature = hash_hmac('sha256', (string) json_encode($payload), config()->string('app.webhook_secret'));

        postJson(route('api.webhook'), $payload, ['X-Signature-256' => 'sha256='.$signature])
            ->assertOk()
            ->assertJson($payload);
    });
});
