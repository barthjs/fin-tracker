<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    private bool $wasUnverified = false;

    public array $sessions = [];

    protected string $view = 'filament.pages.auth.edit-profile';

    public static function getSlug(?Panel $panel = null): string
    {
        return __('user.profile-slug');
    }

    public function mount(): void
    {
        parent::mount();

        $this->sessions = $this->getSessions();
    }

    private function getSessions(): array
    {
        $sessions = DB::table(config('session.table'))
            ->where('user_id', auth()->user()->id)
            ->latest('last_activity')
            ->get();

        return $sessions->map(function (object $session): array {
            $agent = new Agent();
            $agent->setUserAgent($session->user_agent);

            return [
                'device' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === request()->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        })->toArray();
    }

    public function logoutOtherBrowserSessions(): Action
    {
        return Action::make('logoutOtherBrowserSessions')
            ->icon('tabler-trash')
            ->label(__('user.sessions.delete'))
            ->size('sm')
            ->color('danger')
            ->modalHeading(__('user.sessions.delete'))
            ->requiresConfirmation()
            ->schema([
                TextInput::make('password')
                    ->label(__('user.buttons.password'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->action(function (array $data): void {
                if (! Hash::check($data['password'], auth()->user()->password)) {
                    Notification::make()
                        ->danger()
                        ->title(__('user.sessions.incorrect_password'))
                        ->send();

                    return;
                }

                Auth::logoutOtherDevices($data['password']);

                request()->session()->put([
                    'password_hash_'.Auth::getDefaultDriver() => auth()->user()->password,
                ]);

                DB::table(config('session.table'))
                    ->where('user_id', '=', auth()->user()->id)
                    ->where('id', '!=', request()->session()->getId())
                    ->delete();

                Notification::make()
                    ->success()
                    ->title(__('user.sessions.logout_success'))
                    ->send();

                $this->mount();
            });
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
