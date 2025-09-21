<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Concerns\HasResourceActions;
use App\Jobs\ImportCsvWithLocale;
use App\Jobs\PrepareCsvExport;
use BezhanSalleh\LanguageSwitch\Events\LocaleChanged;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Carbon\CarbonImmutable;
use Filament\Actions\Exports\Jobs\PrepareCsvExport as BasePrepareCsvExport;
use Filament\Actions\Imports\Jobs\ImportCsv as BaseImportCsv;
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
    public function register(): void
    {
        $this->app->bind(BaseImportCsv::class, ImportCsvWithLocale::class);
        $this->app->bind(BasePrepareCsvExport::class, PrepareCsvExport::class);
    }

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
            Number::useLocale(app()->getLocale());

            ModalComponent::closedByClickingAway(false);
            Password::defaults(fn (): ?Password => app()->isProduction() ? Password::min(12) : null);
            Table::configureUsing(fn (Table $table): Table => $table
                ->paginationPageOptions([10, 25, 50, 100, 'all'])
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
            );
        });

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
            $switch
                ->visible(outsidePanels: true)
                ->locales(['de', 'en']);
        });
    }
}
