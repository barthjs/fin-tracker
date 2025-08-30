<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

final class Register extends \Filament\Auth\Pages\Register
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getUsernameFormComponent(),
                $this->getFirstNameComponent(),
                $this->getLastNameComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $data['is_verified'] = true;

        return $this->getUserModel()::create($data);
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__('user.fields.username'))
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->unique($this->getUserModel());
    }

    protected function getFirstNameComponent(): Component
    {
        return TextInput::make('first_name')
            ->label(__('user.fields.first_name'))
            ->maxLength(255);
    }

    protected function getLastNameComponent(): Component
    {
        return TextInput::make('last_name')
            ->label(__('user.fields.last_name'))
            ->maxLength(255);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/register.form.email.label'))
            ->email()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }
}
