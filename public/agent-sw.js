/**
 * Service worker of the sales agent app: lets the offline order pages open without internet.
 * - Works only for pages under <app>/agent/ (registered with that scope). An older copy registered for the whole
 *   site (it also took over /admin and /owner) removes itself.
 * - Stores: the two offline pages, the app's own scripts/styles, CDN libraries and product photos.
 *   Never stores API answers (stock, prices, catalogue, barcode lookups): those always come from the network.
 * - The app asks it to save both offline pages again after every login and Sync (logout removes them).
 * - Weak signal: the offline pages and the app's scripts wait at most a few seconds for the network, then the saved copy is used.
 */
const VERSION = 'v12';
const NETWORK_WAIT_MS = 6000;
const PAGE_CACHE = 'snapkid-agent-pages-' + VERSION;
const STATIC_CACHE = 'snapkid-agent-static-' + VERSION;
const IMAGE_CACHE = 'snapkid-product-images';
const KEEP = [PAGE_CACHE, STATIC_CACHE, IMAGE_CACHE];

const BASE = new URL('./', self.location).pathname;           // e.g. "/" or "/keshav-madhav/public/"
const AGENT_SCOPE = BASE + 'agent/';
const OFFLINE_PAGES = [BASE + 'agent/offline', BASE + 'agent/offline/create'];
const HUB = OFFLINE_PAGES[0];

const STATIC_ASSETS = [
    BASE + 'assets/js/html5-qrcode.min.js',
    BASE + 'assets/js/offline-db.js',
    BASE + 'agent_assets/agent-theme.css',
    'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css',
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
    'https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css',
    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
];

function rightScope() {
    return new URL(self.registration.scope).pathname === AGENT_SCOPE;
}

/** A logged-in page (not a redirect to the login page) is worth keeping. */
async function storePage(cache, path, response) {
    if (response && response.status === 200 && !response.redirected && response.type !== 'opaqueredirect') {
        await cache.put(path, response);
    }
}

/** Saves fresh copies of both offline pages for whoever is logged in now; returns how many were saved. */
async function saveOfflinePages() {
    const pages = await caches.open(PAGE_CACHE);
    const stored = await Promise.all(OFFLINE_PAGES.map(async p => {
        try {
            const resp = await fetch(p, { credentials: 'same-origin', redirect: 'manual', cache: 'no-store' });
            const ok = resp && resp.status === 200 && !resp.redirected && resp.type !== 'opaqueredirect';
            await storePage(pages, p, resp);
            return ok ? 1 : 0;
        } catch (_) {
            return 0;
        }
    }));
    return stored.reduce((a, b) => a + b, 0);
}

/** The network's answer, or null when it fails or takes longer than ms (the request still finishes in the background). */
function networkWithin(promise, ms) {
    return Promise.race([promise.catch(() => null), new Promise(resolve => setTimeout(() => resolve(null), ms))]);
}

self.addEventListener('install', event => {
    event.waitUntil((async () => {
        if (rightScope()) {
            const statics = await caches.open(STATIC_CACHE);
            await Promise.all(STATIC_ASSETS.map(u => statics.add(u).catch(() => {})));
            await saveOfflinePages();
        }
        await self.skipWaiting();
    })());
});

// The app (after login / Sync): save both offline pages for the agent logged in now, answer how many were saved
self.addEventListener('message', event => {
    if (!event.data || event.data.type !== 'sk-keep-offline-pages' || !rightScope()) return;
    const port = event.ports && event.ports[0];
    event.waitUntil(saveOfflinePages().then(n => { if (port) port.postMessage({ stored: n }); }));
});

self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        // caches of older versions (they also kept API answers and admin pages)
        for (const key of await caches.keys()) {
            if (key.indexOf('snapkid-') === 0 && !KEEP.includes(key)) await caches.delete(key);
        }
        if (!rightScope()) {
            await self.registration.unregister();
            return;
        }
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', event => {
    if (!rightScope()) return;                    // an old whole-site copy: let the network answer
    const request = event.request;
    if (request.method !== 'GET') return;
    const url = new URL(request.url);
    const sameOrigin = url.origin === self.location.origin;

    // Product photos: from the phone first (downloaded by Sync), else the network
    if (sameOrigin && url.pathname.indexOf(BASE + 'assets/products/') === 0) {
        event.respondWith((async () => {
            const cache = await caches.open(IMAGE_CACHE);
            const hit = await cache.match(request, { ignoreSearch: true });
            if (hit) return hit;
            try {
                const resp = await fetch(request);
                if (resp && resp.status === 200) cache.put(request, resp.clone());
                return resp;
            } catch (_) {
                return new Response('', { status: 404 });
            }
        })());
        return;
    }

    // Pages: from the network when there is one; without internet (or after a few seconds of weak signal for the
    // two offline pages) the saved copy
    if (request.mode === 'navigate') {
        if (!sameOrigin || url.pathname.indexOf(AGENT_SCOPE) !== 0) return;
        const path = url.pathname.replace(/\/+$/, '');
        const isOfflinePage = OFFLINE_PAGES.includes(path);
        event.respondWith((async () => {
            const cache = await caches.open(PAGE_CACHE);
            const network = fetch(request).then(async resp => {
                if (isOfflinePage) await storePage(cache, path, resp.clone());
                return resp;
            });
            event.waitUntil(network.catch(() => {}));
            const saved = isOfflinePage ? await cache.match(path) : null;
            const resp = saved ? await networkWithin(network, NETWORK_WAIT_MS) : await network.catch(() => null);
            if (resp) return resp;
            return saved || (await cache.match(HUB))
                || new Response('<h3 style="font-family:sans-serif;padding:20px">No internet. Open the Offline page once while online first.</h3>',
                    { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } });
        })());
        return;
    }

    // The app's own scripts, styles, icons and fonts: network first (fixes load at once); the saved copy without
    // internet, or when the network takes longer than a few seconds
    if (sameOrigin && /\.(js|css|png|jpe?g|gif|svg|webp|ico|woff2?|ttf)$/i.test(url.pathname) && url.pathname.indexOf(AGENT_SCOPE) !== 0) {
        event.respondWith((async () => {
            const c = await caches.open(STATIC_CACHE);
            const network = fetch(request).then(resp => {
                if (resp && resp.status === 200) {
                    const copy = resp.clone();
                    c.put(request, copy);
                }
                return resp;
            });
            event.waitUntil(network.catch(() => {}));
            const saved = (await c.match(request)) || (await c.match(request, { ignoreSearch: true }));
            const resp = saved ? await networkWithin(network, NETWORK_WAIT_MS) : await network.catch(() => null);
            return resp || saved || Response.error();
        })());
        return;
    }

    // CDN libraries and fonts: saved copy first, refreshed in the background
    if (!sameOrigin) {
        event.respondWith((async () => {
            const cache = await caches.open(STATIC_CACHE);
            const hit = await cache.match(request);
            const refresh = fetch(request).then(resp => {
                if (resp && (resp.status === 200 || resp.type === 'opaque')) cache.put(request, resp.clone());
                return resp;
            }).catch(() => hit || Response.error());
            return hit || refresh;
        })());
        return;
    }

    // Everything else (API answers, other files): network only, never stored
});
