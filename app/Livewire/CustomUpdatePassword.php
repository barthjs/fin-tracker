<?php

declare(strict_types=1);

namespace App\Livewire;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomUpdatePassword
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('current_password')
                    ->label(__('filament-breezy::default.password_confirm.current_password'))
                    ->validationMessages(['current_password' => __('user.buttons.password_wrong_warning')])
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule('current_password'),
                TextInput::make('new_password')
                    ->label(__('filament-breezy::default.fields.new_password'))
                    ->validationMessages(['min' => __('user.buttons.password_length_warning')])
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::default())
                    ->autocomplete('new-password'),
                TextInput::make('new_password_confirmation')
                    ->label(__('user.buttons.password_new_confirmation'))
                    ->validationMessages(['same' => __('user.buttons.password_confirmation_warning')])
                    ->password()
                    ->revealable()
                    ->required()
                    ->same('new_password'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $newPassword = Hash::make($this->form->getState()['new_password']);

        $wasUnverified = ! $this->user->verified;

        $this->user->update([
            'password' => $newPassword,
            'verified' => $this->user->verified ?: true,
        ]);

        session()->forget('password_hash_'.Filament::getCurrentOrDefaultPanel()->getAuthGuard());
        Filament::auth()->login($this->user);
        $this->reset(['data']);

        Notification::make()
            ->success()
            ->title(__('filament-breezy::default.profile.password.notify'))
            ->send();

        if ($wasUnverified) {
            $this->redirect('/');
        }
    }
}
