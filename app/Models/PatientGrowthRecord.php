<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientGrowthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 
        'height', 
        'weight', 
        'recorded_date', 
        'recorded_by', 
        'measurement_type', 
        'notes'
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(HealthWorker::class, 'recorded_by');
    }
}
