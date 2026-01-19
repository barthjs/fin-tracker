<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\getJson;

describe('System API', function () {
    test('up route returns ok for everyone', function () {
        getJson(route('api.up'))
            ->assertOk()
            ->assertJson(['status' => 'ok'])
            ->assertJsonCount(1);
    });

    test('version route is protected and returns version', function () {
        getJson(route('api.version'))
            ->assertUnauthorized();

        asUser();
        $user = User::firstOrFail();
        actingAsWithAbilities($user);

        getJson(route('api.version'))
            ->assertOk()
            ->assertJson(['version' => config('app.version')]);
    });

    test('health route is protected and returns health status', function () {
        getJson(route('api.health'))
            ->assertUnauthorized();

        asUser();
        $user = User::firstOrFail();
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
});
