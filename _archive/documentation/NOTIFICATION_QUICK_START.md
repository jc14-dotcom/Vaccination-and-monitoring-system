# NOTIFICATION SYSTEM - QUICK START GUIDE

## 🚀 TESTING THE SYSTEM (3 MINUTES)

### Step 1: Login as Health Worker
```
1. Go to http://localhost/infantsSystem
2. Login as health worker
3. Navigate to "Vaccination Schedule"
```

### Step 2: Create Vaccination Schedule
```
1. Click "Create New Schedule"
2. Fill in:
   - Date: [Tomorrow's date]
   - Barangay: Balayhangin (or any barangay with registered patients)
   - Notes: Test notification
3. Click "Create Schedule"
```

### Step 3: Watch Notification Appear
```
1. Logout from health worker
2. Login as Parent (with patient in Balayhangin)
3. Watch the notification bell (top right)
4. Within 15 seconds, badge counter should show "1"
5. Click bell to see notification dropdown
6. Click notification to navigate
```

---

## 📱 SMS TESTING (OPTIONAL - COSTS MONEY!)

### Enable SMS (ONLY IF READY TO SPEND)

1. Sign up: https://semaphore.co
2. Get API key from dashboard
3. Edit `.env` file:
   ```env
   SMS_ENABLED=true
   SEMAPHORE_API_KEY=your_api_key_here
   SMS_TRIGGER_SCHEDULE_CREATED=true
   ```
4. Ensure parent has valid `contact_number` in database
5. Create schedule again
6. Parent should receive SMS within 1 minute

---

## 🔍 VERIFICATION CHECKLIST

### Database Verification
```sql
-- Check notifications created
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5;

-- Check SMS logs (if enabled)
SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 5;
```

### API Verification
```bash
# Check polling endpoint (must be logged in)
curl -X GET http://localhost/api/notifications/check \
  --cookie "session_cookie_here"

# Expected response:
{
  "success": true,
  "has_new": false,
  "notifications": [],
  "unread_count": 0,
  "timestamp": "2025-11-20T22:00:00Z"
}
```

### Browser Console Verification
```javascript
// Open browser console (F12)
// Should see polling requests every 15 seconds:
// GET /api/notifications/check?last_checked=...

// Check notification system initialized:
window.notificationSystem
// Should return NotificationSystem object
```

---

## 🐛 QUICK TROUBLESHOOTING

| Problem | Solution |
|---------|----------|
| Bell not appearing | Clear browser cache, check console for JS errors |
| Badge not updating | Check network tab, verify API endpoint responding |
| No notifications received | Verify patient barangay matches schedule barangay |
| SMS not sending | Check `.env` file, verify `SMS_ENABLED=true` |
| 401 Unauthorized | User not logged in, session expired |

---

## 📊 MONITORING COMMANDS

### Check notification statistics
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as read
FROM notifications
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at);
```

### Check SMS costs (if enabled)
```sql
SELECT 
    COUNT(*) as sent,
    SUM(cost) as total_cost
FROM sms_logs
WHERE status = 'sent'
  AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Check polling activity
```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log | grep notification
```

---

## 🎯 EXPECTED BEHAVIOR

### Normal Flow (Polling - Free)
1. ✅ Health worker creates schedule
2. ✅ Notifications inserted into database (one per parent)
3. ✅ Parent's browser polls every 15 seconds
4. ✅ Badge counter updates
5. ✅ Toast notification appears
6. ✅ Notification in dropdown list
7. ✅ Click marks as read
8. ✅ Navigate to action URL

### With SMS Enabled (Costs Money)
1. ✅ All above steps PLUS
2. ✅ SMS sent to parent's phone
3. ✅ Entry created in `sms_logs` table
4. ✅ Cost tracked in database
5. ✅ Delivery status logged

---

## 💡 KEY URLS

| URL | Purpose |
|-----|---------|
| `/api/notifications` | Get notification list |
| `/api/notifications/check` | Polling endpoint |
| `/api/notifications/{id}/mark-read` | Mark as read |
| `/api/notifications/mark-all-read` | Mark all as read |

---

## 📞 SUPPORT

### Check Logs
```bash
# Application logs
tail -f storage/logs/laravel.log

# PHP errors
tail -f C:\laragon\logs\php_error.log

# Apache errors
tail -f C:\laragon\logs\apache_error.log
```

### Debug Mode
```javascript
// In browser console
window.notificationSystem.stopPolling(); // Stop polling
window.notificationSystem.startPolling(); // Resume polling
window.notificationSystem.loadNotifications(); // Force refresh
```

---

## 🎉 SUCCESS INDICATORS

You'll know it's working when:
- ✅ Notification bell appears in navigation
- ✅ Creating schedule triggers database insert
- ✅ Badge counter updates within 15 seconds
- ✅ Toast notification appears
- ✅ Clicking notification navigates correctly
- ✅ Marking as read updates badge count
- ✅ No console errors in browser

---

**READY TO TEST!** Create a vaccination schedule and watch the magic happen! 🎊

**Cost:** ₱0 (SMS disabled by default)
**Implementation Time:** Complete ✅
**Next Phase:** PWA (after successful test)

---

For detailed documentation, see: `NOTIFICATION_IMPLEMENTATION_SUMMARY.md`
