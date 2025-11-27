# NOTIFICATION TESTING GUIDE

## 🔧 Fixes Applied

1. ✅ Updated meta tag to `mobile-web-app-capable` (no more deprecation warning)
2. ✅ Created placeholder icons (no more 404 errors)
3. ✅ Using existing `todoligtass.png` as temporary icons

---

## 🧪 TESTING STEPS

### Step 1: Reset Browser Permissions (IMPORTANT!)

**In Chrome:**
1. Click the 🔒 lock icon in the address bar
2. Click "Site settings"
3. Find "Notifications" 
4. Set to "Ask (default)" or "Allow"
5. Refresh the page (F5)

**In Brave:**
1. Click the 🦁 Brave icon in address bar
2. Click "Site settings"
3. Find "Notifications"
4. Set to "Allow" (Brave blocks by default)
5. Refresh the page

### Step 2: Test Automatic Prompt

1. **Clear cache**: Ctrl+Shift+Delete → Clear cache
2. **Refresh**: F5
3. **Wait 5 seconds** - Permission prompt should appear
4. **Click "I-enable"** button
5. **Click "Allow"** when browser asks

**Expected Result:**
- ✅ Toast message: "Nag-subscribe ka na sa push notifications!"
- ✅ Console shows: "Successfully subscribed to push notifications"

### Step 3: Test Manual Subscription (If Prompt Doesn't Show)

Open browser console (F12) and run:

```javascript
// Clear localStorage flag
localStorage.removeItem('notification-prompt-dismissed');

// Manually trigger subscription
await pushManager.subscribe();
```

**Expected Result:**
- ✅ Browser asks for permission
- ✅ Returns `true` on success

### Step 4: Verify Subscription in Database

**Option A: Check via console:**
```javascript
fetch('/api/notifications/check')
    .then(r => r.json())
    .then(console.log);
```

**Option B: Check database directly:**
```sql
SELECT * FROM push_subscriptions;
```

You should see 1 row with your endpoint and keys.

### Step 5: Send Test Notification

**Make sure you're logged in first!**

```javascript
fetch('/api/push/test', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
}).then(r => r.json()).then(console.log);
```

**Expected Result:**
- ✅ Push notification appears (even with browser minimized)
- ✅ Console shows: `{success: true, message: "Test notification sent."}`

### Step 6: Test Real Notification

1. Go to vaccination schedule page
2. Create a new vaccination schedule
3. **Expected Result:**
   - ✅ Push notification appears instantly
   - ✅ Notification shows Filipino text
   - ✅ Click notification → opens vaccination schedule page

---

## 🐛 TROUBLESHOOTING

### Issue: "Notification permission denied"
**Cause:** You clicked "Block" when browser asked for permission

**Solution:**
1. Go to browser settings
2. Search for "Site settings" or "Permissions"
3. Find "Notifications"
4. Find `127.0.0.1` or `localhost`
5. Remove or change to "Allow"
6. Refresh page and try again

### Issue: "401 Unauthorized" when testing
**Cause:** Not logged in

**Solution:**
1. Login as Parent or Health Worker
2. Then run test commands

### Issue: Permission prompt doesn't appear
**Cause:** Permission already granted/denied, or localStorage flag set

**Solution:**
```javascript
// Check current permission
console.log(Notification.permission); // Should be 'default'

// Clear localStorage
localStorage.removeItem('notification-prompt-dismissed');

// Reload page
location.reload();
```

### Issue: Icons still showing 404
**Cause:** Cache not cleared

**Solution:**
1. Hard refresh: Ctrl+Shift+R
2. Or clear cache completely
3. Check files exist: Visit `http://127.0.0.1:8000/images/icon-144x144.png`

### Issue: Service worker not updating
**Solution:**
1. Open DevTools (F12)
2. Application tab → Service Workers
3. Check "Update on reload"
4. Click "Unregister" button
5. Refresh page (F5)

---

## ✅ SUCCESS CHECKLIST

- [ ] No deprecation warnings in console
- [ ] No 404 errors for icons
- [ ] Service worker registered successfully
- [ ] VAPID public key fetched
- [ ] Permission prompt appeared
- [ ] Notification permission granted
- [ ] Subscription created in database
- [ ] Test notification received
- [ ] Real notification received when creating schedule
- [ ] Clicking notification opens correct page

---

## 📱 MOBILE TESTING

### Android Chrome
1. Open site on mobile
2. Tap menu → "Install app"
3. Open installed app
4. Grant notification permission
5. Lock screen
6. Create schedule from desktop
7. Notification should appear on lock screen!

### iOS Safari (16.4+)
1. Open site in Safari
2. Tap Share → "Add to Home Screen"
3. Open app from home screen
4. Grant notification permission when asked
5. Lock screen
6. Create schedule from desktop
7. Notification should appear!

---

## 🎯 EXPECTED BEHAVIOR

### Desktop (Browser Open)
- Notification appears in browser notification area
- Toast notification also appears in bottom-right
- Both show same message

### Desktop (Browser Closed)
- Notification appears in OS notification center
- Clicking opens browser and navigates to page

### Mobile (App Closed)
- Notification appears on lock screen
- Notification appears in notification tray
- Clicking opens PWA app

---

## 🔍 DEBUG COMMANDS

```javascript
// Check service worker status
navigator.serviceWorker.controller
navigator.serviceWorker.ready.then(reg => console.log(reg))

// Check push subscription
pushManager.isSubscribed

// Get current subscription
navigator.serviceWorker.ready.then(reg => 
    reg.pushManager.getSubscription().then(console.log)
)

// Check notification permission
console.log(Notification.permission)

// Check VAPID key
fetch('/api/push/public-key').then(r => r.json()).then(console.log)

// Check authentication
fetch('/api/notifications/check').then(r => console.log(r.status))
```

---

## 🎉 ALL TESTS PASSED?

If all tests pass, your notification system is **100% functional!**

**You now have:**
- ✅ Real-time push notifications (FREE)
- ✅ Database polling (FREE)
- ✅ SMS backend ready (disabled)
- ✅ PWA installable
- ✅ Filipino language support
- ✅ Works on desktop and mobile
- ✅ Works when app is closed

**Next:** Deploy to production and test on real devices!
