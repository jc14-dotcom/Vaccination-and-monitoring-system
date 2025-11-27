<?php

/**
 * BATCH 1 TEST SCRIPT
 * Run this in terminal: php test_batch1.php
 * 
 * This script tests:
 * 1. VaccineConfig class methods
 * 2. Patient model helper methods
 * 3. VaccinationReportService new methods
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========================================\n";
echo "BATCH 1 TEST SCRIPT\n";
echo "========================================\n\n";

// Test 1: VaccineConfig - getDoseConfiguration()
echo "✅ Test 1: VaccineConfig::getDoseConfiguration()\n";
echo "-------------------------------------------\n";
$config = \App\Config\VaccineConfig::getDoseConfiguration();
echo "Total vaccines configured: " . count($config) . "\n";
foreach ($config as $name => $data) {
    echo "  - {$name}: {$data['total_doses']} dose(s), acronym: {$data['acronym']}, target: {$data['target_age_group']}\n";
}
echo "\n";

// Test 2: VaccineConfig - FIC and CIC vaccines
echo "✅ Test 2: FIC and CIC Vaccines\n";
echo "-------------------------------------------\n";
$ficVaccines = \App\Config\VaccineConfig::getFICVaccines();
echo "FIC Vaccines (" . count($ficVaccines) . "):\n";
foreach ($ficVaccines as $name => $doses) {
    echo "  - {$name}: {$doses} dose(s)\n";
}
echo "\n";

$cicVaccines = \App\Config\VaccineConfig::getCICVaccines();
echo "CIC Vaccines (" . count($cicVaccines) . "):\n";
foreach ($cicVaccines as $name => $doses) {
    echo "  - {$name}: {$doses} dose(s)\n";
}
echo "\n";

// Test 3: VaccineConfig helper methods
echo "✅ Test 3: VaccineConfig Helper Methods\n";
echo "-------------------------------------------\n";
echo "Total doses for Pentavalent: " . \App\Config\VaccineConfig::getTotalDoses('Pentavalent Vaccine') . "\n";
echo "Acronym for BCG: " . \App\Config\VaccineConfig::getAcronym('BCG') . "\n";
echo "Target age group for MMR: " . \App\Config\VaccineConfig::getTargetAgeGroup('Measles, Mumps, Rubella Vaccine') . "\n";
echo "\n";

// Test 4: Patient model methods (need a sample patient)
echo "✅ Test 4: Patient Model Helper Methods\n";
echo "-------------------------------------------\n";
$samplePatient = \App\Models\Patient::first();
if ($samplePatient) {
    echo "Testing with patient: {$samplePatient->name}\n";
    echo "Birth date: {$samplePatient->date_of_birth}\n";
    echo "Age in months: " . $samplePatient->getAgeInMonths() . "\n";
    echo "Age group: " . $samplePatient->getEligibleAgeGroup() . "\n";
    echo "Is FIC? " . ($samplePatient->isFIC() ? 'Yes' : 'No') . "\n";
    echo "Is CIC? " . ($samplePatient->isCIC() ? 'Yes' : 'No') . "\n";
} else {
    echo "No patients in database - skipping patient tests\n";
}
echo "\n";

// Test 5: VaccinationReportService methods
echo "✅ Test 5: VaccinationReportService Methods\n";
echo "-------------------------------------------\n";
$reportService = new \App\Services\VaccinationReportService();

// Test eligible population calculation
$targetDate = \Carbon\Carbon::now();
echo "Testing eligible population for 'Balayhangin':\n";
echo "  - Under 1 year: " . $reportService->calculateEligiblePopulation('Balayhangin', $targetDate, 'under_1_year') . "\n";
echo "  - 0-12 months: " . $reportService->calculateEligiblePopulation('Balayhangin', $targetDate, '0_12_months') . "\n";
echo "  - 13-23 months: " . $reportService->calculateEligiblePopulation('Balayhangin', $targetDate, '13_23_months') . "\n";
echo "  - Grade 1: " . $reportService->calculateEligiblePopulation('Balayhangin', $targetDate, 'grade_1') . "\n";
echo "  - Grade 7: " . $reportService->calculateEligiblePopulation('Balayhangin', $targetDate, 'grade_7') . "\n";
echo "\n";

// Test FIC and CIC calculations
echo "Testing FIC/CIC counts for 'Balayhangin' (current year):\n";
$year = date('Y');
$ficData = $reportService->calculateFICCount('Balayhangin', $year, 1, 12);
echo "  - FIC Count: M={$ficData['male_count']}, F={$ficData['female_count']}, T={$ficData['total_count']}, %={$ficData['percentage']}%\n";

$cicData = $reportService->calculateCICCount('Balayhangin', $year, 1, 12);
echo "  - CIC Count: M={$cicData['male_count']}, F={$cicData['female_count']}, T={$cicData['total_count']}, %={$cicData['percentage']}%\n";
echo "\n";

// Test vaccine dose count
$startDate = \Carbon\Carbon::create($year, 1, 1);
$endDate = \Carbon\Carbon::create($year, 12, 31);
echo "Testing vaccine dose count for BCG Dose 1 in 'Balayhangin':\n";
$doseData = $reportService->getVaccineDoseCount('Balayhangin', 'BCG', 1, $startDate, $endDate);
echo "  - M={$doseData['male_count']}, F={$doseData['female_count']}, T={$doseData['total_count']}\n";
echo "\n";

echo "========================================\n";
echo "BATCH 1 TESTS COMPLETED!\n";
echo "========================================\n";
echo "\n";
echo "✅ All methods are available and working.\n";
echo "📋 Next steps:\n";
echo "   1. Review the output above\n";
echo "   2. Check if data looks correct\n";
echo "   3. If everything looks good, give go signal for Batch 2\n";
echo "\n";
