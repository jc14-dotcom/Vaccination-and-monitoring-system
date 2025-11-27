<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/parent_profile.css') }}">
</head>

<body>
    <header>
        <div class="header-content">
            <a href="{{ route('parent.dashboard') }}" class="back-button">
                <img src="{{ asset('images/arrow.png') }}" alt="Back">
            </a>
            <h1>{{ $patient->mother_name }}'s Profile</h1>
        </div>
    </header>

    <div class="container">
        <form action="{{ route('updateProfile') }}" method="POST" id="profileForm">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="contact_no">Contact No:</label>
                <input type="text" id="contact_no" name="contact_no" value="{{ $patient->contact_no }}" disabled>
                <small id="contactError" class="form-text text-danger"></small>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="{{ auth('parents')->user()->email ?? '' }}" disabled required>
                <small id="emailError" class="form-text text-danger"></small>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="{{ $patient->address }}" disabled>
            </div>

            <div class="form-group">
                <label for="barangay">Barangay:</label>
                <select id="barangay" name="barangay" disabled required>
                    <option value="" disabled>Select Barangay</option>
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

            <div class="btn-group">
                <button type="button" id="editButton" class="btn btn-primary" onclick="enableEditing()">
                    <i class="fas fa-edit mr-2"></i>Edit
                </button>
                <button type="submit" id="saveButton" class="btn btn-success" disabled>
                    <i class="fas fa-save mr-2"></i>Save
                </button>
            </div>
        </form>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Profile Update</h5>
                </div>
                <div class="modal-body">
                    <i class="fas fa-check-circle"></i>
                    <p>Your profile has been successfully updated!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; RHU Calauan 2024
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Set initial barangay value
        document.getElementById('barangay').value = "{{ $patient->barangay }}";

        // Enable editing function
        function enableEditing() {
            document.getElementById('contact_no').disabled = false;
            document.getElementById('email').disabled = false;
            document.getElementById('address').disabled = false;
            document.getElementById('barangay').disabled = false;
            document.getElementById('saveButton').disabled = false;
            document.getElementById('editButton').classList.add('editing');
        }

        // Validate contact number
        function validateContact() {
            const contactInput = document.getElementById('contact_no');
            const contactError = document.getElementById('contactError');
            const contactRegex = /^09\d{9}$/;
            
            if (!contactRegex.test(contactInput.value)) {
                contactError.textContent = 'Contact number must start with 09 and be exactly 11 digits';
                return false;
            } else {
                contactError.textContent = '';
                return true;
            }
        }

        // Validate email
        function validateEmail() {
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(emailInput.value)) {
                emailError.textContent = 'Please enter a valid email address';
                return false;
            } else {
                emailError.textContent = '';
                return true;
            }
        }

        // Add event listeners for real-time validation
        document.getElementById('contact_no').addEventListener('input', validateContact);
        document.getElementById('email').addEventListener('input', validateEmail);

        // Form submission with validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate before submission
            const isContactValid = validateContact();
            const isEmailValid = validateEmail();
            
            if (!isContactValid || !isEmailValid) {
                return; // Stop submission if validation fails
            }
            
            $.ajax({
                url: '{{ route("updateProfile") }}',
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#successModal').modal('show');
                        document.getElementById('contact_no').disabled = true;
                        document.getElementById('email').disabled = true;
                        document.getElementById('address').disabled = true;
                        document.getElementById('barangay').disabled = true;
                        document.getElementById('saveButton').disabled = true;
                        document.getElementById('editButton').classList.remove('editing');
                    } else {
                        alert('Error updating profile. Please try again.');
                    }
                },
                error: function() {
                    alert('Error updating profile. Please try again.');
                }
            });
        });
    </script>
</body>

</html>