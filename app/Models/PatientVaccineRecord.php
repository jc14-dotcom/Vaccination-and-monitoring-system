<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVaccineRecord extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'vaccine_id', 'dose_1_date', 'dose_2_date', 'dose_3_date', 'remarks'];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class, 'vaccine_id');
    }
}
