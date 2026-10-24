<?php
/**
 * WRC Admin - AJAX Upload Handler
 * Handles image and file uploads for the admin CMS.
 * Returns JSON response.
 */
require_once __DIR__ . '/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit;
}

$file = $_FILES['file'];
$type = $_POST['type'] ?? 'image'; // 'image' or 'file'

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload stopped by extension.',
    ];
    $msg = $errors[$file['error']] ?? 'Unknown upload error.';
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$originalName = basename($file['name']);
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Uploads are stored OUTSIDE the deployment tree on purpose.
//
// assets/ is shipped to the server by every deploy, so anything the admin
// uploads straight into it is an untracked file inside a tracked directory and
// is wiped the next time that directory is reconciled with the repository.
// That is exactly why Chairman and Our Team photographs kept vanishing after a
// day or two while the images committed to git stayed online forever.
//
// uploads/ is git-ignored and is not part of the deploy source list, so it is
// never overwritten, never pruned and survives every push — the same way
// includes/config.php does.
if ($type === 'image') {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $uploadDir = __DIR__ . '/../uploads/images';
    $webPath = 'uploads/images';
} else {
    $allowed = ['pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];
    $maxSize = 20 * 1024 * 1024; // 20MB
    $uploadDir = __DIR__ . '/../uploads/files';
    $webPath = 'uploads/files';
}

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'File type .' . $ext . ' is not allowed. Allowed: ' . implode(', ', $allowed)]);
    exit;
}

if ($file['size'] > $maxSize) {
    $maxMB = round($maxSize / 1024 / 1024);
    echo json_encode(['success' => false, 'message' => 'File size exceeds ' . $maxMB . 'MB limit.']);
    exit;
}

// Generate unique filename
$baseName = pathinfo($originalName, PATHINFO_FILENAME);
$baseName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $baseName);
$baseName = preg_replace('/-+/', '-', $baseName);
$baseName = trim($baseName, '-');
$timestamp = time();
$filename = $baseName . '-' . $timestamp . '.' . $ext;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destination = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file.']);
    exit;
}

$fullPath = $webPath . '/' . $filename;

echo json_encode([
    'success' => true,
    'message' => 'File uploaded successfully.',
    'path'    => $fullPath,
    'filename' => $filename,
    'original' => $originalName,
    'size'    => $file['size'],
]);
