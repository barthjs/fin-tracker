<?php

declare(strict_types=1);

use App\Filament\Resources\Securities\Pages\ViewSecurity;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\AccountsRelationManager;
use App\Filament\Resources\Users\RelationManagers\SecuritiesRelationManager;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(fn() => asAdmin());

it('renders the list page', function () {
    $users = User::factory()->count(3)->create();

    livewire(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords($users);
});

it('renders the view page', function () {
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

it('can load the accounts relation manager', function () {
    $user = User::factory()->create();

    livewire(AccountsRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewUser::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($user->accounts);
});

it('can load the securities relation manager', function () {
    $user = User::factory()->create();

    livewire(SecuritiesRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => ViewSecurity::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($user->securities);
});

