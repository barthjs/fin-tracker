<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecurityTypeResource\Pages\ListSecurityTypes;
use App\Filament\Resources\SecurityTypeResource\Pages\ViewSecurityType;
use App\Filament\Resources\SecurityTypeResource\RelationManagers;
use App\Models\SecurityType;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SecurityTypeResource extends Resource
{
    protected static ?string $model = SecurityType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getSlug(): string
    {
        return __('security_type.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('security_type.navigation_label');
    }

    public static function getBreadcrumb(): string
    {
        return __('security_type.navigation_label');
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
                        ->label(__('security_type.columns.name'))
                        ->autofocus()
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    ColorPicker::make('color')
                        ->label(__('widget.color'))
                        ->required()
                        ->default(strtolower(sprintf('#%06X', mt_rand(0, 0xFFFFFF))))
                        ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'),
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
                            ->label(__('security_type.columns.name'))
                            ->tooltip(fn($record): string => !$record->active ? __('table.status_inactive') : "")
                            ->color(fn($record): string => !$record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                    ])
                    ->columns([
                        'default' => 2,
                    ])
            ]);
    }

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
            ->paginated(fn(): bool => SecurityType::count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('active', false)),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->modalHeading(__('security_type.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('security_type.buttons.delete_heading'))
                    ->disabled(fn($record): bool => $record->securities()->count() > 0)
            ])
            ->emptyStateHeading(__('security_type.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('security_type.buttons.create_button_label'))
                    ->modalHeading(__('security_type.buttons.create_heading')),
            ]);
    }

    public static function tableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('security_type.columns.name'))
                ->size(TextColumn\TextColumnSize::Medium)
                ->weight(FontWeight::SemiBold)
                ->wrap()
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurityTypes::route('/'),
            'view' => ViewSecurityType::route('/{record}'),
        ];
    }
}
