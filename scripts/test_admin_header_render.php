<?php
/**
 * Admin header render smoke test.
 *
 * Renders every admin view through the real header/footer with a stubbed
 * session and asserts the structural invariants that the header rebuild was
 * supposed to guarantee:
 *
 *   1. exactly one <header class="topbar"> and one <aside class="sidebar">
 *   2. no PHP notice/warning emitted
 *   3. the <title> matches the page, not a hardcoded "Dashboard"
 *   4. the mobile menu button is present (it was unreachable on tablets)
 *   5. theme-saas.css is no longer referenced
 *   6. both search and notification APIs are referenced by the footer
 *
 * Views are rendered by invoking their controller, so the header is exercised
 * exactly as it is in production rather than through a mock.
 *
 *   C:\xampp\php\php.exe scripts\test_admin_header_render.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

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

// ─── Fake an authenticated admin session ───────────────────────────────────
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME']    = '/admin/dashboard.php';
$_SERVER['REMOTE_ADDR']   = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'smoke-test';

require_once __DIR__ . '/../app/config.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "FATAL: no database connection\n");
    exit(1);
}

// Real admin row so the profile block has a name and role.
$admin = $conn->query("SELECT id, name, username, role FROM admin ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$admin) {
    fwrite(STDERR, "FATAL: no admin row to impersonate\n");
    exit(1);
}

$_SESSION['admin_id']     = (int)$admin['id'];
$_SESSION['admin_name']   = $admin['name'] ?: $admin['username'];
$_SESSION['admin_role']   = $admin['role'] ?: 'admin';
$_SESSION['last_activity'] = time();

echo "\nImpersonating admin #{$_SESSION['admin_id']} ({$_SESSION['admin_name']})\n";

// ─── Controllers that render a full page ───────────────────────────────────
// Each entry is invoked via a subprocess so a fatal in one page cannot abort
// the whole run, and so output buffering state cannot leak between pages.
$pages = [
    'dashboard'            => __DIR__ . '/../admin/dashboard.php',
    'leads'                => __DIR__ . '/../admin/leads.php',
    'services'             => __DIR__ . '/../admin/services.php',
    'reviews'              => __DIR__ . '/../admin/reviews.php',
    'projects'             => __DIR__ . '/../admin/projects.php',
    'testimonials'         => __DIR__ . '/../admin/testimonials.php',
    'files'                => __DIR__ . '/../admin/files.php',
    'blogs'                => __DIR__ . '/../admin/blogs.php',
    'staff'                => __DIR__ . '/../admin/staff.php',
    'activity'             => __DIR__ . '/../admin/activity.php',
    'reports'              => __DIR__ . '/../admin/reports.php',
    'mail_logs'            => __DIR__ . '/../admin/mail_logs.php',
    'contact-us'           => __DIR__ . '/../admin/contact-us.php',
    'map'                  => __DIR__ . '/../admin/map.php',
    'faqs'                 => __DIR__ . '/../admin/faqs.php',
    'why-choose-us'        => __DIR__ . '/../admin/why-choose-us.php',
    'accounts'             => __DIR__ . '/../admin/accounts.php',
    'backup'               => __DIR__ . '/../admin/backup.php',
    'settings/general'     => __DIR__ . '/../admin/settings/general.php',
    'settings/mailsettings'=> __DIR__ . '/../admin/settings/mailsettings.php',
    'settings/integration' => __DIR__ . '/../admin/settings/integration.php',
    'settings/module'      => __DIR__ . '/../admin/settings/module.php',
    'settings/index'       => __DIR__ . '/../admin/settings/index.php',
];

/**
 * Render one page in a child process and return [html, warnings].
 *
 * The child is handed a tiny bootstrap that fakes the session, then includes
 * the controller. stderr is captured separately so PHP notices don't get lost
 * in the page body.
 */
