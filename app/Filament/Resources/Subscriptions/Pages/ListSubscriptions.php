<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\SubscriptionExporter;
use App\Filament\Imports\SubscriptionImporter;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionsByAccountChart;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionsByCategoryChart;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionStats;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

final class ListSubscriptions extends ListRecords
{
    use ExposesTableToWidgets, HasResourceActions;

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
            SubscriptionStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SubscriptionsByAccountChart::class,
            SubscriptionsByCategoryChart::class,
        ];
    }
}
