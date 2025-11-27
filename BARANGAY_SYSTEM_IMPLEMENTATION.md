# Barangay Account System Implementation

## Overview
This document summarizes the implementation of the barangay-based multi-tenant system for the Infant Vaccination System.

## Implementation Status

### ✅ Phase 1: Database Schema (Complete)
- Created `barangays` table with 17 barangays
- Added `barangay_id` foreign key to `health_workers` table
- Kanluran marked with `has_scheduled_vaccination = false` (RHU is located there)

**Files Created:**
- `database/migrations/2025_11_26_000001_create_barangays_table.php`
- `database/migrations/2025_11_26_000002_add_barangay_id_to_health_workers_table.php`
- `database/seeders/BarangaySeeder.php`

### ✅ Phase 2: Models (Complete)
- Created `Barangay` model with helper methods
- Updated `HealthWorker` model with barangay relationship and access methods
- Updated `Patient` model with barangay filtering scopes

**Files Created/Modified:**
- `app/Models/Barangay.php` - NEW
- `app/Models/HealthWorker.php` - MODIFIED (added barangay_id, relationships, helper methods)
- `app/Models/Patient.php` - MODIFIED (added scopeForHealthWorker, scopeInBarangay)

### ✅ Phase 3: Controllers (Complete)
All controllers updated with barangay-based access control:

| Controller | Changes |
|------------|---------|
| `PatientController` | All methods filter by health worker's barangay |
| `HealthWorkerController` | vaccinationStatus, getVaccinationStatus, setVaccinationDay filter by barangay |
| `VaccinationController` | Access verification before vaccination updates |
| `VaccinationScheduleController` | CRUD operations verify barangay access |
| `FeedbackController` | show, loadMore, getAnalytics filter by barangay |
| `InventoryController` | RHU-only for modifications, view-only for barangay workers |

### ✅ Phase 4: Views (Complete)
Updated blade templates with role-based UI:

| View | Changes |
|------|---------|
| `vaccination_schedule.blade.php` | Form hidden for barangay workers, uses dynamic `$schedulableBarangays` |
| `inventory.blade.php` | Add/Update buttons hidden for non-RHU workers |
| `responsive-layout.blade.php` | Backup & Reports menu items hidden for barangay workers |

### ✅ Phase 5: Contact Cascade (Complete)
- Updated `ContactUpdateService` to cascade contact_no, address, AND barangay to all patients when parent updates profile

**File Modified:**
- `app/Services/Parent/ContactUpdateService.php`

---

## Access Control Matrix

| Feature | RHU Admin | Barangay Worker |
|---------|-----------|-----------------|
| View All Patients | ✅ All | ❌ Own barangay only |
| Create/Edit Patients | ✅ All | ✅ Own barangay only |
| View Vaccination Schedule | ✅ All | ✅ Own barangay + RHU |
| Create/Edit/Delete Schedule | ✅ Yes | ❌ No |
| View Inventory | ✅ Yes | ✅ Yes (read-only) |
| Modify Inventory | ✅ Yes | ❌ No |
| View Reports | ✅ Yes | ❌ No |
| Backup & Restore | ✅ Yes | ❌ No |
| View Feedback | ✅ All | ✅ Own barangay only |

---

## Health Worker Account Types

### RHU Admin (barangay_id = NULL)
- Full access to all barangays
- Can modify inventory
- Can create/edit vaccination schedules
- Can view reports and backups
- Located at Kanluran barangay (Health Center)

### Barangay Worker (barangay_id = specific barangay)
- Limited to their assigned barangay's data
- Read-only access to inventory
- Can view vaccination schedules (cannot modify)
- No access to reports or backups

---

## Key Model Methods

### HealthWorker Model
```php
$healthWorker->isRHU()                    // Returns true if RHU admin
$healthWorker->isBarangayWorker()         // Returns true if barangay worker
$healthWorker->canAccessBarangay($name)   // Check if can access specific barangay
$healthWorker->getAssignedBarangayName()  // Get assigned barangay name
$healthWorker->getAccessibleBarangays()   // Get array of accessible barangay names
$healthWorker->getSchedulableBarangays()  // Get barangays that can be scheduled (excludes Kanluran)
```

### Patient Model
```php
Patient::forHealthWorker($healthWorker)   // Scope to filter by health worker's barangay
Patient::inBarangay($barangayName)        // Scope to filter by specific barangay name
```

### Barangay Model
```php
Barangay::getActiveNames()                // Get all active barangay names
Barangay::getSchedulableNames()           // Get schedulable barangay names (excludes Kanluran)
```

---

## Cascade Behavior

When a parent updates their profile (contact_no, address, barangay):
1. Parent record is updated
2. ALL patients linked to that parent are automatically updated with:
   - `contact_no` → `patient.contact_no`
   - `address` → `patient.address`
   - `barangay` → `patient.barangay`

This ensures family data stays consistent across all children.

---

## Next Steps (Manual Tasks)

1. **Create Barangay Worker Accounts**
   - Manually insert into `health_workers` table with appropriate `barangay_id`
   - Each barangay should have at least one worker account

2. **Assign Existing Workers**
   - Update existing health worker records to set their `barangay_id` if they are barangay-specific
   - Leave `barangay_id` as NULL for RHU admins

3. **Testing Checklist**
   - [ ] Login as RHU admin, verify full access
   - [ ] Login as barangay worker, verify limited access
   - [ ] Verify patient filtering works correctly
   - [ ] Verify inventory is read-only for barangay workers
   - [ ] Verify vaccination schedule creation restricted to RHU
   - [ ] Verify parent profile update cascades to all children

---

## Barangay List

| ID | Name | Schedulable |
|----|------|-------------|
| 1 | Balayhangin | ✅ |
| 2 | Bangyas | ✅ |
| 3 | Dayap | ✅ |
| 4 | Hanggan | ✅ |
| 5 | Imok | ✅ |
| 6 | Kanluran | ❌ (RHU Location) |
| 7 | Lamot 1 | ✅ |
| 8 | Lamot 2 | ✅ |
| 9 | Limao | ✅ |
| 10 | Mabacan | ✅ |
| 11 | Masiit | ✅ |
| 12 | Paliparan | ✅ |
| 13 | Perez | ✅ |
| 14 | Prinza | ✅ |
| 15 | San Isidro | ✅ |
| 16 | Santo Tomas | ✅ |
| 17 | Silangan | ✅ |

---

*Last Updated: November 26, 2025*
