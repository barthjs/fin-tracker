<div class="px-3 py-4">
    <div class="flex items-center gap-x-2.5">
        @if (!empty($getState()['logo']))
            <img
                src="{{ Storage::url($getState()['logo']) }}"
                alt="{{ $getState()['name'] }}"
                style="height: 2.5rem; width: 2.5rem"
                class="max-w-none rounded-full object-cover object-center ring-white dark:ring-gray-900"
            />
        @endif
        <span
            class="fi-ta-text-item-label text-sm leading-6 whitespace-normal text-gray-950 dark:text-white"
        >
            {{ $getState()['name'] }}
        </span>
    </div>
</div>
