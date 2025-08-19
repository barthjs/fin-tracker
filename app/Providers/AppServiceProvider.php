<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\View\Components\ModalComponent;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
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
        Password::defaults(fn (): ?Password => app()->isProduction() ? Password::min(12)->max(255) : null);
        Model::shouldBeStrict();
        Model::unguard();
        Number::useLocale(app()->getLocale());
        Vite::useAggressivePrefetching();

        // Filament
        FileUpload::configureUsing(fn (FileUpload $fileUpload): FileUpload => $fileUpload->visibility('public'));
        ImageColumn::configureUsing(fn (ImageColumn $imageColumn): ImageColumn => $imageColumn->visibility('public'));
        ImageEntry::configureUsing(fn (ImageEntry $imageEntry): ImageEntry => $imageEntry->visibility('public'));
        ModalComponent::closedByClickingAway(false);
        Section::configureUsing(fn (Section $section): Section => $section->columnSpanFull());
        Table::configureUsing(fn (Table $table): Table => $table
            ->deferFilters(false)
            ->paginationPageOptions([5, 10, 25, 50, 'all']));
    }
}
