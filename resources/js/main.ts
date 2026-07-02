/*
 * Paginated tables outside of widgets fill the viewport from their top edge downwards, so their pagination and
 * summary are visible without scrolling the page. CSS cannot know where a table starts, because page headers,
 * tabs and widgets above it differ per page, so the offset is measured here and handed to the theme as a variable.
 */
const STICKY_TABLE_SELECTOR =
    '.fi-ta-ctn:has(> .fi-ta-main > .fi-pagination):not(.fi-wi-table .fi-ta-ctn)';
const STICKY_TABLE_TOP_VARIABLE = '--app-sticky-table-top';

// Tables starting further down than this share of the viewport are scrolled to and use the full viewport height
const STICKY_TABLE_MAX_TOP_RATIO = 0.6;

/** Measures the top edge of every sticky table and stores it as a CSS variable on the table container. */
function updateStickyTableTops(): void {
    const maxTop = window.innerHeight * STICKY_TABLE_MAX_TOP_RATIO;

    for (const table of document.querySelectorAll<HTMLElement>(STICKY_TABLE_SELECTOR)) {
        const top = table.getBoundingClientRect().top + window.scrollY;

        if (top > maxTop) {
            table.style.removeProperty(STICKY_TABLE_TOP_VARIABLE);
            continue;
        }

        table.style.setProperty(STICKY_TABLE_TOP_VARIABLE, `${Math.round(top)}px`);
    }
}

// Livewire morphs drop the inline variable and content above a table can change its height, both resize the document
new ResizeObserver(updateStickyTableTops).observe(document.documentElement);
window.addEventListener('resize', updateStickyTableTops);
document.addEventListener('livewire:navigated', updateStickyTableTops);
