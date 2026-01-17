<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Register;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders the register page', function () {
    livewire(Register::class)
        ->assertOk()
        ->assertSee(__('user.fields.username'))
        ->assertSee(__('user.fields.first_name'))
        ->assertSee(__('user.fields.last_name'))
        ->assertSee(__('filament-panels::auth/pages/register.form.email.label'));
});

it('shows oidc buttons on register page when enabled', function () {
    get(Filament::getRegistrationUrl())
        ->assertSee('OIDC');
});

it('can register a new user', function () {
    livewire(Register::class)
        ->fillForm([
            'username' => 'user',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ])
        ->call('register')
        ->assertHasNoFormErrors()
        ->assertRedirect(Filament::getUrl());

    assertDatabaseHas('sys_users', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'user',
        'email' => 'user@example.com',
        'is_verified' => true,
        'is_active' => true,
        'is_admin' => false,
    ]);

    assertAuthenticated();
});

it('validates unique username', function () {
    User::factory()->create(['username' => 'user']);

    livewire(Register::class)
        ->fillForm([
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ])
        ->call('register')
        ->assertHasErrors(['data.username']);
});

it('validates unique email', function () {
    User::factory()->create(['email' => 'user@example.com']);

    livewire(Register::class)
        ->fillForm([
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ])
        ->call('register')
        ->assertHasErrors(['data.email']);
});

it('requires password confirmation', function () {
    livewire(Register::class)
        ->fillForm([
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => 'password',
            'passwordConfirmation' => 'different-password',
        ])
        ->call('register')
        ->assertHasErrors(['data.password']);
});
