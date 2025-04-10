<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Filament\Resources\AccountResource\Pages\ListAccounts;
use App\Filament\Resources\AccountResource\Pages\ViewAccount;
use App\Filament\Resources\AccountResource\RelationManagers\TradesRelationManager;
use App\Filament\Resources\AccountResource\RelationManagers\TransactionRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\AccountsRelationManager;
use App\Models\Account;
use App\Tables\Columns\LogoColumn;
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
                    Textarea::make('description')
                        ->label(__('account.columns.description'))
                        ->autosize()
                        ->rows(1)
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
                            ->label(__('account.columns.name'))
                            ->tooltip(fn (Account $record) => ! $record->active ? __('table.status_inactive') : '')
                            ->color(fn (Account $record) => ! $record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('balance')
                            ->label(__('account.columns.balance'))
                            ->color(fn (float $state): string => match (true) {
                                $state == 0 => 'gray',
                                $state < 0 => 'danger',
                                default => 'success'
                            })
                            ->money(fn (Account $record): string => $record->currency->name)
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('description')
                            ->label(__('account.columns.description'))
                            ->size(TextEntry\TextEntrySize::Small)
                            ->hidden(fn (Account $record) => ! $record->description),
                    ])
                    ->columns([
                        'default' => 2,
                        'md' => 3,
                    ]),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                if (! $table->getActiveFiltersCount()) {
                    return $query->where('active', true);
                } else {
                    return $query;
                }
            })
            ->columns(self::tableColumns())
            ->paginated(fn (): bool => Account::count() > 20)
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
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->modalHeading(__('account.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('account.buttons.delete_heading'))
                    ->visible(fn (Account $record): bool => ! $record->transactions()->exists() && ! $record->trades()->exists()),
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
        $hidden = AccountsRelationManager::class;

        return [
            LogoColumn::make('name')
                ->label(__('account.columns.name'))
                ->state(fn (Account $record): array => [
                    'logo' => $record->logo,
                    'name' => $record->name,
                ])
                ->searchable()
                ->sortable(),
            TextColumn::make('balance')
                ->label(__('account.columns.balance'))
                ->hiddenOn($hidden)
                ->badge()
                ->color(fn (float $state): string => match (true) {
                    $state == 0 => 'gray',
                    $state < 0 => 'danger',
                    default => 'success'
                })
                ->money(currency: fn (Account $record): string => $record->currency->name)
                ->summarize(Sum::make()->money(Account::getCurrency(), 100))
                ->toggleable(),
            TextColumn::make('description')
                ->label(__('account.columns.description'))
                ->hiddenOn($hidden)
                ->wrap()
                ->searchable()
                ->toggleable(),
            TextColumn::make('currency')
                ->label(__('account.columns.currency'))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
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
            BulkAction::make('currency')
                ->icon('tabler-edit')
                ->label(__('account.buttons.bulk_currency'))
                ->form([
                    Select::make('currency')
                        ->label(__('account.columns.currency'))
                        ->placeholder(__('account.form.currency_placeholder'))
                        ->validationMessages(['required' => __('account.form.currency_validation_message')])
                        ->options(Currency::class)
                        ->default(Account::getCurrency())
                        ->required()
                        ->searchable(),
                ])
                ->action(function (Collection $records, array $data): void {
                    $records->each->update(['currency' => $data['currency']]);
                })
                ->deselectRecordsAfterCompletion(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TransactionRelationManager::class,
            TradesRelationManager::class,
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
