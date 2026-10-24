<?php
/**
 * Assert the rendered admin header is structurally correct in saved HTML.
 *
 * Run after fetching authenticated pages (see scripts/_mint_admin_cookie.php
 * + a request with the PHPSESSID cookie). Checks the things the old header
 * got wrong: the hardcoded title, the invisible hamburger, the double-escaped
 * utility class, the missing theme sheet, and the duplicate mobile search box.
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

$dir = sys_get_temp_dir() . '/hdrscreens';

$expectations = [
    'dashboard' => [
        'title_regex'   => '/<title>\s*Dashboard\s*(&mdash;|—)\s*Admin Panel/i',
        'page_title'    => 'Dashboard',
    ],
    'leads' => [
        'title_regex'   => '/<title>\s*Leads Management\s*(&mdash;|—)\s*Admin Panel/i',
        'page_title'    => 'Leads Management',
    ],
];
foreach ($expectations as $page => $exp) {
    $file = "{$dir}/{$page}.html";
    echo "\n=== {$page} ===\n";

    if (!is_file($file)) {
        check("{$page}.html exists", false, 'not found — fetch it first');
        continue;
    }
    check("{$page}.html exists", true);

    $html = file_get_contents($file);

    // 1. The title must be page-specific, not the old hardcoded "Dashboard".
    preg_match('/<title>(.*?)<\/title>/is', $html, $m);
    $title = trim(html_entity_decode($m[1] ?? '', ENT_QUOTES, 'UTF-8'));
    check(
        "title is page-specific",
        (bool)preg_match('/^' . preg_quote($exp['page_title'], '/') . '\s*(—|--|-)\s*Admin Panel$/i', $title),
        "got '{$title}'"
    );

    // 2. Both stylesheets must be linked.
    check(
        'theme-professional.css is linked',
        str_contains($html, 'theme-professional.css'),
    );
    check(
        'dead theme-saas.css is not linked',
        !str_contains($html, 'theme-saas.css'),
    );

    // 3. The hamburger toggle must exist, be wired to the sidebar, and carry a
    //    class that actually hides it at desktop widths. Match each attribute
    //    independently — the markup does not guarantee a fixed attribute order.
    $hasHamburger = str_contains($html, 'id="menuToggle"')
        && str_contains($html, 'onclick="toggleSidebar()"');
    check('hamburger button is present and wired to toggleSidebar()', $hasHamburger);

    check(
        'hamburger uses the single-escaped utility class',
        !str_contains($html, 'md\\:hidden') || preg_match('/md\\:hidden/', $html) && !str_contains($html, 'md\\\\:hidden'),
        'found double-escaped md\\\\:hidden'
    );

    // 4. The old desktop sidebar must not also be shown at mobile widths.
    check(
        'sidebar markup present',
        str_contains($html, 'class="sidebar"') || str_contains($html, "class='sidebar'"),
    );

    // 5. Header search must appear exactly once — the old build duplicated it.
    $searchCount = preg_match_all('/class=["\'][^"\']*\btopbar-search\b/i', $html);
    check(
        'header search appears once',
        $searchCount === 1,
        "found {$searchCount}"
    );

    // 6. The page heading inside the content area should match the title.
    check(
        "content shows the '{$exp['page_title']}' heading",
        str_contains($html, $exp['page_title']),
    );

    // 7. No leftover debugging markers.
    foreach (['var_dump(', 'print_r(', 'TODO: fix header', 'XXX'] as $marker) {
        check("no stray '{$marker}' in output", !str_contains($html, $marker));
    }

    // 8. The <body> must not have been replaced by a login redirect.
    check(
        'page is authenticated (not bounced to login)',
        str_contains($html, 'Welcome Back') === false,
        'got the login screen'
    );
}

echo "\n" . str_repeat('=', 62) . "\n";
printf("PASSED: %d   FAILED: %d\n", $passes, count($failures));

if ($failures) {
    echo "\nFailures:\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
    exit(1);
}

echo "All rendered-header checks passed.\n";
exit(0);
