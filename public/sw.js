const CACHE_NAME = 'what-to-eat-v1';
const STATIC_CACHE_NAME = 'what-to-eat-static-v1';

// Read-only routes that should be cached
const READ_ONLY_ROUTES = [
    '/',
    '/meals',
    '/meals/list',
    '/food-items',
    '/tags',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME).then((cache) => {
            // Cache the homepage
            return cache.add('/');
        })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((cacheName) => {
                        return cacheName !== CACHE_NAME && cacheName !== STATIC_CACHE_NAME;
                    })
                    .map((cacheName) => {
                        return caches.delete(cacheName);
                    })
            );
        })
    );
    return self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only cache GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Don't cache API endpoints that modify data (POST, PUT, DELETE)
    if (url.pathname.includes('/meals/accept-suggestion') ||
        url.pathname.includes('/meals/store') ||
        url.pathname.includes('/meals/update') ||
        url.pathname.includes('/meals/destroy') ||
        url.pathname.includes('/tags/store') ||
        url.pathname.includes('/tags/update') ||
        url.pathname.includes('/tags/destroy') ||
        url.pathname.includes('/food-items/update') ||
        url.pathname.includes('/food-items/destroy')) {
        return;
    }

    // Check if it's a read-only route (query parameters are handled automatically by URL matching)
    const isReadOnlyRoute = READ_ONLY_ROUTES.some(route => {
        return url.pathname === route || url.pathname.startsWith(route + '/');
    });

    // Cache read-only routes and static assets
    if (isReadOnlyRoute || url.pathname.startsWith('/build/') || url.pathname.startsWith('/css/') || url.pathname.startsWith('/js/') || url.pathname === '/favicon.ico' || url.pathname === '/manifest.json') {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cached version, but also update in background
                    fetch(request).then((response) => {
                        if (response && response.status === 200 && response.type === 'basic') {
                            const responseToCache = response.clone();
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, responseToCache);
                            });
                        }
                    }).catch(() => {
                        // Network failed, but we have cache, so that's fine
                    });
                    return cachedResponse;
                }

                // Fetch from network
                return fetch(request).then((response) => {
                    // Don't cache if not a valid response
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }

                    // Clone the response
                    const responseToCache = response.clone();

                    // Cache the response
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseToCache);
                    });

                    return response;
                }).catch(() => {
                    // If network fails and we have a cached version, return it
                    // Otherwise return a basic offline page
                    if (url.pathname === '/') {
                        return caches.match('/');
                    }
                    // For other routes, try to return cached version
                    return caches.match(request);
                });
            })
        );
    }
});

