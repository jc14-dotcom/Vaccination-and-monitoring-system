<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
    <!-- Tailwind utilities -->
    <link rel="stylesheet" href="{{ asset('css/tailwind-utilities.css') }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f4f4f9;
        }        /* Custom styles that complement Tailwind */        .header {
            background-color: #8e6bc8;
            color: white;
            font-size: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            height: 80px;
        }/* We'll replace these with Tailwind classes */
        .header .profile {
            position: relative;
            cursor: pointer;
        }

        .header .profile img {
            border-radius: 50%;
        }

        .container {
            padding: 2rem;
        }        /* Add transition effects */
        
        .child-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .child-card:hover {
            transform: translateY(-2px);
        }/* Dropdown menu styles */        .dropdown-menu {
    display: none;
    position: absolute;
    top: 60px; /* Adjust as needed for spacing below the icon */
    left: 0;
    right: auto;
    min-width: 180px;
    z-index: 1000;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    padding: 0.5rem 0;
    box-sizing: border-box;
    overflow: hidden;
}

.dropdown-menu.show {
    display: block !important;
}

.dropdown-menu a,
.dropdown-menu button {
    display: block;
    width: 100%;
    padding: 0.75rem 1.25rem;
    color: #6b21a8;
    background: none;
    border: none;
    text-align: left;
    font-size: 1rem;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.2s;
    border-radius: 0;
    text-decoration: none; /* Remove underline from links */
    box-sizing: border-box;
}

.dropdown-menu a:hover,
.dropdown-menu button:hover {
    background: #f3e8ff;
    color: #4b2995;
    text-decoration: none;
    /* Remove z-index and position to prevent overlap */
}        /* Modal styles */
        .logout-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .logout-modal.show {
            display: flex !important;
        }
        
        /* Fix Tailwind conflicts with display property */
        .tw-flex {
            display: flex !important;
        }
    </style>
</head>
<body class="tw-font-sans">    <div class="header tw-flex tw-justify-between tw-items-center" style="height: 80px; padding-left: 40px; padding-right: 40px;">        <div class="profile tw-flex tw-items-center tw-relative" id="profile-icon">
            <img src="{{ asset('images/account.png') }}" alt="Parent Icon" title="Profile" style="width: 50px; height: 50px; border-radius: 50%;" class="tw-mr-8">
            <span class="tw-font-semibold" style="font-size: 24px; padding: 10px;">{{ $user->username ?? 'No Username Found' }}</span>
            <div class="dropdown-menu tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-shadow-md" id="profile-dropdown">
                <a href="{{ route('parents.profile') }}" class="tw-block tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-bg-gray-100 tw-text-sm">Profile</a>
                <a href="{{ route('parents.change-password') }}" class="tw-block tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-bg-gray-100 tw-text-sm">Change Password</a>
                <button id="logout-option" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-gray-700 hover:tw-bg-gray-100 tw-text-sm tw-border-0 tw-bg-transparent">Logout</button>
            </div>
        </div>        <div class="tw-flex tw-items-center">
            <img src="{{ asset('images/bell (1).png') }}" alt="Notifications" title="Notifications" style="width: 33px; height: 33px;" class="tw-cursor-pointer">
        </div>    </div>      <div class="tw-bg-gray-100" style="padding-top: 50px; padding-bottom: 50px;">
        <div class="tw-max-w-7xl tw-mx-auto" style="padding-left: 40px; padding-right: 40px;">            
            <h2 class="tw-font-bold tw-mb-10" style="font-size: 28px; color: #333;">Children</h2>              
            <div class="tw-grid tw-grid-cols-1" style="gap: 25px;">
                @forelse($patients as $patient)                    <div class="child-card tw-bg-white tw-rounded-lg tw-shadow-md hover:tw-shadow-lg tw-transition-all tw-duration-200 tw-cursor-pointer" 
                        style="padding: 18px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" 
                        data-patient-id="{{ $patient->id }}">
                        <div class="tw-font-bold tw-text-gray-700" style="font-size: 24px; line-height: 1.4;">{{ $patient->name }}</div>
                    </div>
                @empty                    <div class="tw-bg-white tw-rounded-lg tw-shadow-md" style="padding: 35px 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <p class="tw-text-gray-600" style="font-size: 24px; line-height: 1.4;">No children found.</p>
                    </div>
                @endforelse
            </div>
              </div>
        
        <!-- Logout Modal -->
        <div class="logout-modal" id="logout-modal">
            <div class="modal-content" style="background: #fff; border-radius: 12px; box-shadow: 0 6px 24px rgba(0,0,0,0.15); max-width: 370px; width: 100%; margin: auto; text-align: center; padding: 2rem 1.5rem 1.5rem 1.5rem;">
                <p style="font-size: 1.15rem; color: #22223b; margin-bottom: 1.5rem; font-weight: 500;">Are you sure you want to log out?</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button id="cancel-logout" style="background: #f3f4f6; color: #22223b; min-width: 90px; padding: 0.6rem 1.2rem; border-radius: 0.5rem; font-size: 0.98rem; font-weight: 600; border: none; cursor: pointer; transition: background 0.2s, color 0.2s;">Cancel</button>
                    <button id="confirm-logout" style="background: #ef4444; color: #fff; min-width: 90px; padding: 0.6rem 1.2rem; border-radius: 0.5rem; font-size: 0.98rem; font-weight: 600; border: none; cursor: pointer; transition: background 0.2s, color 0.2s;">Logout</button>
                </div>
            </div>
        </div>
    </div>    <script>        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get modal and buttons
            const logoutModal = document.getElementById('logout-modal');
            const profileIcon = document.getElementById('profile-icon');
            const profileDropdown = document.getElementById('profile-dropdown');
            const logoutOption = document.getElementById('logout-option');
            const confirmLogout = document.getElementById('confirm-logout');
            const cancelLogout = document.getElementById('cancel-logout');

        // Toggle dropdown on profile icon click
        profileIcon.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent click from bubbling up
            profileDropdown.classList.toggle('show');
        });

        // Show modal on logout option click
        logoutOption.addEventListener('click', () => {
            logoutModal.classList.add('show');
            profileDropdown.classList.remove('show'); // Close dropdown
        });

        // Close modal and stay on the dashboard
        cancelLogout.addEventListener('click', () => {
            logoutModal.classList.remove('show');
        });

        // Redirect to welcome page on confirm
        confirmLogout.addEventListener('click', () => {
            window.location.href = '{{ route('welcome') }}';
        });

        // Close modal if user clicks outside of it
        window.addEventListener('click', (event) => {
            if (event.target === logoutModal) {
                logoutModal.classList.remove('show');
            }
            // Close dropdown if clicking outside
            if (!profileIcon.contains(event.target)) {
                profileDropdown.classList.remove('show');
            }
        });            // Redirect to infantsRecord when a child card is clicked
            document.querySelectorAll('.child-card').forEach(card => {
                card.addEventListener('click', function () {
                    const patientId = this.getAttribute('data-patient-id');
                    if (patientId) {
                        window.location.href = "/infantsRecord/" + patientId;
                    }
                });
            });
        });
    </script>
</body>
</html>