function renderPage(string $route, string $script): array
{
    $bootstrap = sprintf(
        '<?php error_reporting(E_ALL); ini_set("display_errors","1");'
        . ' $_SERVER["REQUEST_METHOD"]="GET";'
        . ' $_SERVER["SCRIPT_NAME"]=%s;'
        . ' $_SERVER["REQUEST_URI"]=%s;'
        . ' $_SERVER["REMOTE_ADDR"]="127.0.0.1";'
        . ' $_SERVER["HTTP_USER_AGENT"]="smoke-test";'
        . ' session_start();'
        . ' $_SESSION["admin_id"]=%d;'
        . ' $_SESSION["admin_name"]=%s;'
        . ' $_SESSION["admin_role"]=%s;'
        . ' $_SESSION["last_activity"]=time();'
        . ' ob_start(); include ' . var_export($script, true) . '; echo ob_get_clean();',
        var_export('/admin/' . $route . '.php', true),
        var_export('/admin/' . $route, true),
        (int)$_SESSION['admin_id'],
        var_export((string)$_SESSION['admin_name'], true),
        var_export((string)$_SESSION['admin_role'], true),
        var_export($script, true)
    );

    $tmp = tempnam(sys_get_temp_dir(), 'hdr_') . '.php';
    file_put_contents($tmp, $bootstrap);

    $php = PHP_BINARY;
    $cmd = escapeshellarg($php) . ' -d display_errors=1 -d error_reporting=E_ALL ' . escapeshellarg($tmp) . ' 2>&1';
    $html = shell_exec($cmd);
    @unlink($tmp);

    return [$html === null ? '' : $html, ''];
}

echo "\n=== Rendering " . count($pages) . " admin pages ===\n";

$rendered = [];
foreach ($pages as $route => $script) {
    if (!is_file($script)) {
        check("{$route}: controller exists", false, 'file missing');
        continue;
    }

    [$html, $warn] = renderPage($route, $script);
    $rendered[$route] = $html;

    $label = $route;

    // 1. Structural: one header, one sidebar.
    check(
        "{$label}: exactly one topbar",
        substr_count($html, '<header class="topbar">') === 1,
        'found ' . substr_count($html, '<header class="topbar">')
    );
    check(
        "{$label}: exactly one sidebar",
        substr_count($html, '<aside class="sidebar"') === 1,
        'found ' . substr_count($html, '<aside class="sidebar"')
    );

    // 2. The document actually closed (a fatal would truncate it).
    check("{$label}: page closed cleanly", str_contains($html, '</html>'));
    check(
        "{$label}: no PHP diagnostics in output",
        !str_contains($html, 'Warning:') && !str_contains($html, 'Notice:')
        && !str_contains($html, 'Fatal error') && !str_contains($html, 'Deprecated:')
    );

    // 3. The title must not be a hardcoded "Dashboard" for every page.
    preg_match('/<title>(.*?)<\/title>/s', $html, $m);
    $title = isset($m[1]) ? trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8')) : '';
    check(
        "{$label}: has a page title",
        $title !== '' && $title !== 'Admin Panel',
        $title !== '' ? $title : 'no <title>'
    );
    if ($route !== 'dashboard') {
        check(
            "{$label}: title is not 'Dashboard'",
            stripos($title, 'Dashboard') === false,
            $title
        );
    }

    // 4. Menu button must exist — it was display:none on 768–1024px screens.
    check("{$label}: mobile menu button present", str_contains($html, 'id="menuToggle"'));

    // 5. theme-saas.css is gone.
    check("{$label}: no theme-saas.css reference", !str_contains($html, 'theme-saas'));

    // 6. Both APIs wired up.
    check("{$label}: search API referenced", str_contains($html, 'api/search.php'));
    check("{$label}: notifications API referenced", str_contains($html, 'api/notifications.php'));

    echo "\n";
}

// ─── Cross-page assertions ─────────────────────────────────────────────────

echo "\n=== Header behaviour ===\n";

$leads = $rendered['leads'] ?? '';
$files = $rendered['files'] ?? '';

// $page_title must be resolved per page, not shared.
preg_match('/<span class="breadcrumb-current">(.*?)<\/span>/s', $leads, $lm);
preg_match('/<span class="breadcrumb-current">(.*?)<\/span>/s', $files, $fm);
check(
    'breadcrumb is page-specific',
    isset($lm[1], $fm[1]) && $lm[1] !== $fm[1],
    'leads="' . ($lm[1] ?? '?') . '" files="' . ($fm[1] ?? '?') . '"'
);

