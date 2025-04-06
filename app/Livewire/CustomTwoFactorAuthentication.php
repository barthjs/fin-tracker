<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Filament\Actions\PasswordButtonAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication;

class CustomTwoFactorAuthentication extends TwoFactorAuthentication
{
    public function enableAction(): Action
    {
        return PasswordButtonAction::make('enable')
            ->label(__('filament-breezy::default.profile.2fa.actions.enable'))
            ->action(function () {
                $this->user->enableTwoFactorAuthentication();
                Notification::make()
                    ->success()
                    ->title(__('filament-breezy::default.profile.2fa.enabled.notify'))
                    ->send();
            });
    }

    public function disableAction(): Action
    {
        return PasswordButtonAction::make('disable')
            ->label(__('filament-breezy::default.profile.2fa.actions.disable'))
            ->color('danger')
            ->requiresConfirmation()
            ->action(function () {
                $this->user->disableTwoFactorAuthentication();
                Notification::make()
                    ->warning()
                    ->title(__('filament-breezy::default.profile.2fa.disabling.notify'))
                    ->send();
            });
    }
}
