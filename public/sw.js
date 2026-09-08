const CACHE_NAME = 'arafah-pwa-v1';

// Saat aplikasi di-install
self.addEventListener('install', (event) => {
    self.skipWaiting();
    console.log('[ServiceWorker] Install Berhasil');
});

// Membersihkan cache lama
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(keyList.map((key) => {
                if (key !== CACHE_NAME) {
                    return caches.delete(key);
                }
            }));
        })
    );
    self.clients.claim();
});

// Strategi: Network First (Agar data sidak dan poin asrama selalu real-time)
self.addEventListener('fetch', (event) => {
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});