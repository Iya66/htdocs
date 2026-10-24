<?php
/**
 * Cloud Aspire Admin Portal — Profile Photo Helper
 * 
 * Provides centralized validation, storage, retrieval, and removal of administrator
 * profile photos, ensuring strict security, MIME verification, path traversal prevention,
 * and seamless fallback handling.
 */

if (!defined('MAX_AVATAR_SIZE')) {
    define('MAX_AVATAR_SIZE', 2 * 1024 * 1024); // 2 MB Default
}

/**
 * Returns a web-accessible URL for an admin avatar, or null if empty / missing on disk.
 *
 * @param string|null $avatarPath Stored database path or URL
 * @param string $basePath Relative path prefix (e.g. '../' for subdirectories)
 * @return string|null Web-accessible URL or null for fallback
 */
function getAdminAvatarUrl(?string $avatarPath, string $basePath = ''): ?string
{
    if (empty($avatarPath)) {
        return null;
    }

    $avatarPath = trim($avatarPath);

    // External absolute URL
    if (preg_match('/^https?:\/\//i', $avatarPath)) {
        return filter_var($avatarPath, FILTER_VALIDATE_URL) ? $avatarPath : null;
    }

    // Relative filesystem upload
    $cleanPath = ltrim(str_replace('\\', '/', $avatarPath), '/');

    // Prevent directory traversal
    if (str_contains($cleanPath, '..')) {
        return null;
    }

    $projectRoot = dirname(__DIR__);
    $diskFile = $projectRoot . '/' . $cleanPath;

    if (file_exists($diskFile) && is_file($diskFile)) {
        return rtrim($basePath, '/') . '/' . $cleanPath;
    }

    return null;
}

/**
 * Validates and saves an uploaded administrator profile photo.
 *
 * @param PDO $conn Database connection
 * @param int $adminId Target administrator ID
 * @param array $file $_FILES['avatar'] element
 * @param string|null &$errorMsg Populated with user-friendly error on failure
 * @param int|null $maxSizeBytes Maximum allowed size in bytes (defaults to 2 MB)
 * @return string|null Relative path to stored avatar or null on failure
 */
