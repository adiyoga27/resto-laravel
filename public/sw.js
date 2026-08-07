const CACHE_NAME = 'resto-pos-v1';
const STATIC_ASSETS = [
    '/',
    '/pos',
    '/pos/orders/active',
    '/build/assets/app.css',
    '/build/assets/app.js',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    if (event.request.url.includes('/pos/orders/active')) {
        event.respondWith(
            fetch(event.request).then((response) => {
                const clonedResponse = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, clonedResponse);
                });
                return response;
            }).catch(() => {
                return caches.match(event.request);
            })
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            return cachedResponse || fetch(event.request).then((response) => {
                if (response.ok) {
                    const clonedResponse = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, clonedResponse);
                    });
                }
                return response;
            });
        })
    );
});

self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-orders') {
        event.waitUntil(syncPendingOrders());
    }
});

async function syncPendingOrders() {
    const db = await openDatabase();
    const pendingOrders = await getAllPendingOrders(db);

    for (const order of pendingOrders) {
        try {
            const response = await fetch('/pos/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(order.payload),
            });

            if (response.ok) {
                await deletePendingOrder(db, order.id);
            }
        } catch (error) {
            console.error('Failed to sync order:', order.id, error);
        }
    }
}

function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('RestoPOS', 1);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pendingOrders')) {
                const store = db.createObjectStore('pendingOrders', { keyPath: 'id', autoIncrement: true });
                store.createIndex('idempotency_key', 'idempotency_key', { unique: true });
            }
        };
        request.onsuccess = (event) => resolve(event.target.result);
        request.onerror = (event) => reject(event.target.error);
    });
}

function getAllPendingOrders(db) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('pendingOrders', 'readonly');
        const store = tx.objectStore('pendingOrders');
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function deletePendingOrder(db, id) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('pendingOrders', 'readwrite');
        const store = tx.objectStore('pendingOrders');
        const request = store.delete(id);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}
