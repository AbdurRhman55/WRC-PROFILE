<?php
/**
 * WRC - Public form submission endpoint (AJAX / JSON)
 *
 * Handles: volunteer, job_apply, internship, partner, membership, donation
 * Stores every submission in `contact_submissions` using the most relevant
 * `inquiry_type` so the Admin > Submissions screen can filter it properly.
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

/**
 * Emit a JSON error and stop.
 */
function fail(string $message, string $title = 'Submission Failed'): void
{
    echo json_encode(['success' => false, 'title' => $title, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request method.', 'Invalid Request');
}

// ---------------------------------------------
// Common fields
// ---------------------------------------------
$formType = trim($_POST['form_type'] ?? '');

// Different forms label the person differently - normalise them all.
$name  = trim($_POST['name'] ?? '');
if ($name === '') $name = trim($_POST['full_name'] ?? '');
if ($name === '') $name = trim($_POST['contact'] ?? '');

$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($name === '' || $email === '') {
    fail('Name and email are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid email address.');
}

$messageParts = [];
$subject         = '';
$inquiryType     = 'general';
$volunteerFlag   = 0;
$attachment      = null;
$successTitle    = 'Submitted Successfully';
$successMessage  = 'Thank you! Your submission has been received.';

// ---------------------------------------------
// Per-form handling
// ---------------------------------------------
switch ($formType) {

    case 'volunteer':
        $skills       = $_POST['skills'] ?? '';
        $availability = $_POST['availability'] ?? '';

        $subject       = 'Volunteer Application';
        $inquiryType   = 'volunteer';
        $volunteerFlag = 1;

        $messageParts[] = 'Skills / Interests: ' . $skills;
        $messageParts[] = 'Availability: ' . $availability;

        $successTitle   = 'Application Received';
        $successMessage = 'Thank you for offering your time! Our Volunteer Team will review your application and contact you within 48 hours.';
        break;

    case 'job_apply':
        $position      = trim($_POST['position'] ?? '');
        $qualification = $_POST['qualification'] ?? '';
        $experience    = $_POST['experience'] ?? '';
        $cover         = trim($_POST['cover'] ?? '');

        $subject     = 'Job Application: ' . ($position !== '' ? $position : 'Unspecified Role');
        $inquiryType = 'career';

        $messageParts[] = 'Position: ' . ($position !== '' ? $position : 'Unspecified Role');
        $messageParts[] = 'Qualification: ' . $qualification;
        $messageParts[] = 'Experience: ' . $experience;
        if ($cover !== '') $messageParts[] = "Cover Letter: $cover";

        $successTitle   = 'Application Submitted';
        $successMessage = 'Your application for ' . ($position !== '' ? $position : 'this role') . ' has been received. Our HR team will review it and get back to you within 5-7 business days.';
        break;

    case 'internship':
        $university = trim($_POST['university'] ?? '');
        $degree     = trim($_POST['degree'] ?? '');
        $department = $_POST['department'] ?? '';

        $subject     = 'Internship Application';
        $inquiryType = 'career';

        $messageParts[] = 'University: ' . $university;
        $messageParts[] = 'Degree: ' . $degree;
        $messageParts[] = 'Department: ' . $department;

        $successTitle   = 'Application Submitted';
        $successMessage = 'Thank you for your interest in an internship with WRC. Our team will review your application and respond within 5-7 business days.';
        break;

    case 'partner':
        $org     = trim($_POST['organization'] ?? '');
        $role    = $_POST['role'] ?? '';
        $type    = trim($_POST['type'] ?? '');
        $interest = trim($_POST['interest'] ?? '');

        $subject     = 'Partnership Inquiry';
        $inquiryType = 'partnership';

        $messageParts[] = 'Organization: ' . $org;
        if ($type !== '') $messageParts[] = 'Partnership Type: ' . $type;
        if ($interest !== '') $messageParts[] = 'Area of Interest: ' . $interest;
        $messageParts[] = 'Role / Designation: ' . $role;
        $messageParts[] = 'Proposal: ' . trim($_POST['message'] ?? '');

        $successTitle   = 'Inquiry Submitted';
        $successMessage = 'Thank you for your interest in partnering with WRC. Our Partnerships team will respond within 3 business days.';
        break;

    case 'membership':
        $address = trim($_POST['address'] ?? '');
        $type    = $_POST['type'] ?? '';

        $subject     = 'Membership Registration';
        $inquiryType = 'membership';

        $messageParts[] = 'Address: ' . $address;
        $messageParts[] = 'Membership Type: ' . $type;

        $successTitle   = 'Welcome Aboard!';
        $successMessage = 'Your membership registration has been received. You will get a confirmation email with your member ID within 48 hours.';
        break;

    case 'donation':
        $amount    = trim($_POST['donation_amount'] ?? '');
        $currency  = strtoupper(trim($_POST['currency'] ?? 'PKR'));
        $frequency = strtolower(trim($_POST['frequency'] ?? 'one-time'));
        $payment   = strtolower(trim($_POST['payment'] ?? 'bank'));
        $causeKey  = strtolower(trim($_POST['cause'] ?? 'general'));
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $reference = trim($_POST['payment_reference'] ?? '');
        $note      = trim($_POST['donation_message'] ?? '');

        if ($amount === '' || !is_numeric($amount) || (float) $amount <= 0) {
            fail('Please choose or enter a donation amount.', 'Amount Required');
        }
        if (!in_array($currency, ['PKR', 'USD'], true)) {
            $currency = 'PKR';
        }
        if (!in_array($frequency, ['one-time', 'monthly', 'quarterly', 'annually'], true)) {
            $frequency = 'one-time';
        }
        $paymentMethods = ['bank', 'easy', 'payoneer', 'taptap'];
        if (!in_array($payment, $paymentMethods, true)) {
            $payment = 'bank';
        }
        $causes = wrcDonationCauses();
        if (!isset($causes[$causeKey])) {
            $causeKey = 'general';
        }
        $cause       = $causes[$causeKey];
        $sectorName  = (string) ($cause['sector'] ?? '');
        $projectTitle = '';

        if (empty($_POST['donation_consent'])) {
            fail('Please confirm the donation details before you continue.', 'Consent Required');
        }

        // Resolve the chosen project from the live portfolio, never from posted
        // text, so a stale form cannot write an invented project name.
        if ($projectId > 0) {
            try {
                $projectStmt = $pdo->prepare('SELECT id, title, category FROM projects WHERE id = ? AND is_active = 1 LIMIT 1');
                $projectStmt->execute([$projectId]);
                $project = $projectStmt->fetch(PDO::FETCH_ASSOC);
                if ($project) {
                    $projectId    = (int) $project['id'];
                    $projectTitle = (string) $project['title'];
                    if ($sectorName === '') {
                        $sectorName = wrcProgrammeSector((string) ($project['category'] ?? ''));
                    }
                }
            } catch (Throwable $e) {
                $projectId = 0;
            }
        } else {
            $projectId = 0;
        }

        // Payment proof: required for wallet and international channels, where
        // the team reconciles the transfer from the screenshot.
        $proofPath = null;
        $proofRequired = in_array($payment, ['easy', 'payoneer', 'taptap'], true);
        if (!empty($_FILES['payment_proof']) && is_array($_FILES['payment_proof'])) {
            $proof = $_FILES['payment_proof'];
            if (($proof['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (($proof['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    fail('The payment screenshot could not be uploaded. Please try a smaller image.', 'Upload Failed');
                }
                $proofPath = uploadPublicAttachment($proof);
                if ($proofPath === null) {
                    fail('Please upload a screenshot as JPG, PNG or PDF up to 20MB.', 'Upload Failed');
                }
            }
        }
        if ($proofRequired && $proofPath === null) {
            fail('Please attach your payment screenshot so we can verify the donation.', 'Screenshot Required');
        }

        $amountValue = round((float) $amount, 2);
        $amountPkr   = wrcDonationAmountPkr($amountValue, $currency);

        $donationRow = [
            'full_name'         => $name,
            'email'             => $email,
            'phone'             => $phone,
            'amount'            => $amountValue,
            'currency'          => $currency,
            'amount_pkr'        => $amountPkr,
            'frequency'         => $frequency,
            'sector_key'        => $causeKey,
            'sector_name'       => $sectorName,
            'project_id'        => $projectId ?: null,
            'project_title'     => $projectTitle !== '' ? $projectTitle : null,
            'payment_method'    => $payment,
            'payment_reference' => $reference !== '' ? $reference : null,
            'message'           => $note !== '' ? $note : null,
            'screenshot_path'   => $proofPath,
            'status'            => 'new',
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        $saved = false;
        try {
            $donationColumns = [];
            foreach ($pdo->query('SHOW COLUMNS FROM donations') as $column) {
                $donationColumns[$column['Field']] = true;
            }
            if ($donationColumns) {
                $insertColumns = array_values(array_filter(
                    array_keys($donationRow),
                    static fn($column) => isset($donationColumns[$column])
                ));
                $placeholders = implode(', ', array_fill(0, count($insertColumns), '?'));
                $insert = $pdo->prepare(
                    'INSERT INTO donations (`' . implode('`, `', $insertColumns) . '`) VALUES (' . $placeholders . ')'
                );
                $insert->execute(array_map(static fn($column) => $donationRow[$column], $insertColumns));
                $saved = true;
            }
        } catch (Throwable $e) {
            error_log('donation insert failed: ' . $e->getMessage());
        }

        // Older installs without the donations table keep the pledge in the
        // submissions inbox so no enquiry is ever lost.
        if (!$saved) {
            $messageParts[] = 'Amount: ' . $currency . ' ' . number_format($amountValue, 2);
            $messageParts[] = 'Frequency: ' . $frequency;
            $messageParts[] = 'Cause: ' . $cause['label'] . ($sectorName !== '' ? ' (' . $sectorName . ')' : '');
            if ($projectTitle !== '') {
                $messageParts[] = 'Project: ' . $projectTitle;
            }
            $messageParts[] = 'Payment Method: ' . wrcPaymentMethodLabel($payment);
            if ($reference !== '') {
                $messageParts[] = 'Transaction Reference: ' . $reference;
            }
            if ($note !== '') {
                $messageParts[] = 'Note: ' . $note;
            }
            if ($proofPath !== null) {
                $messageParts[] = 'Payment Screenshot: ' . $proofPath;
            }
            $subject     = 'Donation Pledge: ' . $currency . ' ' . number_format($amountValue, 2);
            $inquiryType = 'donation';

            $successTitle   = 'Thank You for Your Generosity';
            $successMessage = 'Your pledge has been recorded. Our team will verify your payment and email a receipt.';
            break;
        }

        echo json_encode([
            'success' => true,
            'title'   => 'Thank You for Your Generosity',
            'message' => 'Your donation has been recorded. Our team will verify your payment and send a receipt to ' . $email . ' within 2 working days.',
            'donation' => [
                'amount'   => number_format($amountValue, $currency === 'USD' ? 2 : 0),
                'currency' => $currency,
                'cause'    => $cause['label'],
            ],
        ]);
        exit;

    default:
        fail('Unknown form type.', 'Invalid Form');
}

// ---------------------------------------------
// Optional file attachment (job / internship CV)
// ---------------------------------------------
$uploadKeys = ['cv', 'evidence'];
foreach ($uploadKeys as $key) {
    if (empty($_FILES[$key]) || !is_array($_FILES[$key])) {
        continue;
    }
    $file = $_FILES[$key];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        continue;   // nothing was selected
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        fail('The file upload failed. Please try again with a smaller file.', 'Upload Failed');
    }
    $saved = uploadPublicAttachment($file);
    if ($saved === null) {
        fail('Unsupported file type. Please upload a PDF, DOC, DOCX, JPG or PNG file up to 20MB.', 'Upload Failed');
    }
    $attachment = $saved;
    $messageParts[] = 'Attachment (' . $key . '): ' . $saved;
}

// ---------------------------------------------
// Persist
// ---------------------------------------------
$payload = [
    'full_name'         => $name,
    'email'             => $email,
    'phone'             => $phone,
    'subject'           => $subject,
    'message'           => implode("\n", $messageParts),
    'inquiry_type'      => $inquiryType,
    'volunteer_interest'=> $volunteerFlag,
    'status'            => 'new',
    'submitted_at'      => date('Y-m-d H:i:s'),
];

// Read the table shape once so this endpoint also keeps working on databases
// that have not run database/migration_form_submissions.sql yet.
$schema = [];
try {
    foreach ($pdo->query('SHOW COLUMNS FROM contact_submissions') as $col) {
        $schema[$col['Field']] = (string) $col['Type'];
    }
} catch (Throwable $e) {
    $schema = [];
}

// Keep the uploaded file: prefer the dedicated column, otherwise inline it.
if ($attachment !== null) {
    if (isset($schema['attachment'])) {
        $payload['attachment'] = $attachment;
    } else {
        $payload['message'] .= "\nAttachment: " . $attachment;
    }
}

// Degrade gracefully when the inquiry_type ENUM is still the original one.
if (isset($schema['inquiry_type']) && stripos($schema['inquiry_type'], 'enum(') === 0) {
    $inner = substr($schema['inquiry_type'], 5, -1);              // 'general','program',...
    $allowed = array_map(static function ($v) { return trim($v, "'"); }, explode(',', $inner));
    if (!in_array($inquiryType, $allowed, true)) {
        $payload['inquiry_type'] = 'general';
        $payload['message'] .= "\n(Form type: " . $inquiryType . ")";
    }
}

// Never send a column this table does not have.
foreach (array_keys($payload) as $column) {
    if ($schema && !array_key_exists($column, $schema)) {
        unset($payload[$column]);
    }
}

try {
    insertRow('contact_submissions', $payload);
} catch (Throwable $e) {
    error_log('form-submit.php insert failed: ' . $e->getMessage());
    fail('We could not save your submission right now. Please try again in a moment.');
}

echo json_encode([
    'success' => true,
    'title'   => $successTitle,
    'message' => $successMessage,
]);
