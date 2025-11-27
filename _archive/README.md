# Archive Folder

This folder contains archived files that are no longer needed in the main project directory but are kept for reference.

## Structure

### `/test_files`
Contains test scripts and temporary files used during development:
- `analyze_*.php` - Analysis scripts
- `check_*.php` - Verification/check scripts
- `test_*.php` - Test scripts
- `fix_*.php` - Fix/repair scripts
- `generate_*.php` - Generation scripts
- `reset_*.php`, `set_*.php` - Data manipulation scripts
- `troubleshoot_*.php` - Troubleshooting scripts
- `vapid_keys_clean.txt` - VAPID keys backup
- `start-redis.bat` - Redis startup script (if Redis was used)
- `dump.rdb` - Redis dump file

### `/documentation`
Contains markdown documentation files created during development:
- Implementation summaries and guides
- Analysis documents
- Setup guides
- Testing guides
- System documentation

### `/legacy_views`
Contains old/legacy blade template files that have been replaced:
- `welcome_legacy.blade.php`, `welcome_old.blade.php` - Old welcome pages
- `auth/login_legacy.blade.php` - Old login page
- `health_worker/*_old.blade.php`, `*_legacy.blade.php` - Old health worker views
- `health_worker/dashboard_modern.blade.php` - Unused modern dashboard variant
- `health_worker/debug_report.blade.php` - Debug view (disabled for production)
- `health_worker/feedback_analysis_demo.blade.php` - Demo view
- `parents/*_old.blade.php` - Old parent views
- `components/profile-dropdown-fixed.blade.php` - Unused component

### `/legacy_public`
Contains old/unused public assets:
- `cache-test.html` - Cache testing page
- `stock-warning-designs.html` - Design mockup
- `javascript/pwa.js` - PWA script (disabled, using FCM instead)
- `javascript/report-history.js` - Unminified version (minified version is used)

## Note
These files have been moved from the project to keep the structure clean for deployment. If you need any of these files, you can move them back or reference them directly from this archive folder.

**Archived on:** November 25-26, 2025
