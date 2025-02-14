<div class="px-3 py-4">
    <div class="flex items-center gap-x-2.5">
        @if (!empty($getState()['logo']))
            <img src="{{ Storage::url($getState()['logo']) }}"
                 style="height: 2.5rem; width: 2.5rem;"
                 class="max-w-none object-cover object-center rounded-full ring-white dark:ring-gray-900"/>
        @endif
        <span class="fi-ta-text-item-label whitespace-normal text-sm leading-6 text-gray-950 dark:text-white">
            {{ $getState()['name'] }}
        </span>
    </div>
</div>
