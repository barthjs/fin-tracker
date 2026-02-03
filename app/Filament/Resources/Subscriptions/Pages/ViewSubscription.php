<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Resources\Pages\ViewRecord;

final class ViewSubscription extends ViewRecord
{
    use HasResourceActions;

    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::editAction()
                /** @phpstan-ignore-next-line */
                ->action(fn (SubscriptionService $service, Subscription $record, array $data): Subscription => $service->update($record, $data)),

            self::deleteAction(),
        ];
    }
}
