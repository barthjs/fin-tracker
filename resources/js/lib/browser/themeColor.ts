/**
 * Keeps the theme color of the document at the background of the page
 * and returns a function that stops it. Browsers paint their own bars in this color,
 * and the background changes with the color mode and the colors set on the root element.
 */
export function syncThemeColor(): () => void {
    updateThemeColor();

    const observer = new MutationObserver(updateThemeColor);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class', 'style'],
    });

    return () => observer.disconnect();
}

/** Sets the theme color to the background of the page, unless the page has none yet. */
function updateThemeColor(): void {
    const color = getComputedStyle(document.body).backgroundColor;
    if (isTransparent(color)) {
        return;
    }

    themeColorMeta().content = color;
}

/** Reports whether a computed color paints nothing. */
function isTransparent(color: string): boolean {
    return color === '' || color === 'transparent' || color === 'rgba(0, 0, 0, 0)';
}

/**
 * Returns the only theme color of the document. The page follows the color mode of the app
 * instead of the one of the system, so the variants per color scheme of the static page give way to it.
 */
function themeColorMeta(): HTMLMetaElement {
    const [first, ...rest] = document.head.querySelectorAll<HTMLMetaElement>(
        'meta[name="theme-color"]',
    );
    for (const meta of rest) {
        meta.remove();
    }

    if (first) {
        first.removeAttribute('media');
        return first;
    }

    const meta = document.createElement('meta');
    meta.name = 'theme-color';
    document.head.append(meta);
    return meta;
}
