@extends('layouts.responsive-layout')

@section('title', 'Current Vaccination Report')

@section('additional-styles')
<link rel="stylesheet" href="{{ asset('css/tailwind-full.css') }}">
<style>
    /* Vaccination Report Table Styles */
    #vaccinationReportTable {
        border-collapse: collapse;
        width: 100%;
        font-size: 14px;
        background-color: #ffffff;
    }
    
    /* Table headers */
    #vaccinationReportTable thead th {
        background-color: #E5E7EB;
        color: #111827;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        padding: 10px 6px;
        border: 1px solid #9CA3AF;
        white-space: normal;
        word-wrap: break-word;
        font-size: 12px;
    }
    
    /* Sub-header row (M/F/T/%) - lower z-index than AREA */
    #vaccinationReportTable thead tr:nth-child(2) th {
        background-color: #F3F4F6;
        font-size: 12px;
        padding: 8px 4px;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }
    
    /* Sticky first column (Area) - stays on top when scrolling */
    #vaccinationReportTable thead th:first-child {
        position: sticky;
        left: 0;
        z-index: 5 !important;
        background-color: #E5E7EB;
        font-weight: 700;
        min-width: 140px;
        max-width: 180px;
        font-size: 14px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    
    #vaccinationReportTable tbody td:first-child {
        position: sticky;
        left: 0;
        z-index: 4;
        background-color: #FFFFFF;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        font-weight: 600;
        min-width: 140px;
        max-width: 180px;
        font-size: 14px;
    }
    
    /* TOTAL row sticky background */
    #vaccinationReportTable tbody tr:last-child td:first-child {
        background-color: #FEF3C7;
        z-index: 4;
    }
    
    /* All other table headers stay behind AREA column */
    #vaccinationReportTable thead th {
        position: relative;
        z-index: 1;
    }
    
    /* Ensure AREA column header is highest within table */
    #vaccinationReportTable thead th:first-child {
        z-index: 5;
    }
    
    /* Table body cells */
    #vaccinationReportTable tbody td {
        padding: 10px 6px;
        border: 1px solid #D1D5DB;
        text-align: center;
        color: #374151;
        font-size: 14px;
    }
    
    /* TOTAL row styling */
    #vaccinationReportTable tbody tr:last-child {
        background-color: #FEF3C7;
        font-weight: 700;
    }
    
    #vaccinationReportTable tbody tr:last-child td {
        border-top: 2px solid #92400E;
        border-bottom: 2px solid #92400E;
    }
    
    /* Hover effect for non-TOTAL rows */
    #vaccinationReportTable tbody tr:not(:last-child):hover {
        background-color: #F9FAFB;
    }
    
    /* Eligible Population columns - wider to fit text */
    #vaccinationReportTable th:nth-child(2),
    #vaccinationReportTable td:nth-child(2),
    #vaccinationReportTable th:nth-child(3),
    #vaccinationReportTable td:nth-child(3),
    #vaccinationReportTable th:nth-child(4),
    #vaccinationReportTable td:nth-child(4) {
        min-width: 100px;
        max-width: 120px;
        white-space: normal;
        word-wrap: break-word;
        padding: 10px 8px;
        font-size: 13px;
    }
    
    /* Vaccine data columns (M/F/T/%) - consistent width matching HepB */
    #vaccinationReportTable thead th:nth-child(n+5) {
        min-width: 55px;
        max-width: 55px;
        width: 55px;
        padding: 8px 4px;
        font-size: 12px;
    }
    
    #vaccinationReportTable tbody td:nth-child(n+5) {
        min-width: 55px;
        max-width: 55px;
        width: 55px;
        padding: 10px 4px;
        font-size: 14px;
    }
    
    /* Bold total count columns (T columns) */
    #vaccinationReportTable tbody td:nth-child(4n+7) {
        font-weight: 700;
        color: #1F2937;
    }
    
    /* Scrollbar styling */
    .overflow-x-auto::-webkit-scrollbar {
        height: 10px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #F3F4F6;
        border-radius: 5px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #9CA3AF;
        border-radius: 5px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #6B7280;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        #vaccinationReportTable {
            font-size: 12px;
        }
        
        #vaccinationReportTable thead th,
        #vaccinationReportTable tbody td {
            padding: 6px 4px;
        }
        
        #vaccinationReportTable th:first-child,
        #vaccinationReportTable td:first-child {
            min-width: 110px;
            font-size: 12px;
        }
        
        #vaccinationReportTable th:not(:first-child):not(:nth-child(2)),
        #vaccinationReportTable td:not(:first-child):not(:nth-child(2)) {
            min-width: 45px;
            max-width: 55px;
        }
        
        #vaccinationReportTable tbody td:nth-child(4n+4) {
            font-size: 13px;
        }
    }
    
    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        
        #vaccinationReportTable {
            font-size: 8px;
        }
        
        #vaccinationReportTable th:first-child,
        #vaccinationReportTable td:first-child {
            position: static;
            box-shadow: none;
        }
    }
    
    /* Loading animation for cells */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .cell-loading {
        animation: pulse 1.5s ease-in-out infinite;
    }
</style>
@endsection

