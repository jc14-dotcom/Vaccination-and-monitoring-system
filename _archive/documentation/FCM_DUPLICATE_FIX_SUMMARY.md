# FCM Duplicate Notification Fix - Comprehensive Analysis

## Problem Analysis

### Root Cause
Duplicate notifications were appearing due to **Firebase's visibility detection bug** where both foreground (`onMessage`) and background (`onBackgroundMessage`) handlers would fire for the same notification, especially when:
- Browser is minimized but still "focused"
- Multiple browser tabs are open
- Browser window is partially visible

### Investigation Findings

1. **fcm.js loaded in 2 files:**
   - `layouts/parent-tailwind.blade.php` (unused layout)
   - `parents/parentdashboard.blade.php` (active page)
   - Although parent-tailwind layout is not extended by any page, having fcm.js in multiple files created confusion

2. **Duplicate Handler Registration:**
   - `onMessage` (foreground) in fcm.js
   - `onBackgroundMessage` (background) in sw.js
   - Both would fire in edge cases due to visibility API bugs

3. **No Effective Deduplication:**
   - Tag-based deduplication didn't work across foreground/background contexts
   - localStorage and Map caches were separate and not shared
   - Timing-based solutions had race conditions

## Solution Implemented

### Strategy: **Service Worker Only Approach**

Instead of trying to coordinate between foreground and background handlers, we:
1. **Disabled notification display in foreground handler** (fcm.js onMessage)
2. **Service worker handles ALL notification display** (sw.js onBackgroundMessage)
3. **Foreground handler only updates bell icon** (no popup)

### Code Changes

#### 1. fcm.js - Foreground Handler (Line ~188)
```javascript
listenForMessages() {
    this.messaging.onMessage((payload) => {
        console.log('[Foreground] FCM message received');
        
        // DO NOT show notification - let service worker handle ALL notifications
        // Only update bell icon
        if (typeof window.notificationHandler !== 'undefined') {
            window.notificationHandler.loadNotifications();
        }
    });
}
```

**Why:** Prevents foreground handler from showing popup, eliminating one source of duplicates.

#### 2. sw.js - Service Worker Handler (Line ~24)
```javascript
let lastNotification = { title: '', body: '', timestamp: 0 };

messaging.onBackgroundMessage((payload) => {
    const title = payload.notification?.title || 'Notification';
    const body = payload.notification?.body || '';
    const now = Date.now();
    
    // Prevent exact duplicate within 3 seconds
    if (lastNotification.title === title && 
        lastNotification.body === body && 
        now - lastNotification.timestamp < 3000) {
        console.log('[ServiceWorker] Skipping duplicate');
        return Promise.resolve();
    }
    
    lastNotification = { title, body, timestamp: now };
    
    return self.registration.showNotification(title, {
        body: body,
        icon: '/images/icon-192x192.png',
        badge: '/images/icon-192x192.png',
        data: payload.data || {},
        tag: 'fcm-notification'
    });
});
```

**Why:** 
- Service worker handles ALL notifications (foreground + background)
- Tracks last notification to prevent rapid duplicates
- Uses fixed tag so only one notification shows at a time

### How It Works

**Tab Open (Visible):**
1. FCM delivers message
2. `onMessage` fires in fcm.js → Updates bell icon only
3. Message ALSO goes to service worker
4. `onBackgroundMessage` fires → Shows notification popup
5. Result: 1 notification popup + bell icon updated ✓

**Tab Minimized (Hidden):**
1. FCM delivers message
2. `onBackgroundMessage` fires in sw.js → Shows notification
3. Result: 1 notification popup ✓

**Duplicate Prevention:**
- Service worker tracks last shown notification
- If same title+body within 3 seconds → Skip
- Fixed tag ensures browser only shows one notification

## Testing Steps

1. **Clear browser cache:** Ctrl+Shift+R
2. **Unregister old service worker:**
   - DevTools → Application → Service Workers → Unregister
3. **Refresh page** to register new service worker
4. **Test scenarios:**
   - Create schedule with tab OPEN → Should see 1 popup
   - Create schedule with tab MINIMIZED → Should see 1 popup
   - Cancel schedule (both states) → Should see 1 popup each time

## Expected Behavior

✅ **CORRECT:**
- 1 notification popup per event
- Bell icon updates immediately
- Works whether tab is visible or hidden
- No duplicates even with multiple tabs open

❌ **INCORRECT (if you see this, report bug):**
- 2 or more popups for same event
- No popup but bell icon updates (service worker not firing)
- Popup shows but bell icon doesn't update (foreground handler broken)

## Architecture Benefits

1. **Simple:** One source of truth for notifications (service worker)
2. **Reliable:** Service workers are designed for background tasks
3. **Consistent:** Same behavior regardless of tab visibility
4. **No race conditions:** No coordination needed between handlers
5. **Mobile-ready:** Works same way on mobile PWA

## Files Modified

1. `public/javascript/fcm.js` - Removed notification display from foreground handler
2. `public/sw.js` - Added duplicate detection to service worker handler

## Backend (No Changes Needed)

- `app/Services/FcmService.php` - Sends notification payload correctly
- `app/Channels/FcmChannel.php` - Works as designed
- All notification classes - No changes required

## Notes

- This is a **client-side only fix**
- Backend continues to send one FCM message per parent
- Frontend ensures only one popup shows
- Service worker is the single source of truth for notification display
