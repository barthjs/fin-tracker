<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts;

use App\Enums\Currency;
use App\Enums\NotificationEventType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceInfolistEntries;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Accounts\RelationManagers\SubscriptionsRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\TradesRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\TransactionsRelationManager;
use App\Filament\Resources\Users\RelationManagers\AccountsRelationManager;
use App\Models\Account;
use App\Services\AccountService;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class AccountResource extends Resource
{
    use HasResourceActions;
    use HasResourceFormFields;
    use HasResourceInfolistEntries;
    use HasResourceTableColumns;

    protected static ?string $model = Account::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-building-bank';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('account.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('account.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::getFormFields());
    }

    /**
     * Returns the account form, optionally without the low-balance notification that belongs to the current user.
     *
     * @return array<int, Field|Grid|Section>
     */
    public static function getFormFields(bool $withNotifications = true): array
    {
        $fields = [
            self::nameField(),
            self::currencyField(),
            self::colorField(),
            self::statusToggleField(),
            self::logoField('accounts'),
            self::descriptionField(),
        ];

        if (! $withNotifications) {
            return $fields;
        }

        $fields[] = Section::make()
            ->columnSpanFull()
            ->schema([
                Grid::make(2)
                    ->schema([
                        self::notificationToggleField('notify_on_low_balance', 'low_balance_targets')
                            ->label(__('account.fields.notify_on_low_balance')),

                        TextInput::make('low_balance_threshold')
                            ->label(__('account.fields.low_balance_threshold'))
                            ->visible(fn (Get $get): bool => (bool) $get('notify_on_low_balance'))
                            ->numeric()
                            ->default(0)
                            ->suffix(fn (Get $get): ?string => self::formCurrencySuffix($get))
                            ->required(),
                    ]),

                self::notificationTargetsField('low_balance_targets', NotificationEventType::ACCOUNT_LOW_BALANCE, 'notify_on_low_balance'),
            ]);

        return $fields;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        self::numericEntry('balance')
                            ->label(__('account.fields.balance'))
                            ->color(fn (Account $record): string => $record->balanceColor)
                            ->money(fn (Account $record): string => $record->currency->value),

                        self::numericEntry('low_balance_threshold')
                            ->label(__('account.fields.low_balance_threshold'))
                            ->visible(fn (Account $record): bool => $record->notify_on_low_balance)
                            ->money(fn (Account $record): string => $record->currency->value),

                        self::descriptionEntry(),
                    ])
                    ->columns([
                        'default' => 2,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->modelLabel(__('account.label'))
            ->pluralModelLabel(__('account.plural_label'))
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                if ($table->getActiveFiltersCount() === 0) {
                    return $query->where('is_active', true);
                }

                return $query;
            })
            ->columns(self::getTableColumns())
            ->filters([
                self::inactiveFilter(),
            ]);
    }

    /**
     * @return array<int, Column>
     */
    public static function getTableColumns(): array
    {
        $hidden = AccountsRelationManager::class;

        return [
            self::logoAndNameColumn()
                ->state(fn (Account $record): array => [
                    'logo' => $record->logo,
                    'name' => $record->name,
                ]),

            TextColumn::make('balance')
                ->hiddenOn($hidden)
                ->label(__('account.fields.balance'))
                ->badge()
                ->color(fn (Account $record): string => $record->balanceColor)
                ->money(fn (Account $record): string => $record->currency->value)
                ->summarize(Sum::make()->money(Currency::getCurrency()))
                ->searchable()
                ->sortable()
                ->toggleable(),

            self::descriptionColumn()
                ->hiddenOn($hidden),

            self::currencyColumn(),
            self::statusColumn(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
            TradesRelationManager::class,
            SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'view' => ViewAccount::route('/{record}'),
        ];
    }

    public static function configureCreateAction(): CreateAction
    {
        return self::createAction()
            // @phpstan-ignore-next-line
            ->using(fn (AccountService $service, array $data): Account => $service->create($data));
    }
}
