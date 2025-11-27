<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test SMS Service
$smsService = app(\App\Services\Notification\SmsService::class);

echo "========================================\n";
echo "Testing SMS Service\n";
echo "========================================\n\n";

echo "Configuration:\n";
echo "- SMS Enabled: " . (config('sms.enabled') ? 'YES' : 'NO') . "\n";
echo "- API Key: " . substr(config('sms.semaphore.api_key'), 0, 15) . "...\n";
echo "- Sender Name: " . config('sms.semaphore.sender_name') . "\n";
echo "- API URL: " . config('sms.semaphore.api_url') . "\n\n";

// Temporarily disable SSL verification for testing (Laragon certificate issue)
config(['app.ssl_verify' => false]);

// Send test SMS
echo "Sending test SMS to 09923093319...\n\n";

$result = $smsService->send(
    '09923093319',
    'TEST: InfantVax SMS is now active! This is a test notification from your vaccination management system. -InfantVax Team'
);

echo "Result:\n";
echo "- Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "- Message: " . $result['message'] . "\n";

if (isset($result['message_id'])) {
    echo "- Message ID: " . $result['message_id'] . "\n";
}

if (isset($result['cost'])) {
    echo "- Cost: ₱" . number_format($result['cost'], 2) . "\n";
}

echo "\n";

// Check SMS logs
echo "Checking SMS logs table...\n";
$latestLog = \App\Models\SmsLog::latest()->first();

if ($latestLog) {
    echo "- Status: " . $latestLog->status . "\n";
    echo "- Recipient: " . $latestLog->recipient_phone . "\n";
    echo "- Cost: ₱" . number_format($latestLog->cost ?? 0, 2) . "\n";
    echo "- Gateway Message ID: " . ($latestLog->gateway_message_id ?? 'N/A') . "\n";
    echo "- Sent At: " . ($latestLog->sent_at ?? 'Not sent') . "\n";
}

echo "\n========================================\n";
echo "Test Complete\n";
echo "========================================\n";
