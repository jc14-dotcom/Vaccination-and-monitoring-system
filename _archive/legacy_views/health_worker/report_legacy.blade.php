@extends('layouts.responsive-layout')

@section('title', 'Current Vaccination Report')

@section('additional-styles')
<link rel="stylesheet" href="{{ asset('css/tailwind-full.css') }}">
<style>
    /* Report table styles - will be added in later phases */
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
                    
                    <!-- Status Badge -->
                    @if($dataSource === 'live')
                        <div class="mt-4">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-800 text-sm font-semibold ring-1 ring-green-300">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                Live Data
                            </span>
                        </div>
                    @else
                        <div class="mt-4">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold ring-1 ring-blue-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Locked Report
                            </span>
                        </div>
                    @endif
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
                                @for($y = date('Y') + 1; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        
                        <!-- Quarter Filter -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Quarter Range
                            </label>
                            <div class="grid grid-cols-4 gap-2">
                                @php
                                    $qStart = $quarterStart ?? 1;
                                    $qEnd = $quarterEnd ?? 4;
                                @endphp
                                @for($q = 1; $q <= 4; $q++)
                                    <label class="flex items-center justify-center p-2 rounded-lg border-2 transition cursor-pointer hover:bg-green-50 {{ $q >= $qStart && $q <= $qEnd ? 'border-green-500 bg-green-100' : 'border-gray-300 bg-white' }}">
                                        <input type="checkbox" name="quarters[]" value="{{ $q }}" class="quarter-checkbox hidden" {{ $q >= $qStart && $q <= $qEnd ? 'checked' : '' }}>
                                        <span class="text-sm font-semibold {{ $q >= $qStart && $q <= $qEnd ? 'text-green-700' : 'text-gray-600' }}">Q{{ $q }}</span>
                                    </label>
                                @endfor
                            </div>
                            <input type="hidden" name="quarter_start" id="quarter_start" value="{{ $qStart }}">
                            <input type="hidden" name="quarter_end" id="quarter_end" value="{{ $qEnd }}">
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
                            <!-- Main header row - vaccine names will be added in Phase 7.4 -->
                            <tr class="bg-gray-200">
                                <th rowspan="2" class="border border-gray-400 px-4 py-3 text-center font-bold text-gray-900 align-middle sticky left-0 bg-gray-200 z-10">
                                    AREA
                                </th>
                                <th rowspan="2" class="border border-gray-400 px-2 py-3 text-center font-bold text-gray-900 text-xs align-middle">
                                    ELIGIBLE<br>POPULATION<br><span class="text-[10px] font-normal">(Under 1 yr)</span>
                                </th>
                                <!-- Vaccine columns will be added in Phase 7.4 -->
                                <th colspan="4" class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">
                                    Vaccines (M/F/T/%)...
                                </th>
                            </tr>
                            
                            <!-- Sub-header row - M/F/T/% columns will be added in Phase 7.4 -->
                            <tr class="bg-gray-100">
                                <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">M</th>
                                <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">F</th>
                                <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">T</th>
                                <th class="border border-gray-400 px-2 py-2 text-center font-semibold text-gray-700 text-xs">%</th>
                            </tr>
                        </thead>
                        
                        <!-- Table Body -->
                        <tbody>
                            @if(isset($reportData) && count($reportData) > 0)
                                @foreach($reportData as $row)
                                    <tr class="{{ $row['barangay'] === 'TOTAL' ? 'bg-yellow-100 font-bold' : 'hover:bg-gray-50' }}">
                                        <!-- Barangay Name -->
                                        <td class="border border-gray-400 px-4 py-2 text-left font-semibold text-gray-900 sticky left-0 {{ $row['barangay'] === 'TOTAL' ? 'bg-yellow-100' : 'bg-white' }} z-10">
                                            {{ $row['barangay'] }}
                                        </td>
                                        
                                        <!-- Eligible Population -->
                                        <td class="border border-gray-400 px-2 py-2 text-center text-gray-800">
                                            {{ number_format($row['eligible_population'] ?? 0) }}
                                        </td>
                                        
                                        <!-- Vaccine data columns will be added in Phase 7.5 -->
                                        <td class="border border-gray-400 px-2 py-2 text-center text-gray-500">-</td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-gray-500">-</td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-gray-500">-</td>
                                        <td class="border border-gray-400 px-2 py-2 text-center text-gray-500">-</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="border border-gray-400 px-4 py-8 text-center text-gray-500">
                                        No data available for the selected period.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        
                    </table>
                </div>
                
                <!-- Action buttons will be added in Phase 7.6 -->
                <div class="mt-4 flex items-center justify-end gap-3">
                    <button class="px-4 py-2 rounded-lg border border-gray-300 text-gray-500 text-sm cursor-not-allowed" disabled>
                        Export PDF (Phase 7.7)
                    </button>
                    <button class="px-4 py-2 rounded-lg border border-gray-300 text-gray-500 text-sm cursor-not-allowed" disabled>
                        Export Excel (Phase 7.7)
                    </button>
                </div>
                
            </div>
            
        </div>
    </div>
@endsection

@section('additional-scripts')
    <script>
        // Quarter selection logic
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.quarter-checkbox');
            const startInput = document.getElementById('quarter_start');
            const endInput = document.getElementById('quarter_end');
            
            // Update hidden inputs when checkboxes change
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateQuarterRange();
                    updateCheckboxStyles();
                });
            });
            
            function updateQuarterRange() {
                const checked = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => parseInt(cb.value))
                    .sort((a, b) => a - b);
                
                if (checked.length > 0) {
                    startInput.value = checked[0];
                    endInput.value = checked[checked.length - 1];
                    
                    // Auto-check quarters in between for continuous range
                    for (let i = checked[0]; i <= checked[checked.length - 1]; i++) {
                        const cb = document.querySelector(`.quarter-checkbox[value="${i}"]`);
                        if (cb) cb.checked = true;
                    }
                } else {
                    // Default to Q1-Q4 if nothing selected
                    startInput.value = 1;
                    endInput.value = 4;
                    checkboxes.forEach(cb => cb.checked = true);
                }
                
                updateCheckboxStyles();
            }
            
            function updateCheckboxStyles() {
                checkboxes.forEach(cb => {
                    const label = cb.closest('label');
                    const span = label.querySelector('span');
                    
                    if (cb.checked) {
                        label.classList.remove('border-gray-300', 'bg-white');
                        label.classList.add('border-green-500', 'bg-green-100');
                        span.classList.remove('text-gray-600');
                        span.classList.add('text-green-700');
                    } else {
                        label.classList.remove('border-green-500', 'bg-green-100');
                        label.classList.add('border-gray-300', 'bg-white');
                        span.classList.remove('text-green-700');
                        span.classList.add('text-gray-600');
                    }
                });
            }
        });
        
        // Reset filters
        function resetFilters() {
            document.getElementById('year').value = {{ date('Y') }};
            document.querySelectorAll('.quarter-checkbox').forEach(cb => cb.checked = true);
            document.getElementById('quarter_start').value = 1;
            document.getElementById('quarter_end').value = 4;
            document.getElementById('barangay').value = '';
            document.getElementById('filterForm').submit();
        }
    </script>
