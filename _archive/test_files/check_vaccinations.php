<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\VaccinationTransaction;
use Illuminate\Support\Facades\DB;

echo "=== Checking Vaccination Transactions ===\n\n";

// Check total vaccinations
$total = VaccinationTransaction::count();
echo "Total vaccination transactions: {$total}\n\n";

// Check date range of vaccinations
$dateRange = VaccinationTransaction::selectRaw('MIN(created_at) as earliest, MAX(created_at) as latest')
    ->first();
echo "Date range of vaccinations:\n";
echo "  Earliest: " . ($dateRange->earliest ?? 'None') . "\n";
echo "  Latest: " . ($dateRange->latest ?? 'None') . "\n\n";

// Check vaccinations in Jan-Mar 2025
$jan2025Count = VaccinationTransaction::whereBetween('created_at', ['2025-01-01', '2025-03-31'])
    ->count();
echo "Vaccinations between Jan 01, 2025 - Mar 31, 2025: {$jan2025Count}\n\n";

// Show some sample vaccination dates
echo "Sample vaccination records:\n";
$samples = VaccinationTransaction::join('patients', 'vaccination_transactions.patient_id', '=', 'patients.id')
    ->select('patients.first_name', 'patients.last_name', 'vaccination_transactions.created_at', 'vaccination_transactions.vaccine_name')
    ->limit(10)
    ->get();

foreach ($samples as $sample) {
    echo "  - {$sample->first_name} {$sample->last_name}: {$sample->vaccine_name} on {$sample->created_at}\n";
}

echo "\n";
