<?php

namespace App\Filament\Resources;

use App\Enums\TransactionGroup;
use App\Filament\Resources\TransactionCategoryResource\Pages;
use App\Filament\Resources\TransactionCategoryResource\RelationManagers\TransactionRelationManager;
use App\Models\TransactionCategory;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class TransactionCategoryResource extends Resource
{
    protected static ?string $model = TransactionCategory::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'tabler-category';

    public static function getNavigationLabel(): string
    {
        return __('resources.transaction_categories.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('resources.transaction_categories.table.name'))
                    ->autofocus()
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Select::make('group')
                    ->label(__('resources.transaction_categories.table.group'))
                    ->placeholder(__('resources.transaction_categories.form.group_placeholder'))
                    ->options(__('resources.transaction_categories.groups'))
                    ->default(TransactionGroup::transfers->name)
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->label(__('tables.active'))
                    ->default(true)
                    ->inline(false),
            ])
            ->columns(3);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        $tableParts = self::tableColumns();
        return $table
            ->columns($tableParts)
            ->paginated(fn() => TransactionCategory::all()->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('tables.status_inactive'))
                    ->query(fn($query) => $query->where('active', false))
            ])
            ->persistFiltersInSession()
            ->emptyStateHeading(__('resources.transaction_categories.table.empty'))
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()
                    ->modalHeading(__('resources.transaction_categories.edit_heading')),
                Tables\Actions\DeleteAction::make()->iconButton()
                    ->modalHeading(__('resources.transaction_categories.delete_heading'))
                    ->disabled(fn($record) => $record->transactions()->count() > 0),
            ])
            ->bulkActions(self::getBulkActions())
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('resources.transaction_categories.table.name'))
                ->searchable()
                ->sortable()
                ->wrap(),
            Tables\Columns\TextColumn::make('group')
                ->label(__('resources.transaction_categories.table.group'))
                ->formatStateUsing(fn($record): string => __('resources.transaction_categories.groups')[$record->group->name])
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('type')
                ->label(__('resources.transaction_categories.table.type'))
                ->formatStateUsing(fn($record): string => __('resources.transaction_categories.types')[$record->type->name])
                ->searchable()
                ->sortable(),
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

    public static function getBulkActions(): Tables\Actions\BulkActionGroup
    {
        return Tables\Actions\BulkActionGroup::make([
            Tables\Actions\BulkAction::make('Change group')
                ->icon('heroicon-m-pencil-square')
                ->form([
                    Forms\Components\Select::make('group')
                        ->label(__('resources.transaction_categories.table.group'))
                        ->placeholder(__('resources.transaction_categories.form.group_placeholder'))
                        ->options(__('resources.transaction_categories.groups'))
                        ->default(TransactionGroup::transfers->name)
                        ->required(),
                ])
                ->action(function (Collection $records, array $data): void {
                    $records->each->update(['group' => $data['group']]);
                })
                ->deselectRecordsAfterCompletion(),
        ]);
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
