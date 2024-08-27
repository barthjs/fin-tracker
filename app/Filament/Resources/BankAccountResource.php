<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Filament\Resources\BankAccountResource\Pages;
use App\Filament\Resources\BankAccountResource\RelationManagers\TransactionRelationManager;
use App\Models\BankAccount;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'tabler-bank-building';

    public static function getNavigationLabel(): string
    {
        return __('resources.bank_accounts.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('resources.bank_accounts.table.name'))
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Select::make('currency')
                    ->label(__('resources.bank_accounts.table.currency'))
                    ->placeholder(__('resources.bank_accounts.form.currency_placeholder'))
                    ->options(Currency::class)
                    ->default(fn() => BankAccount::getDefaultCurrency())
                    ->required()
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->label(__('tables.description'))
                    ->autosize()
                    ->maxLength(1000)
                    ->rows(1)
                    ->string()
                    ->grow(),
                Forms\Components\Toggle::make('active')
                    ->label(__('tables.active'))
                    ->default(true)
                    ->inline(false)
            ])
            ->columns(4);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        $columns = self::tableColumns();
        return $table
            ->columns($columns)
            ->paginated(fn() => BankAccount::all()->count() > 20)
            ->recordUrl(fn(Model $record): string => Pages\ViewBankAccount::getUrl([$record->id]))
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('tables.status_inactive'))
                    ->query(fn($query) => $query->where('active', false))
            ])
            ->emptyStateHeading(__('resources.bank_accounts.table.empty'))
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()
                    ->modalHeading(__('resources.bank_accounts.edit_heading')),
                Tables\Actions\DeleteAction::make()->iconButton()
                    ->modalHeading(__('resources.bank_accounts.delete_heading'))
                    ->disabled(fn($record) => $record->transactions()->count() > 0)
            ]);
    }

    public static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('resources.bank_accounts.table.name'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('balance')
                ->label(__('resources.bank_accounts.table.balance'))
                ->numeric(2)
                ->badge()
                ->color(function ($record) {
                    $balance = $record->balance;
                    return match (true) {
                        floatval($balance) == 0 => 'gray',
                        floatval($balance) < 0 => 'danger',
                        default => 'success',
                    };
                })
                ->sortable(),
            Tables\Columns\TextColumn::make('currency')
                ->label(__('resources.bank_accounts.table.currency'))
                ->toggleable()
                ->sortable(),
            Tables\Columns\TextColumn::make('description')
                ->label(__('tables.description'))
                ->sortable()
                ->toggleable()
                ->wrap(),
            Tables\Columns\IconColumn::make('active')
                ->label(__('tables.active'))
                ->boolean()
                ->sortable()
                ->tooltip(fn($state): string => $state ? __('tables.status_active') : 'tables.status_inactive')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('tables.created_at'))
                ->dateTime('Y-m-d H:i:s')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('tables.updated_at'))
                ->dateTime('Y-m-d H:i:s')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TransactionRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'view' => Pages\ViewBankAccount::route('/{record}'),
        ];
    }
}
