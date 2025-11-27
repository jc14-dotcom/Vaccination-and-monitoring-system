<?php
// Read the .env file line by line
$lines = file('.env');
$newLines = [];
$skipNext = false;

foreach ($lines as $i => $line) {
    if ($skipNext) {
        $skipNext = false;
        continue;
    }
    
    // Check if this is the broken VAPID_PUBLIC_KEY line
    if (strpos($line, 'VAPID_PUBLIC_KEY=') !== false && !strpos($line, 'VLeA')) {
        // This is the broken first part - combine with next line
        $nextLine = isset($lines[$i + 1]) ? trim($lines[$i + 1]) : '';
        $fixedLine = rtrim($line) . $nextLine . "\n";
        $newLines[] = $fixedLine;
        $skipNext = true;
    } else {
        $newLines[] = $line;
    }
}

// Write back
file_put_contents('.env', implode('', $newLines));

echo "Fixed VAPID_PUBLIC_KEY line break\n";

// Verify
$content = file_get_contents('.env');
if (preg_match('/VAPID_PUBLIC_KEY=([^\r\n]+)/', $content, $matches)) {
    echo "Current VAPID_PUBLIC_KEY:\n";
    echo $matches[1] . "\n";
    echo "Length: " . strlen($matches[1]) . " characters\n";
}

