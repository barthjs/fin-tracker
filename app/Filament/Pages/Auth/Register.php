<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Register extends BaseRegister
{
    protected function handleRegistration(array $data): Model
    {
        $data['verified'] = true;

        return $this->getUserModel()::create($data);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getFirstNameComponent(),
                        $this->getLastNameComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('user.columns.name'))
            ->validationMessages(['unique' => __('user.columns.name_unique_warning')])
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->unique($this->getUserModel());
    }

    protected function getFirstNameComponent(): Component
    {
        return TextInput::make('first_name')
            ->label(__('user.columns.first_name'))
            ->maxLength(255);
    }

    protected function getLastNameComponent(): Component
    {
        return TextInput::make('last_name')
            ->label(__('user.columns.last_name'))
            ->maxLength(255);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('user.columns.email'))
            ->validationMessages(['unique' => __('user.columns.email_unique_warning')])
            ->email()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('user.buttons.password'))
            ->validationMessages(['min' => __('user.buttons.password_length_warning')])
            ->password()
            ->revealable()
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('user.buttons.password_confirmation'))
            ->validationMessages(['same' => __('user.buttons.password_confirmation_warning')])
            ->password()
            ->revealable()
            ->required()
            ->dehydrated(false)
            ->same('password');
    }
}
