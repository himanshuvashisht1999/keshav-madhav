const CACHE_NAME = 'snapkid-agent-v2';
const IMAGE_CACHE_NAME = 'snapkid-product-images';

const STATIC_ASSETS = [
    '/agent/offline',
    '/agent/offline/create',
    'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css',
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    '/assets/js/html5-qrcode.min.js',
    '/assets/js/offline-db.js'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.warn('Pre-cache item warning:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME && key !== IMAGE_CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Image Caching Strategy (Cache First, fallback to Network)
    if (url.pathname.includes('/assets/products/')) {
        event.respondWith(
            caches.open(IMAGE_CACHE_NAME).then(async cache => {
                const cachedResponse = await cache.match(event.request);
                if (cachedResponse) return cachedResponse;
                try {
                    const networkResponse = await fetch(event.request);
                    if (networkResponse && networkResponse.status === 200) {
                        cache.put(event.request, networkResponse.clone());
                    }
                    return networkResponse;
                } catch (e) {
                    return cachedResponse || new Response('', { status: 404 });
                }
            })
        );
        return;
    }

    // App HTML/Asset Pages: Network first, fallback to Cache
    if (event.request.mode === 'navigate' || event.request.destination === 'document') {
        event.respondWith(
            fetch(event.request)
                .then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    const cache = await caches.open(CACHE_NAME);
                    const match = await cache.match(event.request);
                    if (match) return match;
                    // Fallback to offline create or offline hub
                    if (url.pathname.includes('/agent/offline/create')) {
                        return cache.match('/agent/offline/create');
                    }
                    return cache.match('/agent/offline');
                })
        );
        return;
    }

    // General static assets: Stale while revalidate
    event.respondWith(
        caches.match(event.request).then(cached => {
            const fetchPromise = fetch(event.request).then(networkResponse => {
                if (networkResponse && networkResponse.status === 200 && event.request.method === 'GET') {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                }
                return networkResponse;
            }).catch(() => cached);
            return cached || fetchPromise;
        })
    );
});
