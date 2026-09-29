<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$formType = $_POST['form_type'] ?? '';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (!$name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Name and email are required']);
    exit;
}

$messageParts = [];
$subject = '';

switch ($formType) {
    case 'volunteer':
        $subject = 'Volunteer Application';
        $skills = $_POST['skills'] ?? '';
        $availability = $_POST['availability'] ?? '';
        $messageParts[] = "Skills: $skills";
        $messageParts[] = "Availability: $availability";
        break;

    case 'job_apply':
        $subject = 'Job Application: ' . ($_POST['position'] ?? '');
        $qualification = $_POST['qualification'] ?? '';
        $experience = $_POST['experience'] ?? '';
        $cover = trim($_POST['cover'] ?? '');
        $messageParts[] = "Position: " . ($_POST['position'] ?? '');
        $messageParts[] = "Qualification: $qualification";
        $messageParts[] = "Experience: $experience";
        if ($cover) $messageParts[] = "Cover Letter: $cover";
        break;

    case 'internship':
        $subject = 'Internship Application';
        $university = trim($_POST['university'] ?? '');
        $degree = trim($_POST['degree'] ?? '');
        $department = $_POST['department'] ?? '';
        $messageParts[] = "University: $university";
        $messageParts[] = "Degree: $degree";
        $messageParts[] = "Department: $department";
        break;

    case 'partner':
        $subject = 'Partnership Inquiry';
        $org = trim($_POST['organization'] ?? '');
        $role = $_POST['role'] ?? '';
        $messageParts[] = "Organization: $org";
        $messageParts[] = "Role: $role";
        $messageParts[] = "Message: " . trim($_POST['message'] ?? '');
        break;

    case 'membership':
        $subject = 'Membership Registration';
        $address = trim($_POST['address'] ?? '');
        $type = $_POST['type'] ?? '';
        $messageParts[] = "Address: $address";
        $messageParts[] = "Membership Type: $type";
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown form type']);
        exit;
}

$message = implode("\n", $messageParts);

insertRow('contact_submissions', [
    'full_name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
    'inquiry_type' => 'general',
    'phone' => $phone,
    'submitted_at' => date('Y-m-d H:i:s'),
]);

echo json_encode(['success' => true, 'message' => 'Submitted successfully']);
