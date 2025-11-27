<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Report Settings - Infant Immunization System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .toggle-checkbox:checked + .toggle-bg {
            background-color: #7c3aed !important;
            border-color: #7c3aed !important;
        }
        .toggle-checkbox:checked + .toggle-bg .toggle-dot {
            transform: translateX(1.5rem) !important;
        }
        .toggle-bg {
            transition: all 0.3s ease;
        }
        .toggle-dot {
            transition: transform 0.3s ease;
        }
        .gradient-border-top {
            border-top: 3px solid;
            border-image: linear-gradient(to right, #7a5bbd, #c084fc) 1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" class="text-white py-6 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('reports.current') }}" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-arrow-left text-2xl"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold">Report Settings</h1>
                        <p class="text-purple-100 text-sm mt-1">Configure automatic report generation and storage</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-purple-100">{{ Auth::guard('health_worker')->user()->username ?? 'Health Worker' }}</p>
                    <p class="text-xs text-purple-200">{{ Auth::guard('health_worker')->user()->email ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Success/Error Messages -->
        <div id="toast" class="hidden fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-semibold">
            <div class="flex items-center space-x-3">
                <i id="toast-icon" class="fas text-xl"></i>
                <span id="toast-message"></span>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden gradient-border-top">
            <div class="p-8">
                <form id="settingsForm">
                    @csrf
                    
                    <!-- General Settings -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-cog text-purple-600 mr-3"></i>
                            General Settings
                        </h2>
                        <div class="space-y-4">
                            <!-- Enable Auto-Save -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="text-gray-800 font-semibold">Enable Auto-Save Reports</label>
                                    <p class="text-sm text-gray-600 mt-1">Automatically generate and save reports on schedule</p>
                                </div>
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" id="mainToggle" name="enabled" class="toggle-checkbox sr-only" 
                                               {{ $settings['enabled'] ? 'checked' : '' }} value="1">
                                        <div class="toggle-bg block w-14 h-8 rounded-full border-2 border-gray-300 bg-gray-300"></div>
                                        <div class="toggle-dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full shadow-md"></div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Settings -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-calendar-alt text-purple-600 mr-3"></i>
                            Schedule Settings
                        </h2>
                        <div class="space-y-4">
                            <!-- Monthly Auto-Save -->
                            <div id="monthlyToggleContainer" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="text-gray-800 font-semibold">Monthly Auto-Save</label>
                                    <p class="text-sm text-gray-600 mt-1">Generate report at 1:00 AM on the 1st of each month</p>
                                </div>
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" id="monthlyToggle" name="monthly_enabled" class="toggle-checkbox sr-only schedule-toggle" 
                                               {{ $settings['monthly_enabled'] ? 'checked' : '' }} value="1">
                                        <div class="toggle-bg block w-14 h-8 rounded-full border-2 border-gray-300 bg-gray-300"></div>
                                        <div class="toggle-dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full shadow-md"></div>
                                    </div>
                                </label>
                            </div>

                            <!-- Quarterly Auto-Save -->
                            <div id="quarterlyToggleContainer" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="text-gray-800 font-semibold">Quarterly Auto-Save</label>
                                    <p class="text-sm text-gray-600 mt-1">Generate report at 2:00 AM on Jan 1, Apr 1, Jul 1, Oct 1</p>
                                </div>
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" id="quarterlyToggle" name="quarterly_enabled" class="toggle-checkbox sr-only schedule-toggle" 
                                               {{ $settings['quarterly_enabled'] ? 'checked' : '' }} value="1">
                                        <div class="toggle-bg block w-14 h-8 rounded-full border-2 border-gray-300 bg-gray-300"></div>
                                        <div class="toggle-dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full shadow-md"></div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-bell text-purple-600 mr-3"></i>
                            Notification Settings
                        </h2>
                        <div class="space-y-4">
                            <!-- Enable Notifications -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="text-gray-800 font-semibold">Enable Email Notifications</label>
                                    <p class="text-sm text-gray-600 mt-1">Send email when reports are generated</p>
                                </div>
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" name="notifications_enabled" class="toggle-checkbox sr-only" 
                                               {{ $settings['notifications_enabled'] ? 'checked' : '' }} value="1">
                                        <div class="toggle-bg block w-14 h-8 rounded-full border-2 border-gray-300 bg-gray-300"></div>
                                        <div class="toggle-dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full shadow-md"></div>
                                    </div>
                                </label>
                            </div>

                            <!-- Notification Email -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="text-gray-800 font-semibold block mb-2">Notification Email</label>
                                <input type="email" name="notification_email" 
                                       value="{{ $settings['notification_email'] }}"
                                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-purple-600 focus:outline-none"
                                       placeholder="admin@example.com">
                                <p class="text-sm text-gray-600 mt-2">Email address to receive notifications</p>
                            </div>
                        </div>
                    </div>

                    <!-- Retention Settings -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-database text-purple-600 mr-3"></i>
                            Retention Settings
                        </h2>
                        <div class="space-y-4">
                            <!-- Keep Versions -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="text-gray-800 font-semibold block mb-2">Keep Report Versions</label>
                                <input type="number" name="keep_versions" 
                                       value="{{ $settings['keep_versions'] }}"
                                       min="1" max="50"
                                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-purple-600 focus:outline-none"
                                       placeholder="5">
                                <p class="text-sm text-gray-600 mt-2">Number of report versions to keep (1-50)</p>
                            </div>

                            <!-- Auto-Delete Old -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="text-gray-800 font-semibold">Auto-Delete Old Versions</label>
                                    <p class="text-sm text-gray-600 mt-1">Automatically delete versions beyond the retention limit</p>
                                </div>
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" name="auto_delete_old" class="toggle-checkbox sr-only" 
                                               {{ $settings['auto_delete_old'] ? 'checked' : '' }} value="1">
                                        <div class="toggle-bg block w-14 h-8 rounded-full border-2 border-gray-300 bg-gray-300"></div>
                                        <div class="toggle-dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full shadow-md"></div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-6 border-t-2 border-gray-200">
                        <a href="{{ route('reports.current') }}" 
                           class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" 
                                class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition shadow-lg">
                            <i class="fas fa-save mr-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Information Card -->
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-blue-800">Important Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Auto-saved reports are generated automatically based on the schedule</li>
                            <li>Monthly reports are saved on the 1st of each month at 1:00 AM</li>
                            <li>Quarterly reports are saved at the start of each quarter at 2:00 AM</li>
                            <li>Manual saves from the report page are not affected by these settings</li>
                            <li>Changes take effect immediately after saving</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token Setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Toast Notification Function
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toast-icon');
            const toastMessage = document.getElementById('toast-message');
            
            // Set icon and colors
            if (type === 'success') {
                toast.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                toastIcon.className = 'fas fa-check-circle text-xl';
            } else {
                toast.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                toastIcon.className = 'fas fa-exclamation-circle text-xl';
            }
            
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 4000);
        }

        // Form Submission
        document.getElementById('settingsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            // Convert form data to object
            for (let [key, value] of formData.entries()) {
                if (key !== '_token') {
                    data[key] = value;
                }
            }
            
            // Handle unchecked checkboxes
            const checkboxes = ['enabled', 'monthly_enabled', 'quarterly_enabled', 'notifications_enabled', 'auto_delete_old'];
            checkboxes.forEach(name => {
                data[name] = this.querySelector(`input[name="${name}"]`).checked ? 1 : 0;
            });
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            
            try {
                const response = await fetch('{{ route("reports.settings.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message || 'Settings updated successfully', 'success');
                } else {
                    showToast(result.message || 'Failed to update settings', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred while saving settings', 'error');
            } finally {
                // Restore button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Toggle visual feedback and sliding animation
        document.querySelectorAll('.toggle-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const toggleBg = this.nextElementSibling;
                const toggleDot = toggleBg.nextElementSibling;
                
                if (this.checked) {
                    toggleBg.style.backgroundColor = '#7c3aed';
                    toggleBg.style.borderColor = '#7c3aed';
                    toggleDot.style.transform = 'translateX(24px)';
                } else {
                    toggleBg.style.backgroundColor = '#d1d5db';
                    toggleBg.style.borderColor = '#d1d5db';
                    toggleDot.style.transform = 'translateX(0)';
                }
            });
            
            // Initialize colors and position on page load
            const toggleBg = checkbox.nextElementSibling;
            const toggleDot = toggleBg.nextElementSibling;
            
            if (checkbox.checked) {
                toggleBg.style.backgroundColor = '#7c3aed';
                toggleBg.style.borderColor = '#7c3aed';
                toggleDot.style.transform = 'translateX(24px)';
            } else {
                toggleBg.style.backgroundColor = '#d1d5db';
                toggleBg.style.borderColor = '#d1d5db';
                toggleDot.style.transform = 'translateX(0)';
            }
        });

        // Handle main toggle to enable/disable schedule toggles
        const mainToggle = document.getElementById('mainToggle');
        const monthlyToggle = document.getElementById('monthlyToggle');
        const quarterlyToggle = document.getElementById('quarterlyToggle');
        const monthlyContainer = document.getElementById('monthlyToggleContainer');
        const quarterlyContainer = document.getElementById('quarterlyToggleContainer');

        function updateScheduleToggles() {
            const isMainEnabled = mainToggle.checked;
            
            if (!isMainEnabled) {
                // Disable and uncheck schedule toggles
                monthlyToggle.checked = false;
                quarterlyToggle.checked = false;
                monthlyToggle.disabled = true;
                quarterlyToggle.disabled = true;
                
                // Visual feedback - make them appear disabled
                monthlyContainer.style.opacity = '0.5';
                quarterlyContainer.style.opacity = '0.5';
                monthlyContainer.style.pointerEvents = 'none';
                quarterlyContainer.style.pointerEvents = 'none';
                
                // Reset toggle visuals
                const monthlyBg = monthlyToggle.nextElementSibling;
                const monthlyDot = monthlyBg.nextElementSibling;
                const quarterlyBg = quarterlyToggle.nextElementSibling;
                const quarterlyDot = quarterlyBg.nextElementSibling;
                
                monthlyBg.style.backgroundColor = '#d1d5db';
                monthlyBg.style.borderColor = '#d1d5db';
                monthlyDot.style.transform = 'translateX(0)';
                
                quarterlyBg.style.backgroundColor = '#d1d5db';
                quarterlyBg.style.borderColor = '#d1d5db';
                quarterlyDot.style.transform = 'translateX(0)';
            } else {
                // Enable schedule toggles
                monthlyToggle.disabled = false;
                quarterlyToggle.disabled = false;
                monthlyContainer.style.opacity = '1';
                quarterlyContainer.style.opacity = '1';
                monthlyContainer.style.pointerEvents = 'auto';
                quarterlyContainer.style.pointerEvents = 'auto';
            }
        }

        // Initialize on page load
        updateScheduleToggles();

        // Listen to main toggle changes
        mainToggle.addEventListener('change', updateScheduleToggles);
    </script>
</body>
</html>
