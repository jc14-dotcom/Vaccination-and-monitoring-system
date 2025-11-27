<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\VaccinationReportService;
use Carbon\Carbon;

echo "=== Testing November 2025 Report ===\n\n";

$reportService = new VaccinationReportService();

$startDate = Carbon::create(2025, 11, 1);
$endDate = Carbon::create(2025, 11, 30);

echo "Date Range: {$startDate->toDateString()} to {$endDate->toDateString()}\n\n";

// Get the report
$report = $reportService->calculateLiveData(2025, 11, 11);

echo "Total rows in report: " . count($report) . "\n\n";

if (count($report) > 0) {
    $firstRow = $report[0];
    echo "First Barangay: {$firstRow['barangay']}\n\n";
    
    echo "Vaccine keys with data (non-zero):\n";
    foreach ($firstRow['vaccines'] as $key => $data) {
        if ($data['total_count'] > 0) {
            echo "  - {$key}: M={$data['male_count']}, F={$data['female_count']}, T={$data['total_count']}\n";
        }
    }
    
    if (empty(array_filter($firstRow['vaccines'], fn($v) => $v['total_count'] > 0))) {
        echo "  (No vaccines with data in Balayhangin)\n\n";
        
        echo "Checking what's in Dayap barangay:\n";
        $mmrData = $reportService->getVaccineDoseCount('Dayap', 'Measles, Mumps, Rubella', 1, $startDate, $endDate);
        echo "MMR Dose 1 in Dayap: " . json_encode($mmrData) . "\n";
        
        $ipvData = $reportService->getVaccineDoseCount('Dayap', 'Inactivated Polio', 2, $startDate, $endDate);
        echo "IPV Dose 2 in Dayap: " . json_encode($ipvData) . "\n";
    }
}

echo "\n";
