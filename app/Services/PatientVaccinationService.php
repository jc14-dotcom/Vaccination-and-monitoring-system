<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Vaccine;
use App\Models\PatientVaccineRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientVaccinationService
{
    /**
     * Create standard vaccination records for a new patient
     */
    public function createStandardVaccinationRecords(Patient $patient): void
    {
        try {
            DB::beginTransaction();

            // Get all standard vaccines in the correct order
            $standardVaccines = $this->getStandardVaccines();

            foreach ($standardVaccines as $vaccine) {
                // Check if record already exists (safety check)
                $existingRecord = PatientVaccineRecord::where('patient_id', $patient->id)
                                                    ->where('vaccine_id', $vaccine->id)
                                                    ->first();

                if (!$existingRecord) {
                    PatientVaccineRecord::create([
                        'patient_id' => $patient->id,
                        'vaccine_id' => $vaccine->id,
                        'dose_1_date' => null,
                        'dose_2_date' => null,
                        'dose_3_date' => null,
                        'remarks' => null,
                    ]);
                }
            }

            DB::commit();
            Log::info("Standard vaccination records created for patient {$patient->id}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create vaccination records for patient {$patient->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all standard vaccines in the correct order for infants
     */
    private function getStandardVaccines(): \Illuminate\Support\Collection
    {
        // Define the standard vaccination schedule order
        $standardVaccineNames = [
            'BCG',
            'Hepatitis B', 
            'Pentavalent',
            'Oral Polio',
            'Inactivated Polio',
            'Pneumococcal Conjugate',
            'Measles, Mumps, Rubella',
            'Tetanus Diphtheria',
            'Human Papillomavirus'
        ];

        $vaccines = collect();

        // Get vaccines in the specified order
        foreach ($standardVaccineNames as $vaccineName) {
            $vaccine = Vaccine::where('vaccine_name', $vaccineName)->first();
            if ($vaccine) {
                $vaccines->push($vaccine);
            }
        }

        // Add Measles Containing vaccines in correct order (Grade 1 first, then Grade 7)
        $measlesGrade1 = Vaccine::where('vaccine_name', 'Measles Containing')
                               ->where('doses_description', 'Grade 1')
                               ->first();
                               
        $measlesGrade7 = Vaccine::where('vaccine_name', 'Measles Containing')
                               ->where('doses_description', 'Grade 7')
                               ->first();

        if ($measlesGrade1) {
            $vaccines->push($measlesGrade1);
        }
        
        if ($measlesGrade7) {
            $vaccines->push($measlesGrade7);
        }

        return $vaccines;
    }

    /**
     * Fix existing patients who don't have complete vaccination records
     */
    public function fixIncompletePatientRecords(): array
    {
        $results = [
            'patients_processed' => 0,
            'records_created' => 0,
            'errors' => []
        ];

        try {
            $allPatients = Patient::all();
            $standardVaccines = $this->getStandardVaccines();

            foreach ($allPatients as $patient) {
                $results['patients_processed']++;
                
                foreach ($standardVaccines as $vaccine) {
                    $existingRecord = PatientVaccineRecord::where('patient_id', $patient->id)
                                                        ->where('vaccine_id', $vaccine->id)
                                                        ->first();

                    if (!$existingRecord) {
                        try {
                            PatientVaccineRecord::create([
                                'patient_id' => $patient->id,
                                'vaccine_id' => $vaccine->id,
                                'dose_1_date' => null,
                                'dose_2_date' => null,
                                'dose_3_date' => null,
                                'remarks' => null,
                            ]);
                            $results['records_created']++;
                        } catch (\Exception $e) {
                            $results['errors'][] = "Failed to create record for patient {$patient->id}, vaccine {$vaccine->id}: " . $e->getMessage();
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $results['errors'][] = "General error: " . $e->getMessage();
        }

        return $results;
    }
}
