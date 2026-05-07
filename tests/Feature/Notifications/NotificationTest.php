<?php

declare(strict_types=1);

use App\Data\NotificationPayload;
use App\Enums\NotificationProviderType;
use App\Mail\NotificationMail;
use App\Models\Account;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\Subscription;
use App\Notifications\Channels\DynamicTargetChannel;
use App\Notifications\SubscriptionReminderNotification;
use App\Services\Notifications\Configs\DatabaseConfig;
use App\Services\Notifications\Configs\EmailConfig;
use App\Services\Notifications\Configs\GenericWebhookConfig;
use App\Services\Notifications\NotificationConfigFactory;
use App\Services\Notifications\NotificationStrategyFactory;
use App\Services\Notifications\Strategies\DatabaseStrategy;
use App\Services\Notifications\Strategies\EmailStrategy;
use App\Services\Notifications\Strategies\GenericWebhookStrategy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => asUser());

it('builds a notification payload array', function (): void {
    $withMeta = new NotificationPayload('Title', 'Body', ['key' => 'value']);
    $withoutMeta = new NotificationPayload('Title', 'Body');

    expect($withMeta->toArray())
        ->toMatchArray(['title' => 'Title', 'body' => 'Body', 'meta' => ['key' => 'value']])
        ->toHaveKeys(['timestamp', 'version'])
        ->and($withoutMeta->toArray())->not->toHaveKey('meta');
});

it('resolves the correct strategy for each provider type', function (): void {
    $factory = new NotificationStrategyFactory;

    expect($factory->make(NotificationProviderType::DATABASE))->toBeInstanceOf(DatabaseStrategy::class)
        ->and($factory->make(NotificationProviderType::EMAIL))->toBeInstanceOf(EmailStrategy::class)
        ->and($factory->make(NotificationProviderType::GENERIC_WEBHOOK))->toBeInstanceOf(GenericWebhookStrategy::class);
});

it('resolves the correct config for each provider type', function (): void {
    expect(NotificationConfigFactory::make(NotificationProviderType::DATABASE, []))->toBeInstanceOf(DatabaseConfig::class)
        ->and(NotificationConfigFactory::make(NotificationProviderType::EMAIL, ['email' => 'a@b.test']))->toBeInstanceOf(EmailConfig::class)
        ->and(NotificationConfigFactory::make(NotificationProviderType::GENERIC_WEBHOOK, ['url' => 'https://x.test']))->toBeInstanceOf(GenericWebhookConfig::class);
});

it('maps webhook config from and to array', function (): void {
    $config = GenericWebhookConfig::fromArray([
        'url' => 'https://x.test/hook',
        'secret' => 'secret',
        'verify_ssl' => false,
        'content_type' => 'form',
    ]);

    expect($config->url)->toBe('https://x.test/hook')
        ->and($config->secret)->toBe('secret')
        ->and($config->verifySsl)->toBeFalse()
        ->and($config->contentType)->toBe('form')
        ->and($config->toArray())->toMatchArray(['url' => 'https://x.test/hook', 'content_type' => 'form'])
        ->and(EmailConfig::fromArray([])->email)->toBe('')
        ->and(EmailConfig::fromArray(['email' => 'a@b.test'])->toArray())->toBe(['email' => 'a@b.test'])
        ->and(DatabaseConfig::fromArray([])->toArray())->toBe([]);
});

it('sends an email notification', function (): void {
    Mail::fake();

    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::EMAIL,
        'configuration' => ['email' => 'recipient@example.com'],
    ]);

    (new EmailStrategy)->send($target, new NotificationPayload('Hi', 'There'));

    Mail::assertQueued(NotificationMail::class);
});

it('skips the email notification when no email is configured', function (): void {
    Mail::fake();

    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::EMAIL,
        'configuration' => ['email' => ''],
    ]);

    (new EmailStrategy)->send($target, new NotificationPayload('Hi', 'There'));

    Mail::assertNothingSent();
});

it('wraps email sending failures in a runtime exception', function (): void {
    Mail::shouldReceive('to')->once()->andReturnSelf();
    Mail::shouldReceive('send')->once()->andThrow(new RuntimeException('smtp down'));

    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::EMAIL,
        'configuration' => ['email' => 'recipient@example.com'],
    ]);

    expect(fn () => (new EmailStrategy)->send($target, new NotificationPayload('Hi', 'There')))
        ->toThrow(RuntimeException::class);
});

