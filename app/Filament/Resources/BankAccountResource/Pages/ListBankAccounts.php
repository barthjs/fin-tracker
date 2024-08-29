<?php

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Exports\BankAccountExporter;
use App\Filament\Imports\BankAccountImporter;
use App\Filament\Resources\BankAccountResource;
use App\Models\Scopes\BankAccountScope;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;

    public function getTitle(): string
    {
        return __('bank_account.navigation_label');
    }

    public function getHeading(): string
    {
        return __('bank_account.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('bank_account.buttons.create_button_label'))
                ->modalHeading(__('bank_account.buttons.create_heading')),
            Actions\ImportAction::make()
                ->icon('tabler-table-import')
                ->label(__('table.import'))
                ->color('warning')
                ->modalHeading(__('bank_account.buttons.import_heading'))
                ->importer(BankAccountImporter::class)
                ->failureNotificationTitle(__('bank_account.notifications.import.failure_heading'))
                ->successNotificationTitle(__('bank_account.notifications.import.success_heading'))
                ->fileRules([
                    File::types(['csv'])->max(1024),
                ]),
            Actions\ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('bank_account.buttons.export_heading'))
                ->exporter(BankAccountExporter::class)
                ->failureNotificationTitle(__('bank_account.notifications.export.failure_heading'))
                ->successNotificationTitle(__('bank_account.notifications.export.success_heading'))
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([BankAccountScope::class])->where('user_id', auth()->id()))
        ];
    }
}
