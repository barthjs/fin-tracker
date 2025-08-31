<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories;

use App\Enums\CategoryGroup;
use App\Enums\Currency;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceInfolistEntries;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Categories\RelationManagers\TransactionRelationManager;
use App\Models\Category;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CategoryResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceInfolistEntries, HasResourceTableColumns;

    protected static ?string $model = Category::class;

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-category';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('category.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('category.plural_label');
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
            self::categoryGroupField(),
            self::colorField(),
            self::statusToggleField(),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('group')
                            ->label(__('category.fields.group'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make(Carbon::today()->format('Y'))
                            ->money(Currency::getCurrency())
                            ->state(function (Category $record): float {
                                return $record->statistics()
                                    ->where('year', now()->year)
                                    ->first()?->yearlySum() ?? 0.0;
                            })
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),
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
            ->modelLabel(__('category.label'))
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                if (! $table->getActiveFiltersCount()) {
                    return $query->where('is_active', true);
                }

                return $query;
            })
            ->columns(self::getTableColumns())
            ->paginated(fn (): bool => Category::count() > 20)
            ->filters([
                self::inactiveFilter(),
            ])
            ->toolbarActions(self::getBulkActions())
            ->emptyStateHeading(__('No :model found', ['model' => self::getPluralModelLabel()]));
    }

    /**
     * @return array<int, Column>
     */
    public static function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('fields.name'))
                ->weight(FontWeight::SemiBold)
                ->wrap()
                ->searchable()
                ->sortable(),

            TextColumn::make('group')
                ->label(__('category.fields.group'))
                ->badge()
                ->size(TextSize::Medium)
                ->searchable(true, function (Builder $query, string $search): Builder {
                    $groups = [];
                    foreach (CategoryGroup::cases() as $group) {
                        if (str_contains(mb_strtolower($group->getLabel()), mb_strtolower($search))) {
                            $groups[] = $group;
                        }
                    }

                    return $query->whereIn('group', $groups);
                })
                ->sortable(),

            self::statusColumn(),
        ];
    }

    /**
     * @return array<int, BulkAction>
     */
    public static function getBulkActions(): array
    {
        return [
            BulkAction::make('group')
                ->icon('tabler-edit')
                ->label(__('category.buttons.bulk_edit_group'))
                ->schema([
                    self::categoryGroupField(),
                ])
                ->action(function (Collection $records, array $data): void {
                    /** @var Collection<int, Category> $records */
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
