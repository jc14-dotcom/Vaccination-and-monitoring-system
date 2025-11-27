@extends('layouts.responsive-layout')

@section('title', 'Backup & Restore')

@section('additional-styles')
<style>
    .hw-container { width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width: 640px){ .hw-container { padding-left:2rem; padding-right:2rem; } }
    @media (min-width: 1280px){ .hw-container { padding-left:2.5rem; padding-right:2.5rem; } }
    .hw-no-overflow-x { overflow-x:hidden; }
    /* Fade-in animation for header */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0">
    <!-- Page Banner -->
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4" data-animate>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Backup & Restore</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Protect your data with automated backups and easy restoration</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Database Info Card -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Database Information</h2>
                <div class="flex gap-6">
                    <div>
                        <span class="text-sm text-gray-600">Database:</span>
                        <span class="font-semibold text-gray-900 ml-2">{{ $dbInfo['name'] }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Size:</span>
                        <span class="font-semibold text-gray-900 ml-2">{{ $dbInfo['size'] }}</span>
                    </div>
                </div>
            </div>
            <button onclick="createBackup()" id="createBackupBtn" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-md hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Create Backup Now
            </button>
        </div>
    </div>

    <!-- Auto Backup Settings -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Automatic Backup Settings</h2>
        <div class="space-y-4">
            <!-- Enable/Disable Toggle -->
            <div class="flex items-center justify-between flex-wrap gap-4 pb-4 border-b border-gray-200">
                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-1">Enable Automatic Backups</p>
                    <p class="text-xs text-gray-600">Automatically create backups based on your schedule</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="autoBackupToggle" class="sr-only" onchange="toggleAutoBackup(this)">
                    <div class="relative">
                        <div class="toggle-bg block w-14 h-8 rounded-full border-2 border-gray-300 transition-all" style="background-color: #d1d5db;" id="toggleBackground"></div>
                        <div class="toggle-dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full shadow-md transition-transform" id="toggleCircle"></div>
                    </div>
                    <span class="ml-3 text-sm font-bold text-gray-900">
                        <span id="autoBackupStatus">Disabled</span>
                    </span>
                </label>
            </div>
            
            <!-- Schedule Selection -->
            <div id="scheduleSection" class="hidden">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Backup Schedule</label>
                <select id="backupSchedule" onchange="updateSchedule(this)" class="block w-full md:w-64 text-sm font-semibold text-gray-900 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary-600 p-3 bg-white">
                    <option value="daily">Daily (Every day at midnight)</option>
                    <option value="weekly">Weekly (Every Monday)</option>
                    <option value="monthly">Monthly (1st day of month)</option>
                    <option value="quarterly">Quarterly (Every 3 months)</option>
                    <option value="yearly">Yearly (January 1st)</option>
                </select>
                <p class="text-xs text-gray-600 mt-2">
                    <span class="font-semibold">Current schedule:</span> 
                    <span id="scheduleDescription" class="text-gray-800 font-semibold">Daily backups at midnight</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Restore Section -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Restore from Backup</h2>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-yellow-800 font-semibold">
                    Warning: Restoring will replace all current data. A safety backup will be created automatically before restore.
                </p>
            </div>
        </div>
        <form id="restoreForm" class="flex gap-4 items-end flex-wrap">
            <div class="flex-1 min-w-[300px]">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Backup File (.zip)</label>
                <input type="file" name="backup_file" id="backupFile" accept=".zip" class="block w-full text-sm font-semibold text-gray-900 border-2 border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:border-primary-500 p-2.5 bg-white">
            </div>
            <button type="button" onclick="showRestoreConfirmation()" style="background-color: #ea580c;" class="hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-bold flex items-center gap-2 transition-all shadow-md hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="font-bold">Restore</span>
            </button>
        </form>
    </div>

    <!-- Existing Backups -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Existing Backups ({{ count($backups) }})</h2>
        
        @if(count($backups) > 0)
            <div class="grid gap-4" id="backupsList">
                @foreach($backups as $backup)
                <div class="backup-card border border-gray-200 rounded-lg p-4 flex items-center justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="bg-purple-100 rounded-lg p-3">
                            @if($backup['is_safety'])
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $backup['name'] }}</h3>
                            <div class="flex gap-4 text-sm text-gray-600 mt-1">
                                <span>{{ $backup['date'] }}</span>
                                <span>•</span>
                                <span>{{ $backup['size'] }}</span>
                                @if($backup['is_safety'])
                                    <span class="bg-orange-100 text-orange-800 px-2 py-0.5 rounded text-xs font-semibold">Safety Backup</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('backup.download', $backup['name']) }}" style="background-color: #2563eb;" class="hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span class="hidden sm:inline">Download</span>
                        </a>
                        <button onclick="deleteBackup('{{ $backup['name'] }}')" style="background-color: #dc2626;" class="hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span class="hidden sm:inline">Delete</span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-lg font-medium">No backups available</p>
                <p class="text-sm mt-1">Create your first backup to get started</p>
            </div>
        @endif
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-8 max-w-md mx-4">
        <div class="text-center">
            <div class="mx-auto mb-4 border-4 border-gray-200 border-t-primary-600 rounded-full w-12 h-12 animate-spin"></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2" id="loadingTitle">Processing...</h3>
            <p class="text-gray-600 font-medium" id="loadingMessage">Please wait while we process your request.</p>
            <div class="mt-4 bg-gray-200 rounded-full h-2 overflow-hidden">
                <div id="progressBar" class="bg-primary-600 h-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div id="restoreConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-orange-100 rounded-full p-3">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Confirm Restore</h3>
        </div>
        <p class="text-gray-700 font-medium mb-6">
            Are you sure you want to restore from this backup? This will:
        </p>
        <ul class="list-disc list-inside text-sm text-gray-700 mb-6 space-y-1 font-medium">
            <li>Replace all current database data</li>
            <li>Create a safety backup of current state</li>
            <li>Restore configuration settings</li>
            <li>Clear all caches</li>
        </ul>
        <div class="flex gap-3">
            <button onclick="closeRestoreConfirmation()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 px-4 py-2 rounded-lg font-bold transition-colors">
                Cancel
            </button>
            <button onclick="performRestore()" style="background-color: #ea580c;" class="flex-1 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-bold transition-colors">
                Restore Now
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Delete Backup</h3>
        </div>
        <p class="text-gray-700 font-medium mb-4">
            Are you sure you want to delete this backup file?
        </p>
        <p class="text-sm text-gray-600 mb-6">
            <strong id="deleteFileName" class="text-gray-900"></strong>
        </p>
        <p class="text-sm text-red-600 font-semibold mb-6">
            ⚠️ This action cannot be undone.
        </p>
        <div class="flex gap-3">
            <button onclick="closeDeleteConfirmation()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 px-4 py-2 rounded-lg font-bold transition-colors">
                Cancel
            </button>
            <button id="confirmDeleteBtn" onclick="confirmDelete()" style="background-color: #dc2626;" class="flex-1 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold transition-colors">
                Delete Backup
            </button>
        </div>
    </div>
