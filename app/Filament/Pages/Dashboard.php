<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected static ?string $navigationIcon = 'tabler-home';

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label(__('widget.period'))
                ->form([
                    DatePicker::make('created_from')
                        ->label(__('table.filter.created_from'))
                        ->default(Carbon::today()->startOfYear()),
                    DatePicker::make('created_until')
                        ->label(__('table.filter.created_until'))
                ]),
        ];
    }
}
