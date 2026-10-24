<?php
$content = file_get_contents(__DIR__ . '/../.htaccess');
$old = "# 1. Strip .php extension in Admin URLs (Redirect /admin/path.php to /admin/path)\r\nRewriteCond %{THE_REQUEST} ^[A-Z]{3,}\\s/+(admin/[^\\s\\?]+)\\.php [NC]\r\nRewriteRule ^admin/(.+)\\.php$ /admin/$1 [R=301,L,NC]";
$new = "# 1. Strip .php extension in Admin URLs (Redirect /admin/path.php to /admin/path, excluding POST and API requests)\r\nRewriteCond %{REQUEST_METHOD} !POST [NC]\r\nRewriteCond %{REQUEST_URI} !^/admin/api/ [NC]\r\nRewriteCond %{REQUEST_URI} !^/api/ [NC]\r\nRewriteCond %{THE_REQUEST} ^GET\\s/+(admin/[^\\s\\?]+)\\.php [NC]\r\nRewriteRule ^admin/(.+)\\.php$ /admin/$1 [R=301,L,NC]";

if (str_contains($content, $old)) {
    $content = str_replace($old, $new, $content);
    file_put_contents(__DIR__ . '/../.htaccess', $content);
    echo "Successfully updated .htaccess\n";
} else {
    echo "Could not find target in .htaccess\n";
}
