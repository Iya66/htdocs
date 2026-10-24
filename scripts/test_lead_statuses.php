<?php
/**
 * Lead Status Registry — verification harness.
 *
 * Exercises the registry in app/lead_statuses.php against the acceptance
 * criteria without touching any production data: every test uses the
 * in-memory registry only. Run with:
 *
 *   C:\xampp\php\php.exe scripts\test_lead_statuses.php
 */

require_once __DIR__ . '/../app/lead_statuses.php';

$failures = [];
$passes = 0;

function check(string $label, bool $condition): void
{
    global $failures, $passes;
    if ($condition) {
        $passes++;
        echo "  \u{2713} {$label}\n";
    } else {
        $failures[] = $label;
        echo "  \u{2717} {$label}\n";
    }
}

echo "\n=== 1. Approved stage list ===\n";
$expected = [
    'New Lead', 'Call Not Answered', 'Call Back Later', 'Already Has Website',
    'No Website', 'Website Improvement', 'Interested', 'Follow-up Pending',
    'Meeting / Demo Scheduled', 'Proposal Sent', 'Converted', 'Not Interested',
];
$keys = leadStatusKeys();
check('Exactly 12 stages defined', count($keys) === 12);
check('Stage order matches the specification', array_values(array_column(leadStatuses(), 'label')) === $expected);

$expected_descriptions = [
    'Newly received customer enquiry', "Customer didn't answer",
    'Customer asked you to call later', 'Customer already owns a website',
    'Customer may need a new website', 'Existing website needs improvement',
    'Customer wants to know more', 'Waiting for the next conversation',
    'Meeting arranged with the customer', 'Service details or quotation shared',
    'Customer agreed to proceed', 'Customer declined the offer',
];
$actual_descriptions = array_column(leadStatuses(), 'description');
check('All 12 descriptions match the specification', $actual_descriptions === $expected_descriptions);

echo "\n=== 2. Every stage is complete and unique ===\n";
foreach (leadStatuses() as $key => $meta) {
    $complete = isset($meta['label'], $meta['description'], $meta['badge'], $meta['color'], $meta['group'])
        && is_string($meta['label']) && $meta['label'] !== ''
        && is_string($meta['description']) && $meta['description'] !== ''
        && preg_match('/^#[0-9A-Fa-f]{6}$/', $meta['color']) === 1;
    check("Stage '{$key}' has label, description, badge and hex colour", $complete);
}
check('Stage keys are unique', count($keys) === count(array_unique($keys)));
check('Stage labels are unique', count(array_unique(array_column(leadStatuses(), 'label'))) === 12);
check('Every stage maps to a known pipeline group', count(array_diff(array_column(leadStatuses(), 'group'), array_keys(LEAD_STATUS_GROUPS))) === 0);

echo "\n=== 3. Validation accepts approved stages ===\n";
foreach ($keys as $key) {
    check("isValidLeadStatus('{$key}') === true", isValidLeadStatus($key));
}
check("isValidLeadStatus('new_lead') === true", isValidLeadStatus('new_lead'));

echo "\n=== 4. Validation rejects junk ===\n";
// NB: 'proposal_sent' and 'converted' are deliberately NOT in this list —
// they are approved stage #10 and #11 respectively. 'qualified' and 'closed'
// come from the old database_migration.sql enum and must now be rejected.
foreach (['', 'NEW_LEAD', 'new', 'contacted', 'confirmed', 'lose', 'closed', 'qualified',
          'drop table', '0', 'new lead', 'NewLead', 'proposal', 'sent', null,
          ['new_lead'], 123, true, 0.0] as $bad) {
    $result = isValidLeadStatus($bad);
    check('Rejects ' . var_export($bad, true), $result === false);
}

echo "\n=== 5. Legacy statuses are recognised but not approved ===\n";
$legacy = leadLegacyStatuses();
check('Four legacy values are mapped', count($legacy) === 4);
foreach (['new', 'contacted', 'confirmed', 'lose'] as $legacyKey) {
    check("Legacy '{$legacyKey}' is a storable value", isStoredLeadStatus($legacyKey));
    check("Legacy '{$legacyKey}' is NOT an approved stage", !isValidLeadStatus($legacyKey));
    check("Legacy '{$legacyKey}' has a migration target", isset($legacy[$legacyKey]['suggested']) && isValidLeadStatus($legacy[$legacyKey]['suggested']));
}
check("'converted' (an approved stage) is not also legacy", !isset($legacy['converted']));

