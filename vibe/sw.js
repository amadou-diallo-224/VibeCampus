const CACHE_NAME = 'vibe-cache-v2';
const urlsToCache = [
  '/',
  '/style.css',
  '/script.js',
  '/assets/logo.png',
  '/index.php',
  '/connexion.php',
  '/inscription.php',
  '/home.php'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});
