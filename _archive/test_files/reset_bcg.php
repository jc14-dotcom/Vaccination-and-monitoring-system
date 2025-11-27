<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Vaccine;

$vaccine = Vaccine::find(1);
echo "Current BCG: {$vaccine->stocks} doses\n";

$vaccine->stocks = 100;
$vaccine->save();

echo "Updated BCG to: 100 doses (easier to test with)\n";
echo "\nNow refresh /health_worker/inventory and BCG should show 100 doses\n";
echo "Then try updating it to 200 to see if the UI updates!\n";
