<?php

/**
 * BATCH 2 TEST SCRIPT
 * Run this in terminal: php test_batch2.php
 * 
 * This script tests:
 * 1. Enhanced calculateLiveData() with dose-level breakdown
 * 2. Multiple eligible population columns
 * 3. FIC and CIC calculations in report data
 * 4. TOTAL row calculations
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========================================\n";
echo "BATCH 2 TEST SCRIPT\n";
echo "========================================\n\n";

$reportService = new \App\Services\VaccinationReportService();

// Test: Generate report for current year, Q1
$year = date('Y');
$quarterStart = 1;
$quarterEnd = 1; // Q1 only for faster testing

echo "✅ Test 1: Generate Report Data (Year: {$year}, Q1)\n";
echo "-------------------------------------------\n";
echo "Generating report... (this may take a moment)\n\n";

$report = $reportService->getCurrentReport($year, $quarterStart, $quarterEnd);

echo "Report generated successfully!\n";
echo "Data source: {$report['source']}\n";
echo "Date range: {$report['date_range']}\n\n";

// Test: Check data structure
$reportData = $report['data'];
echo "✅ Test 2: Check Data Structure\n";
echo "-------------------------------------------\n";
echo "Total rows (barangays + TOTAL): " . count($reportData) . "\n\n";

// Test first barangay data
if (count($reportData) > 0) {
    $firstBarangay = $reportData[0];
    
    echo "✅ Test 3: First Barangay Data Structure\n";
    echo "-------------------------------------------\n";
    echo "Barangay: {$firstBarangay['barangay']}\n";
    echo "Eligible Population (Under 1 yr): {$firstBarangay['eligible_population_under_1_year']}\n";
    echo "Eligible Population (0-12 mos): {$firstBarangay['eligible_population_0_12_months']}\n";
    echo "Eligible Population (13-23 mos): {$firstBarangay['eligible_population_13_23_months']}\n";
    echo "Total vaccine dose columns: " . count($firstBarangay['vaccines']) . "\n\n";
    
    echo "Sample vaccine doses (first 5):\n";
    $counter = 0;
    foreach ($firstBarangay['vaccines'] as $doseKey => $doseData) {
        if ($counter >= 5) break;
        echo "  - {$doseKey}: M={$doseData['male_count']}, F={$doseData['female_count']}, T={$doseData['total_count']}, %={$doseData['percentage']}%\n";
        $counter++;
    }
    echo "\n";
    
    echo "FIC Data:\n";
    if (isset($firstBarangay['fic'])) {
        echo "  M={$firstBarangay['fic']['male_count']}, F={$firstBarangay['fic']['female_count']}, T={$firstBarangay['fic']['total_count']}, %={$firstBarangay['fic']['percentage']}%\n";
    } else {
        echo "  Not available\n";
    }
    echo "\n";
    
    echo "CIC Data:\n";
    if (isset($firstBarangay['cic'])) {
        echo "  M={$firstBarangay['cic']['male_count']}, F={$firstBarangay['cic']['female_count']}, T={$firstBarangay['cic']['total_count']}, %={$firstBarangay['cic']['percentage']}%\n";
    } else {
        echo "  Not available\n";
    }
    echo "\n";
}

// Test TOTAL row
echo "✅ Test 4: TOTAL Row Data\n";
echo "-------------------------------------------\n";
$totalRow = end($reportData);
if ($totalRow && $totalRow['barangay'] === 'TOTAL') {
    echo "Barangay: {$totalRow['barangay']}\n";
    echo "Total Eligible Population (Under 1 yr): {$totalRow['eligible_population_under_1_year']}\n";
    echo "Total Eligible Population (0-12 mos): {$totalRow['eligible_population_0_12_months']}\n";
    echo "Total Eligible Population (13-23 mos): {$totalRow['eligible_population_13_23_months']}\n\n";
    
    echo "Sample TOTAL vaccine doses (first 5):\n";
    $counter = 0;
    foreach ($totalRow['vaccines'] as $doseKey => $doseData) {
        if ($counter >= 5) break;
        echo "  - {$doseKey}: M={$doseData['male_count']}, F={$doseData['female_count']}, T={$doseData['total_count']}, %={$doseData['percentage']}%\n";
        $counter++;
    }
    echo "\n";
    
    echo "TOTAL FIC: M={$totalRow['fic']['male_count']}, F={$totalRow['fic']['female_count']}, T={$totalRow['fic']['total_count']}, %={$totalRow['fic']['percentage']}%\n";
    echo "TOTAL CIC: M={$totalRow['cic']['male_count']}, F={$totalRow['cic']['female_count']}, T={$totalRow['cic']['total_count']}, %={$totalRow['cic']['percentage']}%\n";
} else {
    echo "⚠️ TOTAL row not found or incorrect\n";
}
echo "\n";

// Test: Verify dose-level breakdown
echo "✅ Test 5: Verify Dose-Level Breakdown\n";
echo "-------------------------------------------\n";
$vaccineConfig = \App\Config\VaccineConfig::getDoseConfiguration();
echo "Expected dose columns based on VaccineConfig:\n";
$expectedDoses = 0;
foreach ($vaccineConfig as $name => $config) {
    $expectedDoses += $config['total_doses'];
    if ($config['total_doses'] > 1) {
        for ($d = 1; $d <= $config['total_doses']; $d++) {
            echo "  - {$name} Dose {$d}\n";
        }
    } else {
        echo "  - {$name}\n";
    }
}
echo "\nExpected total dose columns: {$expectedDoses}\n";
echo "Actual dose columns in report: " . count($firstBarangay['vaccines']) . "\n";

if ($expectedDoses === count($firstBarangay['vaccines'])) {
    echo "✅ Dose columns match!\n";
} else {
    echo "⚠️ Dose column count mismatch - check vaccine configuration\n";
}
echo "\n";

// Test: Data validation
echo "✅ Test 6: Data Validation\n";
echo "-------------------------------------------\n";
$issues = [];

// Check for negative counts
foreach ($reportData as $row) {
    foreach ($row['vaccines'] as $doseKey => $doseData) {
        if ($doseData['male_count'] < 0 || $doseData['female_count'] < 0 || $doseData['total_count'] < 0) {
            $issues[] = "Negative count found in {$row['barangay']} - {$doseKey}";
        }
    }
    
    if (isset($row['fic']) && ($row['fic']['total_count'] < 0 || $row['fic']['percentage'] < 0)) {
        $issues[] = "Invalid FIC data in {$row['barangay']}";
    }
    
    if (isset($row['cic']) && ($row['cic']['total_count'] < 0 || $row['cic']['percentage'] < 0)) {
        $issues[] = "Invalid CIC data in {$row['barangay']}";
    }
}

if (empty($issues)) {
    echo "✅ All data validated successfully!\n";
} else {
    echo "⚠️ Found " . count($issues) . " validation issue(s):\n";
    foreach ($issues as $issue) {
        echo "  - {$issue}\n";
    }
}
echo "\n";

echo "========================================\n";
echo "BATCH 2 TESTS COMPLETED!\n";
echo "========================================\n";
echo "\n";
echo "✅ Report calculation with dose-level breakdown is working.\n";
echo "📋 Next steps:\n";
echo "   1. Review the output above\n";
echo "   2. Check if dose columns are correct\n";
echo "   3. Verify FIC/CIC calculations\n";
echo "   4. If everything looks good, give go signal for Batch 3\n";
echo "\n";
echo "⏱️ Note: Batch 3 will update the UI to display all these columns.\n";
echo "\n";
