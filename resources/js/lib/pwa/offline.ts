/** The texts of the offline page in one language. */
export type OfflineTexts = {
    title: string;
    message: string;
    retry: string;
};

/** The colors of the offline page in one color mode, taken from the theme of the app. */
export type OfflineColors = {
    background: string;
    text: string;
    muted: string;
    primary: string;
    onPrimary: string;
};

/** Holds the colors of the offline page in the light and the dark mode of the system. */
export type OfflineTheme = {
    light: OfflineColors;
    dark: OfflineColors;
};

const WIFI_OFF_ICON =
    '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h.01M8.5 16.429a5 5 0 0 1 7 0M5 12.859a10 10 0 0 1 5.17-2.69m8.83 2.69a10 10 0 0 0-2.007-1.523M2 8.82a15 15 0 0 1 4.177-2.643M22 8.82a15 15 0 0 0-11.288-3.764M2 2l20 20"/></svg>';
const REFRESH_ICON =
    '<svg viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9a9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5m5 4a9 9 0 0 1-9 9a9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></g></svg>';

/** Returns the first preferred language of the browser that the offline page has texts for, and the fallback otherwise. */
export function offlineLanguage(
    available: readonly string[],
    preferred: readonly string[],
    fallback: string,
): string {
    for (const language of preferred) {
        const code = language.split('-')[0];
        if (code && available.includes(code)) {
            return code;
        }
    }

    return fallback;
}

/** How often the offline page tries to load the app again, since an unattended screen has nobody to press its button. */
export const OFFLINE_RETRY_SECONDS = 10;

/**
 * Returns the page that a failed navigation shows instead of the error of the browser.
 * It needs no file from the server, looks like the app, and loads the app again on its own.
 */
export function offlinePage(language: string, texts: OfflineTexts, theme: OfflineTheme): string {
    const title = escapeHtml(texts.title);

    return `<!doctype html>
<html lang="${escapeHtml(language)}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<meta http-equiv="refresh" content="${OFFLINE_RETRY_SECONDS}">
<title>${title}</title>
<style>
:root { ${colorVariables(theme.light)} }
@media (prefers-color-scheme: dark) { :root { ${colorVariables(theme.dark)} } }
html, body { height: 100%; margin: 0; overflow: hidden; }
body { box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; padding: 1.5rem; text-align: center; font-family: system-ui, sans-serif; background: var(--background); color: var(--text); }
h1 { margin: 0; font-size: 1.25rem; font-weight: 600; }
p { margin: 0; max-width: 28rem; color: var(--muted); }
svg { width: 1.25rem; height: 1.25rem; }
.icon { width: 2.5rem; height: 2.5rem; color: var(--muted); }
a { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.625rem; border-radius: 0.375rem; background: var(--primary); color: var(--on-primary); font-size: 0.875rem; font-weight: 500; text-decoration: none; }
</style>
</head>
<body>
${WIFI_OFF_ICON}
<h1>${title}</h1>
<p>${escapeHtml(texts.message)}</p>
<a href="">${REFRESH_ICON}${escapeHtml(texts.retry)}</a>
</body>
</html>
`;
}

/** Returns the colors of one color mode as CSS variables. */
function colorVariables(colors: OfflineColors): string {
    return [
        `--background: ${colors.background};`,
        `--text: ${colors.text};`,
        `--muted: ${colors.muted};`,
        `--primary: ${colors.primary};`,
        `--on-primary: ${colors.onPrimary};`,
    ].join(' ');
}

/** Escapes the characters that HTML would read as markup. */
function escapeHtml(text: string): string {
    return text
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}
