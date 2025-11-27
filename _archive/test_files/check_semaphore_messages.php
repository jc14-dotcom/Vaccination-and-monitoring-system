<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Detailed Semaphore Message Report\n";
echo "========================================\n\n";

try {
    // Get recent messages from Semaphore
    $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get('https://api.semaphore.co/api/v4/messages', [
        'apikey' => config('sms.semaphore.api_key'),
        'limit' => 10,
        'page' => 1
    ]);
    
    if ($response->successful()) {
        $messages = $response->json();
        
        if (empty($messages)) {
            echo "No messages found.\n";
        } else {
            foreach ($messages as $index => $msg) {
                echo "Message #" . ($index + 1) . "\n";
                echo str_repeat("-", 50) . "\n";
                echo "Message ID: " . $msg['message_id'] . "\n";
                echo "Recipient: " . $msg['recipient'] . "\n";
                echo "Network: " . $msg['network'] . "\n";
                echo "Status: " . $msg['status'] . "\n";
                echo "Sender: " . $msg['sender_name'] . "\n";
                echo "Created: " . $msg['created_at'] . "\n";
                echo "Updated: " . $msg['updated_at'] . "\n";
                echo "Message: " . substr($msg['message'], 0, 80) . "...\n";
                
                // Status explanation
                if ($msg['status'] == 'Failed') {
                    echo "\n❌ DELIVERY FAILED\n";
                    echo "Possible reasons:\n";
                    echo "  • Number is in DND (Do Not Disturb) list\n";
                    echo "  • Number is invalid or inactive\n";
                    echo "  • Network rejected the message\n";
                    echo "  • Number blocked promotional messages\n";
                } elseif ($msg['status'] == 'Pending') {
                    echo "\n⏳ PENDING - Waiting for carrier delivery\n";
                } elseif ($msg['status'] == 'Success' || $msg['status'] == 'Sent') {
                    echo "\n✅ DELIVERED SUCCESSFULLY\n";
                }
                
                echo "\n";
            }
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Account Credits: " . $messages[0]['user'] ?? 'N/A' . "\n";
        
    } else {
        echo "Failed to retrieve messages.\n";
        echo "Response: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";
echo "NEXT STEPS:\n";
echo "1. Check https://semaphore.co/dashboard for detailed error\n";
echo "2. Verify 09923093319 can receive SMS from other services\n";
echo "3. Check if number is in NTSP DND registry\n";
echo "4. Try with a different number (Globe/TM preferred)\n";
echo "5. Contact Semaphore support with Message ID: 259433848\n";

echo "\n========================================\n";
