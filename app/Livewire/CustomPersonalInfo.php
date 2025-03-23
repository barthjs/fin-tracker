<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Filament\Forms;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{
    public array $only = ['first_name', 'last_name', 'name', 'email'];

    protected function getProfileFormSchema(): array
    {
        $groupFields = Forms\Components\Group::make([
            $this->getFirstNameComponent(),
            $this->getLastNameComponent(),
            $this->getNameComponent(),
            $this->getEmailComponent(),
        ])->columnSpanFull()->columns(2);

        return ($this->hasAvatars)
            ? [filament('filament-breezy')->getAvatarUploadComponent(), $groupFields]
            : [$groupFields];
    }

    protected function getFirstNameComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('first_name')
            ->label(__('user.columns.first_name'))
            ->maxLength(255)
            ->required()
            ->string();
    }

    protected function getLastNameComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('last_name')
            ->label(__('user.columns.last_name'))
            ->maxLength(255)
            ->required()
            ->string();
    }

    protected function getNameComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('user.columns.name'))
            ->maxLength(255)
            ->required()
            ->string()
            ->unique(User::class, ignorable: $this->user)
            ->validationMessages(['unique' => __('user.columns.name_unique_warning')]);
    }

    protected function getEmailComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('email')
            ->label(__('user.columns.email'))
            ->maxLength(255)
            ->email()
            ->unique(User::class, ignorable: $this->user)
            ->validationMessages(['unique' => __('user.columns.email_unique_warning')]);
    }
}
