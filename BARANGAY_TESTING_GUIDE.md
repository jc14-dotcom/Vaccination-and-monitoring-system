# Barangay System - Performance Analysis & Testing Guide

## Test Account Credentials

### Barangay Worker (Limited Access)
```
URL: http://localhost/infantsSystem/public/health_worker/login
Username: balayhangin_worker
Password: password123
Barangay: Balayhangin
```

### RHU Admin (Full Access)
Use your existing admin account (any health_worker with barangay_id = NULL)

---

## Performance Analysis

### Database Indexes Added

| Table | Index Name | Columns | Purpose |
|-------|-----------|---------|---------|
| `patients` | `idx_patients_barangay` | `barangay` | ✅ Already existed - Filter patients by barangay |
| `patients` | `idx_patients_barangay_dob` | `barangay`, `date_of_birth` | ✅ Already existed - Composite for age queries |
| `patients` | `fk_parent_id` | `parent_id` | ✅ Already existed - Join with parents |
| `health_workers` | `health_workers_barangay_id_index` | `barangay_id` | ✅ Added in Phase 1 - Filter workers by barangay |
| `parents` | `idx_parents_barangay` | `barangay` | ✅ Already existed - Filter parents by barangay |
| `vaccination_schedules` | `idx_vacc_schedules_barangay` | `barangay` | ✅ NEW - Filter schedules by barangay |
| `vaccination_schedules` | `idx_vacc_schedules_date_status` | `vaccination_date`, `status` | ✅ NEW - Efficient date+status queries |
| `patient_vaccine_records` | `idx_pvr_patient_dose1` | `patient_id`, `dose_1_date` | ✅ NEW - Vaccination statistics |
| `barangays` | `idx_barangays_name` | `name` | ✅ NEW - Fast barangay lookup |
| `barangays` | `idx_barangays_active_schedulable` | `is_active`, `has_scheduled_vaccination` | ✅ NEW - Schedulable barangay queries |

### Query Performance Optimizations

#### 1. Patient Queries (PatientController)
```php
// OPTIMIZED: Uses indexed barangay column
Patient::forHealthWorker($healthWorker)->get();
// SQL: SELECT * FROM patients WHERE barangay = 'Balayhangin'
// Uses: idx_patients_barangay index
```

#### 2. Vaccination Schedule Queries (VaccinationScheduleController)
```php
// OPTIMIZED: Uses composite index
VaccinationSchedule::where('vaccination_date', '>=', today())
    ->whereIn('status', ['scheduled', 'active'])
    ->where('barangay', $barangayName)
// Uses: idx_vacc_schedules_date_status, idx_vacc_schedules_barangay indexes
```

#### 3. Feedback Queries (FeedbackController)
```php
// OPTIMIZED: Direct barangay filter
Feedback::where('barangay', $barangayName)->orderBy('created_at', 'desc')
// Uses: idx_feedback_barangay index (if feedback table has barangay column)
```

#### 4. Parent Contact Cascade (ContactUpdateService)
```php
// OPTIMIZED: Bulk update using parent_id (indexed via fk_parent_id)
Patient::where('parent_id', $parent->id)->update([...])
// Uses: fk_parent_id index
```

### Avoided N+1 Query Issues

| Location | Issue | Solution |
|----------|-------|----------|
| PatientController::index() | Loading vaccine records per patient | Used `isFullyImmunized()` with eager loaded relations |
| FeedbackController::loadMore() | Loading patient per feedback | Used `with('patient')` eager loading |
| VaccinationScheduleController::index() | Loading health worker per schedule | Used `with('healthWorker')` eager loading |

---

## Testing Checklist

### As Barangay Worker (balayhangin_worker)

#### Dashboard
- [ ] Should see patient count for Balayhangin only
- [ ] Should see vaccination stats for Balayhangin patients only
- [ ] Should see upcoming schedules for Balayhangin + RHU only

#### Patient List
- [ ] Should see only Balayhangin patients
- [ ] Cannot see patients from other barangays
- [ ] Can view patient details (own barangay)
- [ ] Can add new patient (should auto-assign Balayhangin? - check behavior)

#### Vaccination Schedule
- [ ] Can view schedules (filtered to Balayhangin + RHU)
- [ ] **Cannot create** new vaccination schedule (form hidden)
- [ ] **Cannot cancel** existing schedules (buttons hidden)
- [ ] **Cannot delete** existing schedules (buttons hidden)

#### Vaccination Status
- [ ] Shows only Balayhangin patients
- [ ] Can mark vaccinations for own barangay patients

#### Inventory
- [ ] Can view all vaccines (read-only)
- [ ] **Cannot add** new vaccine (button hidden)
- [ ] **Cannot update** stock (button hidden)
- [ ] Shows "View Only - Inventory managed by RHU" notice

#### Feedback
- [ ] Shows only Balayhangin feedback
- [ ] Barangay filter should be disabled or limited

#### Sidebar Menu
- [ ] **No Backup & Restore** menu item
- [ ] **No Reports** menu item

### As RHU Admin

#### All Features
- [ ] Full access to all barangays
- [ ] Can see all patients
- [ ] Can create/edit/delete vaccination schedules
- [ ] Can modify inventory
- [ ] Can access Backup & Restore
- [ ] Can access Reports

---

## Verification Commands

### Check if indexes were created
```bash
php artisan tinker --execute="print_r(DB::select('SHOW INDEX FROM vaccination_schedules'));"
```

### Check barangay worker access
```bash
php artisan tinker --execute="
\$w = App\Models\HealthWorker::where('username','balayhangin_worker')->first();
echo 'isRHU: '.(\$w->isRHU()?'Yes':'No').PHP_EOL;
echo 'Can access Balayhangin: '.(\$w->canAccessBarangay('Balayhangin')?'Yes':'No').PHP_EOL;
echo 'Can access Bangyas: '.(\$w->canAccessBarangay('Bangyas')?'Yes':'No').PHP_EOL;
"
```

### Check patient filtering
```bash
php artisan tinker --execute="
\$w = App\Models\HealthWorker::where('username','balayhangin_worker')->first();
\$count = App\Models\Patient::forHealthWorker(\$w)->count();
echo 'Patients visible to Balayhangin worker: '.\$count.PHP_EOL;
echo 'Total patients: '.App\Models\Patient::count().PHP_EOL;
"
```

---

## Performance Monitoring

### Enable Query Logging (Development Only)
Add to `app/Providers/AppServiceProvider.php`:
```php
public function boot()
{
    if (config('app.debug')) {
        DB::listen(function ($query) {
            Log::info('Query: ' . $query->sql, ['time' => $query->time . 'ms']);
        });
    }
}
```

### Expected Query Times
| Query Type | Expected Time |
|------------|---------------|
| Patient list (filtered) | < 10ms |
| Vaccination schedules (filtered) | < 5ms |
| Feedback list (filtered) | < 10ms |
| Dashboard statistics | < 50ms |

---

## Troubleshooting

### "Access denied" error
- Verify health worker has correct `barangay_id`
- Check if patient's `barangay` field matches worker's assigned barangay

### Seeing all patients instead of filtered
- Health worker might have `barangay_id = NULL` (making them RHU admin)
- Check with: `HealthWorker::find($id)->barangay_id`

### Inventory buttons not hidden
- Check if `$healthWorker` is passed to the view
- Verify `$healthWorker->isRHU()` returns correct value

---

*Last Updated: November 26, 2025*
