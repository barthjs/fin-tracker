<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use App\Filament\Pages\Settings;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the settings page', function () {
    Http::fake([
        'https://api.github.com/repos/barthjs/fin-tracker/releases/latest' => Http::response([
            'tag_name' => 'v1.2.3',
        ], 200),
    ]);

    livewire(Settings::class)
        ->assertOk();
});
