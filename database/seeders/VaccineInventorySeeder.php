<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vaccine;
use App\Models\VaccineInventory;
use Carbon\Carbon;

class VaccineInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all vaccines
        $vaccines = Vaccine::all();

        foreach ($vaccines as $vaccine) {
            // Create 2-3 inventory batches per vaccine for testing FIFO
            $batchCount = rand(2, 3);
            
            for ($i = 0; $i < $batchCount; $i++) {
                VaccineInventory::create([
                    'vaccine_id' => $vaccine->id,
                    'doses_per_bottle' => 10, // Default 10 doses per bottle
                    'bottles_total' => rand(5, 20),
                    'bottles_used' => rand(0, 5),
                    'doses_used' => rand(0, 30),
                    'received_date' => Carbon::now()->subDays(rand(1, 90)), // Random dates in past 90 days
                    'created_by' => null, // Set to null for seeder
                    'notes' => 'Initial inventory batch ' . ($i + 1),
                ]);
            }
        }
    }
}
