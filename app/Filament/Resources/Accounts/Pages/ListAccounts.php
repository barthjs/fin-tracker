<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Exports\AccountExporter;
use App\Filament\Imports\AccountImporter;
use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    public function getTitle(): string
    {
        return __('account.navigation_label');
    }

    public function getHeading(): string
    {
        return __('account.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('account.buttons.create_button_label'))
                ->modalHeading(__('account.buttons.create_heading')),
            ImportAction::make()
                ->icon('tabler-table-import')
                ->label(__('table.import'))
                ->color('warning')
                ->modalHeading(__('account.buttons.import_heading'))
                ->importer(AccountImporter::class)
                ->failureNotificationTitle(__('account.notifications.import.failure_heading'))
                ->successNotificationTitle(__('account.notifications.import.success_heading'))
                ->fileRules([File::types(['csv'])->max(1024)]),
            ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('account.buttons.export_heading'))
                ->exporter(AccountExporter::class)
                ->failureNotificationTitle(__('account.notifications.export.failure_heading'))
                ->successNotificationTitle(__('account.notifications.export.success_heading'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes()->where('user_id', auth()->id())),
        ];
    }
}
