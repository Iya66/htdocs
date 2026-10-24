<?php
/**
 * Lead Status Registry
 * -----------------------------------------------------------------------------
 * Single source of truth for the lead pipeline stages used across the
 * Leads CRM (admin/leads.php), the admin dashboard pipeline widget, the
 * Kanban board, bulk actions and the global admin search API.
 *
 * Every consumer MUST read this file instead of hard-coding status strings,
 * so that validation, rendering, badges and filters can never drift apart.
 *
 * Status keys are stable identifiers — never rename them once leads exist,
 * because they are persisted in the `leads.status` column.
 */

if (!defined('LEAD_STATUS_REGISTRY_LOADED')) {
    define('LEAD_STATUS_REGISTRY_LOADED', true);
}

/**
 * Pipeline grouping used by the dashboard "Lead Pipeline" widget so that
 * twelve stages stay readable at a glance.
 */
const LEAD_STATUS_GROUPS = [
    'engagement'    => ['label' => 'Engagement',    'color' => '#3B82F6'],
    'qualification' => ['label' => 'Qualification', 'color' => '#8B5CF6'],
    'nurturing'     => ['label' => 'Nurturing',     'color' => '#F59E0B'],
    'closing'       => ['label' => 'Closing',       'color' => '#06B6D4'],
    'outcome'       => ['label' => 'Outcome',       'color' => '#10B981'],
];

/**
 * The twelve approved lead stages, in pipeline order.
 *
 * label       : human readable name shown in dropdowns, badges and listings
 * description : helper text shown under the dropdown and in tooltips
 * badge       : CSS badge modifier (see admin/css/theme-professional.css)
 * color       : dot / accent colour used by the Kanban board and dashboard
 * icon        : FontAwesome icon (without the leading "fas fa-")
 * group       : pipeline phase key from LEAD_STATUS_GROUPS
 */
function leadStatuses(): array
{
    static $statuses = null;

    if ($statuses === null) {
        $statuses = [
            // ── Engagement ──────────────────────────────────────────────
            'new_lead' => [
                'label'       => 'New Lead',
                'description' => 'Newly received customer enquiry',
                'badge'       => 'badge-info',
                'color'       => '#3B82F6',
                'icon'        => 'fa-inbox',
                'group'       => 'engagement',
            ],
            'call_not_answered' => [
                'label'       => 'Call Not Answered',
                'description' => "Customer didn't answer",
                'badge'       => 'badge-slate',
                'color'       => '#64748B',
                'icon'        => 'fa-phone-slash',
                'group'       => 'engagement',
            ],
            'call_back_later' => [
                'label'       => 'Call Back Later',
                'description' => 'Customer asked you to call later',
                'badge'       => 'badge-amber',
                'color'       => '#F59E0B',
                'icon'        => 'fa-clock',
                'group'       => 'engagement',
            ],

            // ── Qualification ───────────────────────────────────────────
            'already_has_website' => [
                'label'       => 'Already Has Website',
                'description' => 'Customer already owns a website',
                'badge'       => 'badge-cyan',
                'color'       => '#06B6D4',
                'icon'        => 'fa-globe',
                'group'       => 'qualification',
            ],
            'no_website' => [
                'label'       => 'No Website',
                'description' => 'Customer may need a new website',
                'badge'       => 'badge-teal',
                'color'       => '#14B8A6',
                'icon'        => 'fa-ban',
                'group'       => 'qualification',
            ],
            'website_improvement' => [
                'label'       => 'Website Improvement',
                'description' => 'Existing website needs improvement',
                'badge'       => 'badge-indigo',
                'color'       => '#6366F1',
                'icon'        => 'fa-wand-magic-sparkles',
                'group'       => 'qualification',
            ],

            // ── Nurturing ───────────────────────────────────────────────
            'interested' => [
                'label'       => 'Interested',
                'description' => 'Customer wants to know more',
                'badge'       => 'badge-violet',
                'color'       => '#8B5CF6',
                'icon'        => 'fa-thumbs-up',
                'group'       => 'nurturing',
            ],
            'followup_pending' => [
                'label'       => 'Follow-up Pending',
                'description' => 'Waiting for the next conversation',
                'badge'       => 'badge-orange',
                'color'       => '#F97316',
                'icon'        => 'fa-hourglass-half',
                'group'       => 'nurturing',
            ],

            // ── Closing ─────────────────────────────────────────────────
            'meeting_scheduled' => [
                'label'       => 'Meeting / Demo Scheduled',
                'description' => 'Meeting arranged with the customer',
                'badge'       => 'badge-sky',
                'color'       => '#0EA5E9',
                'icon'        => 'fa-calendar-check',
                'group'       => 'closing',
            ],
            'proposal_sent' => [
                'label'       => 'Proposal Sent',
                'description' => 'Service details or quotation shared',
                'badge'       => 'badge-fuchsia',
                'color'       => '#D946EF',
                'icon'        => 'fa-file-invoice',
                'group'       => 'closing',
            ],

            // ── Outcome ─────────────────────────────────────────────────
            'converted' => [
                'label'       => 'Converted',
                'description' => 'Customer agreed to proceed',
                'badge'       => 'badge-success',
                'color'       => '#10B981',
                'icon'        => 'fa-circle-check',
                'group'       => 'outcome',
            ],
            'not_interested' => [
                'label'       => 'Not Interested',
                'description' => 'Customer declined the offer',
                'badge'       => 'badge-danger',
                'color'       => '#EF4444',
                'icon'        => 'fa-circle-xmark',
                'group'       => 'outcome',
            ],
        ];
    }

    return $statuses;
}

