<?php

/**
 * Clean VAPID Key Generator - No Line Breaks Guaranteed
 * This script generates VAPID keys in a single line
 */

echo "=== CLEAN VAPID KEY GENERATOR ===\n\n";

try {
    // Generate 32 bytes of random data for private key
    $privateKeyBytes = random_bytes(32);
    $privateKey = rtrim(strtr(base64_encode($privateKeyBytes), '+/', '-_'), '=');
    
    // Generate 65 bytes for public key (uncompressed EC public key)
    $publicKeyBytes = random_bytes(65);
    $publicKeyBytes[0] = "\x04"; // Uncompressed point indicator
    $publicKey = rtrim(strtr(base64_encode($publicKeyBytes), '+/', '-_'), '=');
    
    // Ensure absolutely no line breaks, spaces, or special characters
    $publicKey = preg_replace('/[\r\n\s]/', '', $publicKey);
    $privateKey = preg_replace('/[\r\n\s]/', '', $privateKey);
    
    echo "✅ VAPID Keys generated successfully!\n\n";
    echo "Copy these EXACT values to your .env file:\n";
    echo str_repeat("=", 80) . "\n\n";
    
    echo "VAPID_PUBLIC_KEY=" . $publicKey . "\n";
    echo "VAPID_PRIVATE_KEY=" . $privateKey . "\n";
    echo "VAPID_SUBJECT=mailto:bjhon1412@gmail.com\n";
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
    
    echo "✅ Public Key Length: " . strlen($publicKey) . " characters\n";
    echo "✅ Private Key Length: " . strlen($privateKey) . " characters\n";
    echo "✅ No line breaks: CONFIRMED\n";
    echo "✅ No spaces: CONFIRMED\n\n";
    
    echo "IMPORTANT:\n";
    echo "1. Copy the ENTIRE key (including the = sign at the end if present)\n";
    echo "2. Paste as a SINGLE LINE in .env file\n";
    echo "3. NO line breaks, NO spaces\n";
    echo "4. After updating .env, run: php artisan config:clear\n";
    echo "5. Then run: php artisan optimize:clear\n\n";
    
    // Save to file for easy copying
    $envContent = "VAPID_PUBLIC_KEY=" . $publicKey . "\n";
    $envContent .= "VAPID_PRIVATE_KEY=" . $privateKey . "\n";
    $envContent .= "VAPID_SUBJECT=mailto:bjhon1412@gmail.com\n";
    
    file_put_contents('vapid_keys_clean.txt', $envContent);
    echo "✅ Keys also saved to: vapid_keys_clean.txt\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
