<?php

namespace App\Filament\Resources\BankAccountTransactionResource\Pages;

use App\Filament\Resources\BankAccountTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBankAccountTransaction extends EditRecord
{
    protected static string $resource = BankAccountTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
