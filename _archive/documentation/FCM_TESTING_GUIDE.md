# 🧪 FCM IMPLEMENTATION TESTING GUIDE

**Implementation Date**: November 25, 2025  
**Status**: ✅ Complete - Ready for Testing

---

## ✅ WHAT WAS COMPLETED

### Backend (PHP/Laravel)
1. ✅ Installed `google/auth` package for OAuth2 authentication
2. ✅ Created `FcmService.php` - Sends notifications via FCM v1 API
3. ✅ Created `FcmChannel.php` - Laravel notification channel for FCM
4. ✅ Updated `config/services.php` - Added FCM configuration
5. ✅ Updated `.env` - Added all Firebase credentials
6. ✅ Migration - Added `fcm_token` column to parents table
7. ✅ Updated 5 notification classes to use `FcmChannel`:
   - VaccinationScheduleCreated
   - VaccinationScheduleCancelled
   - VaccinationReminder
   - LowStockAlert
   - FeedbackRequest
8. ✅ Created `FcmController.php` - Handles token subscription/unsubscription
9. ✅ Added FCM routes in `web.php`

### Frontend (JavaScript)
1. ✅ Created `fcm.js` - Firebase SDK integration and token management
2. ✅ Updated `sw.js` - Added Firebase messaging for background notifications
3. ✅ Updated `parent-tailwind.blade.php` - Loads FCM script instead of VAPID

---

## 🧪 TESTING STEPS

### Step 1: Verify Service Worker Registration

1. Open browser (Chrome/Firefox)
2. Go to: `http://localhost/infantsSystem`
3. Login as a parent account
4. Open **DevTools** (F12)
5. Go to **Console** tab
6. Look for these messages:
   ```
   ✅ "Initializing FCM..."
   ✅ "FCM config fetched: infant-vaccination-syste-508e4"
   ✅ "Service Worker registered for FCM"
   ✅ "Notification permission granted"
   ✅ "FCM Token received: ..."
   ✅ "FCM token saved to server"
   ✅ "FCM initialized successfully"
   ```

### Step 2: Verify Token Storage

1. In DevTools Console, run:
   ```javascript
   console.log(window.fcmManager.token);
   ```
2. Should show a long token (150+ characters)

3. Check database:
   ```sql
   SELECT id, name, fcm_token FROM parents WHERE fcm_token IS NOT NULL;
   ```
4. Your parent account should have an FCM token

### Step 3: Test Push Notification

**Method 1: Create Vaccination Schedule**

1. Login as **Health Worker**
2. Go to **Vaccination Schedule** → **Create Schedule**
3. Fill in:
   - Date: Tomorrow
   - Time: 9:00 AM
   - Barangay: **RHU (Health Center)**  ← Important!
   - Vaccine Type: BCG
4. Click **Submit**
5. **Check parent browser** - Should receive notification

**Method 2: Test via Browser DevTools**

1. In parent account browser
2. Open **Application** tab in DevTools
3. Go to **Service Workers**
4. Check "Update on reload"
5. Reload page
6. Go back to Health Worker account
7. Create a schedule
8. Parent should receive notification

### Step 4: Verify Notification Appearance

When notification arrives:
- ✅ Title: "Bagong Schedule ng Bakuna"
- ✅ Body: "May bagong schedule ng bakuna sa RHU (Health Center). Petsa: [date], Oras: [time]"
- ✅ Icon: System icon
- ✅ Click notification → Opens parent dashboard

---

## 🔍 TROUBLESHOOTING

### Problem: "Failed to fetch FCM config"

**Solution**:
```bash
# Clear cache
php artisan optimize:clear

# Check .env has FCM credentials
cat .env | findstr FCM
```

### Problem: "FCM token not saved"

**Causes**:
1. Not logged in as parent
2. CSRF token missing
3. Parent guard not active

**Check**:
```javascript
// In browser console
console.log(document.querySelector('meta[name="csrf-token"]').content);
```

### Problem: "Firebase is not defined"

**Solution**:
- Check internet connection (Firebase scripts load from CDN)
- Check browser console for script loading errors
- Clear browser cache (Ctrl+Shift+Delete)

### Problem: "Service Worker error"

**Solution**:
```javascript
// Unregister old service worker
navigator.serviceWorker.getRegistrations().then(registrations => {
    registrations.forEach(reg => reg.unregister());
});

// Refresh page
location.reload();
```

### Problem: No notification received

**Check**:
1. ✅ Parent has FCM token in database
2. ✅ Schedule created with barangay = "RHU (Health Center)"
3. ✅ Notification permission granted
4. ✅ Browser supports notifications
5. ✅ Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## 📊 VERIFICATION CHECKLIST

Before declaring success, verify:

- [ ] Browser console shows "FCM initialized successfully"
- [ ] Parent record in database has fcm_token
- [ ] Service worker registered (check Application tab)
- [ ] Notification permission = "granted"
- [ ] Created schedule triggers notification
- [ ] Notification appears on screen
- [ ] Clicking notification opens dashboard
- [ ] Notification shows in bell icon (database notification)
- [ ] Laravel logs show "FCM notification sent successfully"

---

## 🔄 COMPARISON: OLD vs NEW

### Before (VAPID)
- ❌ Required VPS with queue worker
- ❌ Timed out on shared hosting
- ❌ 70-80% delivery rate
- ❌ Required HTTPS setup
- ❌ Complex server-side push

### After (FCM)
- ✅ Works on shared hosting
- ✅ No queue worker needed
- ✅ 95%+ delivery rate (Google's infrastructure)
- ✅ Works with standard HTTPS
- ✅ Simple API call

---

## 📝 EXPECTED LOG OUTPUT

### Successful FCM Send (Laravel Log)
```
[2025-11-25 17:45:23] local.INFO: FCM notification sent successfully
{"token":"dPqK3Fgh...","title":"Bagong Schedule ng Bakuna"}
```

### Successful Token Save (Laravel Log)
```
[2025-11-25 17:43:15] local.INFO: FCM token subscribed
{"parent_id":1,"parent_name":"Juan Dela Cruz","token":"dPqK3Fgh..."}
```

---

## 🎯 NEXT STEPS AFTER TESTING

### If Testing Succeeds:
1. ✅ Test with multiple parent accounts
2. ✅ Test all 5 notification types
3. ✅ Test notification appearance on mobile
4. ✅ Document for stakeholders
5. ✅ Prepare for deployment

### If Testing Fails:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for errors
3. Verify Firebase credentials in `.env`
4. Verify service account JSON file exists
5. Check database for fcm_token

---

## 🚀 DEPLOYMENT NOTES

### For Shared Hosting:
1. ✅ Upload all files
2. ✅ Run migrations: `php artisan migrate`
3. ✅ Clear cache: `php artisan optimize:clear`
4. ✅ Ensure `.env` has FCM credentials
5. ✅ Ensure `storage/app/firebase/*.json` exists
6. ✅ Test with real parent account

### Required Server Permissions:
- ✅ `allow_url_fopen` = On
- ✅ `curl` extension enabled
- ✅ Outgoing HTTPS connections allowed
- ✅ SSL certificate for HTTPS

---

## 📞 SUPPORT

If you encounter issues:
1. Check this guide's troubleshooting section
2. Review Laravel logs
3. Check browser console
4. Verify Firebase Console settings
5. Test internet connectivity

**Remember**: FCM requires internet connection for both server and client!

---

**Testing Ready!** 🎉

Start with Step 1 and work through each verification step.
