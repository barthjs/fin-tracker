<div class="px-3 py-4">
    <div class="flex items-center gap-x-2.5">
        @if (!empty($getState()['logo']))
            <img
                src="{{ Storage::url($getState()['logo']) }}"
                alt="{{ $getState()['name'] }}"
                class="size-10 max-w-none rounded-full object-cover object-center"
            />
        @endif
        <span class="fi-ta-text-item-label app-text-strong text-sm leading-6 whitespace-normal">
            {{ $getState()['name'] }}
        </span>
    </div>
</div>
