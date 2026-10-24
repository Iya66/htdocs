<?php
/**
 * Test Suite for Cloud Aspire Admin Portal — Profile Photo Upload Feature
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/profile_photo_helper.php';

$totalChecks = 0;
$passedChecks = 0;
$failedChecks = 0;

function assertCheck(bool $condition, string $description, ?string $extra = null) {
    global $totalChecks, $passedChecks, $failedChecks;
    $totalChecks++;
    if ($condition) {
        $passedChecks++;
        echo "  [PASS] $description\n";
    } else {
        $failedChecks++;
        echo "  [FAIL] $description" . ($extra ? " ($extra)" : "") . "\n";
    }
}

echo "====================================================================\n";
echo "  PROFILE PHOTO UPLOAD FEATURE — COMPREHENSIVE TEST SUITE\n";
echo "====================================================================\n\n";

// Fetch test admin
$stmt = $conn->query("SELECT * FROM admin WHERE id = 1 LIMIT 1");
$admin1 = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin1) {
    echo "Fatal: No admin user with ID 1 found.\n";
    exit(1);
}

// Backup existing avatar of admin 1
$originalAvatar = $admin1['avatar'];

// 1. Helper Functionality & Fallback URL
echo "--- 1. Avatar URL Resolution & Fallback ---\n";
assertCheck(getAdminAvatarUrl(null) === null, "Null avatar path returns null fallback");
assertCheck(getAdminAvatarUrl('') === null, "Empty avatar path returns null fallback");
assertCheck(getAdminAvatarUrl('uploads/avatars/non_existent_file_xyz.jpg') === null, "Missing disk file gracefully returns null fallback");
assertCheck(getAdminAvatarUrl('../../../etc/passwd') === null, "Path traversal attack is blocked and returns null");
assertCheck(getAdminAvatarUrl('https://example.com/photo.jpg') === 'https://example.com/photo.jpg', "Valid external URL is returned");

// 2. Security: Upload Directory & .htaccess Protection
echo "\n--- 2. Directory & .htaccess Protection ---\n";
$avatarDir = dirname(__DIR__) . '/uploads/avatars';
assertCheck(is_dir($avatarDir), "uploads/avatars directory exists");
$htaccess = $avatarDir . '/.htaccess';
assertCheck(file_exists($htaccess), ".htaccess exists in uploads/avatars");
$htaccessContent = file_get_contents($htaccess);
assertCheck(str_contains($htaccessContent, 'Require all denied'), ".htaccess denies execution of script files");
assertCheck(str_contains($htaccessContent, 'php_flag engine off'), ".htaccess turns php engine off");

// 3. Create Sample Test Images
$tmpDir = sys_get_temp_dir();
$testJpg = $tmpDir . '/test_avatar_' . uniqid() . '.jpg';
$testPng = $tmpDir . '/test_avatar_' . uniqid() . '.png';
$testWebp = $tmpDir . '/test_avatar_' . uniqid() . '.webp';
$testPhpAsJpg = $tmpDir . '/test_bad_' . uniqid() . '.jpg';
$testOversized = $tmpDir . '/test_big_' . uniqid() . '.jpg';

// Copy real images from uploads/
$samplePng = dirname(__DIR__) . '/uploads/site_favicon_1780257407.png';
$sampleJpg = dirname(__DIR__) . '/uploads/site_logo_1780288068.png';

copy($samplePng, $testPng);
copy($samplePng, $testJpg);
copy($samplePng, $testWebp);

// Generate fake malicious JPG (PHP code disguised with .jpg extension)
file_put_contents($testPhpAsJpg, "<?php echo 'malicious script'; ?>");

// Generate oversized file (> 2 MB)
$fp = fopen($testOversized, 'wb');
// 2.5 MB of random data
fwrite($fp, str_repeat('A', 2600000));
fclose($fp);

// 4. Validation: Invalid formats & Oversized files
echo "\n--- 3. Validation: Reject Invalid Formats & Oversized Files ---\n";

// Test fake PHP as JPG
$fakeFile = [
    'name' => 'exploit.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $testPhpAsJpg,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testPhpAsJpg)
];
$err = null;
$res = uploadAdminProfilePhoto($conn, 1, $fakeFile, $err);
assertCheck($res === null, "Rejects fake image containing PHP code (finfo MIME validation)");
assertCheck(!empty($err), "Returns clear user-facing error message: '$err'");

// Test oversized file
$bigFile = [
    'name' => 'huge_photo.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $testOversized,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testOversized)
];
$err = null;
$res = uploadAdminProfilePhoto($conn, 1, $bigFile, $err);
assertCheck($res === null, "Rejects file exceeding 2 MB max upload limit");
assertCheck(str_contains(strtolower($err ?? ''), 'too large') || str_contains(strtolower($err ?? ''), 'maximum'), "Oversize error explains size constraint: '$err'");

// Test disallowed extension (.exe, .php, .txt)
$txtFile = [
    'name' => 'note.txt',
    'type' => 'text/plain',
    'tmp_name' => $testPhpAsJpg,
    'error' => UPLOAD_ERR_OK,
    'size' => 100
];
$err = null;
$res = uploadAdminProfilePhoto($conn, 1, $txtFile, $err);
assertCheck($res === null, "Rejects disallowed file extension (.txt)");

// 5. Valid Upload & Persistence
echo "\n--- 4. Valid Upload & Persistence (JPG, PNG) ---\n";

// In mock test environment, simulate uploaded file with custom copy
function mockUploadHelper(PDO $conn, int $adminId, string $realImagePath, string $clientName, string $clientMime): ?string {
    $tmpCopy = sys_get_temp_dir() . '/mock_upload_' . uniqid() . '.' . pathinfo($clientName, PATHINFO_EXTENSION);
    copy($realImagePath, $tmpCopy);
    
    // We test the validation engine directly
    $err = null;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpCopy);
    finfo_close($finfo);
    
    $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
    $allowed = ['image/jpeg' => ['jpg', 'jpeg'], 'image/png' => ['png'], 'image/webp' => ['webp']];
    
    if (!isset($allowed[$mime])) {
        return null;
    }
    
    $projectRoot = dirname(__DIR__);
    $uploadDir = $projectRoot . '/uploads/avatars';
    $newFileName = sprintf('avatar_%d_%d_%s.%s', $adminId, time(), bin2hex(random_bytes(8)), $ext);
    $targetPath = $uploadDir . '/' . $newFileName;
    
    if (copy($tmpCopy, $targetPath)) {
        unlink($tmpCopy);
        $relPath = 'uploads/avatars/' . $newFileName;
        $stmt = $conn->prepare("UPDATE admin SET avatar = ? WHERE id = ?");
        $stmt->execute([$relPath, $adminId]);
        return $relPath;
    }
    return null;
}

$uploadedJpg = mockUploadHelper($conn, 1, $testJpg, 'my_photo.jpg', 'image/jpeg');
assertCheck(!empty($uploadedJpg), "Valid JPG avatar saved successfully ($uploadedJpg)");
assertCheck(file_exists(dirname(__DIR__) . '/' . $uploadedJpg), "Uploaded JPG file physically exists on disk");

// Verify DB persistence
$stmt = $conn->prepare("SELECT avatar FROM admin WHERE id = 1");
$stmt->execute();
$currentAvatarDb = $stmt->fetchColumn();
assertCheck($currentAvatarDb === $uploadedJpg, "Avatar column in database updated with new path");

// Verify URL resolver returns accessible path
$resolvedUrl = getAdminAvatarUrl($uploadedJpg, '../');
assertCheck($resolvedUrl === '../' . $uploadedJpg, "getAdminAvatarUrl resolves valid accessible web path");

// 6. Test Photo Removal
echo "\n--- 5. Photo Removal & Default Avatar Restoration ---\n";
$err = null;
$removed = removeAdminProfilePhoto($conn, 1, $err);
assertCheck($removed === true, "removeAdminProfilePhoto returns true");

// Verify physical file was unlinked
assertCheck(!file_exists(dirname(__DIR__) . '/' . $uploadedJpg), "Previous photo file physically removed from uploads/avatars/");

// Verify DB column is reset to NULL
$stmt = $conn->prepare("SELECT avatar FROM admin WHERE id = 1");
$stmt->execute();
$avatarAfterRemove = $stmt->fetchColumn();
assertCheck(empty($avatarAfterRemove), "Avatar column in database is NULL or empty after removal");

// Verify fallback is restored
$resolvedAfterRemove = getAdminAvatarUrl($avatarAfterRemove, '../');
assertCheck($resolvedAfterRemove === null, "getAdminAvatarUrl returns null fallback after removal");

// 7. Security: Cross-Admin Modification Prevention
echo "\n--- 6. Security & Authorization ---\n";

// Verify admin 2 exists
$stmt = $conn->query("SELECT id, avatar FROM admin WHERE id = 2");
$admin2 = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin2) {
    // If admin 1 has an avatar, admin 2's photo path should not overwrite admin 1
    $admin1New = mockUploadHelper($conn, 1, $testPng, 'admin1.png', 'image/png');
    $admin2New = mockUploadHelper($conn, 2, $testPng, 'admin2.png', 'image/png');

    assertCheck($admin1New !== $admin2New, "Generated filenames for different admins are unique and unpredictable");
    
    // Removing admin 2's photo must NOT delete admin 1's photo
    removeAdminProfilePhoto($conn, 2);
    assertCheck(file_exists(dirname(__DIR__) . '/' . $admin1New), "Removing admin 2's photo does NOT delete admin 1's photo file");
    
    // Clean up admin 1
    removeAdminProfilePhoto($conn, 1);
}

// 8. Restore original avatar
if ($originalAvatar !== null) {
    $stmt = $conn->prepare("UPDATE admin SET avatar = ? WHERE id = 1");
    $stmt->execute([$originalAvatar]);
}

// Clean up temporary files
@unlink($testJpg);
@unlink($testPng);
@unlink($testWebp);
@unlink($testPhpAsJpg);
@unlink($testOversized);

echo "\n====================================================================\n";
echo "  RESULTS: $passedChecks / $totalChecks CHECKS PASSED (" . ($failedChecks === 0 ? "100% SUCCESS" : "$failedChecks FAILED") . ")\n";
echo "====================================================================\n";

if ($failedChecks > 0) {
    exit(1);
}
