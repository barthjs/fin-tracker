<?php

namespace App\Filament\Resources\BankAccountTransactionResource\Pages;

use App\Filament\Resources\BankAccountTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBankAccountTransaction extends CreateRecord
{
    protected static string $resource = BankAccountTransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
