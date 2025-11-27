<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COMPREHENSIVE ANALYSIS: Duplicate Entry Error ===\n\n";

// 1. Check the exact duplicate entry mentioned in error
echo "1. CHECKING DUPLICATE ENTRY: 2025-4-4-10-10-Balayhangin-BCG-1\n";
echo str_repeat("-", 70) . "\n";

$duplicate = \App\Models\VaccinationReportSnapshot::where('year', 2025)
    ->where('quarter_start', 4)
    ->where('quarter_end', 4)
    ->where('month_start', 10)
    ->where('month_end', 10)
    ->where('barangay', 'Balayhangin')
    ->where('vaccine_name', 'BCG')
    ->where('version', 1)
    ->first();

if ($duplicate) {
    echo "✗ FOUND! This exact record already exists:\n";
    echo "  ID: {$duplicate->id}\n";
    echo "  Created: {$duplicate->created_at}\n";
    echo "  Male: {$duplicate->male_count}, Female: {$duplicate->female_count}, Total: {$duplicate->total_count}\n";
    echo "  Percentage: {$duplicate->percentage}%\n";
    echo "  Data Source: {$duplicate->data_source}\n";
    echo "  Notes: {$duplicate->notes}\n";
} else {
    echo "✓ NOT FOUND - Record does not exist\n";
}

// 2. Check all records for this period and version
echo "\n\n2. ALL RECORDS FOR: 2025 Q4 Month 10 Version 1\n";
echo str_repeat("-", 70) . "\n";

$allRecords = \App\Models\VaccinationReportSnapshot::where('year', 2025)
    ->where('quarter_start', 4)
    ->where('quarter_end', 4)
    ->where('month_start', 10)
    ->where('month_end', 10)
    ->where('version', 1)
    ->count();

echo "Total records: {$allRecords}\n";

if ($allRecords > 0) {
    $barangays = \App\Models\VaccinationReportSnapshot::where('year', 2025)
        ->where('quarter_start', 4)
        ->where('quarter_end', 4)
        ->where('month_start', 10)
        ->where('month_end', 10)
        ->where('version', 1)
        ->distinct()
        ->pluck('barangay')
        ->sort();
    
    echo "Barangays with data: " . $barangays->count() . "\n";
    foreach ($barangays as $b) {
        $count = \App\Models\VaccinationReportSnapshot::where('year', 2025)
            ->where('quarter_start', 4)
            ->where('quarter_end', 4)
            ->where('month_start', 10)
            ->where('month_end', 10)
            ->where('version', 1)
            ->where('barangay', $b)
            ->count();
        echo "  " . ($b ?: 'TOTAL') . ": {$count} records\n";
    }
}

// 3. Check what max version query returns
echo "\n\n3. VERSION DETECTION QUERY TEST\n";
echo str_repeat("-", 70) . "\n";

$maxVersion = \App\Models\VaccinationReportSnapshot::where('year', 2025)
    ->where('quarter_start', 4)
    ->where('quarter_end', 4)
    ->where('month_start', 10)
    ->where('month_end', 10)
    ->max('version');

echo "Max version found: " . ($maxVersion ?? 'NULL') . "\n";
echo "Next version would be: " . (($maxVersion ?? 0) + 1) . "\n";

// 4. Check report data structure
echo "\n\n4. SIMULATING REPORT GENERATION\n";
echo str_repeat("-", 70) . "\n";

try {
    $service = app(\App\Services\VaccinationReportService::class);
    $report = $service->getCurrentReport(2025, 4, 4, null);
    
    echo "Report data structure:\n";
    echo "  Total rows: " . count($report['data']) . "\n";
    echo "  Date range: {$report['date_range']}\n";
    
    // Check for Balayhangin
    $balayhanginRow = null;
    foreach ($report['data'] as $row) {
        if ($row['barangay'] === 'Balayhangin') {
            $balayhanginRow = $row;
            break;
        }
    }
    
    if ($balayhanginRow) {
        echo "\n  Balayhangin row found:\n";
        echo "    Eligible population: " . ($balayhanginRow['eligible_population_under_1_year'] ?? 'N/A') . "\n";
        echo "    Vaccines count: " . (isset($balayhanginRow['vaccines']) ? count($balayhanginRow['vaccines']) : 0) . "\n";
        
        if (isset($balayhanginRow['vaccines'])) {
            echo "    Vaccine list:\n";
            $count = 0;
            foreach ($balayhanginRow['vaccines'] as $vaccineName => $vaccineData) {
                $count++;
                echo "      {$count}. {$vaccineName}: M={$vaccineData['male_count']}, F={$vaccineData['female_count']}, T={$vaccineData['total_count']}\n";
                if ($count >= 5) {
                    echo "      ... and " . (count($balayhanginRow['vaccines']) - 5) . " more\n";
                    break;
                }
            }
            
            // Check if BCG appears multiple times
            $bcgCount = 0;
            foreach ($balayhanginRow['vaccines'] as $vaccineName => $vaccineData) {
                if ($vaccineName === 'BCG') {
                    $bcgCount++;
                }
            }
            
            if ($bcgCount > 1) {
                echo "\n  ✗ WARNING: BCG appears {$bcgCount} times in vaccines array!\n";
            } else {
                echo "\n  ✓ BCG appears only once in vaccines array\n";
            }
        }
    } else {
        echo "\n  ✗ Balayhangin not found in report data\n";
    }
    
    // Check for duplicate barangays in report
    echo "\n  Checking for duplicate barangays in report:\n";
    $barangayNames = [];
    foreach ($report['data'] as $row) {
        $name = $row['barangay'];
        if (!isset($barangayNames[$name])) {
            $barangayNames[$name] = 0;
        }
        $barangayNames[$name]++;
    }
    
    $duplicates = array_filter($barangayNames, function($count) { return $count > 1; });
    if (count($duplicates) > 0) {
        echo "  ✗ DUPLICATES FOUND:\n";
        foreach ($duplicates as $name => $count) {
            echo "    {$name}: appears {$count} times\n";
        }
    } else {
        echo "  ✓ No duplicate barangays\n";
    }
    
} catch (\Exception $e) {
    echo "  ✗ Error generating report: " . $e->getMessage() . "\n";
}

// 5. Check database unique constraint
echo "\n\n5. DATABASE CONSTRAINT VERIFICATION\n";
echo str_repeat("-", 70) . "\n";

$constraints = DB::select("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'vaccination_report_snapshots'
        AND CONSTRAINT_NAME LIKE '%unique%'
    ORDER BY CONSTRAINT_NAME, ORDINAL_POSITION
");

echo "Unique constraints on vaccination_report_snapshots:\n";
$currentConstraint = null;
$columns = [];
foreach ($constraints as $constraint) {
    if ($currentConstraint !== $constraint->CONSTRAINT_NAME) {
        if ($currentConstraint !== null) {
            echo "  {$currentConstraint}: [" . implode(', ', $columns) . "]\n";
        }
        $currentConstraint = $constraint->CONSTRAINT_NAME;
        $columns = [];
    }
    $columns[] = $constraint->COLUMN_NAME;
}
if ($currentConstraint !== null) {
    echo "  {$currentConstraint}: [" . implode(', ', $columns) . "]\n";
}

echo "\n=== END OF ANALYSIS ===\n";
