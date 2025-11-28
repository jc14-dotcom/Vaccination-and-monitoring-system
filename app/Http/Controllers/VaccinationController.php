<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\PatientVaccineRecord;
use App\Models\Vaccine;
use App\Models\VaccinationTransaction;
use App\Models\VaccinationSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VaccinationController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    public function update(Request $request, $id)
    {
        $healthWorker = $this->getHealthWorker();
        $patient = Patient::findOrFail($id);
        
        // Verify health worker can access this patient's barangay
        if ($healthWorker && !$healthWorker->canAccessBarangay($patient->barangay)) {
            return redirect()->back()
                ->with('error', 'You do not have permission to update vaccinations for this patient.');
        }
        
        // Validate active vaccination schedule exists before processing
        $bypassScheduleCheck = $request->input('bypass_schedule') === '1';
        if (!$bypassScheduleCheck) {
            $activeSchedule = VaccinationSchedule::where('status', 'active')
                ->where(function($query) use ($patient) {
                    $query->where('barangay', $patient->barangay)
                          ->orWhere('barangay', 'RHU/All Barangays');
                })
                ->first();
            
            if (!$activeSchedule) {
                return redirect()->back()
                    ->with('error', 'Cannot record vaccinations - No active vaccination schedule found for this patient\'s barangay.');
            }
        }
        
        // Check if this is a backdate entry (only check NEWLY CHANGED dates)
        $isBackdated = false;
        $backdateType = $request->input('backdate_type'); // 'calauan' or 'external'
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $today = now('Asia/Manila')->startOfDay()->toDateString(); // Ensure we get just the date part
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $today = now()->startOfDay()->toDateString();
        
        if ($request->has('vaccinations')) {
            foreach ($request->vaccinations as $vaccinationId => $data) {
                // Get the existing vaccination record to compare old vs new dates
                $existingVaccination = PatientVaccineRecord::find($vaccinationId);
                if (!$existingVaccination) continue;
                
                // Only check dates that are NEW or CHANGED
                if (!empty($data['dose_1_date'])) {
                    $newDose1 = date('Y-m-d', strtotime($data['dose_1_date']));
                    $oldDose1 = $existingVaccination->dose_1_date;
                    // If this is a new date (was empty) or changed date, and it's in the past
                    if ((!$oldDose1 || $oldDose1 !== $newDose1) && $newDose1 < $today) {
                        $isBackdated = true;
                        break;
                    }
                }
                
                if (!empty($data['dose_2_date'] ?? null)) {
                    $newDose2 = date('Y-m-d', strtotime($data['dose_2_date']));
                    $oldDose2 = $existingVaccination->dose_2_date;
                    if ((!$oldDose2 || $oldDose2 !== $newDose2) && $newDose2 < $today) {
                        $isBackdated = true;
                        break;
                    }
                }
                
                if (!empty($data['dose_3_date'] ?? null)) {
                    $newDose3 = date('Y-m-d', strtotime($data['dose_3_date']));
                    $oldDose3 = $existingVaccination->dose_3_date;
                    if ((!$oldDose3 || $oldDose3 !== $newDose3) && $newDose3 < $today) {
                        $isBackdated = true;
                        break;
                    }
                }
            }
        }
        
        // If backdated but user hasn't chosen type yet, return error
        if ($isBackdated && !$backdateType) {
            return redirect()->back()
                ->with('error', 'Backdated entry detected. Please specify where the vaccination was administered.')
                ->withInput();
        }
        
        $missedPatients = session('missed_patients', []);
        $vaccinatedPatients = session('vaccinated_patients', []);
        $anyVaccinationChanged = false;
        $stockErrors = [];

        if ($request->has('vaccinations')) {
            foreach ($request->vaccinations as $vaccinationId => $data) {
                $vaccination = PatientVaccineRecord::findOrFail($vaccinationId);
                $vaccine = $vaccination->vaccine;
                $doseCount = $this->getVaccineDoseCount($vaccine->doses_description);

                // Track old dates to detect new vaccinations
                $oldDose1 = $vaccination->dose_1_date;
                $oldDose2 = $vaccination->dose_2_date;
                $oldDose3 = $vaccination->dose_3_date;

                // Count how many NEW doses are being administered
                $newDosesAdministered = 0;
                if (!$oldDose1 && !empty($data['dose_1_date'])) {
                    $newDosesAdministered++;
                }
                if ($doseCount >= 2 && !$oldDose2 && !empty($data['dose_2_date'])) {
                    $newDosesAdministered++;
                }
                if ($doseCount >= 3 && !$oldDose3 && !empty($data['dose_3_date'])) {
                    $newDosesAdministered++;
                }

                // VALIDATE STOCK BEFORE ALLOWING VACCINATION (only for non-backdated entries)
                if ($newDosesAdministered > 0 && !$isBackdated) {
                    $availableStock = (int) ($vaccine->stocks ?? 0);
                    if ($availableStock < $newDosesAdministered) {
                        $stockErrors[] = "{$vaccine->vaccine_name} - Insufficient stock! Available: {$availableStock} doses, Required: {$newDosesAdministered} doses";
                        continue; // Skip this vaccination
                    }
                }

                // Check if any of the dates have changed
                $doseDateChanged = false;
                if (isset($data['dose_1_date']) && $vaccination->dose_1_date !== $data['dose_1_date']) {
                    $doseDateChanged = true;
                }
                if ($doseCount >= 2 && isset($data['dose_2_date']) && $vaccination->dose_2_date !== $data['dose_2_date']) {
                    $doseDateChanged = true;
                }
                if ($doseCount >= 3 && isset($data['dose_3_date']) && $vaccination->dose_3_date !== $data['dose_3_date']) {
                    $doseDateChanged = true;
                }

                // Check if remarks changed
                $remarksChanged = isset($data['remarks']) && $vaccination->remarks !== $data['remarks'];

                // Only update if any field changed
                if ($doseDateChanged || $remarksChanged) {
                    $updateData = [
                        'dose_1_date' => $data['dose_1_date'] ?? null,
                        'remarks' => $data['remarks'] ?? null,
                    ];
                    if ($doseCount >= 2) {
                        $updateData['dose_2_date'] = $data['dose_2_date'] ?? null;
                    }
                    if ($doseCount >= 3) {
                        $updateData['dose_3_date'] = $data['dose_3_date'] ?? null;
                    }
                    $vaccination->update($updateData);

                    // AUTO-DEDUCT INVENTORY: Only for non-backdated entries
                    if ($doseDateChanged && $newDosesAdministered > 0 && !$isBackdated) {
                        $this->deductVaccineStock($vaccine, $newDosesAdministered);
                    }

                    // CREATE VACCINATION TRANSACTIONS for new doses
                    if ($doseDateChanged) {
                        $this->createVaccinationTransactions(
                            $patient,
                            $vaccination,
                            $vaccine,
                            $data,
                            $oldDose1,
                            $oldDose2,
                            $oldDose3,
                            $doseCount,
                            $isBackdated,
                            $backdateType
                        );
                    }
                }

                // Only mark as vaccinated if a dose date changed
                if ($doseDateChanged) {
                    $anyVaccinationChanged = true;
                    $hasAnyDose = !empty($data['dose_1_date']) || 
                                 (!empty($data['dose_2_date'] ?? null)) || 
                                 (!empty($data['dose_3_date'] ?? null));
                    if ($hasAnyDose && !in_array($patient->id, $vaccinatedPatients)) {
                        $vaccinatedPatients[] = $patient->id;
                        if (in_array($patient->id, $missedPatients)) {
                            $missedPatients = array_diff($missedPatients, [$patient->id]);
                        }
                    }
                }
            }
        }
        
        session([
            'missed_patients' => $missedPatients,
            'vaccinated_patients' => $vaccinatedPatients
        ]);

        // If there were stock errors, redirect back to patient card with error messages
        if (count($stockErrors) > 0) {
            return redirect()->route('vaccination.form', $id)
                ->with('error', 'Vaccination failed due to insufficient stock:')
                ->with('stock_errors', $stockErrors);
        }

        // Check if any data was actually submitted and updated
        $hasVaccinations = $request->has('vaccinations') && count($request->vaccinations) > 0;
        if (!$hasVaccinations) {
            return redirect()->route('vaccination.form', $id)
                ->with('error', 'No vaccination data was submitted. Please try again.');
        }

        // Stay on the patient card page with success message
        return redirect()->route('vaccination.form', $id)
            ->with('success', 'Vaccination records updated successfully.')
            ->with('vaccination_changed', $anyVaccinationChanged);
    }

    /**
     * Determine how many doses a vaccine has based on its description
     * 
     * @param string $dosesDescription
     * @return int
     */
    private function getVaccineDoseCount($dosesDescription)
    {
        // Count the number of doses based on the description
        if (strpos($dosesDescription, '&') !== false || strpos($dosesDescription, ',') !== false) {
            // If description contains "&" or "," it likely has multiple doses
            return substr_count($dosesDescription, '&') + substr_count($dosesDescription, ',') + 1;
        } else if (strpos($dosesDescription, 'Pagkapanganak') !== false || 
                   strpos($dosesDescription, 'Birth') !== false || 
                   strpos($dosesDescription, 'Grade') !== false) {
            // Common patterns for single dose vaccines
            return 1;
        }
        
        // Count the number of months/dates mentioned
        preg_match_all('/\d+\s*(Buwan|Month|Year|Taon)/', $dosesDescription, $matches);
        $count = count($matches[0]);
        
        return $count > 0 ? $count : 1; // Return at least 1
    }

    /**
     * Deduct vaccine stock from inventory when doses are administered
     * 
     * @param Vaccine $vaccine
     * @param int $dosesAdministered
     * @return void
     */
    private function deductVaccineStock(Vaccine $vaccine, int $dosesAdministered)
    {
        DB::beginTransaction();

        try {
            // Reload the vaccine to get fresh data
            $vaccine->refresh();

            // Deduct doses from stock
            $vaccine->stocks -= $dosesAdministered;

            // Update doses used counter
            $vaccine->doses_used_from_current_bottles += $dosesAdministered;

            // Check if we've consumed a full bottle
            $dosesPerBottle = max(1, (int) $vaccine->doses_per_bottle);
            
            while ($vaccine->doses_used_from_current_bottles >= $dosesPerBottle && $vaccine->available_bottles > 0) {
                // Deduct one bottle
                $vaccine->available_bottles -= 1;
                // Reset counter (subtract the bottle's worth of doses)
                $vaccine->doses_used_from_current_bottles -= $dosesPerBottle;
            }

            // Save the updated vaccine
            $vaccine->save();

            // Clear cache to ensure fresh inventory data
            Cache::forget('vaccine_stocks_list');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to deduct vaccine stock for {$vaccine->vaccine_name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create vaccination transaction records for newly administered doses
     * 
     * @param Patient $patient
     * @param PatientVaccineRecord $vaccination
     * @param Vaccine $vaccine
     * @param array $data
     * @param string|null $oldDose1
     * @param string|null $oldDose2
     * @param string|null $oldDose3
     * @param int $doseCount
     * @param bool $isBackdated
     * @param string|null $backdateType
     * @return void
     */
    private function createVaccinationTransactions(
        Patient $patient,
        PatientVaccineRecord $vaccination,
        Vaccine $vaccine,
        array $data,
        $oldDose1,
        $oldDose2,
        $oldDose3,
        int $doseCount,
        bool $isBackdated = false,
        $backdateType = null
    ) {
        $vaccinationSessionId = session('vaccination_session_id');
        $healthWorkerId = Auth::guard('health_worker')->id();
        
        // Determine flags based on backdate type
        $administeredElsewhere = ($isBackdated && $backdateType === 'external');
        $stockOverride = ($isBackdated && $backdateType === 'calauan');
        
        // Auto-detect active vaccination schedule based on patient's barangay
        // Set to NULL if external vaccination
        $vaccinationScheduleId = $administeredElsewhere ? null : $this->getActiveScheduleForPatient($patient);

        // Check and create transaction for Dose 1
        if (!$oldDose1 && !empty($data['dose_1_date'])) {
            VaccinationTransaction::create([
                'vaccination_session_id' => $vaccinationSessionId,
                'vaccination_schedule_id' => $vaccinationScheduleId,
                'patient_id' => $patient->id,
                'vaccine_id' => $vaccine->id,
                'patient_vaccine_record_id' => $vaccination->id,
                'dose_number' => 1,
                'doses_deducted' => $isBackdated ? 0 : 1,
                'vaccinated_at' => $data['dose_1_date'],
                'vaccinated_by' => $healthWorkerId,
                'notes' => $data['remarks'] ?? null,
                'administered_elsewhere' => $administeredElsewhere,
                'stock_override' => $stockOverride,
            ]);
        }

        // Check and create transaction for Dose 2
        if ($doseCount >= 2 && !$oldDose2 && !empty($data['dose_2_date'])) {
            VaccinationTransaction::create([
                'vaccination_session_id' => $vaccinationSessionId,
                'vaccination_schedule_id' => $vaccinationScheduleId,
                'patient_id' => $patient->id,
                'vaccine_id' => $vaccine->id,
                'patient_vaccine_record_id' => $vaccination->id,
                'dose_number' => 2,
                'doses_deducted' => $isBackdated ? 0 : 1,
                'vaccinated_at' => $data['dose_2_date'],
                'vaccinated_by' => $healthWorkerId,
                'notes' => $data['remarks'] ?? null,
                'administered_elsewhere' => $administeredElsewhere,
                'stock_override' => $stockOverride,
            ]);
        }

        // Check and create transaction for Dose 3
        if ($doseCount >= 3 && !$oldDose3 && !empty($data['dose_3_date'])) {
            VaccinationTransaction::create([
                'vaccination_session_id' => $vaccinationSessionId,
                'vaccination_schedule_id' => $vaccinationScheduleId,
                'patient_id' => $patient->id,
                'vaccine_id' => $vaccine->id,
                'patient_vaccine_record_id' => $vaccination->id,
                'dose_number' => 3,
                'doses_deducted' => $isBackdated ? 0 : 1,
                'vaccinated_at' => $data['dose_3_date'],
                'vaccinated_by' => $healthWorkerId,
                'notes' => $data['remarks'] ?? null,
                'administered_elsewhere' => $administeredElsewhere,
                'stock_override' => $stockOverride,
            ]);
        }
    }

    /**
     * Get active vaccination schedule for a patient based on their barangay
     * Priority: Patient's specific barangay schedule > RHU/All Barangays schedule
     * 
     * @param Patient $patient
     * @return int|null
     */
    private function getActiveScheduleForPatient(Patient $patient)
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $today = now('Asia/Manila')->toDateString();
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $today = now()->toDateString();
        
        // First, try to find a schedule specifically for the patient's barangay
        $schedule = \App\Models\VaccinationSchedule::where('vaccination_date', $today)
            ->where('barangay', $patient->barangay)
            ->where('status', 'active')
            ->first();
        
        // If no specific barangay schedule, check for RHU/All Barangays schedule
        if (!$schedule) {
            $schedule = \App\Models\VaccinationSchedule::where('vaccination_date', $today)
                ->where('barangay', 'RHU/All Barangays')
                ->where('status', 'active')
                ->first();
        }
        
        return $schedule ? $schedule->id : null;
    }
}
