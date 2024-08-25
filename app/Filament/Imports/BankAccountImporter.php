<?php

namespace App\Filament\Imports;

use App\Models\BankAccount;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class BankAccountImporter extends Importer
{
    protected static ?string $model = BankAccount::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description')
                ->rules(['max:1000']),
        ];
    }

    public function resolveRecord(): ?BankAccount
    {
        return new BankAccount();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your bank account import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
