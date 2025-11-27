<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Semaphore Sender Names Management\n";
echo "========================================\n\n";

// Get list of registered sender names
echo "Checking your registered sender names...\n\n";

try {
    $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get('https://api.semaphore.co/api/v4/account/sendernames', [
        'apikey' => config('sms.semaphore.api_key')
    ]);
    
    if ($response->successful()) {
        $senderNames = $response->json();
        
        if (empty($senderNames)) {
            echo "❌ No sender names registered yet.\n\n";
            echo "IMPORTANT: You need to register a sender name first!\n\n";
        } else {
            echo "✓ Registered Sender Names:\n\n";
            foreach ($senderNames as $sender) {
                echo "  • Name: " . $sender['name'] . "\n";
                echo "    Status: " . $sender['status'] . "\n";
                echo "    Created: " . $sender['created_at'] . "\n";
                echo "\n";
            }
        }
    } else {
        echo "Failed to retrieve sender names.\n";
        echo "Response: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

echo "HOW TO REGISTER A SENDER NAME:\n";
echo str_repeat("-", 50) . "\n\n";

echo "Semaphore Sender Name Requirements:\n";
echo "• Maximum 11 characters\n";
echo "• Letters and numbers only (NO SPACES)\n";
echo "• Cannot start with a number\n";
echo "• Must be unique\n";
echo "• Takes 1-2 business days for approval\n\n";

echo "Suggested Sender Names for 'RHU Calauan':\n";
echo "  1. RHUCalauan (11 chars) ✓\n";
echo "  2. RHU-Calauan (special chars not allowed) ✗\n";
echo "  3. RHU Calauan (spaces not allowed) ✗\n";
echo "  4. RHUCalauanLGU (too long - 13 chars) ✗\n";
echo "  5. Calauan (7 chars) ✓\n";
echo "  6. RHUCal (6 chars) ✓\n";
echo "  7. CalauanRHU (10 chars) ✓\n";
echo "  8. InfantVax (9 chars) ✓\n\n";

echo "RECOMMENDED: RHUCalauan (11 characters, no spaces)\n\n";

echo str_repeat("=", 50) . "\n\n";

echo "TO REGISTER A SENDER NAME:\n";
echo "1. Go to: https://semaphore.co/sender-names\n";
echo "2. Click 'Add Sender Name'\n";
echo "3. Enter: RHUCalauan\n";
echo "4. Submit for approval\n";
echo "5. Wait 1-2 business days\n";
echo "6. Once approved, update your .env file:\n";
echo "   SEMAPHORE_SENDER_NAME=RHUCalauan\n\n";

echo "TEMPORARY SOLUTION (While waiting for approval):\n";
echo "• Use blank sender name (default 'Semaphore')\n";
echo "• Messages will show from 'Semaphore'\n";
echo "• Still functional, just less branded\n\n";

echo "========================================\n";
