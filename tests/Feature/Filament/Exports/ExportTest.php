<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Filament\Exports\AccountExporter;
use App\Filament\Exports\CategoryExporter;
use App\Filament\Exports\PortfolioExporter;
use App\Filament\Exports\SecurityExporter;
use App\Filament\Exports\SubscriptionExporter;
use App\Filament\Exports\TradeExporter;
use App\Filament\Exports\TransactionExporter;
use App\Models\Account;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Subscription;
use App\Models\Trade;
use App\Models\Transaction;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Model;

beforeEach(fn () => asUser());

function makeExport(string $exporter, int $total, int $successful): Export
{
    $export = new Export;
    $export->user_id = auth()->id();
    $export->file_name = 'export';
    $export->file_disk = 'local';
    $export->exporter = $exporter;
    $export->total_rows = $total;
    $export->successful_rows = $successful;
    $export->save();

    return $export;
}

/**
 * @param  class-string<Exporter>  $exporter
 * @return array<mixed>
 */
function exportRow(string $exporter, Model $record): array
{
    $columnMap = [];
    foreach ($exporter::getColumns() as $column) {
        $columnMap[$column->getName()] = $column->getName();
    }

    return new $exporter(makeExport($exporter, 1, 1), $columnMap, [])($record);
}

it('formats an account row', function (): void {
    $account = Account::factory()->create(['name' => 'Savings', 'balance' => 1234.5]);

    expect(exportRow(AccountExporter::class, $account))
        ->toContain('Savings')
        ->toContain($account->currency->getLabel());
});

it('formats a category row with type and group labels', function (): void {
    $category = Category::factory()->create(['name' => 'Food', 'group' => CategoryGroup::VarExpenses]);

    expect(exportRow(CategoryExporter::class, $category))
        ->toContain('Food')
        ->toContain(CategoryGroup::VarExpenses->getLabel())
        ->toContain(TransactionType::Expense->getLabel());
});

it('formats a portfolio row', function (): void {
    $portfolio = Portfolio::factory()->create(['name' => 'Growth']);

    expect(exportRow(PortfolioExporter::class, $portfolio))
        ->toContain('Growth')
        ->toContain($portfolio->currency->getLabel());
});

it('formats a security row', function (): void {
    $security = Security::factory()->create(['name' => 'Apple', 'isin' => 'US0378331005']);

    expect(exportRow(SecurityExporter::class, $security))
        ->toContain('Apple')
        ->toContain('US0378331005');
});

it('formats a trade row with related names', function (): void {
    $account = Account::factory()->create(['name' => 'Broker']);
    $portfolio = Portfolio::factory()->create(['name' => 'Growth']);
    $security = Security::factory()->create(['name' => 'Apple']);

    $trade = Trade::factory()
        ->create(['account_id' => $account->id, 'portfolio_id' => $portfolio->id, 'security_id' => $security->id])
        ->fresh(['account', 'portfolio', 'security']);

    expect(exportRow(TradeExporter::class, $trade))
        ->toContain('Broker')
        ->toContain('Growth')
        ->toContain('Apple');
});

it('formats a transaction row with type label and related names', function (): void {
    $account = Account::factory()->create(['name' => 'Checking']);
    $category = Category::factory()->create(['name' => 'Food', 'group' => CategoryGroup::VarExpenses]);

    $transaction = Transaction::factory()
        ->create([
            'type' => TransactionType::Expense,
            'payee' => 'Shop',
            'account_id' => $account->id,
            'category_id' => $category->id,
        ])
        ->load(['account', 'transferAccount', 'category']);

    expect(exportRow(TransactionExporter::class, $transaction))
        ->toContain('Shop')
        ->toContain('Checking')
        ->toContain('Food')
        ->toContain(TransactionType::Expense->getLabel());
});

it('formats a subscription row with related names', function (): void {
    $account = Account::factory()->create(['name' => 'Main']);
    $category = Category::factory()->create(['name' => 'Bills']);

    $subscription = Subscription::factory()
        ->create(['account_id' => $account->id, 'category_id' => $category->id, 'name' => 'Internet'])
        ->load(['account', 'category']);

    expect(exportRow(SubscriptionExporter::class, $subscription))
        ->toContain('Internet')
        ->toContain('Main')
        ->toContain('Bills');
});

it('builds the completed notification body with success and failure counts', function (string $exporter): void {
    $successOnly = makeExport($exporter, total: 5, successful: 5);
    expect($exporter::getCompletedNotificationBody($successOnly))->toContain('5');

    $withFailures = makeExport($exporter, total: 5, successful: 3);
    expect($exporter::getCompletedNotificationBody($withFailures))
        ->toContain('3')
        ->toContain('2');
})->with([
    AccountExporter::class,
    CategoryExporter::class,
    PortfolioExporter::class,
    SecurityExporter::class,
    SubscriptionExporter::class,
    TradeExporter::class,
    TransactionExporter::class,
]);

it('builds a file name', function (string $exporter): void {
    $export = makeExport($exporter, total: 1, successful: 1);

    expect(new $exporter($export, [], [])->getFileName($export))->toBeString()->not->toBeEmpty();
})->with([
    AccountExporter::class,
    CategoryExporter::class,
    PortfolioExporter::class,
    SecurityExporter::class,
    SubscriptionExporter::class,
    TradeExporter::class,
    TransactionExporter::class,
]);
