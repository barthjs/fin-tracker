<?php

namespace App\Filament\Imports;

use App\Models\BankAccountTransaction;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class BankAccountTransactionImporter extends Importer
{
    protected static ?string $model = BankAccountTransaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('date')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('bank_account_id'),
            ImportColumn::make('amount')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('destination')
                ->rules(['max:255']),
            ImportColumn::make('category_id'),
            ImportColumn::make('notes')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?BankAccountTransaction
    {
        return new BankAccountTransaction([
            'date' => $this->data['date'],
            'bank_account_id' => 1,
            'amount' => $this->data['amount'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your bank account transaction import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
