<?php

declare(strict_types=1);

use App\Services\Oidc\OidcProvider;
use Illuminate\Support\Facades\Http;
use SocialiteProviders\Manager\Config;

function makeOidcProvider(string $baseUrl = 'https://idp.test'): OidcProvider
{
    $provider = new OidcProvider(request(), 'client-id', 'client-secret', 'https://app.test/callback');
    $provider->setConfig(new Config('client-id', 'client-secret', 'https://app.test/callback', ['base_url' => $baseUrl]));

    return $provider;
}

/**
 * @param  array<int, mixed>  $args
 */
function callProtected(object $object, string $method, array $args = []): mixed
{
    $reflection = new ReflectionMethod($object, $method);

    return $reflection->invoke($object, ...$args);
}

it('exposes its additional config keys', function (): void {
    expect(OidcProvider::additionalConfigKeys())->toBe(['base_url']);
});

it('maps a raw oidc payload to a socialite user', function (): void {
    $user = callProtected(makeOidcProvider(), 'mapUserToObject', [[
        'sub' => 'abc-123',
        'given_name' => 'Jane',
        'family_name' => 'Doe',
        'preferred_username' => 'jane',
        'email' => 'jane@idp.test',
        'picture' => 'https://idp.test/avatar.png',
        'locale' => 'de',
    ]]);

    expect($user->getId())->toBe('abc-123')
        ->and($user->getEmail())->toBe('jane@idp.test')
        ->and($user->getAvatar())->toBe('https://idp.test/avatar.png');
});

it('builds the auth and token urls from the discovery document', function (): void {
    Http::fake([
        'https://idp.test/.well-known/openid-configuration' => Http::response([
            'authorization_endpoint' => 'https://idp.test/authorize',
            'token_endpoint' => 'https://idp.test/token',
            'userinfo_endpoint' => 'https://idp.test/userinfo',
        ], 200),
    ]);

    $provider = makeOidcProvider();

    expect(callProtected($provider, 'getAuthUrl', ['state-123']))->toStartWith('https://idp.test/authorize')
        ->and(callProtected($provider, 'getTokenUrl'))->toBe('https://idp.test/token');
});

it('throws when the discovery document misses a required key', function (): void {
    Http::fake(['*' => Http::response(['token_endpoint' => 'https://idp.test/token'], 200)]);

    expect(fn (): mixed => callProtected(makeOidcProvider(), 'getAuthUrl', ['s']))
        ->toThrow(RuntimeException::class);
});

it('throws when a discovery value is not a string', function (): void {
    Http::fake(['*' => Http::response(['authorization_endpoint' => ['nested']], 200)]);

    expect(fn (): mixed => callProtected(makeOidcProvider(), 'getAuthUrl', ['s']))
        ->toThrow(RuntimeException::class);
});

it('throws when the discovery document cannot be loaded', function (): void {
    Http::fake(['*' => Http::response('', 500)]);

    expect(fn (): mixed => callProtected(makeOidcProvider(), 'getTokenUrl'))
        ->toThrow(RuntimeException::class);
});
