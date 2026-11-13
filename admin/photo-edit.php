<?php
/**
 * Legacy field-photo editor.
 *
 * Replaced by the partner/project gallery editor (gallery.php and
 * project-media.php). Redirects so old links land on the supported workflow.
 */
require_once __DIR__ . '/config.php';
requireLogin();

redirect('gallery.php');
