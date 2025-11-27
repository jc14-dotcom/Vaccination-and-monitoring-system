@extends('layouts.responsive-layout')

@section('title', 'View Archived Report')

@section('additional-styles')
    @include('health_worker.report-styles')
    <style>
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
        }
        
        .toast {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid;
        }
        
        .toast.success {
            border-left-color: #10b981;
        }
        
        .toast.error {
            border-left-color: #ef4444;
        }
        
        .toast.warning {
            border-left-color: #f59e0b;
        }
        
        .toast-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
        }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .toast-message {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .toast-close {
            flex-shrink: 0;
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s;
        }
        
        .toast-close:hover {
            color: #4b5563;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
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
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .toast.hiding {
            animation: slideOutRight 0.3s ease-in forwards;
        }
    </style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('reports.history') }}" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-700 font-semibold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Report History
        </a>
    </div>

    <!-- Report Banner (READ-ONLY VERSION) -->
    <div class="report-banner bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl shadow-xl p-8 mb-6 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Left side - Report title and info -->
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold">CHILD CARE PROGRAM</h1>
                        <p class="text-white/90 text-sm">Vaccine Preventable Diseases Surveillance Report</p>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-semibold">{{ $dateRange }}</span>
                    </div>
                    
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="font-semibold">CALAUAN, LAGUNA</span>
                    </div>
                    
                    <!-- Version Badge -->
                    <div class="flex items-center gap-2 bg-blue-500/90 backdrop-blur-sm px-4 py-2 rounded-lg ring-2 ring-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="font-bold">VERSION {{ $version ?? 1 }}</span>
                    </div>
                    
                    <!-- Archived Badge -->
                    <div class="flex items-center gap-2 bg-yellow-500/90 backdrop-blur-sm px-4 py-2 rounded-lg ring-2 ring-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span class="font-bold">READ-ONLY ARCHIVE</span>
                    </div>
                    
                    @if($savedAt ?? null)
                        <!-- Saved Date Badge -->
                        <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-semibold">Saved: {{ \Carbon\Carbon::parse($savedAt)->format('M d, Y h:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Read-Only Notice Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-blue-900 mb-1">Archived Report - Read Only</h3>
                <p class="text-blue-800 text-sm mb-3">
                    This is Version {{ $version ?? 1 }} of the report, saved on {{ $savedAt ? \Carbon\Carbon::parse($savedAt)->format('M d, Y \a\t h:i A') : 'N/A' }}. 
                    Archived reports cannot be edited directly to preserve data integrity.
                </p>
                <p class="text-blue-700 text-sm">
                    If you need to make changes, click <strong>"Edit & Save New Version"</strong> below to create an editable copy with an incremented version number.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Vaccination Report Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table id="vaccinationReportTable" class="vaccination-table">
                <thead>
                    @php
                        // Get ALL unique vaccine names across ALL barangays
                        // This ensures vaccines that don't exist in first barangay still appear as columns
                        $vaccineNames = [];
                        if (isset($reportData) && count($reportData) > 0) {
                            foreach ($reportData as $row) {
                                if (isset($row['vaccines'])) {
                                    foreach (array_keys($row['vaccines']) as $vaccineName) {
                                        if (!in_array($vaccineName, $vaccineNames)) {
                                            $vaccineNames[] = $vaccineName;
                                        }
                                    }
                                }
                            }
                        }
                    @endphp
                    <!-- Vaccine Names Header Row -->
                    <tr class="vaccine-header-row">
                        <th rowspan="2" class="area-column">Area</th>
                        <th rowspan="2" class="population-column">Eligible Pop<br>(Under 1 yr)</th>
                        <th rowspan="2" class="population-column">Eligible Pop<br>(0-12 mos)</th>
                        <th rowspan="2" class="population-column">Eligible Pop<br>(13-23 mos)</th>
                        @foreach($vaccineNames as $vaccineName)
                            <th colspan="4" class="vaccine-header">{{ $vaccineName }}</th>
                        @endforeach
                    </tr>
                    
                    <!-- M/F/T/% Sub-header Row -->
                    <tr class="vaccine-subheader-row">
                        @foreach($vaccineNames as $vaccineName)
                            <th class="gender-header">M</th>
                            <th class="gender-header">F</th>
                            <th class="gender-header total-header">T</th>
                            <th class="gender-header percent-header">%</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData as $barangay)
                        <tr class="@if($barangay['barangay'] === 'TOTAL') total-row @endif" @if($barangay['barangay'] === 'TOTAL') data-is-total="true" @endif>
                            <!-- Area Name -->
                            <td class="area-column" data-barangay-name="{{ $barangay['barangay'] }}">{{ $barangay['barangay'] }}</td>
                            
                            <!-- Three Eligible Population Columns -->
                            <td class="population-column">{{ number_format($barangay['eligible_population_under_1_year'] ?? 0) }}</td>
                            <td class="population-column">{{ number_format($barangay['eligible_population_0_12_months'] ?? 0) }}</td>
                            <td class="population-column">{{ number_format($barangay['eligible_population_13_23_months'] ?? 0) }}</td>
                            
                            <!-- Vaccine Data - loop through standardized vaccine names to match headers -->
                            @foreach($vaccineNames as $vaccineName)
                                @php
                                    // Get vaccine data if it exists for this barangay, otherwise use defaults
                                    $vaccine = $barangay['vaccines'][$vaccineName] ?? [
                                        'male_count' => 0,
                                        'female_count' => 0,
                                        'total_count' => 0,
                                        'percentage' => 0
                                    ];
                                @endphp
                                <td class="gender-cell editable-cell" 
                                    data-barangay="{{ $barangay['barangay'] }}" 
                                    data-vaccine="{{ $vaccineName }}" 
                                    data-type="male"
                                    data-original="{{ $vaccine['male_count'] }}">{{ $vaccine['male_count'] }}</td>
                                <td class="gender-cell editable-cell" 
                                    data-barangay="{{ $barangay['barangay'] }}" 
                                    data-vaccine="{{ $vaccineName }}" 
                                    data-type="female"
                                    data-original="{{ $vaccine['female_count'] }}">{{ $vaccine['female_count'] }}</td>
                                <td class="gender-cell total-cell editable-cell" 
                                    data-barangay="{{ $barangay['barangay'] }}" 
                                    data-vaccine="{{ $vaccineName }}" 
                                    data-type="total"
                                    data-original="{{ $vaccine['total_count'] }}">{{ $vaccine['total_count'] }}</td>
                                <td class="gender-cell percent-cell" 
                                    data-barangay="{{ $barangay['barangay'] }}" 
                                    data-vaccine="{{ $vaccineName }}" 
                                    data-type="percentage">{{ number_format($vaccine['percentage'], 2) }}%</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Actions
        </h3>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Edit Mode Controls - Only show for latest version -->
            @if($isLatestVersion)
                <!-- Toggle Edit Mode Button (shown in view mode) -->
                <button id="toggleEditBtn" onclick="toggleEditMode()" class="inline-flex items-center gap-2 px-5 py-3 bg-purple-600 text-white rounded-lg font-semibold shadow-md hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit & Save New Version
                </button>
                
                <!-- Save Button (shown in edit mode) -->
                <button id="saveEditBtn" onclick="saveEditedReport()" class="hidden items-center gap-2 px-5 py-3 bg-green-600 text-white rounded-lg font-semibold shadow-md hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save as Version {{ ($version ?? 1) + 1 }}
                </button>
                
                <!-- Cancel Button (shown in edit mode) -->
                <button id="cancelEditBtn" onclick="cancelEditMode()" class="hidden items-center gap-2 px-5 py-3 bg-gray-500 text-white rounded-lg font-semibold shadow-md hover:bg-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </button>
            @else
                <!-- Show message and link to latest version for older versions -->
                <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-900">You're viewing an older version</p>
                        <p class="text-xs text-amber-700">Editing is only available for the latest version (v{{ $latestVersion }})</p>
                    </div>
                    <a href="{{ route('reports.show', ['year' => $year, 'quarter_start' => $quarterStart, 'quarter_end' => $quarterEnd, 'version' => $latestVersion]) }}" 
                       class="inline-flex items-center gap-1 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-semibold hover:bg-amber-700 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View Latest (v{{ $latestVersion }})
                    </a>
                </div>
            @endif
            
            <!-- Divider -->
            <div class="h-10 w-px bg-gray-300"></div>
            
            <!-- Export to PDF Button -->
            <button onclick="exportToPDF()" class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 text-white rounded-lg font-semibold shadow-md hover:bg-red-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </button>
            
            <!-- Export to Excel Button -->
            <button onclick="exportToExcel()" class="inline-flex items-center gap-2 px-5 py-3 bg-green-600 text-white rounded-lg font-semibold shadow-md hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>
        </div>
        
        <!-- Helper Text -->
        <div id="editModeHelper" class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">
                <svg class="w-4 h-4 inline text-purple-600 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <strong>Tip:</strong> Click "Edit & Save New Version" to modify vaccination counts directly in the table. Changes will be saved as Version {{ ($version ?? 1) + 1 }}.
            </p>
        </div>
        
        <!-- Edit Mode Active Helper (hidden by default) -->
        <div id="editModeActive" class="hidden mt-4 p-4 bg-purple-50 rounded-lg border-2 border-purple-300">
            <p class="text-sm font-semibold text-purple-900 mb-2">
                <svg class="w-5 h-5 inline text-purple-600 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Mode Active
            </p>
            <ul class="text-sm text-purple-800 space-y-1 ml-6 list-disc">
                <li>Click any Male, Female, or Total cell to edit (except TOTAL row)</li>
                <li>Percentages will recalculate automatically</li>
                <li>Click "Save as Version {{ ($version ?? 1) + 1 }}" when done</li>
                <li>Click "Cancel" to discard changes</li>
            </ul>
        </div>
    </div>
    
