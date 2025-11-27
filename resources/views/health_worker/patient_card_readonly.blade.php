<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Immunization Card</title>
    <link rel="stylesheet" href="{{ asset('css/tailwind-full.css') }}">
    <style>
        .page-wrap{ width:100%; max-width:1320px; margin:0 auto; padding:1rem; }
        @media (min-width:640px){ .page-wrap{ padding:2rem; } }
        .brand-gradient{ background: linear-gradient(135deg, #7a5bbd 0%, #5a3f99 45%, #402d73 100%); }
        .title-shadow{ text-shadow: 0 1px 0 rgba(255,255,255,.35); }
        .table-grid th, .table-grid td { border-bottom: 1px solid rgba(122, 91, 189, 0.16); }
        .table-grid td:not(:first-of-type), .table-grid th:not(:first-of-type) { border-left: 1px solid rgba(122, 91, 189, 0.22); }
        .table-grid tbody tr > td:nth-of-type(2) { text-align: center; }
        
        @media print {
            @page { size: landscape; margin: 0.2cm; }
            .no-print { display: none !important; }
            body { font-size: 7pt; }
            header.brand-gradient { background: #7a5bbd !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { background: #7a5bbd !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-gray-50">
<div class="page-wrap">
    <header class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 brand-gradient text-white">
        <div class="px-3 sm:px-6 py-4 flex items-center justify-between">
            <a href="{{ $returnUrl ?? route('health_worker.patients') }}" aria-label="Back" class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 px-2 sm:px-3 py-2 ring-1 ring-white/25 transition-transform duration-150 ease-out hover:-translate-y-[1px]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
            <div class="flex-1 flex items-center justify-center">
                <div class="w-full max-w-3xl sm:max-w-4xl">
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
            <a aria-hidden="true" tabindex="-1" class="invisible pointer-events-none inline-flex items-center gap-2 rounded-full bg-white/15 px-2 sm:px-3 py-2 ring-1 ring-white/25">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Name</label>
                <input type="text" value="{{ $patient->name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Date of Birth</label>
                <input type="date" value="{{ $patient->date_of_birth ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Place of Birth</label>
                <input type="text" value="{{ $patient->place_of_birth ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Address</label>
                <input type="text" value="{{ $patient->address ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Barangay</label>
                <input type="text" value="{{ $patient->barangay ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Mother's Name</label>
                <input type="text" value="{{ $patient->mother_name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Father's Name</label>
                <input type="text" value="{{ $patient->father_name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Birth Weight (kg)</label>
                    <input type="number" value="{{ $patient->birth_weight ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Birth Height (cm)</label>
                    <input type="number" value="{{ $patient->birth_height ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Sex</label>
                <select disabled class="block w-full h-11 rounded-md border-2 border-primary-300 bg-gray-50 px-3 text-[15px]">
                    <option value="" disabled {{ empty($patient->sex ?? '') ? 'selected' : '' }}>Select Sex</option>
                    <option value="Male" {{ ($patient->sex ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ ($patient->sex ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Contact No.</label>
                <input type="text" value="{{ $patient->contact_no ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
        </div>
    </div>

    <!-- Vaccination Table (Read-only) -->
    <div class="bg-white rounded-xl shadow-sm border-2 border-primary-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full table-grid">
                <thead>
                    <tr class="bg-primary-700 text-white">
                        <th class="p-3 md:p-4 text-left text-xs sm:text-sm font-bold uppercase tracking-wide">BAKUNA</th>
                        <th class="p-3 md:p-4 text-left text-xs sm:text-sm font-bold uppercase tracking-wide">DOSES</th>
                        <th class="p-3 md:p-4 text-center text-xs sm:text-sm font-bold uppercase tracking-wide" colspan="2">PETSA NG BAKUNA</th>
                        <th class="p-3 md:p-4 text-center text-xs sm:text-sm font-bold uppercase tracking-wide">REMARKS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @foreach(($vaccinations ?? []) as $vaccination)
                        @php
                            $vaccineName = optional($vaccination->vaccine)->vaccine_name;
                            $dosesDescription = optional($vaccination->vaccine)->doses_description;
                        @endphp
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="p-3 md:p-4 text-sm sm:text-base text-gray-900 font-medium">{{ $vaccineName ?? 'N/A' }}</td>
                            <td class="p-3 md:p-4 text-sm sm:text-base text-gray-700">{{ $dosesDescription ?? 'N/A' }}</td>
                            <td class="p-3 md:p-4 text-center" colspan="2">
                                @if(in_array($vaccineName, ['BCG', 'Hepatitis B']) || ($vaccineName === 'Measles Containing' && $dosesDescription === 'Grade 1'))
                                    <input type="date" value="{{ $vaccination->dose_1_date ?? '' }}" readonly class="w-full h-10 rounded-md border-2 border-gray-300 bg-gray-50 px-3 text-sm text-center">
                                @elseif(in_array($vaccineName, ['Inactivated Polio', 'Measles, Mumps, Rubella', 'Tetanus Diphtheria', 'Human Papillomavirus']) || ($vaccineName === 'Measles Containing' && $dosesDescription === 'Grade 7'))
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <input type="date" value="{{ $vaccination->dose_1_date ?? '' }}" readonly class="h-10 rounded-md border-2 border-gray-300 bg-gray-50 px-3 text-sm text-center">
                                        <input type="date" value="{{ $vaccination->dose_2_date ?? '' }}" readonly class="h-10 rounded-md border-2 border-gray-300 bg-gray-50 px-3 text-sm text-center">
                                    </div>
                                @elseif(in_array($vaccineName, ['Pentavalent', 'Oral Polio', 'Pneumococcal Conjugate']))
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <input type="date" value="{{ $vaccination->dose_1_date ?? '' }}" readonly class="h-10 rounded-md border-2 border-gray-300 bg-gray-50 px-3 text-sm text-center">
                                        <input type="date" value="{{ $vaccination->dose_2_date ?? '' }}" readonly class="h-10 rounded-md border-2 border-gray-300 bg-gray-50 px-3 text-sm text-center">
                                        <input type="date" value="{{ $vaccination->dose_3_date ?? '' }}" readonly class="h-10 rounded-md border-2 border-gray-300 bg-gray-50 px-3 text-sm text-center">
                                    </div>
                                @else
                                    <input type="date" value="{{ $vaccination->dose_1_date ?? '' }}" readonly class="w-full h-10 rounded-md border-2 border-gray-300 bg-gray-50 px-3 text-sm text-center">
                                @endif
                            </td>
                            <td class="p-3 md:p-4">
                                <textarea readonly class="w-full min-h-[3rem] rounded-md border-2 border-gray-300 bg-gray-50 px-3 py-2 text-sm resize-none">{{ $vaccination->remarks ?? '' }}</textarea>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Note -->
    <div class="mb-6 rounded-xl bg-gradient-to-r from-primary-600 to-primary-800 text-white px-6 py-5 text-center text-sm shadow-sm">
        Sa column ng Petsa ng bakuna, isulat ang petsa ng pagbibigay ng bakuna ayon sa kung ilang dose ito. Sa column ng remarks, isulat ang petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon na maaaring makaapekto sa pagbabakuna ng bata.
    </div>

    <!-- Print Button -->
    <div class="no-print flex justify-center mb-6">
        <button onclick="window.print()" class="inline-flex items-center gap-3 px-8 py-3.5 bg-primary-700 text-white text-base font-bold rounded-lg shadow-md hover:bg-primary-800 hover:shadow-lg active:bg-primary-900 transition-all transform hover:scale-105">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Immunization Card
        </button>
    </div>
</div>
</body>
</html>
