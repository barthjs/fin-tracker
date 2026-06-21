<?php

declare(strict_types=1);

use App\Enums\Country;
use App\Enums\SecurityRegion;
use App\Enums\SecuritySector;
use App\Enums\SecurityType;
use App\Enums\TradeType;
use App\Filament\Resources\Securities\Pages\ListSecurities;
use App\Filament\Resources\Securities\Pages\ViewSecurity;
use App\Filament\Resources\Securities\RelationManagers\TradesRelationManager;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asAdmin());

it('renders the list page', function (): void {
    $securities = Security::factory()->count(3)->create();

    livewire(ListSecurities::class)
        ->assertOk()
        ->assertCanSeeTableRecords($securities)
        ->assertActionExists('import')
        ->assertActionExists('export');
});

it('can filter securities by inactivity', function (): void {
    $activeSecurity = Security::factory()->create(['is_active' => true]);
    $inactiveSecurity = Security::factory()->create(['is_active' => false]);

    livewire(ListSecurities::class)
        ->assertCanSeeTableRecords([$activeSecurity])
        ->assertCanNotSeeTableRecords([$inactiveSecurity])
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactiveSecurity])
        ->assertCanNotSeeTableRecords([$activeSecurity]);
});

it('can filter securities by classification', function (): void {
    $matching = Security::factory()->create([
        'type' => SecurityType::ETF,
        'region' => SecurityRegion::Europe,
        'sector' => SecuritySector::Financials,
        'country' => Country::DE,
    ]);
    $other = Security::factory()->create(['region' => SecurityRegion::AsiaPacific]);
    $inactive = Security::factory()->create(['region' => SecurityRegion::Europe, 'is_active' => false]);

    livewire(ListSecurities::class)
        ->filterTable('type', [SecurityType::ETF->value])
        ->filterTable('region', [SecurityRegion::Europe->value])
        ->filterTable('sector', [SecuritySector::Financials->value])
        ->filterTable('country', [Country::DE->value])
        ->assertCanSeeTableRecords([$matching])
        ->assertCanNotSeeTableRecords([$other, $inactive]);
});

it('can group securities by classification', function (string $group): void {
    $securities = Security::factory()->count(2)->create();
    $unassigned = Security::factory()->create(['region' => null, 'sector' => null]);

    livewire(ListSecurities::class)
        ->set('tableGrouping', $group)
        ->assertCanSeeTableRecords($securities->push($unassigned));
})->with(['region', 'sector']);

it('can create a security', function (): void {
    $data = Security::factory()->make()->toArray();

    livewire(ListSecurities::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('securities', $data);
});

it('renders the view page', function (): void {
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
            'ter' => $security->ter,
            'region' => $security->region,
            'sector' => $security->sector,
            'country' => $security->country,
        ], 'infolist');
});

it('can edit a security', function (): void {
    $security = Security::factory()->create();

    $data = Security::factory()->make()->toArray();

    livewire(ViewSecurity::class, [
        'record' => $security->id,
    ])
        ->callAction('edit', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('securities', array_merge(['id' => $security->id], $data));
});

it('can bulk edit the classification of securities', function (string $field, string $value): void {
    $securities = Security::factory()->count(2)->create();

    livewire(ListSecurities::class)
        ->callTableBulkAction($field, $securities, [$field => $value])
        ->assertHasNoTableBulkActionErrors();

    foreach ($securities as $security) {
        assertDatabaseHas('securities', [
            'id' => $security->id,
            $field => $value,
        ]);
    }
})->with([
    'region' => ['region', SecurityRegion::Europe->value],
    'sector' => ['sector', SecuritySector::Financials->value],
    'country' => ['country', Country::DE->value],
]);

it('can bulk clear the classification of securities', function (): void {
    $securities = Security::factory()->count(2)->create(['region' => SecurityRegion::Europe]);

    livewire(ListSecurities::class)
        ->callTableBulkAction('region', $securities, ['region' => null])
        ->assertHasNoTableBulkActionErrors();

    foreach ($securities as $security) {
        assertDatabaseHas('securities', [
            'id' => $security->id,
            'region' => null,
        ]);
    }
});

it('can delete a security', function (): void {
    $security = Security::factory()->create();

    livewire(ViewSecurity::class, ['record' => $security->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    assertModelMissing($security);
});

it('can load the trades relation manager', function (): void {
    $security = Security::factory()->create();

    livewire(TradesRelationManager::class, [
        'ownerRecord' => $security,
        'pageClass' => ViewSecurity::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($security->trades);
});

it('recalculates portfolio market value when the price changes', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create(['price' => 10.0]);

    Trade::factory()->create([
        'type' => TradeType::Buy,
        'quantity' => 5.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);

    $data = Security::factory()->make(['price' => 20.0])->toArray();

    livewire(ListSecurities::class)
        ->callAction(TestAction::make('edit')->table($security), $data)
        ->assertHasNoFormErrors();

    expect($security->fresh()?->price)->toBe(20.0)
        ->and($portfolio->fresh()?->market_value)->toBe(100.0);
});
