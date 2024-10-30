<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Filament\Resources\AccountResource\Pages\ListAccounts;
use App\Filament\Resources\AccountResource\Pages\ViewAccount;
use App\Filament\Resources\AccountResource\RelationManagers\TransactionRelationManager;
use App\Models\Account;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'tabler-bank-building';

    public static function getSlug(): string
    {
        return __('account.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('account.navigation_label');
    }

    public static function getBreadcrumb(): string
    {
        return __('account.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::formParts());
    }

    public static function formParts(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    TextInput::make('name')
                        ->label(__('account.columns.name'))
                        ->autofocus()
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Select::make('currency')
                        ->label(__('account.columns.currency'))
                        ->placeholder(__('account.form.currency_placeholder'))
                        ->validationMessages(['required' => __('account.form.currency_validation_message')])
                        ->options(Currency::class)
                        ->default(Account::getCurrency())
                        ->required()
                        ->searchable(),
                    Toggle::make('active')
                        ->label(__('table.active'))
                        ->default(true)
                        ->inline(false),
                    Textarea::make('description')
                        ->label(__('account.columns.description'))
                        ->autosize()
                        ->columnSpanFull()
                        ->rows(1)
                        ->maxLength(1000)
                        ->string()
                ])
                ->columns(3)
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('account.columns.name'))
                            ->tooltip(fn($record) => !$record->active ? __('table.status_inactive') : "")
                            ->color(fn($record) => !$record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('balance')
                            ->label(__('account.columns.balance'))
                            ->color(fn($state) => match (true) {
                                floatval($state) == 0 => 'gray',
                                floatval($state) < 0 => 'danger',
                                default => 'success'
                            })
                            ->money(currency: fn($record) => $record->currency->name)
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('description')
                            ->label(__('account.columns.description'))
                            ->size(TextEntry\TextEntrySize::Small)
                    ])
                    ->columns([
                        'default' => 2,
                        'lg' => 3
                    ])
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        $columns = self::tableColumns();
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                if (!$table->getActiveFiltersCount()) {
                    return $query->where('active', true);
                } else {
                    return $query;
                }
            })
            ->columns($columns)
            ->paginated(fn(): bool => Account::count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('active', false)),
            ])
            ->persistFiltersInSession()
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->modalHeading(__('account.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('account.buttons.delete_heading'))
                    ->disabled(fn(Account $record): bool => $record->transactions()->count() > 0)
            ])
            ->bulkActions(self::getBulkActions())
            ->emptyStateHeading(__('account.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('account.buttons.create_button_label'))
                    ->modalHeading(__('account.buttons.create_heading')),
            ]);
    }

    public static function tableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('account.columns.name'))
                ->searchable()
                ->sortable(),
            TextColumn::make('balance')
                ->label(__('account.columns.balance'))
                ->badge()
                ->color(fn($state): string => match (true) {
                    floatval($state) == 0 => 'gray',
                    floatval($state) < 0 => 'danger',
                    default => 'success'
                })
                ->money(currency: fn($record): string => $record->currency->name)
                ->summarize(Sum::make()->money(config('app.currency'))),
            TextColumn::make('description')
                ->label(__('account.columns.description'))
                ->wrap()
                ->sortable()
                ->toggleable(),
            TextColumn::make('currency')
                ->label(__('account.columns.currency'))
                ->sortable()
                ->toggleable()
                ->toggleable(isToggledHiddenByDefault: true),
            IconColumn::make('active')
                ->label(__('table.active'))
                ->tooltip(fn($state): string => $state ? __('table.status_active') : __('table.status_inactive'))
                ->boolean()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')
                ->label(__('table.created_at'))
                ->dateTime('Y-m-d, H:i:s')
                ->fontFamily('mono')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
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
            BulkAction::make('currency')
                ->icon('tabler-edit')
                ->label(__('account.buttons.bulk_currency'))
                ->form([
                    Select::make('currency')
                        ->label(__('account.columns.currency'))
                        ->placeholder(__('account.form.currency_placeholder'))
                        ->options(Currency::class)
                        ->default(Account::getCurrency())
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
            'index' => ListAccounts::route('/'),
            'view' => ViewAccount::route('/{record}'),
        ];
    }
}
