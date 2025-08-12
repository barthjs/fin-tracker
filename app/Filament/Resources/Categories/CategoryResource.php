<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories;

use App\Enums\TransactionGroup;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Categories\RelationManagers\TransactionRelationManager;
use App\Models\Account;
use App\Models\Category;
use BackedEnum;
use Carbon\Carbon;
use Exception;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-category';

    public static function getSlug(?Panel $panel = null): string
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

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::formParts());
    }

    public static function formParts(): array
    {
        return [
            Section::make()
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
                    ColorPicker::make('color')
                        ->label(__('widget.color'))
                        ->validationMessages(['regex' => __('widget.color_validation_message')])
                        ->required()
                        ->default(mb_strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF))))
                        ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'),
                    Toggle::make('active')
                        ->label(__('table.active'))
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(2),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('category.columns.name'))
                            ->tooltip(fn (Category $record): string => ! $record->active ? __('table.status_inactive') : '')
                            ->color(fn (Category $record): string => ! $record->active ? 'danger' : 'success')
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('group')
                            ->label(__('category.columns.group'))
                            ->formatStateUsing(fn (TransactionGroup $state): string => __('category.groups')[$state->name])
                            ->color(fn (Category $record): string => match ($record->type->name) {
                                'expense' => 'danger',
                                'revenue' => 'success',
                                default => 'warning',
                            })
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make(Carbon::today()->format('Y'))
                            ->money(Account::getCurrency())
                            ->state(function (Category $record): float {
                                return Category::with(['transactions' => function (HasMany $query) {
                                    $query->whereYear('date_time', Carbon::now()->year);
                                }])->whereId($record->id)->first()->transactions->sum('amount');
                            })
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),
                    ])
                    ->columns([
                        'default' => 2,
                        'sm' => 3,
                    ]),
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
                if (! $table->getActiveFiltersCount()) {
                    return $query->where('active', true);
                }

                return $query;
            })
            ->columns($columns)
            ->paginated(fn (): bool => Category::count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('active', false)),
            ])
            ->persistFiltersInSession()
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->modalHeading(__('category.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('category.buttons.delete_heading'))
                    ->visible(fn (Category $record): bool => ! $record->transactions()->exists()),
            ])
            ->toolbarActions(self::getBulkActions())
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
                ->size(TextSize::Medium)
                ->weight(FontWeight::SemiBold)
                ->wrap()
                ->searchable()
                ->sortable(),
            TextColumn::make('group')
                ->label(__('category.columns.group'))
                ->formatStateUsing(fn (TransactionGroup $state): string => __('category.groups')[$state->name])
                ->badge()
                ->color(fn (Category $record): string => match ($record->type->name) {
                    'expense' => 'danger',
                    'revenue' => 'success',
                    default => 'warning',
                })
                ->searchable(true, function (Builder $query, string $search): Builder {
                    $groups = [];
                    foreach (__('category.groups') as $group => $value) {
                        if (mb_stripos($value, $search) !== false) {
                            $groups[] = $group;
                        }
                    }

                    return $query->whereIn('group', $groups);
                })
                ->sortable(),
            IconColumn::make('active')
                ->label(__('table.active'))
                ->tooltip(fn (bool $state): string => $state ? __('table.status_active') : __('table.status_inactive'))
                ->boolean()
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
            TransactionRelationManager::class,
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
