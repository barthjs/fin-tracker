<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\View\Components\ModalComponent;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
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
        FileUpload::configureUsing(fn (FileUpload $fileUpload): FileUpload => $fileUpload
            ->visibility('public'));

        ImageColumn::configureUsing(fn (ImageColumn $imageColumn): ImageColumn => $imageColumn
            ->visibility('public'));

        ImageEntry::configureUsing(fn (ImageEntry $imageEntry): ImageEntry => $imageEntry
            ->visibility('public'));

        Number::useLocale(app()->getLocale());

        Section::configureUsing(fn (Section $section): Section => $section
            ->columnSpanFull());

        Table::configureUsing(fn (Table $table): Table => $table
            ->deferFilters(false)
            ->paginationPageOptions([5, 10, 25, 50, 'all']));
    }
}
