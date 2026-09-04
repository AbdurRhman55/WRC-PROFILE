<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

$existing = getRow('newsletter_subscribers', ['email' => $email]);
if ($existing) {
    echo json_encode(['success' => true, 'message' => 'Already subscribed']);
    exit;
}

insertRow('newsletter_subscribers', [
    'email' => $email,
    'subscribed_at' => date('Y-m-d H:i:s'),
    'is_active' => 1,
]);

echo json_encode(['success' => true, 'message' => 'Successfully subscribed']);
