<?php

declare(strict_types=1);

use App\Filament\Resources\Securities\Pages\ListSecurities;
use App\Filament\Resources\Securities\Pages\ViewSecurity;
use App\Filament\Resources\Securities\RelationManagers\TradesRelationManager;
use App\Models\Security;

use function Pest\Livewire\livewire;

beforeEach(fn () => asAdmin());

it('renders the list page', function () {
    $securities = Security::factory()->count(3)->create();

    livewire(ListSecurities::class)
        ->assertOk()
        ->assertCanSeeTableRecords($securities);
});

it('renders the view page', function () {
    $security = Security::factory()->create();

    livewire(ViewSecurity::class, [
        'record' => $security->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'market_value' => $security->market_value,
            'price' => $security->price,
            'total_quantity' => $security->total_quantity,
            'type' => $security->type,
        ], 'infolist');
});

it('can load the trades relation manager', function () {
    $security = Security::factory()->create();

    livewire(TradesRelationManager::class, [
        'ownerRecord' => $security,
        'pageClass' => ViewSecurity::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($security->trades);
});
