<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'patients'; // Name of the table
    protected $fillable = [
        'parent_id', 
        'name',
        'date_of_birth',
        'place_of_birth',
        'birth_height',
        'birth_weight',
        'barangay',
        'address',
        'mother_name',
        'father_name',
        'contact_no',
        'sex',
    ];

    protected $casts = [
        // All fields decrypted for performance and analytics except address
        // 'name' => 'encrypted',
        // 'date_of_birth' => 'encrypted',
        // 'barangay' => 'encrypted',
        // 'contact_no' => 'encrypted',
        // 'mother_name' => 'encrypted',
        // 'father_name' => 'encrypted',
        // 'place_of_birth' => 'encrypted',  // Decrypted for birth location reports
        // 'birth_height' => 'encrypted',    // Decrypted for growth analytics
        // 'birth_weight' => 'encrypted',    // Decrypted for nutrition monitoring
        
        // Only address remains encrypted (security risk - reveals home location)
        'address' => 'encrypted',
    ];

    /**
     * Scope to filter patients by the current health worker's assigned barangay.
     * - RHU admin (barangay_id = NULL): sees all patients
     * - Barangay worker: sees only patients in their assigned barangay
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\HealthWorker|null $healthWorker Optional health worker (defaults to current auth user)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForHealthWorker($query, $healthWorker = null)
    {
        $healthWorker = $healthWorker ?? Auth::guard('health_worker')->user();
        
        // No health worker or RHU admin = no filter (see all)
        if (!$healthWorker || $healthWorker->isRHU()) {
            return $query;
        }
        
        // Barangay worker = filter by their assigned barangay
        $barangayName = $healthWorker->getAssignedBarangayName();
        if ($barangayName) {
            return $query->where('barangay', $barangayName);
        }
        
        return $query;
    }

    /**
     * Scope to filter patients by a specific barangay name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $barangayName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInBarangay($query, ?string $barangayName)
    {
        if (empty($barangayName)) {
            return $query;
        }
        return $query->where('barangay', $barangayName);
    }

    // Define the relationship with the Parents model
    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    // Define the relationship with the PatientVaccineRecord model
    public function vaccines()
    {
        return $this->hasMany(PatientVaccineRecord::class, 'patient_id', 'id');
    }

    // Add these relationships at the end of the class, before the closing brace
    public function growthRecords()
    {
        return $this->hasMany(PatientGrowthRecord::class)->orderBy('recorded_date', 'desc');
    }

    public function latestGrowthRecord()
    {
        return $this->hasOne(PatientGrowthRecord::class)->latest('recorded_date');
    }

    // Helper method to get current height with fallback to birth height
    public function getCurrentHeight()
    {
        $latest = $this->latestGrowthRecord;
        return $latest ? $latest->height : $this->birth_height;
    }

    // Helper method to get current weight with fallback to birth weight
    public function getCurrentWeight()
    {
        $latest = $this->latestGrowthRecord;
        return $latest ? $latest->weight : $this->birth_weight;
    }

    // Helper method to get the date of current measurement
    public function getCurrentMeasurementDate()
    {
        $latest = $this->latestGrowthRecord;
        return $latest ? $latest->recorded_date : null;
    }

    // Helper method to get formatted age for infants/children
    public function getFormattedAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return 'N/A';
        }
        
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        $now = \Carbon\Carbon::now();
        
        // Use floor() to get whole numbers only (no decimals)
        $ageInYears = (int) $birthDate->diffInYears($now);
        $ageInMonths = (int) $birthDate->diffInMonths($now);
        $ageInDays = (int) $birthDate->diffInDays($now);
        
        // For children 2 years and older, show years
        if ($ageInYears >= 2) {
            return $ageInYears . ' ' . ($ageInYears == 1 ? 'year' : 'years') . ' old';
        }
        
        // For children 1-23 months, show months
        if ($ageInMonths >= 1) {
            return $ageInMonths . ' ' . ($ageInMonths == 1 ? 'month' : 'months') . ' old';
        }
        
        // For newborns less than 1 month
        if ($ageInDays < 7) {
            return $ageInDays . ' ' . ($ageInDays == 1 ? 'day' : 'days') . ' old';
        } else {
            $ageInWeeks = (int) floor($ageInDays / 7);
            return $ageInWeeks . ' ' . ($ageInWeeks == 1 ? 'week' : 'weeks') . ' old';
        }
    }

    // Add this relationship if it's missing:
    public function vaccineRecords()
    {
        return $this->hasMany(PatientVaccineRecord::class);
    }

    // Relationship to vaccination transactions
    public function vaccinationTransactions()
    {
        return $this->hasMany(VaccinationTransaction::class);
    }

    /**
     * Get formatted name as "Surname, First Name MI"
     * Assumes name format: "FirstName MiddleInitial. LastName" or "FirstName LastName"
     */
    public function getFormattedNameAttribute()
    {
        if (!$this->name) {
            return 'N/A';
        }

        $parts = explode(' ', trim($this->name));
        
        if (count($parts) < 2) {
            return $this->name; // Return as-is if format is unexpected
        }

        // Extract components
        $firstName = $parts[0];
        $lastName = end($parts);
        
        // Check if there's a middle initial (between first and last)
        $middleInitial = '';
        if (count($parts) > 2) {
            // Join all middle parts (handles "De La Cruz" etc.)
            $middleParts = array_slice($parts, 1, -1);
            
            // Check if first middle part looks like an initial (e.g., "C.")
            if (strlen($middleParts[0]) <= 2 && str_contains($middleParts[0], '.')) {
                $middleInitial = ' ' . $middleParts[0];
                // Rest are part of last name
                if (count($middleParts) > 1) {
                    $lastName = implode(' ', array_slice($parts, 2));
                }
            } else {
                // All middle parts are part of last name (e.g., "De La Cruz")
                $lastName = implode(' ', array_slice($parts, 1));
            }
        }

        return $lastName . ', ' . $firstName . $middleInitial;
    }

    /**
     * Check if patient is fully immunized (all vaccines completed)
     * A patient is considered fully immunized if all their vaccine records have all required doses filled
     */
    public function isFullyImmunized()
    {
        // Get all vaccine records for this patient
        $vaccineRecords = $this->vaccines()->with('vaccine')->get();
        
        if ($vaccineRecords->isEmpty()) {
            return false; // No vaccine records means not immunized
        }
        
        // Check each vaccine record
        foreach ($vaccineRecords as $record) {
            $vaccine = $record->vaccine;
            if (!$vaccine) continue;
            
            $vaccineName = $vaccine->vaccine_name;
            $dosesDescription = $vaccine->doses_description;
            
            // Determine required doses based on vaccine name and description
            $requiredDoses = 1; // Default to 1 dose
            
            // 3-dose vaccines
            if (in_array($vaccineName, ['Pentavalent', 'Oral Polio', 'Pneumococcal Conjugate'])) {
                $requiredDoses = 3;
            }
            // 2-dose vaccines
            elseif (in_array($vaccineName, ['Inactivated Polio', 'Measles, Mumps, Rubella', 'Tetanus Diphtheria', 'Human Papillomavirus']) 
                    || ($vaccineName === 'Measles Containing' && $dosesDescription === 'Grade 7')) {
                $requiredDoses = 2;
            }
            // 1-dose vaccines: BCG, Hepatitis B, Measles Containing Grade 1
            
            // Check if all required doses are filled
            if ($requiredDoses >= 1 && empty($record->dose_1_date)) {
                return false; // Missing dose 1
            }
            if ($requiredDoses >= 2 && empty($record->dose_2_date)) {
                return false; // Missing dose 2
            }
            if ($requiredDoses >= 3 && empty($record->dose_3_date)) {
                return false; // Missing dose 3
            }
        }
        
        // All vaccines have all required doses
        return true;
    }

    /**
     * Check if patient has any incomplete vaccines
     */
    public function hasIncompleteVaccines()
    {
        return !$this->isFullyImmunized();
    }

    /**
     * Get patient's age group at a specific target date
     * Used for determining eligible population categories
     * 
     * @param \Carbon\Carbon|string $targetDate
     * @return string (under_1_year, 0_12_months, 13_23_months, grade_1, grade_7, older)
     */
    public function getEligibleAgeGroup($targetDate = null)
    {
        if (!$this->date_of_birth) {
            return 'unknown';
        }

        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now();
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        $ageInMonths = $birthDate->diffInMonths($targetDate);

        // Determine age group
        if ($ageInMonths < 12) {
            return 'under_1_year';
        } elseif ($ageInMonths <= 12) {
            return '0_12_months';
        } elseif ($ageInMonths >= 13 && $ageInMonths <= 23) {
            return '13_23_months';
        } elseif ($ageInMonths >= 72 && $ageInMonths <= 84) { // 6-7 years
            return 'grade_1';
        } elseif ($ageInMonths >= 144 && $ageInMonths <= 156) { // 12-13 years
            return 'grade_7';
        } else {
            return 'older';
        }
    }

    /**
     * Check if patient is Fully Immunized Child (FIC) at target date
     * FIC = Child 0-12 months who completed: BCG, HepB, Pentavalent (3), OPV (3), MMR (2)
     * 
     * @param \Carbon\Carbon|string $targetDate
     * @return bool
     */
    public function isFIC($targetDate = null)
    {
        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now();
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        $ageInMonths = $birthDate->diffInMonths($targetDate);

        // Must be 0-12 months old at target date
        if ($ageInMonths > 12) {
            return false;
        }

        // Get required FIC vaccines
        $ficVaccines = \App\Config\VaccineConfig::getFICVaccines();

        // Check if patient has completed all required vaccines
        foreach ($ficVaccines as $vaccineName => $requiredDoses) {
            $vaccineRecord = $this->vaccines()
                ->whereHas('vaccine', function($query) use ($vaccineName) {
                    $query->where('vaccine_name', $vaccineName);
                })
                ->first();

            if (!$vaccineRecord) {
                return false; // Vaccine record doesn't exist
            }

            // Check if all required doses are completed
            for ($dose = 1; $dose <= $requiredDoses; $dose++) {
                $doseField = "dose_{$dose}_date";
                if (empty($vaccineRecord->$doseField)) {
                    return false; // Missing dose
                }
            }
        }

        return true; // All FIC vaccines completed
    }

    /**
     * Check if patient is Completely Immunized Child (CIC) at target date
     * CIC = Child 13-23 months who completed all FIC vaccines + school vaccines
     * 
     * @param \Carbon\Carbon|string $targetDate
     * @return bool
     */
    public function isCIC($targetDate = null)
    {
        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now();
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        $ageInMonths = $birthDate->diffInMonths($targetDate);

        // Must be 13-23 months old at target date
        if ($ageInMonths < 13 || $ageInMonths > 23) {
            return false;
        }

        // Get required CIC vaccines
        $cicVaccines = \App\Config\VaccineConfig::getCICVaccines();

        // Check if patient has completed all required vaccines
        foreach ($cicVaccines as $vaccineName => $requiredDoses) {
            $vaccineRecord = $this->vaccines()
                ->whereHas('vaccine', function($query) use ($vaccineName) {
                    $query->where('vaccine_name', $vaccineName);
                })
                ->first();

            if (!$vaccineRecord) {
                return false; // Vaccine record doesn't exist
            }

            // Check if all required doses are completed
            for ($dose = 1; $dose <= $requiredDoses; $dose++) {
                $doseField = "dose_{$dose}_date";
                if (empty($vaccineRecord->$doseField)) {
                    return false; // Missing dose
                }
            }
        }

        return true; // All CIC vaccines completed
    }

    /**
     * Get patient's age in months at a specific date
     * 
     * @param \Carbon\Carbon|string $targetDate
     * @return int
     */
    public function getAgeInMonths($targetDate = null)
    {
        if (!$this->date_of_birth) {
            return 0;
        }

        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now();
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        
        return (int) $birthDate->diffInMonths($targetDate);
    }
}
