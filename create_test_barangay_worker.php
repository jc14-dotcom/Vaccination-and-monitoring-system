<?php
/**
 * Create Test Barangay Worker Account
 * Run: php create_test_barangay_worker.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HealthWorker;
use App\Models\Barangay;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Test Barangay Worker Account ===\n\n";

// Get Balayhangin barangay
$barangay = Barangay::where('name', 'Balayhangin')->first();

if (!$barangay) {
    echo "ERROR: Balayhangin barangay not found!\n";
    exit(1);
}

echo "Found Barangay: {$barangay->name} (ID: {$barangay->id})\n\n";

// Check if worker already exists
$existing = HealthWorker::where('username', 'balayhangin_worker')->first();
if ($existing) {
    echo "Worker already exists:\n";
    echo "  Username: {$existing->username}\n";
    echo "  Email: {$existing->email}\n";
    echo "  Barangay ID: {$existing->barangay_id}\n";
    echo "  Is RHU: " . ($existing->isRHU() ? 'Yes' : 'No') . "\n";
    echo "\nUse password: password123\n";
    exit(0);
}

// Create test barangay worker
$worker = HealthWorker::create([
    'username' => 'balayhangin_worker',
    'email' => 'balayhangin@test.com',
    'password' => Hash::make('password123'),
    'barangay_id' => $barangay->id
]);

echo "Created Health Worker Account:\n";
echo "================================\n";
echo "  Username: {$worker->username}\n";
echo "  Password: password123\n";
echo "  Email: {$worker->email}\n";
echo "  Barangay: {$barangay->name}\n";
echo "  Barangay ID: {$worker->barangay_id}\n";
echo "  Is RHU Admin: " . ($worker->isRHU() ? 'Yes' : 'No') . "\n";
echo "  Is Barangay Worker: " . ($worker->isBarangayWorker() ? 'Yes' : 'No') . "\n";
echo "================================\n\n";

echo "Testing access methods:\n";
echo "  Can access Balayhangin: " . ($worker->canAccessBarangay('Balayhangin') ? 'Yes' : 'No') . "\n";
echo "  Can access Bangyas: " . ($worker->canAccessBarangay('Bangyas') ? 'Yes' : 'No') . "\n";
echo "  Accessible barangays: " . implode(', ', $worker->getAccessibleBarangays()) . "\n";

echo "\n=== Done! ===\n";
echo "\nLogin at: /health_worker/login\n";
echo "Username: balayhangin_worker\n";
echo "Password: password123\n";
