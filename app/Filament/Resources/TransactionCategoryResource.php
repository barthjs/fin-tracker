<?php

namespace App\Filament\Resources;

use App\Enums\TransactionGroup;
use App\Filament\Resources\TransactionCategoryResource\Pages;
use App\Filament\Resources\TransactionCategoryResource\RelationManagers\TransactionRelationManager;
use App\Models\TransactionCategory;
use Carbon\Carbon;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class TransactionCategoryResource extends Resource
{
    protected static ?string $model = TransactionCategory::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'tabler-category';

    public static function getSlug(): string
    {
        return __('transaction_category.url');
    }

    public static function getNavigationLabel(): string
    {
        return __('transaction_category.navigation_label');
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
                    Forms\Components\TextInput::make('name')
                        ->label(__('transaction_category.columns.name'))
                        ->autofocus()
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Forms\Components\Select::make('group')
                        ->label(__('transaction_category.columns.group'))
                        ->placeholder(__('transaction_category.form.group_placeholder'))
                        ->options(__('transaction_category.groups'))
                        ->default(TransactionGroup::transfers->name)
                        ->required(),
                    Forms\Components\Toggle::make('active')
                        ->label(__('table.active'))
                        ->default(true)
                        ->inline(false),
                ])->columns(3)
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('transaction_category.columns.name'))
                            ->tooltip(fn($record) => !$record->active ? __('table.status_inactive') : "")
                            ->color(fn($record) => !$record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('group')
                            ->label(__('transaction_category.columns.group'))
                            ->formatStateUsing(fn($state): string => __('transaction_category.groups')[$state->name])
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('type')
                            ->label(__('transaction_category.columns.type'))
                            ->formatStateUsing(fn($state): string => __('transaction_category.types')[$state->name])
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make(Carbon::now()->year)
                            ->numeric()
                            ->state(function (TransactionCategory $record): float {
                                return TransactionCategory::with(['transactions' => function ($query) {
                                    $query->whereYear('date_time', Carbon::now()->year);
                                }])->whereId($record->id)->first()->transactions->sum('amount');
                            })
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                    ])
                    ->columns([
                        'default' => 2,
                        'sm' => 4
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
            ->columns($columns)
            ->paginated(fn() => TransactionCategory::all()->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('active')
                    ->label(__('table.status_active'))
                    ->toggle()
                    ->query(fn($query) => $query->where('active', true))
            ])
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->modalHeading(__('transaction_category.buttons.edit_heading')),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading(__('transaction_category.buttons.delete_heading'))
                    ->disabled(fn($record) => $record->transactions()->count() > 0)
            ])
            ->bulkActions(self::getBulkActions())
            ->emptyStateHeading(__('transaction_category.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('transaction_category.buttons.create_button_label'))
                    ->modalHeading(__('transaction_category.buttons.create_heading')),
            ]);
    }

    public static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('transaction_category.columns.name'))
                ->searchable()
                ->sortable()
                ->wrap(),
            Tables\Columns\TextColumn::make('group')
                ->label(__('transaction_category.columns.group'))
                ->formatStateUsing(fn($state): string => __('transaction_category.groups')[$state->name])
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('type')
                ->label(__('transaction_category.columns.type'))
                ->formatStateUsing(fn($state): string => __('transaction_category.types')[$state->name])
                ->searchable()
                ->sortable(),
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
            Tables\Actions\BulkAction::make('group')
                ->icon('tabler-edit')
                ->label(__('transaction_category.buttons.bulk_group'))
                ->form([
                    Forms\Components\Select::make('group')
                        ->label(__('transaction_category.columns.group'))
                        ->placeholder(__('transaction_category.form.group_placeholder'))
                        ->options(__('transaction_category.groups'))
                        ->default(TransactionGroup::transfers->name)
                        ->required(),
                ])
                ->action(function (Collection $records, array $data): void {
                    $records->each->update(['group' => $data['group']]);
                })
                ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListTransactionCategories::route('/'),
            'view' => Pages\ViewTransactionCategory::route('/{record}'),
        ];
    }
}
