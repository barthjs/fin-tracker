<?php

namespace App\Filament\Resources;

use App\Enums\TransactionGroup;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\CategoryResource\Pages\ViewCategory;
use App\Filament\Resources\CategoryResource\RelationManagers\TransactionRelationManager;
use App\Models\Account;
use App\Models\Category;
use Carbon\Carbon;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'tabler-category';

    public static function getSlug(): string
    {
        return __('category.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('category.navigation_label');
    }

    public static function getBreadcrumb(): string
    {
        return __('category.navigation_label');
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
                        ->label(__('category.columns.name'))
                        ->autofocus()
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Select::make('group')
                        ->label(__('category.columns.group'))
                        ->placeholder(__('category.form.group_placeholder'))
                        ->options(__('category.groups'))
                        ->default(TransactionGroup::transfers->name)
                        ->required(),
                    Toggle::make('active')
                        ->label(__('table.active'))
                        ->default(true)
                        ->inline(false),
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
                            ->label(__('category.columns.name'))
                            ->tooltip(fn($record): string => !$record->active ? __('table.status_inactive') : "")
                            ->color(fn($record): string => !$record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('group')
                            ->label(__('category.columns.group'))
                            ->formatStateUsing(fn($state): string => __('category.groups')[$state->name])
                            ->color(fn($record): string => match ($record->type->name) {
                                'expense' => 'danger',
                                'revenue' => 'success',
                                default => 'warning',
                            })
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('type')
                            ->label(__('category.columns.type'))
                            ->formatStateUsing(fn($state): string => __('category.types')[$state->name])
                            ->color(fn($state): string => match ($state->name) {
                                'expense' => 'danger',
                                'revenue' => 'success',
                                default => 'warning',
                            })
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make(Carbon::now()->year)
                            ->money(Account::getCurrency())
                            ->state(function (Category $record): float {
                                return Category::with(['transactions' => function ($query) {
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
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                if (!$table->getActiveFiltersCount()) {
                    return $query->where('active', true);
                } else {
                    return $query;
                }
            })
            ->columns($columns)
            ->paginated(fn(): bool => Category::count() > 20)
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
                    ->modalHeading(__('category.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('category.buttons.delete_heading'))
                    ->disabled(fn($record): bool => $record->transactions()->count() > 0)
            ])
            ->bulkActions(self::getBulkActions())
            ->emptyStateHeading(__('category.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('category.buttons.create_button_label'))
                    ->modalHeading(__('category.buttons.create_heading')),
            ]);
    }

    public static function tableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('category.columns.name'))
                ->wrap()
                ->searchable()
                ->sortable(),
            TextColumn::make('group')
                ->label(__('category.columns.group'))
                ->formatStateUsing(fn($state): string => __('category.groups')[$state->name])
                ->badge()
                ->color(fn($record): string => match ($record->type->name) {
                    'expense' => 'danger',
                    'revenue' => 'success',
                    default => 'warning',
                })
                ->searchable()
                ->sortable(),
            TextColumn::make('type')
                ->label(__('category.columns.type'))
                ->formatStateUsing(fn($state): string => __('category.types')[$state->name])
                ->badge()
                ->color(fn($state): string => match ($state->name) {
                    'expense' => 'danger',
                    'revenue' => 'success',
                    default => 'warning',
                })
                ->searchable()
                ->sortable(),
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
            BulkAction::make('group')
                ->icon('tabler-edit')
                ->label(__('category.buttons.bulk_group'))
                ->form([
                    Select::make('group')
                        ->label(__('category.columns.group'))
                        ->placeholder(__('category.form.group_placeholder'))
                        ->options(__('category.groups'))
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
            'index' => ListCategories::route('/'),
            'view' => ViewCategory::route('/{record}'),
        ];
    }
}
