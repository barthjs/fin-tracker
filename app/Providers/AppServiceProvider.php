<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Concerns\HasResourceActions;
use App\Jobs\ExportCompletionWithLocale;
use App\Jobs\ExportCsvWithLocale;
use App\Jobs\ImportCsvWithLocale;
use App\Services\Oidc\OidcProvider;
use App\Services\Oidc\OidcService;
use BezhanSalleh\LanguageSwitch\Events\LocaleChanged;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Carbon\CarbonImmutable;
use Filament\Actions\Exports\Jobs\ExportCompletion;
use Filament\Actions\Exports\Jobs\ExportCsv as BaseExportCsv;
use Filament\Actions\Imports\Jobs\ImportCsv as BaseImportCsv;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\Support\View\Components\ModalComponent;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use SocialiteProviders\Manager\SocialiteWasCalled;

final class AppServiceProvider extends ServiceProvider
{
    use HasResourceActions;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseImportCsv::class, ImportCsvWithLocale::class);
        $this->app->bind(BaseExportCsv::class, ExportCsvWithLocale::class);
        $this->app->bind(ExportCompletion::class, ExportCompletionWithLocale::class);
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

        Event::listen(function (SocialiteWasCalled $event): void {
            $oidcService = $this->app->make(OidcService::class);

            foreach (array_keys($oidcService->getEnabledProviders()) as $provider) {
                $class = 'SocialiteProviders\\'.str($provider)->studly().'\\Provider';

                if (class_exists($class)) {
                    $event->extendSocialite($provider, $class);

                    continue;
                }

                if (config()->boolean('services.oidc.oidc_enabled')) {
                    $event->extendSocialite($provider, OidcProvider::class);
                }
            }
        });

        // Filament
        Filament::serving(function (): void {
            Event::listen(function (LocaleChanged $event): void {
                auth()->user()?->setLocale($event->locale);
            });
            Number::useLocale(app()->getLocale());

            FilamentView::registerRenderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => Blade::render('<x-auth.oidc mode="login" />'),
            );

            FilamentView::registerRenderHook(
                PanelsRenderHook::AUTH_REGISTER_FORM_AFTER,
                fn (): string => Blade::render('<x-auth.oidc mode="register" />'),
            );

            ModalComponent::closedByClickingAway(! app()->isProduction());
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
