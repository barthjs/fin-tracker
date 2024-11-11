<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioResource\Pages\ListPortfolios;
use App\Filament\Resources\PortfolioResource\Pages\ViewPortfolio;
use App\Filament\Resources\PortfolioResource\RelationManagers\TradesRelationManager;
use App\Models\Account;
use App\Models\Portfolio;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PortfolioResource extends Resource
{
    protected static ?string $model = Portfolio::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getSlug(): string
    {
        return __('portfolio.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('portfolio.navigation_label');
    }

    public static function getBreadcrumb(): string
    {
        return __('portfolio.navigation_label');
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
                        ->label(__('portfolio.columns.name'))
                        ->autofocus()
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    ColorPicker::make('color')
                        ->label(__('widget.color'))
                        ->validationMessages(['regex' => __('widget.color_validation_message')])
                        ->required()
                        ->default(strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF))))
                        ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'),
                    Textarea::make('description')
                        ->label(__('portfolio.columns.description'))
                        ->autosize()
                        ->rows(1)
                        ->columnSpanFull()
                        ->maxLength(1000)
                        ->string(),
                    FileUpload::make('logo')
                        ->avatar()
                        ->image()
                        ->imageEditor()
                        ->circleCropper()
                        ->moveFiles()
                        ->directory('logos')
                        ->maxSize(1024),
                    Toggle::make('active')
                        ->label(__('table.active'))
                        ->default(true)
                        ->inline(false),
                ])->columns(2)
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('portfolio.columns.name'))
                            ->tooltip(fn($record) => !$record->active ? __('table.status_inactive') : "")
                            ->color(fn($record) => !$record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('market_value')
                            ->label(__('portfolio.columns.market_value'))
                            ->color(fn($state) => match (true) {
                                floatval($state) == 0 => 'gray',
                                floatval($state) < 0 => 'danger',
                                default => 'success'
                            })
                            ->money(currency: fn($record) => Account::getCurrency())
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('description')
                            ->label(__('portfolio.columns.description'))
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
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                if (!$table->getActiveFiltersCount()) {
                    return $query->where('active', true);
                } else {
                    return $query;
                }
            })
            ->columns(self::getTableColumns())
            ->paginated(fn(): bool => Portfolio::count() > 20)
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
                    ->modalHeading(__('portfolio.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('portfolio.buttons.delete_heading'))
                    ->disabled(fn(Portfolio $record): bool => $record->trades()->count() > 0),
            ])
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('portfolio.buttons.create_button_label'))
                    ->modalHeading(__('portfolio.buttons.create_heading')),
            ]);
    }

    public static function getTableColumns(): array
    {
        return [
            ImageColumn::make('logo')
                ->label(__('widget.color'))
                ->circular()
                ->extraImgAttributes(fn(Account $record): array => [
                    'alt' => "{$record->name} logo",
                ])
                ->toggleable(),
            TextColumn::make('name')
                ->label(__('portfolio.columns.name'))
                ->size(TextColumn\TextColumnSize::Medium)
                ->weight(FontWeight::SemiBold)
                ->searchable()
                ->sortable(),
            TextColumn::make('market_value')
                ->label(__('portfolio.columns.market_value'))
                ->badge()
                ->color(fn($state): string => match (true) {
                    floatval($state) == 0 => 'gray',
                    floatval($state) < 0 => 'danger',
                    default => 'success'
                })
                ->money(Account::getCurrency())
                ->summarize(Sum::make()->money(config('app.currency')))
                ->toggleable(),
            TextColumn::make('description')
                ->label(__('portfolio.columns.description'))
                ->wrap()
                ->sortable()
                ->toggleable(),
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

    public static function getRelations(): array
    {
        return [
            TradesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPortfolios::route('/'),
            'view' => ViewPortfolio::route('/{record}'),
        ];
    }
}
