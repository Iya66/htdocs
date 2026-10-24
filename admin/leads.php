<?php
/**
 * Admin Leads Management
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';
require_once __DIR__ . '/../app/lead_statuses.php';

$message = '';
$message_type = '';
$userRole = $_SESSION['admin_role'] ?? 'viewer';
$canEdit = !in_array($userRole, ['viewer'], true);

/**
 * Persist a validated status for one lead.
 *
 * @throws InvalidArgumentException when the status is not in the approved list
 * @throws RuntimeException when the lead record does not exist
 */
function saveLeadStatus(PDO $conn, int $id, $status, array $extra = []): array
{
    if (!isStoredLeadStatus($status)) {
        throw new InvalidArgumentException('Invalid lead status option: ' . htmlspecialchars((string)$status));
    }

    $checkStmt = $conn->prepare("SELECT * FROM leads WHERE id = ?");
    $checkStmt->execute([$id]);
    $lead = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$lead) {
        throw new RuntimeException("Lead #{$id} not found.");
    }

    $previousStatus = $lead['status'];

    $updates = ["status = ?", "updated_at = NOW()"];
    $params = [$status];

    if (array_key_exists('follow_up_date', $extra)) {
        $val = !empty($extra['follow_up_date']) ? date('Y-m-d H:i:s', strtotime($extra['follow_up_date'])) : null;
        $updates[] = "follow_up_date = ?";
        $params[] = $val;
    }
    if (array_key_exists('stage_notes', $extra)) {
        $updates[] = "stage_notes = ?";
        $params[] = !empty($extra['stage_notes']) ? trim($extra['stage_notes']) : null;
    }
    if (array_key_exists('website', $extra)) {
        $updates[] = "website = ?";
        $params[] = !empty($extra['website']) ? trim($extra['website']) : null;
    }
    if (array_key_exists('proposal_reference', $extra)) {
        $updates[] = "proposal_reference = ?";
        $params[] = !empty($extra['proposal_reference']) ? trim($extra['proposal_reference']) : null;
    }
    if (array_key_exists('lost_reason', $extra)) {
        $updates[] = "lost_reason = ?";
        $params[] = !empty($extra['lost_reason']) ? trim($extra['lost_reason']) : null;
    }
    if ($status === 'converted' && empty($lead['converted_at'])) {
        $updates[] = "converted_at = NOW()";
    }

    $params[] = $id;
    $sql = "UPDATE leads SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'id' => $id,
        'name' => $lead['name'],
        'previous_status' => $previousStatus,
        'new_status' => $status,
        'follow_up_date' => $extra['follow_up_date'] ?? $lead['follow_up_date'] ?? null,
        'stage_notes' => $extra['stage_notes'] ?? $lead['stage_notes'] ?? null,
        'website' => $extra['website'] ?? $lead['website'] ?? null,
        'proposal_reference' => $extra['proposal_reference'] ?? $lead['proposal_reference'] ?? null,
        'lost_reason' => $extra['lost_reason'] ?? $lead['lost_reason'] ?? null,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
              || in_array($_POST['action'], ['update_status', 'get_lead_details'], true);

    if (!$canEdit && $_POST['action'] !== 'get_lead_details') {
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied. You have view-only privileges.']);
            exit;
        }
        $message = 'Access denied. You have view-only privileges.';
        $message_type = 'error';
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid request / security token or session expired. Please refresh and try again.']);
            exit;
        }
        $message = 'Invalid request. Please refresh and try again.';
        $message_type = 'error';
    } else {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        switch ($action) {
            case 'create':
                $lead_name = $_POST['name'] ?? '';
                $lead_email = $_POST['email'] ?: '';
                $lead_phone = $_POST['phone'] ?? '';
                $lead_service = $_POST['service'] ?: '';
                $lead_website = $_POST['website'] ?: null;
                $lead_followup = !empty($_POST['follow_up_date']) ? date('Y-m-d H:i:s', strtotime($_POST['follow_up_date'])) : null;
                $lead_notes = $_POST['stage_notes'] ?: ($_POST['message'] ?: null);

                $new_status = $_POST['status'] ?? 'new_lead';
                if (!isValidLeadStatus($new_status)) {
                    $new_status = 'new_lead';
                }

                $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, service, message, status, website, follow_up_date, stage_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$lead_name, $lead_phone, $lead_email ?: null, $lead_service ?: null, $_POST['message'] ?: null, $new_status, $lead_website, $lead_followup, $lead_notes]);
                logAudit($conn, 'CREATE_LEAD', "Created lead: " . $lead_name, $_SESSION['admin_id']);
                notifySecurity('CREATE_LEAD', "Created lead: $lead_name", $_SESSION['admin_id']);

                $lead_data = ['name' => $lead_name, 'email' => $lead_email, 'phone' => $lead_phone, 'service' => $lead_service, 'message' => $_POST['message'] ?? ''];
                sendLeadNotification($lead_data);

                if (!empty($lead_email)) {
                    sendLeadThankYouEmail($lead_email, $lead_name, $lead_service);
                }

                $message = 'Lead created successfully!';
                $message_type = 'success';
                break;
            case 'update':
                $new_status = $_POST['status'] ?? '';
                if (!isValidLeadStatus($new_status)) {
                    $message = 'Invalid lead status. Please choose a stage from the list.';
                    $message_type = 'error';
                    break;
                }

                $lead_website = $_POST['website'] ?? null;
                $lead_followup = !empty($_POST['follow_up_date']) ? date('Y-m-d H:i:s', strtotime($_POST['follow_up_date'])) : null;
                $lead_notes = $_POST['stage_notes'] ?? null;

                $stmt = $conn->prepare("UPDATE leads SET name = ?, phone = ?, email = ?, service = ?, message = ?, status = ?, website = COALESCE(?, website), follow_up_date = ?, stage_notes = COALESCE(?, stage_notes) WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['email'] ?: null, $_POST['service'] ?: null, $_POST['message'] ?: null, $new_status, $lead_website, $lead_followup, $lead_notes, $id]);
                logAudit($conn, 'UPDATE_LEAD', "Updated lead ID: $id", $_SESSION['admin_id']);
                notifySecurity('UPDATE_LEAD', "Lead ID $id updated to status: " . $new_status, $_SESSION['admin_id']);
                $message = 'Lead updated successfully!';
                $message_type = 'success';
                break;
            case 'update_status':
                try {
                    $extra = [];
                    if (isset($_POST['follow_up_date'])) {
                        $extra['follow_up_date'] = $_POST['follow_up_date'];
                    }
                    if (isset($_POST['stage_notes'])) {
                        $extra['stage_notes'] = $_POST['stage_notes'];
                    }
                    if (isset($_POST['website'])) {
                        $extra['website'] = $_POST['website'];
                    }
                    if (isset($_POST['proposal_reference'])) {
                        $extra['proposal_reference'] = $_POST['proposal_reference'];
                    }
                    if (isset($_POST['lost_reason'])) {
                        $extra['lost_reason'] = $_POST['lost_reason'];
                    }

                    $result = saveLeadStatus($conn, $id, $_POST['status'] ?? '', $extra);
                    $saved_status = $result['new_status'];
                    $prev_status = $result['previous_status'];

                    // Activity logging with Admin ID, Lead ID, Previous Status, New Status & optional details
                    $auditDetails = "Updated lead ID #{$id} ({$result['name']}) status from '{$prev_status}' to '{$saved_status}'";
                    if (!empty($extra['stage_notes'])) {
                        $auditDetails .= " | Notes: " . mb_substr($extra['stage_notes'], 0, 100);
                    }
                    if (!empty($extra['lost_reason'])) {
                        $auditDetails .= " | Reason: " . mb_substr($extra['lost_reason'], 0, 100);
                    }
                    if (!empty($extra['follow_up_date'])) {
                        $auditDetails .= " | Follow-up: " . $extra['follow_up_date'];
                    }

                    logAudit($conn, 'UPDATE_LEAD_STATUS', $auditDetails, $_SESSION['admin_id'] ?? null);
                    notifySecurity('UPDATE_LEAD_STATUS', "Lead ID #{$id} status changed from '{$prev_status}' to '{$saved_status}'", $_SESSION['admin_id'] ?? null);

                    $meta = leadStatusMeta($saved_status);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'id' => $id,
                        'status' => $saved_status,
                        'previous_status' => $prev_status,
                        'label' => leadStatusLabel($saved_status),
                        'badge' => $meta['badge'],
                        'color' => $meta['color'],
                        'icon' => $meta['icon'] ?? 'fa-circle',
                        'follow_up_date' => $result['follow_up_date'],
                        'stage_notes' => $result['stage_notes'],
                        'website' => $result['website'],
                        'message' => "Lead #{$id} status updated to " . leadStatusLabel($saved_status)
                    ]);
                    exit;
                } catch (InvalidArgumentException $e) {
                    header('Content-Type: application/json');
                    http_response_code(422);
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                } catch (RuntimeException $e) {
                    header('Content-Type: application/json');
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                } catch (Exception $e) {
                    error_log('[Lead Status Update Error] ' . $e->getMessage());
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to update lead status. Please try again.']);
                    exit;
                }
                break;
            case 'get_lead_details':
                $stmt = $conn->prepare("SELECT * FROM leads WHERE id = ?");
                $stmt->execute([$id]);
                $lead = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$lead) {
                    header('Content-Type: application/json');
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => "Lead #{$id} not found."]);
                    exit;
                }

                // Query stage activity history for this lead
                $histStmt = $conn->prepare("
                    SELECT al.*, COALESCE(a.name, 'Administrator') as admin_name
                    FROM admin_activities al
                    LEFT JOIN admin a ON al.user_id = a.id
                    WHERE (al.details LIKE :p1 OR al.details LIKE :p2)
                    ORDER BY al.id DESC LIMIT 15
                ");
                $histStmt->execute([
                    ':p1' => "%lead ID #{$id}%",
                    ':p2' => "%lead ID: {$id}%"
                ]);
                $history = $histStmt->fetchAll(PDO::FETCH_ASSOC);

                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'lead' => $lead,
                    'status_meta' => leadStatusMeta($lead['status']),
                    'history' => $history
                ]);
                exit;
                break;
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM leads WHERE id = ?");
                $stmt->execute([$id]);
                logAudit($conn, 'DELETE_LEAD', "Deleted lead ID: $id", $_SESSION['admin_id']);
                notifySecurity('DELETE_LEAD', "Lead ID $id deleted", $_SESSION['admin_id']);
                $message = 'Lead deleted successfully!';
                $message_type = 'success';
                break;
            case 'bulk_delete':
                $ids = $_POST['bulk_ids'] ?? [];
                if (!empty($ids) && is_array($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $intIds = array_map('intval', $ids);
                    $stmt = $conn->prepare("DELETE FROM leads WHERE id IN ($placeholders)");
                    $stmt->execute($intIds);
                    $count = $stmt->rowCount();
                    logAudit($conn, 'BULK_DELETE_LEADS', "Bulk deleted $count leads: " . implode(',', $intIds), $_SESSION['admin_id']);
                    notifySecurity('BULK_DELETE_LEADS', "$count leads bulk deleted", $_SESSION['admin_id']);
                    $message = $count . ' lead(s) deleted successfully!';
                    $message_type = 'success';
                }
                break;
            case 'bulk_update_status':
                $ids = $_POST['bulk_ids'] ?? [];
                $status = $_POST['status'] ?? '';
                if (!isValidLeadStatus($status)) {
                    $message = 'Invalid lead status. Please choose a stage from the list.';
                    $message_type = 'error';
                    break;
                }
                if (!empty($ids) && is_array($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $intIds = array_map('intval', $ids);
                    $params = array_merge([$status], $intIds);
                    $stmt = $conn->prepare("UPDATE leads SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute($params);
                    $count = $stmt->rowCount();
                    logAudit($conn, 'BULK_UPDATE_LEADS', "Bulk updated $count leads to status: $status", $_SESSION['admin_id']);
                    notifySecurity('BULK_UPDATE_LEADS', "$count leads updated to $status", $_SESSION['admin_id']);
                    $message = $count . ' lead(s) updated to ' . leadStatusLabel($status) . '!';
                    $message_type = 'success';
                }
                break;
        }
    }
}

