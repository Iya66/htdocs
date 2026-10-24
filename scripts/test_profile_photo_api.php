<?php
/**
 * Test Suite for Profile Photo API & UI Endpoints
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
echo "  PROFILE PHOTO API & ENDPOINTS TEST SUITE\n";
echo "====================================================================\n\n";

function runApiSubprocess(array $session, array $post, array $server = []): array
{
    $tempDir = sys_get_temp_dir();
    $pid = getmypid();
    $driverFile = $tempDir . "/photo_api_driver_{$pid}.php";
    $specFile = $tempDir . "/photo_api_spec_{$pid}.json";

    file_put_contents($specFile, json_encode([
        'session' => $session,
        'post'    => $post,
        'server'  => $server
    ]));

    $driverCode = <<<'PHP'
<?php
$spec = json_decode(file_get_contents($argv[1]), true);
session_start();
$_SESSION = $spec['session'] ?? [];
$_POST = $spec['post'] ?? [];
$_REQUEST = array_merge($_GET, $_POST);
$_SERVER['REQUEST_METHOD'] = $spec['server']['REQUEST_METHOD'] ?? 'POST';
$_SERVER['HTTP_X_CSRF_TOKEN'] = $spec['server']['HTTP_X_CSRF_TOKEN'] ?? ($spec['post']['csrf_token'] ?? '');
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

chdir('c:/xampp/htdocs');
require_once 'c:/xampp/htdocs/admin/api/profile-photo.php';
PHP;

    file_put_contents($driverFile, $driverCode);

    $cmd = sprintf('php "%s" "%s"', $driverFile, $specFile);
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $proc = proc_open($cmd, $descriptors, $pipes, dirname(__DIR__));
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($proc);

    @unlink($driverFile);
    @unlink($specFile);

    return [
        'exit'   => $exitCode,
        'stdout' => $stdout,
        'stderr' => $stderr,
        'json'   => json_decode($stdout, true)
    ];
}

// 1. Unauthenticated access
echo "--- 1. Authentication Enforcement ---\n";
$res = runApiSubprocess([], ['action' => 'upload']);
assertCheck($res['json'] !== null && $res['json']['success'] === false, "Unauthenticated request returns success=false");
assertCheck(str_contains(strtolower($res['json']['message'] ?? ''), 'unauthorized') || str_contains(strtolower($res['json']['message'] ?? ''), 'log in'), "Returns clear login prompt");

// 2. CSRF enforcement
echo "\n--- 2. CSRF Token Validation ---\n";
$mockSession = [
    'admin_id' => 1,
    'admin_name' => 'cloudaspire',
    'admin_role' => 'admin'
];
$res = runApiSubprocess($mockSession, ['action' => 'upload', 'csrf_token' => 'invalid_csrf_token_123']);
assertCheck($res['json'] !== null && $res['json']['success'] === false, "Invalid CSRF token rejected");
assertCheck(str_contains(strtolower($res['json']['message'] ?? ''), 'token') || str_contains(strtolower($res['json']['message'] ?? ''), 'security'), "Returns security token rejection message");

// 3. Authorization: Modifying another administrator's photo
echo "\n--- 3. Authorization & Privilege Enforcement ---\n";
$viewerToken = 'viewer_token_' . bin2hex(random_bytes(16));
$viewerSession = [
    'admin_id'   => 3,
    'admin_name' => 'TestViewer',
    'admin_role' => 'viewer',
    'csrf_token' => $viewerToken
];

$res = runApiSubprocess($viewerSession, [
    'action' => 'remove',
    'admin_id' => 1, // trying to remove admin 1's photo
    'csrf_token' => $viewerToken
]);
assertCheck($res['json'] !== null && $res['json']['success'] === false, "Viewer cannot modify another admin's photo");
assertCheck(str_contains(strtolower($res['json']['message'] ?? ''), 'access denied') || str_contains(strtolower($res['json']['message'] ?? ''), 'cannot modify'), "Returns access denied message");

// 4. Valid Removal via API
echo "\n--- 4. Valid Photo Removal via API ---\n";
$adminToken = 'admin_token_' . bin2hex(random_bytes(16));
$adminSession = [
    'admin_id'   => 1,
    'admin_name' => 'cloudaspire',
    'admin_role' => 'admin',
    'csrf_token' => $adminToken
];

$res = runApiSubprocess($adminSession, [
    'action' => 'remove',
    'csrf_token' => $adminToken
]);
assertCheck($res['json'] !== null && $res['json']['success'] === true, "Removal returns success=true");
assertCheck(isset($res['json']['default_initial']), "Removal returns default_initial for fallback display");

// 5. Get API action
echo "\n--- 5. Get Current Avatar API Action ---\n";
$res = runApiSubprocess($adminSession, ['action' => 'get'], ['REQUEST_METHOD' => 'GET']);
assertCheck($res['json'] !== null && $res['json']['success'] === true, "Get action returns success=true");
assertCheck(isset($res['json']['initial']), "Get action returns admin initial");

// 6. Header HTML Rendering Verification
echo "\n--- 6. Header HTML Rendering ---\n";
$_SESSION = $adminSession;
$_SESSION['admin_avatar'] = '';
ob_start();
include dirname(__DIR__) . '/admin/includes/header.php';
$headerHtml = ob_get_clean();

assertCheck(str_contains($headerHtml, 'id="headerProfileAvatar"'), "Header renders profile avatar container (#headerProfileAvatar)");
assertCheck(str_contains($headerHtml, 'class="profile-avatar-initial"'), "Header renders fallback initial when avatar is empty");
assertCheck(str_contains($headerHtml, 'accounts.php'), "Header profile button links to accounts.php");

// 7. Dashboard Welcome Hero Rendering Verification
echo "\n--- 7. Dashboard Welcome Banner & Avatar ---\n";
require_once dirname(__DIR__) . '/app/lead_statuses.php';
$leads_count = 10;
$services_count = 5;
$reviews_count = 3;
$why_us_count = 4;
$projects_count = 2;
$uploads_count = 8;
$staff_count = 2;
$new_leads = 2;
$base_path = '';
$admin_avatar_url = null;
$admin_name = 'cloudaspire';
$admin_role = 'admin';
$admin_initial = 'C';
$chart_dates = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$chart_counts = [1, 2, 3, 4, 5, 6, 7];

ob_start();
include dirname(__DIR__) . '/app/Views/admin/dashboard-pro.php';
$dashHtml = ob_get_clean();

assertCheck(str_contains($dashHtml, 'class="dash-welcome-card'), "Dashboard renders .dash-welcome-card banner");
assertCheck(str_contains($dashHtml, 'class="dash-welcome-avatar"'), "Dashboard renders .dash-welcome-avatar");
assertCheck(str_contains($dashHtml, 'Change Photo'), "Dashboard includes quick 'Change Photo' action");
assertCheck(str_contains($dashHtml, 'Edit Profile'), "Dashboard includes quick 'Edit Profile' action");

// 8. Accounts View Rendering Verification
echo "\n--- 8. Accounts View (My Profile) Rendering ---\n";
$current_user = [
    'id' => 1,
    'name' => 'cloudaspire',
    'username' => 'cloudaspire',
    'email' => 'cloudaspire2026@gmail.com',
    'wa_number' => '+91 9999999999',
    'avatar' => '',
    'role' => 'admin',
    'is_active' => 1,
    'last_login' => null,
    'created_at' => date('Y-m-d H:i:s')
];
$message = '';
$message_type = '';
$csrf_token = generateCSRFToken();

ob_start();
include dirname(__DIR__) . '/app/Views/admin/accounts.php';
$accountsHtml = ob_get_clean();

assertCheck(str_contains($accountsHtml, 'id="mainAvatarPreview"'), "Accounts view renders #mainAvatarPreview");
assertCheck(str_contains($accountsHtml, 'id="btnSelectNewPhoto"'), "Accounts view renders 'Change Photo' button");
assertCheck(str_contains($accountsHtml, 'id="profilePhotoFileInput"'), "Accounts view renders file input with image accept filters");
assertCheck(str_contains($accountsHtml, 'id="avatarPreviewBox"'), "Accounts view renders client-side instant preview box");
assertCheck(str_contains($accountsHtml, 'id="confirmRemovePhotoModal"'), "Accounts view renders confirmation modal for photo removal");
assertCheck(str_contains($accountsHtml, 'enctype="multipart/form-data"'), "Profile edit form supports multipart/form-data");

echo "\n====================================================================\n";
echo "  RESULTS: $passedChecks / $totalChecks CHECKS PASSED (" . ($failedChecks === 0 ? "100% SUCCESS" : "$failedChecks FAILED") . ")\n";
echo "====================================================================\n";

if ($failedChecks > 0) {
    exit(1);
}
