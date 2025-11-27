<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Vaccine Monitoring</title>
    <!-- Offline Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('css/tailwind-full.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/infantsRecord.css') }}?v={{ time() }}">
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .animate-slide-in-right {
            animation: slideInRight 0.3s ease-out forwards;
        }
        
        .animate-slide-out-right {
            animation: slideOutRight 0.3s ease-in forwards;
        }
        
        /* Mobile responsive fixes */
        @media (max-width: 640px) {
            .dose-box {
                padding: 0.375rem;
                font-size: 0.65rem;
            }
            .dose-box .font-semibold {
                font-size: 0.6rem;
            }
            .dose-box .text-xs {
                font-size: 0.55rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    @php
        use App\Models\VaccinationSchedule;
        use App\Models\Feedback;
        use Carbon\Carbon;
        
        $parent = auth('parents')->user();
        $eligibleSchedule = null;
        $canEvaluate = false;
        $statusMessage = '';
        $hoursRemaining = 0;
        
        if ($parent && $parent->barangay) {
            // Find the most recent vaccination schedule for parent's barangay
            $recentSchedule = VaccinationSchedule::where('barangay', $parent->barangay)
                ->where('vaccination_date', '<=', Carbon::now())
                ->orderBy('vaccination_date', 'desc')
                ->first();
            
            if ($recentSchedule) {
                $vaccinationDate = Carbon::parse($recentSchedule->vaccination_date);
                $hoursElapsed = Carbon::now()->diffInHours($vaccinationDate);
                $hoursRemaining = max(0, 24 - $hoursElapsed);
                
                // Check if within 24-hour window
                if ($hoursElapsed <= 24) {
                    // Check if already submitted feedback
                    $existingFeedback = Feedback::where('parent_id', $parent->id)
                        ->where('vaccination_schedule_id', $recentSchedule->id)
                        ->first();
                    
                    if (!$existingFeedback) {
                        $eligibleSchedule = $recentSchedule;
                        $canEvaluate = true;
                        $statusMessage = "Evaluation available (expires in " . ceil($hoursRemaining) . " hours)";
                    } else {
                        $statusMessage = "Already submitted feedback for this vaccination schedule";
                    }
                } else {
                    $statusMessage = "24-hour evaluation window expired";
                }
            } else {
                $statusMessage = "No recent vaccination schedule for your barangay";
            }
        } else {
            $statusMessage = "Please update your profile with your barangay";
        }
    @endphp
    
    <!-- Back button -->
    <a href="{{ $returnUrl ?? route('parent.dashboard') }}" class="fixed top-3 left-3 w-9 h-9 sm:w-8 sm:h-8 bg-purple-600 rounded-full flex items-center justify-center shadow-md hover:bg-purple-700 transition-transform hover:scale-110 z-10">
        <img src="{{ asset('images/arrow.png') }}" alt="Back" class="w-4 h-4 brightness-0 invert">
    </a>

    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6" style="max-width: 1200px;">
        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
            <!-- Patient Information Sidebar -->
            <div class="lg:w-1/3">
                <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-lg p-4 sm:p-6 text-white shadow-lg">
                    <div class="text-center mb-4 sm:mb-6">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/20 rounded-full mx-auto mb-2 sm:mb-3 flex items-center justify-center">
                            <span class="text-xl sm:text-2xl">👶</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold">{{ $patient->name ?? 'Patient Name' }}</h2>
                    </div>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div class="bg-white/10 rounded-lg p-2.5 sm:p-3">
                            <div class="text-xs uppercase opacity-75 mb-1.5 sm:mb-2">Personal Information</div>
                            <div class="grid grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm">
                                <div>
                                    <span class="opacity-75">Sex:</span> 
                                    <span class="font-semibold">{{ $patient->sex ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="opacity-75">Age:</span> 
                                    <span class="font-semibold">{{ $patient->formatted_age }}</span>
                                </div>
                            </div>
                            <div class="mt-1.5 sm:mt-2 text-xs sm:text-sm">
                                <span class="opacity-75">Date of Birth:</span><br>
                                <span class="font-semibold">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('M d, Y') : 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="bg-white/10 rounded-lg p-2.5 sm:p-3">
                            <div class="text-xs uppercase opacity-75 mb-1.5 sm:mb-2">Birth Measurements</div>
                            <div class="grid grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm">
                                <div>
                                    <span class="opacity-75">Weight:</span><br>
                                    <span class="font-semibold">{{ $patient->birth_weight ?? 'N/A' }} kg</span>
                                </div>
                                <div>
                                    <span class="opacity-75">Height:</span><br>
                                    <span class="font-semibold">{{ $patient->birth_height ?? 'N/A' }} cm</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 rounded-lg p-2.5 sm:p-3">
                            <div class="text-xs uppercase opacity-75 mb-1.5 sm:mb-2">Current Measurements</div>
                            <div class="grid grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm">
                                <div>
                                    <span class="opacity-75">Weight:</span><br>
                                    <span class="font-semibold">
                                        @if($patient->latestGrowthRecord)
                                            {{ $patient->latestGrowthRecord->weight ?? 'N/A' }} kg
                                        @else
                                            {{ $patient->birth_weight ?? 'N/A' }} kg
                                        @endif
                                    </span>
                                    <div class="text-[10px] sm:text-xs opacity-75 mt-0.5 sm:mt-1">
                                        @if($patient->latestGrowthRecord)
                                            as of {{ \Carbon\Carbon::parse($patient->latestGrowthRecord->recorded_date)->format('M d, Y') }}
                                        @else
                                            (birth)
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="opacity-75">Height:</span><br>
                                    <span class="font-semibold">
                                        @if($patient->latestGrowthRecord)
                                            {{ $patient->latestGrowthRecord->height ?? 'N/A' }} cm
                                        @else
                                            {{ $patient->birth_height ?? 'N/A' }} cm
                                        @endif
                                    </span>
                                    <div class="text-[10px] sm:text-xs opacity-75 mt-0.5 sm:mt-1">
                                        @if($patient->latestGrowthRecord)
                                            as of {{ \Carbon\Carbon::parse($patient->latestGrowthRecord->recorded_date)->format('M d, Y') }}
                                        @else
                                            (birth)
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <button id="growthHistoryBtn" class="w-full mt-2 sm:mt-3 bg-white/20 hover:bg-white/30 rounded-lg py-1.5 sm:py-2 px-3 text-xs sm:text-sm transition">
                                📊 View Growth History
                            </button>
                        </div>
                    </div>
                </div>
                                <!-- Evaluation Button -->
                <div class="mt-4 sm:mt-6 flex flex-col items-end">
                    @if($canEvaluate)
                        <button id="evaluationBtn" class="bg-purple-600 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg hover:bg-purple-700 transition text-sm sm:text-base">
                            Evaluate Service
                        </button>
                        <p class="text-[10px] sm:text-xs text-green-600 mt-1 text-right">{{ $statusMessage }}</p>
                    @else
                        <button disabled class="bg-gray-400 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg cursor-not-allowed text-sm sm:text-base">
                            Evaluate Service
                        </button>
                        <p class="text-[10px] sm:text-xs text-gray-600 mt-1 text-right">{{ $statusMessage }}</p>
                    @endif
                </div>
            </div>

            <!-- Vaccination Table -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-purple-600 text-white p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-semibold">Vaccination Record</h3>
                    </div>
                    
                    <!-- Vaccination Table Section with horizontal scroll -->
                    <div class="overflow-x-auto -mx-0">
                        <table class="vaccine-table w-full border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-purple-600 text-white">
                                <th class="p-1.5 sm:p-2 text-left text-xs sm:text-sm font-semibold whitespace-nowrap">Bakuna</th>
                                <th class="p-1.5 sm:p-2 text-left text-xs sm:text-sm font-semibold whitespace-nowrap">Doses Description</th>
                                <th class="p-1.5 sm:p-2 text-left text-xs sm:text-sm font-semibold whitespace-nowrap" colspan="3">Petsa ng Bakuna</th>
                                <th class="p-1.5 sm:p-2 text-left text-xs sm:text-sm font-semibold whitespace-nowrap">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($vaccinations) && $vaccinations->isNotEmpty())
                                @foreach ($vaccinations as $vaccination)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-2 text-sm font-bold">
                                            @if($vaccination->vaccine->vaccine_name === 'BCG')
                                                <span title="Bacillus Calmette-Guérin">{{ $vaccination->vaccine->vaccine_name ?? 'N/A' }}</span>
                                            @else
                                                {{ $vaccination->vaccine->vaccine_name ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td class="p-2 text-sm">{{ $vaccination->vaccine->doses_description ?? 'N/A' }}</td>
                                        
                    @php
                        // Determine number of doses based on vaccine name and description
                        $totalDoses = 1; // Default
                        if(in_array($vaccination->vaccine->vaccine_name, ['Inactivated Polio', 'Measles, Mumps, Rubella', 'Tetanus Diphtheria', 'Human Papillomavirus'])) {
                            $totalDoses = 2;
                        } elseif($vaccination->vaccine->vaccine_name === 'Measles Containing' && $vaccination->vaccine->doses_description === 'Grade 7') {
                            $totalDoses = 2; // Grade 7 Measles has 2 doses
                        } elseif(in_array($vaccination->vaccine->vaccine_name, ['Pentavalent', 'Oral Polio', 'Pneumococcal Conjugate'])) {
                            $totalDoses = 3;
                        }
                        // Get dose dates
                        $dose1 = $vaccination->dose_1_date;
                        $dose2 = $vaccination->dose_2_date;
                        $dose3 = $vaccination->dose_3_date;
                        
                        // Calculate patient age in days for overdue logic
                        $patientAge = \Carbon\Carbon::parse($patient->date_of_birth)->diffInDays(now());
                        
                        // Helper function logic inline to avoid redeclaration
                        $getDoseStatus = function($doseDate, $doseNumber, $totalDoses, $patientAge, $prevDoseDate = null) {
                            if ($doseDate) {
                                return ['status' => 'completed', 'color' => 'bg-green-100 text-green-800 border-green-300'];
                            }
                            
                            // If previous dose is not completed, this dose is pending
                            if ($prevDoseDate === null && $doseNumber > 1) {
                                return ['status' => 'pending', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-300'];
                            }
                            
                            // Basic overdue logic - simplified for demo
                            // You can enhance this with actual vaccine schedules
                            $isOverdue = false;
                            if ($doseNumber == 1 && $patientAge > 60) { // 2 months
                                $isOverdue = true;
                            } elseif ($doseNumber == 2 && $patientAge > 120 && $prevDoseDate) { // 4 months
                                $isOverdue = true;
                            } elseif ($doseNumber == 3 && $patientAge > 180 && $prevDoseDate) { // 6 months
                                $isOverdue = true;
                            }
                            
                            if ($isOverdue) {
                                return ['status' => 'overdue', 'color' => 'bg-red-100 text-red-800 border-red-300'];
                            }
                            
                            return ['status' => 'pending', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-300'];
                        };
                    @endphp

                    @if($totalDoses == 1)
                        <!-- Single dose vaccines -->
                        @php $dose1Status = $getDoseStatus($dose1, 1, $totalDoses, $patientAge); @endphp
                        <td class="p-2 text-sm" colspan="3">
                            <div class="dose-box p-2 rounded border-2 {{ $dose1Status['color'] }} text-center">
                                @if($dose1)
                                    <div class="font-semibold">{{ \Carbon\Carbon::parse($dose1)->format('M d, Y') }}</div>
                                    <div class="text-xs">✓ Completed</div>
                                @else
                                    <div class="font-semibold">Not Given</div>
                                    <div class="text-xs">
                                        @if($dose1Status['status'] == 'overdue')
                                            ⚠ Overdue
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                    @elseif($totalDoses == 2)
                        <!-- Two dose vaccines -->
                        @php 
                            $dose1Status = $getDoseStatus($dose1, 1, $totalDoses, $patientAge);
                            $dose2Status = $getDoseStatus($dose2, 2, $totalDoses, $patientAge, $dose1);
                        @endphp
                        <td class="p-2 text-sm" colspan="2">
                            <div class="dose-box p-2 rounded border-2 {{ $dose1Status['color'] }} text-center">
                                @if($dose1)
                                    <div class="font-semibold">{{ \Carbon\Carbon::parse($dose1)->format('M d, Y') }}</div>
                                    <div class="text-xs">✓ Dose 1</div>
                                @else
                                    <div class="font-semibold">Dose 1</div>
                                    <div class="text-xs">
                                        @if($dose1Status['status'] == 'overdue')
                                            ⚠ Overdue
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="p-2 text-sm">
                            <div class="dose-box p-2 rounded border-2 {{ $dose2Status['color'] }} text-center">
                                @if($dose2)
                                    <div class="font-semibold">{{ \Carbon\Carbon::parse($dose2)->format('M d, Y') }}</div>
                                    <div class="text-xs">✓ Dose 2</div>
                                @else
                                    <div class="font-semibold">Dose 2</div>
                                    <div class="text-xs">
                                        @if($dose2Status['status'] == 'overdue')
                                            ⚠ Overdue
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                    @elseif($totalDoses == 3)
                        <!-- Three dose vaccines -->
                        @php 
                            $dose1Status = $getDoseStatus($dose1, 1, $totalDoses, $patientAge);
                            $dose2Status = $getDoseStatus($dose2, 2, $totalDoses, $patientAge, $dose1);
                            $dose3Status = $getDoseStatus($dose3, 3, $totalDoses, $patientAge, $dose2);
                        @endphp
                        <td class="p-2 text-sm">
                            <div class="dose-box p-2 rounded border-2 {{ $dose1Status['color'] }} text-center">
                                @if($dose1)
                                    <div class="font-semibold">{{ \Carbon\Carbon::parse($dose1)->format('M d, Y') }}</div>
                                    <div class="text-xs">✓ Dose 1</div>
                                @else
                                    <div class="font-semibold">Dose 1</div>
                                    <div class="text-xs">
                                        @if($dose1Status['status'] == 'overdue')
                                            ⚠ Overdue
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="p-2 text-sm">
                            <div class="dose-box p-2 rounded border-2 {{ $dose2Status['color'] }} text-center">
                                @if($dose2)
                                    <div class="font-semibold">{{ \Carbon\Carbon::parse($dose2)->format('M d, Y') }}</div>
                                    <div class="text-xs">✓ Dose 2</div>
                                @else
                                    <div class="font-semibold">Dose 2</div>
                                    <div class="text-xs">
                                        @if($dose2Status['status'] == 'overdue')
                                            ⚠ Overdue
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="p-2 text-sm">
                            <div class="dose-box p-2 rounded border-2 {{ $dose3Status['color'] }} text-center">
                                @if($dose3)
                                    <div class="font-semibold">{{ \Carbon\Carbon::parse($dose3)->format('M d, Y') }}</div>
                                    <div class="text-xs">✓ Dose 3</div>
                                @else
                                    <div class="font-semibold">Dose 3</div>
                                    <div class="text-xs">
                                        @if($dose3Status['status'] == 'overdue')
                                            ⚠ Overdue
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                    @else
                        <!-- Default for other vaccines -->
                        @php $dose1Status = $getDoseStatus($dose1, 1, $totalDoses, $patientAge); @endphp
                        <td class="p-2 text-sm" colspan="3">
                            <div class="dose-box p-2 rounded border-2 {{ $dose1Status['color'] }} text-center">
                                @if($dose1)
                                    <div class="font-semibold">{{ \Carbon\Carbon::parse($dose1)->format('M d, Y') }}</div>
                                    <div class="text-xs">✓ Completed</div>
                                @else
                                    <div class="font-semibold">Not Given</div>
                                    <div class="text-xs">
                                        @if($dose1Status['status'] == 'overdue')
                                            ⚠ Overdue
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                    @endif                                <td class="p-2 text-sm">{{ $vaccination->remarks ?? 'N/A' }}</td>
                                    </tr>

                                    @if($vaccination->vaccine->vaccine_name === 'Measles, Mumps, Rubella')
                                        <tr class="school-aged bg-gray-100">
                                            <td colspan="6" class="p-2 text-sm text-center font-semibold">SCHOOL-AGED CHILDREN</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="p-2 text-sm text-center text-gray-600">No vaccination records available for this patient.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    </div> <!-- Close overflow-x-auto container -->
                </div>
            </div>
        </div>

        <!-- Evaluation Modal -->
        <div id="evaluationModal" class="modal hidden fixed inset-0 z-50 bg-black/40 overflow-y-auto">
            <div class="modal-content bg-white mx-auto my-4 p-4 sm:p-5 border border-gray-300 rounded-lg shadow-lg w-[95%] sm:w-[90%] max-w-2xl max-h-[95vh] overflow-y-auto -translate-y-16">
                <span class="close float-right text-2xl cursor-pointer ">×</span>
                <h2 class="text-lg text-black sm:text-xl font-bold mb-4">Vaccination Service Evaluation</h2>
                
                <form id="evaluationForm" method="POST" action="{{ route('feedback.store') }}">
                    @csrf
                    <input type="hidden" name="vaccination_schedule_id" value="{{ $eligibleSchedule ? $eligibleSchedule->id : '' }}">
                    
                    <table class="evaluation-table w-full border-collapse bg-white mb-4">
                        <thead>
                            <tr class="bg-purple-600 text-white">
                                <th class="p-2 sm:p-3 text-left text-sm font-semibold w-[60%]">Evaluation Criteria</th>
                                <th class="p-2 sm:p-3 text-center text-sm font-semibold w-[20%]">OO</th>
                                <th class="p-2 sm:p-3 text-center text-sm font-semibold w-[20%]">HINDI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">1. MADALI NINYO NATUNTON ANG TANGGAPANG PUPUNTAHAN NIYO?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question1" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question1" value="HINDI"></td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">2. NAKAKITA BA KAYO NG KARATULA NG DIREKSYON PATUNGO DITO?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question2" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question2" value="HINDI"></td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">3. MALINIS AT MAAYOS BA ANG TANGGAPANG ITO?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question3" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question3" value="HINDI"></td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">4. NAPAKAHABA BA NG PILA NG MGA KOSTUMER SA NATURANG TANGGAPAN?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question4" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question4" value="HINDI"></td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="submit" class="submit-btn bg-purple-600 text-white w-full py-2 rounded-md text-sm hover:bg-purple-700 transition">Submit Evaluation</button>
                </form>
            </div>
        </div>

        <!-- Growth History Modal -->
        <div id="growthHistoryModal" class="modal hidden fixed inset-0 z-50 bg-black/40 overflow-y-auto">
            <div class="modal-content bg-white mx-auto my-4 p-4 sm:p-5 border border-gray-300 rounded-lg shadow-lg w-[95%] sm:w-[90%] max-w-4xl max-h-[95vh] overflow-y-auto">
                <span class="closeGrowth float-right text-2xl cursor-pointer">&times;</span>
                <h2 class="text-lg text-black sm:text-xl font-bold mb-4">📊 Growth History</h2>
                
                @if($patient->growthRecords && $patient->growthRecords->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse bg-white mb-4">
                            <thead>
                                <tr class="bg-purple-600 text-white">
                                    <th class="p-3 text-left text-sm font-semibold">Date</th>
                                    <th class="p-3 text-left text-sm font-semibold">Height (cm)</th>
                                    <th class="p-3 text-left text-sm font-semibold">Weight (kg)</th>
                                    <th class="p-3 text-left text-sm font-semibold">Type</th>
                                    <th class="p-3 text-left text-sm font-semibold">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patient->growthRecords as $record)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-3 text-sm">{{ \Carbon\Carbon::parse($record->recorded_date)->format('M d, Y') }}</td>
                                        <td class="p-3 text-sm font-semibold text-blue-600">{{ $record->height }}</td>
                                        <td class="p-3 text-sm font-semibold text-green-600">{{ $record->weight }}</td>
                                        <td class="p-3 text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                                                {{ ucfirst(str_replace('_', ' ', $record->measurement_type)) }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-sm">{{ $record->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                                
                                <!-- Birth Record -->
                                <tr class="border-b border-gray-200 bg-yellow-50">
                                    <td class="p-3 text-sm">{{ \Carbon\Carbon::parse($patient->date_of_birth)->format('M d, Y') }}</td>
                                    <td class="p-3 text-sm font-semibold text-blue-600">{{ $patient->birth_height }}</td>
                                    <td class="p-3 text-sm font-semibold text-green-600">{{ $patient->birth_weight }}</td>
                                    <td class="p-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Birth</span>
                                    </td>
                                    <td class="p-3 text-sm">Initial measurements at birth</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-6xl mb-4">📊</div>
                        <p class="text-gray-600 mb-2">No growth history available yet.</p>
                        <p class="text-sm text-gray-500">Only birth measurements are recorded.</p>
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm"><strong>Birth Height:</strong> {{ $patient->birth_height }} cm</p>
                            <p class="text-sm"><strong>Birth Weight:</strong> {{ $patient->birth_weight }} kg</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <script>
            const modal = document.getElementById("evaluationModal");
            const btn = document.getElementById("evaluationBtn");
            const span = document.getElementsByClassName("close")[0];
            const form = document.getElementById("evaluationForm");

            // Only attach event listener if button exists and is not disabled
            if (btn) {
                btn.onclick = function() {
                    modal.style.display = "block";
                }
            }

            if (span) {
                span.onclick = function() {
                    modal.style.display = "none";
                }
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                
                const jsonData = {
                    vaccination_schedule_id: formData.get('vaccination_schedule_id'),
                    content: JSON.stringify({
                        question1: formData.get('question1'),
                        question2: formData.get('question2'),
                        question3: formData.get('question3'),
                        question4: formData.get('question4'),
                    })
                };

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(jsonData),
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.error || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Close modal and reset form
                    modal.style.display = "none";
                    form.reset();
                    
                    // Show enhanced success message
                    showSuccessNotification();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorNotification();
                });
            });

            // Enhanced Success Notification
            function showSuccessNotification() {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 z-[60] animate-slide-in-right';
                notification.innerHTML = `
                    <div class="bg-white rounded-lg shadow-2xl border-l-4 border-green-500 p-5 max-w-md">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Evaluation Submitted!</h3>
                                <p class="text-sm text-gray-600 mb-3">Thank you for taking the time to evaluate our vaccination service. Your feedback helps us improve!</p>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Just now</span>
                                </div>
                            </div>
                            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    notification.classList.add('animate-slide-out-right');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }

            // Enhanced Error Notification
            function showErrorNotification() {
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 z-[60] animate-slide-in-right';
                notification.innerHTML = `
                    <div class="bg-white rounded-lg shadow-2xl border-l-4 border-red-500 p-5 max-w-md">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Submission Failed</h3>
                                <p class="text-sm text-gray-600">An error occurred while submitting your evaluation. Please try again.</p>
                            </div>
                            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('animate-slide-out-right');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }

            // Growth History Modal
            const growthModal = document.getElementById("growthHistoryModal");
            const growthBtn = document.getElementById("growthHistoryBtn");
            const growthSpan = document.getElementsByClassName("closeGrowth")[0];

            if (growthBtn) {
                growthBtn.onclick = function() {
                    growthModal.style.display = "block";
                }
            }

            if (growthSpan) {
                growthSpan.onclick = function() {
                    growthModal.style.display = "none";
                }
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
                if (event.target == growthModal) {
                    growthModal.style.display = "none";
                }
            }
        </script>
    </div>
</body>
</html>