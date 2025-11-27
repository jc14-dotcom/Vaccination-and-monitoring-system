<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Record</title>
    <link rel="stylesheet" href="{{ asset('css/tailwind-full.css') }}">
    <style>
    .page-wrap{ width:100%; max-width:1320px; margin:0 auto; padding:1rem; }
        @media (min-width:640px){ .page-wrap{ padding:2rem; } }
        .brand-gradient{ background: linear-gradient(135deg, #7a5bbd 0%, #5a3f99 45%, #402d73 100%); }
        .title-shadow{ text-shadow: 0 1px 0 rgba(255,255,255,.35); }
        .date-input{ height: 2.75rem; } /* slightly bigger date inputs */
        .remarks-area{ min-height: 3.25rem; } /* slightly bigger remarks */
    /* Recolor the native datepicker calendar icon to purple in Chromium/WebKit */
    .date-input::-webkit-calendar-picker-indicator{ filter: invert(28%) sepia(23%) saturate(2877%) hue-rotate(224deg) brightness(88%) contrast(88%); }
    /* Firefox: use accent-color to hint calendar button color */
    .date-input{ accent-color: #6b46c1; }
    /* Faint inner borders for a crisp grid */
    .table-grid th, .table-grid td { border-bottom: 1px solid rgba(122, 91, 189, 0.16); }
    /* Use :not(:first-of-type) to survive hidden inputs between cells */
    .table-grid td:not(:first-of-type), .table-grid th:not(:first-of-type) { border-left: 1px solid rgba(122, 91, 189, 0.22); }
    /* Center-align the DOSES column (2nd TD of each row) */
    .table-grid tbody tr > td:nth-of-type(2) { text-align: center; }
    </style>
    <script>
        // Prevent FOUC while Tailwind loads in some browsers
        document.documentElement.classList.add('js');
    </script>
</head>
<body class="bg-gray-50">
<div class="page-wrap">
    <!-- Branded Header with Logos and Title -->
    <header class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 brand-gradient text-white">
        <div class="px-3 sm:px-6 py-4 flex items-center justify-between">
            <!-- Back Button (icon-only on mobile) -->
            <a href="{{ route('health_worker.patients') }}" aria-label="Back" class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 px-2 sm:px-3 py-2 ring-1 ring-white/25 transition-transform duration-150 ease-out hover:-translate-y-[1px]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
            <!-- Center Title and Logos Row -->
            <div class="flex-1 flex items-center justify-center">
                <div class="w-full max-w-3xl sm:max-w-4xl">
                    <!-- Desktop/Tablet layout -->
                    <div class="hidden sm:grid sm:grid-cols-3 sm:items-center">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/todoligtass.png') }}" alt="Left Logo" class="h-16 md:h-18 w-auto drop-shadow object-contain" loading="lazy"/>
                        </div>
                        <div class="text-center">
                            <h1 class="text-xl md:text-2xl font-bold uppercase tracking-wide title-shadow">Immunization Card</h1>
                            <p class="text-[11px] md:text-xs text-white/85">Infant Vaccination & Growth Monitoring</p>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <img src="{{ asset('images/doh-logo.png') }}" alt="DOH" class="h-14 md:h-16 w-auto drop-shadow object-contain" loading="lazy"/>
                            <img src="{{ asset('images/right.png') }}" alt="Right Logo" class="h-14 md:h-16 w-auto drop-shadow object-contain" loading="lazy"/>
                        </div>
                    </div>
                    <!-- Mobile layout: title then centered logos row -->
                    <div class="sm:hidden flex flex-col items-center">
                        <div class="text-center">
                            <h1 class="text-xl font-bold uppercase tracking-wide title-shadow">Immunization Card</h1>
                            <p class="text-[11px] text-white/85">Infant Vaccination & Growth Monitoring</p>
                        </div>
                        <div class="mt-2 flex items-center justify-center gap-3">
                            <img src="{{ asset('images/todoligtass.png') }}" alt="Left Logo" class="h-10 w-auto drop-shadow object-contain" loading="lazy"/>
                            <img src="{{ asset('images/doh-logo.png') }}" alt="DOH" class="h-10 w-auto drop-shadow object-contain" loading="lazy"/>
                            <img src="{{ asset('images/right.png') }}" alt="Right Logo" class="h-10 w-auto drop-shadow object-contain" loading="lazy"/>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Invisible back button clone to auto-balance width and keep center aligned -->
            <a aria-hidden="true" tabindex="-1" class="invisible pointer-events-none inline-flex items-center gap-2 rounded-full bg-white/15 px-2 sm:px-3 py-2 ring-1 ring-white/25">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
        </div>
    </header>

    @if(session('success'))
        <div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center">
                <p class="text-gray-800 mb-4">{{ session('success') }}</p>
                <button id="okButton" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary-700 text-white font-semibold shadow-sm ring-1 ring-primary-700/30 hover:bg-primary-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-700/50">OK!</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div id="errorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Patient Already Exists</h3>
                <p class="text-gray-700 mb-6">{{ session('error') }}</p>
                <button id="errorOkButton" class="inline-flex items-center justify-center px-6 py-2.5 rounded-md bg-red-600 text-white font-semibold shadow-sm hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600/50">OK</button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-red-800">
            <div class="font-semibold mb-2">There were some problems with your input:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('auth.saveRecord') }}" method="POST" id="editForm" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 mb-1">Name</label>
                    <input type="text" id="name" name="name" placeholder="Firstname, M.I, Lastname" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-semibold text-gray-800 mb-1">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="place_of_birth" class="block text-sm font-semibold text-gray-800 mb-1">Place of Birth</label>
                    <input type="text" id="place_of_birth" name="place_of_birth" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-800 mb-1">Address</label>
                    <input type="text" id="address" name="address" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="barangay" class="block text-sm font-semibold text-gray-800 mb-1">Barangay</label>
                    <select id="barangay" name="barangay" required class="block w-full h-11 rounded-md border-2 border-primary-300 bg-white px-3 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20" @if(isset($healthWorker) && $healthWorker && !$healthWorker->isRHU() && count($accessibleBarangays ?? []) == 1) disabled @endif>
                        @if(isset($healthWorker) && $healthWorker && !$healthWorker->isRHU() && count($accessibleBarangays ?? []) == 1)
                            {{-- Barangay worker - only their assigned barangay, pre-selected --}}
                            <option value="{{ $accessibleBarangays[0] }}" selected>{{ $accessibleBarangays[0] }}</option>
                        @else
                            {{-- RHU admin - show all barangays --}}
                            <option value="" disabled selected>Select Barangay</option>
                            @foreach($accessibleBarangays ?? [] as $barangay)
                                <option value="{{ $barangay }}">{{ $barangay }}</option>
                            @endforeach
                        @endif
                    </select>
                    @if(isset($healthWorker) && $healthWorker && !$healthWorker->isRHU() && count($accessibleBarangays ?? []) == 1)
                        {{-- Hidden input to submit the value when select is disabled --}}
                        <input type="hidden" name="barangay" value="{{ $accessibleBarangays[0] }}">
                    @endif
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <div>
                    <label for="mothers_name" class="block text-sm font-semibold text-gray-800 mb-1">Mother's Name</label>
                    <input type="text" id="mothers_name" name="mothers_name" placeholder="Firstname, M.I, Lastname" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="fathers_name" class="block text-sm font-semibold text-gray-800 mb-1">Father's Name</label>
                    <input type="text" id="fathers_name" name="fathers_name" placeholder="Firstname, M.I, Lastname" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="birth-weight" class="block text-sm font-semibold text-gray-800 mb-1">Birth Weight (kg)</label>
                        <input type="number" id="birth-weight" name="birth_weight" step="0.01" placeholder="e.g., 3.5" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                    </div>
                    <div>
                        <label for="birth-height" class="block text-sm font-semibold text-gray-800 mb-1">Birth Height (cm)</label>
                        <input type="number" id="birth-height" name="birth_height" step="0.1" placeholder="e.g., 50" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                    </div>
                </div>
                <div>
                    <label for="sex" class="block text-sm font-semibold text-gray-800 mb-1">Sex</label>
                    <select id="sex" name="sex" required class="block w-full h-11 rounded-md border-2 border-primary-300 bg-white px-3 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                        <option value="" disabled selected>Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div>
                    <label for="contact_no" class="block text-sm font-semibold text-gray-800 mb-1">Contact No.</label>
                    <input type="text" id="contact_no" name="contact_no" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
            </div>
        </div>

        <!-- Vaccine Table -->
    <div class="w-full overflow-x-auto rounded-2xl shadow-sm ring-1 ring-primary-200 bg-white">
            <table class="min-w-full text-[15px] table-grid">
                <colgroup>
                    <col class="w-[26%]">
                    <col class="w-[16%]">
                    <col class="w-[31%]">
                    <col class="w-[5%]">
                    <col class="w-[22%]">
                </colgroup>
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white first:rounded-tl-2xl">BAKUNA</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white">DOSES</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white" colspan="2">PETSA NG BAKUNA</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white last:rounded-tr-2xl">REMARKS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">BCG Vaccine</td>
                        <td class="p-3 md:p-4">Pagkapanganak</td>
                        <input type="hidden" name="vaccines[0][vaccine_name]" value="BCG">
                        <input type="hidden" name="vaccines[0][doses_description]" value="Pagkapanganak">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <input type="date" name="vaccines[0][dose_1_date]" class="date-input w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[0][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Hepatitis B Vaccine</td>
                        <td class="p-3 md:p-4">Pagkapanganak</td>
                        <input type="hidden" name="vaccines[1][vaccine_name]" value="Hepatitis B">
                        <input type="hidden" name="vaccines[1][doses_description]" value="Pagkapanganak">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <input type="date" name="vaccines[1][dose_1_date]" class="date-input w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[1][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Pentavalent Vaccine</td>
                        <td class="p-3 md:p-4">1 1/2, 2 1/2, 3 1/2 Buwan</td>
                        <input type="hidden" name="vaccines[2][vaccine_name]" value="Pentavalent">
                        <input type="hidden" name="vaccines[2][doses_description]" value="1 1/2, 2 1/2, 3 1/2 Buwan">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <input type="date" name="vaccines[2][dose_1_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[2][dose_2_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[2][dose_3_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[2][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Oral Polio Vaccine</td>
                        <td class="p-3 md:p-4">1 1/2, 2 1/2, 3 1/2 Buwan</td>
                        <input type="hidden" name="vaccines[3][vaccine_name]" value="Oral Polio">
                        <input type="hidden" name="vaccines[3][doses_description]" value="1 1/2, 2 1/2, 3 1/2 Buwan">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <input type="date" name="vaccines[3][dose_1_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[3][dose_2_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[3][dose_3_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[3][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Inactivated Polio Vaccine</td>
                        <td class="p-3 md:p-4">3 1/2 & 9 Buwan</td>
                        <input type="hidden" name="vaccines[4][vaccine_name]" value="Inactivated Polio">
                        <input type="hidden" name="vaccines[4][doses_description]" value="3 1/2 & 9 Buwan">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" name="vaccines[4][dose_1_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[4][dose_2_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[4][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Pneumococcal Conjugate Vaccine</td>
                        <td class="p-3 md:p-4">1 1/2, 2 1/2, 3 1/2 Buwan</td>
                        <input type="hidden" name="vaccines[5][vaccine_name]" value="Pneumococcal Conjugate">
                        <input type="hidden" name="vaccines[5][doses_description]" value="1 1/2, 2 1/2, 3 1/2 Buwan">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <input type="date" name="vaccines[5][dose_1_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[5][dose_2_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[5][dose_3_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[5][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Measles, Mumps, Rubella Vaccine</td>
                        <td class="p-3 md:p-4">9 Buwan & 1 Taon</td>
                        <input type="hidden" name="vaccines[6][vaccine_name]" value="Measles, Mumps, Rubella">
                        <input type="hidden" name="vaccines[6][doses_description]" value="9 Buwan & 1 Taon">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" name="vaccines[6][dose_1_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[6][dose_2_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[6][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="p-3 md:p-4 font-semibold text-primary-800 bg-primary-50 text-left">SCHOOL AGED CHILDREN</td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Measles Containing Vaccine</td>
                        <td class="p-3 md:p-4">Grade 1</td>
                        <input type="hidden" name="vaccines[7][vaccine_name]" value="Measles Containing">
                        <input type="hidden" name="vaccines[7][doses_description]" value="Grade 1">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <input type="date" name="vaccines[7][dose_1_date]" class="date-input w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[7][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Measles Containing Vaccine</td>
                        <td class="p-3 md:p-4">Grade 7</td>
                        <input type="hidden" name="vaccines[8][vaccine_name]" value="Measles Containing">
                        <input type="hidden" name="vaccines[8][doses_description]" value="Grade 7">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" name="vaccines[8][dose_1_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[8][dose_2_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[8][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Tetanus Diphtheria</td>
                        <td class="p-3 md:p-4">Grade 1 & 7</td>
                        <input type="hidden" name="vaccines[9][vaccine_name]" value="Tetanus Diphtheria">
                        <input type="hidden" name="vaccines[9][doses_description]" value="Grade 1 & 7">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" name="vaccines[9][dose_1_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[9][dose_2_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[9][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Human Papillomavirus Vaccine</td>
                        <td class="p-3 md:p-4">Grade 4 - (Babae) 9-14 Taon Gulang</td>
                        <input type="hidden" name="vaccines[10][vaccine_name]" value="Human Papillomavirus">
                        <input type="hidden" name="vaccines[10][doses_description]" value="Grade 4 - (Babae) 9-14 Taon Gulang">
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" name="vaccines[10][dose_1_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                <input type="date" name="vaccines[10][dose_2_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea name="vaccines[10][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-8">
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl brand-gradient text-white text-sm font-semibold px-8 py-3 shadow-md ring-1 ring-white/10 hover:opacity-[.97] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30 transition-transform duration-150 ease-out hover:-translate-y-[1px] active:translate-y-0">
                Save
            </button>
        </div>

        <div class="mt-8 rounded-xl brand-gradient text-white px-6 py-5 text-center text-[13px] shadow-sm ring-1 ring-white/10">
            Sa column ng Petsa ng bakuna, isulat ang petsa ng pagbibigay ng bakuna ayon sa kung ilang dose ito. Sa column ng remarks, isulat ang petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon na maaaring makaapekto sa pagbabakuna ng bata.
        </div>
    </form>
</div>
<script src="{{ asset('javascript/vaccination_form.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handle success modal
    const successModal = document.getElementById('successModal');
    const okButton = document.getElementById('okButton');
    if (successModal && okButton) {
        okButton.addEventListener('click', function () {
            successModal.style.display = 'none';
            window.location.href = "{{ route('health_worker.patients') }}";
        });
    }

    // Handle error modal - auto-show on page load
    const errorModal = document.getElementById('errorModal');
    const errorOkButton = document.getElementById('errorOkButton');
    if (errorModal && errorOkButton) {
        // Show error modal on page load if error exists
        errorModal.style.display = 'flex';
        
        errorOkButton.addEventListener('click', function () {
            errorModal.style.display = 'none';
        });
    }
});
</script>
<br/>
</div>
</body>
</html>