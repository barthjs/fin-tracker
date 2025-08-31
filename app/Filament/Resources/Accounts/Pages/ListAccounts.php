<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\AccountExporter;
use App\Filament\Imports\AccountImporter;
use App\Filament\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\ListRecords;

final class ListAccounts extends ListRecords
{
    use HasResourceActions;

    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::createAction(),

            self::importAction()
                ->modalHeading(__('account.import.modal_heading'))
                ->importer(AccountImporter::class)
                ->failureNotificationTitle(__('account.import.failure_heading'))
                ->successNotificationTitle(__('account.import.success_heading')),

            self::exportAction()
                ->modalHeading(__('account.export.modal_heading'))
                ->exporter(AccountExporter::class)
                ->failureNotificationTitle(__('account.export.failure_heading'))
                ->successNotificationTitle(__('account.export.success_heading')),
        ];
    }
}