echo "\n=== 6. Labels render safely for every value ===\n";
check("leadStatusLabel('new_lead') === 'New Lead'", leadStatusLabel('new_lead') === 'New Lead');
check("leadStatusLabel('new') === 'New' (legacy label, not 'New Lead')", leadStatusLabel('new') === 'New');
check("leadStatusLabel('') falls back to 'New Lead'", leadStatusLabel('') === 'New Lead');
check("leadStatusLabel(null) falls back to 'New Lead'", leadStatusLabel(null) === 'New Lead');
check("leadStatusLabel('garbage') === 'Unknown'", leadStatusLabel('garbage') === 'Unknown');

echo "\n=== 7. Metadata for legacy/unknown never throws ===\n";
foreach (['new', 'contacted', 'confirmed', 'lose', 'garbage', '', null] as $weird) {
    $meta = leadStatusMeta($weird);
    check('leadStatusMeta(' . var_export($weird, true) . ') returns a badge', isset($meta['badge']) && $meta['badge'] !== '');
}
check("leadStatusMeta('new_lead') is flagged current, not legacy", leadStatusMeta('new_lead')['legacy'] === false);
check("leadStatusMeta('new') is flagged legacy", leadStatusMeta('new')['legacy'] === true);

echo "\n=== 8. ENUM SQL matches the registry ===\n";
$enum = leadStatusEnumSql('status');
check('ENUM defaults to new_lead', str_contains($enum, "NOT NULL DEFAULT 'new_lead'"));
check('ENUM contains all 12 approved keys', (function () use ($enum) {
    foreach (leadStatusKeys() as $k) {
        if (!str_contains($enum, "'{$k}'")) {
            return false;
        }
    }
    return true;
})());
check('ENUM retains the 4 legacy keys', (function () use ($enum) {
    foreach (['new', 'contacted', 'confirmed', 'lose'] as $k) {
        if (!str_contains($enum, "'{$k}'")) {
            return false;
        }
    }
    return true;
})());
check('ENUM value count is 16 (12 approved + 4 legacy)', count(leadStatusEnumValues()) === 16);
check('ENUM SQL is safe for MODIFY statements', preg_match('/^ENUM\(\'[a-z_,\']+\'\) NOT NULL DEFAULT \'[a-z_]+\'$/', $enum) === 1);

echo "\n=== 9. Grouping is exhaustive and non-overlapping ===\n";
$grouped = leadStatusesByGroup();
$flat = [];
foreach ($grouped as $g) {
    foreach (array_keys($g['statuses']) as $k) {
        $flat[] = $k;
    }
}
check('Every stage appears in exactly one group', count($flat) === 12 && count(array_unique($flat)) === 12);
check('Grouping covers the same set as the registry', array_diff($keys, $flat) === [] && array_diff($flat, $keys) === []);

echo "\n=== 10. resolveLeadStatus tolerance ===\n";
check("resolveLeadStatus(null) === 'new_lead'", resolveLeadStatus(null) === 'new_lead');
check("resolveLeadStatus('') === 'new_lead'", resolveLeadStatus('') === 'new_lead');
check("resolveLeadStatus('converted') === 'converted'", resolveLeadStatus('converted') === 'converted');
check("resolveLeadStatus('new') preserves legacy value", resolveLeadStatus('new') === 'new');

echo "\n=== 11. Counts degrade safely with no database ===\n";
// leadStatusCounts() requires a PDO handle; call it with a stub that throws
// on any query to prove a DB outage can never blank out the dashboard.
class ThrowingPdo extends PDO
{
    public function __construct()
    {
    }
    public function query($q = null, ...$args): PDOStatement|false
    {
        throw new PDOException('simulated outage');
    }
}
$stub = new ThrowingPdo();
$counts = leadStatusCounts($stub);
check('All 12 stages default to 0 on DB failure', count(array_filter(array_slice($counts, 0, 12), fn($c) => $c !== 0)) === 0);
check('legacy bucket defaults to 0 on DB failure', ($counts['legacy'] ?? -1) === 0);
$groups = leadStatusGroupCounts($stub);
check('Group counts still return every group on DB failure', count($groups) === count(LEAD_STATUS_GROUPS));
check('Group counts sum to 0 on DB failure', array_sum(array_column($groups, 'count')) === 0);

echo "\n" . str_repeat('=', 62) . "\n";
if ($failures === []) {
    echo "ALL {$passes} CHECKS PASSED\n";
    exit(0);
}
echo count($failures) . " FAILURE(S) out of " . (count($failures) + $passes) . " checks:\n";
foreach ($failures as $f) {
    echo "  - {$f}\n";
}
exit(1);