</div>

<script>
// Get CSRF token
const backupCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Toast queue to prevent spam
let toastTimeout = null;
let activeToast = null;

// Check auto backup status on page load
document.addEventListener('DOMContentLoaded', function() {
    const autoBackupEnabled = localStorage.getItem('autoBackupEnabled') === 'true';
    const backupSchedule = localStorage.getItem('backupSchedule') || 'daily';
    const toggle = document.getElementById('autoBackupToggle');
    const status = document.getElementById('autoBackupStatus');
    const scheduleSection = document.getElementById('scheduleSection');
    const scheduleSelect = document.getElementById('backupSchedule');
    const toggleBackground = document.getElementById('toggleBackground');
    const toggleCircle = document.getElementById('toggleCircle');
    
    if (autoBackupEnabled) {
        toggle.checked = true;
        status.textContent = 'Enabled';
        status.classList.add('text-green-700');
        status.classList.remove('text-gray-900');
        scheduleSection.classList.remove('hidden');
        // Set toggle to enabled state
        toggleCircle.style.transform = 'translateX(24px)';
        toggleBackground.style.backgroundColor = '#7c3aed'; // purple-600
        toggleBackground.style.borderColor = '#7c3aed';
    }
    
    scheduleSelect.value = backupSchedule;
    updateScheduleText(backupSchedule);
});

// Toggle auto backup
function toggleAutoBackup(checkbox) {
    const status = document.getElementById('autoBackupStatus');
    const scheduleSection = document.getElementById('scheduleSection');
    const toggleBackground = document.getElementById('toggleBackground');
    const toggleCircle = document.getElementById('toggleCircle');
    const isEnabled = checkbox.checked;
    
    // Clear any existing toast timeout
    if (toastTimeout) {
        clearTimeout(toastTimeout);
        toastTimeout = null;
    }
    
    // Remove existing toast if present
    if (activeToast) {
        activeToast.remove();
        activeToast = null;
    }
    
    localStorage.setItem('autoBackupEnabled', isEnabled);
    
    if (isEnabled) {
        status.textContent = 'Enabled';
        status.classList.add('text-green-700');
        status.classList.remove('text-gray-900');
        scheduleSection.classList.remove('hidden');
        // Move circle to right and change background to purple
        toggleCircle.style.transform = 'translateX(24px)';
        toggleBackground.style.backgroundColor = '#7c3aed'; // purple-600
        toggleBackground.style.borderColor = '#7c3aed';
        const schedule = localStorage.getItem('backupSchedule') || 'daily';
        showToast('Auto backup enabled! Backups will be created ' + getScheduleText(schedule), 'success');
    } else {
        status.textContent = 'Disabled';
        status.classList.remove('text-green-700');
        status.classList.add('text-gray-900');
        scheduleSection.classList.add('hidden');
        // Move circle to left and change background to gray
        toggleCircle.style.transform = 'translateX(0)';
        toggleBackground.style.backgroundColor = '#d1d5db'; // gray-300
        toggleBackground.style.borderColor = '#d1d5db';
        showToast('Auto backup disabled.', 'success');
    }
}