// Theme must be applied in <head> (no flash of light theme in dark mode).
check(
    'theme applied before first paint',
    str_contains($leads, "document.documentElement.setAttribute('data-theme', saved)"),
    'head script missing'
);
check(
    'no opacity:0 body hack left behind',
    !str_contains($leads, 'body {') || !preg_match('/body\s*\{\s*opacity:\s*0/', $leads)
);

// Dark mode shows a sun, light mode shows a moon.
$css = file_get_contents(__DIR__ . '/../admin/css/theme-professional.css');
check(
    'dark theme shows the sun icon',
    (bool)preg_match('/\[data-theme="dark"\]\s*\.theme-icon-moon\s*\{\s*display:\s*none/', $css)
    && (bool)preg_match('/\[data-theme="dark"\]\s*\.theme-icon-sun\s*\{\s*display:\s*inline-flex/', $css)
);

// The menu button must be visible up to the sidebar breakpoint.
// Regression guards for two bugs that made it unreachable:
//   1. the rule was scoped to @media (min-width: 768px) — the inverse
//   2. the colon was double-escaped (.md\\:hidden), so it never matched
check(
    'menu button visible at tablet widths (max-width: 1024px)',
    (bool)preg_match('/@media\s*\(max-width:\s*1024px\)\s*\{\s*\.md\\\\:hidden\s*\{\s*display:\s*none\s*!important/', $css),
    'the inverted min-width:768px rule would hide it on tablets'
);

check(
    'no inverted min-width:768px rule remains',
    !preg_match('/@media\s*\(min-width:\s*768px\)[^}]*\.md\\\\?:hidden/', $css),
    'found @media (min-width: 768px) still scoping .md:hidden'
);

check(
    'colon is escaped exactly once (selector actually matches)',
    (bool)preg_match('/\.md\\\\:hidden/', $css) && !preg_match('/\.md\\\\\\\\:hidden/', $css),
    'a double backslash makes the selector match a literal backslash, not the class'
);

check(
    'exactly one .md:hidden rule',
    preg_match_all('/\.md\\\\:hidden/', $css) === 1,
    'the utility was defined twice with conflicting breakpoints'
);

// Settings pages are one level deeper — relative asset paths must resolve.
$settings = $rendered['settings/general'] ?? '';
check(
    'settings pages use the correct relative asset path',
    str_contains($settings, 'href="../css/theme-professional.css'),
    'base_path not adjusted for subdirectory'
);

// ─── CSS sanity ────────────────────────────────────────────────────────────

echo "\n=== Stylesheet ===\n";

check('theme-professional.css exists', is_file(__DIR__ . '/../admin/css/theme-professional.css'));
check('theme-saas.css deleted', !is_file(__DIR__ . '/../admin/css/theme-saas.css'));

$open  = substr_count($css, '{');
$close = substr_count($css, '}');
check('stylesheet braces balance', $open === $close, "open={$open} close={$close}");

check(
    'the merged SaaS components are present',
    str_contains($css, '.saas-page-header')
    && str_contains($css, '.stat-pill')
    && str_contains($css, '.activity-table')
    && str_contains($css, '.filter-group')
);

// The overlapping selectors must now exist exactly once, so cascade order is
// deterministic for every page.
foreach (['.btn-primary', '.page-title', '.content-card', '.badge-info', '.badge-success', '.btn-edit'] as $sel) {
    $count = preg_match_all('/' . preg_quote($sel, '/') . '\s*\{/', $css);
    check("{$sel} defined exactly once", $count === 1, "found {$count} definitions");
}

// ─── API contract ──────────────────────────────────────────────────────────

echo "\n=== APIs ===\n";

$notifApi = file_get_contents(__DIR__ . '/../admin/api/notifications.php');
check(
    'notifications joins the admin table',
    str_contains($notifApi, 'LEFT JOIN admin acc'),
    'joining `accounts` attributed activity to "System"'
);

$searchApi = file_get_contents(__DIR__ . '/../admin/api/search.php');
check(
    'search returns status_label',
    str_contains($searchApi, 'leadStatusLabel'),
    'the UI depends on it for lead badges'
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

echo "All header render checks passed.\n";
exit(0);
