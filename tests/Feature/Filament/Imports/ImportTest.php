<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\Country;
use App\Enums\PeriodUnit;
use App\Enums\SecurityRegion;
use App\Enums\SecuritySector;
use App\Enums\SecurityType;
use App\Enums\TradeType;
use App\Enums\TransactionType;
use App\Filament\Imports\AccountImporter;
use App\Filament\Imports\BudgetImporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Imports\PortfolioImporter;
use App\Filament\Imports\SecurityImporter;
use App\Filament\Imports\SubscriptionImporter;
use App\Filament\Imports\TradeImporter;
use App\Filament\Imports\TransactionImporter;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Security;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

/**
 * Runs a single CSV row through an importer's full lifecycle
 * (remap, cast, validate, fill, save).
 *
 * @param  class-string<Importer>  $importer
 * @param  array<string, mixed>  $row
 */
function importRow(string $importer, array $row): void
{
    $import = new Import;
    $import->user_id = auth()->id();
    $import->file_name = 'import.csv';
    $import->file_path = 'imports/import.csv';
    $import->importer = $importer;
    $import->total_rows = 1;
    $import->processed_rows = 0;
    $import->successful_rows = 0;
    $import->save();

    $columnMap = [];
    foreach (array_keys($row) as $key) {
        $columnMap[$key] = $key;
    }

    new $importer($import, $columnMap, [])($row);
}

it('builds the completed notification body with success and failure counts', function (string $importer): void {
    $import = new Import;
    $import->user_id = auth()->id();
    $import->file_name = 'import.csv';
    $import->file_path = 'import.csv';
    $import->importer = $importer;
    $import->total_rows = 5;
    $import->processed_rows = 5;
    $import->successful_rows = 3;
    $import->save();

    expect($importer::getCompletedNotificationBody($import))
        ->toContain('3')
        ->toContain('2');
})->with([
    AccountImporter::class,
    BudgetImporter::class,
    CategoryImporter::class,
    PortfolioImporter::class,
    SecurityImporter::class,
    SubscriptionImporter::class,
    TradeImporter::class,
    TransactionImporter::class,
]);

describe('AccountImporter', function (): void {
    it('imports an account and deduplicates on re-import', function (): void {
        $row = [
            'name' => 'Savings',
            'currency' => 'EUR',
            'color' => '#aabbcc',
            'is_active' => '1',
        ];

        importRow(AccountImporter::class, $row);
        importRow(AccountImporter::class, $row);

        expect(Account::query()->where('name', 'Savings')->count())->toBe(1);
        assertDatabaseHas('accounts', [
            'name' => 'Savings',
            'color' => '#aabbcc',
            'user_id' => auth()->id(),
        ]);
    });

    it('imports the low balance notification of an account', function (): void {
        $account = Account::factory()->make();

        importRow(AccountImporter::class, [
            'name' => $account->name,
            'currency' => $account->currency->value,
            'color' => $account->color,
            'notify_on_low_balance' => '1',
            'low_balance_threshold' => '-250,50',
        ]);

        assertDatabaseHas('accounts', [
            'name' => $account->name,
            'notify_on_low_balance' => true,
            'low_balance_threshold' => -250.5,
        ]);
    });

    it('checks the balance of an imported account', function (): void {
        $account = Account::factory()->make();

        importRow(AccountImporter::class, [
            'name' => $account->name,
            'currency' => $account->currency->value,
            'color' => $account->color,
            'notify_on_low_balance' => '1',
            'low_balance_threshold' => '100',
        ]);

        expect(Account::query()->where('name', $account->name)->sole()->low_balance_notified_at)->not->toBeNull();
    });
});