$filter_stage = trim($_GET['stage'] ?? '');
$search_query = trim($_GET['q'] ?? $_GET['search'] ?? '');

$where = [];
$params = [];

if ($filter_stage !== '' && $filter_stage !== 'all') {
    if ($filter_stage === 'overdue') {
        $where[] = "follow_up_date IS NOT NULL AND follow_up_date < NOW() AND status NOT IN ('converted', 'not_interested', 'lose')";
    } elseif ($filter_stage === 'upcoming') {
        $where[] = "follow_up_date IS NOT NULL AND follow_up_date >= NOW() AND status NOT IN ('converted', 'not_interested', 'lose')";
    } elseif (isStoredLeadStatus($filter_stage)) {
        $where[] = "status = ?";
        $params[] = $filter_stage;
    }
}

if ($search_query !== '') {
    $searchTerm = "%{$search_query}%";
    $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ? OR website LIKE ? OR service LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
$stmt = $conn->prepare("SELECT * FROM leads {$whereSql} ORDER BY created_at DESC");
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_leads_count = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$lead_status_counts = leadStatusCounts($conn);
$followup_counts = leadFollowupCounts($conn);
$csrf_token = generateCSRFToken();
$lead_status_options = leadStatuses();
$lead_legacy_options = leadLegacyStatuses();

require_once __DIR__ . '/../app/Views/admin/leads-pro.php';