function uploadAdminProfilePhoto(PDO $conn, int $adminId, array $file, ?string &$errorMsg = null, ?int $maxSizeBytes = null): ?string
{
    $maxSize = $maxSizeBytes ?? MAX_AVATAR_SIZE;

    // 1. Verify upload error status
    if (!isset($file['error']) || is_array($file['error'])) {
        $errorMsg = 'Invalid file upload parameters.';
        return null;
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errorMsg = 'File exceeds maximum upload size (max ' . round($maxSize / (1024 * 1024), 1) . ' MB).';
            return null;
        case UPLOAD_ERR_NO_FILE:
            $errorMsg = 'No file was selected for upload.';
            return null;
        default:
            $errorMsg = 'An upload error occurred. Please try again.';
            return null;
    }

    // 2. Validate file size
    if ($file['size'] > $maxSize) {
        $errorMsg = 'File is too large. Maximum allowed size is ' . round($maxSize / (1024 * 1024), 1) . ' MB.';
        return null;
    }

    if ($file['size'] < 100) {
        $errorMsg = 'Uploaded file is too small or empty.';
        return null;
    }

    // 3. Validate temporary file exists
    if (!is_uploaded_file($file['tmp_name'])) {
        $errorMsg = 'Possible file upload attack detected.';
        return null;
    }

    // 4. Validate extension
    $originalName = $file['name'] ?? '';
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowedExts, true)) {
        $errorMsg = 'Invalid file extension. Only JPG, JPEG, PNG, and WebP are allowed.';
        return null;
    }

    // 5. Server-side MIME validation using PHP Fileinfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        $errorMsg = 'Server environment does not support file inspection.';
        return null;
    }
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
        'image/webp' => ['webp']
    ];

    if (!isset($allowedMimes[$mimeType])) {
        $errorMsg = 'Invalid image format. Detected type: ' . htmlspecialchars($mimeType) . '. Allowed: JPG, PNG, WebP.';
        return null;
    }

    // Ensure extension matches MIME type
    if (!in_array($ext, $allowedMimes[$mimeType], true)) {
        // Normalize extension to primary match
        $ext = $allowedMimes[$mimeType][0];
    }

    // 6. Deep image inspection with getimagesize
    $imageInfo = @getimagesize($file['tmp_name']);
    if (!$imageInfo || empty($imageInfo[0]) || empty($imageInfo[1])) {
        $errorMsg = 'The uploaded file is not a valid image.';
        return null;
    }

    // 7. Verify administrator exists
    $stmt = $conn->prepare("SELECT id, avatar FROM admin WHERE id = ? LIMIT 1");
    $stmt->execute([$adminId]);
    $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingAdmin) {
        $errorMsg = 'Administrator record not found.';
        return null;
    }

    // 8. Prepare target directory
    $projectRoot = dirname(__DIR__);
    $uploadDir = $projectRoot . '/uploads/avatars';

    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0755, true)) {
            $errorMsg = 'Failed to initialize avatar storage directory.';
            return null;
        }
    }

    // Ensure .htaccess security in avatars directory
    $htaccessFile = $uploadDir . '/.htaccess';
    if (!file_exists($htaccessFile)) {
        @file_put_contents($htaccessFile, "# Disable script execution\n<FilesMatch \"\\.(php|phtml|phar|sh|pl|py|cgi)$\">\nRequire all denied\n</FilesMatch>\n<IfModule mod_php.c>\nphp_flag engine off\n</IfModule>\n");
    }

    // 9. Generate unpredictable, unique filename
    try {
        $randomHex = bin2hex(random_bytes(8));
    } catch (Throwable $e) {
        $randomHex = substr(md5(uniqid((string)$adminId, true)), 0, 16);
    }

    $newFileName = sprintf('avatar_%d_%d_%s.%s', $adminId, time(), $randomHex, $ext);
    $targetFilePath = $uploadDir . '/' . $newFileName;
    $relativeDbPath = 'uploads/avatars/' . $newFileName;

    // 10. Store file
    if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        $errorMsg = 'Failed to save uploaded image to destination.';
        return null;
    }

    // Set safe permissions
    @chmod($targetFilePath, 0644);

    // 11. Update database record
    try {
        $updateStmt = $conn->prepare("UPDATE admin SET avatar = ? WHERE id = ?");
        $updateStmt->execute([$relativeDbPath, $adminId]);
    } catch (Throwable $e) {
        // Rollback uploaded file if DB update fails
        @unlink($targetFilePath);
        $errorMsg = 'Database update failed. Please try again.';
        return null;
    }

    // 12. Safely remove previous avatar file if in uploads/avatars/
    $oldAvatar = $existingAdmin['avatar'] ?? '';
    if (!empty($oldAvatar) && str_starts_with($oldAvatar, 'uploads/avatars/')) {
        $oldFileBasename = basename($oldAvatar);
        $oldFilePath = $uploadDir . '/' . $oldFileBasename;
        if (file_exists($oldFilePath) && is_file($oldFilePath) && $oldFilePath !== $targetFilePath) {
            @unlink($oldFilePath);
        }
    }

    // 13. Update session if this is the currently authenticated admin
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] === $adminId) {
        $_SESSION['admin_avatar'] = $relativeDbPath;
    }

    // 14. Activity logging
    if (function_exists('logAudit')) {
        logAudit($conn, 'UPLOAD_PROFILE_PHOTO', "Profile photo updated for admin ID $adminId", $_SESSION['admin_id'] ?? $adminId);
    }

    return $relativeDbPath;
}

/**
 * Removes an administrator's profile photo and restores default avatar.
 *
 * @param PDO $conn Database connection
 * @param int $adminId Target administrator ID
 * @param string|null &$errorMsg Populated with user-friendly error on failure
 * @return bool True on success
 */
function removeAdminProfilePhoto(PDO $conn, int $adminId, ?string &$errorMsg = null): bool
{
    // 1. Fetch current avatar
    $stmt = $conn->prepare("SELECT id, avatar FROM admin WHERE id = ? LIMIT 1");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        $errorMsg = 'Administrator record not found.';
        return null;
    }

    $currentAvatar = $admin['avatar'] ?? '';

    // 2. Clear database column
    try {
        $updateStmt = $conn->prepare("UPDATE admin SET avatar = NULL WHERE id = ?");
        $updateStmt->execute([$adminId]);
    } catch (Throwable $e) {
        $errorMsg = 'Failed to reset profile photo in database.';
        return false;
    }

    // 3. Delete previous file safely if stored in uploads/avatars/
    if (!empty($currentAvatar) && str_starts_with($currentAvatar, 'uploads/avatars/')) {
        $projectRoot = dirname(__DIR__);
        $oldFileBasename = basename($currentAvatar);
        $oldFilePath = $projectRoot . '/uploads/avatars/' . $oldFileBasename;

        if (file_exists($oldFilePath) && is_file($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    // 4. Update session
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] === $adminId) {
        $_SESSION['admin_avatar'] = '';
    }

    // 5. Activity log
    if (function_exists('logAudit')) {
        logAudit($conn, 'REMOVE_PROFILE_PHOTO', "Profile photo removed for admin ID $adminId", $_SESSION['admin_id'] ?? $adminId);
    }

    return true;
}
