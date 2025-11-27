<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "SMS Troubleshooting & Testing\n";
echo "========================================\n\n";

// Test 1: Check Semaphore Account Info
echo "Test 1: Checking Semaphore Account Status\n";
echo "-------------------------------------------\n";

try {
    $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get('https://api.semaphore.co/api/v4/account', [
        'apikey' => config('sms.semaphore.api_key')
    ]);
    
    if ($response->successful()) {
        $account = $response->json();
        echo "✓ Account Status: ACTIVE\n";
        echo "✓ Account Name: " . ($account['account_name'] ?? 'N/A') . "\n";
        echo "✓ Credits: " . ($account['credit_balance'] ?? 'N/A') . "\n";
        echo "✓ Status: " . ($account['status'] ?? 'N/A') . "\n";
    } else {
        echo "✗ Failed to retrieve account info\n";
        echo "Response: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Check message status
echo "Test 2: Checking Recent Message Delivery Status\n";
echo "-------------------------------------------\n";

$recentLog = \App\Models\SmsLog::where('status', 'sent')->latest()->first();

if ($recentLog && $recentLog->gateway_message_id) {
    try {
        $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get('https://api.semaphore.co/api/v4/messages', [
            'apikey' => config('sms.semaphore.api_key'),
            'limit' => 5
        ]);
        
        if ($response->successful()) {
            $messages = $response->json();
            
            echo "Recent messages from Semaphore:\n\n";
            
            foreach ($messages as $msg) {
                if ($msg['message_id'] == $recentLog->gateway_message_id) {
                    echo "FOUND YOUR MESSAGE:\n";
                    echo "  Message ID: " . $msg['message_id'] . "\n";
                    echo "  Recipient: " . $msg['recipient'] . "\n";
                    echo "  Status: " . $msg['status'] . "\n";
                    echo "  Network: " . $msg['network'] . "\n";
                    echo "  Created: " . $msg['created_at'] . "\n";
                    echo "  Updated: " . $msg['updated_at'] . "\n";
                    
                    if ($msg['status'] == 'Pending') {
                        echo "\n⚠️  Message is still PENDING with the carrier.\n";
                        echo "   This could mean:\n";
                        echo "   - Network delay (can take 5-30 minutes)\n";
                        echo "   - Number is in DND (Do Not Disturb) list\n";
                        echo "   - Number is inactive or unreachable\n";
                        echo "   - Smart network congestion\n";
                    } elseif ($msg['status'] == 'Failed') {
                        echo "\n✗ Message delivery FAILED\n";
                    } elseif ($msg['status'] == 'Sent' || $msg['status'] == 'Success') {
                        echo "\n✓ Message delivered successfully!\n";
                    }
                    
                    break;
                }
            }
        }
    } catch (\Exception $e) {
        echo "✗ Error checking message status: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Test 3: Phone number validation
echo "Test 3: Phone Number Analysis\n";
echo "-------------------------------------------\n";
echo "Original: 09923093319\n";
echo "Formatted: 639923093319\n";
echo "Network: Smart (prefix 0992 = Smart/TNT)\n";
echo "\n";

// Test 4: Recommendations
echo "Recommendations:\n";
echo "-------------------------------------------\n";
echo "1. Wait 5-30 minutes for delivery\n";
echo "2. Check if 09923093319 is:\n";
echo "   - Active and can receive SMS\n";
echo "   - Not in DND (Do Not Disturb) list\n";
echo "   - Has signal/network coverage\n";
echo "3. Try sending to a different number (Globe/TM)\n";
echo "4. Check Semaphore dashboard: https://semaphore.co/dashboard\n";
echo "5. Contact Semaphore support if issue persists\n";

echo "\n========================================\n";
