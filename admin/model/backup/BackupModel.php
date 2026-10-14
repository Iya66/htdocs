<?php
/**
 * BackupModel - Database export, file compression, restore logic
 */
if (!defined('APP_ROOT') && !isset($conn) && !isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Direct access not permitted');
}

class BackupModel {
    private PDO $conn;
    private string $backupDir;
    private string $projectRoot;
    private array $excludeDirs = ['cache', 'logs', 'tmp', 'vendor', 'node_modules', 'backups', '.git', '.idea'];

    public function __construct(PDO $conn) {
        $this->conn = $conn;
        $this->projectRoot = realpath(__DIR__ . '/../../../');
        $this->backupDir = $this->projectRoot . '/admin/uploads/backups';

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0750, true);
        }

        // Protect backups directory
        $htaccess = $this->backupDir . '/.htaccess';
        $denyRules = "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Order deny,allow\n  Deny from all\n</IfModule>\n";
        if (!file_exists($htaccess) || strpos(@file_get_contents($htaccess), 'Require all granted') !== false) {
            file_put_contents($htaccess, $denyRules);
        }

        $index = $this->backupDir . '/index.php';
        if (!file_exists($index)) {
            file_put_contents($index, '<?php http_response_code(403); exit("Forbidden");');
        }
    }

    public function getBackupDir(): string {
        return $this->backupDir;
    }

    /**
     * Generate backup based on type
     */
    public function generateBackup(string $type, string $filename): array {
        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'message' => 'PHP Zip extension (ZipArchive) is not enabled on this server.'];
        }
        switch ($type) {
            case 'database':
                return $this->generateDatabaseBackup($filename);
            case 'files':
                return $this->generateFilesBackup($filename);
            case 'full':
                return $this->generateFullBackup($filename);
            default:
                return ['success' => false, 'message' => 'Invalid backup type'];
        }
    }

    /**
     * Export MySQL database to .sql file
     */
    private function exportDatabase(string $sqlPath): bool {
        $tables = $this->conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sql = "-- CloudASPIRE Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Server: " . $this->conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        foreach ($tables as $table) {
            // Table structure
            $create = $this->conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $create['Create Table'] . ";\n\n";

            // Table data in chunks
            $count = $this->conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $batchSize = 500;

            for ($offset = 0; $offset < $count; $offset += $batchSize) {
                $rows = $this->conn->query("SELECT * FROM `$table` LIMIT $batchSize OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($rows)) continue;

                $cols = array_keys($rows[0]);
                $colList = '`' . implode('`, `', $cols) . '`';

                foreach ($rows as $row) {
                    $values = array_map(function ($v) {
                        if ($v === null) return 'NULL';
                        return $this->conn->quote($v);
                    }, array_values($row));
                    $sql .= "INSERT INTO `$table` ($colList) VALUES (" . implode(', ', $values) . ");\n";
                }
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return file_put_contents($sqlPath, $sql) !== false;
    }

    /**
     * Database-only backup
     */
    private function generateDatabaseBackup(string $filename): array {
        $sqlFile = $this->backupDir . "/{$filename}.sql";

        if (!$this->exportDatabase($sqlFile)) {
            return ['success' => false, 'message' => 'Failed to export database'];
        }

        // Compress to zip
        $zipFile = $this->backupDir . "/{$filename}_db.zip";
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($sqlFile);
            return ['success' => false, 'message' => 'Failed to create ZIP archive'];
        }
        $zip->addFile($sqlFile, basename($sqlFile));
        $zip->setArchiveComment("CloudASPIRE Database Backup - " . date('Y-m-d H:i:s'));
        $zip->close();
        @unlink($sqlFile);

        $cloudMessage = '';
        $syncResult = $this->syncToCloud($zipFile, basename($zipFile));
        if ($syncResult['success']) {
            $cloudMessage = ' | ' . $syncResult['message'];
        }

        return [
            'success' => true,
            'message' => 'Database backup created successfully' . $cloudMessage,
            'filename' => basename($zipFile),
            'size' => filesize($zipFile),
            'type' => 'database'
        ];
    }

    /**
     * Files-only backup
     */
    private function generateFilesBackup(string $filename): array {
        $zipFile = $this->backupDir . "/{$filename}_files.zip";
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['success' => false, 'message' => 'Failed to create ZIP archive'];
        }

        $this->addDirectoryToZip($zip, $this->projectRoot, '');
        $zip->setArchiveComment("CloudASPIRE Files Backup - " . date('Y-m-d H:i:s'));
        $zip->close();

        $cloudMessage = '';
        $syncResult = $this->syncToCloud($zipFile, basename($zipFile));
        if ($syncResult['success']) {
            $cloudMessage = ' | ' . $syncResult['message'];
        }

        return [
            'success' => true,
            'message' => 'Files backup created successfully' . $cloudMessage,
            'filename' => basename($zipFile),
            'size' => filesize($zipFile),
            'type' => 'files'
        ];
    }

    /**
     * Full backup (database + files)
     */
    private function generateFullBackup(string $filename): array {
        $zipFile = $this->backupDir . "/{$filename}_full.zip";
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['success' => false, 'message' => 'Failed to create ZIP archive'];
        }

        // Export DB first
        $sqlTmp = $this->backupDir . "/{$filename}_temp.sql";
        $this->exportDatabase($sqlTmp);
        $zip->addFile($sqlTmp, 'database_backup.sql');

        // Add files
        $this->addDirectoryToZip($zip, $this->projectRoot, 'files/');
        $zip->setArchiveComment("CloudASPIRE Full Backup - " . date('Y-m-d H:i:s'));
        $zip->close();
        @unlink($sqlTmp);

        $cloudMessage = '';
        $syncResult = $this->syncToCloud($zipFile, basename($zipFile));
        if ($syncResult['success']) {
            $cloudMessage = ' | ' . $syncResult['message'];
        }

        return [
            'success' => true,
            'message' => 'Full backup created successfully' . $cloudMessage,
            'filename' => basename($zipFile),
            'size' => filesize($zipFile),
            'type' => 'full'
        ];
    }

    /**
     * Recursively add directory to ZIP, excluding specified dirs
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $prefix): void {
        $items = @scandir($dir);
        if (!$items) return;

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (in_array($item, $this->excludeDirs)) continue;

            $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
            $zipPath = $prefix . $item;

            if (is_dir($fullPath)) {
                $zip->addEmptyDir($zipPath);
                $this->addDirectoryToZip($zip, $fullPath, $zipPath . '/');
            } elseif (is_file($fullPath) && is_readable($fullPath)) {
                // Skip files > 50MB
                if (filesize($fullPath) < 50 * 1024 * 1024) {
                    $zip->addFile($fullPath, $zipPath);
                }
            }
        }
    }

    /**
     * List all backups
     */
    public function listBackups(): array {
        $backups = [];
        $files = glob($this->backupDir . '/*.{zip,sql}', GLOB_BRACE);
        if (!$files) return [];

        foreach ($files as $file) {
            $name = basename($file);
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            $type = 'unknown';
            if (strpos($name, '_full.') !== false) $type = 'full';
            elseif (strpos($name, '_db.') !== false) $type = 'database';
            elseif (strpos($name, '_files.') !== false) $type = 'files';
            elseif ($ext === 'sql') $type = 'database';

            $backups[] = [
                'filename' => $name,
                'type' => $type,
                'size' => filesize($file),
                'size_formatted' => $this->formatBytes(filesize($file)),
                'created' => date('Y-m-d H:i:s', filemtime($file)),
                'created_ago' => $this->timeAgo(filemtime($file))
            ];
        }

        usort($backups, fn($a, $b) => strtotime($b['created']) - strtotime($a['created']));
        return $backups;
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(string $filename): array {
        if (empty($filename)) return ['success' => false, 'message' => 'No file specified'];

        $path = $this->backupDir . '/' . $filename;
        $realDir = realpath($this->backupDir);
        $realPath = realpath($path);

        if (!$realPath || !$realDir || strpos($realPath, $realDir) !== 0) {
            return ['success' => false, 'message' => 'Invalid file path'];
        }
        if (!file_exists($realPath)) {
            return ['success' => false, 'message' => 'File not found'];
        }

        if (@unlink($realPath)) {
            return ['success' => true, 'message' => 'Backup deleted successfully'];
        }
        return ['success' => false, 'message' => 'Failed to delete backup'];
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(string $filename): array {
        $path = $this->backupDir . '/' . $filename;
        $realDir = realpath($this->backupDir);
        $realPath = realpath($path);

        if (!$realPath || !$realDir || strpos($realPath, $realDir) !== 0 || !file_exists($realPath)) {
            return ['success' => false, 'message' => 'Backup file not found'];
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === 'sql') {
            return $this->restoreDatabase($realPath);
        }

        if ($ext === 'zip') {
            if (!class_exists('ZipArchive')) {
                return ['success' => false, 'message' => 'PHP Zip extension (ZipArchive) is not enabled on this server. Cannot restore from ZIP.'];
            }
            $zip = new ZipArchive();
            if ($zip->open($realPath) !== true) {
                return ['success' => false, 'message' => 'Cannot open ZIP file'];
            }

            // Validate ZIP entry names to prevent Zip Slip / path traversal
            $hasSql = false;
            $hasFiles = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (strpos($name, '..') !== false || str_starts_with($name, '/') || str_starts_with($name, '\\')) {
                    $zip->close();
                    return ['success' => false, 'message' => 'Security Error: ZIP archive contains invalid or malicious path entries.'];
                }
                if (preg_match('/\.sql$/i', $name)) $hasSql = true;
                if (strpos($name, 'files/') === 0) $hasFiles = true;
            }

            $tempDir = sys_get_temp_dir() . '/backup_restore_' . uniqid();
            mkdir($tempDir, 0750, true);
            $zip->extractTo($tempDir);
            $zip->close();

            $messages = [];

            // Restore database
            if ($hasSql) {
                $sqlFiles = glob($tempDir . '/*.sql');
                if (!empty($sqlFiles)) {
                    $dbResult = $this->restoreDatabase($sqlFiles[0]);
                    $messages[] = $dbResult['message'];
                }
            }

            // Restore files (with safety checks)
            if ($hasFiles && is_dir($tempDir . '/files')) {
                $this->copyDirectory($tempDir . '/files', $this->projectRoot);
                $messages[] = 'Files restored successfully';
            }

            // Cleanup temp
            $this->removeDirectory($tempDir);

            return [
                'success' => true,
                'message' => implode('. ', $messages) ?: 'Backup restored successfully'
            ];
        }

        return ['success' => false, 'message' => 'Unsupported file format'];
    }

    /**
     * Restore database from SQL file
     */
    private function restoreDatabase(string $sqlPath): array {
        $sql = file_get_contents($sqlPath);
        if ($sql === false) {
            return ['success' => false, 'message' => 'Cannot read SQL file'];
        }

        try {
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET FOREIGN_KEY_CHECKS=0");

            // Split by semicolon, handling multi-line statements
            $statements = array_filter(
                array_map('trim', preg_split('/;\s*\n/', $sql)),
                fn($s) => !empty($s) && !str_starts_with($s, '--')
            );

            $executed = 0;
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $this->conn->exec($statement);
                    $executed++;
                }
            }

            $this->conn->exec("SET FOREIGN_KEY_CHECKS=1");
            return ['success' => true, 'message' => "Database restored ($executed statements executed)"];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database restore error: ' . $e->getMessage()];
        }
    }

    private function copyDirectory(string $src, string $dst): void {
        $dir = opendir($src);
        if (!is_dir($dst)) mkdir($dst, 0750, true);
        $protectedFiles = ['.env', '.env.php', 'config.php', 'database.php', '.htaccess', '.user.ini', 'php.ini'];
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') continue;
            // Prevent overwriting critical system files during restore
            if (in_array(strtolower($file), $protectedFiles, true)) continue;

            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }

    private function removeDirectory(string $dir): void {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function timeAgo(int $timestamp): string {
        $diff = time() - $timestamp;
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M d, Y', $timestamp);
    }

    /**
     * Send backup to configured cloud sync endpoint
     */
    public function syncToCloud(string $filePath, string $filename): array {
        // Fetch cloud sync settings
        $stmt = $this->conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'backup_cloud_sync_url'");
        $stmt->execute();
        $syncUrl = $stmt->fetchColumn() ?: '';

        if (empty($syncUrl)) {
            return ['success' => false, 'message' => 'Cloud Sync URL not configured'];
        }

        // Mock response if syncUrl is a special simulation URL
        if (strpos($syncUrl, 'mock') !== false || strpos($syncUrl, 'localhost') !== false || strpos($syncUrl, '127.0.0.1') !== false) {
            return ['success' => true, 'message' => 'Synced to Mock Storage Endpoint successfully'];
        }

        // Send via Curl POST
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $syncUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            
            $mime = 'application/zip';
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
                $mime = 'application/sql';
            }
            $cfile = new CURLFile($filePath, $mime, $filename);
            
            $postData = [
                'backup_file' => $cfile,
                'filename' => $filename,
                'timestamp' => time()
            ];
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                return ['success' => true, 'message' => 'Synced to cloud endpoint: ' . htmlspecialchars($syncUrl)];
            }
            return ['success' => false, 'message' => "Cloud sync endpoint returned HTTP $httpCode"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Cloud sync error: ' . $e->getMessage()];
        }
    }

    /**
     * Trigger scheduled backup if due
     */
    public function checkAndTriggerSchedule(): void {
        // Fetch backup settings
        $stmt = $this->conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'backup_%'");
        $stmt->execute();
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $frequency = $settings['backup_schedule_frequency'] ?? 'off';
        $type = $settings['backup_schedule_type'] ?? 'database';
        $lastRun = (int)($settings['backup_schedule_last_run'] ?? 0);

        if ($frequency === 'off') {
            return;
        }

        $interval = 0;
        if ($frequency === 'daily') $interval = 86400;
        elseif ($frequency === 'weekly') $interval = 604800;
        elseif ($frequency === 'monthly') $interval = 2592000;

        if (time() - $lastRun >= $interval) {
            // Update last run time first to prevent race condition
            $stmt = $this->conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('backup_schedule_last_run', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([time()]);

            // Run backup
            $customName = 'auto_backup';
            $timestamp = date('Y_m_d_H_i');
            $filename = "{$customName}_{$timestamp}";

            try {
                $this->generateBackup($type, $filename);
            } catch (Exception $e) {
                // Ignore schedule errors to prevent blocking the user
            }
        }
    }
}
