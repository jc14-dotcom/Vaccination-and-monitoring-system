<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING IPV 2 KEY STRUCTURE ===\n\n";

$service = app(\App\Services\VaccinationReportService::class);

// Get report data for current year and quarter
$report = $service->getCurrentReport(2025, 1, 1, null);

if (empty($report['data'])) {
    echo "No data found\n";
    exit;
}

// Get first barangay (not TOTAL)
$firstRow = null;
foreach ($report['data'] as $row) {
    if ($row['barangay'] !== 'TOTAL') {
        $firstRow = $row;
        break;
    }
}

if (!$firstRow) {
    echo "No barangay data found\n";
    exit;
}

echo "Barangay: {$firstRow['barangay']}\n";
echo "Vaccine keys in data:\n\n";

foreach ($firstRow['vaccines'] as $key => $data) {
    if (strpos($key, 'IPV') !== false || strpos($key, 'Inactivated') !== false) {
        echo "  Key: '{$key}'\n";
        echo "    Male: {$data['male_count']}, Female: {$data['female_count']}, Total: {$data['total_count']}\n\n";
    }
}

echo "\n=== CHECKING VACCINE CONFIG ===\n\n";

$config = \App\Config\VaccineConfig::getDoseConfiguration();

if (isset($config['Inactivated Polio Vaccine'])) {
    $ipvConfig = $config['Inactivated Polio Vaccine'];
    echo "IPV Configuration:\n";
    echo "  Acronym: {$ipvConfig['acronym']}\n";
    echo "  Total Doses: {$ipvConfig['total_doses']}\n";
    
    if (isset($ipvConfig['recommended_ages'][2])) {
        echo "  IPV Dose 2 recommended age: ";
        echo $ipvConfig['recommended_ages'][2]['min'] . " to " . $ipvConfig['recommended_ages'][2]['max'] . " months\n";
    }
}

echo "\n=== END OF TEST ===\n";
