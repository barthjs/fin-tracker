<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Concerns\HasResourceActions;
use App\Jobs\PrepareCsvExport;
use BezhanSalleh\LanguageSwitch\Events\LocaleChanged;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Carbon\CarbonImmutable;
use Filament\Actions\Exports\Jobs\PrepareCsvExport as BasePrepareCsvExport;
use Filament\Facades\Filament;
use Filament\Support\View\Components\ModalComponent;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    use HasResourceActions;

    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Laravel
        Date::use(CarbonImmutable::class);
        Model::shouldBeStrict();
        Model::unguard();
        Vite::useAggressivePrefetching();

        // Filament
        Filament::serving(function (): void {
            Event::listen(function (LocaleChanged $event): void {
                auth()->user()?->setLocale($event->locale);
            });
            $this->app->bind(BasePrepareCsvExport::class, PrepareCsvExport::class);
            Number::useLocale(app()->getLocale());

            ModalComponent::closedByClickingAway(false);
            Password::defaults(fn (): ?Password => app()->isProduction() ? Password::min(12) : null);
            Table::configureUsing(fn (Table $table): Table => $table
                ->paginationPageOptions([5, 10, 25, 50, 'all'])
                ->extremePaginationLinks()
                ->reorderableColumns()
                ->deferColumnManager(false)
                ->defaultSort('name')
                ->persistSortInSession()
                ->striped()
                ->deferFilters(false)
                ->persistFiltersInSession()
                ->recordActions([
                    self::tableEditAction(),
                    self::tableDeleteAction(),
                ])
                ->emptyStateDescription(null)
                ->emptyStateActions([
                    self::createAction(),
                ])
            );
        });

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->visible(outsidePanels: true)
                ->locales(['de', 'en']);
        });
    }
}
