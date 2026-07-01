<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Budgets\BudgetResource;
use App\Filament\Resources\Budgets\Pages\ViewBudget;
use App\Models\Budget;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class BudgetOverviewWidget extends TableWidget
{
    use HasResourceTableColumns;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    /**
     * Shows the widget only when a budget is currently running.
     */
    public static function canView(): bool
    {
        return self::currentBudgetsQuery()->exists();
    }

    public function getTableHeading(): string
    {
        return __('budget.plural_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => self::currentBudgetsQuery()->with(['category', 'user']))
            ->columns([
                self::nameColumn('category.name')
                    ->label(Str::ucfirst(__('category.label')))
                    ->weight(FontWeight::SemiBold),

                TextColumn::make('usage')
                    ->label(__('budget.fields.usage'))
                    ->view('filament.tables.columns.budget-progress-column')
                    ->state(fn (Budget $record): array => BudgetResource::usageState($record)),
            ])
            ->defaultSort(fn (Builder $query): Builder => BudgetResource::orderByCategoryName($query))
            ->paginated(false)
            ->recordUrl(fn (Budget $record): string => ViewBudget::getUrl(['record' => $record]));
    }

    /**
     * Queries active budgets whose date range contains today.
     *
     * @return Builder<Budget>
     */
    private static function currentBudgetsQuery(): Builder
    {
        $today = today(auth()->user()->timezone)->toDateString();

        return Budget::query()
            ->where('is_active', true)
            ->whereDate('starts_at', '<=', $today)
            ->where(function (Builder $query) use ($today): void {
                $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today);
            });
    }
}
