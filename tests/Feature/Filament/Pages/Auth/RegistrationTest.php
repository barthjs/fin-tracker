<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Register;

use function Pest\Livewire\livewire;

it('renders the registration page', function () {
    livewire(Register::class)
        ->assertOk();
});
