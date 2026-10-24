<?php
/**
 * Controller-level tests for admin/leads.php status handling.
 *
 * Drives the real request-handling code path (POST -> switch -> response) by
 * invoking the controller in a subprocess with a controlled $_POST and a
 * simulated admin session, then asserts on the JSON/HTML response and the
 * database.
 *
 * Only rows this test creates are ever written, and they are removed at the
 * end. Existing leads are never modified.
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/lead_statuses.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "FATAL: no DB\n");
    exit(1);
}

$failures = [];
$passes = 0;

function check(string $label, bool $ok): void
{
    global $failures, $passes;
    if ($ok) { $passes++; echo "  \u{2713} {$label}\n"; }
    else { $failures[] = $label; echo "  \u{2717} {$label}\n"; }
}

/**
 * Run the controller's request-handling code in an isolated subprocess.
 *
 * The driver starts a real session, installs a simulated admin, mints a genuine
 * CSRF token, then includes admin/leads.php so the real POST switch executes.
 * The spec travels via a file so the child inherits the parent environment
 * untouched (replacing the env block breaks PDO's DNS lookup of the DB host on
 * Windows).
 */
function runController(array $post, string $role): array
{
    $driver = sys_get_temp_dir() . '/lead_ctrl_driver_' . getmypid() . '.php';
    $spec_file = sys_get_temp_dir() . '/lead_ctrl_spec_' . getmypid() . '.json';
    file_put_contents($spec_file, json_encode(['post' => $post, 'role' => $role]));

    // Nothing may be echoed before the include: auth.php calls session_start(),
    // which fails once output has flushed headers in CLI.
    $code = <<<'PHP'
<?php
session_start();
$spec = json_decode(file_get_contents(getenv('LEAD_CTRL_SPEC_FILE')), true);

$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Test Admin';
$_SESSION['admin_role'] = $spec['role'];
$_SESSION['last_activity'] = time();

require_once 'c:/xampp/htdocs/app/config.php';

// When the caller passes csrf_token = "__GENERATE__", mint a genuine token in
// this session so the CSRF check exercises the real code path end to end.
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
    echo json_encode(['__body' => $body, '__thrown' => $e->getMessage(), '__headers' => headers_list()]);
    exit;
}
$body = ob_get_clean();
echo json_encode(['__body' => $body, '__headers' => headers_list()]);
PHP;

    file_put_contents($driver, $code);

    // Pass the spec path through the environment, but START from the parent's
    // own environment so the child keeps SystemRoot/PATH/TEMP. Replacing the
    // env block wholesale breaks PDO's DNS lookup of the DB host on Windows.
    $env = getenv() ?: [];
    $env['LEAD_CTRL_SPEC_FILE'] = $spec_file;

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
    if (is_array($decoded) && array_key_exists('__body', $decoded)) {
        return [
            'stdout' => (string)$decoded['__body'],
            'stderr' => $err,
            'exit' => $exit,
            'headers' => $decoded['__headers'] ?? [],
            'thrown' => $decoded['__thrown'] ?? null,
        ];
    }

    return ['stdout' => $out, 'stderr' => $err, 'exit' => $exit, 'headers' => [], 'thrown' => null];
}

/** Create a throwaway lead directly. Rows are keyed by name so cleanup is safe. */
function makeLead(PDO $conn, string $status): int
{
    $s = $conn->prepare("INSERT INTO leads (name, phone, service, status) VALUES (?, ?, ?, ?)");
    $s->execute(['CTRL-TEST', '0000000000', 'Test', $status]);
    return (int)$conn->lastInsertId();
}

// Sweep up any rows this test left behind on a previous, interrupted run.
// Only rows this test itself creates (name = 'CTRL-TEST' / 'CTRL-CREATE') are
// ever touched.
function sweepTestRows(PDO $conn): int
{
    $n = (int)$conn->exec("DELETE FROM leads WHERE name IN ('CTRL-TEST', 'CTRL-CREATE')");
    return $n;
}

