<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\SecurityType;
use App\Filament\Resources\SecurityResource\Pages\ListSecurities;
use App\Filament\Resources\SecurityResource\Pages\ViewSecurity;
use App\Filament\Resources\SecurityResource\RelationManagers\TradesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\SecuritiesRelationManager;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SecurityResource extends Resource
{
    protected static ?string $model = Security::class;
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'tabler-file-percent';

    public static function getSlug(): string
    {
        return __('security.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('security.navigation_label');
    }

    public static function getBreadcrumb(): string
    {
        return __('security.navigation_label');
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
                        ->label(__('security.columns.name'))
                        ->autofocus()
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    TextInput::make('isin')
                        ->label(__('security.columns.isin'))
                        ->maxLength(255)
                        ->string(),
                    TextInput::make('symbol')
                        ->label(__('security.columns.symbol'))
                        ->maxLength(255)
                        ->string(),
                    TextInput::make('price')
                        ->label(__('security.columns.price'))
                        ->required()
                        ->numeric()
                ])->columns(2),
            Forms\Components\Section::make()
                ->schema([
                    Select::make('type')
                        ->label(__('security.columns.type'))
                        ->placeholder(__('security.form.type_placeholder'))
                        ->options(__('security.types'))
                        ->default(SecurityType::STOCK)
                        ->required(),
                    ColorPicker::make('color')
                        ->label(__('widget.color'))
                        ->validationMessages(['regex' => __('widget.color_validation_message')])
                        ->required()
                        ->default(strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF))))
                        ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'),
                    Toggle::make('active')
                        ->label(__('table.active'))
                        ->default(true)
                        ->inline(false),
                    FileUpload::make('logo')
                        ->avatar()
                        ->image()
                        ->imageEditor()
                        ->circleCropper()
                        ->moveFiles()
                        ->directory('logos')
                        ->maxSize(1024),
                    Textarea::make('description')
                        ->label(__('security.columns.description'))
                        ->autosize()
                        ->columnSpan(2)
                        ->maxLength(1000)
                        ->string(),
                ])->columns(3),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('security.columns.name'))
                            ->tooltip(fn(Security $record) => !$record->active ? __('table.status_inactive') : "")
                            ->color(fn(Security $record) => !$record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('price')
                            ->label(__('security.columns.price'))
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->numeric(6),
                        TextEntry::make('total_quantity')
                            ->label(__('security.columns.total_quantity'))
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->numeric(6),
                        TextEntry::make('type')
                            ->label(__('security.columns.type'))
                            ->formatStateUsing(fn($state): string => __('security.types')[$state->name])
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                    ])
                    ->columns([
                        'default' => 2,
                        'md' => 4
                    ])
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                if (!$table->getActiveFiltersCount()) {
                    return $query->where('active', true);
                } else {
                    return $query;
                }
            })
            ->columns(self::getTableColumns())
            ->paginated(fn(): bool => Security::count() > 20)
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
                    ->modalHeading(__('security.buttons.edit_heading'))
                    ->using(function (Security $record, array $data): Security {
                        $price = $record->price;
                        $record->update($data);
                        if ($data['price'] !== $price) {
                            $portfolios = Trade::whereSecurityId($record->id)
                                ->pluck('portfolio_id')
                                ->unique()
                                ->toArray();
                            if (!empty($portfolios)) {
                                foreach ($portfolios as $portfolio) {
                                    Portfolio::updatePortfolioMarketValue($portfolio);
                                }
                            }
                        }
                        return $record;
                    }),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('security.buttons.delete_heading'))
                    ->disabled(fn(Security $record): bool => $record->trades()->count() > 0)
            ])
            ->emptyStateHeading(__('security.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('security.buttons.create_button_label'))
                    ->modalHeading(__('security.buttons.create_heading')),
            ]);
    }

    public static function getTableColumns(): array
    {
        return [
            ImageColumn::make('logo')
                ->label(__('security.columns.logo'))
                ->circular()
                ->extraImgAttributes(fn(Security $record): array => [
                    'alt' => "{$record->name} logo",
                ])
                ->toggleable(),
            TextColumn::make('name')
                ->label(__('security.columns.name'))
                ->size(TextColumn\TextColumnSize::Medium)
                ->weight(FontWeight::SemiBold)
                ->wrap()
                ->searchable()
                ->sortable(),
            TextColumn::make('price')
                ->label(__('security.columns.price'))
                ->badge()
                ->numeric(2)
                ->searchable()
                ->sortable(),
            TextColumn::make('total_quantity')
                ->label(__('security.columns.total_quantity'))
                ->hiddenOn(SecuritiesRelationManager::class)
                ->numeric(2)
                ->searchable()
                ->sortable(),
            TextColumn::make('isin')
                ->label(__('security.columns.isin'))
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('symbol')
                ->label(__('security.columns.symbol'))
                ->searchable()
                ->sortable()
                ->toggleable(),
            TextColumn::make('description')
                ->label(__('security.columns.description'))
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: true),
            IconColumn::make('active')
                ->label(__('table.active'))
                ->tooltip(fn($state): string => $state ? __('table.status_active') : __('table.status_inactive'))
                ->boolean()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TradesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurities::route('/'),
            'view' => ViewSecurity::route('/{record}'),
        ];
    }
}
