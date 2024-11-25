<x-filament-panels::page>
    <x-filament::section heading="{{ __('settings.about') }}">
        <h1 class="text-2xl">{{ config('app.name') }}</h1>
        <div id="version" class="py-6">
            <div class="py-1">
                <h3>
                    {{ __('settings.current_version') }}: {{ config('app.version') }}
                </h3>
            </div>
            <div class="py-1">
                <x-filament::link href='https://hub.docker.com/r/barthjs/fin-tracker/tags' target="_blank"
                                  size="LARGE">
                    {{ __('settings.latest_version')}}: {{ $latestVersion }}
                </x-filament::link>
            </div>
            <div class="py-1">
                <x-filament::button tag="a" href='https://github.com/barthjs/fin-tracker/issues' target="_blank"
                                    color="info" icon="github">
                    {{ __('settings.report_bug') }}
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
