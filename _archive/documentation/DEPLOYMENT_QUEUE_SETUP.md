# 📋 Deployment Queue Setup Guide

## ⚙️ Current Configuration (Development)

### Queue Driver: `sync`
```env
QUEUE_CONNECTION=sync
```
- Notifications process **immediately** during HTTP request
- No background worker needed
- Perfect for local development/testing

---

## 🚀 Production Deployment Setup

### Step 1: Update `.env` File

Change queue connection to database:
```env
# CURRENT (Development):
# QUEUE_CONNECTION=sync

# FOR DEPLOYMENT (Production):
QUEUE_CONNECTION=database
```

### Step 2: Create Jobs Table (Already Done)

The `jobs` table should already exist. If not, run:
```bash
php artisan queue:table
php artisan migrate
```

### Step 3: Start Queue Worker (CRITICAL!)

**The queue worker MUST be running 24/7 in production.**

#### Option A: Using Supervisor (Recommended for VPS/Cloud)

Create `/etc/supervisor/conf.d/infantsystem-worker.conf`:

```ini
[program:infantsystem-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/infantsSystem/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/infantsSystem/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start infantsystem-worker:*
```

#### Option B: Using Systemd (Alternative for Linux)

Create `/etc/systemd/system/infantsystem-worker.service`:

```ini
[Unit]
Description=Infants System Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/infantsSystem
ExecStart=/usr/bin/php /path/to/infantsSystem/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl daemon-reload
sudo systemctl enable infantsystem-worker
sudo systemctl start infantsystem-worker
```

#### Option C: Cron Job (For Shared Hosting)

If you can't run background processes, use Laravel Scheduler:

Add to crontab:
```bash
* * * * * cd /path/to/infantsSystem && php artisan schedule:run >> /dev/null 2>&1
```

Then update `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('queue:work --stop-when-empty')
             ->everyMinute()
             ->withoutOverlapping();
}
```

---

## 📱 PWA Push Notifications (Already Configured!)

### What's Ready:

✅ **VAPID Keys** - Already generated and configured
```env
VAPID_PUBLIC_KEY=BMsZRlLlQC5Pvwpy4IhB6yysFy2oolAVfy-tjxDH48CF-Sw1pMKkMLxbI8_1FrpC22GtbpalZAlINOV2fwteXR0
VAPID_PRIVATE_KEY=405ks326fTqVlwQxFDpeU0tNs_kaH1p81R8smDt8YVU
```

✅ **Service Worker** - `/public/sw.js` configured

✅ **PWA Manifest** - `/public/manifest.json` configured

✅ **Push Subscription Controller** - `PushSubscriptionController.php` ready

✅ **Notification Classes** - All use `WebPushChannel::class`

✅ **Frontend** - `pwa.js` handles subscription and registration

### What to Check Before Deployment:

1. **HTTPS Required** - PWA push only works on HTTPS (not HTTP)
2. **Service Worker Path** - Ensure `/sw.js` is accessible
3. **Manifest Path** - Ensure `/manifest.json` is accessible
4. **VAPID Keys** - Keep the same keys in production (don't regenerate!)

### Testing Push Notifications After Deployment:

1. Install PWA on mobile device
2. Create vaccination schedule
3. Check if push notification appears
4. Check browser console for errors
5. Check worker logs: `tail -f storage/logs/worker.log`

---

## 📨 SMS Notifications (Backend Ready, Disabled)

### Current Status: **DISABLED**

```env
SMS_ENABLED=false
```

### To Enable SMS in Production:

1. Get Semaphore API Key from https://semaphore.co/

2. Update `.env`:
```env
SMS_ENABLED=true
SEMAPHORE_API_KEY=your_actual_api_key_here
SEMAPHORE_SENDER_NAME=YourAppName
```

3. Enable SMS triggers in `config/sms.php`:
```php
'triggers' => [
    'vaccination_schedule_created' => true,  // Send SMS for new schedules
    'vaccination_schedule_cancelled' => true, // Send SMS for cancellations
    'vaccination_reminder' => true,           // Send SMS reminders
    'low_stock_alert' => false,               // Don't send for stock alerts
],
```

4. **Cost**: ~₱0.65 per SMS

### SMS Classes Ready:
- ✅ `SmsService.php` - Semaphore integration
- ✅ `sms_logs` table - Track costs and delivery
- ✅ All notification classes have `toSms()` methods

---

## 🔍 Monitoring Queue in Production

### Check Queue Status:
```bash
# See jobs waiting
php artisan queue:work --once

# See failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear all failed jobs
php artisan queue:flush
```

### Check Worker Status:
```bash
# Supervisor
sudo supervisorctl status infantsystem-worker:*

# Systemd
sudo systemctl status infantsystem-worker

# View logs
tail -f storage/logs/worker.log
tail -f storage/logs/laravel.log
```

---

## 🎯 Deployment Checklist

- [ ] Change `QUEUE_CONNECTION=database` in `.env`
- [ ] Start queue worker (supervisor/systemd/cron)
- [ ] Verify HTTPS is enabled (required for PWA push)
- [ ] Test PWA installation on mobile device
- [ ] Create test vaccination schedule
- [ ] Verify notifications appear (polling + push)
- [ ] Check worker logs for errors
- [ ] Monitor queue with `php artisan queue:work --once`
- [ ] (Optional) Enable SMS if needed
- [ ] Set up monitoring/alerts for failed jobs

---

## ⚠️ Important Notes

1. **Don't change VAPID keys** - Existing subscriptions will break
2. **Queue worker must run 24/7** - Notifications won't send without it
3. **HTTPS is mandatory** - PWA push doesn't work on HTTP
4. **Monitor failed jobs** - Set up alerts for `queue:failed`
5. **SMS costs money** - Only enable if budget allows (~₱0.65/SMS)

---

## 🆘 Troubleshooting

### Notifications not appearing?
1. Check if queue worker is running
2. Check `jobs` table - are jobs stuck?
3. Check `failed_jobs` table - any failures?
4. Check `storage/logs/laravel.log` for errors

### Push notifications not working?
1. Is site on HTTPS?
2. Is service worker registered? (Check browser console)
3. Are VAPID keys correct in `.env`?
4. Is user subscribed? (Check `push_subscriptions` table)

### Queue worker keeps stopping?
1. Use supervisor for auto-restart
2. Check memory limits in `php.ini`
3. Use `--max-time=3600` to restart hourly
4. Check `storage/logs/worker.log` for crashes