/**
 * Ordered list of approved status keys.
 *
 * @return string[]
 */
function leadStatusKeys(): array
{
    return array_keys(leadStatuses());
}

/**
 * Full metadata for one status, with safe fallbacks for values that are
 * neither an approved stage nor a known legacy value.
 *
 * This is the single authority for a status's label, badge and colour:
 * views should never re-derive them, so a legacy lead and a current lead
 * can never disagree about how they are rendered.
 */
function leadStatusMeta(?string $key): array
{
    $key = (string)$key;

    // Approved stage
    if (isset(leadStatuses()[$key])) {
        return array_merge(leadStatuses()[$key], ['key' => $key, 'legacy' => false]);
    }

    // Known legacy value — keep its historical label and colour so old rows
    // still read correctly until an admin migrates them forward.
    $legacy = leadLegacyStatuses();
    if (isset($legacy[$key])) {
        return array_merge($legacy[$key], ['key' => $key, 'legacy' => true]);
    }

    // Anything else (null, empty, or a value from an even older schema).
    return [
        'key'         => $key,
        'label'       => 'Unknown',
        'description' => 'Unrecognised status — please re-assign to a current stage',
        'badge'       => 'badge-neutral',
        'color'       => '#94A3B8',
        'icon'        => 'fa-circle-question',
        'group'       => 'outcome',
        'legacy'      => true,
    ];
}

/**
 * Display label for a status key (falls back to a readable legacy label).
 */
function leadStatusLabel(?string $key): string
{
    $key = (string)$key;

    if ($key === '') {
        return leadStatusMeta('new_lead')['label'];
    }

    $legacy = leadLegacyStatusLabels();
    if (isset($legacy[$key])) {
        return $legacy[$key];
    }

    return leadStatusMeta($key)['label'];
}

/**
 * True only for the twelve approved stages — used for backend validation.
 */
function isValidLeadStatus($key): bool
{
    return is_string($key) && array_key_exists($key, leadStatuses());
}

/**
 * Legacy statuses that predate the twelve-stage workflow.
 *
 * They remain valid database values so that historical leads are never
 * corrupted or silently re-labelled, and they remain selectable in the
 * dropdown (inside a "Legacy" optgroup) so an admin can migrate them
 * forward. Once a legacy value is no longer present in any row it simply
 * never appears in the UI.
 */
function leadLegacyStatuses(): array
{
    return [
        'new' => [
            'label'       => 'New',
            'description' => 'Legacy stage — replaced by "New Lead"',
            'badge'       => 'badge-info',
            'color'       => '#3B82F6',
            'icon'        => 'fa-inbox',
            'group'       => 'engagement',
            'suggested'   => 'new_lead',
        ],
        'contacted' => [
            'label'       => 'Contacted',
            'description' => 'Legacy stage — replaced by "Interested"',
            'badge'       => 'badge-warning',
            'color'       => '#F59E0B',
            'icon'        => 'fa-comments',
            'group'       => 'engagement',
            'suggested'   => 'interested',
        ],
        'confirmed' => [
            'label'       => 'Confirmed',
            'description' => 'Legacy stage — replaced by "Converted"',
            'badge'       => 'badge-success',
            'color'       => '#10B981',
            'icon'        => 'fa-check',
            'group'       => 'outcome',
            'suggested'   => 'converted',
        ],
        'lose' => [
            'label'       => 'Lost',
            'description' => 'Legacy stage — replaced by "Not Interested"',
            'badge'       => 'badge-danger',
            'color'       => '#EF4444',
            'icon'        => 'fa-xmark',
            'group'       => 'outcome',
            'suggested'   => 'not_interested',
        ],
    ];
}

/**
 * @return array<string, string>
 */
