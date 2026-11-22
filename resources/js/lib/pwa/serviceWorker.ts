/** The service worker of every build, at the root so that its scope covers the whole app. */
const SERVICE_WORKER_URL = '/sw.js';

/**
 * Registers the service worker of the build.
 */
export function registerServiceWorker(): void {
    if (!import.meta.env.PROD || !('serviceWorker' in navigator)) {
        return;
    }

    navigator.serviceWorker.register(SERVICE_WORKER_URL).catch((err: unknown) => {
        console.warn('[pwa] the service worker failed to register', err);
    });
}
