<?php

declare(strict_types=1);

use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Portfolios\Pages\ViewPortfolio;
use App\Filament\Resources\Securities\Pages\ViewSecurity;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\AccountsRelationManager;
use App\Filament\Resources\Users\RelationManagers\CategoriesRelationManager;
use App\Filament\Resources\Users\RelationManagers\PortfoliosRelationManager;
use App\Filament\Resources\Users\RelationManagers\SecuritiesRelationManager;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asAdmin());

it('renders the list page', function (): void {
    $users = User::factory()->count(3)->create();

    livewire(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords($users);
});

it('renders the view page', function (): void {
    $user = User::factory()->create();

    livewire(ViewUser::class, [
        'record' => $user->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'username' => $user->username,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
        ], 'infolist');
});

it('filters users by tab', function (): void {
    $verified = User::factory()->verified()->create();
    $inactive = User::factory()->verified()->inactive()->create();
    $unverified = User::factory()->create();

    livewire(ListUsers::class)
        ->set('activeTab', 'inactive')
        ->assertCanSeeTableRecords([$inactive])
        ->assertCanNotSeeTableRecords([$verified, $unverified])
        ->set('activeTab', 'unverified')
        ->assertCanSeeTableRecords([$unverified])
        ->assertCanNotSeeTableRecords([$verified, $inactive]);
});

it('can load the accounts relation manager', function (): void {
    $user = User::factory()->create();

    livewire(AccountsRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewUser::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($user->accounts);
});

it('can load the categories relation manager', function (): void {
    $user = User::factory()->create();

    livewire(CategoriesRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewCategory::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($user->categories);
});

it('can load the portfolios relation manager', function (): void {
    $user = User::factory()->create();

    livewire(PortfoliosRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewPortfolio::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($user->portfolios);
});

it('can load the securities relation manager', function (): void {
    $user = User::factory()->create();

    livewire(SecuritiesRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewSecurity::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($user->securities);
});

it('can create a user', function (): void {
    livewire(CreateUser::class)
        ->fillForm([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
            'passwordConfirmation' => 'Password123!',
            'is_admin' => false,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, [
        'username' => 'testuser',
        'email' => 'testuser@example.com',
    ]);
});

it('can edit a user', function (): void {
    $user = User::factory()->create();

    livewire(EditUser::class, ['record' => $user->id])
        ->fillForm(['first_name' => 'Updated'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(User::class, ['id' => $user->id, 'first_name' => 'Updated']);
});

it('can delete a user', function (): void {
    $user = User::factory()->create();

    livewire(ListUsers::class)
        ->callAction(TestAction::make('delete')->table($user));

    assertModelMissing($user);
});
