<?php
/**
 * Verify Barangay System Setup
 * Run: php verify_barangay_setup.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HealthWorker;
use App\Models\Patient;
use App\Models\Barangay;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║       BARANGAY SYSTEM VERIFICATION                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// 1. Check Barangays
echo "1. BARANGAYS TABLE:\n";
echo "   Total: " . Barangay::count() . " barangays\n";
echo "   Active: " . Barangay::where('is_active', true)->count() . "\n";
echo "   Schedulable: " . Barangay::where('has_scheduled_vaccination', true)->count() . "\n";
echo "   Non-schedulable: " . Barangay::where('has_scheduled_vaccination', false)->pluck('name')->implode(', ') . "\n\n";

// 2. Check Health Workers
echo "2. HEALTH WORKERS:\n";
$rhuWorkers = HealthWorker::whereNull('barangay_id')->get();
$barangayWorkers = HealthWorker::whereNotNull('barangay_id')->get();
echo "   RHU Admins: " . $rhuWorkers->count() . " (" . $rhuWorkers->pluck('username')->implode(', ') . ")\n";
echo "   Barangay Workers: " . $barangayWorkers->count() . "\n";
foreach ($barangayWorkers as $w) {
    echo "     - {$w->username} → {$w->barangay->name}\n";
}
echo "\n";

// 3. Check Patient Distribution
echo "3. PATIENT DISTRIBUTION:\n";
$distribution = Patient::selectRaw('barangay, count(*) as cnt')
    ->groupBy('barangay')
    ->orderBy('cnt', 'desc')
    ->get();
echo "   Total Patients: " . Patient::count() . "\n";
foreach ($distribution as $d) {
    echo "     - {$d->barangay}: {$d->cnt}\n";
}
echo "\n";

// 4. Test Barangay Worker Access
echo "4. TEST ACCOUNT ACCESS:\n";
$testWorker = HealthWorker::where('username', 'balayhangin_worker')->first();
if ($testWorker) {
    echo "   Account: {$testWorker->username}\n";
    echo "   Email: {$testWorker->email}\n";
    echo "   Barangay: " . ($testWorker->barangay->name ?? 'N/A') . "\n";
    echo "   Is RHU: " . ($testWorker->isRHU() ? 'Yes' : 'No') . "\n";
    echo "   Visible Patients: " . Patient::forHealthWorker($testWorker)->count() . "\n";
    echo "   Accessible Barangays: " . implode(', ', $testWorker->getAccessibleBarangays()) . "\n";
    echo "   Schedulable Barangays: " . implode(', ', $testWorker->getSchedulableBarangays()) . "\n";
} else {
    echo "   ERROR: Test account not found!\n";
}
echo "\n";

// 5. Check Indexes
echo "5. DATABASE INDEXES:\n";
$indexes = [
    'patients' => ['idx_patients_barangay', 'fk_parent_id'],
    'health_workers' => ['health_workers_barangay_id_index'],
    'vaccination_schedules' => ['idx_vacc_schedules_barangay', 'idx_vacc_schedules_date_status'],
    'barangays' => ['idx_barangays_name', 'idx_barangays_active_schedulable'],
];
foreach ($indexes as $table => $indexNames) {
    echo "   {$table}:\n";
    foreach ($indexNames as $indexName) {
        $exists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        $status = count($exists) > 0 ? '✓' : '✗';
        echo "     {$status} {$indexName}\n";
    }
}
echo "\n";

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  LOGIN CREDENTIALS                                          ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  URL: /health_worker/login                                  ║\n";
echo "║  Username: balayhangin_worker                               ║\n";
echo "║  Password: password123                                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
