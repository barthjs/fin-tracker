<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\CategoryGroup;
use App\Enums\Currency;
use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Portfolios\PortfolioResource;
use App\Filament\Resources\Securities\SecurityResource;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Builder;

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
            ->imageEditor()
            ->circleCropper()
            ->moveFiles()
            ->directory($directory)
            ->visibility('public')
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

    public static function categoryGroupField(string $column = 'group'): Select
    {
        return Select::make($column)
            ->label(__('category.fields.group'))
            ->options(CategoryGroup::class)
            ->default(CategoryGroup::VarExpenses)
            ->selectablePlaceholder(false)
            ->required();
    }

    public static function dateTimePickerField(string $name = 'date_time'): DateTimePicker
    {
        return DateTimePicker::make($name)
            ->label(__('fields.date_time'))
            ->autofocus()
            ->seconds(false)
            ->default(today())
            ->required();
    }

    public static function tradeAmountField(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->default(0.0)
            ->minValue(0.0)
            ->maxValue(1e9)
            ->live(true, 500);
    }

    public static function typeSelectField(string $name = 'type'): Select
    {
        return Select::make($name)
            ->label(__('fields.type'))
            ->selectablePlaceholder(false)
            ->required();
    }

    public static function notesField(?string $name = 'notes'): Textarea
    {
        return Textarea::make($name)
            ->label(__('fields.notes'))
            ->columnSpanFull()
            ->autosize()
            ->maxLength(255);
    }

    public static function accountSelectField(string $name = 'account_id'): Select
    {
        return Select::make($name)
            ->label(__('account.label'))
            ->selectablePlaceholder(false)
            ->relationship('account', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->preload()
            ->searchable()
            ->required()
            ->createOptionForm(AccountResource::getFormFields());
    }

    public static function categorySelectField(string $column = 'category_id'): Select
    {
        return Select::make($column)
            ->label(__('category.label'))
            ->selectablePlaceholder(false)
            ->relationship('category', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->preload()
            ->searchable()
            ->required()
            ->createOptionForm(CategoryResource::getFormFields());
    }

    public static function portfolioSelectField(string $column = 'portfolio_id'): Select
    {
        return Select::make($column)
            ->label(__('portfolio.label'))
            ->selectablePlaceholder(false)
            ->relationship('portfolio', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->preload()
            ->searchable()
            ->required()
            ->createOptionForm(PortfolioResource::getFormFields());
    }

    public static function securitySelectField(string $column = 'security_id'): Select
    {
        return Select::make($column)
            ->label(__('security.label'))
            ->selectablePlaceholder(false)
            ->relationship('security', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->preload()
            ->searchable()
            ->required()
            ->createOptionForm(SecurityResource::getFormFields());
    }
}
