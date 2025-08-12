<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Forms\Components\TextInput;

class PasswordButtonAction
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->isPasswordSessionValid()) {
            $this->requiresConfirmation()
                ->modalHeading(__('filament-breezy::default.password_confirm.heading'))
                ->modalDescription(__('filament-breezy::default.password_confirm.description'))
                ->form([
                    TextInput::make('current_password')
                        ->label(__('filament-breezy::default.password_confirm.current_password'))
                        ->validationMessages(['current_password' => __('user.buttons.password_wrong_warning')])
                        ->required()
                        ->password()
                        ->revealable()
                        ->rule('current_password')
                        ->autocomplete('password'),
                ]);
        }
    }
}
