<?php

namespace App\Filament\Imports;

use App\Models\Account;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AccountImporter extends Importer
{
    protected static ?string $model = Account::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('account.columns.name'))
                ->exampleHeader(__('account.columns.name'))
                ->examples(__('account.columns.name_examples'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('currency')
                ->label(__('account.columns.currency'))
                ->exampleHeader(__('account.columns.currency'))
                ->examples(__('account.columns.currency_examples'))
                ->fillRecordUsing(function (Account $record, string $state): void {
                    $record->currency = Account::getCurrency($state);
                }),
            ImportColumn::make('description')
                ->label(__('account.columns.description'))
                ->exampleHeader(__('account.columns.description'))
                ->examples(__('account.columns.description_examples'))
                ->rules(['max:1000']),
        ];
    }

    public function resolveRecord(): ?Account
    {
        return Account::firstOrNew([
            'name' => trim($this->data['name']),
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('account.notifications.import.body_heading') . "\n\r" .
            __('account.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('account.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'account-import';
    }
}