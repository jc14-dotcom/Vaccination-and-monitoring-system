<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking for Balayhangin + BCG records...\n\n";

$snapshots = \App\Models\VaccinationReportSnapshot::where('barangay', 'Balayhangin')
    ->where('vaccine_name', 'BCG')
    ->where('version', 1)
    ->select('year', 'quarter_start', 'quarter_end', 'month_start', 'month_end', 'barangay', 'vaccine_name', 'version')
    ->get();

echo "Total Balayhangin BCG v1 records: " . $snapshots->count() . "\n\n";

if ($snapshots->count() > 0) {
    foreach ($snapshots as $s) {
        echo "  Year: {$s->year}, Q{$s->quarter_start}-Q{$s->quarter_end}, M{$s->month_start}-{$s->month_end}, Version: {$s->version}\n";
    }
}

echo "\n\nChecking all 2025 Q4 records...\n";
$q4_snapshots = \App\Models\VaccinationReportSnapshot::where('year', 2025)
    ->where('quarter_start', 4)
    ->where('quarter_end', 4)
    ->count();
    
echo "Total 2025 Q4 records: {$q4_snapshots}\n";

if ($q4_snapshots > 0) {
    echo "\nBreakdown by month:\n";
    for ($m = 10; $m <= 12; $m++) {
        $count = \App\Models\VaccinationReportSnapshot::where('year', 2025)
            ->where('quarter_start', 4)
            ->where('quarter_end', 4)
            ->where('month_start', $m)
            ->where('month_end', $m)
            ->count();
        echo "  Month {$m}: {$count} records\n";
    }
}
