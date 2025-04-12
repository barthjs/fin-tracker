<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{
    public array $only = ['avatar', 'first_name', 'last_name', 'name', 'email'];

    protected function getProfileFormSchema(): array
    {
        $groupFields = Group::make([
            $this->getFirstNameComponent(),
            $this->getLastNameComponent(),
            $this->getNameComponent(),
            $this->getEmailComponent(),
        ])->columnSpanFull()->columns(2);

        return ($this->hasAvatars)
            ? [$this->getAvatarUploadComponent(), $groupFields]
            : [$groupFields];
    }

    public function getAvatarUploadComponent(): FileUpload
    {
        return FileUpload::make('avatar')
            ->label(__('filament-breezy::default.fields.avatar'))
            ->avatar()
            ->image()
            ->imageEditor()
            ->circleCropper()
            ->moveFiles()
            ->directory('logos/avatars')
            ->maxSize(1024);
    }

    protected function getFirstNameComponent(): TextInput
    {
        return TextInput::make('first_name')
            ->label(__('user.columns.first_name'))
            ->maxLength(255);
    }

    protected function getLastNameComponent(): TextInput
    {
        return TextInput::make('last_name')
            ->label(__('user.columns.last_name'))
            ->maxLength(255);
    }

    protected function getNameComponent(): TextInput
    {
        return TextInput::make('name')
            ->label(__('user.columns.name'))
            ->validationMessages(['unique' => __('user.columns.name_unique_warning')])
            ->required()
            ->maxLength(255)
            ->unique(User::class, ignorable: $this->user);
    }

    protected function getEmailComponent(): TextInput
    {
        return TextInput::make('email')
            ->label(__('user.columns.email'))
            ->validationMessages(['unique' => __('user.columns.email_unique_warning')])
            ->email()
            ->maxLength(255)
            ->unique(User::class, ignorable: $this->user);
    }
}
