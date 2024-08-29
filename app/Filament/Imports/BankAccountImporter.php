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
                ->label(__('bank_account.columns.name'))
                ->exampleHeader(__('bank_account.columns.name'))
                ->examples(__('bank_account.columns.name_examples'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('currency')
                ->label(__('bank_account.columns.currency'))
                ->exampleHeader(__('bank_account.columns.currency'))
                ->examples(__('bank_account.columns.currency_examples'))
                ->fillRecordUsing(function (BankAccount $record, string $state): void {
                    $record->currency = BankAccount::getCurrency($state);
                }),
            ImportColumn::make('description')
                ->label(__('bank_account.columns.description'))
                ->exampleHeader(__('bank_account.columns.description'))
                ->examples(__('bank_account.columns.description_examples'))
                ->rules(['max:1000']),
        ];
    }

    public function resolveRecord(): ?BankAccount
    {
        return BankAccount::firstOrNew([
            'name' => $this->data['name'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('bank_account.notifications.import.body_heading') . "\n\r" .
            __('bank_account.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('bank_account.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'bank-account-import';
    }
}