@endsection
    #vaccinationTable tbody td:nth-child(14),
    #vaccinationTable tbody td:nth-child(24),
    #vaccinationTable tbody td:nth-child(25),
    #vaccinationTable tbody td:nth-child(26),
    #vaccinationTable tbody td:nth-child(36),
    #vaccinationTable tbody td:nth-child(37),
    #vaccinationTable tbody td:nth-child(38),
    #vaccinationTable tbody td:nth-child(48),
    #vaccinationTable tbody td:nth-child(49),
    #vaccinationTable tbody td:nth-child(50) { background-color: #F3F4F6; }

    /* Shade TOTAL columns (screen) */
    #vaccinationTable thead tr:nth-child(2) th:nth-child(49),
    #vaccinationTable thead tr:nth-child(2) th:nth-child(50),
    #vaccinationTable thead tr:nth-child(2) th:nth-child(51) { background-color: #E5E7EB; }
    #vaccinationTable tbody td:nth-child(51),
    #vaccinationTable tbody td:nth-child(52),
    #vaccinationTable tbody td:nth-child(53),
    #vaccinationTable tbody td.total-cell { background-color: #F3F4F6; }
    /* Responsive font tweaks */
    @media (max-width: 768px) {
        #vaccinationTable { font-size: 10px; }
        .month-header { font-size: 9px; }
        .sub-header { font-size: 9px; }
        #vaccinationTable th:first-child, #vaccinationTable td:first-child {
            width: 120px; min-width: 120px; max-width: 120px;
        }
        #vaccinationTable th:nth-child(2), #vaccinationTable td:nth-child(2) {
            width: 120px; min-width: 120px; max-width: 120px;
        }
        #vaccinationTable th:not(:first-child):not(:nth-child(2)), #vaccinationTable td:not(:first-child):not(:nth-child(2)) {
            width: 28px; min-width: 28px; max-width: 28px;
        }
    }
    @media (max-width: 1024px) and (min-width: 769px) {
        #vaccinationTable th:first-child, #vaccinationTable td:first-child {
            width: 140px; min-width: 140px; max-width: 140px;
        }
        #vaccinationTable th:nth-child(2), #vaccinationTable td:nth-child(2) {
            width: 130px; min-width: 130px; max-width: 130px;
        }
        #vaccinationTable th:not(:first-child):not(:nth-child(2)), #vaccinationTable td:not(:first-child):not(:nth-child(2)) {
            width: 30px; min-width: 30px; max-width: 30px;
        }
    }
    /* Print: avoid sticky issues in export preview/print */
    @media print {
        #vaccinationTable th:first-child, #vaccinationTable td:first-child { position: static; border-right: 2px solid #6B7280; }
    }
    </style>
