<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthWorkerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ShowVaccinationFormController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PatientApiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\VaccineStockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VaccinationScheduleController;
// New refactored controllers
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parent\ProfileController as ParentProfileController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PrivacyController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\FcmController;

// Home route (welcome page)
Route::get('/', function () {
    return response()
        ->view('welcome')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('welcome');


// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'handleForgotPassword'])->name('password.email');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'handleReset'])->name('password.update');

// Parent routes with auth middleware
// Note: Using only ensure.auth:parents (not auth:parents) to avoid double-checking
// which causes timing issues with database session driver
Route::middleware(['prevent.back', 'ensure.auth:parents'])->group(function () {
    // Parent dashboard (refactored to controller)
    Route::get('/parents/parentdashboard', [ParentDashboardController::class, 'index'])
        ->name('parent.dashboard');
    
    // Parent profile (refactored to dedicated controller)
    Route::get('/profile', [ParentProfileController::class, 'show'])
        ->name('parents.profile');
    Route::put('/profile/update', [ParentProfileController::class, 'update'])
        ->name('updateProfile');
    
    // Parent's view of patient vaccination card
    Route::get('/infantsRecord/{id}', [PatientController::class, 'showParentVaccinationCard'])
        ->name('patient_card');
    
    // Feedback submission
    Route::post('/feedback', [AuthController::class, 'store'])
        ->name('feedback.store');

    Route::get('/health-worker/feedback/load-more', [FeedbackController::class, 'loadMore'])
        ->name('health_worker.feedback.load_more');

    // Password management (refactored to dedicated controller)
    Route::get('/change-password', [PasswordController::class, 'showChangeForm'])
        ->name('parents.change-password');
    Route::post('/change-password', [PasswordController::class, 'change'])
        ->name('parents.update-password');
    
    // First-login password change (forced) - refactored to controller
    Route::get('/first-login-change-password', [PasswordController::class, 'showFirstLoginForm'])
        ->name('parents.first-login-change-password');
    Route::post('/first-login-change-password', [PasswordController::class, 'firstLoginChange'])
        ->name('parents.first-login-update-password');
    
    // Privacy policy consent (forced on first login) - refactored to controller
    Route::get('/parent-privacy-consent', [PrivacyController::class, 'show'])
        ->name('parent.privacy.consent');
    Route::post('/parent-privacy-consent', [PrivacyController::class, 'accept'])
        ->name('parent.privacy.accept');
});



// Health worker routes with auth middleware
// Note: Using only ensure.auth:health_worker to avoid double-checking
Route::middleware(['prevent.back', 'ensure.auth:health_worker'])->group(function () {
    // Dashboard and patient management
    Route::get('/health_worker/dashboard', [PatientController::class, 'index'])->name('health_worker.dashboard');
    Route::get('/health_worker/patients', [\App\Http\Controllers\PatientController::class, 'showPatientList'])
        ->name('health_worker.patients');

    Route::get('/patients/load', [\App\Http\Controllers\PatientController::class, 'getPatients'])
        ->name('patients.load');
    
    // Vaccination management
    Route::get('/health_worker/vaccination_form', [AuthController::class, 'showVaccinationForm'])->name('health_worker.vaccination_form');
    Route::get('/vaccination_form/{id}', [ShowVaccinationFormController::class, 'show'])->name('vaccination.form');
    Route::get('/patient_card/{id}', [PatientController::class, 'show'])->name('health_worker.patient_card');
    
    // Health worker view of read-only patient card (from patient list)
    Route::get('/health_worker/patient_view/{id}', [PatientController::class, 'showHealthWorkerPatientView'])->name('health_worker.patient_view');
    
    Route::put('/vaccinations/update/{id}', [VaccinationController::class, 'update'])->name('vaccinations.update');
    
    // Inventory management
    Route::get('/health_worker/inventory', [InventoryController::class, 'index'])->name('health_worker.inventory');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/update/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/add-vaccine', [InventoryController::class, 'addVaccine'])->name('inventory.add');
    Route::get('/inventory/show/{vaccineId}', [InventoryController::class, 'showInventory'])->name('inventory.show');
    Route::post('/inventory/add-stock/{vaccineId}', [InventoryController::class, 'addStock'])->name('inventory.addStock');
    
    // Reports and feedback
    Route::get('/health_worker/report', [ReportController::class, 'current'])
        ->middleware('cache.response:5')
        ->name('health_worker.report');
    Route::get('/reports/current', [ReportController::class, 'current'])
        ->middleware('cache.response:5')
        ->name('reports.current');
    // Debug route - disabled for production
    // Route::get('/reports/debug', function() {
    //     return view('health_worker.debug_report');
    // })->name('reports.debug');
    Route::get('/reports/history', [ReportController::class, 'history'])
        ->name('reports.history');
    Route::get('/reports/show', [ReportController::class, 'show'])
        ->middleware('cache.response:30')
        ->name('reports.show');
    Route::get('/reports/settings', [ReportController::class, 'showSettings'])->name('reports.settings');
    Route::post('/reports/settings', [ReportController::class, 'updateSettings'])->name('reports.settings.update');
    Route::get('/reports/import', [ReportController::class, 'importPage'])->name('reports.import.page');
    Route::get('/reports/download-template', [ReportController::class, 'downloadTemplate'])->name('reports.downloadTemplate');
    
    // Backup & Restore routes
    Route::get('/backup', [App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup/create', [App\Http\Controllers\BackupController::class, 'create'])->name('backup.create');
    Route::get('/backup/download/{filename}', [App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/delete/{filename}', [App\Http\Controllers\BackupController::class, 'delete'])->name('backup.delete');
    Route::post('/backup/restore', [App\Http\Controllers\BackupController::class, 'restore'])->name('backup.restore');
    
    // AJAX routes for report actions
    Route::post('/reports/save-edit', [ReportController::class, 'saveEdit'])->name('reports.save-edit');
    Route::post('/reports/save-edited', [ReportController::class, 'saveEditedReport'])->name('reports.saveEdited');
    Route::post('/reports/lock', [ReportController::class, 'lock'])->name('reports.lock');
    Route::post('/reports/reset', [ReportController::class, 'resetToLive'])->name('reports.reset');
    Route::post('/reports/import', [ReportController::class, 'import'])->name('reports.import');
    Route::delete('/reports/delete', [ReportController::class, 'delete'])->name('reports.delete');
    Route::post('/reports/restore', [ReportController::class, 'restore'])->name('reports.restore');
    Route::post('/reports/bulk-restore', [ReportController::class, 'bulkRestore'])->name('reports.bulk.restore');
    Route::delete('/reports/bulk-delete', [ReportController::class, 'bulkDelete'])->name('reports.bulk.delete');
    Route::get('/reports/compare', [ReportController::class, 'compare'])->name('reports.compare');
    
    Route::get('/health_worker/feedback', [AuthController::class, 'showFeedback'])->name('health_worker.feedback');
    Route::get('/feedback/{id}', [FeedbackController::class, 'show'])->name('feedback.show');
    
    // Vaccination status management
    Route::get('/health_worker/vaccination_status', [HealthWorkerController::class, 'vaccinationStatus'])->name('health_worker.vaccination_status');
    Route::get('/vaccination-status/load', [HealthWorkerController::class, 'getVaccinationStatus'])->name('vaccination_status.load');
    Route::post('/set-vaccination-day', [HealthWorkerController::class, 'setVaccinationDay'])->name('set_vaccination_day');
    
    // Vaccination schedule management
    Route::get('/health_worker/vaccination_schedule', [VaccinationScheduleController::class, 'index'])->name('vaccination_schedule.index');
    Route::post('/health_worker/vaccination_schedule', [VaccinationScheduleController::class, 'store'])->name('vaccination_schedule.store');
    Route::post('/health_worker/vaccination_schedule/{id}/cancel', [VaccinationScheduleController::class, 'cancel'])->name('vaccination_schedule.cancel');
    Route::delete('/health_worker/vaccination_schedule/{id}', [VaccinationScheduleController::class, 'destroy'])->name('vaccination_schedule.destroy');
    
    // Record saving
    Route::post('/save-record', [AuthController::class, 'saveRecord'])->name('auth.saveRecord');

    // Change password for health workers (admin)
    Route::post('/health_worker/change-password', [HealthWorkerController::class, 'updatePassword'])->name('health_worker.update-password');
    
    // Change email for health workers
    Route::post('/health_worker/change-email', [HealthWorkerController::class, 'updateEmail'])->name('health_worker.update-email');
});

// Session verification endpoint (refactored to controller)
Route::get('/api/check-session', [SessionController::class, 'check'])
    ->name('api.check.session');

// API routes for notifications (accessible to authenticated users)
Route::middleware(['web'])->group(function () {
    Route::get('/api/notifications', [NotificationController::class, 'index'])
        ->name('api.notifications.index');
    Route::get('/api/notifications/check', [NotificationController::class, 'check'])
        ->name('api.notifications.check');
    Route::post('/api/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])
        ->name('api.notifications.mark-read');
    Route::post('/api/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('api.notifications.mark-all-read');
    Route::delete('/api/notifications/clear-read', [NotificationController::class, 'clearRead'])
        ->name('api.notifications.clear-read');
    Route::delete('/api/notifications/{id}', [NotificationController::class, 'destroy'])
        ->name('api.notifications.destroy');
    
    // Push subscription routes (VAPID - legacy)
    Route::post('/api/push/subscribe', [PushSubscriptionController::class, 'subscribe'])
        ->name('api.push.subscribe');
    Route::post('/api/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])
        ->name('api.push.unsubscribe');
    Route::get('/api/push/public-key', [PushSubscriptionController::class, 'getPublicKey'])
        ->name('api.push.public-key');
    Route::post('/api/push/test', [PushSubscriptionController::class, 'testPush'])
        ->name('api.push.test');
    
    // FCM routes (Firebase Cloud Messaging - new)
    Route::post('/api/fcm/subscribe', [FcmController::class, 'subscribe'])
        ->name('api.fcm.subscribe');
    Route::post('/api/fcm/unsubscribe', [FcmController::class, 'unsubscribe'])
        ->name('api.fcm.unsubscribe');
    Route::get('/api/fcm/config', [FcmController::class, 'getConfig'])
        ->name('api.fcm.config');
});

// Catch-all route for any other routes - redirect to login
Route::fallback(function () {
    return redirect()->route('welcome');
});

// Feedback routes
Route::get('/api/feedback/analytics', [App\Http\Controllers\FeedbackController::class, 'getAnalytics'])->name('feedback.analytics');

// Vaccine Stocks API - Public endpoint for real-time stock display
// Rate limited to 60 requests per minute per IP to prevent abuse
Route::get('/api/vaccine-stocks', [VaccineStockController::class, 'index'])
    ->name('api.vaccine-stocks');



//for demo lang ng graphs
// // Demo route for feedback analysis presentation
// Route::get('/feedback-analysis-demo', function() {
//     return view('health_worker.feedback_analysis_demo');
// })->name('feedback_analysis_demo');


// Route::post('/filtzzer-vaccination-report', [ReportController::class, 'filterReport'])->name('filter_vaccination_report');
// Route::get('/export-vaccination-pdf', [ReportController::class, 'exportPdf'])->name('export_vaccination_pdf');
// Route::get('/export-vaccination-excel', [ReportController::class, 'exportExcel'])->name('export_vaccination_excel');