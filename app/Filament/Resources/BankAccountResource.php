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
use Illuminate\Support\Collection;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'tabler-bank-building';

    public static function getSlug(): string
    {
        return __('bank_account.url');
    }

    public static function getNavigationLabel(): string
    {
        return __('bank_account.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('bank_account.columns.name'))
                    ->autofocus()
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Select::make('currency')
                    ->label(__('bank_account.columns.currency'))
                    ->placeholder(__('bank_account.form.currency_placeholder'))
                    ->options(Currency::class)
                    ->default(fn() => BankAccount::getCurrency())
                    ->required()
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->label(__('bank_account.columns.description'))
                    ->autosize()
                    ->maxLength(1000)
                    ->rows(1)
                    ->string()
                    ->grow(),
                Forms\Components\Toggle::make('active')
                    ->label(__('table.active'))
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
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->query(fn($query) => $query->where('active', false))
            ])
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->modalHeading(__('bank_account.buttons.edit_heading')),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading(__('bank_account.buttons.delete_heading'))
                    ->disabled(fn($record) => $record->transactions()->count() > 0)
            ])
            ->bulkActions(self::getBulkActions())
            ->emptyStateHeading(__('bank_account.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('bank_account.buttons.create_button_label'))
                    ->modalHeading(__('bank_account.buttons.create_heading')),
            ]);
    }

    public static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('bank_account.columns.name'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('balance')
                ->label(__('bank_account.columns.balance'))
                ->numeric(2)
                ->badge()
                ->color(fn($state) => match (true) {
                    floatval($state) == 0 => 'gray',
                    floatval($state) < 0 => 'danger',
                    default => 'success'
                })
                ->sortable(),
            Tables\Columns\TextColumn::make('currency')
                ->label(__('bank_account.columns.currency'))
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('description')
                ->label(__('bank_account.columns.description'))
                ->sortable()
                ->toggleable()
                ->wrap(),
            Tables\Columns\IconColumn::make('active')
                ->label(__('table.active'))
                ->boolean()
                ->sortable()
                ->tooltip(fn($state): string => $state ? __('table.status_active') : __('table.status_inactive'))
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('table.created_at'))
                ->dateTime('Y-m-d, H:i:s')
                ->fontFamily('mono')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('table.updated_at'))
                ->dateTime('Y-m-d, H:i:s')
                ->fontFamily('mono')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkAction::make('currency')
                ->icon('tabler-edit')
                ->label(__('bank_account.buttons.bulk_currency'))
                ->form([
                    Forms\Components\Select::make('currency')
                        ->label(__('bank_account.columns.currency'))
                        ->placeholder(__('bank_account.form.currency_placeholder'))
                        ->options(Currency::class)
                        ->default(fn() => BankAccount::getCurrency())
                        ->required()
                        ->searchable(),
                ])
                ->action(function (Collection $records, array $data): void {
                    $records->each->update(['currency' => $data['currency']]);
                })
                ->deselectRecordsAfterCompletion()
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