it('sends a signed json webhook', function (): void {
    Http::fake();

    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::GENERIC_WEBHOOK,
        'configuration' => ['url' => 'https://x.test/hook', 'secret' => 'secret', 'content_type' => 'json'],
    ]);

    (new GenericWebhookStrategy)->send($target, new NotificationPayload('Hi', 'There', ['a' => 'b']));

    Http::assertSent(fn ($request): bool => $request->url() === 'https://x.test/hook'
        && $request->hasHeader('X-Signature-256')
        && str_contains((string) $request->header('Content-Type')[0], 'application/json'));
});

it('sends a form encoded webhook', function (): void {
    Http::fake();

    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::GENERIC_WEBHOOK,
        'configuration' => ['url' => 'https://x.test/form', 'secret' => 'secret', 'content_type' => 'form', 'verify_ssl' => false],
    ]);

    (new GenericWebhookStrategy)->send($target, new NotificationPayload('Hi', 'There'));

    Http::assertSent(fn ($request): bool => str_contains((string) $request->header('Content-Type')[0], 'application/x-www-form-urlencoded'));
});

it('logs a warning when no webhook secret is available', function (): void {
    Http::fake();
    config()->set('app.webhook_secret', '');

    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::GENERIC_WEBHOOK,
        'configuration' => ['url' => 'https://x.test/nosecret', 'secret' => null],
    ]);

    (new GenericWebhookStrategy)->send($target, new NotificationPayload('Hi', 'There'));

    Http::assertSent(fn ($request): bool => ! $request->hasHeader('X-Signature-256'));
});

it('throws when the webhook responds with an error', function (): void {
    Http::fake(['*' => Http::response('server error', 500)]);

    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::GENERIC_WEBHOOK,
        'configuration' => ['url' => 'https://x.test/fail', 'secret' => 'secret'],
    ]);

    expect(fn () => (new GenericWebhookStrategy)->send($target, new NotificationPayload('Hi', 'There')))
        ->toThrow(RuntimeException::class);
});

it('skips the webhook when no url is configured', function (): void {
    Http::fake();

    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::GENERIC_WEBHOOK,
        'configuration' => ['url' => ''],
    ]);

    (new GenericWebhookStrategy)->send($target, new NotificationPayload('Hi', 'There'));

    Http::assertNothingSent();
});

it('routes through the dynamic channel to the strategy', function (): void {
    $target = NotificationTarget::factory()->create(['type' => NotificationProviderType::DATABASE]);
    $subscription = subscriptionForReminder();
    $notification = new SubscriptionReminderNotification($subscription, $target);

    new DynamicTargetChannel(new NotificationStrategyFactory)->send($subscription->user, $notification);

    $this->assertDatabaseCount('notifications', 1);
});

it('does nothing when the target is inactive', function (): void {
    $target = NotificationTarget::factory()->create(['type' => NotificationProviderType::DATABASE, 'is_active' => false]);
    $subscription = subscriptionForReminder();
    $notification = new SubscriptionReminderNotification($subscription, $target);

    new DynamicTargetChannel(new NotificationStrategyFactory)->send($subscription->user, $notification);

    $this->assertDatabaseCount('notifications', 0);
});

it('builds the reminder notification payload', function (): void {
    $target = NotificationTarget::factory()->create();
    $subscription = subscriptionForReminder();
    $notification = new SubscriptionReminderNotification($subscription, $target);

    expect($notification->via())->toBe([DynamicTargetChannel::class])
        ->and($notification->getNotificationTarget()->id)->toBe($target->id);

    $payload = $notification->toNotificationPayload();

    expect($payload->title)->toContain($subscription->name)
        ->and($payload->metadata)->toHaveKey('subscription_id', $subscription->id);
});

it('exposes the mail envelope, content and attachments', function (): void {
    $mail = new NotificationMail('Subject', 'Body');

    expect($mail->envelope()->subject)->toBe('Subject')
        ->and($mail->content()->markdown)->toBe('mail.notification')
        ->and($mail->attachments())->toBe([]);
});

/**
 * @param  array<string, mixed>  $attributes
 */
function subscriptionForReminder(array $attributes = []): Subscription
{
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    return Subscription::factory()->create(array_merge([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'name' => 'Internet',
    ], $attributes))->load(['user', 'account']);
}
