<x-filament-panels::page>
    <x-filament::section>
        <div id="version">
            <h1>
                {{ __('settings.current_version') }}: {{ config('app.version') }}
            </h1>
            <a href="https://hub.docker.com/r/barthjs/fin-tracker/tags" target="_blank">
                <h1>
                    {{ __('settings.latest_version') }}: {{ $latestVersion }}
                </h1>
            </a>
        </div>
    </x-filament::section>
</x-filament-panels::page>
