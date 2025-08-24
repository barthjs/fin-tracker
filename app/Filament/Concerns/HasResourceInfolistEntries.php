<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

trait HasResourceInfolistEntries
{
    public static function totalValueEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->size(TextSize::Medium)
            ->weight(FontWeight::SemiBold);
    }

    public static function descriptionEntry(?string $name = 'description'): TextEntry
    {
        return TextEntry::make($name)
            ->label(__('fields.description'))
            ->size(TextSize::Small);
    }
}
