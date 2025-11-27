
{{-- for demo lang to ng graph --}}



{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Analysis Demo</title>
    <link rel="stylesheet" href="{{ asset('css/tailwind-utilities.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            padding: 20px;
        }
        
        .feedback-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .feedback-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .feedback-content {
            padding: 20px;
        }
        
        .filter-section {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .filter-label {
            font-weight: 500;
            margin-right: 10px;
        }
        
        .filter-select {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            background-color: white;
            min-width: 200px;
        }
        
        .chart-container {
            height: 350px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .questions-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .question-item {
            display: flex;
            align-items: baseline;
        }
        
        .question-number {
            font-weight: 600;
            margin-right: 5px;
            min-width: 30px;
        }
        
        .tooltip {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            pointer-events: none;
            z-index: 100;
        }
        
        /* Legend styles */
        .legend-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            border-radius: 3px;
        }
        
        .positive-color {
            background-color: #4ade80;
        }
        
        .negative-color {
            background-color: #f87171;
        }
    </style>
</head>
<body>
    <div class="feedback-container">
        <div class="feedback-header">
            <h1 class="text-xl font-bold">Feedback Analysis</h1>
        </div>
        
        <div class="feedback-content">
            <div class="filter-section">
                <span class="filter-label">Filter by Barangay:</span>
                <select id="barangayFilter" class="filter-select">
                    <option value="masit">Masit</option>
                    <option value="centro">Centro</option>
                    <option value="all">All Barangays</option>
                </select>
            </div>
            
            <div class="chart-container">
                <canvas id="feedbackChart"></canvas>
            </div>
            
            <div class="legend-container">
                <div class="legend-item">
                    <div class="legend-color positive-color"></div>
                    <span>Positive (OO)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color negative-color"></div>
                    <span>Negative (HINDI)</span>
                </div>
            </div>
            
            <div class="questions-container">
                <div class="question-item">
                    <span class="question-number">Q1:</span>
                    <span>MADALI NINYO NATUNTON ANG TANGGAPANG PUPUNTAHAN NIYO?</span>
                </div>
                <div class="question-item">
                    <span class="question-number">Q2:</span>
                    <span>NAKAKITA BA KAYO NG KARATULA NG DIREKSYON PATUNGO DITO?</span>
                </div>
                <div class="question-item">
                    <span class="question-number">Q3:</span>
                    <span>MALINIS AT MAAYOS BA ANG TANGGAPANG ITO?</span>
                </div>
                <div class="question-item">
                    <span class="question-number">Q4:</span>
                    <span>NAPAKAHABA BA NG PILA NG MGA KOSTUMER SA NATURANG TANGGAPAN?</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sample data for the chart (matches your image)
        const feedbackData = {
            q1: { positive: 75, negative: 25 },
            q2: { positive: 88, negative: 12 },
            q3: { positive: 50, negative: 50 },
            q4: { positive: 25, negative: 75 }
        };
        
        // Chart configuration
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('feedbackChart').getContext('2d');
            
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                    datasets: [
                        {
                            label: 'Positive (OO)',
                            data: [
                                feedbackData.q1.positive,
                                feedbackData.q2.positive,
                                feedbackData.q3.positive,
                                feedbackData.q4.positive
                            ],
                            backgroundColor: '#4ade80',
                            borderColor: '#4ade80',
                            borderWidth: 1,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Negative (HINDI)',
                            data: [
                                feedbackData.q1.negative,
                                feedbackData.q2.negative,
                                feedbackData.q3.negative,
                                feedbackData.q4.negative
                            ],
                            backgroundColor: '#f87171',
                            borderColor: '#f87171',
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
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // We're using custom legend
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                title: function(tooltipItems) {
                                    const questions = [
                                        'Q1: MADALI NINYO NATUNTON ANG TANGGAPANG PUPUNTAHAN NIYO?',
                                        'Q2: NAKAKITA BA KAYO NG KARATULA NG DIREKSYON PATUNGO DITO?',
                                        'Q3: MALINIS AT MAAYOS BA ANG TANGGAPANG ITO?',
                                        'Q4: NAPAKAHABA BA NG PILA NG MGA KOSTUMER SA NATURANG TANGGAPAN?'
                                    ];
                                    return questions[tooltipItems[0].dataIndex];
                                },
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    }
                }
            });
            
            // Filter functionality (for demo purposes)
            document.getElementById('barangayFilter').addEventListener('change', function() {
                // In a real implementation, this would fetch new data
                // For the demo, we'll just show a tooltip
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = 'Filter applied: ' + this.options[this.selectedIndex].text;
                tooltip.style.position = 'absolute';
                tooltip.style.top = '60px';
                tooltip.style.right = '20px';
                document.querySelector('.feedback-content').appendChild(tooltip);
                
                setTimeout(() => {
                    tooltip.remove();
                }, 2000);
            });
        });
    </script>
</body>
</html> --}}