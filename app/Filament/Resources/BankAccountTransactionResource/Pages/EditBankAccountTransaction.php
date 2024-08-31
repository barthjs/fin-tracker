<?php

namespace App\Filament\Resources\BankAccountTransactionResource\Pages;

use App\Filament\Resources\BankAccountTransactionResource;
use App\Models\BankAccount;
use App\Models\BankAccountTransaction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

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

    protected function handleRecordUpdate($record, array $data): Model
    {
        $oldAccountId = $record->getOriginal('bank_account_id');
        $record->update($data);
        if ($oldAccountId != $record->bank_account_id) {
            $sum = BankAccountTransaction::whereBankAccountId($oldAccountId)->sum('amount');
            BankAccount::whereId($oldAccountId)->update(['balance' => $sum]);

            $sum = BankAccountTransaction::whereBankAccountId($record->bank_account_id)->sum('amount');
            BankAccount::whereId($record->bank_account_id)->update(['balance' => $sum]);
        }
        return $record;
    }
}
