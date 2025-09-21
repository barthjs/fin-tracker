<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Models\User;
use Filament\Facades\Filament;

it('redirects to the login page', function () {
    $response = $this->get('/');
    $response->assertRedirect(Filament::getLoginUrl());
});

it('displays the login page', function () {
    $response = $this->get('/login');
    $response->assertStatus(200)
        ->assertSeeLivewire(Login::class);
});

it('redirects an unverified user to the profile page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/');

    $response->assertRedirect(EditProfile::getUrl());
});

it('shows the dashboard to a verified user', function () {
    $user = User::factory()->create(['is_verified' => true]);
    $this->actingAs($user);
    $response = $this->get(Dashboard::getUrl());

    $response->assertStatus(200)
        ->assertSeeLivewire(Dashboard::class);
});
