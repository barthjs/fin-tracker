<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\PeriodUnit;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

final class SubscriptionImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Subscription::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn(),

            self::numericColumn('amount')
                ->label(__('fields.amount')),

            ImportColumn::make('period_unit')
                ->label(__('subscription.fields.period_unit'))
                ->requiredMapping()
                ->rules(['required', Rule::enum(PeriodUnit::class)]),

            ImportColumn::make('period_frequency')
                ->label(__('subscription.fields.period_frequency'))
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:1', 'max:365']),

            self::dateColumn('started_at')
                ->label(__('subscription.fields.started_at')),

            self::dateColumn('next_payment_date')
                ->label(__('subscription.fields.next_payment_date'))
                ->rules(['required', 'after_or_equal:started_at']),

            self::dateColumn('ended_at')
                ->label(__('subscription.fields.ended_at'))
                ->requiredMapping(false)
                ->rules(['nullable', 'after_or_equal:started_at']),

            ImportColumn::make('auto_generate_transaction')
                ->label(__('subscription.fields.auto_generate_transaction'))
                ->rules(['boolean']),

            ImportColumn::make('remind_before_payment')
                ->label(__('subscription.fields.remind_before_payment'))
                ->boolean()
                ->rules(['boolean']),

            ImportColumn::make('reminder_days_before')
                ->label(__('subscription.fields.reminder_days_before'))
                ->numeric()
                ->rules(['required_if:remind_before_payment,true', 'integer', 'min:1', 'max:30']),

            ImportColumn::make('account')
                ->label(__('account.label'))
                ->requiredMapping()
                ->relationship(resolveUsing: 'name')
                ->rules(['required']),

            ImportColumn::make('category')
                ->label(__('category.label'))
                ->requiredMapping()
                ->relationship(resolveUsing: 'name')
                ->rules(['required']),

            self::descriptionColumn(),
            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('subscription.import.body_heading')."\n\r".
            __('subscription.import.body_success').number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r".__('subscription.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): Model
    {
        return new Subscription;
    }

    public function saveRecord(): void
    {
        $service = app(SubscriptionService::class);

        $data = collect($this->record?->toArray() ?? [])
            ->except(['account', 'category'])
            ->toArray();

        /** @phpstan-ignore-next-line  */
        $this->record = $service->create($data);
    }

    public function getJobBatchName(): string
    {
        return 'subscription-import';
    }
}
