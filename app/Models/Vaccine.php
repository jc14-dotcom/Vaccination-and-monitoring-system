<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory;

    protected $fillable = [
        'vaccine_name',
        'doses_description',
        'stocks',
        'available_bottles',
        'doses_per_bottle',
        'doses_used_from_current_bottles',
    ];

    protected $casts = [
        'stocks' => 'integer',
        'available_bottles' => 'integer',
        'doses_per_bottle' => 'integer',
        'doses_used_from_current_bottles' => 'integer',
    ];

    // Relationship with PatientVaccineRecord
    public function vaccineRecords()
    {
        return $this->hasMany(PatientVaccineRecord::class, 'vaccine_id');
    }

    // Relationship with VaccineInventory
    public function inventories()
    {
        return $this->hasMany(VaccineInventory::class, 'vaccine_id');
    }
}
