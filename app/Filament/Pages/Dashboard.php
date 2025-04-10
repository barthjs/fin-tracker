<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'tabler-home';

    protected ?string $heading = '';

    public function getColumns(): int|string|array
    {
        return 4;
    }
}
