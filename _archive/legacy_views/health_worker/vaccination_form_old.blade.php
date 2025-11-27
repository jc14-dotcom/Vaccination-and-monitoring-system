<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Record</title>
    <link rel="stylesheet" href="{{ asset('css/vaccination_form.css') }}">
    <style>
        .privacy-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .privacy-modal-content {
            background-color: #fefefe;
            margin: 2% auto;
            /* Lifted higher on the page */
            padding: 25px;
            border: 1px solid #888;
            width: 80%;
            max-width: 700px;
            border-radius: 12px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        .privacy-modal h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.5rem;
        }

        .privacy-modal p {
            margin-bottom: 18px;
            line-height: 1.6;
            font-size: 1rem;
            text-align: justify;
        }

        .consent-checkbox {
            margin: 25px 0;
            padding: 18px;
            background-color: #f0f7fa;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .consent-checkbox label {
            display: flex;
            align-items: flex-start;
            font-weight: bold;
            line-height: 1.4;
        }

        .consent-checkbox input[type="checkbox"] {
            margin-right: 12px;
            margin-top: 3px;
            transform: scale(1.5);
        }

        .modal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
        }

        .modal-buttons button {
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        .cancel-button {
            background-color: #e74c3c;
            color: white;
            border: none;
        }

        .cancel-button:hover {
            background-color: #c0392b;
        }

        .agree-button {
            background-color: #27ae60;
            color: white;
            border: none;
            opacity: 0.5;
            cursor: not-allowed;
        }

        .agree-button.active {
            opacity: 1;
            cursor: pointer;
        }

        .agree-button.active:hover {
            background-color: #219a52;
        }
    </style>
</head>

<body>
    <header>
        <!-- Back Button -->
        <div class="back-button">
            <a href="{{ route('health_worker.patients') }}" class="btn btn-secondary">
                <img src="{{ asset('images/arrow.png') }}" alt="Back">
            </a>
        </div>

        <!-- Images and Title -->
        <div class="logos-left">
            <img src="{{ asset('images/todo.png') }}" alt="Logo Left 1">
        </div>

        <div class="title">IMMUNIZATION CARD</div>

        <div class="logos-right">
            <img src="{{ asset('images/doh-logo.png') }}" alt="Logo Right 1">
            <img src="{{ asset('images/right.png') }}" alt="Logo Right 2">
        </div>
    </header>

    <!-- Success Message Modal -->
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

    <form action="{{ route('auth.saveRecord') }}" method="POST" id="editForm">
        @csrf
        <div class="form-container">
            <!-- Left Column -->
            <div class="left-column">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" placeholder="Firstname, M.I, Lastname" required>
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                </div>

                <div class="form-group">
                    <label for="place_of_birth">Place of Birth:</label>
                    <input type="text" id="place_of_birth" name="place_of_birth" required>
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>
                </div>

                <div class="form-group">
                    <label for="barangay">Barangay:</label>
                    <select id="barangay" name="barangay" required>
                        <option value="" disabled selected>Select Barangay</option>
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

            <!-- Right Column -->
            <div class="right-column">
                <div class="form-group">
                    <label for="mothers_name">Mother's Name:</label>
                    <input type="text" id="mothers_name" name="mothers_name" placeholder="Firstname, M.I, Lastname"
                        required>
                </div>

                <div class="form-group">
                    <label for="fathers_name">Father's Name:</label>
                    <input type="text" id="fathers_name" name="fathers_name" placeholder="Firstname, M.I, Lastname"
                        required>
                </div>

                <div class="form-group-row">
                    <div class="form-group small-input">
                        <label for="birth-weight">Birth Weight (kg)</label>
                        <input type="number" id="birth-weight" name="birth_weight" step="0.01" placeholder="e.g., 3.5"
                            required>
                    </div>

                    <div class="form-group small-input">
                        <label for="birth-height">Birth Height (cm)</label>
                        <input type="number" id="birth-height" name="birth_height" step="0.1" placeholder="e.g., 50"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sex">Sex:</label>
                    <select id="sex" name="sex" required>
                        <option value="" disabled selected>Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="contact_no">Contact No.:</label>
                    <input type="text" id="contact_no" name="contact_no" required>
                </div>
            </div>
        </div>

        <!-- Vaccine Table -->
        <div class="table-container">
            <table class="vaccine-table">
                <thead>
                    <tr>
                        <th>BAKUNA</th>
                        <th>DOSES</th>
                        <th colspan="2">PETSA NG BAKUNA</th>
                        <th>REMARKS</th>
                    </tr>
                </thead>

                <tr>
                    <td>BCG Vaccine</td>
                    <td>Pagkapanganak</td>
                    <input type="hidden" name="vaccines[0][vaccine_name]" value="BCG">
                    <input type="hidden" name="vaccines[0][doses_description]" value="Pagkapanganak">
                    <td colspan="2" class="single-date-cell">
                        <input type="date" name="vaccines[0][dose_1_date]">
                    </td>
                    <td><textarea name="vaccines[0][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Hepatitis B Vaccine</td>
                    <td>Pagkapanganak</td>
                    <input type="hidden" name="vaccines[1][vaccine_name]" value="Hepatitis B">
                    <input type="hidden" name="vaccines[1][doses_description]" value="Pagkapanganak">
                    <td colspan="2" class="single-date-cell">
                        <input type="date" name="vaccines[1][dose_1_date]">
                    </td>
                    <td><textarea name="vaccines[1][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Pentavalent Vaccine</td>
                    <td>1 1/2, 2 1/2, 3 1/2 Buwan</td>
                    <input type="hidden" name="vaccines[2][vaccine_name]" value="Pentavalent">
                    <input type="hidden" name="vaccines[2][doses_description]" value="1 1/2, 2 1/2, 3 1/2 Buwan">
                    <td colspan="2" class="triple-date-cell">
                        <div class="triple-date-container">
                            <input type="date" name="vaccines[2][dose_1_date]">
                            <input type="date" name="vaccines[2][dose_2_date]">
                            <input type="date" name="vaccines[2][dose_3_date]">
                        </div>
                    </td>
                    <td><textarea name="vaccines[2][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Oral Polio Vaccine</td>
                    <td>1 1/2, 2 1/2, 3 1/2 Buwan</td>
                    <input type="hidden" name="vaccines[3][vaccine_name]" value="Oral Polio">
                    <input type="hidden" name="vaccines[3][doses_description]" value="1 1/2, 2 1/2, 3 1/2 Buwan">
                    <td colspan="2" class="triple-date-cell">
                        <div class="triple-date-container">
                            <input type="date" name="vaccines[3][dose_1_date]">
                            <input type="date" name="vaccines[3][dose_2_date]">
                            <input type="date" name="vaccines[3][dose_3_date]">
                        </div>
                    </td>
                    <td><textarea name="vaccines[3][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Inactivated Polio Vaccine</td>
                    <td>3 1/2 & 9 Buwan</td>
                    <input type="hidden" name="vaccines[4][vaccine_name]" value="Inactivated Polio">
                    <input type="hidden" name="vaccines[4][doses_description]" value="3 1/2 & 9 Buwan">
                    <td colspan="2" class="double-date-cell">
                        <div class="double-date-container">
                            <input type="date" name="vaccines[4][dose_1_date]">
                            <input type="date" name="vaccines[4][dose_2_date]">
                        </div>
                    </td>
                    <td><textarea name="vaccines[4][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Pneumococcal Conjugate Vaccine</td>
                    <td>1 1/2, 2 1/2, 3 1/2 Buwan</td>
                    <input type="hidden" name="vaccines[5][vaccine_name]" value="Pneumococcal Conjugate">
                    <input type="hidden" name="vaccines[5][doses_description]" value="1 1/2, 2 1/2, 3 1/2 Buwan">
                    <td colspan="2" class="triple-date-cell">
                        <div class="triple-date-container">
                            <input type="date" name="vaccines[5][dose_1_date]">
                            <input type="date" name="vaccines[5][dose_2_date]">
                            <input type="date" name="vaccines[5][dose_3_date]">
                        </div>
                    </td>
                    <td><textarea name="vaccines[5][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Measles, Mumps, Rubella Vaccine</td>
                    <td>9 Buwan & 1 Taon</td>
                    <input type="hidden" name="vaccines[6][vaccine_name]" value="Measles, Mumps, Rubella">
                    <input type="hidden" name="vaccines[6][doses_description]" value="9 Buwan & 1 Taon">
                    <td colspan="2" class="double-date-cell">
                        <div class="double-date-container">
                            <input type="date" name="vaccines[6][dose_1_date]">
                            <input type="date" name="vaccines[6][dose_2_date]">
                        </div>
                    </td>
                    <td><textarea name="vaccines[6][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:left; font-weight:bold;">SCHOOL AGED CHILDREN</td>
                </tr>
                <tr>
                    <td>Measles Containing Vaccine</td>
                    <td>Grade 1</td>
                    <input type="hidden" name="vaccines[7][vaccine_name]" value="Measles Containing">
                    <input type="hidden" name="vaccines[7][doses_description]" value="Grade 1">
                    <td colspan="2" class="single-date-cell">
                        <input type="date" name="vaccines[7][dose_1_date]">
                    </td>
                    <td><textarea name="vaccines[7][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Measles Containing Vaccine</td>
                    <td>Grade 7</td>
                    <input type="hidden" name="vaccines[8][vaccine_name]" value="Measles Containing">
                    <input type="hidden" name="vaccines[8][doses_description]" value="Grade 7">
                    <td colspan="2" class="double-date-cell">
                        <div class="double-date-container">
                            <input type="date" name="vaccines[8][dose_1_date]">
                            <input type="date" name="vaccines[8][dose_2_date]">
                        </div>
                    </td>
                    <td><textarea name="vaccines[8][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Tetanus Diphtheria</td>
                    <td>Grade 1 & 7</td>
                    <input type="hidden" name="vaccines[9][vaccine_name]" value="Tetanus Diphtheria">
                    <input type="hidden" name="vaccines[9][doses_description]" value="Grade 1 & 7">
                    <td colspan="2" class="double-date-cell">
                        <div class="double-date-container">
                            <input type="date" name="vaccines[9][dose_1_date]">
                            <input type="date" name="vaccines[9][dose_2_date]">
                        </div>
                    </td>
                    <td><textarea name="vaccines[9][remarks]" class="remarks-input"></textarea></td>
                </tr>
                <tr>
                    <td>Human Papillomavirus Vaccine</td>
                    <td>Grade 4 - (Babae) 9-14 Taon Gulang</td>
                    <input type="hidden" name="vaccines[10][vaccine_name]" value="Human Papillomavirus">
                    <input type="hidden" name="vaccines[10][doses_description]"
                        value="Grade 4 - (Babae) 9-14 Taon Gulang">
                    <td colspan="2" class="double-date-cell">
                        <div class="double-date-container">
                            <input type="date" name="vaccines[10][dose_1_date]">
                            <input type="date" name="vaccines[10][dose_2_date]">
                        </div>
                    </td>
                    <td><textarea name="vaccines[10][remarks]" class="remarks-input"></textarea></td>
                </tr>
                </tbody>
            </table>
            <!-- Save Button (styled with existing CSS) -->
            <div class="flex justify-end mt-8">
                <button type="button" class="save-button vaccination-save">Save</button>
            </div>
        </div>

        <!-- Modified Data Privacy Modal -->
        <div id="privacyModal" class="privacy-modal">
            <div class="privacy-modal-content">
                <h2>PAGPAPATUNAY NG PAGSUNOD SA BATAS SA PRIVACY NG DATA</h2>

                <p>
                    Bago ipasok ang datos ng pasyente sa RHU Infant Vaccination System, pinatutunayan ko na ipinaalam ko
                    sa magulang o tagapag-alaga ang layunin ng pagkolekta ng kanilang personal na impormasyon at
                    impormasyon ng kanilang anak. Ipinaliwanag ko na ito ay gagamitin para sa pagtatala ng kasaysayan ng
                    pagbabakuna, pag-iskedyul ng mga susunod na bakuna, pagsubaybay at pag-uulat ng pampublikong
                    kalusugan, at pakikipag-ugnayan sa kanila kaugnay ng kalusugan ng kanilang anak.
                </p>

                <p>
                    Ipinaliwanag ko rin na ang kanilang datos ay ligtas na itatago sa sistema at magkakaroon lamang ng
                    limitadong access ang mga awtorisadong kawani ng kalusugan. Bukod dito, ipinaalam ko na ang kanilang
                    impormasyon ay maaaring ibahagi sa Department of Health at iba pang kaugnay na ahensya ng kalusugan
                    para sa layunin ng pagsubaybay ng pampublikong kalusugan.
                </p>

                <p>
                    Dagdag pa rito, ipinaalam ko sa kanila ang kanilang mga karapatan sa ilalim ng Data Privacy Act of
                    2012, kabilang ang karapatan nilang i-access ang kanilang impormasyon, itama ang anumang hindi
                    tamang datos, at humiling ng pagtanggal ng impormasyon kung naaangkop. Nagbigay rin ako ng
                    impormasyon sa pakikipag-ugnayan para sa RHU Data Protection Officer kung sakaling may mga
                    katanungan o alalahanin sila tungkol sa kanilang datos.
                </p>

                <p>
                    Pinatutunayan ko na ang magulang o tagapag-alaga ay nagbigay ng kanilang pasalitang pahintulot upang
                    ipasok ang kanilang impormasyon at impormasyon ng kanilang anak sa RHU Infant Vaccination System.
                    Nauunawaan nila kung paano ito gagamitin at poprotektahan, at nabigyan sila ng pagkakataong
                    magtanong tungkol sa privacy ng kanilang datos.
                </p>

                <div class="consent-checkbox">
                    <label for="privacyConsent">
                        <input type="checkbox" id="privacyConsent" name="privacy_consent">
                        Tinitiyak ko na natapos ko ang lahat ng hakbang sa proseso at nakakuha ng tamang pahintulot bago
                        ipasok ang kanilang impormasyon sa sistema.
                    </label>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="cancel-button" id="cancelButton">Kanselahin</button>
                    <button type="button" class="agree-button" id="agreeButton">Sumasang-ayon ako at Magpatuloy</button>
                </div>
            </div>
        </div>

        <footer>
            <p>
                Sa column ng Petsa ng bakuna, Isulat ang petsa ng pagbibigay ng bakuna ayon sa kung ilang dose ito. Sa
                column ng remarks, isulat ang petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon
                na
                maaring maka apekto sa pag babakuna ng bata.
            </p>
        </footer>

        <script src="{{ asset('javascript/vaccination_form.js') }}"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('editForm');
                const saveButton = document.querySelector('.save-button');
                const privacyModal = document.getElementById('privacyModal');
                const agreeButton = document.getElementById('agreeButton');
                const cancelButton = document.getElementById('cancelButton');
                const consentCheckbox = document.getElementById('privacyConsent');

                // Modify modal styles to lift it up
                const modalContent = document.querySelector('.privacy-modal-content');
                if (modalContent) {
                    // Center the modal higher on the page
                    modalContent.style.margin = "2% auto";
                    // Add a subtle shadow for better visibility
                    modalContent.style.boxShadow = "0 4px 20px rgba(0, 0, 0, 0.25)";
                }

                // When the save button is clicked, show the privacy modal instead of submitting
                saveButton.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Validate the form first
                    if (form.checkValidity()) {
                        privacyModal.style.display = 'block';
                    } else {
                        // If the form is not valid, trigger the browser's validation
                        form.reportValidity();
                    }
                });

                // Enable/disable the agree button based on checkbox
                consentCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        agreeButton.classList.add('active');
                    } else {
                        agreeButton.classList.remove('active');
                    }
                });

                // When the agree button is clicked and consent is checked, submit the form
                agreeButton.addEventListener('click', function () {
                    if (consentCheckbox.checked) {
                        // Add a hidden field to the form to indicate consent was given
                        const hiddenField = document.createElement('input');
                        hiddenField.type = 'hidden';
                        hiddenField.name = 'data_privacy_consent';
                        hiddenField.value = 'yes';
                        form.appendChild(hiddenField);

                        // Submit the form
                        form.submit();
                    }
                });

                // When the cancel button is clicked, close the modal
                cancelButton.addEventListener('click', function () {
                    privacyModal.style.display = 'none';
                });

                // Close the modal if clicking outside of it
                window.addEventListener('click', function (event) {
                    if (event.target == privacyModal) {
                        privacyModal.style.display = 'none';
                    }
                });

                // Additional handling for any existing success modal
                const successModal = document.getElementById('successModal');
                const okButton = document.getElementById('okButton');

                if (successModal && okButton) {
                    okButton.addEventListener('click', function () {
                        successModal.style.display = 'none';
                    });
                }
            });
        </script>
</body>

</html>