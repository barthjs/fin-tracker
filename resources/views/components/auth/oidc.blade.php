@props (['mode'])
@if (!empty($availableProviders))
    <div class="flex flex-col items-center space-y-4">
        <div class="flex w-full items-center">
            <div class="app-divider grow border-t"></div>
            <span class="app-text-muted px-3">
                @if ($mode === 'login')
                    {{ __('or sign in with') }}
                @else
                    {{ __('or sign up with') }}
                @endif
            </span>
            <div class="app-divider grow border-t"></div>
        </div>

        <div class="grid @if(count($availableProviders) > 1) md:grid-cols-2 @endif gap-4 w-full">
            @foreach ($availableProviders as $provider)
                <x-filament::button
                    :icon="$provider['key']"
                    color="gray"
                    :outline="true"
                    tag="a"
                    :href="route('auth.oidc.redirect', ['provider' => $provider['key']])"
                    :spa-mode="false"
                    @class([
                        'w-full',
                        'md:col-span-2' =>
                            $loop->last && count($availableProviders) % 2 !== 0 && count($availableProviders) > 1
                    ])
                >
                    {{ $provider['label'] }}
                </x-filament::button>
            @endforeach
        </div>
    </div>
@endif
