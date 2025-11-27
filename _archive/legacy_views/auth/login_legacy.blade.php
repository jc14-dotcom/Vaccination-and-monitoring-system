<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            height: 100vh;
            background-color: #7a5bbd;
            font-family: 'Arial', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: white;
            border-radius: 50%;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .login-container {
            background: white;
            width: 1000px;
            height: 600px;
            display: flex;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .image-section {
            flex: 1;
            background-image: url('{{ asset("images/background.jpg") }}');
            background-size: cover;
            background-position: center;
        }

        .form-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #7a5bbd;
        }

        input {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            outline: none;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #7a5bbd;
            box-shadow: 0 0 0 3px rgba(122, 91, 189, 0.1);
        }

        .password-container {
            position: relative;
        }

        .see-password {
            position: absolute;
            right: 15px;
            top: 15px;
            margin-top: 30px;
            width: 24px;
            height: 24px;
            cursor: pointer;
            opacity: 0.6;
            z-index: 10;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .login-btn {
            flex: 1;
            background: #7a5bbd;
            color: white;
            border: none;
            padding: 12px 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s;
            width: 90%;
            max-width: 500px; /* Adjust as needed */
            margin: 2px auto;
            display: block;
        }

        .forgot-btn {
            flex: 1;
            background: transparent;
            color: #7a5bbd;
            border: 1px solid #7a5bbd;
            padding: 12px 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s;
        }

        .login-btn:hover {
            background: #6447a0;
        }

        .forgot-btn:hover {
            background: #f8f4ff;
        }

        /* Error message styles */
        .error-message {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 8px;
            margin-top: 5px;
            font-size: 0.9rem;
            animation: fadeIn 0.3s ease-out;
        }
        
        .credentials-error {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: 500;
            display: none;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 15px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: modalSlideIn 0.3s ease-out;
        }

        .modal-header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
            position: relative;
        }

        .modal-header h2 {
            color: #7a5bbd;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close-modal {
            position: absolute;
            right: -10px;
            top: -10px;
            width: 30px;
            height: 30px;
            background: #7a5bbd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s;
            border: none;
        }

        .close-modal:hover {
            background: #6447a0;
        }

        .success-message {
            display: none;
            color: #28a745;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-top: 1rem;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @media (max-width: 768px) {
            .login-container {
                width: 95%;
                flex-direction: column;
                height: auto;
            }

            .image-section {
                height: 200px;
            }

            .button-group {
                flex-direction: column;
            }

            .modal-content {
                width: 95%;
                margin: 20px;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <a href="{{ url('/') }}" class="back-button">
        <img src="{{ asset('images/arrowv.png') }}" alt="Back" width="24" height="24">
    </a>

    <div class="login-container">
        <div class="image-section"></div>
        <div class="form-section">
            <form id="loginForm" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" value="{{ old('username') }}" required>
                    @error('username')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group password-container">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <img src="{{ asset('images/hide_password.png') }}" alt="Toggle password" class="see-password" onclick="togglePasswordVisibility()">
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    
                    <!-- Error message placed directly under password field -->
                    <div id="credentialsError" class="credentials-error">
                        Incorrect username or password. Please try again.
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="login-btn">Login</button>
                    <button type="button" class="forgot-btn">Forgot Password?</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reset Password</h2>
                <button class="close-modal">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="resetForm">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="Enter your email address"
                            autocomplete="email">
                    </div>
                    <button type="submit" class="login-btn">
                        Send Code
                    </button>
                </form>

                <div id="successMessage" class="success-message">
                    Password reset link has been sent to your email!
                </div>
                <div id="errorMessage" class="error-message" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script>
 // Check for authentication errors from Laravel session
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's an authentication error from server
    @if(session('error'))
        showCredentialsError("{{ session('error') }}");
    @endif
    
    // Check for invalid_credentials error from Laravel
    @if(session('status') === 'invalid_credentials')
        showCredentialsError("The password you entered is incorrect.");
    @endif
});

function showCredentialsError(message) {
    const credentialsError = document.getElementById('credentialsError');
    credentialsError.textContent = message;
    credentialsError.style.display = 'block';
    
    // Shake animation removed
}

// Handle form submission with client-side validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!username || !password) {
        e.preventDefault();
        showCredentialsError("Please enter both username and password");
        return false;
    }
});

// Handle form submission with client-side validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!username || !password) {
        e.preventDefault();
        showCredentialsError("Please enter both username and password");
        return false;
    }
});

        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const icon = event.target;

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.src = '{{ asset("images/see_password.png") }}';
            } else {
                passwordField.type = 'password';
                icon.src = '{{ asset("images/hide_password.png") }}';
            }
        }

        const modal = document.getElementById('forgotPasswordModal');
        const closeBtn = document.querySelector('.close-modal');
        const forgotBtn = document.querySelector('.forgot-btn');
        const resetForm = document.getElementById('resetForm');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');

        forgotBtn.onclick = function() {
            modal.style.display = 'flex';
        }

        closeBtn.onclick = function() {
            closeModal();
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        function closeModal() {
            modal.style.display = 'none';
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
            resetForm.reset();
            resetForm.style.display = 'block';
        }

        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.email.value;
            
            // Add loading state to button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            fetch('{{ route("password.email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    successMessage.style.display = 'block';
                    resetForm.style.display = 'none';
                    
                    setTimeout(() => {
                        closeModal();
                    }, 3000);
                } else {
                    errorMessage.textContent = data.message || 'An error occurred. Please try again.';
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.style.display = 'block';
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Reset Link';
            });
        });
        
// Add this to your existing scripts in login.blade.php
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a lockout error message
    @if(session('error') && str_contains(session('error'), 'Too many failed attempts'))
        const errorText = "{{ session('error') }}";
        const secondsMatch = errorText.match(/(\d+) seconds/);
        if (secondsMatch && secondsMatch[1]) {
            startLockoutTimer(parseInt(secondsMatch[1]));
        }
    @endif
});

function startLockoutTimer(seconds) {
    const loginBtn = document.querySelector('.login-btn');
    const credentialsError = document.getElementById('credentialsError');
    
    // Disable the login button
    loginBtn.disabled = true;
    
    // Update the error message with countdown
    let remainingTime = seconds;
    credentialsError.style.display = 'block';
    credentialsError.textContent = `Too many failed attempts. Please try again after ${remainingTime} seconds.`;
    
    // Update countdown every second
    const countdownInterval = setInterval(function() {
        remainingTime--;
        
        if (remainingTime <= 0) {
            // Re-enable login when timer completes
            clearInterval(countdownInterval);
            loginBtn.disabled = false;
            credentialsError.textContent = "You can try logging in again now.";
            
            // Hide the message after 3 seconds
            setTimeout(() => {
                credentialsError.style.display = 'none';
            }, 3000);
        } else {
            credentialsError.textContent = `Too many failed attempts. Please try again after ${remainingTime} seconds.`;
        }
    }, 1000);
}

    </script>
</body>
</html>