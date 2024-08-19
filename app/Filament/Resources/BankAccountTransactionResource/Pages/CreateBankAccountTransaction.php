<?php

namespace App\Filament\Resources\BankAccountTransactionResource\Pages;

use App\Filament\Resources\BankAccountTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankAccountTransaction extends CreateRecord
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
        return __('resources.bank_account_transactions.create_heading');
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
