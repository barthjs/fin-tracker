<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Support\View\Components\ModalComponent;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        ModalComponent::closedByClickingAway(false);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Number::useLocale(app()->getLocale());
    }
}
