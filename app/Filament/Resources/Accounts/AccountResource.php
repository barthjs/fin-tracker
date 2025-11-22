<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts;

use App\Enums\Currency;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceInfolistEntries;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Accounts\RelationManagers\TradesRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\TransactionsRelationManager;
use App\Filament\Resources\Users\RelationManagers\AccountsRelationManager;
use App\Models\Account;
use BackedEnum;
use Filament\Forms\Components\Field;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class AccountResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceInfolistEntries, HasResourceTableColumns;

    protected static ?string $model = Account::class;

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-bank-building';

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
     * @return array<int, Field>
     */
    public static function getFormFields(): array
    {
        return [
            self::nameField(),
            self::currencyField(),
            self::colorField(),
            self::statusToggleField(),
            self::logoField(directory: 'accounts'),
            self::descriptionField(),
        ];
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
                if (! $table->getActiveFiltersCount()) {
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
                ->summarize(Sum::make()->money(Currency::getCurrency())),

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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'view' => ViewAccount::route('/{record}'),
        ];
    }
}