describe('BudgetImporter', function (): void {
    it('imports a budget resolving its category by name', function (): void {
        $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

        importRow(BudgetImporter::class, [
            'amount' => '400',
            'category' => $category->name,
            'period_unit' => PeriodUnit::Month->value,
            'period_frequency' => '1',
            'starts_at' => today()->startOfMonth()->toDateString(),
            'warning_threshold' => '80',
            'notify_on_exceed' => '1',
            'is_active' => '1',
        ]);

        assertDatabaseHas('budgets', [
            'category_id' => $category->id,
            'amount' => 400.0,
            'warning_threshold' => 80,
            'notify_on_exceed' => true,
        ]);
    });

    it('rejects a budget import row with a warning threshold of 100 or more', function (): void {
        $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

        expect(fn () => importRow(BudgetImporter::class, [
            'amount' => '400',
            'category' => $category->name,
            'period_unit' => PeriodUnit::Month->value,
            'period_frequency' => '1',
            'starts_at' => today()->startOfMonth()->toDateString(),
            'warning_threshold' => '100',
        ]))->toThrow(ValidationException::class);
    });

    it('rejects a budget import row with an amount of zero', function (): void {
        $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

        expect(fn () => importRow(BudgetImporter::class, [
            'amount' => '0',
            'category' => $category->name,
            'period_unit' => PeriodUnit::Month->value,
            'period_frequency' => '1',
            'starts_at' => today()->startOfMonth()->toDateString(),
        ]))->toThrow(ValidationException::class);
    });

    it('rejects a budget import row with a revenue category', function (): void {
        $category = Category::factory()->create(['group' => CategoryGroup::FixRevenues]);

        expect(fn () => importRow(BudgetImporter::class, [
            'amount' => '400',
            'category' => $category->name,
            'period_unit' => PeriodUnit::Month->value,
            'period_frequency' => '1',
            'starts_at' => today()->startOfMonth()->toDateString(),
        ]))->toThrow(RowImportFailedException::class, __('budget.validation.category_expense'));
    });

    it('rejects a budget import row for a category that already has a budget', function (): void {
        $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
        Budget::factory()->create(['category_id' => $category->id]);

        expect(fn () => importRow(BudgetImporter::class, [
            'amount' => '400',
            'category' => $category->name,
            'period_unit' => PeriodUnit::Month->value,
            'period_frequency' => '1',
            'starts_at' => today()->startOfMonth()->toDateString(),
        ]))->toThrow(RowImportFailedException::class, __('budget.validation.category_unique'));
    });
});

describe('CategoryImporter', function (): void {
    it('imports a category, derives the type and deduplicates on re-import', function (): void {
        $row = [
            'name' => 'Food',
            'group' => CategoryGroup::VarExpenses->getLabel(),
            'color' => '#aabbcc',
            'is_active' => '1',
        ];

        importRow(CategoryImporter::class, $row);
        importRow(CategoryImporter::class, $row);

        expect(Category::query()->where('name', 'Food')->count())->toBe(1);
        assertDatabaseHas('categories', [
            'name' => 'Food',
            'group' => CategoryGroup::VarExpenses->value,
            'type' => TransactionType::Expense->value,
        ]);
    });
});

describe('PortfolioImporter', function (): void {
    it('imports a portfolio and deduplicates on re-import', function (): void {
        $row = [
            'name' => 'Growth',
            'currency' => 'EUR',
            'color' => '#aabbcc',
            'is_active' => '1',
        ];

        importRow(PortfolioImporter::class, $row);
        importRow(PortfolioImporter::class, $row);

        expect(Portfolio::query()->where('name', 'Growth')->count())->toBe(1);
    });
});

