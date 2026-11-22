import { isGatewayError } from '@/lib/http/errors';
import {
    offlineLanguage,
    offlinePage,
    type OfflineTexts,
    type OfflineTheme,
} from '@/lib/pwa/offline';

declare const self: ServiceWorkerGlobalScope;

// Set by the build from the locale files and the theme
declare const __PWA_OFFLINE_TEXTS__: Record<string, OfflineTexts>;
declare const __PWA_DEFAULT_LANGUAGE__: string;
declare const __PWA_OFFLINE_THEME__: OfflineTheme;

const OFFLINE_TEXTS = __PWA_OFFLINE_TEXTS__;

self.addEventListener('install', () => {
    void self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([self.clients.claim(), self.registration.navigationPreload.enable()]),
    );
});

// Only navigations get the offline page, every other request reaches the network untouched
self.addEventListener('fetch', (event) => {
    if (event.request.mode !== 'navigate') {
        return;
    }

    event.respondWith(navigationResponse(event));
});

/**
 * Answers a navigation from the network, and with the offline page when the network fails
 * or a reverse proxy finds the server down.
 */
async function navigationResponse(event: FetchEvent): Promise<Response> {
    try {
        const preloaded: Response | undefined = await event.preloadResponse;
        const response = preloaded ?? (await fetch(event.request));
        return isGatewayError(response.status) ? offlineResponse() : response;
    } catch {
        return offlineResponse();
    }
}

/** Returns the offline page in the language of the browser. */
function offlineResponse(): Response {
    const language = offlineLanguage(
        Object.keys(OFFLINE_TEXTS),
        self.navigator.languages,
        __PWA_DEFAULT_LANGUAGE__,
    );
    const texts = OFFLINE_TEXTS[language];
    if (!texts) {
        return Response.error();
    }

    return new Response(offlinePage(language, texts, __PWA_OFFLINE_THEME__), {
        status: 503,
        headers: {
            'Cache-Control': 'no-store',
            'Content-Type': 'text/html; charset=utf-8',
        },
    });
}
