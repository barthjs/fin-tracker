<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use App\Filament\Pages\Dashboard;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the dashboard page', function () {
    livewire(Dashboard::class)
        ->assertOk();
});
