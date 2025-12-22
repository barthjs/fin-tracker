<?php

declare(strict_types=1);

use App\Filament\Resources\Portfolios\Pages\ListPortfolios;
use App\Filament\Resources\Portfolios\Pages\ViewPortfolio;
use App\Filament\Resources\Portfolios\RelationManagers\SecuritiesRelationManager;
use App\Filament\Resources\Portfolios\RelationManagers\TradesRelationManager;
use App\Models\Portfolio;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function () {
    $portfolios = Portfolio::factory()->count(3)->create();

    livewire(ListPortfolios::class)
        ->assertOk()
        ->assertCanSeeTableRecords($portfolios);
});

it('can filter portfolios by inactivity', function () {
    $activePortfolio = Portfolio::factory()->create(['is_active' => true]);
    $inactivePortfolio = Portfolio::factory()->create(['is_active' => false]);

    livewire(ListPortfolios::class)
        ->assertCanSeeTableRecords([$activePortfolio])
        ->assertCanNotSeeTableRecords([$inactivePortfolio])
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactivePortfolio])
        ->assertCanNotSeeTableRecords([$activePortfolio]);
});

it('can create a portfolio', function () {
    $data = Portfolio::factory()->make()->toArray();

    livewire(ListPortfolios::class)
        ->callAction('create', $data)
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('portfolios', $data);
});

it('renders the view page', function () {
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

it('can edit a portfolio', function () {
    $portfolio = Portfolio::factory()->create();
    $data = Portfolio::factory()->make()->toArray();

    livewire(ViewPortfolio::class, ['record' => $portfolio->id])
        ->callAction('edit', $data)
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('portfolios', array_merge(['id' => $portfolio->id], $data));
});

it('can load the securities relation manager', function () {
    $portfolio = Portfolio::factory()->create();

    livewire(SecuritiesRelationManager::class, [
        'ownerRecord' => $portfolio,
        'pageClass' => ViewPortfolio::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($portfolio->securities);
});

it('can load the trades relation manager', function () {
    $portfolio = Portfolio::factory()->create();

    livewire(TradesRelationManager::class, [
        'ownerRecord' => $portfolio,
        'pageClass' => ViewPortfolio::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($portfolio->trades);
});
