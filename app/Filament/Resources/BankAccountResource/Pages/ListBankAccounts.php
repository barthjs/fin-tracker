<?php

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Imports\BankAccountImporter;
use App\Filament\Resources\BankAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;

    public function getTitle(): string
    {
        return __('resources.bank_accounts.navigation_label');
    }

    public function getHeading(): string
    {
        return __('resources.bank_accounts.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('resources.bank_accounts.create_label'))
                ->modalHeading(__('resources.bank_accounts.create_heading')),
            Actions\ImportAction::make()
                ->label('import')
                ->importer(BankAccountImporter::class)
        ];
    }
}