// Baseline snapshot of REAL leads only, taken before any test rows exist.
sweepTestRows($conn);
$before = $conn->query("SELECT id, name, status FROM leads ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Belt and braces: clean up on normal exit AND on fatal error / early bail.
register_shutdown_function(static function () use ($conn) {
    $n = sweepTestRows($conn);
    if ($n > 0) {
        echo "\nCleaned up {$n} test row(s).\n";
    }
});

echo "\n=== 1. Valid status update (AJAX / Kanban path) ===\n";
$id = makeLead($conn, 'new_lead');
$r = runController([
    'action' => 'update_status', 'id' => (string)$id,
    'status' => 'proposal_sent', 'csrf_token' => '__GENERATE__',
], 'admin');
check('Controller exits cleanly', $r['exit'] === 0);
check('No PHP error on stderr', !str_contains($r['stderr'], 'Fatal error') && !str_contains($r['stderr'], 'Warning:'));
check('No exception thrown', $r['thrown'] === null);
$json = json_decode(trim($r['stdout']), true);
check('Returns JSON with success=true', is_array($json) && ($json['success'] ?? false) === true);
check('Response echoes the saved status', ($json['status'] ?? '') === 'proposal_sent');
$stored = $conn->query("SELECT status FROM leads WHERE id = $id")->fetchColumn();
check("Database now holds 'proposal_sent'", $stored === 'proposal_sent');

echo "\n=== 2. All 12 stages can be written through the controller ===\n";
foreach (leadStatusKeys() as $key) {
    $lid = makeLead($conn, 'new_lead');
    $res = runController([
        'action' => 'update_status', 'id' => (string)$lid,
        'status' => $key, 'csrf_token' => '__GENERATE__',
    ], 'admin');
    $j = json_decode(trim($res['stdout']), true);
    $val = $conn->query("SELECT status FROM leads WHERE id = $lid")->fetchColumn();
    check("'{$key}' accepted and stored", ($j['success'] ?? false) === true && $val === $key);
}

echo "\n=== 3. Invalid status is rejected ===\n";
foreach (['', 'closed', 'qualified', 'NEW_LEAD', 'nope', 'new lead'] as $bad) {
    $lid = makeLead($conn, 'new_lead');
    $res = runController([
        'action' => 'update_status', 'id' => (string)$lid,
        'status' => $bad, 'csrf_token' => '__GENERATE__',
    ], 'admin');
    $j = json_decode(trim($res['stdout']), true);
    $val = $conn->query("SELECT status FROM leads WHERE id = $lid")->fetchColumn();
    check('Rejects ' . var_export($bad, true) . ' with success=false', ($j['success'] ?? true) === false);
    check("Row left untouched at 'new_lead' for " . var_export($bad, true), $val === 'new_lead');
    check('Error message is user-readable', !empty($j['message']));
}

echo "\n=== 4. CSRF protection still enforced ===\n";
$lid = makeLead($conn, 'new_lead');
$res = runController([
    'action' => 'update_status', 'id' => (string)$lid,
    'status' => 'converted', 'csrf_token' => 'not-a-real-token',
], 'admin');
$val = $conn->query("SELECT status FROM leads WHERE id = $lid")->fetchColumn();
check('Bad CSRF token is not accepted as a status update', !str_contains($res['stdout'], '"success":true'));
check('Row unchanged after CSRF rejection', $val === 'new_lead');
check('CSRF path surfaces the invalid-request message', str_contains($res['stdout'], 'Invalid request'));

echo "\n=== 5. Role permissions preserved ===\n";
$lid = makeLead($conn, 'new_lead');
$res = runController([
    'action' => 'update_status', 'id' => (string)$lid,
    'status' => 'converted', 'csrf_token' => '__GENERATE__',
], 'viewer');
$val = $conn->query("SELECT status FROM leads WHERE id = $lid")->fetchColumn();
check("Role 'viewer' cannot change status", $val === 'new_lead');
check("Role 'viewer' gets an access-denied message", str_contains($res['stdout'], 'Access denied'));

foreach (['admin', 'manager', 'editor', 'staff'] as $allowed) {
    $lid = makeLead($conn, 'new_lead');
    $res = runController([
        'action' => 'update_status', 'id' => (string)$lid,
        'status' => 'interested', 'csrf_token' => '__GENERATE__',
    ], $allowed);
    $val = $conn->query("SELECT status FROM leads WHERE id = $lid")->fetchColumn();
    check("Role '{$allowed}' can change status", $val === 'interested');
}

echo "\n=== 6. Bulk update validation ===\n";
$b1 = makeLead($conn, 'new_lead');
$b2 = makeLead($conn, 'new_lead');
$res = runController([
    'action' => 'bulk_update_status',
    'bulk_ids' => [(string)$b1, (string)$b2],
    'status' => 'not_interested', 'csrf_token' => '__GENERATE__',
], 'admin');
$vals = $conn->query("SELECT status FROM leads WHERE id IN ($b1,$b2)")->fetchAll(PDO::FETCH_COLUMN);
check('Bulk update applies the approved stage to all rows', $vals === ['not_interested', 'not_interested']);
check('Bulk success message names the stage label', str_contains($res['stdout'], 'Not Interested'));

$b3 = makeLead($conn, 'new_lead');
$res = runController([
    'action' => 'bulk_update_status', 'bulk_ids' => [(string)$b3],
    'status' => 'closed', 'csrf_token' => '__GENERATE__',
], 'admin');
$val = $conn->query("SELECT status FROM leads WHERE id = $b3")->fetchColumn();
check('Bulk update rejects an unapproved stage', $val === 'new_lead');
check('Bulk rejection surfaces a message', str_contains($res['stdout'], 'Invalid lead status'));

echo "\n=== 7. Full update (edit form) validation ===\n";
$e1 = makeLead($conn, 'new_lead');
$res = runController([
    'action' => 'update', 'id' => (string)$e1, 'name' => 'CTRL-TEST',
    'phone' => '0000000000', 'status' => 'garbage', 'csrf_token' => '__GENERATE__',
], 'admin');
$val = $conn->query("SELECT status FROM leads WHERE id = $e1")->fetchColumn();
check('Edit form rejects an invalid status', $val === 'new_lead');
check('Edit form shows a validation message', str_contains($res['stdout'], 'Invalid lead status'));

$e2 = makeLead($conn, 'new_lead');
$res = runController([
    'action' => 'update', 'id' => (string)$e2, 'name' => 'CTRL-TEST',
    'phone' => '0000000000', 'status' => 'meeting_scheduled', 'csrf_token' => '__GENERATE__',
], 'admin');
$val = $conn->query("SELECT status FROM leads WHERE id = $e2")->fetchColumn();
check('Edit form saves a valid approved stage', $val === 'meeting_scheduled');

echo "\n=== 8. Create defaults to New Lead ===\n";
$res = runController([
    'action' => 'create', 'name' => 'CTRL-CREATE', 'phone' => '0000000000',
    'service' => 'Test', 'csrf_token' => '__GENERATE__',
], 'admin');
$new_row = $conn->query("SELECT id, status FROM leads WHERE name = 'CTRL-CREATE' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
check('Create without a status lands on New Lead', $new_row && $new_row['status'] === 'new_lead');

echo "\n=== 9. Pre-existing leads untouched ===\n";
// The baseline is real leads only (test rows are swept before it is taken), so
// compare directly rather than filtering again.
$after = $conn->query("SELECT id, name, status FROM leads WHERE name <> 'CTRL-TEST' AND name <> 'CTRL-CREATE' ORDER BY id")
    ->fetchAll(PDO::FETCH_ASSOC);
check('Pre-existing rows unchanged', $after === $before);
check('Pre-existing row count unchanged', count($after) === count($before));

echo "\n" . str_repeat('=', 62) . "\n";
if ($failures === []) {
    echo "ALL {$passes} CONTROLLER CHECKS PASSED\n";
    exit(0);
}
echo count($failures) . " FAILURE(S) out of " . (count($failures) + $passes) . ":\n";
foreach ($failures as $f) { echo "  - {$f}\n"; }
exit(1);
