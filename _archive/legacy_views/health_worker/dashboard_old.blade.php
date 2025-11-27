@extends('layouts.responsive-layout')

@section('title', 'Dashboard')

@section('additional-styles')
<link rel="stylesheet" href="{{ asset('css/tailwind-full.css') }}">
@endsection

@section('content')
<div class="flex flex-col pb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-5">Dashboard Overview</h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
        <div class="flex items-center gap-4 bg-white rounded-lg shadow p-4">
            <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center">
                <img src="{{ asset('images/patient.png') }}" alt="Total Patients" class="h-6 w-6" />
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Patients</p>
                <p class="text-xl font-bold text-gray-900">{{ $totalPatients ?? '0' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-white rounded-lg shadow p-4">
            <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center">
                <img src="{{ asset('images/vaccination.png') }}" alt="Vaccinated" class="h-6 w-6" />
            </div>
            <div>
                <p class="text-sm text-gray-500">Vaccinated</p>
                <p class="text-xl font-bold text-gray-900">{{ $vaccinated ?? '0' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-white rounded-lg shadow p-4">
            <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center">
                <img src="{{ asset('images/feedback.png') }}" alt="Feedback" class="h-6 w-6" />
            </div>
            <div>
                <p class="text-sm text-gray-500">Feedback Complete</p>
                <p class="text-xl font-bold text-gray-900">{{ $feedbackCount ?? '0' }}</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 h-72">
            <h2 class="text-base font-semibold text-gray-700 mb-3">Monthly Statistics</h2>
            <canvas id="monthlyChart" class="h-full w-full"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-4 h-72">
            <h2 class="text-base font-semibold text-gray-700 mb-3">Vaccination Status</h2>
            <canvas id="vaccineStatusChart" class="h-full w-full"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-4 h-72 md:col-span-2 xl:col-span-1">
            <h2 class="text-base font-semibold text-gray-700 mb-3">Vaccination Distribution</h2>
            <canvas id="vaccinationDistChart" class="h-full w-full"></canvas>
        </div>
    </div>

    <!-- Upcoming Vaccinations -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-700">Upcoming Vaccinations</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-[#7a5bbd] text-white">
                    <tr>
                        <th class="px-3 py-2 text-left text-sm font-semibold">Patient Name</th>
                        <th class="px-3 py-2 text-left text-sm font-semibold">Vaccine</th>
                        <th class="px-3 py-2 text-left text-sm font-semibold">Scheduled Date</th>
                        <th class="px-3 py-2 text-left text-sm font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if(isset($upcomingVaccinations) && count($upcomingVaccinations) > 0)
                        @foreach($upcomingVaccinations as $vaccination)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 whitespace-nowrap">{{ $vaccination->patient_name ?? 'N/A' }}</td>
                            <td class="px-3 py-2">{{ $vaccination->vaccine_name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $vaccination->scheduled_date ?? 'N/A' }}</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('health_worker.view_patient', $vaccination->patient_id ?? 0) }}" class="inline-flex items-center rounded bg-[#7a5bbd] text-white text-xs font-semibold px-2 py-1 shadow hover:bg-[#6d51ad] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7a5bbd]/50">View</a>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="px-3 py-3 text-center text-sm text-gray-500">No upcoming vaccinations</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Feedback Analysis -->
    <div class="bg-white rounded-lg shadow">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-4 py-3 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-700">Feedback Analysis</h2>
            <div class="flex flex-wrap items-center gap-3">
                <select id="feedbackMonth" class="rounded-md border-2 border-gray-200 bg-white px-3 py-2 text-sm focus:border-[#7a5bbd] focus:ring-2 focus:ring-[#7a5bbd]/20">
                    <option value="">Current Month</option>
                </select>
                <label for="feedbackBarangay" class="text-sm font-semibold text-gray-700">Barangay:</label>
                <select id="feedbackBarangay" class="rounded-md border-2 border-gray-200 bg-white px-3 py-2 text-sm focus:border-[#7a5bbd] focus:ring-2 focus:ring-[#7a5bbd]/20">
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
        </div>
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Feedback Analysis for <span id="currentPeriod" class="text-gray-900">Current Month</span></h3>
                <p id="totalResponses" class="text-sm text-gray-500">Total Responses: 0</p>
            </div>
            <div class="h-[350px]">
                <canvas id="feedbackChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional-scripts')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sample data for charts if not provided by the controller
            const monthlyData = {{ isset($monthlyData) ? json_encode($monthlyData) : json_encode([0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }};
            const statusData = {{ isset($vaccineStatusData) ? json_encode($vaccineStatusData) : json_encode([0, 0, 0]) }};

            // Monthly chart
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Vaccinations',
                        data: monthlyData,
                        backgroundColor: '#7a5bbd',
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Vaccination status chart
            const statusCtx = document.getElementById('vaccineStatusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Complete', 'Partial', 'Not Started'],
                    datasets: [{
                        data: statusData,
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            
            // Vaccination Distribution Chart
            const vaccineDistCtx = document.getElementById('vaccinationDistChart').getContext('2d');
            new Chart(vaccineDistCtx, {
                type: 'bar',
                data: {
                    labels: ['BCG', 'HepB', 'Penta', 'OPV', 'IPV', 'PCV', 'Measles'],
                    datasets: [{
                        label: 'Vaccinations',
                        data: {{ isset($vaccineDistData) ? json_encode($vaccineDistData) : json_encode([65, 59, 80, 81, 56, 55, 40]) }},
                        backgroundColor: 'rgba(122, 91, 189, 0.6)',
                        borderColor: 'rgba(122, 91, 189, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Initialize feedback chart with empty data
            const feedbackCtx = document.getElementById('feedbackChart');
            
            // Define the questions
            const questions = [
                'Q1: Madali ninyo natunton ang tanggapan?',
                'Q2: May karatula ba ng direksyon?',
                'Q3: Malinis at maayos ba ang tanggapan?',
                'Q4: Napakahaba ba ang pila ng kostumer?'
            ];
            
            const shortenedQuestions = [
                'Q1: Madali ninyo natunton',
                'Q2: May karatula ba',
                'Q3: Malinis at maayos',
                'Q4: Mahaba ba ang pila'
            ];
            
            let feedbackChart = new Chart(feedbackCtx, {
                type: 'bar',
                data: {
                    labels: shortenedQuestions,
                    datasets: [
                        {
                            label: 'OO',
                            data: [0, 0, 0, 0],
                            backgroundColor: 'rgba(75, 192, 112, 0.8)',
                            borderColor: 'rgb(75, 192, 112)',
                            borderWidth: 1,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'HINDI',
                            data: [0, 0, 0, 0],
                            backgroundColor: 'rgba(255, 99, 132, 0.8)',
                            borderColor: 'rgb(255, 99, 132)',
                            borderWidth: 1,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    const index = tooltipItems[0].dataIndex;
                                    return questions[index];
                                },
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '%';
                                }
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    },
                    onHover: (event, elements, chart) => {
                        // Show cursor as pointer when hovering over bars
                        if (event && event.native && event.native.target) {
                            event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                        }
                        
                        // Show tooltip for the question
                        if (elements.length) {
                            const index = elements[0].index;
                            const datasetIndex = elements[0].datasetIndex;
                            const value = chart.data.datasets[datasetIndex].data[index];
                            
                            // Create tooltip element if it doesn't exist
                            let tooltip = document.getElementById('custom-tooltip');
                            if (!tooltip) {
                                tooltip = document.createElement('div');
                                tooltip.id = 'custom-tooltip';
                                tooltip.style.position = 'absolute';
                                tooltip.style.padding = '8px 12px';
                                tooltip.style.background = 'rgba(0,0,0,0.8)';
                                tooltip.style.color = 'white';
                                tooltip.style.borderRadius = '4px';
                                tooltip.style.pointerEvents = 'none';
                                tooltip.style.zIndex = 100;
                                tooltip.style.fontSize = '14px';
                                tooltip.style.transform = 'translate(-50%, -100%)';
                                document.body.appendChild(tooltip);
                            }
                            
                            // Update tooltip content and position
                            tooltip.innerHTML = `${questions[index]}<br>${chart.data.datasets[datasetIndex].label}: ${value}%`;
                            tooltip.style.left = elements[0].element.x + 'px';
                            tooltip.style.top = (elements[0].element.y - 10) + 'px';
                            tooltip.style.display = 'block';
                        } else {
                            // Hide tooltip when not hovering over bars
                            const tooltip = document.getElementById('custom-tooltip');
                            if (tooltip) tooltip.style.display = 'none';
                        }
                    }
                }
            });

            // Function to fetch feedback analytics data
            function fetchFeedbackAnalytics(month, year, barangay) {
                const params = new URLSearchParams();
                if (month) params.append('month', month);
                if (year) params.append('year', year);
                if (barangay) params.append('barangay', barangay);
                const url = `/api/feedback/analytics?${params.toString()}`;

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(r => {
                        if (!r.ok) throw new Error(`HTTP ${r.status}`);
                        return r.json();
                    })
                    .then(data => updateFeedbackChart(data))
                    .catch(err => {
                        console.error('Error fetching feedback analytics:', err);
                    });
            }

            // Function to update the feedback chart with data
            function updateFeedbackChart(data) {
                if (!data || !data.analysis) return;
                
                // Extract data for the chart
                const positiveData = [
                    data.analysis.Q1.OO_percent || 0,
                    data.analysis.Q2.OO_percent || 0,
                    data.analysis.Q3.OO_percent || 0,
                    data.analysis.Q4.OO_percent || 0
                ];
                
                const negativeData = [
                    data.analysis.Q1.HINDI_percent || 0,
                    data.analysis.Q2.HINDI_percent || 0,
                    data.analysis.Q3.HINDI_percent || 0,
                    data.analysis.Q4.HINDI_percent || 0
                ];
                
                // Update chart data
                feedbackChart.data.datasets[0].data = positiveData;
                feedbackChart.data.datasets[1].data = negativeData;
                feedbackChart.update();
                                
                // Update the title and total responses
                const currentPeriod = document.getElementById('currentPeriod');
                const totalResponses = document.getElementById('totalResponses');
                if (currentPeriod) currentPeriod.textContent = data.current_month || 'Current Month';
                if (totalResponses) totalResponses.textContent = 'Total Responses: ' + (data.total_responses || 0);
                
                // Update the month selector with available months
                if (data.available_months && data.available_months.length > 0) {
                    updateMonthSelector(data.available_months);
                }
            }
            
            // Function to update the month selector with available months
            function updateMonthSelector(availableMonths) {
                const monthSelector = document.getElementById('feedbackMonth');
                if (!monthSelector) return;
                const keepFirst = monthSelector.options[0];
                monthSelector.innerHTML = '';
                monthSelector.appendChild(keepFirst);
                availableMonths.forEach(month => {
                    const opt = document.createElement('option');
                    opt.value = month.value;
                    opt.textContent = month.label;
                    monthSelector.appendChild(opt);
                });
            }
            
            // Handle filter changes
            function onFiltersChange() {
                let selectedMonth = null;
                let selectedYear = null;
                const monthEl = document.getElementById('feedbackMonth');
                const barangayEl = document.getElementById('feedbackBarangay');
                const selectedMonthValue = monthEl ? monthEl.value : '';
                if (selectedMonthValue && selectedMonthValue.includes('-')) {
                    const [year, month] = selectedMonthValue.split('-');
                    selectedMonth = month;
                    selectedYear = year;
                } else if (selectedMonthValue) {
                    selectedMonth = selectedMonthValue;
                    selectedYear = new Date().getFullYear();
                }
                const selectedBarangay = barangayEl ? barangayEl.value : '';
                fetchFeedbackAnalytics(selectedMonth, selectedYear, selectedBarangay);
            }
            const monthEl = document.getElementById('feedbackMonth');
            const barangayEl = document.getElementById('feedbackBarangay');
            if (monthEl) monthEl.addEventListener('change', onFiltersChange);
            if (barangayEl) barangayEl.addEventListener('change', onFiltersChange);
            
            // Initialize by loading current month's data
            fetchFeedbackAnalytics();
        });
    </script>
@endsection