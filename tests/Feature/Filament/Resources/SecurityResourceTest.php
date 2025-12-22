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

it('can filter securities by inactivity', function () {
    $activeSecurity = Security::factory()->create(['is_active' => true]);
    $inactiveSecurity = Security::factory()->create(['is_active' => false]);

    livewire(ListSecurities::class)
        ->assertCanSeeTableRecords([$activeSecurity])
        ->assertCanNotSeeTableRecords([$inactiveSecurity])
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactiveSecurity])
        ->assertCanNotSeeTableRecords([$activeSecurity]);
});

it('can create a security', function () {
    $data = Security::factory()->make()->toArray();

    livewire(ListSecurities::class)
        ->callAction('create', $data)
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('securities', $data);
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

it('can edit a security', function () {
    $security = Security::factory()->create();

    $data = Security::factory()->make()->toArray();

    livewire(ViewSecurity::class, [
        'record' => $security->id,
    ])
        ->callAction('edit', $data)
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('securities', array_merge(['id' => $security->id], $data));
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
