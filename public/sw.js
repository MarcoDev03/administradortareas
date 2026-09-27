/**
 * NegocioManager - Progressive Web App (PWA) Service Worker
 * Version: 1.0.0
 * 
 * Strategy Overview:
 * 1. Non-GET requests (POST, PUT, PATCH, DELETE) -> Network Only (no interception)
 * 2. HTML Navigation requests (pages) -> Network-First, with Offline Fallback (/offline.html).
 *    Dynamic authenticated pages are NEVER permanently cached to protect data integrity.
 * 3. Static core assets (icons, manifest, offline page) -> Cache-First with Network fallback.
 * 4. API/JSON calls -> Network Only.
 */

const CACHE_VERSION = 'v1.0.1';
const STATIC_CACHE_NAME = 'negociomanager-static-' + CACHE_VERSION;
const OFFLINE_CACHE_NAME = 'negociomanager-offline-' + CACHE_VERSION;

const CORE_STATIC_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/favicon.ico',
    '/icons/icon-72x72.png',
    '/icons/icon-96x96.png',
    '/icons/icon-128x128.png',
    '/icons/icon-144x144.png',
    '/icons/icon-152x152.png',
    '/icons/icon-192x192.png',
    '/icons/icon-384x384.png',
    '/icons/icon-512x512.png',
    '/icons/icon-maskable-192x192.png',
    '/icons/icon-maskable-512x512.png',
    '/icons/apple-touch-icon.png',
    '/icons/icon.svg'
];

// 1. INSTALL EVENT
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME).then((cache) => {
            return cache.addAll(CORE_STATIC_ASSETS).catch((err) => {
                console.warn('[PWA ServiceWorker] Pre-cache warning (some assets may not be available yet):', err);
            });
        }).then(() => {
            // Activate immediately
            return self.skipWaiting();
        })
    );
});

// 2. ACTIVATE EVENT - Clean old versions of caches
self.addEventListener('activate', (event) => {
    const validCaches = [STATIC_CACHE_NAME, OFFLINE_CACHE_NAME];

    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (!validCaches.includes(cacheName)) {
                        console.log('[PWA ServiceWorker] Deleting old cache version:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            // Take control of all open client tabs immediately
            return self.clients.claim();
        })
    );
});

// 3. MESSAGE EVENT - Allow clients to trigger skipWaiting on demand
self.addEventListener('message', (event) => {
    if (event.data && event.data.action === 'skipWaiting') {
        self.skipWaiting();
    }
});

// 4. FETCH EVENT - Intelligent routing
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // RULE 1: Only handle GET requests. All POST/PUT/PATCH/DELETE go direct to Laravel
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // RULE 2: Navigation (HTML document requests) -> Network-First with Offline fallback
    if (request.mode === 'navigate' || (request.headers.get('accept') && request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Return fresh response from network directly
                    return response;
                })
                .catch(async () => {
                    // If network fails (user is offline or server unreachable), return offline page
                    const cache = await caches.open(STATIC_CACHE_NAME);
                    const offlinePage = await cache.match('/offline.html');
                    return offlinePage || new Response('Estás sin conexión. Por favor reintenta cuando tengas conexión a internet.', {
                        status: 503,
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // RULE 3: Core static assets & icons (manifest, icons, fonts, offline.html)
    const isCoreAsset = CORE_STATIC_ASSETS.some(path => url.pathname.endsWith(path)) ||
                        url.pathname.startsWith('/icons/');

    if (isCoreAsset) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Return from cache and update in background if online
                    fetch(request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            caches.open(STATIC_CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }

                // Not in cache, fetch from network and store
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(STATIC_CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // RULE 4: All other requests (AJAX, dynamic assets, API calls) -> Network with graceful fallback
    event.respondWith(
        fetch(request).catch(async () => {
            const cachedResponse = await caches.match(request);
            if (cachedResponse) {
                return cachedResponse;
            }
            return new Response('', { status: 408, statusText: 'Request timed out or offline' });
        })
    );
});
