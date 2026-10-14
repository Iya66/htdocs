<?php
/**
 * Admin Reports - Activity Reports & Email Notifications
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';

// Role check (Admin or Manager only)
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || !in_array($cu['role'], ['admin', 'manager'])) {
    $_SESSION['error'] = 'Access denied. Privileged role required.';
    header('Location: dashboard.php');
    exit;
}

$message = '';
$message_type = '';

// Handle email report request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_report_email'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request.';
        $message_type = 'error';
    } else {
        require_once __DIR__ . '/../app/email_helper.php';

        $report_range = $_POST['report_range'] ?? 'all';
        $admin_email = $_POST['admin_email'] ?? '';

        if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please provide a valid email address.';
            $message_type = 'error';
        } else {
            // Build report based on range
            $where = '';
            if ($report_range === 'today') {
                $where = "WHERE DATE(al.created_at) = CURDATE()";
            } elseif ($report_range === 'week') {
                $where = "WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            } elseif ($report_range === 'month') {
                $where = "WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            }

            $stmt = $conn->query("SELECT al.*, a.name as admin_name, a.email as admin_email
                FROM admin_activities al
                LEFT JOIN admin a ON al.user_id = a.id
                $where
                ORDER BY al.created_at DESC LIMIT 100");
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $subject = 'CloudASPIRE Activity Report - ' . ucfirst($report_range) . ' - ' . date('Y-m-d');
            $body = '
            <div style="font-family: Arial, sans-serif; max-width: 700px; margin: 0 auto;">
                <h2 style="color: #667eea; text-align: center;">CloudASPIRE Activity Report</h2>
                <p style="text-align: center; color: #666;">Report Period: ' . ucfirst($report_range) . ' | Generated: ' . date('Y-m-d H:i:s') . '</p>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background: #667eea; color: #fff;">
                            <th style="padding: 12px; text-align: left;">Date & Time</th>
                            <th style="padding: 12px; text-align: left;">Admin</th>
                            <th style="padding: 12px; text-align: left;">Action</th>
                            <th style="padding: 12px; text-align: left;">Details</th>
                            <th style="padding: 12px; text-align: left;">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>';

            if (empty($logs)) {
                $body .= '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #666;">No activity found for this period.</td></tr>';
            } else {
                foreach ($logs as $log) {
                    $body .= '<tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px; font-size: 12px;">' . date('Y-m-d H:i', strtotime($log['created_at'])) . '</td>
                        <td style="padding: 10px;">' . htmlspecialchars($log['admin_name'] ?? 'System') . '</td>
                        <td style="padding: 10px;"><span style="background: #e8eaf6; color: #667eea; padding: 3px 8px; border-radius: 4px; font-size: 11px;">' . htmlspecialchars($log['action']) . '</span></td>
                        <td style="padding: 10px; font-size: 12px;">' . htmlspecialchars($log['details'] ?? '-') . '</td>
                        <td style="padding: 10px; font-family: monospace; font-size: 11px;">' . htmlspecialchars($log['ip_address'] ?? '-') . '</td>
                    </tr>';
                }
            }

            $body .= '</tbody></table>
                <div style="background: #f8f9fa; padding: 15px; margin-top: 20px; border-radius: 8px;">
                    <p style="margin: 0; color: #666; font-size: 12px;"><strong>Summary:</strong> ' . count($logs) . ' actions recorded</p>
                </div>
                <p style="color: #999; font-size: 11px; margin-top: 20px; text-align: center;">This is an automated report from CloudASPIRE Admin Panel.</p>
            </div>';

            $result = sendEmail($admin_email, $subject, $body);
            $isLocalhost = ($_SERVER['HTTP_HOST'] ?? '') === 'localhost' || ($_SERVER['HTTP_HOST'] ?? '') === '127.0.0.1';

            if ($result['success'] || $isLocalhost) {
                $message = 'Report sent to ' . htmlspecialchars($admin_email) . ' successfully!';
                if ($isLocalhost && !$result['success']) {
                    $message .= ' (Localhost Debug: Simulated Send)';
                }
                $message_type = 'success';
            } else {
                $message = 'Failed to send email: ' . $result['message'];
                $message_type = 'error';
            }
        }
    }
}

// Fetch audit logs with admin info
$audit_logs = $conn->query("SELECT al.*, a.name as admin_name, a.username as admin_username, a.email as admin_email
    FROM admin_activities al
    LEFT JOIN admin a ON al.user_id = a.id
    ORDER BY al.created_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

// Get unique actions
$unique_actions = $conn->query("SELECT DISTINCT action FROM admin_activities ORDER BY action")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$today_count = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$week_count = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$month_count = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$total_count = $conn->query("SELECT COUNT(*) FROM admin_activities")->fetchColumn();

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/reports.php';
