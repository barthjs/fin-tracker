@props (['label', 'percentage', 'width', 'color'])

<div {{ $attributes }}>
    <div class="app-text-muted mb-1 flex items-center justify-between gap-x-2 text-xs">
        <span class="font-mono whitespace-nowrap">{{ $label }}</span>
        <span class="font-semibold whitespace-nowrap">{{ $percentage }}%</span>
    </div>

    <div class="budget-progress-track h-2 w-full overflow-hidden rounded-full">
        <div
            @class (['budget-progress-bar fi-color h-2 rounded-full', 'fi-color-' . $color])
            style="width: {{ $width }}%"
        ></div>
    </div>
</div>
