<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEEP DATABASE SEARCH ===\n\n";

// Search WITHOUT soft delete scope
echo "1. Searching with withTrashed()...\n";
$existing = \App\Models\VaccinationReportSnapshot::withTrashed()
    ->where('year', 2025)
    ->where('quarter_start', 4)
    ->where('quarter_end', 4)
    ->where('month_start', 10)
    ->where('month_end', 10)
    ->where('barangay', 'Balayhangin')
    ->where('vaccine_name', 'BCG')
    ->where('version', 1)
    ->first();

if ($existing) {
    echo "✓ FOUND with withTrashed()!\n";
    echo "  ID: {$existing->id}\n";
    echo "  Created: {$existing->created_at}\n";
    echo "  Deleted: " . ($existing->deleted_at ?? 'NULL') . "\n";
    echo "  Notes: {$existing->notes}\n";
} else {
    echo "✗ Still not found\n";
}

// Direct SQL query
echo "\n2. Direct SQL query...\n";
$results = DB::select("
    SELECT id, year, quarter_start, quarter_end, month_start, month_end, 
           barangay, vaccine_name, version, deleted_at, notes,
           CHAR_LENGTH(barangay) as barangay_length,
           HEX(barangay) as barangay_hex
    FROM vaccination_report_snapshots
    WHERE year = 2025
        AND quarter_start = 4
        AND quarter_end = 4
        AND month_start = 10
        AND month_end = 10
        AND vaccine_name = 'BCG'
        AND version = 1
    LIMIT 10
");

echo "Found " . count($results) . " records\n";
foreach ($results as $row) {
    echo "\n  Record ID: {$row->id}\n";
    echo "  Barangay: '{$row->barangay}' (length: {$row->barangay_length})\n";
    echo "  Barangay HEX: {$row->barangay_hex}\n";
    echo "  Vaccine: {$row->vaccine_name}\n";
    echo "  Version: {$row->version}\n";
    echo "  Deleted: " . ($row->deleted_at ?? 'NULL') . "\n";
    echo "  Notes: " . ($row->notes ?? 'NULL') . "\n";
}

// Check all records for this period regardless of barangay
echo "\n3. All records for 2025 Q4 M10 v1...\n";
$all = DB::select("
    SELECT barangay, vaccine_name, COUNT(*) as count
    FROM vaccination_report_snapshots
    WHERE year = 2025
        AND quarter_start = 4
        AND quarter_end = 4
        AND month_start = 10
        AND month_end = 10
        AND version = 1
    GROUP BY barangay, vaccine_name
    ORDER BY barangay, vaccine_name
    LIMIT 20
");

echo "Total unique combinations: " . count($all) . "\n";
foreach ($all as $row) {
    echo "  " . ($row->barangay ?? 'NULL') . " + {$row->vaccine_name}: {$row->count} record(s)\n";
}

echo "\n=== END OF SEARCH ===\n";
