<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Currency;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Portfolios\Pages\ViewPortfolio;
use App\Models\Account;
use App\Models\Combined;
use App\Models\Portfolio;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

final class TotalOverviewTable extends BaseWidget
{
    use HasResourceTableColumns;

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
                        DB::raw("CONCAT('a_', id) AS id"),
                        'name',
                        DB::raw('(balance / 100) AS market_value'),
                        'description',
                        'logo',
                        'is_active',
                        DB::raw("'account' AS type"),
                        'user_id',
                    ])
                    ->unionAll(
                        DB::table('portfolios')
                            ->select([
                                DB::raw("CONCAT('p_', id) AS id"),
                                'name',
                                DB::raw('market_value'),
                                'description',
                                'logo',
                                'is_active',
                                DB::raw("'portfolio' AS type"),
                                'user_id',
                            ])
                    );

                return Combined::query()->fromSub($unionQuery, 'combined_models')->newQuery()->where('user_id', auth()->id());
            })
            ->columns([
                self::logoAndNameColumn()
                    ->label(__('fields.name'))
                    ->state(fn (Combined $record): array => [
                        'logo' => $record->logo,
                        'name' => $record->name,
                    ])
                    ->sortable(),
                TextColumn::make('market_value')
                    ->label(__('fields.market_value'))
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state === 0.0 => 'gray',
                        $state < 0.0 => 'danger',
                        default => 'success'
                    })
                    ->money(Currency::getCurrency())
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency())),
                TextColumn::make('description')
                    ->label(__('fields.description'))
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
                            return __('account.plural_label');
                        }

                        return __('portfolio.plural_label');
                    }),
            ])
            ->recordActions([
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
            ->emptyStateHeading(null)
            ->emptyStateDescription(null);
    }
}
