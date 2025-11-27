<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;
use Carbon\Carbon;

class BackupController extends Controller
{
    private $backupPath = 'backups';
    
    /**
     * Show backup management page
     */
    public function index()
    {
        // Get list of existing backups
        $backups = $this->getBackupsList();
        
        // Get database info
        $dbInfo = [
            'name' => env('DB_DATABASE'),
            'size' => $this->getDatabaseSize(),
        ];
        
        return view('health_worker.backup', compact('backups', 'dbInfo'));
    }
    
    /**
     * Create a new backup
     */
    public function create(Request $request)
    {
        try {
            set_time_limit(300); // 5 minutes timeout
            
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $backupName = "backup_{$timestamp}";
            $tempPath = storage_path("app/temp_backup_{$timestamp}");
            
            // Create temporary directory
            if (!File::exists($tempPath)) {
                File::makeDirectory($tempPath, 0755, true);
            }
            
            // 1. Export MySQL database
            $sqlFile = $tempPath . '/database.sql';
            $this->exportDatabase($sqlFile);
            
            // 2. Create backup info file
            $infoFile = $tempPath . '/backup-info.json';
            $this->createBackupInfo($infoFile, $request->user());
            
            // 3. Copy .env file
            if (File::exists(base_path('.env'))) {
                File::copy(base_path('.env'), $tempPath . '/.env.backup');
            }
            
            // 4. Copy storage files if they exist
            $storagePublicPath = storage_path('app/public');
            if (File::exists($storagePublicPath) && count(File::allFiles($storagePublicPath)) > 0) {
                File::copyDirectory($storagePublicPath, $tempPath . '/storage');
            }
            
            // 5. Create ZIP file
            $zipFile = storage_path("app/{$this->backupPath}/{$backupName}.zip");
            
            // Ensure backup directory exists
            if (!File::exists(storage_path("app/{$this->backupPath}"))) {
                File::makeDirectory(storage_path("app/{$this->backupPath}"), 0755, true);
            }
            
            $this->createZipArchive($tempPath, $zipFile);
            
            // 6. Clean up temporary directory
            File::deleteDirectory($tempPath);
            
            // 7. Clean old backups (keep last 10)
            $this->cleanOldBackups();
            
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully!',
                'backup' => [
                    'name' => $backupName . '.zip',
                    'size' => $this->formatBytes(File::size($zipFile)),
                    'date' => Carbon::now()->format('F d, Y h:i A')
                ]
            ]);
            
        } catch (\Exception $e) {
            // Clean up on error
            if (isset($tempPath) && File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download a backup file
     */
    public function download($filename)
    {
        $filePath = storage_path("app/{$this->backupPath}/{$filename}");
        
        if (!File::exists($filePath)) {
            abort(404, 'Backup file not found');
        }
        
        return response()->download($filePath);
    }
    
    /**
     * Delete a backup file
     */
    public function delete($filename)
    {
        try {
            $filePath = storage_path("app/{$this->backupPath}/{$filename}");
            
            if (File::exists($filePath)) {
                File::delete($filePath);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Backup deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Restore from backup
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:512000' // Max 500MB
        ]);
        
        try {
            set_time_limit(600); // 10 minutes timeout
            
            // Create safety backup first
            $safetyBackupName = 'safety_backup_' . Carbon::now()->format('Y-m-d_His');
            $this->createSafetyBackup($safetyBackupName);
            
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $tempPath = storage_path("app/temp_restore_{$timestamp}");
            
            // Create temporary directory
            File::makeDirectory($tempPath, 0755, true);
            
            // Save uploaded file
            $uploadedFile = $request->file('backup_file');
            $zipPath = $tempPath . '/backup.zip';
            $uploadedFile->move($tempPath, 'backup.zip');
            
            // Extract ZIP file
            $extractPath = $tempPath . '/extracted';
            File::makeDirectory($extractPath, 0755, true);
            
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Failed to open backup file');
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Validate backup structure
            if (!File::exists($extractPath . '/database.sql')) {
                throw new \Exception('Invalid backup file: database.sql not found');
            }
            
            // Restore database
            $this->importDatabase($extractPath . '/database.sql');
            
            // Restore .env if exists
            if (File::exists($extractPath . '/.env.backup')) {
                File::copy($extractPath . '/.env.backup', base_path('.env'));
            }
            
            // Restore storage files if exist
            if (File::exists($extractPath . '/storage')) {
                $storagePublicPath = storage_path('app/public');
                if (File::exists($storagePublicPath)) {
                    File::deleteDirectory($storagePublicPath);
                }
                File::copyDirectory($extractPath . '/storage', $storagePublicPath);
            }
            
            // Clean up
            File::deleteDirectory($tempPath);
            
            // Clear caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'System restored successfully! Please refresh the page.',
                'safety_backup' => $safetyBackupName . '.zip'
            ]);
            
        } catch (\Exception $e) {
            // Clean up on error
            if (isset($tempPath) && File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export MySQL database to SQL file
     */
    private function exportDatabase($outputFile)
    {
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        
        // Try to find mysqldump in common Laragon locations
        $mysqldumpPaths = [
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
            'C:\laragon\bin\mysql\mysql-5.7.24-winx64\bin\mysqldump.exe',
            'mysqldump' // Fallback to system PATH
        ];
        
        $mysqldump = null;
        foreach ($mysqldumpPaths as $path) {
            if ($path === 'mysqldump' || File::exists($path)) {
                $mysqldump = $path;
                break;
            }
        }
        
        if (!$mysqldump) {
            throw new \Exception('mysqldump not found. Please check MySQL installation in Laragon.');
        }
        
        // Create temporary config file to avoid password warning
        $configFile = sys_get_temp_dir() . '/my_' . uniqid() . '.cnf';
        $configContent = "[client]\n";
        $configContent .= "user={$dbUser}\n";
        $configContent .= "password={$dbPass}\n";
        $configContent .= "host={$dbHost}\n";
        $configContent .= "port={$dbPort}\n";
        File::put($configFile, $configContent);
        
        // Use mysqldump command with config file
        $command = sprintf(
            '"%s" --defaults-extra-file=%s %s > %s 2>&1',
            $mysqldump,
            escapeshellarg($configFile),
            escapeshellarg($dbName),
            escapeshellarg($outputFile)
        );
        
        exec($command, $output, $returnVar);
        
        // Clean up config file
        if (File::exists($configFile)) {
            File::delete($configFile);
        }
        
        if ($returnVar !== 0 || !File::exists($outputFile) || File::size($outputFile) === 0) {
            $errorMsg = implode("\n", $output);
            throw new \Exception('Database export failed: ' . $errorMsg);
        }
    }
    
    /**
     * Import MySQL database from SQL file
     */
    private function importDatabase($sqlFile)
    {
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        
        // Try to find mysql in common Laragon locations
        $mysqlPaths = [
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe',
            'C:\laragon\bin\mysql\mysql-5.7.24-winx64\bin\mysql.exe',
            'mysql' // Fallback to system PATH
        ];
        
        $mysql = null;
        foreach ($mysqlPaths as $path) {
            if ($path === 'mysql' || File::exists($path)) {
                $mysql = $path;
                break;
            }
        }
        
        if (!$mysql) {
            throw new \Exception('mysql not found. Please check MySQL installation in Laragon.');
        }
        
        // Create temporary config file to avoid password warning
        $configFile = sys_get_temp_dir() . '/my_' . uniqid() . '.cnf';
        $configContent = "[client]\n";
        $configContent .= "user={$dbUser}\n";
        $configContent .= "password={$dbPass}\n";
        $configContent .= "host={$dbHost}\n";
        $configContent .= "port={$dbPort}\n";
        File::put($configFile, $configContent);
        
        // Use mysql command with config file
        $command = sprintf(
            '"%s" --defaults-extra-file=%s %s < %s 2>&1',
            $mysql,
            escapeshellarg($configFile),
            escapeshellarg($dbName),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnVar);
        
        // Clean up config file
        if (File::exists($configFile)) {
            File::delete($configFile);
        }
        
        if ($returnVar !== 0) {
            throw new \Exception('Database import failed: ' . implode("\n", $output));
        }
    }
    
    /**
     * Create backup info file
     */
    private function createBackupInfo($filePath, $user = null)
    {
        $info = [
            'created_at' => Carbon::now()->toISOString(),
            'created_by' => $user ? $user->name : 'System',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database' => env('DB_DATABASE'),
            'app_url' => env('APP_URL'),
        ];
        
        File::put($filePath, json_encode($info, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create ZIP archive from directory
     */
    private function createZipArchive($sourcePath, $zipFile)
    {
        $zip = new ZipArchive;
        
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Failed to create ZIP archive');
        }
        
        $files = File::allFiles($sourcePath);
        
        foreach ($files as $file) {
            $relativePath = str_replace($sourcePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);
        }
        
        $zip->close();
    }
    
    /**
     * Create safety backup before restore
     */
    private function createSafetyBackup($backupName)
    {
        $tempPath = storage_path("app/temp_safety_backup");
        
        if (!File::exists($tempPath)) {
            File::makeDirectory($tempPath, 0755, true);
        }
        
        // Export current database
        $sqlFile = $tempPath . '/database.sql';
        $this->exportDatabase($sqlFile);
        
        // Create info file
        $infoFile = $tempPath . '/backup-info.json';
        File::put($infoFile, json_encode([
            'type' => 'safety_backup',
            'created_at' => Carbon::now()->toISOString(),
            'note' => 'Automatic safety backup created before restore operation'
        ], JSON_PRETTY_PRINT));
        
        // Create ZIP
        $zipFile = storage_path("app/{$this->backupPath}/{$backupName}.zip");
        $this->createZipArchive($tempPath, $zipFile);
        
        // Clean up
        File::deleteDirectory($tempPath);
    }
    
    /**
     * Get list of existing backups
     */
    private function getBackupsList()
    {
        $backupDir = storage_path("app/{$this->backupPath}");
        
        if (!File::exists($backupDir)) {
            return [];
        }
        
        $files = File::files($backupDir);
        $backups = [];
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'zip') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'date' => Carbon::createFromTimestamp($file->getMTime())->format('F d, Y h:i A'),
                    'timestamp' => $file->getMTime(),
                    'is_safety' => str_contains($file->getFilename(), 'safety_backup')
                ];
            }
        }
        
        // Sort by timestamp descending (newest first)
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        return $backups;
    }
    
    /**
     * Clean old backups (keep last 10)
     */
    private function cleanOldBackups()
    {
        $backups = $this->getBackupsList();
        
        // Keep last 10 backups
        if (count($backups) > 10) {
            $toDelete = array_slice($backups, 10);
            
            foreach ($toDelete as $backup) {
                $filePath = storage_path("app/{$this->backupPath}/{$backup['name']}");
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
        }
    }
    
    /**
     * Get database size
     */
    private function getDatabaseSize()
    {
        try {
            $dbName = env('DB_DATABASE');
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);
            
            return $result[0]->size_mb . ' MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Format bytes to human readable size
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
