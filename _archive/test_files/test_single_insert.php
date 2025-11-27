<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING SINGLE INSERT ===\n\n";

// Simulate exactly what the command does
$targetYear = 2025;
$quarterStart = 4;
$quarterEnd = 4;
$targetMonth = 10;
$nextVersion = 1;

echo "Attempting to create ONE record for Balayhangin + BCG...\n";

try {
    $record = \App\Models\VaccinationReportSnapshot::create([
        'year' => $targetYear,
        'quarter_start' => $quarterStart,
        'quarter_end' => $quarterEnd,
        'month_start' => $targetMonth,
        'month_end' => $targetMonth,
        'barangay' => 'Balayhangin',
        'vaccine_name' => 'BCG',
        'version' => $nextVersion,
        'male_count' => 0,
        'female_count' => 0,
        'total_count' => 0,
        'percentage' => 0,
        'eligible_population' => 0,
        'data_source' => 'calculated',
        'created_by' => null,
        'notes' => 'Test insert'
    ]);
    
    echo "✓ Record created successfully!\n";
    echo "  ID: {$record->id}\n";
    echo "  Created at: {$record->created_at}\n";
    
    // Try to create the EXACT same record again
    echo "\nAttempting to create DUPLICATE record...\n";
    
    try {
        $record2 = \App\Models\VaccinationReportSnapshot::create([
            'year' => $targetYear,
            'quarter_start' => $quarterStart,
            'quarter_end' => $quarterEnd,
            'month_start' => $targetMonth,
            'month_end' => $targetMonth,
            'barangay' => 'Balayhangin',
            'vaccine_name' => 'BCG',
            'version' => $nextVersion,
            'male_count' => 0,
            'female_count' => 0,
            'total_count' => 0,
            'percentage' => 0,
            'eligible_population' => 0,
            'data_source' => 'calculated',
            'created_by' => null,
            'notes' => 'Test insert 2'
        ]);
        
        echo "✗ UNEXPECTED: Duplicate was allowed!\n";
        
    } catch (\Exception $e) {
        echo "✓ EXPECTED: Duplicate was rejected\n";
        echo "  Error: " . $e->getMessage() . "\n";
    }
    
    // Clean up
    echo "\nCleaning up test record...\n";
    $record->forceDelete();
    echo "✓ Test record deleted\n";
    
} catch (\Exception $e) {
    echo "✗ ERROR creating first record:\n";
    echo "  " . $e->getMessage() . "\n";
    echo "\nThis suggests the record ALREADY EXISTS from a previous failed attempt.\n";
    echo "Checking database...\n\n";
    
    $existing = \App\Models\VaccinationReportSnapshot::where('year', 2025)
        ->where('quarter_start', 4)
        ->where('quarter_end', 4)
        ->where('month_start', 10)
        ->where('month_end', 10)
        ->where('barangay', 'Balayhangin')
        ->where('vaccine_name', 'BCG')
        ->where('version', 1)
        ->first();
    
    if ($existing) {
        echo "✗ FOUND existing record!\n";
        echo "  ID: {$existing->id}\n";
        echo "  Created: {$existing->created_at}\n";
        echo "  Notes: {$existing->notes}\n";
        
        echo "\nDeleting this orphaned record...\n";
        $existing->forceDelete();
        echo "✓ Deleted\n";
    } else {
        echo "✓ No existing record found\n";
        echo "This is a different error.\n";
    }
}

echo "\n=== END OF TEST ===\n";
