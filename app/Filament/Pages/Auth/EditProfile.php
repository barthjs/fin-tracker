<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Filament\Concerns\HasResourceFormFields;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;

final class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    use HasResourceFormFields;

    /** @var array<int, array<string, mixed>> */
    public array $sessions = [];

    protected string $view = 'filament.pages.auth.edit-profile';

    private bool $wasUnverified = false;

    public static function getSlug(?Panel $panel = null): string
    {
        return __('user.profile-slug');
    }

    public static function isSimple(): bool
    {
        return ! auth()->user()->is_verified;
    }

    public function mount(): void
    {
        parent::mount();

        $this->sessions = $this->getSessions();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::logoField('avatar', 'users/avatars')
                    ->label(__('user.fields.avatar')),

                $this->getFirstNameFormComponent(),
                $this->getLastNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getUsernameFormComponent(),
                $this->getCurrentPasswordFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        $record = parent::handleRecordUpdate($record, $data);

        if (! $record->is_verified && isset($data['password'])) {
            $this->wasUnverified = true;

            $record->is_verified = true;
            $record->save();
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

    protected function getFirstNameFormComponent(): Component
    {
        return TextInput::make('first_name')
            ->label(__('user.fields.first_name'))
            ->maxLength(255);
    }

    protected function getLastNameFormComponent(): Component
    {
        return TextInput::make('last_name')
            ->label(__('user.fields.last_name'))
            ->maxLength(255);
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__('user.fields.username'))
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->live(debounce: 500);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('filament-panels::auth/pages/edit-profile.form.current_password.label'))
            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.current_password.validation_attribute'))
            ->belowContent(__('filament-panels::auth/pages/edit-profile.form.current_password.below_content'))
            ->password()
            ->visible(fn (Get $get): bool => filled($get('password'))
                || ($get('email') !== $this->getUser()->getAttributeValue('email'))
                || ($get('username') !== $this->getUser()->getAttributeValue('username'))
            );
    }

    protected function logoutOtherBrowserSessions(): Action
    {
        return Action::make('logoutOtherBrowserSessions')
            ->icon('tabler-trash')
            ->size('sm')
            ->color('danger')
            ->label(__('user.sessions.delete'))
            ->modalHeading(__('user.sessions.delete'))
            ->requiresConfirmation()
            ->schema([
                TextInput::make('password')
                    ->label(__('filament-panels::auth/pages/edit-profile.form.current_password.label'))
                    ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.current_password.validation_attribute'))
                    ->password()
                    ->currentPassword(guard: Filament::getAuthGuard())
                    ->revealable()
                    ->required(),
            ])
            ->action(function (array $data): void {
                /** @var string $password */
                $password = $data['password'];
                $user = auth()->user();

                if (! Hash::check($password, $user->password)) {
                    return;
                }

                Auth::logoutOtherDevices($password);

                request()->session()->put([
                    'password_hash_'.Auth::getDefaultDriver() => $user->password,
                ]);

                DB::table('sys_sessions')
                    ->where('user_id', '=', $user->id)
                    ->where('id', '!=', request()->session()->getId())
                    ->delete();

                Notification::make()
                    ->success()
                    ->title(__('user.sessions.logout_success'))
                    ->send();

                $this->mount();
            });
    }

    /**
     * @return array<int, array{
     *     device: array{
     *         is_desktop: bool,
     *         platform: bool|string,
     *         browser: bool|string
     *     },
     *     ip_address: string|null,
     *     is_current_device: bool,
     *     last_active: int
     * }>
     */
    private function getSessions(): array
    {
        /** @var Collection<int, object{ id: string, user_agent: string|null, ip_address: string|null, last_activity: int }> $sessions */
        $sessions = DB::table('sys_sessions')
            ->where('user_id', '=', auth()->user()->id)
            ->latest('last_activity')
            ->get();

        $result = [];
        $agent = new Agent();
        foreach ($sessions as $session) {
            $agent->setUserAgent($session->user_agent);

            $result[] = [
                'device' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === request()->session()->getId(),
                'last_active' => $session->last_activity,
            ];
        }

        return $result;
    }
}
