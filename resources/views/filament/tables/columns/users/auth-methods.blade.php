<div class="flex flex-wrap gap-2 px-4">
    @php
        /** @var User $record */
        use App\Models\User;
        $record = $getRecord();
    @endphp

    @if ($record->password !== null)
        <div title="{{ __('user.fields.password') }}" class="flex items-center justify-center">
            <x-filament::icon icon="tabler-lock" class="w-5 h-5"/>
        </div>
    @endif

    @foreach ($record->providers as $provider)
        <div title="{{ ucfirst($provider->provider_name) }}" class="flex items-center justify-center">
            <x-filament::icon
                :icon="$provider->provider_name"
                class="w-5 h-5"
            />
        </div>
    @endforeach
</div>
