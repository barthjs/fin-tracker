<?php

namespace App\Providers;

use Filament\Support\View\Components\Modal;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Modal::closedByClickingAway(false);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Number::useLocale(app()->getLocale());
    }
}