</div>

<!-- Edit & Save New Version Confirmation Modal -->
<div id="editNewVersionModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit & Save New Version
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-base mb-4">
                You're about to edit this report and create <strong>Version {{ ($version ?? 1) + 1 }}</strong>.
            </p>
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg mb-4">
                <p class="text-sm text-purple-900 mb-2">
                    <strong>📝 What happens next:</strong>
                </p>
                <ul class="text-sm text-purple-800 space-y-1 ml-4">
                    <li>• The table will become editable</li>
                    <li>• Click any Male, Female, or Total cell to modify values</li>
                    <li>• Percentages will update automatically</li>
                    <li>• Click "Save" to create <strong>Version {{ ($version ?? 1) + 1 }}</strong></li>
                    <li>• This version (v{{ $version ?? 1 }}) will remain unchanged</li>
                </ul>
            </div>
            <p class="text-sm text-gray-600">
                Do you want to proceed?
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeEditNewVersionModal()" 
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button onclick="confirmEditNewVersion()" 
                    class="px-5 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                Start Editing
            </button>
        </div>
    </div>
</div>
@endsection

@section('additional-scripts')
    <!-- Export libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        // Global state for edit mode
        let isEditMode = false;
        let originalData = {};
        let modifiedCells = new Set();
        
        // Show Edit & Save New Version Modal
        function showEditNewVersionModal() {
            const modal = document.getElementById('editNewVersionModal');
            modal.style.display = 'flex';
        }

        // Close Edit & Save New Version Modal
        function closeEditNewVersionModal() {
            const modal = document.getElementById('editNewVersionModal');
            modal.style.display = 'none';
        }

        // Confirm Edit - Enable edit mode instead of redirecting
        function confirmEditNewVersion() {
            closeEditNewVersionModal();
            enableEditMode();
        }
        
        // Toggle Edit Mode (wrapper for button)
        function toggleEditMode() {
            showEditNewVersionModal();
        }
        
        // Enable Edit Mode
        function enableEditMode() {
            isEditMode = true;
            
            // Toggle button visibility
            document.getElementById('toggleEditBtn').classList.add('hidden');
            document.getElementById('saveEditBtn').classList.remove('hidden');
            document.getElementById('saveEditBtn').classList.add('inline-flex');
            document.getElementById('cancelEditBtn').classList.remove('hidden');
            document.getElementById('cancelEditBtn').classList.add('inline-flex');
            
            // Toggle helper text
            document.getElementById('editModeHelper').classList.add('hidden');
            document.getElementById('editModeActive').classList.remove('hidden');
            
            // Store original data and make cells editable
            const editableCells = document.querySelectorAll('.editable-cell');
            editableCells.forEach(cell => {
                const barangay = cell.dataset.barangay;
                const vaccine = cell.dataset.vaccine;
                const type = cell.dataset.type;
                
                // Skip TOTAL row
                if (barangay === 'TOTAL') {
                    return;
                }
                
                // Store original value
                const key = `${barangay}|${vaccine}|${type}`;
                originalData[key] = cell.textContent.trim();
                
                // Make cell editable with visual feedback
                cell.style.cursor = 'pointer';
                cell.style.backgroundColor = '#fef3c7'; // amber-100
                cell.title = 'Click to edit';
                
                // Add click handler
                cell.onclick = function() {
                    makeEditable(cell);
                };
            });
        }
        
        // Make a cell editable
        function makeEditable(cell) {
            if (cell.querySelector('input')) {
                return; // Already editing
            }
            
            const currentValue = cell.textContent.trim();
            const input = document.createElement('input');
            input.type = 'number';
            input.min = '0';
            input.value = currentValue;
            input.className = 'w-full px-2 py-1 border-2 border-purple-500 rounded text-center';
            
            cell.textContent = '';
            cell.appendChild(input);
            input.focus();
            input.select();
            
            // Save on blur or Enter
            const saveEdit = () => {
                const newValue = parseInt(input.value) || 0;
                cell.textContent = newValue;
                
                // Track modification
                const key = `${cell.dataset.barangay}|${cell.dataset.vaccine}|${cell.dataset.type}`;
                if (originalData[key] !== newValue.toString()) {
                    modifiedCells.add(key);
                    cell.style.backgroundColor = '#dbeafe'; // blue-100 - indicates modified
                } else {
                    modifiedCells.delete(key);
                    cell.style.backgroundColor = '#fef3c7'; // back to amber-100
                }
                
                // Recalculate totals and percentages for this row
                recalculateRow(cell);
            };
            
            input.addEventListener('blur', saveEdit);
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    saveEdit();
                }
                if (e.key === 'Escape') {
                    cell.textContent = currentValue;
                }
            });
        }
        
        // Recalculate totals and percentages for a row
        function recalculateRow(cell) {
            const barangay = cell.dataset.barangay;
            const vaccine = cell.dataset.vaccine;
            const row = cell.parentElement;
            
            // Find male, female, total cells for this vaccine
            const cells = row.querySelectorAll(`[data-vaccine="${vaccine}"]`);
            let maleCell, femaleCell, totalCell, percentCell;
            
            cells.forEach(c => {
                if (c.dataset.type === 'male') maleCell = c;
                if (c.dataset.type === 'female') femaleCell = c;
                if (c.dataset.type === 'total') totalCell = c;
                if (c.dataset.type === 'percentage') percentCell = c;
            });
            
            // Get values
            const male = parseInt(maleCell.textContent) || 0;
            const female = parseInt(femaleCell.textContent) || 0;
            const total = male + female;
            
            // Update total
            totalCell.textContent = total;
            
            // Get eligible population for this vaccine's age group
            const eligiblePop = getEligiblePopulation(row, vaccine);
            
            // Calculate percentage
            const percentage = eligiblePop > 0 ? (total / eligiblePop * 100) : 0;
            percentCell.textContent = percentage.toFixed(2) + '%';
            
            // Recalculate TOTAL row
            recalculateTotalRow();
        }
        
        // Get eligible population for a vaccine based on its age group
        function getEligiblePopulation(row, vaccine) {
            // Determine age group from vaccine name
            const vaccineConfig = @json($vaccineConfig ?? []);
            const baseVaccineName = vaccine.split('|')[0];
            const targetAgeGroup = vaccineConfig[baseVaccineName]?.target_age_group || 'under_1_year';
            
            // Get the appropriate eligible population column
            const cells = row.children;
            if (targetAgeGroup === 'under_1_year') {
                return parseInt(cells[1].textContent.replace(/,/g, '')) || 0; // Column 2
            } else if (targetAgeGroup === '0_12_months') {
                return parseInt(cells[2].textContent.replace(/,/g, '')) || 0; // Column 3
            } else if (targetAgeGroup === '13_23_months') {
                return parseInt(cells[3].textContent.replace(/,/g, '')) || 0; // Column 4
            }
            return 0;
        }
        
        // Recalculate TOTAL row by summing all barangay rows
        // Recalculate TOTAL row by summing all barangay rows
        function recalculateTotalRow() {
            const table = document.querySelector('table tbody');
            if (!table) {
                return;
            }
            
            // Try multiple ways to find TOTAL row
            let totalRow = table.querySelector('tr[data-is-total="true"]');
            if (!totalRow) {
                totalRow = table.querySelector('.total-row');
            }
            if (!totalRow) {
                // Last resort: find row where area-column contains "TOTAL"
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    const areaCell = row.querySelector('.area-column');
                    if (areaCell && areaCell.textContent.trim() === 'TOTAL') {
                        totalRow = row;
                    }
                });
            }
            
            if (!totalRow) {
                return;
            }
            
            const allRows = table.querySelectorAll('tbody tr:not(.total-row):not([data-is-total="true"])');
            
            // Get all vaccine cells in the TOTAL row to process
            const totalRowVaccineCells = totalRow.querySelectorAll('[data-vaccine]');
            const processedVaccines = new Set();
            
            totalRowVaccineCells.forEach(cell => {
                const vaccine = cell.dataset.vaccine;
                if (processedVaccines.has(vaccine)) return;
                processedVaccines.add(vaccine);
                
                let totalMale = 0, totalFemale = 0, totalTotal = 0;
                
                // Sum up all barangay rows for this vaccine
                allRows.forEach(row => {
                    const cells = Array.from(row.querySelectorAll('[data-vaccine]'));
                    cells.forEach(c => {
                        if (c.dataset.vaccine !== vaccine) return;
                        
                        // Get text content, handling input fields if present
                        let textValue = c.textContent.trim();
                        if (c.querySelector('input')) {
                            textValue = c.querySelector('input').value;
                        }
                        const value = parseInt(textValue) || 0;
                        
                        if (c.dataset.type === 'male') totalMale += value;
                        if (c.dataset.type === 'female') totalFemale += value;
                        if (c.dataset.type === 'total') totalTotal += value;
                    });
                });
                
                // Update TOTAL row cells for this vaccine
                const totalRowCells = Array.from(totalRow.querySelectorAll('[data-vaccine]'));
                totalRowCells.forEach(c => {
                    if (c.dataset.vaccine !== vaccine) return;
                    
                    if (c.dataset.type === 'male') c.textContent = totalMale;
                    if (c.dataset.type === 'female') c.textContent = totalFemale;
                    if (c.dataset.type === 'total') c.textContent = totalTotal;
                    
                    if (c.dataset.type === 'percentage') {
                        const eligiblePop = getEligiblePopulation(totalRow, vaccine);
                        const percentage = eligiblePop > 0 ? (totalTotal / eligiblePop * 100) : 0;
                        c.textContent = percentage.toFixed(2) + '%';
                    }
                });
            });
        }
        
        // Cancel Edit Mode
        function cancelEditMode() {
            showConfirm(
                'Discard Changes?',
                'Are you sure you want to cancel? All unsaved changes will be lost.',
                function() {
                    performCancel();
                }
            );
        }
        
        // Perform the actual cancel operation
        function performCancel() {
            // Restore original values
            const editableCells = document.querySelectorAll('.editable-cell');
            editableCells.forEach(cell => {
                const key = `${cell.dataset.barangay}|${cell.dataset.vaccine}|${cell.dataset.type}`;
                if (originalData[key]) {
                    cell.textContent = originalData[key];
                }
                cell.style.cursor = '';
                cell.style.backgroundColor = '';
                cell.title = '';
                cell.onclick = null;
            });
            
            // Reset state
            isEditMode = false;
            originalData = {};
            modifiedCells.clear();
            
            // Toggle button visibility
            document.getElementById('toggleEditBtn').classList.remove('hidden');
            document.getElementById('saveEditBtn').classList.add('hidden');
            document.getElementById('cancelEditBtn').classList.add('hidden');
            
            // Toggle helper text
            document.getElementById('editModeHelper').classList.remove('hidden');
            document.getElementById('editModeActive').classList.add('hidden');
            
            // Recalculate TOTAL row
            recalculateTotalRow();
        }
        
        // Save Edited Report
        function saveEditedReport() {
            if (modifiedCells.size === 0) {
                showToast('No changes were made.', 'warning');
                return;
            }
            
            showConfirm(
                'Confirm Save',
                `Save ${modifiedCells.size} change(s) as Version {{ ($version ?? 1) + 1 }}? This will create a new version of the report.`,
                function() {
                    performSave();
                }
            );
        }
        
        // Perform the actual save operation
        function performSave() {
            
            // Collect all data from the table
            const reportData = [];
            const table = document.querySelector('table tbody');
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const areaCell = row.querySelector('.area-column');
                if (!areaCell) return;
                
                // Use data attribute if available, otherwise use text content
                const barangay = areaCell.dataset.barangayName || areaCell.textContent.trim();
                const vaccines = {};
                
                const vaccineCells = row.querySelectorAll('[data-vaccine]');
                const processedVaccines = new Set();
                
                vaccineCells.forEach(cell => {
                    const vaccine = cell.dataset.vaccine;
                    if (processedVaccines.has(vaccine)) return;
                    processedVaccines.add(vaccine);
                    
                    const vaccineCellsForThis = Array.from(row.querySelectorAll('[data-vaccine]')).filter(c => c.dataset.vaccine === vaccine);
                    let male = 0, female = 0, total = 0;
                    
                    vaccineCellsForThis.forEach(c => {
                        const value = parseInt(c.textContent.trim()) || 0;
                        if (c.dataset.type === 'male') male = value;
                        if (c.dataset.type === 'female') female = value;
                        if (c.dataset.type === 'total') total = value;
                    });
                    
                    vaccines[vaccine] = { male, female, total };
                });
                
                reportData.push({ barangay, vaccines });
            });
            
            // Show loading state
            const saveBtn = document.getElementById('saveEditBtn');
            const originalBtnText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...';
            
            // Send to backend
            fetch('{{ route("reports.saveEdited") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    year: {{ $year }},
                    quarter_start: {{ $quarterStart }},
                    quarter_end: {{ $quarterEnd }},
                    month_start: {{ $monthStart ?? 'null' }},
                    month_end: {{ $monthEnd ?? 'null' }},
                    current_version: {{ $version ?? 1 }},
                    report_data: reportData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Successfully saved as Version ${data.new_version}! Redirecting...`, 'success', 2000);
                    // Redirect to the new version
                    setTimeout(() => {
                        window.location.href = `{{ route('reports.show') }}?year={{ $year }}&quarter_start={{ $quarterStart }}&quarter_end={{ $quarterEnd }}&version=${data.new_version}`;
                    }, 2000);
                } else {
                    showToast('Error: ' + (data.message || 'Failed to save report'), 'error', 0);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while saving. Please try again.', 'error', 0);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnText;
            });
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editNewVersionModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeEditNewVersionModal();
                    }
                });
            }
        });
        
        // Export to PDF function
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: [215.9, 330.2] // Long bond paper (8.5" x 13")
            });
            
            // Title
            doc.setFontSize(16);
            doc.setFont(undefined, 'bold');
            doc.text('CHILD CARE PROGRAM', doc.internal.pageSize.getWidth() / 2, 15, { align: 'center' });
            
            doc.setFontSize(12);
            doc.setFont(undefined, 'normal');
            doc.text('Vaccine Preventable Diseases Surveillance Report', doc.internal.pageSize.getWidth() / 2, 22, { align: 'center' });
            doc.text('{{ $dateRange }} - CALAUAN, LAGUNA', doc.internal.pageSize.getWidth() / 2, 28, { align: 'center' });
            
            // Version info
            doc.setFontSize(10);
            doc.text('Archived Version {{ $version ?? 1 }} - Saved: {{ $savedAt ? \Carbon\Carbon::parse($savedAt)->format("M d, Y h:i A") : "N/A" }}', doc.internal.pageSize.getWidth() / 2, 33, { align: 'center' });
            
            // Get table data
            const table = document.getElementById('vaccinationReportTable');
            
            doc.autoTable({
                html: table,
                startY: 38,
                theme: 'grid',
                styles: { fontSize: 8, cellPadding: 2 },
                headStyles: { fillColor: [124, 58, 237], textColor: 255, fontStyle: 'bold' },
                columnStyles: {
                    0: { cellWidth: 30, fontStyle: 'bold' },
                    1: { cellWidth: 20, halign: 'center' }
                },
                margin: { left: 10, right: 10 }
            });
            
            doc.save('Vaccination_Report_{{ $year }}_Q{{ $quarterStart }}-Q{{ $quarterEnd }}_v{{ $version ?? 1 }}.pdf');
        }
        
        // Export to Excel function
        function exportToExcel() {
            const table = document.getElementById('vaccinationReportTable');
            const wb = XLSX.utils.table_to_book(table, {sheet: 'Vaccination Report'});
            
            // Add title rows
            const ws = wb.Sheets['Vaccination Report'];
            XLSX.utils.sheet_add_aoa(ws, [
                ['CHILD CARE PROGRAM'],
                ['Vaccine Preventable Diseases Surveillance Report'],
                ['{{ $dateRange }} - CALAUAN, LAGUNA'],
                ['Archived Version {{ $version ?? 1 }} - Saved: {{ $savedAt ? \Carbon\Carbon::parse($savedAt)->format("M d, Y h:i A") : "N/A" }}'],
                [''],
            ], {origin: 'A1'});
            
            // Adjust column widths
            const colWidths = [
                { wch: 20 }, // Area
                { wch: 15 }, // Population
            ];
            ws['!cols'] = colWidths;
            
            XLSX.writeFile(wb, 'Vaccination_Report_{{ $year }}_Q{{ $quarterStart }}-Q{{ $quarterEnd }}_v{{ $version ?? 1 }}.xlsx');
        }
    </script>
    
    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Confirmation Modal -->
    <div id="confirmModal" style="display: none; position: fixed; inset: 0; z-index: 9998; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 400px; width: 90%; animation: fadeIn 0.2s ease-out;">
            <div style="padding: 24px; border-bottom: 1px solid #e5e7eb;">
                <h3 id="confirmTitle" style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;"></h3>
            </div>
            <div style="padding: 24px;">
                <p id="confirmMessage" style="font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0;"></p>
            </div>
            <div style="padding: 16px 24px 24px; display: flex; gap: 12px; justify-content: flex-end;">
                <button id="confirmCancelBtn" style="padding: 10px 20px; border: 1px solid #d1d5db; background: white; color: #374151; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Cancel</button>
                <button id="confirmOkBtn" style="padding: 10px 20px; border: none; background: #7c3aed; color: white; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Confirm</button>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        #confirmCancelBtn:hover { background: #f3f4f6; }
        #confirmOkBtn:hover { background: #6d28d9; }
    </style>
    
    <script>
        // Toast Notification System
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" style="stroke: #10b981;"/></svg>',
                error: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" style="stroke: #ef4444;"/></svg>',
                warning: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" style="stroke: #f59e0b;"/></svg>'
            };
            
            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Warning'
            };
            
            toast.innerHTML = `
                ${icons[type]}
                <div class="toast-content">
                    <div class="toast-title">${titles[type]}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <div class="toast-close">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Close button handler
            toast.querySelector('.toast-close').addEventListener('click', () => {
                closeToast(toast);
            });
            
            // Auto-dismiss
            if (duration > 0) {
                setTimeout(() => {
                    closeToast(toast);
                }, duration);
            }
        }
        
        function closeToast(toast) {
            toast.classList.add('hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
        
        // Confirmation Modal System
        function showConfirm(title, message, onConfirm) {
            const modal = document.getElementById('confirmModal');
            const titleEl = document.getElementById('confirmTitle');
            const messageEl = document.getElementById('confirmMessage');
            const okBtn = document.getElementById('confirmOkBtn');
            const cancelBtn = document.getElementById('confirmCancelBtn');
            
            titleEl.textContent = title;
            messageEl.textContent = message;
            modal.style.display = 'flex';
            
            // Remove old listeners
            const newOkBtn = okBtn.cloneNode(true);
            const newCancelBtn = cancelBtn.cloneNode(true);
            okBtn.parentNode.replaceChild(newOkBtn, okBtn);
            cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
            
            // Add new listeners
            newOkBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                if (onConfirm) onConfirm();
            });
            
            newCancelBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
            
            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
@endsection
