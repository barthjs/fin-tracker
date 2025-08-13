@php
    $pageComponent = static::isSimple() ? 'filament-panels::page.simple' : 'filament-panels::page';
@endphp

<x-dynamic-component :component="$pageComponent">
    @if(!auth()->user()->verified)
        <div class="rounded-lg border-l-4 border-warning-500 bg-warning-100 p-4 text-lg text-warning-600 text-center">
            {{ __('user.unverified_message') }}
        </div>
    @endif

    {{ $this->content }}
</x-dynamic-component>
