<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "SMS Delivery Status Check\n";
echo "========================================\n\n";

$logs = \App\Models\SmsLog::latest()->take(10)->get();

if ($logs->isEmpty()) {
    echo "No SMS logs found.\n";
} else {
    echo "Recent SMS messages:\n\n";
    
    foreach ($logs as $log) {
        echo "─────────────────────────────────────\n";
        echo "ID: {$log->id}\n";
        echo "Recipient: {$log->recipient_phone}\n";
        echo "Status: {$log->status}\n";
        echo "Gateway Message ID: " . ($log->gateway_message_id ?? 'N/A') . "\n";
        echo "Cost: ₱" . number_format($log->cost ?? 0, 2) . "\n";
        echo "Sent At: " . ($log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : 'Not sent') . "\n";
        echo "Created At: {$log->created_at->format('Y-m-d H:i:s')}\n";
        
        if ($log->gateway_response) {
            $response = json_decode($log->gateway_response, true);
            if (is_array($response) && isset($response[0])) {
                echo "\nGateway Details:\n";
                echo "  Network: " . ($response[0]['network'] ?? 'N/A') . "\n";
                echo "  Status: " . ($response[0]['status'] ?? 'N/A') . "\n";
                echo "  Sender: " . ($response[0]['sender_name'] ?? 'N/A') . "\n";
                echo "  Account: " . ($response[0]['account'] ?? 'N/A') . "\n";
            }
        }
        
        echo "\nMessage Preview:\n";
        echo "  " . substr($log->message, 0, 100) . (strlen($log->message) > 100 ? '...' : '') . "\n";
    }
    
    echo "\n─────────────────────────────────────\n";
}

echo "\n";
echo "Total SMS sent: " . \App\Models\SmsLog::where('status', 'sent')->count() . "\n";
echo "Total SMS failed: " . \App\Models\SmsLog::where('status', 'failed')->count() . "\n";
echo "Total cost: ₱" . number_format(\App\Models\SmsLog::where('status', 'sent')->sum('cost'), 2) . "\n";

echo "\n========================================\n";
