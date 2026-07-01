@php ($state = $getState())

<x-budget-progress
    class="min-w-48 px-3 py-4"
    :label="$state['label']"
    :percentage="$state['percentage']"
    :width="$state['width']"
    :color="$state['color']"
/>
