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
        .date-input{ height: 2.5rem; }
        .remarks-area{ min-height: 3rem; }
        .date-input::-webkit-calendar-picker-indicator{ filter: invert(28%) sepia(23%) saturate(2877%) hue-rotate(224deg) brightness(88%) contrast(88%); }
        .date-input{ accent-color: #6b46c1; }
        .table-grid th, .table-grid td { border-bottom: 1px solid rgba(122, 91, 189, 0.16); }
        .table-grid td:not(:first-of-type), .table-grid th:not(:first-of-type) { border-left: 1px solid rgba(122, 91, 189, 0.22); }
        .table-grid tbody tr > td:nth-of-type(2) { text-align: center; }
        
        /* Modal animations */
        #errorModal { transition: opacity 0.3s ease; }
        
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
            <a href="{{ $returnUrl ?? route('health_worker.vaccination_status') }}" aria-label="Back" class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 px-2 sm:px-3 py-2 ring-1 ring-white/25 transition-transform duration-150 ease-out hover:-translate-y-[1px]">
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
                <input type="date" id="date_of_birth" value="{{ $patient->date_of_birth ?? '' }}" readonly class="date-input block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
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

    @if (session('error'))
        <!-- Error Modal Backdrop -->
        <div id="errorModal" class="no-print fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
                <!-- Modal Header -->
                <div class="bg-red-600 rounded-t-2xl px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Vaccination Failed</h3>
                    </div>
                    <button onclick="closeErrorModal()" class="text-white hover:text-red-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="px-6 py-5">
                    <p class="text-gray-800 font-semibold mb-3">{{ session('error') }}</p>
                    @if (session('stock_errors'))
                        <ul class="space-y-2 mb-4">
                            @foreach(session('stock_errors') as $stockError)
                                <li class="flex items-start gap-2 text-sm text-gray-700 bg-red-50 rounded-lg px-3 py-2">
                                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ $stockError }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg px-4 py-3 mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm text-blue-800">Please update the inventory before administering these vaccines. You can access the inventory from the dashboard menu.</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Modal Footer -->
                @if (session('stock_errors'))
                    <div class="bg-gray-50 rounded-b-2xl px-6 py-4 flex justify-end">
                        <a href="{{ route('health_worker.inventory') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            Go to Inventory
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if (session('success'))
        <!-- Success Modal Backdrop -->
        <div id="successModal" class="no-print fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
                <!-- Modal Header -->
                <div class="bg-green-600 rounded-t-2xl px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Success!</h3>
                    </div>
                    <button onclick="closeSuccessModal()" class="text-white hover:text-green-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="px-6 py-5 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-gray-800 font-semibold text-lg mb-2">{{ session('success') }}</p>
                    <p class="text-sm text-gray-600">The vaccination records and inventory have been updated.</p>
                </div>
                
            </div>
        </div>
    @endif

    <!-- Backdate Detection Modal -->
    <div id="backdateModal" class="no-print hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all">
            <!-- Modal Header -->
            <div class="relative overflow-hidden rounded-t-2xl px-6 py-5" style="background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);">
                <div class="relative flex items-center gap-3">
                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7" style="color: #f97316;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Backdated Vaccination Entry</h3>
                        <p class="text-sm text-white mt-0.5" style="text-shadow: 0 1px 2px rgba(0,0,0,0.15);">Please specify the vaccination location</p>
                    </div>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="px-6 py-6">
                <div class="bg-orange-50 border-l-4 border-orange-500 rounded-r-lg px-4 py-3 mb-5">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-orange-800">You entered a vaccination date in the past:</p>
                            <p id="backdateInfo" class="text-base font-bold text-orange-900 mt-1"></p>
                        </div>
                    </div>
                </div>
                
                <p class="text-gray-700 font-medium mb-4">Where was this vaccine administered?</p>
                
                <div class="space-y-3">
                    <label class="group flex items-start gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary-400 hover:bg-primary-50/50 transition-all duration-200 hover:shadow-md">
                        <input type="radio" name="backdateType" value="calauan" class="mt-1 w-5 h-5 text-primary-600 focus:ring-primary-500 focus:ring-2">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1.5">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="font-bold text-gray-900">Calauan Health Center</div>
                            </div>
                            <div class="text-sm text-gray-600 space-y-0.5 ml-7">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    <span>Delayed entry - vaccination happened in Calauan</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                    <span><strong class="text-green-700">Will be included</strong> in Calauan reports</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <span>Stock will NOT be deducted (already consumed)</span>
                                </div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="group flex items-start gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary-400 hover:bg-primary-50/50 transition-all duration-200 hover:shadow-md">
                        <input type="radio" name="backdateType" value="external" class="mt-1 w-5 h-5 text-primary-600 focus:ring-primary-500 focus:ring-2">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1.5">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="font-bold text-gray-900">External Facility</div>
                            </div>
                            <div class="text-sm text-gray-600 space-y-0.5 ml-7">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    <span>Vaccinated outside Calauan (private clinic, other RHU)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                    <span><strong class="text-red-700">Will NOT be included</strong> in Calauan reports</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <span>Stock will NOT be deducted (not from Calauan supply)</span>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
                <button type="button" onclick="closeBackdateModal()" class="px-6 py-2.5 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all shadow-sm">
                    Cancel
                </button>
                <button type="button" onclick="confirmBackdateChoice()" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg font-semibold hover:from-primary-700 hover:to-primary-800 transition-all shadow-md hover:shadow-lg transform hover:scale-[1.02]">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Confirm & Save
                </button>
            </div>
        </div>
    </div>

    @php
        // Check for low/out of stock vaccines
        $lowStockVaccines = [];
        $outOfStockVaccines = [];
        foreach ($vaccineStocks ?? [] as $vaccineId => $stockInfo) {
            if (($stockInfo['status'] ?? '') === 'out') {
                $outOfStockVaccines[] = [
                    'name' => $stockInfo['name'] ?? 'Unknown',
                    'doses' => $stockInfo['available_doses'] ?? 0,
                    'bottles' => $stockInfo['available_bottles'] ?? 0
                ];
            } elseif (($stockInfo['status'] ?? '') === 'low') {
                $lowStockVaccines[] = [
                    'name' => $stockInfo['name'] ?? 'Unknown',
                    'doses' => $stockInfo['available_doses'] ?? 0,
                    'bottles' => $stockInfo['available_bottles'] ?? 0
                ];
            }
        }
        $totalAlerts = count($outOfStockVaccines) + count($lowStockVaccines);
    @endphp

    @if ($totalAlerts > 0)
        <div id="stockAlertBanner" class="no-print mb-6">
            <!-- Collapsed State -->
            <div id="stockAlertCollapsed" class="bg-gradient-to-r from-red-50 to-yellow-50 border-l-4 border-red-500 rounded-xl p-4 cursor-pointer hover:shadow-md transition-shadow" onclick="toggleStockAlert()">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2">
                            @if (count($outOfStockVaccines) > 0)
                                <div class="w-9 h-9 bg-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold ring-2 ring-white shadow-sm">
                                    {{ count($outOfStockVaccines) }}
                                </div>
                            @endif
                            @if (count($lowStockVaccines) > 0)
                                <div class="w-9 h-9 bg-yellow-500 rounded-full flex items-center justify-center text-white text-sm font-bold ring-2 ring-white shadow-sm">
                                    {{ count($lowStockVaccines) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                {{ $totalAlerts }} vaccine{{ $totalAlerts > 1 ? 's' : '' }} need{{ $totalAlerts > 1 ? '' : 's' }} attention
                            </p>
                            <p class="text-xs text-gray-600">
                                @if (count($outOfStockVaccines) > 0 && count($lowStockVaccines) > 0)
                                    {{ count($outOfStockVaccines) }} out of stock, {{ count($lowStockVaccines) }} running low
                                @elseif (count($outOfStockVaccines) > 0)
                                    {{ count($outOfStockVaccines) }} out of stock
                                @else
                                    {{ count($lowStockVaccines) }} running low
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                            $healthWorker = Auth::guard('health_worker')->user();
                        @endphp
                        @if(!$healthWorker || $healthWorker->isRHU())
                        <a href="{{ route('health_worker.inventory') }}" onclick="event.stopPropagation()" class="text-xs font-semibold text-purple-600 hover:text-purple-800 underline transition-colors">
                            Update Inventory
                        </a>
                        @endif
                        <svg id="stockAlertChevron" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Expanded State -->
            <div id="stockAlertExpanded" class="hidden bg-white border-2 border-purple-200 rounded-xl overflow-hidden shadow-lg">
                <div class="bg-gradient-to-r from-red-50 to-yellow-50 border-l-4 border-red-500 p-4 cursor-pointer" onclick="toggleStockAlert()">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-2">
                                @if (count($outOfStockVaccines) > 0)
                                    <div class="w-9 h-9 bg-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold ring-2 ring-white shadow-sm">
                                        {{ count($outOfStockVaccines) }}
                                    </div>
                                @endif
                                @if (count($lowStockVaccines) > 0)
                                    <div class="w-9 h-9 bg-yellow-500 rounded-full flex items-center justify-center text-white text-sm font-bold ring-2 ring-white shadow-sm">
                                        {{ count($lowStockVaccines) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $totalAlerts }} vaccine{{ $totalAlerts > 1 ? 's' : '' }} need{{ $totalAlerts > 1 ? '' : 's' }} attention
                                </p>
                                <p class="text-xs text-gray-600">Click to collapse details</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if(!$healthWorker || $healthWorker->isRHU())
                            <a href="{{ route('health_worker.inventory') }}" onclick="event.stopPropagation()" class="text-xs font-semibold text-purple-600 hover:text-purple-800 underline transition-colors">
                                Update Inventory
                            </a>
                            @endif
                            <svg id="stockAlertChevronExpanded" class="w-5 h-5 text-gray-500 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="p-5 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if (count($outOfStockVaccines) > 0)
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold text-red-800 uppercase flex items-center gap-1.5">
                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                    Out of Stock
                                </h4>
                                <div class="space-y-1.5">
                                    @foreach($outOfStockVaccines as $vaccine)
                                        <div class="text-sm bg-red-50 rounded-lg px-3 py-2 border border-red-100">
                                            <span class="font-semibold text-red-900">{{ $vaccine['name'] }}</span>
                                            <span class="text-xs text-red-600 ml-1">(0 doses)</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if (count($lowStockVaccines) > 0)
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold text-yellow-800 uppercase flex items-center gap-1.5">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                    Low Stock
                                </h4>
                                <div class="space-y-1.5">
                                    @foreach($lowStockVaccines as $vaccine)
                                        <div class="text-sm bg-yellow-50 rounded-lg px-3 py-2 border border-yellow-100">
                                            <span class="font-semibold text-yellow-900">{{ $vaccine['name'] }}</span>
                                            <span class="text-xs text-yellow-700 ml-1">({{ $vaccine['doses'] }} doses)</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('vaccinations.update', $patient->id ?? 0) }}" method="POST" id="vaccinationForm" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="w-full overflow-x-auto rounded-2xl shadow-sm ring-1 ring-primary-200 bg-white">
                    <table class="min-w-full text-[15px] table-grid">
                        <colgroup>
                            <col class="w-[26%]"><col class="w-[16%]"><col class="w-[31%]"><col class="w-[5%]"><col class="w-[22%]">
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
                            @foreach(($vaccinations ?? []) as $vaccination)
                                @php
                                    $vaccineName = optional($vaccination->vaccine)->vaccine_name;
                                    $dosesDescription = optional($vaccination->vaccine)->doses_description;
                                @endphp
                                <tr class="odd:bg-white even:bg-gray-50">
                                    <td class="p-3 md:p-4">{{ $vaccineName ?? 'N/A' }}</td>
                                    <td class="p-3 md:p-4">{{ $dosesDescription ?? 'N/A' }}</td>
                                    <td class="p-3 md:p-4 text-center" colspan="2">
                                        @if(in_array($vaccineName, ['BCG', 'Hepatitis B']) || ($vaccineName === 'Measles Containing' && $dosesDescription === 'Grade 1'))
                                            <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_1_date]" value="{{ $vaccination->dose_1_date ?? '' }}" {{ $vaccination->dose_1_date ? 'readonly' : '' }} class="date-input w-full rounded-md border-2 border-primary-300 bg-white text-center px-3 text-[15px]">
                                        @elseif(in_array($vaccineName, ['Inactivated Polio', 'Measles, Mumps, Rubella', 'Tetanus Diphtheria', 'Human Papillomavirus']) || ($vaccineName === 'Measles Containing' && $dosesDescription === 'Grade 7'))
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_1_date]" value="{{ $vaccination->dose_1_date ?? '' }}" {{ $vaccination->dose_1_date ? 'readonly' : '' }} class="date-input rounded-md border-2 border-primary-300 bg-white text-center px-3 text-[15px]">
                                                <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_2_date]" value="{{ $vaccination->dose_2_date ?? '' }}" {{ $vaccination->dose_2_date ? 'readonly' : '' }} class="date-input rounded-md border-2 border-primary-300 bg-white text-center px-3 text-[15px]">
                                            </div>
                                        @elseif(in_array($vaccineName, ['Pentavalent', 'Oral Polio', 'Pneumococcal Conjugate']))
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_1_date]" value="{{ $vaccination->dose_1_date ?? '' }}" {{ $vaccination->dose_1_date ? 'readonly' : '' }} class="date-input rounded-md border-2 border-primary-300 bg-white text-center px-3 text-[15px]">
                                                <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_2_date]" value="{{ $vaccination->dose_2_date ?? '' }}" {{ $vaccination->dose_2_date ? 'readonly' : '' }} class="date-input rounded-md border-2 border-primary-300 bg-white text-center px-3 text-[15px]">
                                                <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_3_date]" value="{{ $vaccination->dose_3_date ?? '' }}" {{ $vaccination->dose_3_date ? 'readonly' : '' }} class="date-input rounded-md border-2 border-primary-300 bg-white text-center px-3 text-[15px]">
                                            </div>
                                        @else
                                            <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_1_date]" value="{{ $vaccination->dose_1_date ?? '' }}" {{ $vaccination->dose_1_date ? 'readonly' : '' }} class="date-input w-full rounded-md border-2 border-primary-300 bg-white text-center px-3 text-[15px]">
                                        @endif
                                    </td>
                                    <td class="p-3 md:p-4">
                                        <textarea name="vaccinations[{{ $vaccination->id }}][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]">{{ $vaccination->remarks ?? '' }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center mt-6">
                    <button type="submit" id="saveButton" disabled class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white shadow-sm ring-1 ring-primary-600/50 focus:outline-none focus:ring-4 focus:ring-primary-300 transition hover:bg-primary-700 active:scale-[.98] disabled:opacity-60 disabled:cursor-not-allowed">
                        <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-120%] group-hover:translate-x-[120%] transition duration-700"></span>
                        {{-- <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                        </svg> --}}
                        <span>Save</span>
                    </button>
                </div>
            </form>

    <div class="mt-8 rounded-xl brand-gradient text-white px-6 py-5 text-center text-[13px] shadow-sm ring-1 ring-white/10">
        Sa column ng Petsa ng bakuna, isulat ang petsa ng pagbibigay ng bakuna ayon sa kung ilang dose ito. Sa column ng remarks, isulat ang petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon na maaaring makaapekto sa pagbabakuna ng bata.
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('vaccinationForm');
        const saveButton = document.getElementById('saveButton');
        if (!form || !saveButton) return;
        const inputs = Array.from(form.querySelectorAll('input[type="date"]:not([readonly]), textarea'));
        const initial = inputs.map(i => i.value || '');
        const check = () => { saveButton.disabled = !inputs.some((i, idx) => (i.value || '') !== initial[idx]); };
        inputs.forEach(i => i.addEventListener('input', check));
        check();
    });

    // Toggle stock alert collapse/expand
    function toggleStockAlert() {
        const collapsed = document.getElementById('stockAlertCollapsed');
        const expanded = document.getElementById('stockAlertExpanded');
        const chevron = document.getElementById('stockAlertChevron');
        const chevronExpanded = document.getElementById('stockAlertChevronExpanded');
        
        if (collapsed && expanded) {
            if (collapsed.style.display === 'none') {
                // Show collapsed, hide expanded
                collapsed.style.display = 'block';
                expanded.style.display = 'none';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            } else {
                // Hide collapsed, show expanded
                collapsed.style.display = 'none';
                expanded.style.display = 'block';
                if (chevronExpanded) chevronExpanded.style.transform = 'rotate(180deg)';
            }
        }
    }

    // Close error modal
    function closeErrorModal() {
        const modal = document.getElementById('errorModal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 300);
        }
    }

    // Close success modal
    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 300);
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeErrorModal();
            closeSuccessModal();
        }
    });

    // Close modal by clicking backdrop
    document.addEventListener('DOMContentLoaded', function() {
        const errorModal = document.getElementById('errorModal');
        if (errorModal) {
            errorModal.addEventListener('click', function(e) {
                if (e.target === errorModal) {
                    closeErrorModal();
                }
            });
        }
        
        const successModal = document.getElementById('successModal');
        if (successModal) {
            successModal.addEventListener('click', function(e) {
                if (e.target === successModal) {
                    closeSuccessModal();
                }
            });
            
            // Auto-close success modal after 2 seconds
            setTimeout(function() {
                closeSuccessModal();
            }, 2000);
        }
    });

    // Backdate detection and modal handling
    let backdateDetected = false;
    let backdateDateString = '';

    // Close backdate modal
    function closeBackdateModal() {
        const modal = document.getElementById('backdateModal');
        if (modal) {
            modal.classList.add('hidden');
            // Reset radio buttons
            document.querySelectorAll('input[name="backdateType"]').forEach(radio => {
                radio.checked = false;
            });
        }
    }

    // Confirm backdate choice and submit form
    function confirmBackdateChoice() {
        const selectedType = document.querySelector('input[name="backdateType"]:checked');
        if (!selectedType) {
            alert('Please select where the vaccination was administered.');
            return;
        }

        const form = document.getElementById('vaccinationForm');
        if (!form) return;

        // Add hidden input with backdate type
        let backdateInput = document.getElementById('backdateTypeInput');
        if (!backdateInput) {
            backdateInput = document.createElement('input');
            backdateInput.type = 'hidden';
            backdateInput.name = 'backdate_type';
            backdateInput.id = 'backdateTypeInput';
            form.appendChild(backdateInput);
        }
        backdateInput.value = selectedType.value;

        // Close modal and submit form
        closeBackdateModal();
        form.submit();
    }

    // Check if any date is backdated before form submission
    document.getElementById('vaccinationForm')?.addEventListener('submit', function(e) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const dateInputs = this.querySelectorAll('input[type="date"]:not([readonly])');
        backdateDetected = false;
        let oldestBackdate = null;

        dateInputs.forEach(input => {
            if (input.value) {
                const inputDate = new Date(input.value);
                inputDate.setHours(0, 0, 0, 0);
                
                if (inputDate < today) {
                    backdateDetected = true;
                    if (!oldestBackdate || inputDate < oldestBackdate) {
                        oldestBackdate = inputDate;
                    }
                }
            }
        });

        // If backdate detected and user hasn't chosen type yet
        const backdateTypeInput = document.getElementById('backdateTypeInput');
        if (backdateDetected && (!backdateTypeInput || !backdateTypeInput.value)) {
            e.preventDefault();
            
            // Calculate days ago
            const daysAgo = Math.floor((today - oldestBackdate) / (1000 * 60 * 60 * 24));
            const dateStr = oldestBackdate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            // Update modal with the date info
            document.getElementById('backdateInfo').textContent = 
                `${dateStr} (${daysAgo} ${daysAgo === 1 ? 'day' : 'days'} ago)`;
            
            // Show modal
            document.getElementById('backdateModal').classList.remove('hidden');
        }
    });

    // Close backdate modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBackdateModal();
        }
    });

    // Close backdate modal by clicking backdrop
    document.getElementById('backdateModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeBackdateModal();
        }
    });
    </script>
    
</div>
</body>
</html>
