<?php

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Dashboard;
use App\Models\User;

it('redirects to the login page', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});


it('displays the login page', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

it('redirects an unverified user to the profile', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/');

    $response->assertRedirect(EditProfile::getUrl());
    $user->delete();
});

it('shows the dashboard to an verified user', function () {
    $user = User::factory(['verified' => true])->create();
    $this->actingAs($user);
    $response = $this->get(Dashboard::getUrl());

    $response->assertStatus(200);
    $user->delete();
});
