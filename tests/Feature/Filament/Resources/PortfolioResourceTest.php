<?php

declare(strict_types=1);

use App\Filament\Resources\Portfolios\Pages\ListPortfolios;
use App\Filament\Resources\Portfolios\Pages\ViewPortfolio;
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

it('can load the trades relation manager', function () {
    $portfolio = Portfolio::factory()->create();

    livewire(TradesRelationManager::class, [
        'ownerRecord' => $portfolio,
        'pageClass' => ViewPortfolio::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($portfolio->trades);
});
