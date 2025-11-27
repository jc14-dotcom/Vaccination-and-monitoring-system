@extends('layouts.responsive-layout')

@section('title', 'Vaccination Status')

@section('additional-styles')
<link rel="stylesheet" href="{{ asset('css/tailwind-full.css') }}">
<style>
    /* Page container matches dashboard spacing and full-width desktop */
    .hw-container{ width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width:640px){ .hw-container{ padding-left:2rem; padding-right:2rem; } }
    @media (min-width:1280px){ .hw-container{ padding-left:2.5rem; padding-right:2.5rem; } }
    .hw-no-overflow-x{ overflow-x:hidden; }
    
    /* Spinner animation */
    .spinner{ display:inline-block; width:20px; height:20px; border:3px solid rgba(0,0,0,0.1); border-radius:50%; border-top-color:#7a5bbd; animation:spin 1s ease-in-out infinite; margin-right:10px; }
    @keyframes spin{ to{ transform:rotate(360deg); } }
    /* Fade-in animation for header */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0">
    <!-- Page Banner -->
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex items-center gap-4" data-animate>
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold leading-tight">Vaccination Status</h1>
                <p class="text-sm md:text-base text-white/90 mt-1">Filter by status and search patients</p>
            </div>
        </div>
    </section>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Status Filter -->
            <div class="space-y-2">
                <label for="statusFilter" class="block text-base font-semibold text-gray-800">
                    Filter by Status
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </div>
                    <select id="statusFilter" class="w-full h-12 text-base rounded-lg border-2 border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all pl-11">
                        <option value="">All Status</option>
                        <option value="vaccinated" {{ ($filters['status'] ?? '') == 'vaccinated' ? 'selected' : '' }}>Vaccinated</option>
                        <option value="missed" {{ ($filters['status'] ?? '') == 'missed' ? 'selected' : '' }}>Missed</option>
                        <option value="not_done" {{ ($filters['status'] ?? '') == 'not_done' ? 'selected' : '' }}>Not Done</option>
                    </select>
                </div>
            </div>

            <!-- Search Input -->
            <div class="space-y-2">
                <label for="searchBar" class="block text-base font-semibold text-gray-800">
                    Search by Name or Contact
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="searchBar" value="{{ $filters['search'] ?? '' }}" placeholder="Type patient name or contact..." class="w-full h-12 text-base rounded-lg border-2 border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all placeholder:text-gray-400 pl-11" />
                    </div>
                    <button type="button" id="clearFiltersBtn" class="h-12 w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-gray-600 text-white text-base font-semibold px-6 shadow-sm hover:bg-gray-700 active:bg-gray-800 transition-all">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Clear Filters</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vaccination Day Banner (Auto-detected from Schedule) -->
    @if(isset($todaySchedules) && $todaySchedules->count() > 0)
        <div class="mb-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-5 shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 text-white">
                    <h3 class="text-lg font-bold mb-1">Vaccination Day Active</h3>
                    @if($todaySchedules->count() === 1)
                        <p class="text-sm text-white/95 mb-2">Today's vaccination is scheduled for <strong>{{ $todaySchedules->first()->barangay }}</strong></p>
                    @else
                        <p class="text-sm text-white/95 mb-2">Multiple barangays scheduled for vaccination today:</p>
                        <ul class="text-sm text-white/95 ml-4 list-disc">
                            @foreach($todaySchedules as $schedule)
                                <li><strong>{{ $schedule->barangay }}</strong></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <a href="{{ route('vaccination_schedule.index') }}" class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-white text-green-700 rounded-lg font-semibold text-sm hover:bg-green-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Manage Schedule
                </a>
            </div>
        </div>
    @endif

    <!-- Error/Success Messages -->
    @if (session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-600 rounded-xl p-4 shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-red-800">{{ session('error') }}</h3>
                    @if (session('stock_errors'))
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                            @foreach(session('stock_errors') as $stockError)
                                <li>{{ $stockError }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="text-xs text-red-600 mt-2">Please check the inventory and restock if needed before administering vaccines.</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-600 rounded-xl p-4 shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-bold text-green-800">{{ session('success') }}</h3>
                </div>
            </div>
        </div>
    @endif

    <!-- Table with Priority Sections -->
    <div class="overflow-x-auto w-full shadow-md rounded-lg bg-white mb-6">
        <table class="min-w-full" id="vaccinationStatusTable">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Age</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Barangay</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody id="patient-list" class="divide-y divide-gray-200">
                @php
                    // Check if we have priority patients (today's schedule)
                    $hasPriorityPatients = isset($priorityPatients) && $priorityPatients->count() > 0;
                    $hasOtherPatients = isset($otherPatients) && $otherPatients->count() > 0;
                    $hasActiveSchedule = isset($todaySchedules) && $todaySchedules->count() > 0;
                    
                    // Determine section display
                    $showSections = $hasActiveSchedule && ($hasPriorityPatients || $hasOtherPatients);
                @endphp
                
                @if($patients->count() > 0)
                    {{-- Priority Section: Today's Vaccination Schedule --}}
                    @if($showSections && $hasPriorityPatients)
                        <tr class="bg-green-50">
                            <td colspan="5" class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-base font-bold text-green-800 uppercase tracking-wide">
                                        Today's Vaccination Schedule - 
                                        @if($todaySchedules->count() === 1)
                                            {{ $todaySchedules->first()->barangay }}
                                        @else
                                            Multiple Barangays
                                        @endif
                                    </span>
                                    <span class="ml-auto text-sm font-semibold text-green-700 bg-green-200 px-3 py-1 rounded-full">
                                        {{ $priorityPatients->count() }} {{ Str::plural('patient', $priorityPatients->count()) }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @foreach($patients as $patient)
                            @if($patient->is_priority)
                                @php
                                    // Use status already calculated in controller
                                    $status = $patient->status ?? 'not_done';
                                    
                                    if ($status === 'missed') {
                                        $statusClass = 'bg-red-100 text-red-700';
                                        $statusLabel = 'Missed';
                                    } elseif ($status === 'vaccinated') {
                                        $statusClass = 'bg-green-100 text-green-700';
                                        $statusLabel = 'Vaccinated';
                                    } else {
                                        $statusClass = 'bg-amber-100 text-amber-700';
                                        $statusLabel = 'Not Done';
                                    }
                                @endphp
                                <tr data-id="{{ $patient->id }}" data-status="{{ $status }}" class="clickable-row hover:bg-primary-50 cursor-pointer transition-colors bg-green-50/30" data-url="{{ route('health_worker.patient_card', $patient->id) }}">
                                    <td class="px-6 py-4 text-base font-semibold text-gray-900">{{ $patient->display_name }}</td>
                                    <td class="px-6 py-4 text-base text-gray-700">{{ $patient->display_age }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $patient->barangay }}</td>
                                    <td class="px-6 py-4 text-base text-gray-700">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                                            @if($status === 'vaccinated')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    
                    {{-- Other Patients Section --}}
                    @if($showSections && $hasOtherPatients)
                        <tr class="bg-blue-50">
                            <td colspan="5" class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                    </svg>
                                    <span class="text-base font-bold text-blue-800 uppercase tracking-wide">Other Patients</span>
                                    <span class="ml-auto text-sm font-semibold text-blue-700 bg-blue-200 px-3 py-1 rounded-full">
                                        {{ $otherPatients->count() }} {{ Str::plural('patient', $otherPatients->count()) }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @foreach($patients as $patient)
                            @if(!$patient->is_priority)
                                @php
                                    // Use status already calculated in controller
                                    $status = $patient->status ?? 'not_done';
                                    
                                    if ($status === 'missed') {
                                        $statusClass = 'bg-red-100 text-red-700';
                                        $statusLabel = 'Missed';
                                    } elseif ($status === 'vaccinated') {
                                        $statusClass = 'bg-green-100 text-green-700';
                                        $statusLabel = 'Vaccinated';
                                    } else {
                                        $statusClass = 'bg-amber-100 text-amber-700';
                                        $statusLabel = 'Not Done';
                                    }
                                @endphp
                                <tr data-id="{{ $patient->id }}" data-status="{{ $status }}" class="clickable-row hover:bg-primary-50 cursor-pointer transition-colors" data-url="{{ route('health_worker.patient_card', $patient->id) }}">
                                    <td class="px-6 py-4 text-base font-semibold text-gray-900">{{ $patient->display_name }}</td>
                                    <td class="px-6 py-4 text-base text-gray-700">{{ $patient->display_age }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $patient->barangay }}</td>
                                    <td class="px-6 py-4 text-base text-gray-700">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                                            @if($status === 'vaccinated')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    
                    {{-- If no sections needed (no active schedule), show all patients normally --}}
                    @if(!$showSections)
                        @foreach($patients as $patient)
                            @php
                                // Use status already calculated in controller
                                $status = $patient->status ?? 'not_done';
                                
                                if ($status === 'missed') {
                                    $statusClass = 'bg-red-100 text-red-700';
                                    $statusLabel = 'Missed';
                                } elseif ($status === 'vaccinated') {
                                    $statusClass = 'bg-green-100 text-green-700';
                                    $statusLabel = 'Vaccinated';
                                } else {
                                    $statusClass = 'bg-amber-100 text-amber-700';
                                    $statusLabel = 'Not Done';
                                }
                            @endphp
                            <tr data-id="{{ $patient->id }}" data-status="{{ $status }}" class="clickable-row hover:bg-primary-50 cursor-pointer transition-colors" data-url="{{ route('health_worker.patient_card', $patient->id) }}">
                                <td class="px-6 py-4 text-base font-semibold text-gray-900">{{ $patient->display_name }}</td>
                                <td class="px-6 py-4 text-base text-gray-700">{{ $patient->display_age }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $patient->barangay }}</td>
                                <td class="px-6 py-4 text-base text-gray-700">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                                        @if($status === 'vaccinated')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @else
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-lg font-medium">No patients with incomplete vaccinations found</p>
                                <p class="text-sm">All patients are either fully immunized or match your filter criteria</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($patients->hasMorePages())
        <!-- Load More Button -->
        <div class="text-center mb-6">
            <button id="loadMoreBtn" onclick="loadMorePatients()" class="inline-flex items-center gap-2.5 px-8 py-3.5 bg-primary-700 text-white text-base rounded-lg font-semibold shadow-md hover:bg-primary-800 hover:shadow-lg active:bg-primary-900 transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
                Load More Patients
            </button>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="hidden text-center mb-6">
            <div class="inline-flex items-center gap-3 px-6 py-3.5 bg-white rounded-lg shadow-md border border-gray-200">
                <div class="spinner"></div>
                <span class="text-base text-gray-700 font-medium">Loading patients...</span>
            </div>
        </div>
    @endif

    <!-- Pagination Info -->
    @if($patients->total() > 0)
        <div id="paginationInfo" class="text-center text-base text-gray-600 mb-6 font-medium">
            Showing <strong class="text-primary-700" id="currentCount">{{ $patients->count() }}</strong> of <strong class="text-primary-700" id="totalCount">{{ $patients->total() }}</strong> patients
            <span id="filterInfo">
                @if($filters['search'] ?? false)
                    matching "<strong class="text-gray-900">{{ $filters['search'] }}</strong>"
                @endif
                @if($filters['status'] ?? false)
                    with status <strong class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $filters['status'])) }}</strong>
                @endif
            </span>
        </div>
    @endif
