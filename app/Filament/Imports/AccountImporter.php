<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Account;
use App\Services\AccountService;
use App\Tools\Convertor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Builder;

final class AccountImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Account::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn()
                ->examples(__('account.import.examples.name')),

            self::currencyColumn(),

            self::descriptionColumn()
                ->examples(__('account.import.examples.description')),

            self::colorColumn(),
            self::statusColumn(),

            ImportColumn::make('notify_on_low_balance')
                ->label(__('account.fields.notify_on_low_balance'))
                ->castStateUsing(fn (?string $state): bool => (bool) $state),

            ImportColumn::make('low_balance_threshold')
                ->label(__('account.fields.low_balance_threshold'))
                ->rules(['nullable', 'max:1e9'])
                ->castStateUsing(fn (?string $state): float => Convertor::formatNumber($state ?? '0')),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('account.import.body_heading')."\n\r".
            __('account.import.body_success').number_format($import->successful_rows);

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= "\n\r".__('account.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): Account
    {
        return Account::query()
            ->where('user_id', auth()->id())
            ->where('name', $this->data['name'])
            ->where('currency', $this->data['currency'])
            ->unless(empty($this->data['color']), function (Builder $query): void {
                $query->where('color', $this->data['color']);
            })
            ->first() ?? new Account([
                'name' => $this->data['name'],
                'currency' => $this->data['currency'],
                'color' => $this->data['color'] ?? mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => auth()->id(),
            ]);
    }

    /**
     * Saves the account through the service, so low-balance checks run like for accounts created in the panel.
     */
    public function saveRecord(): void
    {
        $service = resolve(AccountService::class);

        /** @var Account $account */
        $account = $this->record;

        if ($account->exists) {
            $this->record = $service->update($account, $account->getDirty());

            return;
        }

        $this->record = $service->create($account->getAttributes());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getJobBatchName(): string
    {
        return 'account-import';
    }
}
