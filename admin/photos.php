<?php
/**
 * Legacy field-photo gallery screen.
 *
 * Project photographs are now managed per partner and per project from the
 * Project Galleries hub (gallery.php -> project-media.php), which is the only
 * photo workflow the public gallery reads. The old photo_gallery archive is no
 * longer displayed on the News & Media page, so this screen is kept only as a
 * redirect for old bookmarks instead of an extra, conflicting media section.
 */
require_once __DIR__ . '/config.php';
requireLogin();

redirect('gallery.php');
