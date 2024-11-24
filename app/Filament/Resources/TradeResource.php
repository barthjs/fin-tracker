<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\TradeType;
use App\Filament\Resources\TradeResource\Pages\ListTrades;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Carbon\Carbon;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Number;

class TradeResource extends Resource
{
    protected static ?string $model = Trade::class;

    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'tabler-exchange';

    public static function getSlug(): string
    {
        return __('trade.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('trade.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::formParts());
    }

    public static function formParts($account = null, $portfolio = null, $security = null): array
    {
        // account, portfolio and security for default values in relation manager
        return [
            Section::make()
                ->schema([
                    DateTimePicker::make('date_time')
                        ->label(__('transaction.columns.date'))
                        ->autofocus()
                        ->native(false)
                        ->displayFormat('d.m.Y, H:i')
                        ->seconds(false)
                        ->default(today())
                        ->required(),
                    Select::make('security_id')
                        ->label(__('trade.columns.security'))
                        ->relationship('security', 'name')
                        ->options(Security::query()->whereActive(true)->pluck('name', 'id'))
                        ->placeholder(__('trade.form.security_placeholder'))
                        ->validationMessages(['required' => __('trade.form.security_validation_message')])
                        ->preload()
                        ->default(fn(): int|string => $security->id ?? "")
                        ->required()
                        ->searchable()
                        ->createOptionForm(SecurityResource::formParts())
                        ->createOptionModalHeading(__('security.buttons.create_heading')),
                    TextInput::make('quantity')
                        ->label(__('trade.columns.quantity'))
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->live(true, 500)
                        ->formatStateUsing(function ($state) {
                            if ($state < 0) {
                                return $state * -1;
                            }
                            return $state;
                        })
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $set('total_amount', $get('price') * $state + $get('tax') + $get('fee'));
                        }),
                    TextInput::make('price')
                        ->label(__('trade.columns.price'))
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->live(true, 500)
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $set('total_amount', round($state * $get('quantity') + $get('tax') + $get('fee'), 2));
                        }),
                    TextInput::make('tax')
                        ->label(__('trade.columns.tax'))
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->live(true, 500)
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $set('total_amount', $get('price') * $get('quantity') + $state + $get('fee'));
                        }),
                    TextInput::make('fee')
                        ->label(__('trade.columns.fee'))
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->live(true, 500)
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $set('total_amount', $get('price') * $get('quantity') + $get('tax') + $state);
                        }),
                    TextInput::make('total_amount')
                        ->label(__('trade.columns.total_amount'))
                        ->suffix(fn($get): string => Account::whereId($get('account_id'))->first()->currency->name ?? "")
                        ->disabled(),
                    Select::make('type')
                        ->label(__('trade.columns.type'))
                        ->placeholder(__('trade.form.type_placeholder'))
                        ->options(__('trade.types'))
                        ->required(),
                    Section::make()
                        ->schema([
                            Select::make('account_id')
                                ->label(__('trade.columns.account'))
                                ->relationship('account', 'name')
                                ->placeholder(__('trade.form.account_placeholder'))
                                ->validationMessages(['required' => __('trade.form.account_validation_message')])
                                ->preload()
                                ->default(fn(): int|string => $account->id ?? "")
                                ->live(true)
                                ->required()
                                ->searchable()
                                ->createOptionForm(AccountResource::formParts())
                                ->createOptionModalHeading(__('account.buttons.create_heading')),
                            Select::make('portfolio_id')
                                ->label(__('trade.columns.portfolio'))
                                ->relationship('portfolio', 'name')
                                ->placeholder(__('trade.form.portfolio_placeholder'))
                                ->validationMessages(['required' => __('trade.form.portfolio_validation_message')])
                                ->preload()
                                ->default(fn(): int|string => $portfolio->id ?? "")
                                ->required()
                                ->searchable()
                                ->createOptionForm(PortfolioResource::formParts())
                                ->createOptionModalHeading(__('portfolio.buttons.create_heading')),
                            Textarea::make('notes')
                                ->label(__('trade.columns.notes'))
                                ->autosize()
                                ->columnSpanFull()
                                ->maxLength(255)
                                ->rows(1)
                                ->string(),
                        ])
                        ->columns(2),

                ])
                ->columns(2)
        ];
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_time')
                    ->label(__('trade.columns.date'))
                    ->dateTime('Y-m-d, H:i')
                    ->fontFamily('mono')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_amount')
                    ->label(__('trade.columns.total_amount'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyableState(fn($state) => Number::format($state, 2))
                    ->numeric(2)
                    ->badge()
                    ->color(fn(Trade $record): string => match ($record->type) {
                        TradeType::BUY => 'danger',
                        TradeType::SELL => 'success',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label(__('trade.columns.quantity'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->label(__('trade.columns.price'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('tax')
                    ->label(__('trade.columns.tax'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('fee')
                    ->label(__('trade.columns.fee'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),
                ImageColumn::make('account.logo')
                    ->label('')
                    ->hiddenOn(AccountResource\RelationManagers\TradesRelationManager::class)
                    ->circular()
                    ->alignEnd(),
                TextColumn::make('account.name')
                    ->label(__('trade.columns.account'))
                    ->hiddenOn(AccountResource\RelationManagers\TradesRelationManager::class)
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('portfolio.name')
                    ->label(__('trade.columns.portfolio'))
                    ->hiddenOn(PortfolioResource\RelationManagers\TradesRelationManager::class)
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('security.name')
                    ->label(__('trade.columns.security'))
                    ->hiddenOn(SecurityResource\RelationManagers\TradesRelationManager::class)
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('notes')
                    ->label(__('trade.columns.notes'))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(fn(): bool => Trade::count() > 20)
            ->deferLoading()
            ->extremePaginationLinks()
            ->defaultSort('date_time', 'desc')
            ->persistSortInSession()
            ->striped()
            ->filters([
                SelectFilter::make('account')
                    ->label(__('trade.columns.account'))
                    ->hiddenOn(AccountResource\RelationManagers\TradesRelationManager::class)
                    ->relationship('account', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('portfolio')
                    ->label(__('trade.columns.portfolio'))
                    ->hiddenOn(PortfolioResource\RelationManagers\TradesRelationManager::class)
                    ->relationship('portfolio', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('security')
                    ->label(__('trade.columns.security'))
                    ->hiddenOn(SecurityResource\RelationManagers\TradesRelationManager::class)
                    ->relationship('security', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Filter::make('date')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('table.filter.created_from'))
                            ->default(Carbon::today()->startOfYear()),
                        DatePicker::make('created_until')
                            ->label(__('table.filter.created_until'))
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_time', '>=', $date))
                            ->when($data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_time', '<=', $date));
                    })
            ], FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                CreateAction::make('header-create')
                    ->icon('tabler-plus')
                    ->label(__('trade.buttons.create_button_label'))
                    ->hidden(function ($livewire) {
                        return $livewire instanceof ListTrades;
                    })
                    ->modalHeading(__('trade.buttons.create_heading')),
            ])
            ->persistFiltersInSession()
            ->filtersFormColumns(function ($livewire) {
                return $livewire instanceof ListTrades ? 4 : 3;
            })
            ->actions(self::getActions())
            ->bulkActions(self::getBulkActions())
            ->emptyStateHeading(__('trade.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('trade.buttons.create_button_label'))
                    ->modalHeading(__('trade.buttons.create_heading'))
            ]);
    }

    public static function getActions(): array
    {
        return [
            EditAction::make()
                ->iconButton()
                ->icon('tabler-edit')
                ->modalHeading(__('trade.buttons.edit_heading'))
                ->using(function (Trade $record, array $data): Trade {
                    $oldAmount = $record->getOriginal('total_amount');
                    $oldQuantity = $record->getOriginal('quantity');
                    $oldAccountId = $record->getOriginal('account_id');
                    $oldPortfolioId = $record->getOriginal('portfolio_id');
                    $oldSecurityId = $record->getOriginal('security_id');

                    DB::transaction(function () use ($record, $data, $oldAmount, $oldQuantity, $oldAccountId, $oldPortfolioId, $oldSecurityId): void {
                        $record->update($data);

                        $amount = $record->total_amount;
                        $quantity = $record->quantity;
                        $accountId = $record->account_id;
                        $portfolioId = $record->portfolio_id;
                        $securityId = $record->security_id;

                        if ($oldAccountId !== $accountId) {
                            Account::updateAccountBalance($oldAccountId);
                            Account::updateAccountBalance($accountId);
                        } else if ($oldAmount !== $amount) {
                            Account::updateAccountBalance($accountId);
                        }

                        if ($oldPortfolioId !== $portfolioId) {
                            Portfolio::updatePortfolioMarketValue($oldPortfolioId);
                            Portfolio::updatePortfolioMarketValue($portfolioId);
                        } else if ($oldQuantity !== $quantity) {
                            Portfolio::updatePortfolioMarketValue($portfolioId);
                        }

                        if ($oldSecurityId !== $securityId) {
                            Security::updateSecurityQuantity($oldSecurityId);
                            Security::updateSecurityQuantity($securityId);
                        } else if ($oldQuantity !== $quantity) {
                            Security::updateSecurityQuantity($securityId);
                        }
                    });

                    return $record;
                }),
            DeleteAction::make()
                ->iconButton()
                ->icon('tabler-trash')
                ->modalHeading(__('trade.buttons.delete_heading'))
                ->after(function (Trade $record): Trade {
                    Account::updateAccountBalance($record->account_id);
                    Portfolio::updatePortfolioMarketValue($record->portfolio_id);
                    Security::updateSecurityQuantity($record->security_id);
                    return $record;
                })
        ];
    }

    public static function getBulkActions(): BulkActionGroup
    {
        return BulkActionGroup::make([
            DeleteBulkAction::make()
                ->modalHeading(__('trade.buttons.bulk_delete_heading'))
                ->after(function (Collection $records) {
                    $deletedAccountIds = [];
                    $deletedPortfolioIds = [];
                    $deletedSecurityIds = [];

                    foreach ($records as $record) {
                        if (!in_array($record->account_id, $deletedAccountIds)) {
                            $deletedAccountIds[] = $record->account_id;
                        }

                        if (!in_array($record->portfolio_id, $deletedPortfolioIds)) {
                            $deletedPortfolioIds[] = $record->portfolio_id;
                        }

                        if (!in_array($record->security_id, $deletedSecurityIds)) {
                            $deletedSecurityIds[] = $record->security_id;
                        }
                    }

                    foreach ($deletedAccountIds as $accountId) {
                        Account::updateAccountBalance($accountId);
                    }

                    foreach ($deletedPortfolioIds as $portfolioId) {
                        Portfolio::updatePortfolioMarketValue($portfolioId);
                    }

                    foreach ($deletedSecurityIds as $securityId) {
                        Security::updateSecurityQuantity($securityId);
                    }
                }),
            BulkAction::make('account')
                ->icon('tabler-edit')
                ->label(__('trade.buttons.bulk_account'))
                ->form([
                    Select::make('account_id')
                        ->label(__('trade.columns.account'))
                        ->relationship('account', 'name')
                        ->placeholder(__('trade.form.account_placeholder'))
                        ->validationMessages(['required' => __('trade.form.account_validation_message')])
                        ->preload()
                        ->required()
                        ->searchable()
                ])
                ->action(function (Collection $records, array $data): void {
                    // save old values before updating
                    $oldAccountIds = $records->pluck('account_id')->unique();
                    $records->each->update(['account_id' => $data['account_id']]);

                    // update balance for new account
                    Account::updateAccountBalance(intval($data['account_id']));

                    // update balance for old accounts
                    foreach ($oldAccountIds as $oldAccountId) {
                        Account::updateAccountBalance($oldAccountId);
                    }
                })
                ->deselectRecordsAfterCompletion(),
            BulkAction::make('portfolio')
                ->icon('tabler-edit')
                ->label(__('trade.buttons.bulk_portfolio'))
                ->form([
                    Select::make('portfolio_id')
                        ->label(__('trade.columns.portfolio'))
                        ->relationship('portfolio', 'name')
                        ->placeholder(__('trade.form.portfolio_placeholder'))
                        ->validationMessages(['required' => __('trade.form.portfolio_validation_message')])
                        ->preload()
                        ->required()
                        ->searchable()
                ])
                ->action(function (Collection $records, array $data): void {
                    // save old values before updating
                    $oldPortfolioIds = $records->pluck('portfolio_id')->unique();
                    $records->each->update(['portfolio_id' => $data['portfolio_id']]);

                    // update market value for new portfolio
                    Portfolio::updatePortfolioMarketValue(intval($data['portfolio_id']));

                    // update market value for old portfolios
                    foreach ($oldPortfolioIds as $oldPortfolioId) {
                        Portfolio::updatePortfolioMarketValue($oldPortfolioId);
                    }
                })
                ->deselectRecordsAfterCompletion(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrades::route('/'),
        ];
    }
}
