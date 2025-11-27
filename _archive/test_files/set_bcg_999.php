<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Vaccine;

$vaccine = Vaccine::find(1);
echo "Before: {$vaccine->stocks} doses\n";

$vaccine->stocks = 999;
$vaccine->save();

echo "After: {$vaccine->stocks} doses\n";
echo "\nNow REFRESH the inventory page in your browser and check if BCG shows 999 doses\n";