describe('SecurityImporter', function (): void {
    it('imports a security with its type and deduplicates on re-import', function (): void {
        $row = [
            'name' => 'Apple',
            'isin' => 'US0378331005',
            'type' => SecurityType::Stock->getLabel(),
            'symbol' => 'AAPL',
            'price' => '150',
            'color' => '#aabbcc',
            'is_active' => '1',
        ];

        importRow(SecurityImporter::class, $row);
        importRow(SecurityImporter::class, $row);

        expect(Security::query()->where('name', 'Apple')->count())->toBe(1);
        assertDatabaseHas('securities', [
            'name' => 'Apple',
            'type' => SecurityType::Stock->value,
            'price' => 150.0,
        ]);
    });

    it('imports a security with classification values or labels', function (): void {
        importRow(SecurityImporter::class, [
            'name' => 'Allianz',
            'type' => SecurityType::Stock->getLabel(),
            'region' => SecurityRegion::Europe->getLabel(),
            'sector' => SecuritySector::Financials->value,
            'country' => mb_strtolower(Country::DE->value),
            'exchange' => 'XETRA',
            'ter' => '0,20',
            'website' => 'https://www.allianz.com',
            'price' => '250',
        ]);

        assertDatabaseHas('securities', [
            'name' => 'Allianz',
            'region' => SecurityRegion::Europe->value,
            'sector' => SecuritySector::Financials->value,
            'country' => Country::DE->value,
            'exchange' => 'XETRA',
            'ter' => 0.2,
            'website' => 'https://www.allianz.com',
        ]);
    });

    it('imports a security without classification fields', function (): void {
        importRow(SecurityImporter::class, [
            'name' => 'Allianz',
            'type' => SecurityType::Stock->getLabel(),
            'region' => '',
            'sector' => '',
            'country' => '',
            'ter' => '',
            'price' => '250',
        ]);

        assertDatabaseHas('securities', [
            'name' => 'Allianz',
            'region' => null,
            'sector' => null,
            'country' => null,
            'ter' => null,
        ]);
    });

    it('rejects a security import row with an unknown classification value', function (): void {
        expect(fn () => importRow(SecurityImporter::class, [
            'name' => 'Allianz',
            'type' => SecurityType::Stock->getLabel(),
            'country' => 'ZZ',
            'price' => '250',
        ]))->toThrow(RowImportFailedException::class);
    });
});

describe('SubscriptionImporter', function (): void {
    it('imports a subscription resolving account and category by name', function (): void {
        $account = Account::factory()->create(['name' => 'Main']);
        $category = Category::factory()->create(['name' => 'Bills']);

        importRow(SubscriptionImporter::class, [
            'name' => 'Internet',
            'amount' => '40',
            'period_unit' => PeriodUnit::Month->value,
            'period_frequency' => '1',
            'started_at' => '2026-06-01',
            'next_payment_date' => '2026-12-01',
            'account' => 'Main',
            'category' => 'Bills',
            'color' => '#aabbcc',
        ]);

        assertDatabaseHas('subscriptions', [
            'name' => 'Internet',
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 40.0,
        ]);
    });
});

describe('TradeImporter', function (): void {
    it('imports a trade and resolves account, portfolio and security', function (): void {
        $account = Account::factory()->create(['name' => 'Broker']);
        $portfolio = Portfolio::factory()->create(['name' => 'Growth']);
        $security = Security::factory()->create(['name' => 'Apple', 'isin' => 'US0378331005']);

        importRow(TradeImporter::class, [
            'date_time' => '2026-01-15 10:00:00',
            'type' => TradeType::Buy->getLabel(),
            'quantity' => '10',
            'price' => '15',
            'tax' => '0',
            'fee' => '0',
            'account_id' => 'Broker',
            'portfolio' => 'Growth',
            'isin' => 'US0378331005',
        ]);

        assertDatabaseHas('trades', [
            'account_id' => $account->id,
            'portfolio_id' => $portfolio->id,
            'security_id' => $security->id,
            'type' => TradeType::Buy->value,
            'quantity' => 10.0,
        ]);
    });

    it('falls back to default account, portfolio and security on ambiguous trade data', function (): void {
        Account::factory()->count(2)->create(['name' => 'Broker']);
        Portfolio::factory()->count(2)->create(['name' => 'Growth']);
        Security::factory()->count(2)->create(['isin' => 'US0378331005']);

        importRow(TradeImporter::class, [
            'date_time' => '2026-01-15 10:00:00',
            'type' => 'Unknown Type',
            'quantity' => '10',
            'price' => '15',
            'tax' => '0',
            'fee' => '0',
            'account_id' => 'Broker',
            'portfolio' => 'Growth',
            'isin' => 'US0378331005',
        ]);

        // Ambiguous names and unknown type all fall back to defaults.
        assertDatabaseHas('accounts', ['name' => 'Demo', 'user_id' => auth()->id()]);
        assertDatabaseHas('portfolios', ['name' => 'Demo', 'user_id' => auth()->id()]);
        assertDatabaseHas('securities', ['name' => 'Demo', 'user_id' => auth()->id()]);
        assertDatabaseHas('trades', ['type' => TradeType::Buy->value]);
    });
});

