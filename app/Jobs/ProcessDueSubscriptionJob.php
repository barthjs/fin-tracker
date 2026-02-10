<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Throwable;

final class ProcessDueSubscriptionJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly Subscription $subscription
    ) {}

    public function uniqueId(): string
    {
        return $this->subscription->id;
    }

    public function handle(SubscriptionService $service): void
    {
        if (! $this->subscription->is_active || ! $this->subscription->auto_generate_transaction) {
            return;
        }

        app()->setLocale($this->subscription->user->locale);
        Number::useLocale($this->subscription->user->locale);

        $service->generateTransaction($this->subscription);
    }

    public function failed(Throwable $exception): void
    {
        Log::error($exception->getMessage());

        Notification::make()
            ->title(__('subscription.notifications.failed_title'))
            ->body(__('subscription.notifications.failed_body', ['name' => $this->subscription->name]))
            ->danger()
            ->persistent()
            ->sendToDatabase($this->subscription->user);
    }
}
