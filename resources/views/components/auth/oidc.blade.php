@props(['mode'])
@if(!empty($availableProviders))
    <div class="flex flex-col items-center space-y-4">
        <div class="flex items-center w-full">
            <div class="grow border-t border-gray-300 dark:border-gray-700"></div>
            <span class="px-3 text-gray-500">
            @if($mode === 'login')
                    {{ __('or sign in with') }}
                @else
                    {{ __('or sign up with') }}
                @endif
        </span>
            <div class="grow border-t border-gray-300 dark:border-gray-700"></div>
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
                        'md:col-span-2' => $loop->last && count($availableProviders) % 2 !== 0 && count($availableProviders) > 1,
                    ])
                >
                    {{ $provider['label'] }}
                </x-filament::button>
            @endforeach
        </div>
    </div>
@endif
