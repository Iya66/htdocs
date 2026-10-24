<?php
/**
 * Admin header API contract test.
 *
 * The header rebuild wires up two endpoints that were previously called by
 * nothing: admin/api/search.php and admin/api/notifications.php. This test
 * exercises them as real HTTP-ish requests (subprocess, faked session) and
 * checks the exact shape the footer JavaScript depends on.
 *
 *   C:\xampp\php\php.exe scripts\test_admin_header_api.php
 */

error_reporting(E_ALL);

$failures = [];
$passes   = 0;

function check(string $label, bool $ok, string $detail = ''): void
{
    global $failures, $passes;
    if ($ok) {
        $passes++;
        echo "  \u{2713} {$label}\n";
    } else {
        $failures[] = $label . ($detail ? " — {$detail}" : '');
        echo "  \u{2717} {$label}" . ($detail ? " — {$detail}" : '') . "\n";
    }
}

require_once __DIR__ . '/../app/config.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "FATAL: no database connection\n");
    exit(1);
}

$admin = $conn->query("SELECT id, name, role FROM admin ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$admin) {
    fwrite(STDERR, "FATAL: no admin row\n");
    exit(1);
}

/**
 * Run an API file in a child process with a faked admin session and a given
 * $_GET payload. Returns the decoded JSON (or null).
 */
function callApi(string $file, array $get): ?array
{
    $bootstrap = '<?php '
        . 'error_reporting(0); ini_set("display_errors","0");'
        . ' $_SERVER["REQUEST_METHOD"]="GET";'
        . ' $_SERVER["SCRIPT_NAME"]=' . var_export($file, true) . ';'
        . ' $_SERVER["REQUEST_URI"]=' . var_export($file . '?' . http_build_query($get), true) . ';'
        . ' $_SERVER["HTTP_HOST"]="localhost";'
        . ' $_SERVER["REMOTE_ADDR"]="127.0.0.1";'
        . ' $_SERVER["HTTP_USER_AGENT"]="api-test";'
        . ' session_start();'
        . ' $_SESSION["admin_id"]=' . (int)$GLOBALS['admin']['id'] . ';'
        . ' $_SESSION["admin_name"]=' . var_export((string)$GLOBALS['admin']['name'], true) . ';'
        . ' $_SESSION["admin_role"]=' . var_export((string)$GLOBALS['admin']['role'], true) . ';'
        . ' $_SESSION["last_activity"]=time();'
        . ' $_GET=' . var_export($get, true) . ';'
        . ' ob_start(); include ' . var_export($file, true) . ';'
        . ' $raw = ob_get_clean();'
        . ' $start = strpos($raw, "{");'
        . ' echo $start === false ? $raw : substr($raw, $start);';

    $tmp = tempnam(sys_get_temp_dir(), 'api_') . '.php';
    file_put_contents($tmp, $bootstrap);

    $out = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($tmp) . ' 2>&1');
    @unlink($tmp);

    if ($out === null) {
        return null;
    }

    $start = strpos($out, '{');
    if ($start === false) {
        return null;
    }

    $decoded = json_decode(substr($out, $start), true);
    return is_array($decoded) ? $decoded : null;
}

$searchFile = __DIR__ . '/../admin/api/search.php';
$notifFile  = __DIR__ . '/../admin/api/notifications.php';

// ─── Search API ────────────────────────────────────────────────────────────

echo "\n=== Search API ===\n";

// Empty query must return both keys as empty arrays, not null — the footer
// calls renderSearch() on the response directly.
$res = callApi($searchFile, ['q' => '']);
check(
    'empty query returns {pages:[], leads:[]}',
    is_array($res) && array_key_exists('pages', $res) && array_key_exists('leads', $res)
    && $res['pages'] === [] && $res['leads'] === [],
    json_encode($res)
);

