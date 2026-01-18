<?php

declare(strict_types=1);

namespace App\Services\Oidc;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use RuntimeException;
use SocialiteProviders\Manager\ConfigTrait;
use SocialiteProviders\Manager\OAuth2\User;

final class OidcProvider extends AbstractProvider implements ProviderInterface
{
    use ConfigTrait;

    public const string IDENTIFIER = 'OIDC';

    /** @var array<string> */
    protected $scopes = ['openid', 'profile', 'email'];

    protected $scopeSeparator = ' ';

    /** @var array<string, mixed>|null */
    private ?array $discoveryConfig = null;

    /**
     * @return array<string>
     */
    public static function additionalConfigKeys(): array
    {
        return ['base_url'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(mixed $state): string
    {
        return $this->buildAuthUrlFromBase($this->getDiscoveryEndpoint('authorization_endpoint'), $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->getDiscoveryEndpoint('token_endpoint');
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    protected function getUserByToken(mixed $token): array
    {
        $response = $this->getHttpClient()
            ->get($this->getDiscoveryEndpoint('userinfo_endpoint'), [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]);

        /** @var array<string, mixed> $data */
        $data = json_decode((string) $response->getBody(), true);

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * @param  array<string, mixed>  $user
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'given_name' => $user['given_name'] ?? null,
            'family_name' => $user['family_name'] ?? null,
            'name' => $user['full_name'] ?? null,
            'nickname' => $user['nickname'] ?? null,
            'preferred_username' => $user['preferred_username'] ?? null,
            'email' => $user['email'] ?? null,
            'avatar' => $user['picture'] ?? null,
            'locale' => $user['locale'] ?? null,
        ]);
    }

    private function getDiscoveryEndpoint(string $key): string
    {
        $config = $this->getDiscoveryConfig();
        if (! array_key_exists($key, $config)) {
            throw new RuntimeException("OIDC discovery config is missing the required key: $key");
        }

        $value = $config[$key];
        if (! is_string($value)) {
            throw new RuntimeException("OIDC discovery key $key must be a string, ".gettype($value).' given.');
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function getDiscoveryConfig(): array
    {
        if ($this->discoveryConfig !== null) {
            return $this->discoveryConfig;
        }

        $baseUrl = $this->getConfig('base_url');
        if (! is_string($baseUrl) || empty($baseUrl)) {
            throw new InvalidArgumentException('Missing or invalid base_url');
        }

        $baseUrl = mb_rtrim($baseUrl, '/');

        $cachedConfig = Cache::remember(
            'oidc_discovery_'.hash('sha256', $baseUrl),
            now()->addDay(),
            function () use ($baseUrl): ?array {
                $response = Http::get($baseUrl.'/.well-known/openid-configuration');
                if (! $response->successful()) {
                    return null;
                }

                $json = $response->json();

                return is_array($json) ? $json : null;
            }
        );

        if (! is_array($cachedConfig)) {
            throw new RuntimeException("Could not load valid OIDC discovery configuration from $baseUrl");
        }

        /** @var array<string, mixed> $cachedConfig */
        return $this->discoveryConfig = $cachedConfig;
    }
}
