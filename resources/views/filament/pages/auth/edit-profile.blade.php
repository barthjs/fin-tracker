@php
    use App\Filament\Pages\Auth\EditProfile;
    use Carbon\Carbon;
    $pageComponent = EditProfile::isSimple() ? 'filament-panels::page.simple' : 'filament-panels::page';
@endphp

<x-dynamic-component :component="$pageComponent">
    @if(!auth()->user()->is_verified)
        <div class="rounded-lg border-l-4 border-warning-500 bg-warning-100 p-4 text-lg text-warning-600 text-center">
            {{ __('user.change_password_message') }}
        </div>
    @endif

    {{ $this->content }}

    <x-filament::section :heading="__('user.sessions.heading')">
        <div class="mt-5 space-y-6">
            @foreach ($this->sessions as $session)
                <div class="flex items-center">
                    <div>
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

                    <div class="ms-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $session['device']['platform'] ?: __('user.sessions.unknown_platform') }}
                            - {{ $session['device']['browser'] ?: __('user.sessions.unknown_browser') }}
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">
                                {{ $session['ip_address'] }},

                                @if ($session['is_current_device'])
                                    <span class="font-semibold text-primary-500">
                                        {{ __('user.sessions.this_device') }}
                                    </span>
                                @else
                                    {{ __('user.sessions.last_active') }} {{ Carbon::createFromTimestamp($session['last_active'])->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if (count($this->sessions) > 1)
                {{ $this->logoutOtherBrowserSessions }}
            @endif
        </div>
    </x-filament::section>
</x-dynamic-component>
