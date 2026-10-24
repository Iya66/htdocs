<?php
/**
 * Renders the real Leads view and the real dashboard pipeline widget in a
 * simulated authenticated admin session, then asserts on the produced HTML.
 *
 * This exercises the same code path as a browser request (config.php ->
 * admin/leads.php -> app/Views/admin/leads-pro.php) without needing a real
 * login, and without writing anything to the database.
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/lead_statuses.php';

$failures = [];
$passes = 0;

function check(string $label, bool $ok): void
{
    global $failures, $passes;
    if ($ok) {
        $passes++;
        echo "  \u{2713} {$label}\n";
    } else {
        $failures[] = $label;
        echo "  \u{2717} {$label}\n";
    }
}

function countOccurrences(string $haystack, string $needle): int
{
    $n = 0;
    $offset = 0;
    while (($pos = strpos($haystack, $needle, $offset)) !== false) {
        $n++;
        $offset = $pos + 1;
    }
    return $n;
}

// Simulate an authenticated admin session so the view renders fully.
$_SESSION['admin_id'] = 1;
$_SESSION['admin_name'] = 'Test Admin';
$_SESSION['admin_role'] = 'admin';
$_SESSION['last_activity'] = time();

$leads = $conn->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCSRFToken();
$lead_status_options = leadStatuses();
$lead_legacy_options = leadLegacyStatuses();
$lead_status_counts = leadStatusCounts($conn);
$lead_group_counts = leadStatusGroupCounts($conn);
$has_legacy = false;
foreach ($leads as $l) {
    if (!isValidLeadStatus($l['status'] ?? null)) {
        $has_legacy = true;
        break;
    }
}

echo "\n=== Leads view rendering ===\n";
ob_start();
include __DIR__ . '/../app/Views/admin/leads-pro.php';
$html = ob_get_clean();

check('View rendered without error', $html !== '');
check('No PHP fatal error in output', !str_contains($html, 'Fatal error'));
check('No PHP warning in output', !str_contains($html, 'Warning:'));
check('No undefined-variable notice', !str_contains($html, 'Undefined variable'));
check('No undefined-index notice', !str_contains($html, 'Undefined index'));

echo "\n--- Modal status dropdown ---\n";
// Isolate the modal's Pipeline Status <select> by its full opening tag. The
// id string also appears in the hint div's aria-describedby, so anchor on the
// complete tag rather than the bare id.
$select_html = '';
$select_start = strpos($html, '<select name="status" id="leadStatus"');
if ($select_start === false) {
    check('Modal status select found', false);
} else {
    $select_close = strpos($html, '</select>', $select_start);
    $select_html = substr($html, $select_start, $select_close - $select_start);

    // The select holds 12 approved stages in 5 phase optgroups, plus a
    // separate "Legacy" optgroup so retired values stay re-assignable.
    $legacy_pos = strpos($select_html, '<optgroup label="Legacy (retired stages)">');
    check('Legacy optgroup is present in the modal select', $legacy_pos !== false);
    $approved_html = $legacy_pos !== false ? substr($select_html, 0, $legacy_pos) : $select_html;
    $legacy_html = $legacy_pos !== false ? substr($select_html, $legacy_pos) : '';

    $option_count = countOccurrences($approved_html, '<option');
    check("Modal dropdown offers exactly 12 approved options (found {$option_count})", $option_count === 12);
    check("Modal groups the 12 stages into 5 pipeline phases (found " . countOccurrences($approved_html, '<optgroup') . ")", countOccurrences($approved_html, '<optgroup') === 5);
    check("Legacy optgroup holds the 4 retired values (found " . countOccurrences($legacy_html, '<option') . ")", countOccurrences($legacy_html, '<option') === 4);

    foreach (leadStatuses() as $key => $meta) {
        check("Option present with value=\"{$key}\"", str_contains($approved_html, "value=\"{$key}\""));
        check("Option '{$meta['label']}' carries its description", str_contains($approved_html, 'data-description="' . htmlspecialchars($meta['description'], ENT_QUOTES, 'UTF-8') . '"'));
    }
    foreach (array_keys(leadLegacyStatuses()) as $legacy_key) {
        check("Legacy option '{$legacy_key}' is re-assignable", str_contains($legacy_html, "value=\"{$legacy_key}\""));
    }
}
check('Description helper element rendered', str_contains($html, 'id="leadStatusDescription"'));
check('Helper wired via aria-describedby', str_contains($html, 'aria-describedby="leadStatusHint"'));

echo "\n--- Optgroups by pipeline phase ---\n";
foreach (array_keys(LEAD_STATUS_GROUPS) as $g) {
    check("Optgroup for phase '{$g}' present", str_contains($select_html, "<optgroup label=\"" . LEAD_STATUS_GROUPS[$g]['label'] . "\">"));
}

echo "\n--- Bulk action dropdown ---\n";
$bulk_count = countOccurrences($html, 'Change Status To');
check('Bulk status control rendered', $bulk_count >= 1);

echo "\n--- Kanban board ---\n";
// Count only the twelve stage columns: the extra "legacy" column is rendered
// conditionally when unmigrated rows exist, and is deliberately not counted.
$kanban_all = countOccurrences($html, 'class="kanban-column"');
$legacy_cols = countOccurrences($html, 'data-status="legacy"');
$kanban_columns = $kanban_all - $legacy_cols;
check("Kanban renders 12 stage columns (found {$kanban_columns})", $kanban_columns === 12);
check("Legacy safety column appears only when needed ({$legacy_cols} rendered)", $legacy_cols === ($has_legacy ? 1 : 0));
foreach (leadStatuses() as $key => $meta) {
    check("Kanban column '{$key}' present", str_contains($html, "id=\"cards-{$key}\""));
    check("Kanban count badge for '{$key}' present", str_contains($html, "id=\"count-{$key}\""));
    check("Kanban column '{$key}' uses its colour " . $meta['color'], str_contains($html, 'background: ' . $meta['color']));
    check("Kanban drop target '{$key}' wired", str_contains($html, "dropLead(event, '{$key}')"));
}
check('Kanban drop uses the approved stage key, not a label', !str_contains($html, "dropLead(event, 'New Lead')"));

echo "\n--- JavaScript payload ---\n";
check('LEAD_STAGES constant injected', str_contains($html, 'const LEAD_STAGES = {'));
check('LEAD_STAGES holds 12 entries', countOccurrences($html, '"label":') >= 12);
check('Status hint updater wired to change event', str_contains($html, "getElementById('leadStatus').addEventListener('change', updateStatusHint)"));
check('Drop handler guards against invalid stages', str_contains($html, 'hasOwnProperty.call(LEAD_STAGES'));
check('Failed drops roll back the optimistic update', str_contains($html, 'previousStatus'));

echo "\n--- Responsive CSS ---\n";
check('Mobile single-column breakpoint present', str_contains($html, '@media (max-width: 640px)'));
check('Tablet two-column breakpoint present', str_contains($html, 'and (max-width: 1023px)'));
check('Desktop three-column breakpoint present', str_contains($html, '@media (min-width: 1024px)'));
check('Kanban scroll-snap enabled', str_contains($html, 'scroll-snap-type: x proximity'));

echo "\n--- Table badge rendering ---\n";
// Count only the table's status badges: they are the badge-sm spans that also
// carry a data-status attribute. Kanban column headers use badge-sm for their
// per-column counts, and Kanban wrappers use data-status on a div.
$badge_count = preg_match_all('/<span class="badge-sm [^"]*"\s*\n?\s*title="[^"]*"\s*\n?\s*data-status="/', $html);
check('Every lead row renders a status badge', $badge_count === count($leads));
foreach ($leads as $lead) {
    $key = (string)$lead['status'];
    $meta = leadStatusMeta($key);
    $expected_label = htmlspecialchars(leadStatusLabel($key), ENT_QUOTES, 'UTF-8');
    // The span wraps the label in whitespace/newlines, so match the label
    // between the attribute list and the closing tag.
    check("Lead #{$lead['id']} shows '{$expected_label}'",
        (bool)preg_match('/data-status="' . preg_quote($key, '/') . '"[^>]*>\s*' . preg_quote($expected_label, '/') . '\s*<\/span>/', $html));
    check("Lead #{$lead['id']} badge class is {$meta['badge']}",
        (bool)preg_match('/<span class="badge-sm ' . preg_quote($meta['badge'], '/') . '"/', $html));
    check("Lead #{$lead['id']} tooltip describes the stage",
        str_contains($html, 'title="' . htmlspecialchars($meta['description'], ENT_QUOTES, 'UTF-8') . '"'));
    if ($meta['legacy']) {
        check("Lead #{$lead['id']} is flagged legacy in the table", str_contains($html, 'legacy'));
    }
}

echo "\n=== Dashboard pipeline widget ===\n";
ob_start();
$icon_path = '/files/lcons/';
$base_path = '';
$leads_count = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$services_count = 0; $reviews_count = 0; $why_us_count = 0;
$recent_leads = []; $recent_activity = []; $active_projects = [];
$today_activity = 0; $new_leads = 0; $projects_count = 0; $uploads_count = 0; $staff_count = 0;
$lead_status_counts = leadStatusCounts($conn);
$lead_group_counts = leadStatusGroupCounts($conn);
$dash_cards = [];
// The velocity chart needs its own series; the real dashboard.php builds these.
$chart_dates = [];
$chart_counts = [];
include __DIR__ . '/../app/Views/admin/dashboard-pro.php';
$dash = ob_get_clean();

check('Dashboard rendered without error', $dash !== '');
check('No fatal error in dashboard', !str_contains($dash, 'Fatal error'));
check('No undefined-variable notice in dashboard', !str_contains($dash, 'Undefined variable'));
$pipe_rows = countOccurrences($dash, 'class="dash-pipe-row"');
$expected_rows = count($lead_group_counts);
check("Pipeline widget renders one row per group (found {$pipe_rows}, expected {$expected_rows})", $pipe_rows === $expected_rows);
check('Pipeline links to the Leads CRM', str_contains($dash, 'Manage all 12 pipeline stages'));
check('Pipeline no longer references the old $p_new variables', !str_contains($dash, '$p_new'));

echo "\n" . str_repeat('=', 62) . "\n";
if ($failures === []) {
    echo "ALL {$passes} RENDER CHECKS PASSED\n";
    exit(0);
}
echo count($failures) . " FAILURE(S) out of " . (count($failures) + $passes) . ":\n";
foreach ($failures as $f) {
    echo "  - {$f}\n";
}
exit(1);
