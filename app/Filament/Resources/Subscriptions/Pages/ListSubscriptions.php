<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\SubscriptionExporter;
use App\Filament\Imports\SubscriptionImporter;
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

            self::importAction()
                ->modalHeading(__('subscription.import.modal_heading'))
                ->importer(SubscriptionImporter::class)
                ->failureNotificationTitle(__('subscription.import.failure_heading'))
                ->successNotificationTitle(__('subscription.import.success_heading')),

            self::exportAction()
                ->modalHeading(__('subscription.export.modal_heading'))
                ->exporter(SubscriptionExporter::class)
                ->failureNotificationTitle(__('subscription.export.failure_heading'))
                ->successNotificationTitle(__('subscription.export.success_heading')),
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