@section('content')
    <div class="min-h-screen w-full">
        <!-- Main content area -->
        <div class="w-full max-w-full mx-auto px-4 sm:px-8 xl:px-10 pb-8">
            
            <!-- Banner Header (matching image style) -->
            <section class="relative overflow-hidden rounded-2xl mb-6 bg-white border-2 border-gray-300 shadow-md">
                <div class="relative px-6 py-8 text-center">
                    <!-- Logo Section (optional - can add DOH logo here) -->
                    <div class="flex items-center justify-center gap-4 mb-4">
                        <img src="{{ asset('images/todoligtass.png') }}" alt="Logo" class="h-16 w-auto object-contain">
                    </div>
                    
                    <!-- Main Title -->
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                        CHILD CARE PROGRAM
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="text-sm md:text-base text-gray-700 mb-4">
                        Immunization Services for Newborns, Infants and School-Aged Children/Adolescents cont.
                    </p>
                    
                    <!-- Date Range (from controller) -->
                    <p class="text-sm font-semibold text-gray-800 mb-3">
                        {{ $dateRange ?? 'Jan 01, 2025 to Dec 31, 2025' }}
                    </p>
                    
                    <!-- Location -->
                    <p class="text-base font-bold text-gray-900">
                        CALAUAN, LAGUNA
                    </p>
                    
                    <!-- Status Badge - Always Live Data -->
                    <div class="mt-4">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-800 text-sm font-semibold ring-1 ring-green-300">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            Live Data
                        </span>
                    </div>
                </div>
            </section>

            <!-- Filter Controls -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" action="{{ route('reports.current') }}" id="filterForm" class="space-y-4">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <!-- Year Filter -->
                        <div>
                            <label for="year" class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Year
                            </label>
                            <select name="year" id="year" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 transition">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        
                        <!-- Month Range Filter -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Month Range
                            </label>
                            
                            <div class="space-y-3">
                                <!-- Month Dropdowns -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="month_start" class="block text-xs text-gray-600 mb-1">From Month</label>
                                        <select name="month_start" id="month_start" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 transition text-sm">
                                            @php
                                                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                                $monthStart = $monthStart ?? 1;
                                            @endphp
                                            @foreach($months as $index => $month)
                                                <option value="{{ $index + 1 }}" {{ $monthStart == ($index + 1) ? 'selected' : '' }}>
                                                    {{ $month }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="month_end" class="block text-xs text-gray-600 mb-1">To Month</label>
                                        <select name="month_end" id="month_end" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 transition text-sm">
                                            @php
                                                $monthEnd = $monthEnd ?? 12;
                                            @endphp
                                            @foreach($months as $index => $month)
                                                <option value="{{ $index + 1 }}" {{ $monthEnd == ($index + 1) ? 'selected' : '' }}>
                                                    {{ $month }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Barangay Filter -->
                        <div>
                            <label for="barangay" class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Barangay
                            </label>
                            <select name="barangay" id="barangay" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 transition">
                                <option value="">All Barangays</option>
                                @foreach($barangays ?? [] as $brgy)
                                    <option value="{{ $brgy }}" {{ ($barangayFilter ?? '') == $brgy ? 'selected' : '' }}>
                                        {{ $brgy }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" onclick="resetFilters()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset
                        </button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 shadow-md transition">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Apply Filters
                        </button>
                    </div>
                    
                </form>
            </div>

            <!-- Vaccination Report Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                
                <!-- Table wrapper with horizontal scroll -->
                <div class="overflow-x-auto">
                    <table id="vaccinationReportTable" class="min-w-full border-collapse border border-gray-300">
                        
                        <!-- Table Header -->
                        <thead>
                            <!-- Main header row - vaccine acronyms with dose numbers -->
                            <tr class="bg-gray-200">
                                <th rowspan="2" class="border border-gray-400 px-4 py-3 text-center font-bold text-gray-900 align-middle sticky left-0 bg-gray-200">
                                    AREA
                                </th>
                                
                                <!-- 3 Eligible Population Columns -->
                                <th rowspan="2" class="border border-gray-400 px-2 py-3 text-center font-bold text-gray-900 text-xs align-middle">
                                    ELIGIBLE<br>POPULATION<br><span class="text-[10px] font-normal">(Under 1 yr)*</span>
                                </th>
                                <th rowspan="2" class="border border-gray-400 px-2 py-3 text-center font-bold text-gray-900 text-xs align-middle">
                                    ELIGIBLE<br>POPULATION<br><span class="text-[10px] font-normal">(0-12 mos)**</span>
                                </th>
                                <th rowspan="2" class="border border-gray-400 px-2 py-3 text-center font-bold text-gray-900 text-xs align-middle">
                                    ELIGIBLE<br>POPULATION<br><span class="text-[10px] font-normal">(13-23 mos)***</span>
                                </th>
                                
                                @php
                                    use App\Config\VaccineConfig;
                                    
                                    // Get vaccine config and build dose columns
                                    $vaccineConfig = VaccineConfig::getDoseConfiguration();
                                    $doseColumns = [];
                                    
                                    foreach ($vaccineConfig as $vaccineName => $config) {
                                        $acronym = $config['acronym'];
                                        $totalDoses = $config['total_doses'];
                                        
                                        if ($totalDoses > 1) {
                                            // Multi-dose vaccine: show acronym with dose number
                                            for ($dose = 1; $dose <= $totalDoses; $dose++) {
                                                // Special handling for IPV Dose 2 - split into Routine and Catch-up
                                                if ($vaccineName === 'Inactivated Polio' && $dose === 2) {
                                                    $doseColumns[] = [
                                                        'label' => $acronym . ' 2 (R)',
                                                        'key' => $vaccineName . '|Dose ' . $dose . '|Routine'
                                                    ];
                                                    $doseColumns[] = [
                                                        'label' => $acronym . ' 2 (C-U)',
                                                        'key' => $vaccineName . '|Dose ' . $dose . '|Catch-up'
                                                    ];
                                                } else {
                                                    $doseColumns[] = [
                                                        'label' => $acronym . ' ' . $dose,
                                                        'key' => $vaccineName . '|Dose ' . $dose
                                                    ];
                                                }
                                            }
                                        } else {
                                            // Single-dose vaccine: just show acronym
                                            $doseColumns[] = [
                                                'label' => $acronym,
                                                'key' => $vaccineName
                                            ];
                                        }
                                    }
                                @endphp
                                
                                @if(count($doseColumns) > 0)
                                    @foreach($doseColumns as $column)
                                        <th colspan="4" class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">
                                            {{ $column['label'] }}
                                        </th>
                                    @endforeach
                                    
                                    <!-- FIC Column -->
                                    <th colspan="4" class="border border-gray-400 px-2 py-2 text-center font-semibold text-blue-700 text-xs">
                                        FIC**
                                    </th>
                                    
                                    <!-- CIC Column -->
                                    <th colspan="4" class="border border-gray-400 px-2 py-2 text-center font-semibold text-green-700 text-xs">
                                        CIC***
                                    </th>
                                @else
                                    <th colspan="4" class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">
                                        No vaccines available
                                    </th>
                                @endif
                            </tr>
                            
                            <!-- Sub-header row - M/F/T/% for each vaccine -->
                            <tr class="bg-gray-100">
                                @if(count($doseColumns) > 0)
                                    @foreach($doseColumns as $column)
                                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">M</th>
                                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">F</th>
                                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">T</th>
                                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">%</th>
                                    @endforeach
                                    
                                    <!-- FIC sub-headers -->
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-blue-700 text-xs">M</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-blue-700 text-xs">F</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-blue-700 text-xs">T</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-blue-700 text-xs">%</th>
                                    
                                    <!-- CIC sub-headers -->
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-green-700 text-xs">M</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-green-700 text-xs">F</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-green-700 text-xs">T</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-green-700 text-xs">%</th>
                                @else
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">M</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">F</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">T</th>
                                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">%</th>
                                @endif
                            </tr>
                        </thead>
                        
                        <!-- Table Body -->
                        <tbody>
                            @if(isset($reportData) && count($reportData) > 0)
                                @foreach($reportData as $row)
                                    <tr class="{{ $row['barangay'] === 'TOTAL' ? 'bg-yellow-100 font-bold' : 'hover:bg-gray-50' }}">
                                        <!-- Barangay Name -->
                                        <td class="border border-gray-400 px-4 py-2 text-left font-semibold text-gray-900 sticky left-0 {{ $row['barangay'] === 'TOTAL' ? 'bg-yellow-100' : 'bg-white' }}">
                                            {{ $row['barangay'] }}
                                        </td>
                                        
                                        <!-- 3 Eligible Population Columns -->
                                        <td class="border border-gray-400 px-2 py-2 text-center text-gray-800">
                                            {{ number_format($row['eligible_population_under_1_year'] ?? 0) }}
                                        </td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-gray-800">
                                            {{ number_format($row['eligible_population_0_12_months'] ?? 0) }}
                                        </td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-gray-800">
                                            {{ number_format($row['eligible_population_13_23_months'] ?? 0) }}
                                        </td>
                                        
                                        <!-- Vaccine dose columns - loop through dose columns to match headers -->
                                        @foreach($doseColumns as $column)
                                            @php
                                                // Get vaccine data if it exists for this barangay, otherwise use defaults
                                                $vaccineData = $row['vaccines'][$column['key']] ?? [
                                                    'male_count' => 0,
                                                    'female_count' => 0,
                                                    'total_count' => 0,
                                                    'percentage' => 0
                                                ];
                                            @endphp
                                            <!-- Male Count -->
                                            <td class="border border-gray-400 px-2 py-2 text-center text-gray-800">
                                                {{ $vaccineData['male_count'] ?? 0 }}
                                            </td>
                                            <!-- Female Count -->
                                            <td class="border border-gray-400 px-2 py-2 text-center text-gray-800">
                                                {{ $vaccineData['female_count'] ?? 0 }}
                                            </td>
                                            <!-- Total Count -->
                                            <td class="border border-gray-400 px-2 py-2 text-center text-gray-800 font-semibold">
                                                {{ $vaccineData['total_count'] ?? 0 }}
                                            </td>
                                            <!-- Percentage -->
                                            <td class="border border-gray-400 px-2 py-2 text-center text-gray-800">
                                                {{ number_format($vaccineData['percentage'] ?? 0, 2) }}%
                                            </td>
                                        @endforeach
                                        
                                        <!-- FIC Data -->
                                        @php
                                            $ficData = $row['fic'] ?? [
                                                'male_count' => 0,
                                                'female_count' => 0,
                                                'total_count' => 0,
                                                'percentage' => 0
                                            ];
                                        @endphp
                                        <td class="border border-gray-400 px-2 py-2 text-center text-blue-800">
                                            {{ $ficData['male_count'] ?? 0 }}
                                        </td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-blue-800">
                                            {{ $ficData['female_count'] ?? 0 }}
                                        </td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-blue-800 font-semibold">
                                            {{ $ficData['total_count'] ?? 0 }}
                                        </td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-blue-800">
                                            {{ number_format($ficData['percentage'] ?? 0, 2) }}%
                                        </td>
                                        
                                        <!-- CIC Data -->
                                        @php
                                            $cicData = $row['cic'] ?? [
                                                'male_count' => 0,
                                                'female_count' => 0,
                                                'total_count' => 0,
                                                'percentage' => 0
                                            ];
                                        @endphp
                                        <td class="border border-gray-400 px-2 py-2 text-center text-green-800">
                                            {{ $cicData['male_count'] ?? 0 }}
                                        </td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-green-800">
                                            {{ $cicData['female_count'] ?? 0 }}
                                        </td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-green-800 font-semibold">
                                            {{ $cicData['total_count'] ?? 0 }}
                                        </td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-green-800">
                                            {{ number_format($cicData['percentage'] ?? 0, 2) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="{{ 4 + (count($doseColumns) * 4) + 8 }}" class="border border-gray-400 px-4 py-8 text-center text-gray-500">
                                        No data available for the selected period.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        
                    </table>
                </div>
                
                <!-- Footnotes -->
                <div class="mt-4 px-4 py-3 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-700 font-semibold mb-2">Legend:</p>
                    <ul class="text-xs text-gray-600 space-y-1">
                        <li><span class="font-semibold">*</span> Denominator: Eligible Population (Under 1 year) - Used for calculating percentages of infant vaccines (BCG, HepB, Pentavalent, OPV, IPV, PCV)</li>
                        <li><span class="font-semibold text-blue-700">** FIC (Fully Immunized Child)</span> - Children who completed BCG, HepB, Pentavalent (3 doses), OPV (3 doses), and MMR (2 doses) before reaching 12 months of age. Denominator: Eligible Population (0-12 months)</li>
                        <li><span class="font-semibold text-green-700">*** CIC (Completely Immunized Child)</span> - Children aged 13-23 months who completed all FIC vaccines plus school-based vaccines (MCV Grade 1, MCV Grade 7, TD, HPV). Denominator: Eligible Population (13-23 months)</li>
                    </ul>
                    <p class="text-xs text-gray-500 italic mt-2">Note: MMR uses 0-12 months eligible population as denominator. School-based vaccines (MCV, TD, HPV) use grade-specific eligible populations.</p>
                </div>
                
                <!-- Action buttons -->
                <div class="mt-6 flex items-center justify-between gap-3 flex-wrap">
                    
                    <!-- Left side - Save Report button (always show for live data) -->
                    <div class="flex items-center gap-3">
                        <!-- Save Report Button (creates new version) -->
                        <button onclick="saveReport()" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 shadow-md transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Save Report
                        </button>
                        
                        <!-- Report History Link -->
                        <a href="{{ route('reports.history') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 shadow-md transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            View Report History
                        </a>
                        
                        <!-- Settings Link -->
                        <a href="{{ route('reports.settings') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 shadow-md transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Settings
                        </a>
                    </div>
                    
                    <!-- Right side - Export buttons -->
                    <div class="flex items-center gap-3">
                        <button onclick="exportToPDF()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 shadow-md transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Export PDF
                        </button>
                        <button onclick="exportToExcel()" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 shadow-md transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Excel
                        </button>
                    </div>
                    
                </div>
                
            </div>
            
        </div>
    </div>
    
    <!-- Save Report Confirmation Modal -->
    <div id="saveReportModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Save Report as Version
                </h3>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <p class="text-gray-700 text-base mb-4">
                    This will save the current live data as a new version in Report History.
                </p>
                <ul class="space-y-2 mb-4">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-600">Creates a timestamped snapshot of current data</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-600">Version number will auto-increment (v1, v2, v3...)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-600">Saved versions are read-only archives</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-600">Current page will continue showing live data</span>
                    </li>
                </ul>
                <p class="text-sm text-gray-500 bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <span class="font-semibold text-blue-800">Tip:</span> You can save multiple versions of the same period for version tracking and audit purposes.
                </p>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
                <button onclick="closeSaveModal()" 
                        class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmSaveReport()" 
                        class="px-5 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                    Save Report
                </button>
            </div>
        </div>
    </div>
    
    <!-- Success Message Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Success
                </h3>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <p class="text-gray-700 text-base" id="successMessage"></p>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button onclick="closeSuccessModal()" 
                        class="px-5 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>
    
    <!-- Error Message Modal -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Error
                </h3>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <p class="text-gray-700 text-base" id="errorMessage"></p>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button onclick="closeErrorModal()" 
                        class="px-5 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>
@endsection

@section('additional-scripts')
    <!-- Export libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.min.js"></script>
    
    <script>
        // Reset filters
        function resetFilters() {
            document.getElementById('year').value = {{ date('Y') }};
            document.getElementById('month_start').value = 1;
            document.getElementById('month_end').value = 12;
            document.getElementById('barangay').value = '';
            document.getElementById('filterForm').submit();
        }
        
        // Save Report (replaces Lock Report)
        function saveReport() {
            const modal = document.getElementById('saveReportModal');
            modal.style.display = 'flex';
        }
        
        function closeSaveModal() {
            const modal = document.getElementById('saveReportModal');
            modal.style.display = 'none';
        }
        
        function confirmSaveReport() {
            closeSaveModal();
            
            // Get current values from the page (not the initial server values)
            const year = parseInt(document.getElementById('year').value);
            const monthStart = parseInt(document.getElementById('month_start').value);
            const monthEnd = parseInt(document.getElementById('month_end').value);
            
            // DEBUG: Uncomment for debugging
            // console.log('Saving report:', { year, monthStart, monthEnd });
            
            fetch('{{ route("reports.lock") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    year, 
                    quarter_start: Math.ceil(monthStart / 3), // Convert months to quarters for backend
                    quarter_end: Math.ceil(monthEnd / 3),
                    month_start: monthStart, // Send actual months for proper labeling
                    month_end: monthEnd 
                })
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned an error. Please check the logs or contact support.');
                }
                
                // Check HTTP status
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Failed to save report');
                    });
                }
                
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const version = data.version || '?';
                    const message = `Report saved successfully as Version ${version}! You can view it in Report History.`;
                    showSuccessModal(message);
                } else {
                    showErrorModal(data.message || 'Failed to save report');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal(error.message || 'An error occurred while saving the report. Please try again.');
            });
        }
        
        // Show Success Modal
        function showSuccessModal(message) {
            document.getElementById('successMessage').textContent = message;
            const modal = document.getElementById('successModal');
            modal.style.display = 'flex';
        }
        
        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'none';
            // Don't reload - stay on current report page
        }
        
        // Show Error Modal
        function showErrorModal(message) {
            document.getElementById('errorMessage').textContent = message;
            const modal = document.getElementById('errorModal');
            modal.style.display = 'flex';
        }
        
        function closeErrorModal() {
            const modal = document.getElementById('errorModal');
            modal.style.display = 'none';
        }
        
        // Enhanced PDF Export with Landscape Orientation and DOH Format
        function exportToPDF() {
            try {
                const { jsPDF } = window.jspdf;
                
                // A4 Landscape: 297mm x 210mm (fits all columns better)
                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });
                
                const pageWidth = doc.internal.pageSize.getWidth();
                const pageHeight = doc.internal.pageSize.getHeight();
                
                // Add header on each page
                const addHeader = (doc, pageNum) => {
                    // Title
                    doc.setFontSize(14);
                    doc.setFont(undefined, 'bold');
                    doc.text('CHILD CARE PROGRAM', pageWidth / 2, 12, { align: 'center' });
                    
                    // Subtitle
                    doc.setFontSize(9);
                    doc.setFont(undefined, 'normal');
                    doc.text('Immunization Services for Newborns, Infants and School-Aged Children/Adolescents cont.', 
                        pageWidth / 2, 18, { align: 'center' });
                    
                    // Date range
                    doc.text('{{ $dateRange ?? "" }}', pageWidth / 2, 23, { align: 'center' });
                    
                    // Location
                    doc.setFont(undefined, 'bold');
                    doc.text('CALAUAN, LAGUNA', pageWidth / 2, 28, { align: 'center' });
                    
                    // Page number
                    doc.setFontSize(8);
                    doc.setFont(undefined, 'normal');
                    doc.text(`Page ${pageNum}`, pageWidth - 15, 10, { align: 'right' });
                };
                
                // Get table element
                const table = document.getElementById('vaccinationReportTable');
                
                // Build headers array
                const headers = [];
                const subHeaders = [];
                
                // First header row (AREA, Eligible Pops, Vaccine names)
                const mainHeaderRow = table.querySelector('thead tr:first-child');
                mainHeaderRow.querySelectorAll('th').forEach(th => {
                    const colspan = parseInt(th.getAttribute('colspan') || 1);
                    const rowspan = parseInt(th.getAttribute('rowspan') || 1);
                    let text = th.textContent.trim().replace(/\s+/g, ' ');
                    
                    // Clean up text
                    text = text.replace('(Under 1 yr)*', '(U1)*');
                    text = text.replace('(0-12 mos)**', '(0-12)**');
                    text = text.replace('(13-23 mos)***', '(13-23)***');
                    
                    if (rowspan === 2) {
                        headers.push({ 
                            content: text, 
                            rowSpan: 2, 
                            styles: { 
                                halign: 'center', 
                                valign: 'middle',
                                fontStyle: 'bold',
                                fontSize: 7,
                                cellPadding: 0.5
                            }
                        });
                    } else {
                        headers.push({ 
                            content: text, 
                            colSpan: colspan, 
                            styles: { 
                                halign: 'center',
                                fontStyle: 'bold',
                                fontSize: 7,
                                cellPadding: 0.5
                            }
                        });
                    }
                });
                
                // Second header row (M/F/T/%)
                const subHeaderRow = table.querySelector('thead tr:nth-child(2)');
                subHeaderRow.querySelectorAll('th').forEach(th => {
                    subHeaders.push({ 
                        content: th.textContent.trim(), 
                        styles: { 
                            halign: 'center',
                            fontStyle: 'bold',
                            fontSize: 6,
                            cellPadding: 0.5
                        }
                    });
                });
                
                // Build body data
                const bodyData = [];
                table.querySelectorAll('tbody tr').forEach((tr, index) => {
                    const row = [];
                    tr.querySelectorAll('td').forEach((td, colIndex) => {
                        let text = td.textContent.trim();
                        
                        // Format percentages
                        if (text.includes('%') && colIndex > 0) {
                            text = text.replace('.00%', '%');
                        }
                        
                        row.push(text);
                    });
                    
                    bodyData.push(row);
                });
                
                // Column widths - compressed to fit all 31 columns
                const columnStyles = {
                    0: { cellWidth: 20, fontStyle: 'bold', fontSize: 7 }, // AREA
                };
                
                // Eligible population columns (narrower)
                for (let i = 1; i <= 3; i++) {
                    columnStyles[i] = { cellWidth: 10, halign: 'center', fontSize: 6 };
                }
                
                // Vaccine columns (M/F/T/%) - very narrow to fit
                for (let i = 4; i < 120; i++) {
                    columnStyles[i] = { cellWidth: 5.5, halign: 'center', fontSize: 6 };
                }
                
                let pageNum = 1;
                
                // Add first page header
                addHeader(doc, pageNum);
                
                // Generate table with auto-pagination
                doc.autoTable({
                    head: [headers, subHeaders],
                    body: bodyData,
                    startY: 33,
                    styles: { 
                        fontSize: 6,
                        cellPadding: 0.3,
                        overflow: 'linebreak',
                        lineWidth: 0.1,
                        lineColor: [200, 200, 200]
                    },
                    headStyles: {
                        fillColor: [229, 231, 235],
                        textColor: [0, 0, 0],
                        fontStyle: 'bold',
                        halign: 'center',
                        fontSize: 6,
                        cellPadding: 0.5
                    },
                    columnStyles: columnStyles,
                    alternateRowStyles: {
                        fillColor: [249, 250, 251]
                    },
                    theme: 'grid',
                    margin: { left: 5, right: 5, top: 33, bottom: 10 },
                    tableWidth: 'auto',
                    didDrawPage: (data) => {
                        // Add header on each new page
                        if (data.pageNumber > pageNum) {
                            pageNum = data.pageNumber;
                            addHeader(doc, pageNum);
                        }
                        
                        // Add footer
                        doc.setFontSize(7);
                        doc.setFont(undefined, 'normal');
                        doc.text('Generated: ' + new Date().toLocaleString(), 10, pageHeight - 5);
                        doc.text('* Under 1 yr  ** 0-12 mos (FIC)  *** 13-23 mos (CIC)', pageWidth / 2, pageHeight - 5, { align: 'center' });
                    },
                    // Highlight TOTAL row
                    willDrawCell: (data) => {
                        if (data.row.index === bodyData.length - 1 && data.section === 'body') {
                            doc.setFillColor(254, 243, 199); // Yellow background for TOTAL
                            doc.setFont(undefined, 'bold');
                        }
                    }
                });
                
                // Add footnotes on last page
                const finalY = doc.lastAutoTable.finalY + 5;
                doc.setFontSize(7);
                doc.setFont(undefined, 'bold');
                doc.text('Legend:', 10, finalY);
                
                doc.setFont(undefined, 'normal');
                doc.setFontSize(6);
                const footnotes = [
                    '• FIC (Fully Immunized Child): Completed BCG, HepB, Pentavalent (3), OPV (3), MMR (2) before 12 months',
                    '• CIC (Completely Immunized Child): Completed all FIC vaccines plus school-based vaccines by 13-23 months',
                    '• Percentages calculated using target age group eligible population as denominator',
                    '• External vaccinations (administered elsewhere) are excluded from counts'
                ];
                
                let yPos = finalY + 4;
                footnotes.forEach(note => {
                    doc.text(note, 10, yPos);
                    yPos += 3;
                });
                
                // Save PDF
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const startMonth = monthNames[{{ $monthStart ?? 1 }} - 1];
                const endMonth = monthNames[{{ $monthEnd ?? 12 }} - 1];
                const filename = `Vaccination_Report_{{ $year ?? date("Y") }}_${startMonth}-${endMonth}_${new Date().getTime()}.pdf`;
                doc.save(filename);
                
            } catch (error) {
                console.error('PDF Export Error:', error);
                alert('Failed to export PDF. Error: ' + error.message);
            }
        }
        
        // Enhanced Excel Export with Multi-Sheet Workbook
        function exportToExcel() {
            try {
                if (!window.XLSX) {
                    alert('Excel export library not loaded. Please refresh the page and try again.');
                    return;
                }
                
                const wb = XLSX.utils.book_new();
                
                // ========== SHEET 1: DATA SHEET ==========
                const dataSheet = createDataSheet();
                XLSX.utils.book_append_sheet(wb, dataSheet, 'Vaccination Data');
                
                // ========== SHEET 2: METADATA SHEET ==========
                const metadataSheet = createMetadataSheet();
                XLSX.utils.book_append_sheet(wb, metadataSheet, 'Report Metadata');
                
                // ========== SHEET 3: SUMMARY SHEET ==========
                const summarySheet = createSummarySheet();
                XLSX.utils.book_append_sheet(wb, summarySheet, 'Summary');
                
                // ========== SHEET 4: FOOTNOTES SHEET ==========
                const footnotesSheet = createFootnotesSheet();
                XLSX.utils.book_append_sheet(wb, footnotesSheet, 'Footnotes');
                
                // Save workbook
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const startMonth = monthNames[{{ $monthStart ?? 1 }} - 1];
                const endMonth = monthNames[{{ $monthEnd ?? 12 }} - 1];
                const filename = `Vaccination_Report_{{ $year ?? date("Y") }}_${startMonth}-${endMonth}_${new Date().getTime()}.xlsx`;
                XLSX.writeFile(wb, filename);
                
            } catch (error) {
                console.error('Excel Export Error:', error);
                alert('Failed to export Excel. Error: ' + error.message);
            }
        }
        
        // Create Data Sheet with formatting
        function createDataSheet() {
            const table = document.getElementById('vaccinationReportTable');
            const ws = XLSX.utils.table_to_sheet(table, { raw: false });
            
            // Add title header rows
            XLSX.utils.sheet_add_aoa(ws, [
                ['CHILD CARE PROGRAM'],
                ['Immunization Services for Newborns, Infants and School-Aged Children/Adolescents cont.'],
                ['{{ $dateRange ?? "" }}'],
                ['CALAUAN, LAGUNA'],
                ['Report Generated: ' + new Date().toLocaleString()],
                []
            ], { origin: 'A1' });
            
            // Set column widths
            const colWidths = [
                { wch: 18 }, // AREA
                { wch: 12 }, { wch: 12 }, { wch: 12 }, // Eligible Population columns
            ];
            
            // Add widths for vaccine columns (M/F/T/% = 4 columns × 26 vaccine doses including IPV 2 Routine/Catch-up)
            for (let i = 0; i < 104; i++) {
                colWidths.push({ wch: 8 });
            }
            ws['!cols'] = colWidths;
            
            // Style header cells
            const range = XLSX.utils.decode_range(ws['!ref']);
            for (let R = 0; R <= 5; R++) {
                for (let C = range.s.c; C <= range.e.c; C++) {
                    const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
                    if (!ws[cellAddress]) continue;
                    
                    ws[cellAddress].s = {
                        font: { bold: true, sz: R === 0 ? 14 : 11 },
                        alignment: { horizontal: 'center', vertical: 'center' },
                        fill: { fgColor: { rgb: 'E5E7EB' } }
                    };
                }
            }
            
            // Freeze panes (first column and header rows)
            ws['!freeze'] = { xSplit: 1, ySplit: 8 };
            
            return ws;
        }
        
        // Create Metadata Sheet
        function createMetadataSheet() {
            const metadata = [
                ['VACCINATION REPORT METADATA'],
                [],
                ['Report Information'],
                ['Report Type:', 'Live Vaccination Report'],
                ['Municipality:', 'Calauan, Laguna'],
                ['Province:', 'Laguna'],
                ['Year:', '{{ $year ?? date("Y") }}'],
                ['Month Range:', '{{ $monthStart ?? 1 }} to {{ $monthEnd ?? 12 }}'],
                ['Barangay Filter:', '{{ $barangayFilter ?? "All Barangays" }}'],
                ['Generated Date:', new Date().toLocaleString()],
                ['Generated By:', 'Health Worker System'],
                [],
                ['Data Coverage'],
                ['Total Barangays:', @json(count($reportData ?? []))],
                ['Vaccines Tracked:', '11 vaccines (25 doses total)'],
                ['Age Groups:', 'Under 1 year, 0-12 months, 13-23 months, Grade 1, Grade 7'],
                [],
                ['System Information'],
                ['System Name:', 'Infant Immunization System'],
                ['Version:', '1.0'],
                ['Data Source:', 'vaccination_transactions table'],
                ['Calculation Method:', 'Real-time aggregation']
            ];
            
            const ws = XLSX.utils.aoa_to_sheet(metadata);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 25 },
                { wch: 50 }
            ];
            
            // Style title
            ws['A1'].s = {
                font: { bold: true, sz: 14 },
                alignment: { horizontal: 'center' },
                fill: { fgColor: { rgb: '4CAF50' } }
            };
            
            // Style section headers
            ['A3', 'A13', 'A18'].forEach(cell => {
                if (ws[cell]) {
                    ws[cell].s = {
                        font: { bold: true, sz: 12 },
                        fill: { fgColor: { rgb: 'E5E7EB' } }
                    };
                }
            });
            
            return ws;
        }
        
        // Create Summary Sheet
        function createSummarySheet() {
            const reportData = @json($reportData ?? []);
            const totalRow = reportData.find(row => row.barangay === 'TOTAL') || {};
            
            const summary = [
                ['VACCINATION REPORT SUMMARY'],
                [],
                ['Overall Statistics'],
                ['Metric', 'Value'],
                ['Total Eligible Population (Under 1 yr)', totalRow.eligible_population_under_1_year || 0],
                ['Total Eligible Population (0-12 mos)', totalRow.eligible_population_0_12_months || 0],
                ['Total Eligible Population (13-23 mos)', totalRow.eligible_population_13_23_months || 0],
                [],
                ['Immunization Status'],
                ['FIC (Fully Immunized Children)', (totalRow.fic?.total_count || 0) + ' (' + (totalRow.fic?.percentage || 0).toFixed(2) + '%)'],
                ['CIC (Completely Immunized Children)', (totalRow.cic?.total_count || 0) + ' (' + (totalRow.cic?.percentage || 0).toFixed(2) + '%)'],
                [],
                ['Top 5 Vaccines by Coverage'],
            ];
            
            // Calculate top vaccines
            if (totalRow.vaccines) {
                const vaccines = Object.entries(totalRow.vaccines)
                    .map(([name, data]) => ({
                        name: name,
                        total: data.total_count || 0,
                        percentage: data.percentage || 0
                    }))
                    .sort((a, b) => b.percentage - a.percentage)
                    .slice(0, 5);
                
                summary.push(['Vaccine', 'Total', 'Coverage %']);
                vaccines.forEach(v => {
                    summary.push([v.name, v.total, v.percentage.toFixed(2) + '%']);
                });
            }
            
            const ws = XLSX.utils.aoa_to_sheet(summary);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 40 },
                { wch: 20 },
                { wch: 15 }
            ];
            
            // Style title
            ws['A1'].s = {
                font: { bold: true, sz: 14, color: { rgb: 'FFFFFF' } },
                alignment: { horizontal: 'center' },
                fill: { fgColor: { rgb: '2196F3' } }
            };
            
            return ws;
        }
        
        // Create Footnotes Sheet
        function createFootnotesSheet() {
            const footnotes = [
                ['VACCINATION REPORT FOOTNOTES & DEFINITIONS'],
                [],
                ['Asterisk Notations'],
                ['Symbol', 'Description'],
                ['*', 'Denominator: Eligible Population (Under 1 year) - Used for infant vaccines'],
                ['**', 'FIC (Fully Immunized Child) - Children who completed BCG, HepB, Pentavalent (3), OPV (3), MMR (2) before 12 months'],
                ['***', 'CIC (Completely Immunized Child) - Children aged 13-23 months who completed all FIC + school vaccines'],
                [],
                ['Vaccine Acronyms'],
                ['Acronym', 'Full Name', 'Doses'],
                ['BCG', 'Bacillus Calmette-Guérin Vaccine', '1'],
                ['HepB', 'Hepatitis B', '1'],
                ['DPT-HIB-HepB', 'Pentavalent (Diphtheria, Pertussis, Tetanus, Haemophilus influenzae type B, Hepatitis B)', '3'],
                ['OPV', 'Oral Polio', '3'],
                ['IPV', 'Inactivated Polio', '2'],
                ['PCV', 'Pneumococcal Conjugate', '3'],
                ['MMR', 'Measles, Mumps, Rubella', '2'],
                ['MCV1', 'Measles Containing (Grade 1)', '1'],
                ['MCV2', 'Measles Containing (Grade 7)', '2'],
                ['TD', 'Tetanus Diphtheria', '2'],
                ['HPV', 'Human Papillomavirus', '2'],
                [],
                ['Target Age Groups'],
                ['Age Group', 'Description'],
                ['Under 1 year', 'Children aged 0-11 months (for infant vaccines)'],
                ['0-12 months', 'Children aged 0-12 months (for FIC calculation and MMR)'],
                ['13-23 months', 'Children aged 13-23 months (for CIC calculation)'],
                ['Grade 1', 'Children aged 6-7 years (72-84 months)'],
                ['Grade 7', 'Children aged 12-13 years (144-156 months)'],
                [],
                ['Data Notes'],
                ['• Percentages are calculated using target age group eligible population as denominator'],
                ['• FIC percentage uses 0-12 months eligible population'],
                ['• CIC percentage uses 13-23 months eligible population'],
                ['• External vaccinations (administered_elsewhere = true) are excluded from counts'],
                ['• TOTAL row shows aggregated data across all barangays'],
                [],
                ['For questions or clarifications, contact the Health Office of Calauan, Laguna']
            ];
            
            const ws = XLSX.utils.aoa_to_sheet(footnotes);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 20 },
                { wch: 80 },
                { wch: 10 }
            ];
            
            // Style title
            ws['A1'].s = {
                font: { bold: true, sz: 14, color: { rgb: 'FFFFFF' } },
                alignment: { horizontal: 'center' },
                fill: { fgColor: { rgb: 'FF9800' } }
            };
            
            // Style section headers
            ['A3', 'A9', 'A23', 'A31'].forEach(cell => {
                if (ws[cell]) {
                    ws[cell].s = {
                        font: { bold: true, sz: 12 },
                        fill: { fgColor: { rgb: 'FFF3E0' } }
                    };
                }
            });
            
            return ws;
        }
    </script>
@endsection
