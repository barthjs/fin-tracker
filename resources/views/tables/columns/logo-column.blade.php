<div class="fi-ta-image px-3 py-4">
    <div class="flex items-center gap-x-2.5">
        @if (!empty($getState()['logo']))
            <img src="{{ Storage::url($getState()['logo']) }}"
                 style="height: 2.5rem; width: 2.5rem;"
                 class="max-w-none object-cover object-center rounded-full ring-white dark:ring-gray-900"/>
        @endif
        <div class="hidden 2xl:block">
            {{ $getState()['name'] }}
        </div>
    </div>
</div>
