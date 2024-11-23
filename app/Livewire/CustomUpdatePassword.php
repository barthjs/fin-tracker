<?php

namespace App\Livewire;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword;

class CustomUpdatePassword extends UpdatePassword
{
    public function submit(): void
    {
        $newPassword = Hash::make($this->form->getState()['new_password']);

        $this->user->update([
            'password' => $newPassword,
            'verified' => $this->user->verified ?: true,
        ]);

        session()->forget('password_hash_' . Filament::getCurrentPanel()->getAuthGuard());
        Filament::auth()->login($this->user);
        $this->reset(['data']);

        Notification::make()
            ->success()
            ->title(__('filament-breezy::default.profile.password.notify'))
            ->send();

        if ($this->user->verified) {
            $this->redirect('/');
        }
    }
}
