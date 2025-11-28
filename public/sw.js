/**
 * Service Worker for Infant Vaccination System
 * Handles Firebase Cloud Messaging (FCM) push notifications and basic offline caching
 */

// Import Firebase scripts for FCM background messaging
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Initialize Firebase in service worker
firebase.initializeApp({
    apiKey: "AIzaSyDt1exJjVw9x2NdP1cfv32pgy0Ie2ZvJh4",
    authDomain: "infant-vaccination-syste-508e4.firebaseapp.com",
    projectId: "infant-vaccination-syste-508e4",
    storageBucket: "infant-vaccination-syste-508e4.firebasestorage.app",
    messagingSenderId: "182620664136",
    appId: "1:182620664136:web:19df9a9d048b7e1cbc837d"
});

// Get Firebase Messaging instance
const messaging = firebase.messaging();

// Handle background FCM messages (data-only messages)
messaging.onBackgroundMessage(async (payload) => {
    // DEBUG: console.log('[SW] Background message received');
    
    // Check if any window/tab is currently visible
    const clientList = await self.clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    });
    
    const hasVisibleClient = clientList.some(client => client.visibilityState === 'visible');
    
    if (hasVisibleClient) {
        // Page is visible - foreground handler will show notification
        // DEBUG: console.log('[SW] Page visible, skipping (foreground will handle)');
        return;
    }
    
    // Page is NOT visible - show notification from service worker
    // DEBUG: console.log('[SW] Page hidden, showing notification');
    
    const title = payload.data?.title || 'Notification';
    const body = payload.data?.body || '';
    const icon = payload.data?.icon || '/images/icon-192x192.png';
    const clickAction = payload.data?.click_action || '/parents/parentdashboard';
    
    const options = {
        body: body,
        icon: icon,
        badge: '/images/icon-192x192.png',
        data: { click_action: clickAction },
        tag: 'fcm-notification',
        requireInteraction: false
    };
    
    return self.registration.showNotification(title, options);
});

const CACHE_VERSION = 'ivs-v1';
const CACHE_NAME = `ivs-cache-${CACHE_VERSION}`;

// Assets to cache for offline functionality
const ASSETS_TO_CACHE = [
    '/',
    '/css/app.css',
    '/javascript/notifications.js',
    '/images/logo.png',
    '/manifest.json'
];

// Install event - cache essential assets
self.addEventListener('install', (event) => {
    // DEBUG: console.log('[Service Worker] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                // DEBUG: console.log('[Service Worker] Caching essential assets');
                return cache.addAll(ASSETS_TO_CACHE.map(url => new Request(url, {cache: 'reload'})));
            })
            .catch((error) => {
                console.error('[Service Worker] Cache installation failed:', error);
            })
    );
    
    // Force the waiting service worker to become the active service worker
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    // DEBUG: console.log('[Service Worker] Activating...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        // DEBUG: console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    
    // Claim all clients immediately
    return self.clients.claim();
});

// Fetch event - serve from cache when offline, network when online
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }
    
    // Skip chrome extensions and other non-http(s) requests
    if (!event.request.url.startsWith('http')) {
        return;
    }
    
    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                // Return cached response if found
                if (cachedResponse) {
                    return cachedResponse;
                }
                
                // Otherwise fetch from network
                return fetch(event.request)
                    .then((response) => {
                        // Don't cache if not a valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }
                        
                        // Clone the response
                        const responseToCache = response.clone();
                        
                        // Cache static assets
                        if (event.request.url.includes('/css/') || 
                            event.request.url.includes('/javascript/') ||
                            event.request.url.includes('/images/')) {
                            caches.open(CACHE_NAME)
                                .then((cache) => {
                                    cache.put(event.request, responseToCache);
                                });
                        }
                        
                        return response;
                    })
                    .catch((error) => {
                        console.error('[Service Worker] Fetch failed:', error);
                        
                        // Return offline page if available
                        return caches.match('/offline.html');
                    });
            })
    );
});

// Push event - handle incoming push notifications
self.addEventListener('push', (event) => {
    // DEBUG: console.log('[Service Worker] Push notification received');
    
    let notificationData = {
        title: 'Bagong Abiso',
        body: 'May bagong notification para sa iyo',
        icon: '/images/icon-192x192.png',
        badge: '/images/badge-72x72.png',
        vibrate: [200, 100, 200],
        tag: 'notification',
        requireInteraction: false,
        data: {
            url: '/'
        }
    };
    
    // Parse notification data from server
    if (event.data) {
        try {
            const payload = event.data.json();
            
            notificationData = {
                title: payload.title || notificationData.title,
                body: payload.message || payload.body || notificationData.body,
                icon: payload.icon || notificationData.icon,
                badge: payload.badge || notificationData.badge,
                vibrate: payload.vibrate || notificationData.vibrate,
                tag: payload.type || payload.tag || notificationData.tag,
                requireInteraction: payload.requireInteraction || false,
                data: {
                    url: payload.action_url || payload.url || '/',
                    notification_id: payload.notification_id || null,
                    type: payload.type || 'general'
                },
                actions: payload.actions || []
            };
            
            // Add timestamp
            notificationData.timestamp = Date.now();
            
        } catch (error) {
            console.error('[Service Worker] Error parsing push data:', error);
        }
    }
    
    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

// Notification click event - handle user clicking notification
self.addEventListener('notificationclick', (event) => {
    // DEBUG: console.log('[Service Worker] Notification clicked:', event.notification.tag);
    
    event.notification.close();
    
    const urlToOpen = event.notification.data?.url || '/';
    const notificationId = event.notification.data?.notification_id;
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Check if app is already open
                for (let client of clientList) {
                    if (client.url.includes(urlToOpen) && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                // If not open, open new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen).then((client) => {
                        // Mark notification as read via API
                        if (notificationId && client) {
                            client.postMessage({
                                type: 'MARK_NOTIFICATION_READ',
                                notificationId: notificationId
                            });
                        }
                        return client;
                    });
                }
            })
    );
});

// Notification close event - track when user dismisses notification
self.addEventListener('notificationclose', (event) => {
    // DEBUG: console.log('[Service Worker] Notification closed:', event.notification.tag);
    
    // Optional: Track dismissal analytics
    const notificationId = event.notification.data?.notification_id;
    if (notificationId) {
        // Send dismissal event to server if needed
        fetch('/api/notifications/dismissed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId,
                dismissed_at: Date.now()
            })
        }).catch(() => {
            // Silently fail - not critical
        });
    }
});

// Message event - handle messages from the app
self.addEventListener('message', (event) => {
    // DEBUG: console.log('[Service Worker] Message received:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        return caches.delete(cacheName);
                    })
                );
            })
        );
    }
});

// Sync event - handle background sync (for offline actions)
self.addEventListener('sync', (event) => {
    // DEBUG: console.log('[Service Worker] Background sync:', event.tag);
    
    if (event.tag === 'sync-notifications') {
        event.waitUntil(
            // Sync notifications when back online
            fetch('/api/notifications/check')
                .then(response => response.json())
                .then(data => {
                    // DEBUG: console.log('[Service Worker] Notifications synced:', data);
                })
                .catch(error => {
                    console.error('[Service Worker] Sync failed:', error);
                })
        );
    }
});

// DEBUG: console.log('[Service Worker] Loaded successfully');
