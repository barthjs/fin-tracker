@php
    use App\Enums\ApiAbility;use App\Filament\Pages\Auth\EditProfile;
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
                                <x-filament::icon :icon="$slug" class="w-full h-full"/>
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

    <x-filament::section :heading="__('profile.api_tokens.heading')" collapsible collapsed>
        <x-slot name="afterHeader">
            {{ $this->createApiTokenAction }}
        </x-slot>

        <div class="-mx-6 -my-6 divide-y divide-gray-200 dark:divide-white/5">
            @if ($this->apiTokens->isEmpty())
                <x-filament::empty-state :contained="false">
                    <x-slot name="heading">
                        {{ __('profile.api_tokens.empty') }}
                    </x-slot>
                </x-filament::empty-state>
            @else
                @foreach ($this->apiTokens as $token)
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <div class="text-sm font-medium">
                                    {{ $token->name }}
                                </div>
                                <div class="text-xs text-gray-500 space-y-0.5">
                                    <div>
                                        <span class="font-medium">{{ __('fields.created_at') }}:</span>
                                        {{ $token->created_at->format('d.m.Y H:i') }}
                                    </div>
                                    @if ($token->expires_at)
                                        <div>
                                            <span class="font-medium"> {{ __('profile.api_tokens.expires_at') }}:</span>
                                            {{ $token->expires_at->format('d.m.Y') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            {{ ($this->deleteApiTokenAction)(['token' => $token->id]) }}
                        </div>

                        <x-filament::section
                            :heading="__('profile.api_tokens.abilities')"
                            collapsible
                            collapsed
                        >
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach (ApiAbility::cases() as $ability)
                                    @php
                                        $hasRead = in_array($ability->read(), $token->abilities);
                                        $hasWrite = in_array($ability->write(), $token->abilities);
                                    @endphp

                                    @if ($hasRead || $hasWrite)
                                        <li>
                                            <span class="font-medium">{{ Str::ucfirst(__("$ability->value.plural_label")) }}:</span>
                                            {{ $hasWrite ? __('profile.api_tokens.write') : __('profile.api_tokens.read') }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </x-filament::section>
                    </div>
                @endforeach
            @endif
        </div>
    </x-filament::section>

    <x-filament::section :heading="__('profile.sessions.heading')" collapsible>
        @if (count($this->sessions) > 1)
            <x-slot name="afterHeader">
                {{ $this->logoutOtherBrowserSessionsAction }}
            </x-slot>
        @endif

        <div class="-mx-6 -my-6 divide-y divide-gray-200 dark:divide-white/5">
            @foreach ($this->sessions as $session)
                <div class="flex items-center gap-4 p-6 hover:bg-gray-50 dark:hover:bg-white/5 transition duration-200">
                    <div class="flex items-center justify-center p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                        @if ($session['device']['is_desktop'])
                            <x-filament::icon
                                icon="tabler-device-desktop"
                                class="w-8 h-8 text-gray-500 dark:text-gray-400"
                            />
                        @else
                            <x-filament::icon
                                icon="tabler-device-mobile"
                                class="w-8 h-8 text-gray-500 dark:text-gray-400"
                            />
                        @endif
                    </div>

                    <div class="flex-1">
                        <div class="text-sm font-medium">
                            {{ $session['device']['platform'] ?: __('profile.sessions.unknown_platform') }}
                            - {{ $session['device']['browser'] ?: __('profile.sessions.unknown_browser') }}
                        </div>

                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            {{ $session['ip_address'] }}

                            @if($session['is_current_device'])
                                <div class="flex items-center gap-2 ml-1">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75">
                                        </span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                                    </span>
                                    <span class="font-semibold text-primary-500">
                                        {{ __('profile.sessions.this_device') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <span>
                                    {{ __('profile.sessions.last_active') }} {{ Carbon::createFromTimestamp($session['last_active'])->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section
        :heading="__('profile.delete_account')"
        :description="__('profile.delete_account_section_description')"
    >
        <x-slot name="afterHeader">
            {{ $this->deleteUserAccountAction }}
        </x-slot>
    </x-filament::section>
</x-dynamic-component>
