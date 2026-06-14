<?php

declare(strict_types=1);

use App\Enums\TradeType;
use App\Filament\Resources\Portfolios\Pages\ListPortfolios;
use App\Filament\Resources\Portfolios\Pages\ViewPortfolio;
use App\Filament\Resources\Portfolios\RelationManagers\SecuritiesRelationManager;
use App\Filament\Resources\Portfolios\RelationManagers\TradesRelationManager;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function (): void {
    $portfolios = Portfolio::factory()->count(3)->create();

    livewire(ListPortfolios::class)
        ->assertOk()
        ->assertCanSeeTableRecords($portfolios)
        ->assertActionExists('import')
        ->assertActionExists('export');
});

it('can filter portfolios by inactivity', function (): void {
    $activePortfolio = Portfolio::factory()->create(['is_active' => true]);
    $inactivePortfolio = Portfolio::factory()->create(['is_active' => false]);

    livewire(ListPortfolios::class)
        ->assertCanSeeTableRecords([$activePortfolio])
        ->assertCanNotSeeTableRecords([$inactivePortfolio])
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactivePortfolio])
        ->assertCanNotSeeTableRecords([$activePortfolio]);
});

it('can create a portfolio', function (): void {
    $data = Portfolio::factory()->make()->toArray();

    livewire(ListPortfolios::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('portfolios', $data);
});

it('renders the view page', function (): void {
    $portfolio = Portfolio::factory()->create();

    livewire(ViewPortfolio::class, [
        'record' => $portfolio->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'market_value' => $portfolio->market_value,
            'description' => $portfolio->description,
        ], 'infolist');
});

it('can edit a portfolio', function (): void {
    $portfolio = Portfolio::factory()->create();
    $data = Portfolio::factory()->make()->toArray();

    livewire(ViewPortfolio::class, ['record' => $portfolio->id])
        ->callAction('edit', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('portfolios', array_merge(['id' => $portfolio->id], $data));
});

it('can delete a portfolio', function (): void {
    $portfolio = Portfolio::factory()->create();

    livewire(ViewPortfolio::class, ['record' => $portfolio->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    assertModelMissing($portfolio);
});

it('can load the securities relation manager and shows the per-portfolio quantity', function (): void {
    $portfolio = Portfolio::factory()->create();
    $account = Account::factory()->create();
    $security = Security::factory()->create();
    Trade::factory()->create([
        'type' => TradeType::Buy,
        'quantity' => 10.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);

    livewire(SecuritiesRelationManager::class, [
        'ownerRecord' => $portfolio,
        'pageClass' => ViewPortfolio::class,
    ])
        ->loadTable()
        ->assertOk()
        ->assertCanSeeTableRecords([$security]);
});

it('can load the trades relation manager', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    $data = Trade::factory()->make([
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ])->toArray();
    $data['date_time'] = now()->startOfMinute()->toDateTimeString();

    livewire(TradesRelationManager::class, [
        'ownerRecord' => $portfolio,
        'pageClass' => ViewPortfolio::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($portfolio->trades)
        ->callAction(TestAction::make('create')->table(), $data);
});
