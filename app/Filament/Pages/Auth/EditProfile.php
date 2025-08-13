<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class EditProfile extends BaseEditProfile
{
    private bool $wasUnverified = false;

    protected string $view = 'filament.pages.auth.edit-profile';

    public static function getSlug(?Panel $panel = null): string
    {
        return __('user.profile-slug');
    }

    public static function isSimple(): bool
    {
        if (auth()->user()->verified) {
            return false;
        }

        return true;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record * */
        $record = parent::handleRecordUpdate($record, $data);

        if (! $record->verified && isset($data['password'])) {
            $this->wasUnverified = true;

            $record->update([
                'verified' => true,
            ]);
        }

        return $record;
    }

    protected function getRedirectUrl(): ?string
    {
        if ($this->wasUnverified) {
            return '/';
        }

        return null;
    }

    public function getAvatarUploadComponent(): FileUpload
    {
        return FileUpload::make('avatar')
            ->label(__('user.columns.avatar'))
            ->avatar()
            ->image()
            ->imageEditor()
            ->circleCropper()
            ->moveFiles()
            ->directory('logos/avatars')
            ->maxSize(1024);
    }

    protected function getFirstNameFormComponent(): Component
    {
        return TextInput::make('first_name')
            ->label(__('user.columns.first_name'))
            ->maxLength(255);
    }

    protected function getLastNameFormComponent(): Component
    {
        return TextInput::make('last_name')
            ->label(__('user.columns.last_name'))
            ->maxLength(255);
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('user.columns.name'))
            ->validationMessages(['unique' => __('user.columns.name_unique_warning')])
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('filament-panels::auth/pages/edit-profile.form.current_password.label'))
            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.current_password.validation_attribute'))
            ->belowContent(__('filament-panels::auth/pages/edit-profile.form.current_password.below_content'))
            ->password()
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn (Get $get): bool => filled($get('password')) || ($get('email') !== $this->getUser()->getAttributeValue('email')))
            ->dehydrated(false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getAvatarUploadComponent(),
                $this->getFirstNameFormComponent(),
                $this->getLastNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getUsernameFormComponent(),
                $this->getCurrentPasswordFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
