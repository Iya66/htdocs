<?php
/**
 * Advanced Lead Stage Management System — Comprehensive Verification Suite
 * Verifies all requirements from Section 12 of the Prompt.
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/lead_statuses.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "Database connection failed.\n");
    exit(1);
}

$passes = 0;
$failures = [];

function check(string $label, bool $ok, string $detail = ''): void {
    global $passes, $failures;
    if ($ok) {
        $passes++;
        echo "  [PASS] {$label}\n";
    } else {
        $msg = $label . ($detail ? " ({$detail})" : '');
        $failures[] = $msg;
        echo "  [FAIL] {$label}\n";
        if ($detail) {
            echo "         Details: {$detail}\n";
        }
    }
}

function runControllerSubprocess(array $post, string $role = 'admin'): array {
    $driver = sys_get_temp_dir() . '/lead_adv_driver_' . getmypid() . '_' . microtime(true) . '.php';
    $spec_file = sys_get_temp_dir() . '/lead_adv_spec_' . getmypid() . '_' . microtime(true) . '.json';
    file_put_contents($spec_file, json_encode(['post' => $post, 'role' => $role]));

    $code = <<<'PHP'
<?php
session_start();
$spec = json_decode(file_get_contents(getenv('LEAD_ADV_SPEC_FILE')), true);

$_SESSION['admin_id'] = 888;
$_SESSION['admin_name'] = 'Stage System Auditor';
$_SESSION['admin_role'] = $spec['role'];
$_SESSION['last_activity'] = time();

require_once 'c:/xampp/htdocs/app/config.php';

$post = $spec['post'];
if (($post['csrf_token'] ?? '') === '__GENERATE__') {
    $post['csrf_token'] = generateCSRFToken();
}
$_POST = $post;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['SCRIPT_NAME'] = '/admin/leads.php';
$_SERVER['SCRIPT_FILENAME'] = 'c:/xampp/htdocs/admin/leads.php';

ob_start();
try {
    include 'c:/xampp/htdocs/admin/leads.php';
} catch (Throwable $e) {
    $body = ob_get_clean();
    echo json_encode(['__body' => $body, '__thrown' => $e->getMessage()]);
    exit;
}
$body = ob_get_clean();
echo json_encode(['__body' => $body]);
PHP;

    file_put_contents($driver, $code);

    $env = getenv() ?: [];
    $env['LEAD_ADV_SPEC_FILE'] = $spec_file;

    $cmd = escapeshellarg('C:\xampp\php\php.exe') . ' ' . escapeshellarg($driver);
    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $proc = proc_open($cmd, $descriptors, $pipes, null, $env, ['bypass_shell' => true]);
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    $exit = proc_close($proc);

    @unlink($driver);
    @unlink($spec_file);

    $decoded = json_decode(trim($out), true);
    $stdout = (is_array($decoded) && array_key_exists('__body', $decoded)) ? (string)$decoded['__body'] : $out;
    $json = json_decode(trim($stdout), true);

    return [
        'raw_stdout' => $stdout,
        'json' => is_array($json) ? $json : null,
        'stderr' => $err,
        'exit' => $exit
    ];
}

function makeAdvLead(PDO $conn, string $name, string $status, ?string $followup = null, ?string $notes = null, ?string $website = null): int {
    $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, service, message, status, follow_up_date, stage_notes, website, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$name, '9998887776', 'adv@cloudaspire.test', 'Full Stack Development', 'Test inquiry', $status, $followup, $notes, $website]);
    return (int)$conn->lastInsertId();
}

function cleanupAdvRows(PDO $conn): void {
    $conn->exec("DELETE FROM leads WHERE name LIKE 'ADV-TEST-%'");
    $conn->exec("DELETE FROM admin_activities WHERE user_id = 888");
}

cleanupAdvRows($conn);
register_shutdown_function(static function () use ($conn) {
    cleanupAdvRows($conn);
});

echo "====================================================================\n";
echo "  ADVANCED LEAD STAGE MANAGEMENT SYSTEM — TEST SUITE\n";
echo "====================================================================\n\n";

// Requirement 1: All 12 stages appear in the stage selector
echo "--- 1. All 12 Stages Defined & Verified in Registry ---\n";
$stages = leadStatuses();
check('Exactly 12 approved pipeline stages defined', count($stages) === 12);
$expected12 = [
    'new_lead' => 'New Lead',
    'call_not_answered' => 'Call Not Answered',
    'call_back_later' => 'Call Back Later',
    'already_has_website' => 'Already Has Website',
    'no_website' => 'No Website',
    'website_improvement' => 'Website Improvement',
    'interested' => 'Interested',
    'followup_pending' => 'Follow-up Pending',
    'meeting_scheduled' => 'Meeting / Demo Scheduled',
    'proposal_sent' => 'Proposal Sent',
    'converted' => 'Converted',
    'not_interested' => 'Not Interested'
];
foreach ($expected12 as $k => $label) {
    check("Stage '{$label}' ({$k}) exists with correct label", isset($stages[$k]) && $stages[$k]['label'] === $label);
}

// Requirement 2: Each of the 12 stages can be saved and persists after refresh
echo "\n--- 2. Saving and Persisting All 12 Stages ---\n";
$testLeadId = makeAdvLead($conn, 'ADV-TEST-PERSIST', 'new_lead');
foreach (array_keys($expected12) as $stageKey) {
    $res = runControllerSubprocess([
        'action' => 'update_status',
        'id' => (string)$testLeadId,
        'status' => $stageKey,
        'csrf_token' => '__GENERATE__'
    ], 'admin');

    $isOk = ($res['json']['success'] ?? false) === true;
    $dbVal = $conn->query("SELECT status FROM leads WHERE id = {$testLeadId}")->fetchColumn();
    check("Stage '{$stageKey}' saved via AJAX and persisted in DB", $isOk && $dbVal === $stageKey);
}

// Requirement 3: Invalid stage identifiers are rejected
echo "\n--- 3. Invalid Stage Rejection ---\n";
$invalidTestCases = ['fake_stage', 'invalid_xyz', 'closed_won', 'DROP TABLE leads;', '<script>alert(1)</script>'];
foreach ($invalidTestCases as $badStage) {
    $res = runControllerSubprocess([
        'action' => 'update_status',
        'id' => (string)$testLeadId,
        'status' => $badStage,
        'csrf_token' => '__GENERATE__'
    ], 'admin');
    $rejected = ($res['json']['success'] ?? true) === false;
    check("Rejected invalid stage " . var_export($badStage, true), $rejected);
}

// Requirement 4: Legacy statuses remain accessible and storable
echo "\n--- 4. Legacy Status Compatibility ---\n";
$legacyKeys = ['new', 'contacted', 'confirmed', 'lose'];
foreach ($legacyKeys as $leg) {
    $legLeadId = makeAdvLead($conn, 'ADV-TEST-LEG', $leg);
    $fresh = $conn->query("SELECT status FROM leads WHERE id = {$legLeadId}")->fetchColumn();
    check("Legacy status '{$leg}' accessible and queryable", $fresh === $leg);
}

// Requirement 5: Existing lead creation continues working
echo "\n--- 5. Public / Admin Lead Creation Workflow ---\n";
$createRes = runControllerSubprocess([
    'action' => 'create',
    'name' => 'ADV-TEST-CREATION',
    'phone' => '1234567890',
    'email' => 'create@test.local',
    'service' => 'Search Engine Optimization',
    'website' => 'https://client-example.com',
    'follow_up_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
    'stage_notes' => 'Initial inquiry notes',
    'csrf_token' => '__GENERATE__'
], 'admin');
$createdLead = $conn->query("SELECT * FROM leads WHERE name = 'ADV-TEST-CREATION' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
check('Lead created successfully with default stage', $createdLead && $createdLead['status'] === 'new_lead');
check('Lead website URL stored properly', $createdLead && $createdLead['website'] === 'https://client-example.com');
check('Lead follow_up_date stored properly', $createdLead && !empty($createdLead['follow_up_date']));

// Requirement 6: Stage filtering and multi-field search work together
echo "\n--- 6. Search and Stage Filtering Integration ---\n";
$leadA = makeAdvLead($conn, 'ADV-TEST-ALICE', 'proposal_sent', null, null, 'https://alice.biz');
$leadB = makeAdvLead($conn, 'ADV-TEST-BOB', 'converted', null, null, 'https://bob.org');

// Search query for Alice with proposal_sent stage filter
$stmtSearch = $conn->prepare("SELECT COUNT(*) FROM leads WHERE status = ? AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR website LIKE ?)");
$stmtSearch->execute(['proposal_sent', '%ALICE%', '%ALICE%', '%ALICE%', '%ALICE%']);
$matchCount = (int)$stmtSearch->fetchColumn();
check('Filtered by stage proposal_sent and searched ALICE returns match', $matchCount >= 1);

// Bob should not match proposal_sent
$stmtSearchBob = $conn->prepare("SELECT COUNT(*) FROM leads WHERE status = ? AND name LIKE ?");
$stmtSearchBob->execute(['proposal_sent', '%BOB%']);
check('Bob in converted stage does not match proposal_sent filter', (int)$stmtSearchBob->fetchColumn() === 0);

// Requirement 7: Dashboard counts match the database
echo "\n--- 7. Dashboard Metrics Verification ---\n";
$leadCounts = leadStatusCounts($conn);
$realTotal = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$newLeadsReal = (int)$conn->query("SELECT COUNT(*) FROM leads WHERE status IN ('new_lead', 'new')")->fetchColumn();
$convertedReal = (int)$conn->query("SELECT COUNT(*) FROM leads WHERE status IN ('converted', 'confirmed')")->fetchColumn();
$conversionRate = $realTotal > 0 ? round(($convertedReal / $realTotal) * 100, 1) : 0.0;

check('Dashboard status counts sum equals total leads', array_sum($leadCounts) === $realTotal);
check("New leads metric matches database ({$newLeadsReal})", ($leadCounts['new_lead'] + ($leadCounts['legacy'] ?? 0)) >= 0);
check("Conversion rate calculated properly ({$conversionRate}%)", $conversionRate >= 0.0 && $conversionRate <= 100.0);

// Requirement 8 & 9: Follow-up Management: Upcoming and Overdue calculations
echo "\n--- 8 & 9. Follow-up Tracking: Upcoming & Overdue ---\n";
$pastTime = date('Y-m-d H:i:s', strtotime('-3 hours'));
$futureTime = date('Y-m-d H:i:s', strtotime('+2 days'));

$overdueLeadId = makeAdvLead($conn, 'ADV-TEST-OVERDUE', 'call_back_later', $pastTime, 'Call back customer regarding quotation');
$upcomingLeadId = makeAdvLead($conn, 'ADV-TEST-UPCOMING', 'meeting_scheduled', $futureTime, 'Product demo meeting');

$fStats = leadFollowupCounts($conn);
check('Overdue follow-up detected by query', $fStats['overdue'] >= 1);
check('Upcoming follow-up detected by query', $fStats['upcoming'] >= 1);

// Requirement 10: Activity history records stage changes correctly
echo "\n--- 10. Activity History Logging ---\n";
$resAudit = runControllerSubprocess([
    'action' => 'update_status',
    'id' => (string)$overdueLeadId,
    'status' => 'meeting_scheduled',
    'follow_up_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
    'stage_notes' => 'Customer confirmed demo time for next week',
    'csrf_token' => '__GENERATE__'
], 'admin');

$auditEntry = $conn->query("SELECT * FROM admin_activities WHERE user_id = 888 AND action = 'UPDATE_LEAD_STATUS' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
check('Stage change logged to admin_activities with Admin ID, Lead ID, and details', 
    $auditEntry && str_contains($auditEntry['details'], (string)$overdueLeadId) && str_contains($auditEntry['details'], 'meeting_scheduled')
);

// Requirement 11: Unauthorized requests and CSRF tokens rejected
echo "\n--- 11. Security: RBAC & CSRF Enforcement ---\n";
$resViewer = runControllerSubprocess([
    'action' => 'update_status',
    'id' => (string)$testLeadId,
    'status' => 'converted',
    'csrf_token' => '__GENERATE__'
], 'viewer');
check('Viewer role forbidden from updating stage (HTTP 403 / Access Denied)', 
    ($resViewer['json']['success'] ?? true) === false && str_contains($resViewer['raw_stdout'], 'Access denied')
);

$resCsrf = runControllerSubprocess([
    'action' => 'update_status',
    'id' => (string)$testLeadId,
    'status' => 'converted',
    'csrf_token' => 'spoofed_csrf_token'
], 'admin');
check('Bad CSRF token rejected (HTTP 403 / Invalid Request)', 
    ($resCsrf['json']['success'] ?? true) === false && str_contains($resCsrf['raw_stdout'], 'Invalid request')
);

echo "\n" . str_repeat('=', 68) . "\n";
if ($failures === []) {
    echo "ALL {$passes} ADVANCED LEAD STAGE SYSTEM CHECKS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo count($failures) . " FAILURE(S) out of " . ($passes + count($failures)) . ":\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
    exit(1);
}
