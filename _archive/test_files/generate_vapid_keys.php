<?php

require __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\VAPID;

try {
    echo "Generating VAPID keys...\n\n";
    
    $keys = VAPID::createVapidKeys();
    
    echo "===========================================\n";
    echo "VAPID KEYS GENERATED SUCCESSFULLY\n";
    echo "===========================================\n\n";
    
    echo "Add these to your .env file:\n\n";
    echo "VAPID_PUBLIC_KEY=" . $keys['publicKey'] . "\n";
    echo "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "\n";
    echo "VAPID_SUBJECT=mailto:healthworker@balayhangin.local\n\n";
    
    echo "===========================================\n";
    echo "Public Key (for JavaScript):\n";
    echo $keys['publicKey'] . "\n\n";
    
} catch (Exception $e) {
    echo "Error generating VAPID keys: " . $e->getMessage() . "\n";
    exit(1);
}
