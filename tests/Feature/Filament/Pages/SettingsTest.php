<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use App\Filament\Pages\Settings;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the settings page', function () {
    livewire(Settings::class)
        ->assertOk();
});
