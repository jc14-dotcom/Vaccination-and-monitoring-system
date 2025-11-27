<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientVaccineRecord;
use App\Models\Feedback;
use App\Models\Vaccine;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\PatientVaccinationService;
use App\Models\VaccinationSchedule;

class PatientController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    public function index()
    {
        $healthWorker = $this->getHealthWorker();
        
        // Get patients filtered by health worker's barangay access
        $patients = Patient::forHealthWorker($healthWorker)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate total patients (filtered by barangay)
        $totalPatients = $patients->count();
        
        // Calculate vaccinated patients (patients with at least one vaccine record)
        // For barangay workers, only count patients in their barangay
        $vaccinatedQuery = PatientVaccineRecord::distinct('patient_id')
            ->whereNotNull('dose_1_date');
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            $patientIds = Patient::forHealthWorker($healthWorker)->pluck('id');
            $vaccinatedQuery->whereIn('patient_id', $patientIds);
        }
        $vaccinated = $vaccinatedQuery->count('patient_id');
        
        // Count feedback submissions (filtered by barangay for barangay workers)
        if ($healthWorker && !$healthWorker->isRHU()) {
            $feedbackCount = Feedback::where('barangay', $healthWorker->getAssignedBarangayName())->count();
        } else {
            $feedbackCount = Feedback::count();
        }
        
        // Get upcoming scheduled vaccination days (next 5, including today)
        // For barangay workers, only show their barangay's schedules
        $schedulesQuery = VaccinationSchedule::where('vaccination_date', '>=', Carbon::today())
            ->whereIn('status', ['scheduled', 'active']);
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            $barangayName = $healthWorker->getAssignedBarangayName();
            $schedulesQuery->where(function($q) use ($barangayName) {
                $q->where('barangay', $barangayName)
                  ->orWhere('barangay', 'RHU/All Barangays');
            });
        }
        
        $upcomingVaccinations = $schedulesQuery
            ->orderBy('vaccination_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function($schedule) {
                // Check if vaccination is today (ongoing)
                $isToday = $schedule->vaccination_date->isToday();
                $statusText = $isToday ? 'Ongoing' : $schedule->daysUntil();
                
                return (object)[
                    'patient_name' => $schedule->barangay,
                    'vaccine_name' => $statusText,
                    'scheduled_date' => $schedule->vaccination_date->format('M d, Y'),
                    'patient_id' => null, // No patient ID for schedule
                    'is_ongoing' => $isToday, // Flag for styling
                ];
            });
        
        // Monthly vaccination data for the current year (Jan to Dec)
        // For barangay workers, only count their barangay's patients
        $currentYear = Carbon::now()->year;
        $monthlyData = [];
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            $patientIds = Patient::forHealthWorker($healthWorker)->pluck('id');
            for ($month = 1; $month <= 12; $month++) {
                $count = PatientVaccineRecord::whereIn('patient_id', $patientIds)
                    ->whereYear('dose_1_date', $currentYear)
                    ->whereMonth('dose_1_date', $month)
                    ->count();
                $monthlyData[] = $count;
            }
        } else {
            for ($month = 1; $month <= 12; $month++) {
                $count = PatientVaccineRecord::whereYear('dose_1_date', $currentYear)
                    ->whereMonth('dose_1_date', $month)
                    ->count();
                $monthlyData[] = $count;
            }
        }
        
        // Vaccination status data (Complete, Partial, Not Started)
        $filteredPatients = Patient::forHealthWorker($healthWorker)->get();
        $totalPatientCount = $filteredPatients->count();
        
        // Complete: Patients who have FULLY COMPLETED all vaccines (all required doses)
        $completeCount = $filteredPatients->filter(function($patient) {
            return $patient->isFullyImmunized();
        })->count();
        
        // Partial: Patients with some vaccines but not all completed
        $partialCount = $filteredPatients->filter(function($patient) {
            $hasVaccines = $patient->vaccineRecords()->whereNotNull('dose_1_date')->exists();
            return $hasVaccines && !$patient->isFullyImmunized();
        })->count();
        
        // Not Started: Patients with no vaccine records at all
        $notStartedCount = $totalPatientCount - $completeCount - $partialCount;
        
        $vaccineStatusData = [$completeCount, $partialCount, $notStartedCount];
        
        // Vaccination distribution by vaccine type (BCG, HepB, Penta, OPV, IPV, PCV, Measles)
        $vaccineNames = ['BCG', 'Hepatitis B', 'Pentavalent', 'OPV', 'IPV', 'PCV', 'Measles'];
        $vaccineDistData = [];
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            $patientIds = Patient::forHealthWorker($healthWorker)->pluck('id');
            foreach ($vaccineNames as $vaccineName) {
                $count = PatientVaccineRecord::whereIn('patient_id', $patientIds)
                    ->whereHas('vaccine', function($query) use ($vaccineName) {
                        $query->where('vaccine_name', 'LIKE', '%' . $vaccineName . '%');
                    })->whereNotNull('dose_1_date')->count();
                
                $vaccineDistData[] = $count;
            }
        } else {
            foreach ($vaccineNames as $vaccineName) {
                $count = PatientVaccineRecord::whereHas('vaccine', function($query) use ($vaccineName) {
                    $query->where('vaccine_name', 'LIKE', '%' . $vaccineName . '%');
                })->whereNotNull('dose_1_date')->count();
                
                $vaccineDistData[] = $count;
            }
        }
        
        // Pass all statistics to the dashboard view
        return view('health_worker.dashboard', compact(
            'patients', 
            'totalPatients', 
            'vaccinated', 
            'feedbackCount', 
            'upcomingVaccinations',
            'monthlyData',
            'vaccineStatusData',
            'vaccineDistData'
        ));
    }

    public function show($id)
    {
        $healthWorker = $this->getHealthWorker();
        $patient = Patient::findOrFail($id);
        
        // Verify health worker can access this patient's barangay
        if ($healthWorker && !$healthWorker->canAccessBarangay($patient->barangay)) {
            abort(403, 'You do not have permission to view this patient.');
        }

        // Ensure standard vaccination records exist for this patient
        try {
            $svc = new PatientVaccinationService();
            $svc->createStandardVaccinationRecords($patient);
        } catch (\Throwable $e) {
            // Continue even if seeding fails; view will still render
        }

        $vaccinations = PatientVaccineRecord::with('vaccine')->where('patient_id', $id)->get();

        // Get all vaccines with their stock information for warnings
        $vaccineStocks = Vaccine::all()->mapWithKeys(function ($vaccine) {
            $availableDoses = max(0, (int) ($vaccine->stocks ?? 0));
            
            if ($availableDoses <= 0) {
                $status = 'out';
            } elseif ($availableDoses < 10) {
                $status = 'low';
            } elseif ($availableDoses < 50) {
                $status = 'medium';
            } else {
                $status = 'high';
            }
            
            return [
                $vaccine->id => [
                    'available_doses' => $availableDoses,
                    'status' => $status,
                    'name' => $vaccine->vaccine_name,
                ]
            ];
        });

        return view('health_worker.patient_card_tailwind', compact('patient', 'vaccinations', 'vaccineStocks'));
    }

    public function showParentVaccinationCard(Request $request, $id)
    {
        // Get the currently authenticated parent
        $parent = auth('parents')->user();

        // Find the patient and make sure it belongs to the authenticated parent
        $patient = Patient::where('id', $id)
            ->where('parent_id', $parent->id)
            ->firstOrFail(); // Get ALL fields, not just id and name

        // Load the relationships
        $patient->load([
            'latestGrowthRecord', 
            'growthRecords',
            'parent'
        ]);

        // Get vaccination records
        $vaccinations = PatientVaccineRecord::with('vaccine')
            ->where('patient_id', $id)
            ->get();

        // Determine return URL for back button - default to parent dashboard for parents
        $returnUrl = $request->query('return', route('parent.dashboard'));

        return view('parents.infantsRecord', compact('patient', 'vaccinations', 'returnUrl'));
    }

    /**
     * Show read-only patient vaccination card for health workers (from patient list)
     */
    public function showHealthWorkerPatientView(Request $request, $id)
    {
        $healthWorker = $this->getHealthWorker();
        
        // Find the patient (health worker can view any patient in their barangay)
        $patient = Patient::findOrFail($id);
        
        // Verify health worker can access this patient's barangay
        if ($healthWorker && !$healthWorker->canAccessBarangay($patient->barangay)) {
            abort(403, 'You do not have permission to view this patient.');
        }

        // Load the relationships
        $patient->load([
            'latestGrowthRecord', 
            'growthRecords',
            'parent'
        ]);

        // Get vaccination records
        $vaccinations = PatientVaccineRecord::with('vaccine')
            ->where('patient_id', $id)
            ->get();

        // Determine return URL for back button
        $returnUrl = $request->query('return', route('health_worker.patients'));

        return view('health_worker.patient_card_readonly', compact('patient', 'vaccinations', 'returnUrl'));
    }

    /**
     * Show patient list page with initial data
     */
    public function showPatientList(Request $request)
    {
        $healthWorker = $this->getHealthWorker();
        
        // Build query with efficient SQL filtering (fields are now decrypted)
        // Apply health worker's barangay filter first
        $query = Patient::forHealthWorker($healthWorker);
        
        // Apply search filter (case-insensitive)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('contact_no', 'LIKE', '%' . $searchTerm . '%');
            });
        }
        
        // Apply additional barangay filter (for RHU admin selecting specific barangay)
        // Barangay workers will only see their barangay anyway
        if ($request->filled('barangay') && $healthWorker && $healthWorker->isRHU()) {
            $query->where('barangay', $request->barangay);
        }
        
        // Get paginated results (30 per page) - ordered alphabetically by last name
        // Extract last name using SUBSTRING_INDEX to get the last word in name
        $patients = $query->orderByRaw('SUBSTRING_INDEX(name, " ", -1) ASC')->paginate(30);
        
        // Add age and formatted name to each patient (modify after pagination)
        foreach ($patients as $patient) {
            $patient->display_age = $patient->formatted_age;
            $patient->display_name = $patient->formatted_name;
        }
        
        // Keep filter values for form
        $filters = [
            'search' => $request->get('search', ''),
            'barangay' => $request->get('barangay', '')
        ];
        
        // Get accessible barangays for dropdown (RHU sees all, barangay worker sees only theirs)
        $accessibleBarangays = $healthWorker ? $healthWorker->getAccessibleBarangays() : Barangay::getActiveNames();
        
        return view('health_worker.patients', compact('patients', 'filters', 'accessibleBarangays', 'healthWorker'));
    }

    /**
     * Get paginated patients data with filtering options (AJAX endpoint)
     */
    public function getPatients(Request $request)
    {
        try {
            $healthWorker = $this->getHealthWorker();
            
            Log::info('getPatients called', [
                'search' => $request->get('search'),
                'barangay' => $request->get('barangay'),
                'page' => $request->get('page'),
                'health_worker_barangay' => $healthWorker ? $healthWorker->getAssignedBarangayName() : 'RHU'
            ]);

            // Build query with health worker's barangay filter
            $query = Patient::forHealthWorker($healthWorker);
            
            // Apply search filter (case-insensitive)
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('contact_no', 'LIKE', '%' . $searchTerm . '%');
                });
                Log::info('Applied search filter', ['term' => $searchTerm]);
            }
            
            // Apply additional barangay filter (for RHU admin selecting specific barangay)
            if ($request->filled('barangay') && $healthWorker && $healthWorker->isRHU()) {
                $query->where('barangay', $request->barangay);
                Log::info('Applied barangay filter', ['barangay' => $request->barangay]);
            }
            
            // Order by created_at with newest first
            $query->orderBy('created_at', 'desc');
            
            // Paginate results (30 per page, max 50)
            $perPage = min((int)$request->get('size', 30), 50);
            $patients = $query->paginate($perPage);
            
            Log::info('Query results', ['total' => $patients->total()]);
            
            // Transform data to include age and formatted name
            $transformedPatients = [];
            foreach ($patients->items() as $patient) {
                $patientArray = $patient->toArray();
                
                // Add formatted age (e.g., "2 months old", "3 years old")
                $patientArray['display_age'] = $patient->formatted_age;
                
                // Add formatted name
                $patientArray['display_name'] = $patient->formatted_name;
                
                $transformedPatients[] = $patientArray;
            }
            
            return response()->json([
                'patients' => $transformedPatients,
                'has_more' => $patients->hasMorePages(),
                'total' => $patients->total(),
                'current_page' => $patients->currentPage(),
                'per_page' => $patients->perPage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in patients API: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load patients',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}