<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\CategoryGroup;
use App\Enums\Currency;
use App\Enums\NotificationEventType;
use App\Enums\PeriodUnit;
use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Portfolios\PortfolioResource;
use App\Filament\Resources\Securities\SecurityResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Subscription;
use App\Services\AccountService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

trait HasResourceFormFields
{
    public static function nameField(?string $name = 'name'): TextInput
    {
        return TextInput::make($name)
            ->label(__('fields.name'))
            ->autofocus()
            ->required()
            ->maxLength(255);
    }

    public static function currencyField(?string $name = 'currency'): Select
    {
        return Select::make($name)
            ->label(__('fields.currency'))
            ->options(Currency::class)
            ->default(Currency::getCurrency())
            ->selectablePlaceholder(false)
            ->required()
            ->searchable();
    }

    public static function colorField(?string $name = 'color'): ColorPicker
    {
        return ColorPicker::make($name)
            ->label(__('fields.color'))
            ->default(mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))))
            ->required()
            ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/')
            ->validationMessages(['regex' => __('validation.hex_color', ['attribute' => __('fields.color')])]);
    }

    public static function statusToggleField(?string $name = 'is_active'): Toggle
    {
        return Toggle::make($name)
            ->label(__('fields.status'))
            ->default(true)
            ->inline(false);
    }

    public static function logoField(string $directory, ?string $name = 'logo'): FileUpload
    {
        return FileUpload::make($name)
            ->label(__('fields.logo'))
            ->avatar()
            ->imageEditor()
            ->circleCropper()
            ->moveFiles()
            ->directory($directory)
            ->visibility('public')
            ->maxSize(1024);
    }

    public static function descriptionField(?string $name = 'description'): Textarea
    {
        return Textarea::make($name)
            ->label(__('fields.description'))
            ->autosize()
            ->rows(4)
            ->maxLength(1000);
    }

    public static function categoryGroupField(string $column = 'group'): Select
    {
        return Select::make($column)
            ->label(__('category.fields.group'))
            ->options(CategoryGroup::class)
            ->default(CategoryGroup::VarExpenses)
            ->selectablePlaceholder(false)
            ->required();
    }

    public static function dateTimePickerField(string $name = 'date_time'): DateTimePicker
    {
        return DateTimePicker::make($name)
            ->label(__('fields.date_time'))
            ->autofocus()
            ->seconds(false)
            ->default(today()->timezone(auth()->user()->timezone)->startOfDay())
            ->formatStateUsing(fn (?string $state): string => Date::parse($state)->timezone(auth()->user()->timezone)->toDateTimeString())
            ->dehydrateStateUsing(fn (?string $state): string => Date::parse($state, auth()->user()->timezone)->utc()->toDateTimeString())
            ->required();
    }

    public static function amountField(string $name = 'amount'): TextInput
    {
        return TextInput::make($name)
            ->label(__('fields.amount'))
            ->numeric()
            ->step(0.01)
            ->minValue(0.0)
            ->maxValue(1e9)
            ->suffix(fn (Get $get): ?string => self::accountCurrencySuffix($get))
            ->required();
    }

    public static function tradeAmountField(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->default(0.0)
            ->minValue(0.0)
            ->maxValue(1e9)
            ->live(true, 500);
    }

    public static function typeSelectField(string $name = 'type'): Select
    {
        return Select::make($name)
            ->label(__('fields.type'))
            ->selectablePlaceholder(false)
            ->live()
            ->required();
    }

    public static function notesField(?string $name = 'notes'): Textarea
    {
        return Textarea::make($name)
            ->label(__('fields.notes'))
            ->columnSpanFull()
            ->autosize()
            ->maxLength(255);
    }

    public static function accountSelectField(string $name = 'account_id'): Select
    {
        return Select::make($name)
            ->label(Str::ucfirst(__('account.label')))
            ->selectablePlaceholder(false)
            ->relationship('account', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->getOptionLabelUsing(fn (?string $value): ?string => Account::query()->find($value)?->name)
            ->preload()
            ->searchable()
            ->required()
            ->createOptionForm(AccountResource::getFormFields())
            ->createOptionUsing(function (AccountService $service, array $data): string {
                /** @var array<string, mixed> $data */
                return $service->create($data)->id;
            });
    }

    public static function categorySelectField(string $column = 'category_id'): Select
    {
        return Select::make($column)
            ->label(Str::ucfirst(__('category.label')))
            ->selectablePlaceholder(false)
            ->relationship(
                'category',
                'name',
                function (Builder $query, Get $get): Builder {
                    $query->where('is_active', true);

                    $type = $get('type');
                    if ($type !== null) {
                        $query->where('type', $type);
                    }

                    return $query;
                }
            )
            ->getOptionLabelUsing(fn (?string $value): ?string => Category::query()->find($value)?->name)
            ->preload()
            ->searchable()
            ->required()
            ->createOptionForm(CategoryResource::getFormFields());
    }

    public static function portfolioSelectField(string $column = 'portfolio_id'): Select
    {
        return Select::make($column)
            ->label(Str::ucfirst(__('portfolio.label')))
            ->selectablePlaceholder(false)
            ->relationship('portfolio', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->getOptionLabelUsing(fn (?string $value): ?string => Portfolio::query()->find($value)?->name)
            ->preload()
            ->searchable()
            ->required()
            ->createOptionForm(PortfolioResource::getFormFields());
    }

    public static function securitySelectField(string $column = 'security_id'): Select
    {
        return Select::make($column)
            ->label(Str::ucfirst(__('security.label')))
            ->selectablePlaceholder(false)
            ->relationship('security', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->getOptionLabelUsing(fn (?string $value): ?string => Security::query()->find($value)?->name)
            ->preload()
            ->searchable()
            ->required()
            ->createOptionForm(SecurityResource::getFormFields());
    }

    public static function subscriptionSelectField(string $column = 'subscription_id'): Select
    {
        return Select::make($column)
            ->label(Str::ucfirst(__('subscription.label')))
            ->relationship('subscription', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->getOptionLabelUsing(fn (?string $value): ?string => Subscription::query()->find($value)?->name)
            ->preload()
            ->searchable()
            ->createOptionForm(SubscriptionResource::getFormFields());
    }

    /**
     * Returns the currency of the account selected in the form, as suffix for amounts booked on that account.
     */
    public static function accountCurrencySuffix(Get $get): ?string
    {
        return Account::query()->whereKey($get('account_id'))->first()?->currency->value;
    }

    /**
     * Returns the currency selected in the form itself, as suffix for amounts of the edited record.
     */
    public static function formCurrencySuffix(Get $get): ?string
    {
        $currency = $get('currency');

        return $currency instanceof Currency ? $currency->value : null;
    }

    /**
     * Number of period units between two occurrences, with the selected unit as suffix.
     */
    public static function periodFrequencyField(string $label): TextInput
    {
        return TextInput::make('period_frequency')
            ->label($label)
            ->numeric()
            ->default(1)
            ->minValue(1)
            ->maxValue(365)
            ->live(onBlur: true)
            ->suffix(function (Get $get): ?string {
                $unit = $get('period_unit');
                $frequency = $get('period_frequency');

                return $unit instanceof PeriodUnit
                    ? trans_choice('subscription.units.'.$unit->value, is_numeric($frequency) ? (int) $frequency : 1)
                    : null;
            });
    }

    /**
     * Toggle that enables a notification and preselects the default targets when it is switched on without targets.
     */
    public static function notificationToggleField(string $name, string $targetsField): Toggle
    {
        return Toggle::make($name)
            ->default(false)
            ->inline(false)
            ->live()
            ->afterStateUpdated(function (bool $state, Get $get, Set $set) use ($targetsField): void {
                if (! $state) {
                    return;
                }

                $targets = $get($targetsField);

                if (is_array($targets) && $targets !== []) {
                    return;
                }

                $set($targetsField, self::defaultNotificationTargetIds());
            });
    }

    /**
     * Active notification targets for one event of the record, shown while the notification toggle is on.
     */
    public static function notificationTargetsField(string $name, NotificationEventType $event, string $toggleField): CheckboxList
    {
        return CheckboxList::make($name)
            ->label(Str::ucfirst(__('notification_target.plural_label')))
            ->visible(fn (Get $get): bool => (bool) $get($toggleField))
            ->options(fn (): array => NotificationTarget::query()->where('is_active', true)->pluck('name', 'id')->toArray())
            ->columns(3)
            ->gridDirection('row')
            ->bulkToggleable()
            ->default(fn (): array => self::defaultNotificationTargetIds())
            ->loadStateFromRelationshipsUsing(
                function (Account|Budget|Subscription $record, CheckboxList $component) use ($event): void {
                    $targetIds = $record->notificationAssignments()
                        ->where('event_type', $event)
                        ->pluck('notification_target_id')
                        ->toArray();

                    $component->state($targetIds);
                }
            );
    }

    /**
     * Returns the ids of the active notification targets that are marked as default.
     *
     * @return array<int, string>
     */
    private static function defaultNotificationTargetIds(): array
    {
        /** @var array<int, string> $targetIds */
        $targetIds = NotificationTarget::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->pluck('id')
            ->all();

        return $targetIds;
    }
}
