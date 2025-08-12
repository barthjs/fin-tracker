<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'tabler-home';

    protected ?string $heading = '';

    public function getColumns(): int|array
    {
        return 4;
    }
}