@endsection

@section('content')
    <div class="min-h-screen w-full">
        <!-- Main content area -->
        <div class="w-full max-w-full mx-auto px-4 sm:px-8 xl:px-10 pb-8">
            <!-- Banner -->
            <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
                <div class="relative px-5 py-6 text-white flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 ring-1 ring-white/25">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-xl md:text-2xl font-semibold leading-tight">Vaccination Schedule Report</h1>
                        <p class="text-xs text-white/85">Export yearly and barangay-specific reports</p>
                    </div>
                </div>
            </section>

            <!-- Filter Section -->
            <div class="flex flex-wrap items-end gap-3 md:gap-4 mb-4">
                <div class="flex flex-col">
                    <label for="yearFilter" class="text-sm font-semibold text-gray-700">Filter by Year</label>
                    <select id="yearFilter" class="w-48 sm:w-56 md:w-64 px-3 py-2 border-2 border-gray-300 rounded-lg text-sm focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600/20"></select>
                </div>
                <div class="flex flex-col">
                    <label for="barangayFilter" class="text-sm font-semibold text-gray-700">Barangay</label>
                    <select id="barangayFilter" class="w-56 sm:w-64 md:w-72 px-3 py-2 border-2 border-gray-300 rounded-lg text-sm focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600/20">
                        <option value="">All Barangays</option>
                        <option value="Balayhangin">Balayhangin</option>
                        <option value="Bangyas">Bangyas</option>
                        <option value="Dayap">Dayap</option>
                        <option value="Hanggan">Hanggan</option>
                        <option value="Imok">Imok</option>
                        <option value="Kanluran">Kanluran</option>
                        <option value="Lamot 1">Lamot 1</option>
                        <option value="Lamot 2">Lamot 2</option>
                        <option value="Limao">Limao</option>
                        <option value="Mabacan">Mabacan</option>
                        <option value="Masiit">Masiit</option>
                        <option value="Paliparan">Paliparan</option>
                        <option value="Perez">Perez</option>
                        <option value="Prinza">Prinza</option>
                        <option value="San Isidro">San Isidro</option>
                        <option value="Santo Tomas">Santo Tomas</option>
                        <option value="Silangan">Silangan</option>
                    </select>
                </div>
                <div class="flex-1"></div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button type="button" onclick="exportToPDF()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 text-white px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-red-600/30 hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600/50">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 2h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm9 0v5h5"/></svg>
                        Export to PDF
                    </button>
                    <button type="button" onclick="exportToExcel()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 text-white px-4 py-2 text-sm font-semibold shadow-sm ring-1 ring-green-600/30 hover:bg-green-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-600/50">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4zM7 7l10 10M17 7L7 17"/></svg>
                        Export to Excel
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto rounded-lg ring-1 ring-gray-200 shadow-sm bg-white">
                <table id="vaccinationTable" class="bg-white w-full">
                    <thead>
                        <tr>
                            <th rowspan="2">Indicators</th>
                            <th rowspan="2">Eligible Population</th>
                            <th colspan="3" class="month-header">JAN</th>
                            <th colspan="3" class="month-header">FEB</th>
                            <th colspan="3" class="month-header">MAR</th>
                            <th colspan="3" class="month-header">1st Qtr</th>
                            <th colspan="3" class="month-header">APR</th>
                            <th colspan="3" class="month-header">MAY</th>
                            <th colspan="3" class="month-header">JUN</th>
                            <th colspan="3" class="month-header">2nd Qtr</th>
                            <th colspan="3" class="month-header">JUL</th>
                            <th colspan="3" class="month-header">AUG</th>
                            <th colspan="3" class="month-header">SEP</th>
                            <th colspan="3" class="month-header">3rd Qtr</th>
                            <th colspan="3" class="month-header">OCT</th>
                            <th colspan="3" class="month-header">NOV</th>
                            <th colspan="3" class="month-header">DEC</th>
                            <th colspan="3" class="month-header">4th Qtr</th>
                            <th colspan="3" class="month-header">TOTAL</th>
                        </tr>
                        <tr>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                            <th class="sub-header" title="Male">M</th>
                            <th class="sub-header" title="Female">F</th>
                            <th class="sub-header" title="Total">T</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="shaded-row">
                            <td>Part 1 - Immunization and Nutrition Services</td>
                            <td colspan="51"></td>
                        </tr>
                        <tr>
                            <td>A. Immunization Services</td>
                            <td colspan="51"></td>
                        </tr>
                        <tr>
                            <td>1. Newborn/Infants Vaccinated With:</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">BCG - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">HepB1 w/in 24 hrs - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">Child Protected at Birth - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">DPT-Hib-HepB 1 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">DPT-Hib-HepB 2 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">DPT-Hib-HepB 3 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">OPV 1 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">OPV 2 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">OPV 3 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">IPV 1 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">IPV 2 (routine) - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">IPV 2 (catch-up) - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">PCV 1 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">PCV 2 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">PCV 3 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">MCV 1 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="vaccine-header">MCV 2 - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td class="category-header">2. Fully Immunized Child - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td> 
                        </tr>
                        <tr>
                            <td class="category-header">3. Completely Immunized Child (13-23 months) - Total</td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td>
                            <td></td><td></td><td></td> 
                        </tr>
                        <tr>
                            <td class="category-header">4. School-Based Immunnization:</td>
                            <td colspan="51"></td>
                        </tr>
                        <tr class="shaded-row" data-wide="true">
                            <td class="category-header">4.1 Total Grade 1 Learners:</td>
                            <td></td>
                            <td colspan="39" class="school-shade"></td>
                            <td></td><td></td><td></td>
                            <td colspan="3" class="school-shade"></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr class="shaded-row" data-wide="true">
                            <td class="vaccine-header">• Td - Total</td>
                            <td></td>
                            <td colspan="39" class="school-shade"></td>
                            <td></td><td></td><td></td>
                            <td colspan="3" class="school-shade"></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr class="shaded-row" data-wide="true">
                            <td class="vaccine-header">• MR - Total</td>
                            <td></td>
                            <td colspan="39" class="school-shade"></td>
                            <td></td><td></td><td></td>
                            <td colspan="3" class="school-shade"></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr class="shaded-row" data-wide="true">
                            <td class="category-header">4.2 Total Grade 7 Learners:</td>
                            <td></td>
                            <td colspan="39" class="school-shade"></td>
                            <td></td><td></td><td></td>
                            <td colspan="3" class="school-shade"></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr class="shaded-row" data-wide="true">
                            <td class="vaccine-header">• Td - Total</td>
                            <td></td>
                            <td colspan="39" class="school-shade"></td>
                            <td></td><td></td><td></td>
                            <td colspan="3" class="school-shade"></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        <tr class="shaded-row" data-wide="true">
                            <td class="vaccine-header">• MR - Total</td>
                            <td></td>
                            <td colspan="39" class="school-shade"></td>
                            <td></td><td></td><td></td>
                            <td colspan="3" class="school-shade"></td>
                            <td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.min.js"></script>
