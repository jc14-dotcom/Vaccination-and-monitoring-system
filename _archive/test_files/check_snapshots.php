<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking snapshots for 2025 Q4 Month 10...\n\n";

$snapshots = \App\Models\VaccinationReportSnapshot::where('year', 2025)
    ->where('quarter_start', 4)
    ->where('quarter_end', 4)
    ->where('month_start', 10)
    ->where('month_end', 10)
    ->select('year', 'quarter_start', 'quarter_end', 'month_start', 'month_end', 'barangay', 'vaccine_name', 'version')
    ->take(10)
    ->get();

echo "Total records: " . $snapshots->count() . "\n\n";

if ($snapshots->count() > 0) {
    echo "Sample records:\n";
    foreach ($snapshots as $s) {
        echo "  Year: {$s->year}, Q{$s->quarter_start}-Q{$s->quarter_end}, M{$s->month_start}-{$s->month_end}, ";
        echo "Barangay: " . ($s->barangay ?: 'TOTAL') . ", Vaccine: {$s->vaccine_name}, Version: {$s->version}\n";
    }
    
    echo "\nVersions breakdown:\n";
    $versions = \App\Models\VaccinationReportSnapshot::where('year', 2025)
        ->where('quarter_start', 4)
        ->where('quarter_end', 4)
        ->where('month_start', 10)
        ->where('month_end', 10)
        ->distinct()
        ->pluck('version')
        ->sort();
    
    foreach ($versions as $v) {
        $count = \App\Models\VaccinationReportSnapshot::where('year', 2025)
            ->where('quarter_start', 4)
            ->where('quarter_end', 4)
            ->where('month_start', 10)
            ->where('month_end', 10)
            ->where('version', $v)
            ->count();
        echo "  Version {$v}: {$count} records\n";
    }
    
    echo "\nMax version: " . $versions->max() . "\n";
}
