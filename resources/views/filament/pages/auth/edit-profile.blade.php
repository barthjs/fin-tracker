@php
    use App\Filament\Pages\Auth\EditProfile;
    use Carbon\Carbon;
    $pageComponent = EditProfile::isSimple() ? 'filament-panels::page.simple' : 'filament-panels::page';
@endphp

<x-dynamic-component :component="$pageComponent">
    @if(!auth()->user()->is_verified)
        <div class="rounded-lg border-l-4 border-warning-500 bg-warning-100 p-4 text-lg text-warning-600 text-center">
            {{ __('profile.change_password_message') }}
        </div>
    @endif

    {{ $this->content }}

    @if (!empty($this->oidcProviders))
        <x-filament::section :heading="__('profile.oidc.heading')">
            <div class="-mx-6 -my-6 divide-y divide-gray-200 dark:divide-white/5">
                @foreach ($this->oidcProviders as $slug => $provider)
                    <div
                        class="flex items-center justify-between p-6 hover:bg-gray-50 dark:hover:bg-white/5 transition duration-200">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-8 h-8">
                                <x-filament::icon
                                    :icon="$slug"
                                    class="w-full h-full"
                                />
                            </div>

                            <div>
                                <div class="font-medium text-sm">{{ $provider['label'] }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $provider['is_connected'] ? __('profile.oidc.connected') : __('profile.oidc.not_connected') }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($provider['is_connected'])
                                {{ ($this->removeProviderAction)(['id' => $provider['id']]) }}
                            @else
                                <x-filament::button
                                    icon="tabler-link-plus"
                                    color="gray"
                                    size="sm"
                                    tag="a"
                                    :outline="true"
                                    href="{{ route('auth.oidc.redirect', ['provider' => $slug]) }}"
                                    :spa-mode="false"
                                >
                                    {{ __('profile.oidc.connect') }}
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    <x-filament::section :heading="__('profile.sessions.heading')">
        <div class="space-y-6">
            @foreach ($this->sessions as $session)
                <div class="flex items-center">
                    <div>
                        @if ($session['device']['is_desktop'])
                            <x-filament::icon
                                icon="tabler-device-desktop"
                                class="text-gray-500 dark:text-gray-400"
                            />
                        @else
                            <x-filament::icon
                                icon="tabler-device-mobile"
                                class="w-8 h-8 text-gray-500 dark:text-gray-400"
                            />
                        @endif
                    </div>

                    <div class="ms-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $session['device']['platform'] ?: __('profile.sessions.unknown_platform') }}
                            - {{ $session['device']['browser'] ?: __('profile.sessions.unknown_browser') }}
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">
                                {{ $session['ip_address'] }},

                                @if ($session['is_current_device'])
                                    <span class="font-semibold text-primary-500">
                                        {{ __('profile.sessions.this_device') }}
                                    </span>
                                @else
                                    {{ __('profile.sessions.last_active') }} {{ Carbon::createFromTimestamp($session['last_active'])->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if (count($this->sessions) > 1)
                {{ $this->logoutOtherBrowserSessionsAction }}
            @endif
        </div>
    </x-filament::section>

    <x-filament::section
        :heading="__('profile.delete_account')"
        :description="__('profile.delete_account_section_description')"
    >
        {{ $this->deleteUserAccountAction }}
    </x-filament::section>
</x-dynamic-component>