// A term that matches a page.
$res = callApi($searchFile, ['q' => 'lead']);
check('search returns an array', is_array($res));
if (is_array($res)) {
    check('pages key is a list', isset($res['pages']) && is_array($res['pages']));
    check('leads key is a list', isset($res['leads']) && is_array($res['leads']));

    $pageCount = count($res['pages'] ?? []);
    check('matched at least one page for "lead"', $pageCount > 0, "matched {$pageCount}");

    if (!empty($res['pages'][0])) {
        $page = $res['pages'][0];
        check(
            'page result has title, url, icon',
            isset($page['title'], $page['url'], $page['icon']),
            json_encode(array_keys($page))
        );
    }

    // The footer renders lead.status_label and lead.email — both must exist,
    // and status_label must be a human label, not the raw enum key.
    foreach (($res['leads'] ?? []) as $lead) {
        check(
            "lead #{$lead['id']} carries status_label",
            array_key_exists('status_label', $lead) && $lead['status_label'] !== '',
            'missing or empty status_label'
        );
        check(
            "lead #{$lead['id']} carries email",
            array_key_exists('email', $lead),
            'footer renders lead.email'
        );
        // A raw key would be snake_case; labels contain spaces or are capitalised.
        $label = (string)($lead['status_label'] ?? '');
        $isRawKey = $label !== '' && $label === strtolower($label) && str_contains($label, '_');
        check(
            "lead #{$lead['id']} status_label is human-readable",
            !$isRawKey,
            "got raw key '{$label}'"
        );
        break;
    }
}

// A term matching nothing.
$res = callApi($searchFile, ['q' => 'zzzznotarealtermzzzz']);
check(
    'no matches returns empty lists',
    is_array($res) && ($res['pages'] ?? []) === [] && ($res['leads'] ?? []) === [],
    json_encode($res)
);

// SQL wildcards must not match everything.
$res = callApi($searchFile, ['q' => '%']);
check(
    'LIKE wildcard "%" is escaped, not treated as a pattern',
    is_array($res) && ($res['leads'] ?? []) === [],
    'unescaped % returned ' . count($res['leads'] ?? []) . ' leads'
);

// ─── Notifications API ─────────────────────────────────────────────────────

echo "\n=== Notifications API ===\n";

$res = callApi($notifFile, []);
check('notifications return an array', is_array($res));
if (is_array($res)) {
    check('notifications key present', isset($res['notifications']) && is_array($res['notifications']));
    check('unread_count present and numeric', isset($res['unread_count']) && is_numeric($res['unread_count']));

    if (!empty($res['notifications'][0])) {
        $n = $res['notifications'][0];
        foreach (['id', 'title', 'details', 'time', 'icon', 'link', 'is_read', 'time_ago'] as $field) {
            check("notification carries {$field}", array_key_exists($field, $n), json_encode(array_keys($n)));
        }
    }

    // Every item needs a link the footer can resolve to a URL.
    $badLinks = [];
    foreach (($res['notifications'] ?? []) as $n) {
        if (empty($n['link'])) {
            $badLinks[] = ($n['id'] ?? '?');
        }
    }
    check('every notification has a link', $badLinks === [], 'missing on: ' . implode(', ', $badLinks));

    // time_ago must be a rendered string, not a raw timestamp.
    $badTime = [];
    foreach (($res['notifications'] ?? []) as $n) {
        $t = (string)($n['time_ago'] ?? '');
        if ($t === '' || preg_match('/^\d{4}-\d{2}-\d{2}/', $t)) {
            $badTime[] = ($n['id'] ?? '?') . ':' . $t;
        }
    }
    check('time_ago is human-readable', $badTime === [], implode(', ', $badTime));
}

// The join fix: activity notifications must attribute to a real admin name,
// not the "System" fallback the broken `accounts` join produced.
$res = callApi($notifFile, []);
$systemCount = 0;
$activityCount = 0;
foreach (($res['notifications'] ?? []) as $n) {
    if (($n['title'] ?? '') === 'System Activity') {
        $activityCount++;
        if (str_starts_with((string)($n['details'] ?? ''), 'System:')) {
            $systemCount++;
        }
    }
}
check(
    'activity notifications name the real admin',
    $activityCount === 0 || $systemCount === 0,
    "{$systemCount} of {$activityCount} still attributed to 'System'"
);

