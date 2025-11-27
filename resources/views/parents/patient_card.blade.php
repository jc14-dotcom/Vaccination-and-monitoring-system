<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMMUNIZATION CARD</title>
    <link rel="stylesheet" href="{{ asset('css/vaccination_form.css') }}">
</head>

<body>
    <header>
        <!-- Back Button -->
        <div class="back-button">
            <a href="{{ route('health_worker.vaccination_status') }}" class="btn btn-secondary">
                <img src="{{ asset('images/arrow.png') }}" alt="Back">
            </a>
        </div>

        <!-- Logos and Title -->
        <div class="logos-left">
            <img src="{{ asset('images/todo.png') }}" alt="Logo Left">
        </div>

        <div class="title">IMMUNIZATION CARD</div>

        <div class="logos-right">
            <img src="{{ asset('images/doh-logo.png') }}" alt="Logo Right 1">
            <img src="{{ asset('images/right.png') }}" alt="Logo Right 2">
        </div>
    </header>

    <!-- Success and Error Messages -->
    @if(session('success'))
        <div class="modal" id="successModal">
            <div class="modal-content">
                <p>{{ session('success') }}</p>
                <button id="okButton">OK!</button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Patient Details -->
    <div class="form-container">
        <div class="left-column">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="{{ $patient->name }}" readonly>
            </div>

            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ $patient->date_of_birth }}"
                    readonly>
            </div>

            <div class="form-group">
                <label for="place_of_birth">Place of Birth:</label>
                <input type="text" id="place_of_birth" name="place_of_birth" value="{{ $patient->place_of_birth }}"
                    readonly>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="{{ $patient->address }}" readonly>
            </div>

            <div class="form-group">
                <label for="barangay">Barangay:</label>
                <input type="text" id="barangay" name="barangay" value="{{ $patient->barangay }}" readonly>
            </div>
        </div>
        <div class="right-column">
            <div class="form-group">
                <label for="mothers_name">Mother's Name:</label>
                <input type="text" id="mothers_name" name="mothers_name" value="{{ $patient->mother_name }}" readonly>
            </div>

            <div class="form-group">
                <label for="fathers_name">Father's Name:</label>
                <input type="text" id="fathers_name" name="fathers_name" value="{{ $patient->father_name }}" readonly>
            </div>

            <div class="form-group-row">
                <div class="form-group small-input">
                    <label for="birth_height">Birth Height (cm):</label>
                    <input type="number" id="birth_height" name="birth_height" value="{{ $patient->birth_height }}"
                        readonly>
                </div>
                <div class="form-group small-input">
                    <label for="birth_weight">Birth Weight (kg):</label>
                    <input type="number" id="birth_weight" name="birth_weight" value="{{ $patient->birth_weight }}"
                        readonly>
                </div>
            </div>


            <div class="form-group">
                <label for="sex">Sex:</label>
                <input type="text" id="sex" name="sex" value="{{ $patient->sex }}" readonly>
            </div>


            <div class="form-group">
                <label for="contact_no">Contact No.:</label>
                <input type="text" id="contact_no" name="contact_no" value="{{ $patient->contact_no }}" readonly>
            </div>
        </div>
    </div>
    </div>

    <!-- Vaccination Table -->
    <form action="{{ route('vaccinations.update', $patient->id) }}" method="POST" id="vaccinationForm">
        @csrf
        @method('PUT')
        <div class="table-container">
            <table class="vaccine-table">
                <thead>
                    <tr>
                        <th>BAKUNA</th>
                        <th>DOSES</th>
                        <th colspan="3">PETSA NG BAKUNA</th>
                        <th>REMARKS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vaccinations as $vaccination)
                        <tr>
                            <td>{{ optional($vaccination->vaccine)->vaccine_name ?? 'N/A' }}</td>
                            <td>{{ optional($vaccination->vaccine)->doses_description ?? 'N/A' }}</td>
                            @php
                                $vaccineName = optional($vaccination->vaccine)->vaccine_name;
                                $dosesDescription = optional($vaccination->vaccine)->doses_description;
                            @endphp
                            @if(in_array($vaccineName, ['BCG', 'Hepatitis B']) || ($vaccineName === 'Measles Containing' && $dosesDescription === 'Grade 1'))
                                <!-- Single dose vaccines -->
                                <td colspan="3" class="single-date-cell">
                                    <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_1_date]"
                                        value="{{ $vaccination->dose_1_date ?? '' }}" {{ $vaccination->dose_1_date ? 'readonly' : '' }} style="width:100%;max-width:435px;">
                                </td>
                            @elseif(in_array($vaccineName, ['Inactivated Polio', 'Measles, Mumps, Rubella', 'Tetanus Diphtheria', 'Human Papillomavirus']) || ($vaccineName === 'Measles Containing' && $dosesDescription === 'Grade 7'))
                                <!-- Two dose vaccines -->
                                <td colspan="3" class="double-date-cell">
                                    <div class="double-date-container">
                                        <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_1_date]"
                                            value="{{ $vaccination->dose_1_date ?? '' }}" {{ $vaccination->dose_1_date ? 'readonly' : '' }}>
                                        <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_2_date]"
                                            value="{{ $vaccination->dose_2_date ?? '' }}" {{ $vaccination->dose_2_date ? 'readonly' : '' }}>
                                    </div>
                                </td>
                            @elseif(in_array($vaccineName, ['Pentavalent', 'Oral Polio', 'Pneumococcal Conjugate']))
                                <!-- Three dose vaccines -->
                                <td colspan="3" class="triple-date-cell">
                                    <div class="triple-date-container">
                                        <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_1_date]"
                                            value="{{ $vaccination->dose_1_date ?? '' }}" {{ $vaccination->dose_1_date ? 'readonly' : '' }}>
                                        <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_2_date]"
                                            value="{{ $vaccination->dose_2_date ?? '' }}" {{ $vaccination->dose_2_date ? 'readonly' : '' }}>
                                        <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_3_date]"
                                            value="{{ $vaccination->dose_3_date ?? '' }}" {{ $vaccination->dose_3_date ? 'readonly' : '' }}>
                                    </div>
                                </td>
                            @else
                                <!-- Default for other vaccines -->
                                <td colspan="3" class="single-date-cell">
                                    <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_1_date]"
                                        value="{{ $vaccination->dose_1_date ?? '' }}" {{ $vaccination->dose_1_date ? 'readonly' : '' }} style="width:100%;max-width:435px;">
                                </td>
                            @endif
                            <td>
                                <textarea name="vaccinations[{{ $vaccination->id }}][remarks]"
                                    class="remarks-input">{{ $vaccination->remarks ?? '' }}</textarea>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="submit" class="save-button" id="saveButton" disabled>Save</button>
    </form>


    <!-- Footer -->
    <footer>
        <p>Sa column ng Petsa ng bakuna, isulat ang petsa ng pagbibigay ng bakuna. Sa column ng remarks, isulat ang
            petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Collect all dose date and remarks fields
        const form = document.getElementById('vaccinationForm');
        const saveButton = document.getElementById('saveButton');
        // Get all input[type=date] and textarea. Only those that are not readonly.
        const inputs = Array.from(form.querySelectorAll('input[type="date"]:not([readonly]), textarea'));
        // Store initial values
        const initialValues = inputs.map(input => input.value);

        function checkForChanges() {
            let changed = false;
            inputs.forEach((input, idx) => {
                if (input.value !== initialValues[idx]) {
                    changed = true;
                }
            });
            saveButton.disabled = !changed;
        }

        // Listen for changes
        inputs.forEach(input => {
            input.addEventListener('input', checkForChanges);
        });
        // Initial check
        checkForChanges();
    });
    </script>
</body>

</html>