<?php

namespace App\Filament\Resources\BankAccountTransactionResource\Pages;

use App\Filament\Resources\BankAccountTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBankAccountTransaction extends EditRecord
{
    protected static string $resource = BankAccountTransactionResource::class;

    public function getTitle(): string
    {
        return __('resources.bank_account_transactions.navigation_label');
    }

    public function getHeading(): string
    {
        return __('resources.bank_account_transactions.navigation_label');
    }

    public function getSubheading(): string
    {
        return __('resources.bank_account_transactions.edit_heading');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading(__('resources.bank_account_transactions.delete_heading')),

        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
