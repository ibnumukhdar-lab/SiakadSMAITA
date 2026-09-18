// SIAKAD SMA IT Arafah — Service Worker (PWA)
//
// PERBAIKAN 18 Sep 2026 — penyebab "419 Page Expired" & "setelah login balik ke halaman login":
// Versi sebelumnya memakai strategi "network first, fallback ke cache" (caches.match).
// Halaman HTML yang tersimpan di cache (dari versi SW yang lebih lama) bisa MUNCUL KEMBALI
// saat jaringan tidak stabil, membawa token CSRF lama => permintaan berikutnya ditolak
// (419 Page Expired) dan pengguna seolah dilempar balik ke halaman login.
// Mulai sekarang SW TIDAK pernah menyimpan/menyajikan halaman dari cache:
// semua permintaan selalu diambil dari jaringan, dan SEMUA cache lama dibuang.
const CACHE_NAME = 'arafah-pwa-v2';

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            // Buang seluruh cache lama (termasuk 'arafah-pwa-v1' yang mungkin
            // masih memuat salinan halaman /login atau /dashboard).
            const keys = await caches.keys();
            await Promise.all(keys.map((key) => caches.delete(key)));
            await self.clients.claim();
        })()
    );
});

// Sengaja TIDAK memakai respondWith(): permintaan diteruskan apa adanya ke jaringan
// (tanpa campur tangan cache), tetapi handler tetap terdaftar agar aplikasi tetap
// bisa dipasang sebagai PWA.
self.addEventListener('fetch', () => {
    // tidak menangani apa pun — selalu ke jaringan
});