// Update schedule
function updateSchedule(select) {
    const schedule = select.value;
    localStorage.setItem('backupSchedule', schedule);
    updateScheduleText(schedule);
    
    // Clear existing toast
    if (toastTimeout) {
        clearTimeout(toastTimeout);
        toastTimeout = null;
    }
    if (activeToast) {
        activeToast.remove();
        activeToast = null;
    }
    
    showToast('Backup schedule updated to ' + getScheduleText(schedule), 'success');
}

// Update schedule description text
function updateScheduleText(schedule) {
    const descriptions = {
        'daily': 'Daily backups at midnight',
        'weekly': 'Weekly backups every Monday',
        'monthly': 'Monthly backups on 1st day of month',
        'quarterly': 'Quarterly backups every 3 months',
        'yearly': 'Yearly backups on January 1st'
    };
    document.getElementById('scheduleDescription').textContent = descriptions[schedule] || descriptions['daily'];
}

// Get schedule text for toast
function getScheduleText(schedule) {
    const texts = {
        'daily': 'daily',
        'weekly': 'weekly',
        'monthly': 'monthly',
        'quarterly': 'quarterly',
        'yearly': 'yearly'
    };
    return texts[schedule] || 'daily';
}

// Create backup
function createBackup() {
    const btn = document.getElementById('createBackupBtn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<div class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span class="ml-2">Creating...</span>';
    
    showLoading('Creating Backup', 'Exporting database and compressing files...');
    
    fetch('{{ route('backup.create') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': backupCsrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('Backup created successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to create backup', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Delete backup
function deleteBackup(filename) {
    // Store filename in button's data attribute for reliability
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.setAttribute('data-filename', filename);
    
    // Display filename in modal
    document.getElementById('deleteFileName').textContent = filename;
    
    // Show modal
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

function closeDeleteConfirmation() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
}

function confirmDelete() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const filename = confirmBtn.getAttribute('data-filename');
    
    if (!filename) {
        showToast('Error: No file selected', 'error');
        return;
    }
    
    closeDeleteConfirmation();
    
    fetch(`{{ url('/backup/delete') }}/${filename}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': backupCsrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Backup deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to delete backup', 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred: ' + error.message, 'error');
    });
}

// Show restore confirmation
function showRestoreConfirmation() {
    const fileInput = document.getElementById('backupFile');
    
    if (!fileInput.files.length) {
        showToast('Please select a backup file first', 'error');
        return;
    }
    
    document.getElementById('restoreConfirmModal').classList.remove('hidden');
}

// Close restore confirmation
function closeRestoreConfirmation() {
    document.getElementById('restoreConfirmModal').classList.add('hidden');
}

// Perform restore
function performRestore() {
    closeRestoreConfirmation();
    showLoading('Restoring Backup', 'This may take a few minutes. Please do not close this window...');
    
    const formData = new FormData();
    const fileInput = document.getElementById('backupFile');
    formData.append('backup_file', fileInput.files[0]);
    
    fetch('{{ route('backup.restore') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': backupCsrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('Restore completed successfully! Refreshing page...', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast(data.message || 'Failed to restore backup', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred: ' + error.message, 'error');
    });
}

// Show loading modal
function showLoading(title, message) {
    document.getElementById('loadingTitle').textContent = title;
    document.getElementById('loadingMessage').textContent = message;
    document.getElementById('loadingModal').classList.remove('hidden');
    
    // Simulate progress
    let progress = 0;
    const progressBar = document.getElementById('progressBar');
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        progressBar.style.width = progress + '%';
    }, 500);
    
    // Store interval ID to clear later
    window.backupLoadingInterval = interval;
}

// Hide loading modal
function hideLoading() {
    if (window.backupLoadingInterval) {
        clearInterval(window.backupLoadingInterval);
    }
    document.getElementById('progressBar').style.width = '100%';
    setTimeout(() => {
        document.getElementById('loadingModal').classList.add('hidden');
        document.getElementById('progressBar').style.width = '0%';
    }, 300);
}

// Show toast notification
function showToast(message, type = 'info') {
    // Remove any existing toast first
    if (activeToast) {
        activeToast.remove();
        activeToast = null;
    }
    
    // Clear any existing timeout
    if (toastTimeout) {
        clearTimeout(toastTimeout);
        toastTimeout = null;
    }
    
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600';
    
    toast.className = `fixed bottom-6 right-6 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 font-semibold`;
    toast.textContent = message;
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
    
    document.body.appendChild(toast);
    activeToast = toast;
    
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    toastTimeout = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => {
            toast.remove();
            if (activeToast === toast) {
                activeToast = null;
            }
        }, 300);
    }, 3000);
}
</script>
</div>
@endsection
