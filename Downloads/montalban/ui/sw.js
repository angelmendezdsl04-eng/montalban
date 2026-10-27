// Service Worker para habilitar la instalación en escritorio (PWA)
// Usa estrategia Network-First para que PHP y MySQL siempre muestren datos en tiempo real
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    event.respondWith(fetch(event.request));
});