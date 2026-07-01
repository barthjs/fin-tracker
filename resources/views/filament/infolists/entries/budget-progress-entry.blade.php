@php ($state = $getState())

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <x-budget-progress
        :label="$state['label']"
        :percentage="$state['percentage']"
        :width="$state['width']"
        :color="$state['color']"
    />
</x-dynamic-component>
