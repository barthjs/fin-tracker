<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\Currency;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

trait HasResourceFormFields
{
    public static function nameField(?string $name = 'name'): TextInput
    {
        return TextInput::make($name)
            ->label(__('fields.name'))
            ->autofocus()
            ->required()
            ->maxLength(255);
    }

    public static function currencyField(?string $name = 'currency'): Select
    {
        return Select::make($name)
            ->label(__('fields.currency'))
            ->options(Currency::class)
            ->default(Currency::getCurrency())
            ->selectablePlaceholder(false)
            ->required()
            ->searchable();
    }

    public static function colorField(?string $name = 'color'): ColorPicker
    {
        return ColorPicker::make($name)
            ->label(__('fields.color'))
            ->default(mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))))
            ->required()
            ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/')
            ->validationMessages(['regex' => __('validation.hex_color', ['attribute' => __('fields.color')])]);
    }

    public static function statusToggleField(?string $name = 'is_active'): Toggle
    {
        return Toggle::make($name)
            ->label(__('fields.status'))
            ->default(true)
            ->inline(false);
    }

    public static function logoField(?string $name = 'logo', ?string $directory = 'logos'): FileUpload
    {
        return FileUpload::make($name)
            ->label(__('fields.logo'))
            ->avatar()
            ->image()
            ->imageEditor()
            ->circleCropper()
            ->moveFiles()
            ->directory($directory)
            ->maxSize(1024);
    }

    public static function descriptionField(?string $name = 'description'): Textarea
    {
        return Textarea::make($name)
            ->label(__('fields.description'))
            ->autosize()
            ->rows(4)
            ->maxLength(1000);
    }
}
