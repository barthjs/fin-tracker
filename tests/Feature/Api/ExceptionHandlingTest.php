<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Models\Account;
use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;

test('returns a json 404 for unknown api routes', function (): void {
    getJson('/api/does-not-exist')
        ->assertNotFound()
        ->assertJson(['message' => 'Not Found']);
});

test('returns a json 405 for an unsupported method', function (): void {
    putJson(route('api.version'))
        ->assertStatus(405)
        ->assertJson(['message' => 'Method Not Allowed']);
});

test('returns a json 404 for a missing model', function (): void {
    $user = User::factory()->verified()->create();
    actingAsWithAbilities($user, ApiAbility::ACCOUNT->all());

    getJson(route('api.accounts.show', 'non-existent-id'))
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});

test('returns an unauthenticated json response', function (): void {
    getJson(route('api.accounts.index'))
        ->assertUnauthorized()
        ->assertJsonStructure(['message']);
});

test('returns a forbidden json response without ability', function (): void {
    $user = User::factory()->verified()->create();
    Account::factory()->create(['user_id' => $user->id]);
    actingAsWithAbilities($user);

    getJson(route('api.accounts.index'))
        ->assertForbidden()
        ->assertJsonStructure(['message']);
});
