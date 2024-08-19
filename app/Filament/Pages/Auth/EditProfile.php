<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->label(__('resources.users.table.first_name'))
                    ->autofocus()
                    ->maxLength(255)
                    ->required()
                    ->string(),
                TextInput::make('last_name')
                    ->label(__('resources.users.table.last_name'))
                    ->maxLength(255)
                    ->required()
                    ->string(),
                TextInput::make('name')
                    ->label(__('resources.users.table.name'))
                    ->maxLength(255)
                    ->required()
                    ->string()
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label(__('resources.users.table.email'))
                    ->maxLength(255)
                    ->email()
                    ->unique(ignoreRecord: true),
                $this->getPasswordFormComponent()
                    ->label(__('resources.users.password')),
                $this->getPasswordConfirmationFormComponent()
                    ->label(__('resources.users.password_confirmation')),
            ]);
    }
}