</div>
@endsection

@section('additional-scripts')
<script>
    let currentPage = 1;
    let hasMore = {{ $patients->hasMorePages() ? 'true' : 'false' }};
    let isLoading = false;
    let searchDebounceTimer = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Let blade template handle initial render, don't replace
        setupEventListeners();
        setupClickableRows();
    });

    function setupClickableRows() {
        // Add click handlers to all clickable rows
        document.querySelectorAll('.clickable-row').forEach(function(row) {
            row.onclick = function() {
                window.location = this.getAttribute('data-url');
            };
        });
    }

    function setupEventListeners() {
        const searchInput = document.getElementById('searchBar');
        const statusFilter = document.getElementById('statusFilter');
        const clearBtn = document.getElementById('clearFiltersBtn');

        // Debounced search input (300ms)
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    handleSearch();
                }, 300);
            });

            // Enter key triggers immediate search
            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    handleSearch();
                }
            });
        }

        // Status filter - triggers immediate search
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                clearTimeout(searchDebounceTimer);
                handleSearch();
            });
        }

        // Clear all filters and reload
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (statusFilter) statusFilter.value = '';
                if (searchInput) searchInput.value = '';
                window.location.href = '{{ route("health_worker.vaccination_status") }}';
            });
        }
    }

    function handleSearch() {
        currentPage = 1;
        const searchQuery = document.getElementById('searchBar').value.trim();
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams();
        params.append('page', 1);
        if (searchQuery) params.append('search', searchQuery);
        if (status) params.append('status', status);

        showLoading(true);

        fetch(`/vaccination-status/load?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                replaceTableRows(data.patients);
                hasMore = data.has_more || false;
                updateLoadMoreButton();
                updatePaginationInfo(data.patients.length, data.total, searchQuery, status);
                showLoading(false);
            })
            .catch(error => {
                console.error('Search failed:', error);
                showLoading(false);
            });
    }

    function loadMorePatients() {
        if (isLoading || !hasMore) return;

        currentPage++;
        const searchQuery = document.getElementById('searchBar').value.trim();
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams();
        params.append('page', currentPage);
        if (searchQuery) params.append('search', searchQuery);
        if (status) params.append('status', status);

        showLoading(true);

        fetch(`/vaccination-status/load?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                appendTableRows(data.patients);
                hasMore = data.has_more || false;
                updateLoadMoreButton();
                const currentCount = document.querySelectorAll('.clickable-row').length;
                updatePaginationInfo(currentCount, data.total, searchQuery, status);
                showLoading(false);
            })
            .catch(error => {
                console.error('Load more failed:', error);
                showLoading(false);
                currentPage--;
            });
    }

    function replaceTableRows(patients) {
        const tbody = document.getElementById('patient-list');
        tbody.innerHTML = '';

        if (patients.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-lg font-medium">No patients found</p>
                            <p class="text-sm">Try adjusting your search or filter criteria</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        patients.forEach(patient => {
            tbody.appendChild(createPatientRow(patient));
        });
    }

    function appendTableRows(patients) {
        const tbody = document.getElementById('patient-list');
        patients.forEach(patient => {
            tbody.appendChild(createPatientRow(patient));
        });
    }

    function createPatientRow(patient) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', patient.id);
        tr.setAttribute('data-status', patient.status);
        tr.className = 'clickable-row hover:bg-primary-50 cursor-pointer transition-colors';
        tr.setAttribute('data-url', `/vaccination_form/${patient.id}`);
        tr.onclick = function() { window.location = this.getAttribute('data-url'); };

        // Determine status styling
        let statusClass, statusLabel, statusIcon;
        if (patient.status === 'missed') {
            statusClass = 'bg-red-100 text-red-700';
            statusLabel = 'Missed';
            statusIcon = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
        } else if (patient.status === 'vaccinated') {
            statusClass = 'bg-green-100 text-green-700';
            statusLabel = 'Vaccinated';
            statusIcon = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
        } else {
            statusClass = 'bg-amber-100 text-amber-700';
            statusLabel = 'Not Done';
            statusIcon = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
        }

        const currentDate = new Date().toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });

        tr.innerHTML = `
            <td class="px-6 py-4 text-base font-semibold text-gray-900">${patient.display_name}</td>
            <td class="px-6 py-4 text-base text-gray-700">${patient.display_age}</td>
            <td class="px-6 py-4 text-base text-gray-700">${currentDate}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold ${statusClass}">
                    ${statusIcon}
                    ${statusLabel}
                </span>
            </td>
        `;

        return tr;
    }

    function showLoading(show) {
        isLoading = show;
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        const loadingIndicator = document.getElementById('loadingIndicator');

        if (loadMoreBtn) loadMoreBtn.style.display = show ? 'none' : '';
        if (loadingIndicator) loadingIndicator.classList.toggle('hidden', !show);
    }

    function updateLoadMoreButton() {
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = hasMore ? '' : 'none';
        }
    }

    function updatePaginationInfo(currentCount, total, searchQuery, status) {
        const paginationInfo = document.getElementById('paginationInfo');
        if (!paginationInfo) return;

        const currentCountEl = document.getElementById('currentCount');
        const totalCountEl = document.getElementById('totalCount');
        const filterInfoEl = document.getElementById('filterInfo');

        if (currentCountEl) currentCountEl.textContent = currentCount;
        if (totalCountEl) totalCountEl.textContent = total;

        if (filterInfoEl) {
            let filterText = '';
            if (searchQuery) {
                filterText += ` matching "<strong class="text-gray-900">${searchQuery}</strong>"`;
            }
            if (status) {
                const statusLabel = status.replace('_', ' ');
                filterText += ` with status <strong class="text-gray-900">${statusLabel.charAt(0).toUpperCase() + statusLabel.slice(1)}</strong>`;
            }
            filterInfoEl.innerHTML = filterText;
        }

        paginationInfo.style.display = total > 0 ? '' : 'none';
    }
</script>
@endsection
