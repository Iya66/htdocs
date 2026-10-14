<?php
/**
 * Admin Pages & SEO Manager Controller
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/security_notifier.php';

// Ensure admin role
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
if (!$admin || $admin['role'] !== 'admin') {
    die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }
    
    if ($action === 'toggle_status') {
        $id = (int)$_POST['id'];
        $status = (int)$_POST['status'];
        $stmt = $conn->prepare("UPDATE pages SET is_active = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true, 'message' => 'Page status updated.']);
        exit;
    }
    
    if ($action === 'bulk_toggle') {
        $ids = json_decode($_POST['ids'] ?? '[]');
        $status = (int)$_POST['status'];
        if (!empty($ids)) {
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$status], $ids);
            $stmt = $conn->prepare("UPDATE pages SET is_active = ? WHERE id IN ($inQuery)");
            $stmt->execute($params);
        }
        echo json_encode(['success' => true, 'message' => 'Bulk status updated.']);
        exit;
    }
    
    if ($action === 'get_page') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $page]);
        exit;
    }
    
    if ($action === 'save_page') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['page_title'] ?? '');
        $raw_slug = trim($_POST['page_slug'] ?? '');
        
        // Clean and validate slug to prevent directory traversal and arbitrary file write
        $slug_filename = basename($raw_slug);
        $slug_name = pathinfo($slug_filename, PATHINFO_FILENAME);
        $slug_name = preg_replace('/[^a-z0-9_-]/', '', strtolower($slug_name));
        
        if (empty($slug_name)) {
            echo json_encode(['success' => false, 'message' => 'Invalid page slug! Must be alphanumeric with dashes/underscores.']);
            exit;
        }
        
        $slug = $slug_name . '.php';
        
        // Check duplicate slug
        $stmt = $conn->prepare("SELECT id FROM pages WHERE page_slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Page slug already exists!']);
            exit;
        }

        $fields = [
            'page_title' => $title,
            'page_slug' => $slug,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            
            // SEO
            'seo_title' => $_POST['seo_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'meta_keywords' => $_POST['meta_keywords'] ?? '',
            'canonical_url' => $_POST['canonical_url'] ?? '',
            'og_title' => $_POST['og_title'] ?? '',
            'og_description' => $_POST['og_description'] ?? '',
            'og_image' => $_POST['og_image'] ?? '',
            'twitter_title' => $_POST['twitter_title'] ?? '',
            'twitter_description' => $_POST['twitter_description'] ?? '',
            'twitter_image' => $_POST['twitter_image'] ?? '',
            'robots_meta' => $_POST['robots_meta'] ?? 'index, follow',
            'schema_markup' => $_POST['schema_markup'] ?? '',
            
            // Content
            'hero_section' => $_POST['hero_section'] ?? '',
            'main_content' => $_POST['main_content'] ?? '',
            'images' => $_POST['images'] ?? '',
            'buttons' => $_POST['buttons'] ?? '',
            'custom_html' => $_POST['custom_html'] ?? '',
            'custom_css' => $_POST['custom_css'] ?? '',
            'custom_js' => $_POST['custom_js'] ?? '',
        ];
        
        $rootPath = realpath(__DIR__ . '/../');

        if ($id > 0) {
            $setClause = implode(' = ?, ', array_keys($fields)) . ' = ?';
            $params = array_values($fields);
            $params[] = $id;
            
            // Get old slug to rename file if it changed
            $stmt = $conn->prepare("SELECT page_slug FROM pages WHERE id = ?");
            $stmt->execute([$id]);
            $oldSlug = $stmt->fetchColumn();
            
            $stmt = $conn->prepare("UPDATE pages SET $setClause WHERE id = ?");
            $stmt->execute($params);
            
            // Rename file if slug changed
            if ($oldSlug && $oldSlug !== $slug) {
                $old_name = preg_replace('/[^a-z0-9_-]/', '', strtolower(pathinfo(basename($oldSlug), PATHINFO_FILENAME))) . '.php';
                $oldPath = $rootPath . '/' . $old_name;
                $newPath = $rootPath . '/' . $slug;
                if ($oldPath && file_exists($oldPath) && strpos($oldPath, $rootPath) === 0) {
                    rename($oldPath, $newPath);
                }
            }
            
            $message = 'Page updated successfully.';
        } else {
            $cols = implode(', ', array_keys($fields));
            $vals = implode(', ', array_fill(0, count($fields), '?'));
            
            $stmt = $conn->prepare("INSERT INTO pages ($cols) VALUES ($vals)");
            $stmt->execute(array_values($fields));
            $message = 'New page created successfully.';
        }
        
        // Ensure physical file exists
        $filePath = $rootPath . '/' . $slug;
        if (!file_exists($filePath)) {
            $template = "<?php require_once __DIR__ . '/includes/header.php'; ?>\n";
            $template .= "<?php\n";
            $template .= "    \$title_parts = explode(' ', \$page_settings['page_title'] ?? 'Custom Page');\n";
            $template .= "    \$highlight = array_pop(\$title_parts);\n";
            $template .= "    \$main_title = implode(' ', \$title_parts);\n";
            $template .= "?>\n";
            $template .= "<section class=\"page-header\">\n";
            $template .= "    <div class=\"container\">\n";
            $template .= "        <h1><?= htmlspecialchars(\$main_title) ?> <span class=\"highlight\"><?= htmlspecialchars(\$highlight) ?></span></h1>\n";
            $template .= "        <p><?= htmlspecialchars(\$page_settings['meta_description'] ?? 'Explore our custom content') ?></p>\n";
            $template .= "    </div>\n";
            $template .= "</section>\n";
            $template .= "<?php if(!empty(\$page_settings['hero_section'])): ?>\n";
            $template .= "<section class=\"section\" style=\"background: var(--primary-soft); padding: 60px 0;\">\n";
            $template .= "    <div class=\"container\">\n";
            $template .= "        <div class=\"hero-content fade-in\">\n";
            $template .= "            <?= \$page_settings['hero_section'] ?>\n";
            $template .= "        </div>\n";
            $template .= "    </div>\n";
            $template .= "</section>\n";
            $template .= "<?php endif; ?>\n";
            $template .= "<section class=\"section\" style=\"padding-top: 40px;\">\n";
            $template .= "    <div class=\"container\">\n";
            $template .= "        <div class=\"legal-content fade-in\" style=\"background: var(--card); padding: 40px 50px; border-radius: var(--border-radius, 16px); box-shadow: 0 10px 40px rgba(0,0,0,0.05);\">\n";
            $template .= "            <?= \$page_settings['main_content'] ?? '<div style=\"text-align:center; color:var(--text2); padding: 40px;\">This page is currently empty. Edit it in the Admin Panel!</div>' ?>\n";
            $template .= "        </div>\n";
            $template .= "    </div>\n";
            $template .= "</section>\n";
            $template .= "<?php require_once __DIR__ . '/includes/footer.php'; ?>\n";
            file_put_contents($filePath, $template);
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        exit;
    }
}

// Fetch stats for dashboard
$total = $conn->query("SELECT COUNT(*) FROM pages")->fetchColumn();
$active = $conn->query("SELECT COUNT(*) FROM pages WHERE is_active = 1")->fetchColumn();
$disabled = $total - $active;

// Fetch all pages
$stmt = $conn->query("SELECT id, page_slug, page_title, is_active, updated_at FROM pages ORDER BY page_title ASC");
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/pages.php';
