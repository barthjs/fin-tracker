<?php

use App\Models\User;

it('redirects to the login page', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});


it('displays the login page', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

it('shows the dashboard for an authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/');

    $response->assertStatus(200);
    $user->delete();
});
