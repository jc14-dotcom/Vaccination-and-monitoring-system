<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Testing SMS with Different Number\n";
echo "========================================\n\n";

$testNumber = '09613233428';

echo "Test Number: {$testNumber}\n";
echo "Network: Smart/TNT (0961 prefix)\n";
echo "Formatted: 63" . substr($testNumber, 1) . "\n\n";

echo "Sending test SMS...\n\n";

$smsService = app(\App\Services\Notification\SmsService::class);

$result = $smsService->send(
    $testNumber,
    'TEST: This is a test SMS from InfantVax vaccination system. If you receive this, SMS is working! -RHU Calauan'
);

echo "========================================\n";
echo "Result:\n";
echo "========================================\n";
echo "Success: " . ($result['success'] ? 'YES ✓' : 'NO ✗') . "\n";
echo "Message: " . $result['message'] . "\n";

if (isset($result['message_id'])) {
    echo "Message ID: " . $result['message_id'] . "\n";
}

if (isset($result['cost'])) {
    echo "Cost: ₱" . number_format($result['cost'], 2) . "\n";
}

echo "\n========================================\n";
echo "Checking SMS Log...\n";
echo "========================================\n";

sleep(2); // Wait a bit for log to save

$latestLog = \App\Models\SmsLog::latest()->first();

if ($latestLog) {
    echo "Status: " . $latestLog->status . "\n";
    echo "Recipient: " . $latestLog->recipient_phone . "\n";
    echo "Gateway Message ID: " . ($latestLog->gateway_message_id ?? 'N/A') . "\n";
    
    if ($latestLog->gateway_response) {
        $response = json_decode($latestLog->gateway_response, true);
        if (is_array($response) && isset($response[0])) {
            echo "\nGateway Response:\n";
            echo "  Network: " . ($response[0]['network'] ?? 'N/A') . "\n";
            echo "  Status: " . ($response[0]['status'] ?? 'N/A') . "\n";
            echo "  Sender: " . ($response[0]['sender_name'] ?? 'N/A') . "\n";
        }
    }
}

echo "\n========================================\n";
echo "Next Steps:\n";
echo "========================================\n";
echo "1. Check if 09613233428 received the SMS\n";
echo "2. Wait 2-5 minutes for delivery\n";
echo "3. If received: SMS system is working! ✓\n";
echo "4. If not received: Number might also be in DND\n";
echo "5. Run: php troubleshoot_sms.php to check delivery status\n";
echo "\n========================================\n";
