<x-filament-breezy::grid-section md=2 :title="__('filament-breezy::default.profile.password.heading')"
                                 :description="__('filament-breezy::default.profile.password.subheading')">
    <x-filament::card>
        @if(!$this->user->verified)
            <div class="mb-6 rounded-lg border-l-4 border-warning-500 bg-warning-100 p-4 text-lg text-warning-600">
                {{ __('user.unverified_message') }}
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    {{ __('filament-breezy::default.profile.password.submit.label') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>