@endsection

@section('additional-scripts')
    <script>
        // Modal controls
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function logout() {
            window.location.href = '/logout'; // Replace with actual logout route
        }

        // Dynamically populate year dropdown
        function populateYearDropdown() {
            const yearSelect = document.getElementById('yearFilter');
            const currentYear = new Date().getFullYear();
            yearSelect.innerHTML = '';

            for (let year = currentYear; year >= currentYear - 2; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.text = year;
                yearSelect.appendChild(option);
            }
        }

        // Normalize THEAD subcells (ensure TOTAL has M/F/T) and TBody cells
        function normalizeTableColumns() {
            const table = document.getElementById('vaccinationTable');
            if (!table) return;
            const thead = table.tHead;
            if (!thead || thead.rows.length < 2) return;
            const topHeader = thead.rows[0];
            const subHeader = thead.rows[1];

            // Compute expected detail columns by summing colspans of top header cells excluding the first two (Indicators + Eligible Population)
            let detailCols = 0;
            Array.from(topHeader.cells).forEach((cell, idx) => {
                if (idx < 2) return; // skip Indicators and Eligible Population
                const span = parseInt(cell.getAttribute('colspan') || '1', 10);
                detailCols += isNaN(span) ? 1 : span;
            });

            // If subHeader has fewer cells than expected (e.g., TOTAL missing M/F/T), append missing M/F/T
            let missingSub = detailCols - subHeader.cells.length;
            const mft = ['M', 'F', 'T'];
            let mftIndex = 0;
            while (missingSub > 0) {
                const th = document.createElement('th');
                th.className = 'sub-header';
                th.textContent = mft[mftIndex % 3];
                th.title = th.textContent === 'M' ? 'Male' : th.textContent === 'F' ? 'Female' : 'Total';
                subHeader.appendChild(th);
                mftIndex++;
                missingSub--;
            }

            // Normalize tbody rows to match expected (Indicators + Eligible Population + detailCols)
            const expected = 2 + detailCols;
            const tbody = table.tBodies[0];
            if (!tbody) return;
            Array.from(tbody.rows).forEach(row => {
                // Ensure Eligible Population blank cell is present at column 2 (index 1)
                if (row.cells.length >= 2 && row.cells[1].hasAttribute('colspan')) {
                    const ep = document.createElement('td');
                    row.insertBefore(ep, row.cells[1]);
                } else if (row.cells.length === 1) {
                    const ep = document.createElement('td');
                    row.appendChild(ep);
                }

                // compute current columns considering colSpan
                let current = 0;
                Array.from(row.cells).forEach(cell => {
                    const span = parseInt(cell.getAttribute('colspan') || '1', 10);
                    current += isNaN(span) ? 1 : span;
                });
                const hasWideColspan = Array.from(row.cells).some(c => parseInt(c.getAttribute('colspan') || '1', 10) > 1);
                const isWideRow = row.hasAttribute('data-wide');
                if (!hasWideColspan && current === expected - 1) {
                    // Likely missing the Eligible Population cell; insert after the first cell
                    const ep = document.createElement('td');
                    if (row.cells.length > 0) {
                        row.insertBefore(ep, row.cells[1] || null);
                    } else {
                        row.appendChild(ep);
                    }
                    current += 1;
                }
                if (!isWideRow) {
                    const deficit = expected - current;
                    for (let i = 0; i < deficit; i++) {
                        const td = document.createElement('td');
                        row.appendChild(td);
                    }
                }
            });
        }

        // Sum monthly values into the TOTAL M/F/T cells, ignoring quarter groups
        function computeTotals() {
            const table = document.getElementById('vaccinationTable');
            if (!table || !table.tHead || !table.tBodies.length) return;
            const subHeader = table.tHead.rows[1];
            const labels = Array.from(subHeader.cells).map(c => c.textContent.trim());
            // Build indices for monthly M/F/T and total columns (skip quarters)
            const monthGroups = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
            const groupMap = [];
            let topIdx = 0;
            Array.from(table.tHead.rows[0].cells).forEach((cell, idx) => {
                if (idx < 2) return; // skip Indicators and Eligible Population
                const span = parseInt(cell.getAttribute('colspan') || '1', 10);
                const label = cell.textContent.trim().toUpperCase();
                for (let i = 0; i < span; i++) groupMap.push(label);
                topIdx += span;
            });

            // Subheader aligns with detail columns; compute absolute column indices (1-based including Indicators)
            const monthlyIdx = { M: [], F: [], T: [] };
            let absCol = 3; // start at first detail column (after Indicators + Eligible Population)
            for (let i = 0; i < labels.length; i++, absCol++) {
                const parent = groupMap[i];
                if (!monthGroups.includes(parent)) continue; // skip quarters and TOTAL here
                const key = labels[i] === 'M' ? 'M' : labels[i] === 'F' ? 'F' : labels[i] === 'T' ? 'T' : null;
                if (key) monthlyIdx[key].push(absCol);
            }

            // Find TOTAL columns (last 3 cells in subheader)
            const totalCols = { M: [], F: [], T: [] };
            absCol = 3;
            for (let i = 0; i < labels.length; i++, absCol++) {
                const parent = groupMap[i];
                if (parent !== 'TOTAL') continue;
                const key = labels[i] === 'M' ? 'M' : labels[i] === 'F' ? 'F' : labels[i] === 'T' ? 'T' : null;
                if (key) totalCols[key].push(absCol);
            }

            const tbody = table.tBodies[0];
            Array.from(tbody.rows).forEach(row => {
                // Ignore category/separator rows that contain any wide colspan cell
                if (row.hasAttribute('data-wide') || Array.from(row.cells).some(c => parseInt(c.getAttribute('colspan') || '1', 10) > 1)) return;
                const getNum = (td) => {
                    const v = (td?.textContent || '').trim();
                    const n = parseInt(v, 10);
                    return isNaN(n) ? 0 : n;
                };
                const sumFor = (indices) => indices.reduce((sum, idx) => sum + getNum(row.cells[idx-1]), 0);
                const mSum = sumFor(monthlyIdx.M);
                const fSum = sumFor(monthlyIdx.F);
                const tSum = sumFor(monthlyIdx.T);
                // Write into TOTAL columns (if present)
                if (totalCols.M.length) row.cells[totalCols.M[0]-1].textContent = mSum === 0 ? '' : String(mSum);
                if (totalCols.F.length) row.cells[totalCols.F[0]-1].textContent = fSum === 0 ? '' : String(fSum);
                if (totalCols.T.length) row.cells[totalCols.T[0]-1].textContent = tSum === 0 ? '' : String(tSum);
            });
        }

        // Call on page load
        document.addEventListener('DOMContentLoaded', () => {
            populateYearDropdown();
            normalizeTableColumns();
            computeTotals();
            positionEligiblePopulationOverlay();
            const table = document.getElementById('vaccinationTable');
            table.classList.add('ep-overlay-active');
            window.addEventListener('resize', positionEligiblePopulationOverlay);
        });

        function positionEligiblePopulationOverlay() {
            const table = document.getElementById('vaccinationTable');
            const thead = table.tHead;
            const tbody = table.tBodies[0];
            if (!thead || !tbody) return;
            // Find the second column header cell in the first row (Eligible Population)
            const topRow = thead.rows[0];
            const epTh = topRow?.cells[1];
            if (!epTh) return;
            const tableRect = table.getBoundingClientRect();
            const epRect = epTh.getBoundingClientRect();
            const bodyRect = tbody.getBoundingClientRect();
            const left = epRect.left - tableRect.left;
            const width = epRect.width;
            const top = bodyRect.top - tableRect.top;
            const height = bodyRect.height;
            table.style.setProperty('--ep-overlay-left', left + 'px');
            table.style.setProperty('--ep-overlay-width', width + 'px');
            table.style.setProperty('--ep-overlay-top', top + 'px');
            table.style.setProperty('--ep-overlay-height', height + 'px');
        }

        // PDF Export
        function exportToPDF() {
            try {
                // Temporarily disable EP overlay for export parsing
                const table = document.getElementById('vaccinationTable');
                const hadOverlay = table.classList.contains('ep-overlay-active');
                if (hadOverlay) table.classList.remove('ep-overlay-active');
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                // Get selected year and barangay
                const year = document.getElementById('yearFilter').value || 'Not Specified';
                const barangay = document.getElementById('barangayFilter').value || 'All Barangays';

                // Add title and filters
                doc.setFontSize(16);
                doc.text('Vaccination Schedule Report', 14, 10);
                doc.setFontSize(12);
                doc.text(`Year: ${year}`, 14, 18);
                doc.text(`Barangay: ${barangay}`, 14, 26);

                // Generate table
                const qCols = new Set([11,12,13,23,24,25,35,36,37,47,48,49]); // zero-based indices
                const totalCols = new Set([50,51,52]);
                doc.autoTable({
                    html: '#vaccinationTable',
                    startY: 34, // Adjusted to accommodate filter text
                    theme: 'grid',
                    headStyles: {
                        fillColor: [200, 200, 200],
                        textColor: [0, 0, 0],
                        fontSize: 8,
                        lineColor: [0, 0, 0],
                        lineWidth: 0.2
                    },
                    bodyStyles: {
                        fontSize: 7,
                        lineColor: [0, 0, 0],
                        lineWidth: 0.2
                    },
                    columnStyles: {
                        0: { cellWidth: 48 }, // Indicators
                        1: { cellWidth: 40 }  // Eligible Population
                    },
                    styles: {
                        lineColor: [0,0,0],
                        lineWidth: 0.2
                    },
                    margin: { top: 34 },
                    didParseCell: function(data) {
                        const col = data.column.index;
                        // Shade quarters and TOTAL columns
                        if (qCols.has(col)) {
                            data.cell.styles.fillColor = [235, 238, 241]; // light gray
                        }
                        if (totalCols.has(col)) {
                            data.cell.styles.fillColor = [230, 232, 235]; // gray for TOTAL
                        }
                        // Shade school-shade blocks
                        const raw = data.cell?.raw;
                        if (raw && raw.classList && raw.classList.contains('school-shade')) {
                            data.cell.styles.fillColor = [156, 163, 175]; // gray-400
                            data.cell.styles.textColor = [0,0,0];
                        }
                    }
                });

                // Add auto-generated footer
                const finalY = doc.lastAutoTable.finalY || 34;
                doc.setFontSize(10);
                doc.setTextColor(100);
                doc.text('Note: This document is computer-generated based on vaccination records entered by health workers.', 14, finalY + 10);

                doc.save('vaccination_report.pdf');
                if (hadOverlay) {
                    // Restore overlay and reposition
                    table.classList.add('ep-overlay-active');
                    positionEligiblePopulationOverlay();
                }
            } catch (error) {
                console.error('PDF export error:', error);
                alert('Failed to export to PDF. Please check the console for details or try again.');
            }
        }

        // Excel Export
        function exportToExcel() {
            try {
                // Temporarily disable EP overlay for export parsing
                const table = document.getElementById('vaccinationTable');
                const hadOverlay = table.classList.contains('ep-overlay-active');
                if (hadOverlay) table.classList.remove('ep-overlay-active');
                if (!window.XLSX) {
                    console.error('XLSX library not loaded');
                    alert('Excel export failed: XLSX library not loaded. Please try again or contact support.');
                    return;
                }
                if (!table) {
                    console.error('Table with ID vaccinationTable not found');
                    alert('Excel export failed: Table not found.');
                    return;
                }

                const year = document.getElementById('yearFilter').value || 'Not Specified';
                const barangay = document.getElementById('barangayFilter').value || 'All Barangays';

                const ws = XLSX.utils.table_to_sheet(table, {
                    raw: true,
                    skipHeader: false,
                    dateNF: 'yyyy-mm-dd'
                });

                if (!ws || Object.keys(ws).length === 0) {
                    console.error('Worksheet is empty or invalid');
                    alert('Excel export failed: No data to export.');
                    return;
                }

                XLSX.utils.sheet_add_aoa(ws, [
                    ['Vaccination Schedule Report'],
                    [`Year: ${year}`],
                    [`Barangay: ${barangay}`],
                    []
                ], { origin: 'A1' });

                const range = XLSX.utils.decode_range(ws['!ref'] || 'A1:A1');
                const lastRow = range.e.r + 2;
                XLSX.utils.sheet_add_aoa(ws, [['Note: This document is computer-generated based on vaccination records entered by health workers.']], { origin: `A${lastRow}` });

                ws['!ref'] = XLSX.utils.encode_range({ s: { c: 0, r: 0 }, e: { c: range.e.c, r: lastRow } });

                // Apply black borders and shade quarter + TOTAL columns
                const range2 = XLSX.utils.decode_range(ws['!ref'] || 'A1:A1');
                const border = {
                    top: { style: 'thin', color: { rgb: '000000' } },
                    bottom: { style: 'thin', color: { rgb: '000000' } },
                    left: { style: 'thin', color: { rgb: '000000' } },
                    right: { style: 'thin', color: { rgb: '000000' } }
                };
                const shadeLight = { patternType: 'solid', fgColor: { rgb: 'ECEFF1' } };
                const shadeTotal = { patternType: 'solid', fgColor: { rgb: 'E6E8EB' } };
                const shadeCols = new Set([11,12,13,23,24,25,35,36,37,47,48,49]);
                const totalColsX = new Set([50,51,52]);
                for (let r = range2.s.r; r <= range2.e.r; r++) {
                    for (let c = range2.s.c; c <= range2.e.c; c++) {
                        const addr = XLSX.utils.encode_cell({ r, c });
                        if (!ws[addr]) ws[addr] = { v: '', t: 's' };
                        if (!ws[addr].s) ws[addr].s = {};
                        ws[addr].s.border = border;
                        if (shadeCols.has(c)) ws[addr].s.fill = shadeLight;
                        if (totalColsX.has(c)) ws[addr].s.fill = shadeTotal;
                    }
                }

                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Vaccination Report');

                const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
                const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                if (hadOverlay) {
                    // Restore overlay and reposition
                    table.classList.add('ep-overlay-active');
                    positionEligiblePopulationOverlay();
                }
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'vaccination_report.xlsx';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Excel export error:', error);
                alert('Failed to export to Excel. Please check the console for details or try again.');
            }
        }

        // Filter Interactivity
        document.getElementById('yearFilter').addEventListener('change', function() {
            console.log('Year selected:', this.value);
        });

        document.getElementById('barangayFilter').addEventListener('change', function() {
            console.log('Barangay selected:', this.value);
        });
    </script>
@endsection