describe('TransactionImporter', function (): void {
    it('imports a transaction, resolves the category by name and falls back to a default account', function (): void {
        $category = Category::factory()->create(['name' => 'Groceries', 'group' => CategoryGroup::VarExpenses]);

        importRow(TransactionImporter::class, [
            'date_time' => '2026-01-15 10:00:00',
            'type' => TransactionType::Expense->getLabel(),
            'amount' => '100',
            'payee' => 'Shop',
            'category_id' => 'Groceries',
            'group' => CategoryGroup::VarExpenses->getLabel(),
        ]);

        assertDatabaseHas('transactions', [
            'payee' => 'Shop',
            'amount' => 100.0,
            'category_id' => $category->id,
        ]);
        // No account column exists, so it falls back to the default "Demo" account.
        assertDatabaseHas('accounts', ['name' => 'Demo', 'user_id' => auth()->id()]);
    });

    it('falls back to the current time for an unparseable date', function (): void {
        Category::factory()->create(['name' => 'Groceries', 'group' => CategoryGroup::VarExpenses]);

        importRow(TransactionImporter::class, [
            'date_time' => 'not-a-real-date',
            'type' => TransactionType::Expense->getLabel(),
            'amount' => '50',
            'payee' => 'Shop',
            'category_id' => 'Groceries',
            'group' => CategoryGroup::VarExpenses->getLabel(),
        ]);

        assertDatabaseHas('transactions', [
            'payee' => 'Shop',
            'date_time' => now()->toDateTimeString(),
        ]);
    });

    it('falls back to the default type, category and transfer account on ambiguous data', function (): void {
        Account::factory()->count(2)->create(['name' => 'Shared']);
        Category::factory()->count(2)->create(['name' => 'Groceries', 'group' => CategoryGroup::VarExpenses]);

        importRow(TransactionImporter::class, [
            'date_time' => '2026-01-15 10:00:00',
            'type' => TransactionType::Transfer->getLabel(),
            'amount' => '50',
            'payee' => 'Shop',
            'category_id' => 'Groceries',
            'group' => CategoryGroup::VarExpenses->getLabel(),
            'transfer_account_id' => 'Shared',
        ]);

        // Ambiguous category name -> a default "Demo" category is created and used.
        assertDatabaseHas('categories', ['name' => 'Demo', 'user_id' => auth()->id()]);
        assertDatabaseHas('transactions', ['payee' => 'Shop', 'type' => TransactionType::Transfer->value]);
    });

    it('uses the default category when the imported category does not exist', function (): void {
        importRow(TransactionImporter::class, [
            'date_time' => '2026-01-15 10:00:00',
            'type' => 'Unknown Type',
            'amount' => '50',
            'payee' => 'Shop',
            'category_id' => 'Missing Category',
            'group' => 'Unknown Group',
        ]);

        // Unknown type falls back to Expense, unknown category falls back to "Demo".
        assertDatabaseHas('transactions', ['payee' => 'Shop', 'type' => TransactionType::Expense->value]);
        assertDatabaseHas('categories', ['name' => 'Demo', 'user_id' => auth()->id()]);
    });
});
