/**
 * PWA Service Worker Registration and Push Notification Subscription
 * This script handles:
 * - Service worker registration
 * - Push notification permission requests
 * - Push subscription management
 */

class PushNotificationManager {
    constructor() {
        this.registration = null;
        this.publicKey = null;
        this.isSubscribed = false;
        this.swRegistrationRetries = 0;
        this.maxRetries = 3;
    }

    /**
     * Initialize the push notification system
     */
    async init() {
        // Check if service workers and push notifications are supported
        if (!('serviceWorker' in navigator)) {
            console.log('Service Workers are not supported in this browser');
            return;
        }

        if (!('PushManager' in window)) {
            console.log('Push notifications are not supported in this browser');
            return;
        }

        // Register service worker
        await this.registerServiceWorker();

        // Fetch VAPID public key
        await this.fetchPublicKey();

        // Check current subscription status
        await this.checkSubscription();
    }

    /**
     * Register the service worker with retry logic
     */
    async registerServiceWorker() {
        try {
            this.registration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/'
            });

            console.log('Service Worker registered successfully:', this.registration.scope);

            // Wait for service worker to be ready
            await navigator.serviceWorker.ready;
            console.log('Service Worker is ready');

            // Listen for service worker updates
            this.registration.addEventListener('updatefound', () => {
                const newWorker = this.registration.installing;
                console.log('New service worker found, installing...');
                
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        console.log('New service worker available! Refresh to update.');
                        this.showUpdateNotification();
                    }
                });
            });

        } catch (error) {
            console.error('Service Worker registration failed:', error);
            
            // Retry registration if it failed
            if (this.swRegistrationRetries < this.maxRetries) {
                this.swRegistrationRetries++;
                console.log(`Retrying service worker registration (${this.swRegistrationRetries}/${this.maxRetries})...`);
                setTimeout(() => this.registerServiceWorker(), 2000);
            }
        }
    }

    /**
     * Fetch VAPID public key from server
     */
    async fetchPublicKey() {
        try {
            const response = await fetch('/api/push/public-key');
            const data = await response.json();
            
            // Clean the VAPID key: remove line breaks, spaces, quotes, and trim
            this.publicKey = data.publicKey
                .toString()
                .replace(/[\r\n\s"']/g, '') // Remove line breaks, spaces, quotes
                .trim();
            
            console.log('VAPID public key fetched:', this.publicKey.substring(0, 20) + '...');
        } catch (error) {
            console.error('Failed to fetch VAPID public key:', error);
        }
    }

    /**
     * Check current push subscription status
     */
    async checkSubscription() {
        if (!this.registration) return;

        try {
            const subscription = await this.registration.pushManager.getSubscription();
            this.isSubscribed = subscription !== null;
            console.log('Current subscription status:', this.isSubscribed);

            if (this.isSubscribed) {
                console.log('Already subscribed to push notifications');
            }
        } catch (error) {
            console.error('Error checking subscription:', error);
        }
    }

    /**
     * Request notification permission and subscribe to push notifications
     */
    async subscribe() {
        if (!this.registration || !this.publicKey) {
            console.error('Service worker not registered or VAPID key not available');
            return false;
        }

        try {
            // Request notification permission
            const permission = await Notification.requestPermission();
            
            if (permission !== 'granted') {
                console.log('Notification permission denied');
                return false;
            }

            console.log('Notification permission granted');

            // Subscribe to push notifications
            const subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
            });

            console.log('Push subscription created:', subscription);

            // Send subscription to server
            const success = await this.sendSubscriptionToServer(subscription);

            if (success) {
                this.isSubscribed = true;
                console.log('Successfully subscribed to push notifications');
                this.showSuccessMessage('Successfully subscribed to push notifications!');
                return true;
            }

        } catch (error) {
            console.error('Failed to subscribe to push notifications:', error);
            this.showErrorMessage('Failed to subscribe to push notifications');
            return false;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        if (!this.registration) return false;

        try {
            const subscription = await this.registration.pushManager.getSubscription();
            
            if (!subscription) {
                console.log('No active subscription found');
                return false;
            }

            // Unsubscribe from push notifications
            await subscription.unsubscribe();

            // Remove subscription from server
            await this.removeSubscriptionFromServer(subscription);

            this.isSubscribed = false;
            console.log('Successfully unsubscribed from push notifications');
            this.showSuccessMessage('Successfully unsubscribed from push notifications');
            return true;

        } catch (error) {
            console.error('Failed to unsubscribe:', error);
            return false;
        }
    }

    /**
     * Send subscription to server
     */
    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin', // Important: Send cookies for authentication
                body: JSON.stringify(subscription.toJSON())
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error('Server error:', errorData);
                throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
            }

            const data = await response.json();
            return data.success;

        } catch (error) {
            console.error('Failed to send subscription to server:', error);
            return false;
        }
    }

    /**
     * Remove subscription from server
     */
    async removeSubscriptionFromServer(subscription) {
        try {
            await fetch('/api/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin', // Send cookies for authentication
                body: JSON.stringify({
                    endpoint: subscription.endpoint
                })
            });

        } catch (error) {
            console.error('Failed to remove subscription from server:', error);
        }
    }

    /**
     * Convert VAPID key from base64 to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Show update notification when new service worker is available
     */
    showUpdateNotification() {
        const notification = document.createElement('div');
        notification.className = 'fixed bottom-4 right-4 bg-primary-600 text-white px-6 py-4 rounded-lg shadow-xl z-50 max-w-sm';
        notification.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas fa-sync-alt"></i>
                <div class="flex-1">
                    <p class="font-semibold">May Bagong Update!</p>
                    <p class="text-sm text-white/90">I-refresh ang page para ma-update ang app.</p>
                </div>
                <button onclick="location.reload()" class="bg-white text-primary-600 px-3 py-1 rounded font-semibold text-sm hover:bg-gray-100">
                    Refresh
                </button>
            </div>
        `;
        document.body.appendChild(notification);

        // Auto-remove after 30 seconds
        setTimeout(() => notification.remove(), 30000);
    }

    /**
     * Show success message
     */
    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    /**
     * Show error message
     */
    showErrorMessage(message) {
        this.showToast(message, 'error');
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const bgColor = type === 'success' ? 'bg-green-600' : 
                       type === 'error' ? 'bg-red-600' : 
                       'bg-primary-600';
        
        const icon = type === 'success' ? 'fa-check-circle' : 
                    type === 'error' ? 'fa-exclamation-circle' : 
                    'fa-info-circle';

        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-xl z-50 max-w-sm animate-slide-in`;
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas ${icon}"></i>
                <p class="flex-1">${message}</p>
            </div>
        `;
        document.body.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('animate-slide-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    /**
     * Show notification permission prompt UI
     */
    showNotificationPrompt() {
        // Check if already subscribed or permission already granted
        if (this.isSubscribed || Notification.permission === 'granted') {
            return;
        }

        // Don't show if permission was denied
        if (Notification.permission === 'denied') {
            console.log('Notification permission was denied by user');
            return;
        }

        // Create permission prompt UI
        const prompt = document.createElement('div');
        prompt.id = 'notification-prompt';
        prompt.className = 'fixed top-4 right-4 bg-white border border-primary-200 rounded-lg shadow-xl z-50 max-w-sm p-4 animate-slide-in';
        prompt.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="bg-primary-100 text-primary-600 rounded-full p-2">
                    <i class="fas fa-bell text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900 mb-1">Enable Push Notifications</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Get instant notifications for vaccination schedules and important updates.
                    </p>
                    <div class="flex gap-2">
                        <button id="enable-notifications-btn" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                            Enable
                        </button>
                        <button id="dismiss-notifications-btn" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-semibold text-sm transition">
                            Later
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(prompt);

        // Handle enable button
        document.getElementById('enable-notifications-btn').addEventListener('click', async () => {
            prompt.remove();
            await this.subscribe();
        });

        // Handle dismiss button
        document.getElementById('dismiss-notifications-btn').addEventListener('click', () => {
            prompt.remove();
            // Show again after 24 hours
            localStorage.setItem('notification-prompt-dismissed', Date.now().toString());
        });
    }

    /**
     * Check if notification prompt should be shown
     */
    shouldShowPrompt() {
        // Don't show if already subscribed
        if (this.isSubscribed) {
            console.log('Notification prompt hidden: Already subscribed');
            return false;
        }

        // Don't show if permission was denied
        if (Notification.permission === 'denied') {
            console.log('Notification prompt hidden: Permission denied');
            return false;
        }

        // Check if dismissed recently (within 24 hours)
        const dismissed = localStorage.getItem('notification-prompt-dismissed');
        if (dismissed) {
            const dismissedTime = parseInt(dismissed);
            const dayInMs = 24 * 60 * 60 * 1000;
            if (Date.now() - dismissedTime < dayInMs) {
                const hoursLeft = Math.ceil((dayInMs - (Date.now() - dismissedTime)) / (60 * 60 * 1000));
                console.log(`Notification prompt hidden: Dismissed ${hoursLeft} hours ago (will show in ${24 - hoursLeft} hours)`);
                return false;
            }
        }

        console.log('Notification prompt will be shown');
        return true;
    }
}

// Initialize push notification manager when DOM is ready
const pushManager = new PushNotificationManager();

document.addEventListener('DOMContentLoaded', async () => {
    await pushManager.init();

    // Show notification prompt after 5 seconds if appropriate
    setTimeout(() => {
        if (pushManager.shouldShowPrompt()) {
            pushManager.showNotificationPrompt();
        }
    }, 5000);
});

// Make pushManager globally available for manual triggering
window.pushManager = pushManager;

// Helper function to manually show notification prompt (for testing/debugging)
window.forceShowNotificationPrompt = function() {
    localStorage.removeItem('notification-prompt-dismissed');
    pushManager.showNotificationPrompt();
    console.log('Notification prompt forced to show');
};

// Helper to check notification status
window.checkNotificationStatus = function() {
    console.log('=== Notification Status ===');
    console.log('Service Worker:', pushManager.registration ? 'Registered' : 'Not registered');
    console.log('VAPID Key:', pushManager.publicKey ? 'Loaded' : 'Not loaded');
    console.log('Subscribed:', pushManager.isSubscribed);
    console.log('Permission:', Notification.permission);
    console.log('Dismissed:', localStorage.getItem('notification-prompt-dismissed') || 'No');
    console.log('Should show prompt:', pushManager.shouldShowPrompt());
};
