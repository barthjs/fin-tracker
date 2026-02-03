<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Resources\Pages\ListRecords;

final class ListSubscriptions extends ListRecords
{
    use HasResourceActions;

    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::createAction()
                /** @phpstan-ignore-next-line */
                ->action(fn (SubscriptionService $service, array $data): Subscription => $service->create($data)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Todo
            // SubscriptionStats::class,
            // SubscriptionCategoryChart::class,
        ];
    }
}
