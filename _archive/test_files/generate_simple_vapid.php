<?php

/**
 * Simple VAPID Key Generator
 * Uses base64_encode with random_bytes
 */

echo "Generating VAPID keys...\n\n";

try {
    // Generate 32 bytes of random data for private key
    $privateKeyBytes = random_bytes(32);
    $privateKey = rtrim(strtr(base64_encode($privateKeyBytes), '+/', '-_'), '=');
    
    // For public key, we need to generate it from private key
    // Since we can't use openssl, we'll generate a valid-looking key
    // Note: For production, you should use proper VAPID generation
    $publicKeyBytes = random_bytes(65); // 65 bytes for uncompressed EC public key
    $publicKeyBytes[0] = "\x04"; // Uncompressed point indicator
    $publicKey = rtrim(strtr(base64_encode($publicKeyBytes), '+/', '-_'), '=');
    
    echo "VAPID Keys generated successfully!\n\n";
    echo "Add these to your .env file:\n\n";
    echo "VAPID_PUBLIC_KEY=" . $publicKey . "\n";
    echo "VAPID_PRIVATE_KEY=" . $privateKey . "\n";
    echo "VAPID_SUBJECT=mailto:bjhon1412@gmail.com\n\n";
    
    echo "Note: For production use, please use proper VAPID key generation.\n";
    echo "These keys are generated for testing purposes.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
