@extends('layouts.responsive-layout')

@section('title', 'Vaccination Status')

@section('additional-styles')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<style>
    /* Base font styling for the entire page */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
        
        /* Enhanced Vaccination Status Heading */
        .page-title {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            color: #2d3748;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Enhanced form controls with outline boxes */
        .outlined-select, .outlined-input {
            border: 2px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 0.50rem 1rem !important;
            background-color: white;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .outlined-select:focus, .outlined-input:focus {
            outline: none !important;
            border-color: #7a5bbd !important;
            box-shadow: 0 0 0 3px rgba(122, 91, 189, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        }
        
        .outlined-select:hover, .outlined-input:hover {
            border-color: #cbd5e0 !important;
        }
        
        /* Enhanced labels */
        .filter-label {
            font-weight: 600 !important;
            color: #4a5568 !important;
        }
        
        /* Minimal custom styles that maintain functionality */
        .clickable-row {
            cursor: pointer;
        }
        
        .vaccinated {
            color: #10b981;
            font-weight: 600;
        }
        
        .missed {
            color: #ef4444;
            font-weight: 600;
        }
        
        .not-done {
            color: #f59e0b;
            font-weight: 600;
        }

        .vax-status-table-header th {
            color: #fff !important;
        }

        .vax-day-btn {
            background: linear-gradient(90deg, #7a5bbd 0%, #6447a0 100%);
            color: #fff !important;
            font-weight: bold;
            font-size: 1rem;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(122,91,189,0.10);
            transition: background 0.3s, box-shadow 0.3s; letter-spacing: 0.5px;
            cursor: pointer;
            outline: none;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .vax-day-btn:hover, .vax-day-btn:focus {
            background: linear-gradient(90deg, #6447a0 0%, #7a5bbd 100%);
            box-shadow: 0 4px 16px rgba(122,91,189,0.18);
        }

        .vax-search-btn {
            background: linear-gradient(90deg, #7a5bbd 0%, #6447a0 100%);
            color: #fff !important;
            font-weight: 500;
            font-size: 1rem;
            padding: 10px 20px;
            border: none;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(122,91,189,0.10);
            transition: background 0.3s, box-shadow 0.3s;
            cursor: pointer;
            outline: none;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 5px;
        }
        .vax-search-btn:hover, .vax-search-btn:focus {
            background: linear-gradient(90deg, #6447a0 0%, #7a5bbd 100%);
            box-shadow: 0 4px 16px rgba(122,91,189,0.18);
        }
        .vax-search-btn img {
            width: 22px;
            height: 22px;
        }
        
        /* Mobile adjustments */
        @media (max-width: 767px) {
            .page-title {
                font-size: 2rem !important;
            }
        }
    </style>
@endsection

@section('content')
        <section>
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                <h1 class="page-title">Vaccination Status</h1>
            </div>

            <!-- Filter and Search Section -->
            <div class="tw-flex tw-flex-wrap tw-justify-between tw-items-center tw-gap-4 tw-mb-12">
                <div class="tw-flex tw-items-center tw-gap-2">
                    <label for="statusFilter" class="filter-label">Filter by Status:</label>
                    <select id="statusFilter" class="outlined-select">
                        <option value="none">No filter</option>
                        <option value="vaccinated">Vaccinated</option>
                        <option value="missed">Missed</option>
                        <option value="not_done">Not Done</option>
                    </select>
                </div>
                
                <div class="tw-flex tw-items-center tw-gap-2">
                    <input type="text" id="searchBar" placeholder="Search by Name" class="outlined-input">
                    <button id="searchBtn" class="vax-search-btn">
                        <img src="{{ asset('images/magnifying-glass.png') }}" alt="Search">
                    </button>
                </div>
            </div>

            <!-- Vaccination Day Button -->
            <div class="tw-mb-8">
                <form id="vaccinationDayForm" method="POST" action="{{ route('set_vaccination_day') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-4">
                    @csrf
                    <input type="hidden" name="after_six_pm" id="afterSixPmFlag" value="0">
                    <button type="submit" class="vax-day-btn">
                        Mark Today as Vaccination Day
                    </button>
                    @if(session('vaccination_day') === \Carbon\Carbon::now()->toDateString())
                        <span class="tw-text-green-600 tw-font-bold">Vaccination Day is active for today!</span>
                    @endif
                </form>
            </div>

            <!-- Vaccine Status Table with Responsive Design -->
            <div class="tw-overflow-x-auto tw-w-full tw-shadow-md tw-rounded-lg tw-bg-white tw-mb-6">
                <table class="status-table tw-w-full tw-border-collapse">
                    <thead class="tw-bg-primary vax-status-table-header">
                        <tr>
                            <th class="tw-p-4 tw-text-left">Name</th>
                            <th class="tw-p-4 tw-text-left">Age</th>
                            <th class="tw-p-4 tw-text-left">Date</th>
                            <th class="tw-p-4 tw-text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody id="patient-list">
                        @foreach($patients as $patient)
                            @if(in_array($patient->id, session('missed_patients', [])))
                                @php $missedPatients[] = $patient; @endphp
                            @elseif(in_array($patient->id, session('vaccinated_patients', [])))
                                <tr data-id="{{ $patient->id }}" class="clickable-row tw-border-b tw-border-gray-200 hover:tw-bg-gray-50" data-url="{{ route('patient_card', $patient->id) }}">
                                    <td class="tw-p-4">{{ $patient->name }}</td>
                                    <td class="tw-p-4">{{ \Carbon\Carbon::parse($patient->date_of_birth)->age }}</td>
                                    <td class="tw-p-4">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                    <td class="tw-p-4">
                                        <span class="vaccinated">Vaccinated</span>
                                    </td>
                                </tr>
                            @else
                                <tr data-id="{{ $patient->id }}" class="clickable-row tw-border-b tw-border-gray-200 hover:tw-bg-gray-50" data-url="{{ route('patient_card', $patient->id) }}">
                                    <td class="tw-p-4">{{ $patient->name }}</td>
                                    <td class="tw-p-4">{{ \Carbon\Carbon::parse($patient->date_of_birth)->age }}</td>
                                    <td class="tw-p-4">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                    <td class="tw-p-4">
                                        <span class="not-done">Not Done</span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                        @if(isset($missedPatients))
                            @foreach($missedPatients as $patient)
                                <tr data-id="{{ $patient->id }}" class="clickable-row tw-border-b tw-border-gray-200 hover:tw-bg-gray-50" data-url="{{ route('patient_card', $patient->id) }}">
                                    <td class="tw-p-4">{{ $patient->name }}</td>
                                    <td class="tw-p-4">{{ \Carbon\Carbon::parse($patient->date_of_birth)->age }}</td>
                                    <td class="tw-p-4">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                    <td class="tw-p-4">
                                        <span class="missed">Missed</span>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
@endsection

@section('additional-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            function searchPatients() {
                let searchQuery = document.getElementById('searchBar').value.toLowerCase();
                let patientRows = document.querySelectorAll('.clickable-row');
                patientRows.forEach(row => {
                    let patientName = row.querySelector('td').innerText.toLowerCase();
                    if (patientName.includes(searchQuery)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            document.getElementById('searchBar').addEventListener('input', searchPatients);

            // Filter functionality
            function filterPatients() {
                let filterValue = document.getElementById('statusFilter').value;
                let patientRows = document.querySelectorAll('.clickable-row');
                patientRows.forEach(row => {
                    let status = row.querySelector('td:last-child').innerText.trim().toLowerCase();
                    if (filterValue === 'none') {
                        row.style.display = '';
                    } else if (filterValue === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            document.getElementById('statusFilter').addEventListener('change', filterPatients);

            // Table row click navigation
            document.querySelectorAll('.clickable-row').forEach(row => {
                row.addEventListener('click', function() {
                    window.location = this.getAttribute('data-url');
                });
            });

            // Move vaccinated patients to the bottom on page load
            function moveVaccinatedToBottom() {
                let patientRows = document.querySelectorAll('.clickable-row');
                let patientList = document.getElementById('patient-list');
                patientRows.forEach(row => {
                    let status = row.querySelector('td:last-child').innerText.trim().toLowerCase();
                    if (status === 'vaccinated') {
                        patientList.appendChild(row);
                    }
                });
            }
            moveVaccinatedToBottom();

            // Client-side time check for defense demo
            document.getElementById('vaccinationDayForm').addEventListener('submit', function(e) {
                var now = new Date();
                var hours = now.getHours();
                if (hours >= 18) {
                    document.getElementById('afterSixPmFlag').value = '1';
                } else {
                    document.getElementById('afterSixPmFlag').value = '0';
                }
            });
        });
    </script>
@endsection