// mark-all-read must drive unread_count to zero on the next read.
// This has to happen in ONE child process: the API tracks read state in
// $_SESSION, so two separate callApi() invocations (two processes) would
// start from a fresh session each time and could never observe the change.
$multi = '<?php error_reporting(0); ini_set("display_errors","0");'
    . ' $_SERVER["REQUEST_METHOD"]="GET"; $_SERVER["SCRIPT_NAME"]=' . var_export($notifFile, true) . ';'
    . ' $_SERVER["HTTP_HOST"]="localhost"; $_SERVER["REMOTE_ADDR"]="127.0.0.1";'
    . ' session_start();'
    . ' $_SESSION["admin_id"]=' . (int)$admin['id'] . ';'
    . ' $_SESSION["admin_name"]=' . var_export((string)$admin['name'], true) . ';'
    . ' $_SESSION["admin_role"]=' . var_export((string)$admin['role'], true) . ';'
    . ' $_SESSION["last_activity"]=time();'
    . ' $_GET=[];'
    . ' ob_start(); include ' . var_export($notifFile, true) . ';'
    . ' $before_raw = ob_get_clean();'
    . ' $_GET=["action"=>"read_all"];'
    . ' ob_start(); include ' . var_export($notifFile, true) . ';'
    . ' $after_raw = ob_get_clean();'
    . ' echo json_encode(['
    . '   "before" => json_decode(substr($before_raw, strpos($before_raw, "{")), true),'
    . '   "after"  => json_decode(substr($after_raw,  strpos($after_raw,  "{")), true)'
    . ' ]);';

$tmp = tempnam(sys_get_temp_dir(), 'api_') . '.php';
file_put_contents($tmp, $multi);
$out = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($tmp) . ' 2>&1');
@unlink($tmp);

$start = strpos((string)$out, '{');
$pair = $start === false ? null : json_decode(substr((string)$out, $start), true);

$before = (int)($pair['before']['unread_count'] ?? -1);
$after  = (int)($pair['after']['unread_count'] ?? -1);

check(
    'action=read_all clears the unread count',
    $before > 0 && $after === 0,
    "before={$before} after={$after}"
);

// ─── Unauthorised access ───────────────────────────────────────────────────

echo "\n=== Auth ===\n";

$bootstrap = '<?php error_reporting(0); ini_set("display_errors","0");'
    . ' $_SERVER["REQUEST_METHOD"]="GET"; $_SERVER["SCRIPT_NAME"]=' . var_export($searchFile, true) . ';'
    . ' $_SERVER["REMOTE_ADDR"]="127.0.0.1"; $_SERVER["HTTP_HOST"]="localhost";'
    . ' session_start();'
    . ' ob_start(); include ' . var_export($searchFile, true) . ';'
    . ' $raw = ob_get_clean();'
    . ' $s = strpos($raw, "{"); echo $s === false ? $raw : substr($raw, $s);';

$tmp = tempnam(sys_get_temp_dir(), 'api_') . '.php';
file_put_contents($tmp, $bootstrap);
$out = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($tmp) . ' 2>&1');
@unlink($tmp);

$start = strpos((string)$out, '{');
$decoded = $start === false ? null : json_decode(substr((string)$out, $start), true);
check(
    'unauthenticated search is rejected',
    is_array($decoded) && isset($decoded['error']) && $decoded['error'] === 'Unauthorized',
    json_encode($decoded)
);

// ─── Summary ───────────────────────────────────────────────────────────────

echo "\n" . str_repeat('=', 62) . "\n";
printf("PASSED: %d   FAILED: %d\n", $passes, count($failures));

if ($failures) {
    echo "\nFailures:\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
    exit(1);
}

echo "All header API contract checks passed.\n";
exit(0);
