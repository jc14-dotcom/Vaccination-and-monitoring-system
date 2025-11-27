<?php

/**
 * FCM Test Script
 * Run this to test if FCM notifications are working properly
 * 
 * Usage: php test_fcm_notification.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FcmService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== FCM NOTIFICATION TEST ===" . PHP_EOL;
echo PHP_EOL;

// Get parent with FCM token
$parent = DB::table('parents')
    ->select('id', 'fcm_token')
    ->whereNotNull('fcm_token')
    ->first();

if (!$parent) {
    echo "❌ ERROR: No parent found with FCM token!" . PHP_EOL;
    echo "   Solution: Login as parent and wait for FCM to initialize." . PHP_EOL;
    exit(1);
}

echo "✅ Found parent ID: {$parent->id}" . PHP_EOL;
echo "   Token: " . substr($parent->fcm_token, 0, 30) . "..." . PHP_EOL;
echo PHP_EOL;

// Test FCM Service
echo "📤 Sending test notification via FCM..." . PHP_EOL;

try {
    $fcmService = new FcmService();
    
    $result = $fcmService->send(
        $parent->fcm_token,
        '🧪 FCM Test Notification',
        'This is a test notification from Firebase Cloud Messaging. If you see this as a desktop popup, FCM is working perfectly!',
        [
            'type' => 'test',
            'timestamp' => now()->toIso8601String(),
            'url' => url('/parents/parentdashboard')
        ]
    );
    
    if ($result['success']) {
        echo "✅ SUCCESS! Notification sent via FCM!" . PHP_EOL;
        echo "   Response: " . json_encode($result['response']) . PHP_EOL;
        echo PHP_EOL;
        echo "🔔 CHECK YOUR DESKTOP:" . PHP_EOL;
        echo "   - Notification should appear as a popup" . PHP_EOL;
        echo "   - Even if browser is minimized" . PHP_EOL;
        echo "   - Should also appear in bell icon" . PHP_EOL;
    } else {
        echo "❌ FAILED! Notification not sent!" . PHP_EOL;
        echo "   Error: " . $result['error'] . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . PHP_EOL;
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL;
echo "=== TEST COMPLETE ===" . PHP_EOL;
echo PHP_EOL;
echo "📊 VERIFICATION CHECKLIST:" . PHP_EOL;
echo "   [ ] Desktop notification appeared (outside browser)" . PHP_EOL;
echo "   [ ] Notification appeared in bell icon" . PHP_EOL;
echo "   [ ] Clicking notification opens parent dashboard" . PHP_EOL;
echo "   [ ] Check storage/logs/laravel.log for 'FCM notification sent'" . PHP_EOL;
echo PHP_EOL;
echo "🎯 EXPECTED BEHAVIOR:" . PHP_EOL;
echo "   - If browser tab is ACTIVE: Notification in bell icon only" . PHP_EOL;
echo "   - If browser tab is MINIMIZED/BACKGROUND: Desktop popup + bell icon" . PHP_EOL;
echo "   - If browser is CLOSED: Desktop popup (service worker)" . PHP_EOL;
echo PHP_EOL;
echo "📱 MOBILE APP BEHAVIOR:" . PHP_EOL;
echo "   - Yes! When installed as PWA on phone:" . PHP_EOL;
echo "   - ✅ Appears in notification bar" . PHP_EOL;
echo "   - ✅ Works even when app is closed" . PHP_EOL;
echo "   - ✅ Makes notification sound" . PHP_EOL;
echo "   - ✅ Shows on lock screen" . PHP_EOL;
echo PHP_EOL;