function leadLegacyStatusLabels(): array
{
    $labels = [];
    foreach (leadLegacyStatuses() as $key => $meta) {
        $labels[$key] = $meta['label'];
    }
    return $labels;
}

/**
 * True for a value that may be stored: an approved stage or a legacy value.
 */
function isStoredLeadStatus($key): bool
{
    return isValidLeadStatus($key) || array_key_exists((string)$key, leadLegacyStatuses());
}

/**
 * The complete ENUM definition for the `leads.status` column:
 * the twelve approved stages plus the legacy values kept for history.
 *
 * @return string[]
 */
function leadStatusEnumValues(): array
{
    return array_merge(leadStatusKeys(), array_keys(leadLegacyStatuses()));
}

/**
 * SQL fragment for ALTER TABLE ... MODIFY `status`.
 */
function leadStatusEnumSql(string $column = 'status'): string
{
    $list = "'" . implode("','", array_map('addslashes', leadStatusEnumValues())) . "'";
    return "ENUM($list) NOT NULL DEFAULT 'new_lead'";
}

/**
 * Statuses grouped by pipeline phase, for the dashboard widget.
 *
 * @return array<string, array{label:string,color:string,statuses:array}>
 */
function leadStatusesByGroup(): array
{
    $grouped = [];
    foreach (LEAD_STATUS_GROUPS as $groupKey => $groupMeta) {
        $grouped[$groupKey] = [
            'label'    => $groupMeta['label'],
            'color'    => $groupMeta['color'],
            'statuses' => [],
        ];
    }

    foreach (leadStatuses() as $key => $meta) {
        $group = $meta['group'] ?? 'engagement';
        if (!isset($grouped[$group])) {
            $group = 'engagement';
        }
        $grouped[$group]['statuses'][$key] = $meta;
    }

    return $grouped;
}

/**
 * Counts per status key for every approved stage, plus a "legacy" bucket.
 * A single GROUP BY keeps this to one query.
 */
function leadStatusCounts(PDO $conn): array
{
    $counts = array_fill_keys(leadStatusKeys(), 0);
    $counts['legacy'] = 0;

    try {
        $rows = $conn->query("SELECT status, COUNT(*) AS total FROM leads GROUP BY status")
                     ->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return $counts;
    }

    foreach ($rows as $row) {
        $key = (string)($row['status'] ?? '');
        $total = (int)($row['total'] ?? 0);
        if (isset($counts[$key])) {
            $counts[$key] += $total;
        } else {
            $counts['legacy'] += $total;
        }
    }

    return $counts;
}

/**
 * Per-group counts for the dashboard pipeline bars.
 */
function leadStatusGroupCounts(PDO $conn): array
{
    $counts = leadStatusCounts($conn);
    $groups = [];

    foreach (leadStatusesByGroup() as $groupKey => $groupMeta) {
        $total = 0;
        foreach (array_keys($groupMeta['statuses']) as $statusKey) {
            $total += $counts[$statusKey] ?? 0;
        }
        $groups[$groupKey] = [
            'label' => $groupMeta['label'],
            'color' => $groupMeta['color'],
            'count' => $total,
        ];
    }

    // Surface anything that has not been migrated yet so the numbers add up.
    $legacy = $counts['legacy'] ?? 0;
    if ($legacy > 0) {
        $groups['legacy'] = [
            'label' => 'Legacy (needs review)',
            'color' => '#94A3B8',
            'count' => $legacy,
        ];
    }

    return $groups;
}

/**
 * Resolve the current stage for a lead row, tolerating null/legacy values.
 */
function resolveLeadStatus($value): string
{
    $value = (string)$value;
    return $value !== '' ? $value : 'new_lead';
}

/**
 * Count overdue and upcoming follow-ups for leads.
 * Overdue: follow_up_date < NOW() and active stage (not converted, not_interested, lose).
 * Upcoming: follow_up_date >= NOW() and active stage.
 *
 * @return array{overdue: int, upcoming: int}
 */
function leadFollowupCounts(PDO $conn): array
{
    $res = ['overdue' => 0, 'upcoming' => 0];
    try {
        $stmt = $conn->query("
            SELECT 
                SUM(CASE WHEN follow_up_date IS NOT NULL AND follow_up_date < NOW() AND status NOT IN ('converted', 'not_interested', 'lose') THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN follow_up_date IS NOT NULL AND follow_up_date >= NOW() AND status NOT IN ('converted', 'not_interested', 'lose') THEN 1 ELSE 0 END) as upcoming_count
            FROM leads
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $res['overdue'] = (int)($row['overdue_count'] ?? 0);
            $res['upcoming'] = (int)($row['upcoming_count'] ?? 0);
        }
    } catch (Throwable $e) {
        // Safe fallback
    }
    return $res;
}

