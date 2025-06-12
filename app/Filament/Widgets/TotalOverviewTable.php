<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\AccountResource\Pages\ViewAccount;
use App\Filament\Resources\PortfolioResource\Pages\ViewPortfolio;
use App\Models\Account;
use App\Models\Combined;
use App\Models\Portfolio;
use App\Tables\Columns\LogoColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TotalOverviewTable extends BaseWidget
{
    protected static ?int $sort = 6;

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        if (Account::count() > 0 || Portfolio::count() > 0) {
            return true;
        }

        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->query(function () {
                $unionQuery = DB::table('accounts')
                    ->select([
                        DB::raw('CONCAT("a_", id) as id'),
                        'name',
                        DB::raw('(balance / 100) as market_value'),
                        'description',
                        'logo',
                        'active',
                        DB::raw('"account" as type'),
                        'user_id',
                    ])
                    ->unionAll(
                        DB::table('portfolios')
                            ->select([
                                DB::raw('CONCAT("p_", id) as id'),
                                'name',
                                DB::raw('market_value'),
                                'description',
                                'logo',
                                'active',
                                DB::raw('"portfolio" as type'),
                                'user_id',
                            ])
                    );

                return Combined::query()->fromSub($unionQuery, 'combined_models')->newQuery()->where('user_id', '=', auth()->id());
            })
            ->columns([
                LogoColumn::make('name')
                    ->label(__('account.columns.name'))
                    ->state(fn (Combined $record): array => [
                        'logo' => $record->logo,
                        'name' => $record->name,
                    ])
                    ->sortable(),
                TextColumn::make('market_value')
                    ->label(__('security.columns.market_value'))
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state === 0.0 => 'gray',
                        $state < 0.0 => 'danger',
                        default => 'success'
                    })
                    ->money(Account::getCurrency())
                    ->summarize(Sum::make()->label('')->money(config('app.currency'))),
                TextColumn::make('description')
                    ->label(__('account.columns.description'))
                    ->wrap(),
            ])
            ->paginated(false)
            ->defaultSort('name')
            ->persistSortInSession()
            ->defaultGroup('type')
            ->groupingSettingsHidden()
            ->groups([
                Group::make('type')
                    ->label('')
                    ->getTitleFromRecordUsing(function (Combined $record): string {
                        if ($record->type === 'account') {
                            return __('account.navigation_label');
                        }

                        return __('portfolio.navigation_label');
                    }),
            ])
            ->actions([
                ViewAction::make('view')
                    ->iconButton()
                    ->url(function (Combined $record): string {
                        if ($record->type === 'account') {
                            return ViewAccount::getUrl([mb_substr($record->id, 2)]);
                        }

                        return ViewPortfolio::getUrl([mb_substr($record->id, 2)]);
                    }, true),
            ])
            ->striped()
            ->recordUrl(function (Combined $record): string {
                if ($record->type === 'account') {
                    return ViewAccount::getUrl([mb_substr($record->id, 2)]);
                }

                return ViewPortfolio::getUrl([mb_substr($record->id, 2)]);
            }, true)
            ->emptyStateHeading('')
            ->emptyStateDescription('');
    }
}
