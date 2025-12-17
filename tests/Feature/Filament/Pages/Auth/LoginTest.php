<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use Filament\Facades\Filament;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders the login page', function () {
    livewire(Login::class)
        ->assertOk();
});

it('redirects unauthenticated users to the login page', function () {
    get(Filament::getHomeUrl())
        ->assertRedirect(Filament::getLoginUrl());
});
