<?php
/**
 * Admin Leads Management
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = '';
$message_type = '';
$userRole = $_SESSION['admin_role'] ?? 'viewer';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (in_array($userRole, ['viewer'])) {
        $message = 'Access denied. You have view-only privileges.';
        $message_type = 'error';
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
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

                $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, service, message, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$lead_name, $lead_phone, $lead_email ?: null, $lead_service ?: null, $_POST['message'] ?: null, $_POST['status'] ?: 'new']);
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
                $stmt = $conn->prepare("UPDATE leads SET name = ?, phone = ?, email = ?, service = ?, message = ?, status = ? WHERE id = ?");
                $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['email'] ?: null, $_POST['service'] ?: null, $_POST['message'] ?: null, $_POST['status'], $id]);
                logAudit($conn, 'UPDATE_LEAD', "Updated lead ID: $id", $_SESSION['admin_id']);
                notifySecurity('UPDATE_LEAD', "Lead ID $id updated to status: " . $_POST['status'], $_SESSION['admin_id']);
                $message = 'Lead updated successfully!';
                $message_type = 'success';
                break;
            case 'update_status':
                $status = $_POST['status'] ?? 'new';
                $stmt = $conn->prepare("UPDATE leads SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                logAudit($conn, 'UPDATE_LEAD_STATUS', "Updated lead ID $id to status $status via Kanban Board", $_SESSION['admin_id']);
                notifySecurity('UPDATE_LEAD_STATUS', "Lead ID $id updated to status: " . $status, $_SESSION['admin_id']);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
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
                $validStatuses = ['new', 'contacted', 'confirmed', 'lose'];
                if (!empty($ids) && is_array($ids) && in_array($status, $validStatuses)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $intIds = array_map('intval', $ids);
                    $params = array_merge([$status], $intIds);
                    $stmt = $conn->prepare("UPDATE leads SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute($params);
                    $count = $stmt->rowCount();
                    logAudit($conn, 'BULK_UPDATE_LEADS', "Bulk updated $count leads to status: $status", $_SESSION['admin_id']);
                    notifySecurity('BULK_UPDATE_LEADS', "$count leads updated to $status", $_SESSION['admin_id']);
                    $message = $count . ' lead(s) updated to ' . ucfirst($status) . '!';
                    $message_type = 'success';
                }
                break;
        }
    }
}

$leads = $conn->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/leads-pro.php';
