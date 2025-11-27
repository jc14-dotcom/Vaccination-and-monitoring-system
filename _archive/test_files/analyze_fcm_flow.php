<?php
// Test to analyze FCM notification flow and check for duplicates

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FCM DUPLICATE NOTIFICATION ANALYSIS ===\n\n";

// Check FcmService payload structure
echo "1. BACKEND PAYLOAD STRUCTURE:\n";
echo "   Checking what data is sent to FCM...\n\n";

$fcmService = app(\App\Services\FcmService::class);
$reflection = new ReflectionClass($fcmService);
$method = $reflection->getMethod('send');
echo "   FcmService::send() parameters:\n";
foreach ($method->getParameters() as $param) {
    echo "   - " . $param->getName() . "\n";
}

echo "\n2. CHECKING FCM CONFIGURATION:\n";
$config = config('services.fcm');
echo "   Project ID: " . ($config['project_id'] ?? 'NOT SET') . "\n";
echo "   Credentials: " . ($config['credentials_path'] ?? 'NOT SET') . "\n";

echo "\n3. CHECKING NOTIFICATION CHANNELS:\n";
$notifications = [
    'VaccinationScheduleCreated',
    'VaccinationScheduleCancelled',
    'VaccinationReminder',
    'LowStockAlert',
    'FeedbackRequest'
];

foreach ($notifications as $notif) {
    $class = "App\\Notifications\\{$notif}";
    if (class_exists($class)) {
        $reflection = new ReflectionClass($class);
        $viaMethod = $reflection->getMethod('via');
        echo "   {$notif}:\n";
        echo "     Has toFcm(): " . ($reflection->hasMethod('toFcm') ? 'YES' : 'NO') . "\n";
    }
}

echo "\n4. FRONTEND FILES CHECK:\n";
echo "   fcm.js exists: " . (file_exists('public/javascript/fcm.js') ? 'YES' : 'NO') . "\n";
echo "   sw.js exists: " . (file_exists('public/sw.js') ? 'YES' : 'NO') . "\n";

echo "\n5. CHECKING WHERE FCM.JS IS LOADED:\n";
$bladeFiles = glob('resources/views/**/*.blade.php', GLOB_BRACE);
$filesWithFcm = [];
foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'fcm.js') !== false) {
        $filesWithFcm[] = str_replace('resources/views/', '', $file);
    }
}
echo "   Loaded in " . count($filesWithFcm) . " files:\n";
foreach ($filesWithFcm as $file) {
    echo "   - {$file}\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
