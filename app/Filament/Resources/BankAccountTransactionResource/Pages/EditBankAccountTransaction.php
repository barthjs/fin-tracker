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
        return __('bank_account_transaction.navigation_label');
    }

    public function getHeading(): string
    {
        return __('bank_account_transaction.navigation_label');
    }

    public function getSubheading(): string
    {
        return __('bank_account_transaction.buttons.edit_heading');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading(__('bank_account_transaction.buttons.delete_heading')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
