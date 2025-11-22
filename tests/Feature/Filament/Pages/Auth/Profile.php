<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders the profile page', function () {
    livewire(EditProfile::class)
        ->assertOk();
});

it('redirects unverified users to the profile page', function () {
    actingAs(User::factory()->create());

    get(Filament::getLoginUrl())
        ->assertRedirect(EditProfile::getUrl());
});
