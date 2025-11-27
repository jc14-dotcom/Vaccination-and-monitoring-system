@extends('layouts.responsive-layout')

@section('title', 'Debug Report Keys')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">Debug Report Keys</h1>
    
    @php
        use App\Config\VaccineConfig;
        
        $service = app(\App\Services\VaccinationReportService::class);
        $report = $service->getCurrentReport(2025, 1, 1, null);
        
        // Build dose columns like in main report
        $vaccineConfig = VaccineConfig::getDoseConfiguration();
        $doseColumns = [];
        
        foreach ($vaccineConfig as $vaccineName => $config) {
            $acronym = $config['acronym'];
            $totalDoses = $config['total_doses'];
            
            if ($totalDoses > 1) {
                for ($dose = 1; $dose <= $totalDoses; $dose++) {
                    if ($vaccineName === 'Inactivated Polio Vaccine' && $dose === 2) {
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
                $doseColumns[] = [
                    'label' => $acronym,
                    'key' => $vaccineName
                ];
            }
        }
        
        // Get first non-TOTAL row
        $firstRow = null;
        foreach ($report['data'] as $row) {
            if ($row['barangay'] !== 'TOTAL') {
                $firstRow = $row;
                break;
            }
        }
    @endphp
    
    <div class="bg-white p-4 rounded shadow mb-4">
        <h2 class="font-bold mb-2">Report Info</h2>
        <p>Total rows: {{ count($report['data']) }}</p>
        <p>Date range: {{ $report['date_range'] }}</p>
        @if($firstRow)
            <p>First barangay: {{ $firstRow['barangay'] }}</p>
            <p>Vaccines in data: {{ count($firstRow['vaccines']) }}</p>
        @endif
    </div>
    
    <div class="bg-white p-4 rounded shadow mb-4">
        <h2 class="font-bold mb-2">Expected Keys (from $doseColumns)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-1">#</th>
                        <th class="border p-1">Label</th>
                        <th class="border p-1">Key</th>
                        <th class="border p-1">Has Data?</th>
                        <th class="border p-1">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($doseColumns as $index => $column)
                        @php
                            $hasData = $firstRow && isset($firstRow['vaccines'][$column['key']]);
                            $data = $hasData ? $firstRow['vaccines'][$column['key']] : null;
                        @endphp
                        <tr class="{{ $hasData ? 'bg-green-50' : 'bg-red-50' }}">
                            <td class="border p-1">{{ $index + 1 }}</td>
                            <td class="border p-1">{{ $column['label'] }}</td>
                            <td class="border p-1 text-xs font-mono">{{ $column['key'] }}</td>
                            <td class="border p-1 text-center">
                                @if($hasData)
                                    <span class="text-green-600">✓</span>
                                @else
                                    <span class="text-red-600">✗</span>
                                @endif
                            </td>
                            <td class="border p-1">
                                @if($hasData)
                                    M:{{ $data['male_count'] }} F:{{ $data['female_count'] }} T:{{ $data['total_count'] }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    @if($firstRow)
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-bold mb-2">Actual Keys (from report data)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-1">#</th>
                        <th class="border p-1">Key in Data</th>
                        <th class="border p-1">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($firstRow['vaccines'] as $key => $data)
                        <tr>
                            <td class="border p-1">{{ $loop->iteration }}</td>
                            <td class="border p-1 font-mono text-xs">{{ $key }}</td>
                            <td class="border p-1">M:{{ $data['male_count'] }} F:{{ $data['female_count'] }} T:{{ $data['total_count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
