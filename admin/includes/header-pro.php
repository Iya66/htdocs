<?php
/**
 * Compatibility shim.
 *
 * The admin header used to exist twice — header.php and header-pro.php — as
 * two near-identical copies that drifted apart (the -pro variant was missing
 * nav items and loaded a conflicting stylesheet). There is now a single
 * header at admin/includes/header.php.
 *
 * This file only exists so the ~30 admin views that still reference
 * 'includes/header-pro.php' keep working without being edited. New code
 * should include header.php directly.
 */

require_once __DIR__ . '/header.php';
