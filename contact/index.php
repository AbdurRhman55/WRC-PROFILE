<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Contact Us | Welfare & Relief Committee (WRC)';
$metaDescription = 'Contact the Welfare & Relief Committee for inquiries, partnerships, programme information, feedback, or confidential reporting.';

// Fetch hero & content from dedicated tables
$heroRow = getRow('contact_hero');
$contentRow = getRow('contact_content');
$contentData = json_decode($contentRow['content'] ?? '{}', true);

// offices data
$offices      = $contentData['offices'] ?? [];
$contactInfo  = $contentData['contact_info'] ?? [];
$feedbackData = $contentData['feedback'] ?? [];
$whistleData  = $contentData['whistleblower'] ?? [];
$mapEmbed     = $contentData['map_embed'] ?? '';

// Introduction copy (dashboard-managed, approved wording as the fallback).
$introOne     = trim((string) ($contentData['intro1'] ?? '')) ?: "We welcome communication from individuals, communities, government institutions, development partners, donors, and organizations interested in working with WRC.";
$introTwo     = trim((string) ($contentData['intro2'] ?? '')) ?: "Whether you have an inquiry, wish to explore a partnership, need information about our programmes, or would like to share feedback, our team is here to assist you.";
$introThree   = trim((string) ($contentData['intro3'] ?? '')) ?: 'We value open communication and aim to respond to all inquiries timely.';

// Contact form copy (dashboard-managed, approved wording as the fallback).
$formEyebrow     = trim((string) ($contentData['form_subtitle'] ?? '')) ?: 'Contact Us';
$formTitle       = trim((string) ($contentData['form_title'] ?? '')) ?: 'Send Us a Message';
$formDescription = trim((string) ($contentData['form_description'] ?? '')) ?: 'Have a question, need more information about our work, or wish to explore an opportunity to collaborate with WRC? We would be pleased to hear from you.';
$formNote        = trim((string) ($contentData['form_note'] ?? '')) ?: 'Please complete the contact form, and our team will review your inquiry and get back to you as soon as possible.';
$formClosing     = trim((string) ($contentData['form_closing'] ?? '')) ?: "We value your communication and appreciate your interest in WRC's humanitarian and development initiatives.";

// Inquiry Type options and the dashboard filter each one maps to.
$inquiryTypes = array_values(array_filter(array_map('trim', (array) ($contentData['inquiry_types'] ?? [])), 'strlen'));
if (empty($inquiryTypes)) {
    $inquiryTypes = [
        'General Inquiry',
        'Partnership & Collaboration',
        'Project / Programme Inquiry',
        'Donor & Funding Inquiry',
        'Feedback & Suggestions',
        'Complaint / Concern',
        'Other',
    ];
}
$inquiryTypeMap = [
    'General Inquiry'             => 'general',
    'Partnership & Collaboration' => 'partnership',
    'Project / Programme Inquiry' => 'program',
    'Donor & Funding Inquiry'     => 'donation',
    'Feedback & Suggestions'      => 'general',
    'Complaint / Concern'         => 'general',
    'Other'                       => 'general',
];

$heroTitle    = trim((string) ($heroRow['section_title'] ?? '')) ?: 'Contact Us';
$heroSubtitle = trim((string) ($heroRow['section_subtitle'] ?? '')) ?: "Let's Connect and Make a Difference";
$emailAddress = 'info@wrc.org.pk';
foreach ($contactInfo as $infoItem) {
    if (strtolower((string) ($infoItem['label'] ?? '')) === 'email' && !empty($infoItem['value'])) {
        $emailAddress = $infoItem['value'];
        break;
    }
}

// Handle form submissions
$contactSuccess = false;
$feedbackSuccess = false;
$whistleSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'contact') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $organization = trim($_POST['organization'] ?? '');
        $inquiryLabel = trim($_POST['inquiry_type'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $inquiryLabel && $subject && $message) {
            $row = [
                'full_name' => $name, 'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'organization' => $organization !== '' ? $organization : null,
                'inquiry_label' => $inquiryLabel,
                'subject' => $subject,
                'message' => $message, 'inquiry_type' => $inquiryTypeMap[$inquiryLabel] ?? 'general',
                'status' => 'new',
                'submitted_at' => date('Y-m-d H:i:s'),
            ];
            try {
                try {
                    insertRow('contact_submissions', $row);
                } catch (Throwable $e) {
                    // Older database without the organization/label columns.
                    unset($row['organization'], $row['inquiry_label']);
                    insertRow('contact_submissions', $row);
                }
                $contactSuccess = true;
            } catch (Throwable $e) {
                error_log('contact form failed: ' . $e->getMessage());
                $formError = 'We could not save your message right now. Please try again in a moment.';
            }
        } else {
            $formError = 'Please fill in your name, a valid email address, an inquiry type, a subject, and a message.';
        }
    } elseif ($formType === 'feedback') {
        $name = trim($_POST['feedback_name'] ?? '');
        $email = trim($_POST['feedback_email'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $rating = (int) ($_POST['rating'] ?? 0);
        $feedbackMsg = trim($_POST['feedback_message'] ?? '');
        if ($category && $feedbackMsg) {
            try {
                insertRow('contact_submissions', [
                    'full_name' => $name ?: 'Anonymous', 'email' => $email,
                    'subject' => 'Feedback: ' . $category,
                    'message' => $feedbackMsg, 'inquiry_type' => 'general',
                    'rating' => $rating, 'status' => 'new',
                    'submitted_at' => date('Y-m-d H:i:s'),
                ]);
                $feedbackSuccess = true;
            } catch (Throwable $e) {
                error_log('feedback form failed: ' . $e->getMessage());
                $formError = 'We could not save your feedback right now. Please try again in a moment.';
            }
        } else {
            $formError = 'Please choose a category and describe your feedback.';
        }
    } elseif ($formType === 'whistleblower') {
        $anonymous = isset($_POST['anonymous']) ? 1 : 0;
        $name = $anonymous ? 'Anonymous' : trim($_POST['wb_name'] ?? '');
        $email = $anonymous ? '' : trim($_POST['wb_email'] ?? '');
        $reportType = trim($_POST['report_type'] ?? '');
        $description = trim($_POST['wb_description'] ?? '');
        if ($reportType && $description) {
            $attachment = null;
            if (!empty($_FILES['evidence']['error']) && $_FILES['evidence']['error'] !== UPLOAD_ERR_NO_FILE) {
                $attachment = uploadPublicAttachment($_FILES['evidence']);
                if ($attachment === null) {
                    $formError = 'The supporting evidence file could not be uploaded. Please use a PDF, DOC, DOCX, JPG or PNG under 20MB.';
                }
            }
            if ($formError === '') {
                try {
                    insertRow('contact_submissions', [
                        'full_name' => $name, 'email' => $email,
                        'subject' => 'Whistleblower: ' . $reportType,
                        'message' => $description . ($attachment ? "\nEvidence: " . $attachment : ''),
                        'inquiry_type' => 'general', 'is_anonymous' => $anonymous,
                        'status' => 'new',
                        'submitted_at' => date('Y-m-d H:i:s'),
                    ]);
                    $whistleSuccess = true;
                } catch (Throwable $e) {
                    error_log('whistleblower form failed: ' . $e->getMessage());
                    $formError = 'We could not save your report right now. Please try again in a moment.';
                }
            }
        } else {
            $formError = 'Please choose a report type and provide a description.';
        }
    }

    if ($contactSuccess || $feedbackSuccess || $whistleSuccess) {
        redirect($_SERVER['PHP_SELF'] . '?submitted=' . urlencode($formType));
    }
    // Never claim success when nothing was stored.
    redirect($_SERVER['PHP_SELF'] . '?error=' . urlencode($formType));
}

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- HERO SECTION -->
<section class="relative w-full bg-slate-900 text-white overflow-hidden min-h-[480px] md:min-h-[540px] lg:min-h-[580px] flex items-center">
  <div class="absolute inset-0">
    <?php
      $heroImg = $heroRow['hero_image'] ?? 'assets/images/contact-hero.jpg';
      if (strpos($heroImg, 'assets/') !== 0 && strpos($heroImg, 'http') !== 0 && strpos($heroImg, 'uploads/') !== 0) {
          $heroImg = 'assets/images/' . $heroImg;
      }
    ?>
    <img src="<?= $base ?><?= $heroImg ?>" alt="Contact Welfare &amp; Relief Committee" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-950/70 via-slate-900/50 to-brandRed/20"></div>
  </div>
  <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24">
    <div class="max-w-3xl mx-auto text-center">
      <nav class="text-xs text-white/50 mb-6 flex items-center gap-2 justify-center">
        <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <span class="text-white/80">Contact Us</span>
      </nav>
      <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-[11px] font-semibold uppercase tracking-widest px-4 py-2 rounded-full mb-6">
        <i class="fa-solid fa-envelope text-white text-xs"></i> <?= sanitize($heroRow['badge'] ?? 'Get In Touch') ?>
      </div>
      <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-4" style="font-family:'Fraunces',serif;">
        <?= sanitize($heroTitle) ?>
      </h1>
      <p class="text-xl md:text-2xl font-semibold text-white/95 leading-snug max-w-2xl mx-auto">
        <?= sanitize($heroSubtitle) ?>
      </p>
      <div class="mt-6 max-w-3xl mx-auto space-y-3 text-sm md:text-base leading-relaxed text-white/75">
        <p><?= sanitize($introOne) ?></p>
        <p><?= sanitize($introTwo) ?></p>
      </div>
      <div class="mt-4 text-center">
        <p class="inline-flex max-w-2xl items-start gap-2 text-left text-xs md:text-sm text-white/60">
          <i class="fa-solid fa-clock mt-0.5 shrink-0 text-brandRed"></i>
          <span><?= sanitize($introThree) ?></span>
        </p>
      </div>
      <div class="mt-8 flex flex-wrap justify-center gap-4">
        <a href="#offices" class="bg-white text-slate-900 hover:bg-slate-100 font-bold text-sm px-8 py-4 rounded-xl shadow-lg transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
          <i class="fa-solid fa-location-dot text-brandRed"></i> Our Office
        </a>
        <a href="#contact" class="bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold text-sm px-8 py-4 rounded-xl backdrop-blur-sm transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
          <i class="fa-solid fa-paper-plane"></i> Send Message
        </a>
      </div>
    </div>
  </div>
</section>


<!-- OFFICE LOCATION + MAP -->
<section id="offices" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14">
      <span class="text-brandRed text-xs font-bold uppercase tracking-widest"><?= sanitize($contentData['offices_subtitle'] ?? 'Visit Us') ?></span>
      <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-2" style="font-family:'Fraunces',serif;"><?= sanitize($contentData['offices_title'] ?? 'Office Locations') ?></h2>
      <p class="mt-3 text-slate-500 text-sm max-w-xl mx-auto"><?= sanitize($contentData['offices_description'] ?? '') ?></p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
      <?php if (!empty($offices)): ?>
      <?php if (count($offices) === 1): $office = $offices[0]; ?>
      <div class="md:col-span-3 mx-auto w-full max-w-4xl overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-sm">
        <div class="grid gap-8 p-8 sm:p-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:items-center">
          <div>
            <div class="flex items-center gap-4">
              <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brandRed text-white">
                <i class="fa-solid <?= sanitize($office['icon'] ?? 'fa-building') ?> text-xl"></i>
              </span>
              <div>
                <h3 class="text-xl font-bold text-slate-900"><?= sanitize($office['name'] ?? 'Head Office') ?></h3>
                <?php if (!empty($office['badge'])): ?>
                <span class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-brandRed">
                  <i class="fa-solid fa-star text-[9px]"></i> <?= sanitize($office['badge']) ?>
                </span>
                <?php endif; ?>
              </div>
            </div>
            <p class="mt-5 text-sm leading-relaxed text-slate-600"><?= sanitize($office['address'] ?? '') ?></p>
          </div>
          <div class="space-y-3">
            <?php if (!empty($office['phone'])): ?>
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
              <i class="fa-solid fa-phone text-brandRed text-xs w-4"></i> <?= sanitize($office['phone']) ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($office['email'])): ?>
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
              <i class="fa-solid fa-envelope text-brandRed text-xs w-4"></i>
              <a href="mailto:<?= sanitize($office['email']) ?>" class="transition-colors hover:text-brandRed"><?= sanitize($office['email']) ?></a>
            </div>
            <?php endif; ?>
            <?php if (!empty($office['hours'])): ?>
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
              <i class="fa-solid fa-clock text-brandRed text-xs w-4"></i> <?= sanitize($office['hours']) ?>
            </div>
            <?php endif; ?>
            <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode($office['address'] ?? ($office['name'] ?? 'WRC')) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center gap-2 bg-brandRed text-white text-xs font-bold px-5 py-3 rounded-xl hover:bg-brandRed-hover transition-all">
              <i class="fa-solid fa-directions"></i> Get Directions
            </a>
          </div>
        </div>
      </div>
      <?php else: ?>
      <?php foreach ($offices as $office): ?>
      <div class="bg-slate-50 rounded-2xl p-7 border border-slate-200 hover:shadow-xl hover:border-brandRed/20 transition-all flex flex-col">
        <div class="w-12 h-12 rounded-xl bg-brandRed text-white flex items-center justify-center mb-5">
          <i class="fa-solid <?= sanitize($office['icon'] ?? 'fa-building') ?> text-xl"></i>
        </div>
        <div class="flex flex-wrap items-center gap-2 mb-3">
          <h3 class="text-lg font-bold text-slate-900"><?= sanitize($office['name'] ?? 'Office') ?></h3>
          <?php if (!empty($office['badge'])): ?>
          <span class="text-[10px] bg-brandRed text-white px-2.5 py-0.5 rounded-full font-bold"><?= sanitize($office['badge']) ?></span>
          <?php endif; ?>
        </div>
        <p class="text-sm text-slate-600 mb-5 leading-relaxed flex-1"><?= sanitize($office['address'] ?? '') ?></p>
        <div class="space-y-2.5 text-sm text-slate-500 mb-5">
          <?php if (!empty($office['phone'])): ?>
          <div class="flex items-center gap-3"><i class="fa-solid fa-phone text-brandRed text-xs w-4"></i> <?= sanitize($office['phone']) ?></div>
          <?php endif; ?>
          <?php if (!empty($office['email'])): ?>
          <div class="flex items-center gap-3"><i class="fa-solid fa-envelope text-brandRed text-xs w-4"></i> <a href="mailto:<?= sanitize($office['email']) ?>" class="transition-colors hover:text-brandRed"><?= sanitize($office['email']) ?></a></div>
          <?php endif; ?>
          <?php if (!empty($office['hours'])): ?>
          <div class="flex items-center gap-3"><i class="fa-solid fa-clock text-brandRed text-xs w-4"></i> <?= sanitize($office['hours']) ?></div>
          <?php endif; ?>
        </div>
        <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode($office['address'] ?? ($office['name'] ?? 'WRC')) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 bg-brandRed text-white text-xs font-bold px-5 py-2.5 rounded-lg hover:bg-brandRed-hover transition-all mt-auto">
          <i class="fa-solid fa-directions"></i> Get Directions
        </a>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php else: ?>
      <div class="md:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-sm text-slate-500">
        Our office details are being updated. Please use the contact form below or email us at
        <a href="mailto:<?= sanitize($emailAddress) ?>" class="font-semibold text-brandRed hover:underline"><?= sanitize($emailAddress) ?></a>.
      </div>
      <?php endif; ?>
    </div>
    <!-- Map -->
    <div class="mt-8 rounded-2xl overflow-hidden border border-slate-200 min-h-[380px]">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3319.47!2d71.5421574!3d34.0232965!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38d9173ab0306c9f:0x652720dbab04c8e6!2sMarbella+Cave+Wedding+Hall!5e0!3m2!1sen!2s!4v1725000000000!5m2!1sen!2s" class="w-full h-full border-0 min-h-[380px]" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="WRC head office location"></iframe>
    </div>
  </div>
</section>


<!-- CONTACT FORM + INFO -->
<section id="contact" class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-5 gap-12">

      <!-- LEFT: Info -->
      <div class="lg:col-span-2">
        <span class="text-brandRed text-xs font-bold uppercase tracking-widest"><?= sanitize($formEyebrow) ?></span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-2 mb-4" style="font-family:'Fraunces',serif;"><?= sanitize($formTitle) ?></h2>
        <p class="text-slate-600 text-sm leading-relaxed"><?= sanitize($formDescription) ?></p>
        <p class="text-slate-500 text-sm leading-relaxed mt-3"><?= sanitize($formNote) ?></p>
        <div class="mt-6 mb-8 flex items-start gap-3 rounded-2xl border border-brandRed/15 bg-brandRed/5 p-4">
          <i class="fa-solid fa-hand-holding-heart text-brandRed mt-0.5"></i>
          <p class="text-xs leading-relaxed text-slate-600"><?= sanitize($formClosing) ?></p>
        </div>
        <div class="space-y-3">
          <?php foreach ($contactInfo as $info): ?>
          <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 hover:shadow-md transition-all">
            <div class="w-10 h-10 rounded-lg bg-brandRed/10 flex items-center justify-center shrink-0"><i class="fa-solid <?= sanitize($info['icon'] ?? 'fa-phone') ?> text-brandRed"></i></div>
            <div><p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider"><?= sanitize($info['label'] ?? '') ?></p><p class="text-sm font-bold text-slate-900"><?= sanitize($info['value'] ?? '') ?></p></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- RIGHT: Form -->
      <div class="lg:col-span-3">
        <div class="bg-white p-8 md:p-10 rounded-2xl shadow-lg border border-slate-100">
          <?php if ($contactSuccess || ($_GET['submitted'] ?? '') === 'contact'): ?>
          <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium mb-6 flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> Message sent successfully! We'll get back to you soon.</div>
          <?php elseif (($_GET['error'] ?? '') === 'contact'): ?>
          <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium mb-6 flex items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i> Your message could not be sent. Please check the required fields and try again.</div>
          <?php endif; ?>
          <form id="contact-form" class="space-y-5" method="POST" action="">
            <input type="hidden" name="form_type" value="contact">
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label for="contact-name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name <span class="text-brandRed">*</span></label><input type="text" id="contact-name" name="name" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Your full name"></div>
              <div><label for="contact-email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address <span class="text-brandRed">*</span></label><input type="email" id="contact-email" name="email" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="you@example.com"></div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label for="contact-phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number</label><input type="tel" id="contact-phone" name="phone" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="+92 300 1234567"></div>
              <div><label for="contact-organization" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Organization</label><input type="text" id="contact-organization" name="organization" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Your organization (optional)"></div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label for="contact-inquiry" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Inquiry Type <span class="text-brandRed">*</span></label>
                <select id="contact-inquiry" name="inquiry_type" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all text-slate-700">
                  <option value="">Select an inquiry type...</option>
                  <?php foreach ($inquiryTypes as $inquiryType): ?>
                  <option value="<?= sanitize($inquiryType) ?>"><?= sanitize($inquiryType) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div><label for="contact-subject" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject <span class="text-brandRed">*</span></label><input type="text" id="contact-subject" name="subject" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="How can we help?"></div>
            </div>
            <div><label for="contact-message" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Message <span class="text-brandRed">*</span></label><textarea id="contact-message" name="message" required rows="5" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all resize-none" placeholder="Tell us more about your inquiry..."></textarea></div>
            <button type="submit" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold py-3.5 rounded-xl transition-all shadow-md text-sm uppercase tracking-wider flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
              <i class="fa-solid fa-paper-plane text-xs"></i> Send Message
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- FEEDBACK -->
<section id="feedback" class="py-20 bg-white">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10">
      <span class="text-brandRed text-xs font-bold uppercase tracking-widest"><?= sanitize($feedbackData['subtitle'] ?? 'Your Voice Matters') ?></span>
      <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-2" style="font-family:'Fraunces',serif;"><?= sanitize($feedbackData['title'] ?? 'Feedback & Complaints') ?></h2>
      <p class="mt-3 text-slate-500 text-sm"><?= sanitize($feedbackData['description'] ?? '') ?></p>
    </div>
    <div class="bg-slate-50 p-8 md:p-10 rounded-2xl border border-slate-200">
      <?php if ($feedbackSuccess || ($_GET['submitted'] ?? '') === 'feedback'): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium mb-6 flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> Thank you for your feedback! It helps us serve communities better.</div>
      <?php elseif (($_GET['error'] ?? '') === 'feedback'): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium mb-6 flex items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i> Your feedback could not be saved. Please check the fields and try again.</div>
      <?php endif; ?>
      <form id="feedback-form" class="space-y-5" method="POST" action="">
        <input type="hidden" name="form_type" value="feedback">
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Your Name</label><input type="text" name="feedback_name" class="w-full bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Optional"></div>
          <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label><input type="email" name="feedback_email" class="w-full bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Optional"></div>
        </div>
        <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Category <span class="text-brandRed">*</span></label>
          <select name="category" required class="w-full bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all text-slate-700">
            <option value="">Select category...</option>
            <?php foreach ($feedbackData['categories'] ?? ['General Feedback','Service Quality','Staff Conduct','Suggestion for Improvement','Complaint'] as $cat): ?>
            <option value="<?= sanitize($cat) ?>"><?= sanitize($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Rating <span class="text-brandRed">*</span></label>
          <div class="flex gap-2" id="rating-stars">
            <button type="button" onclick="setRating(1)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button>
            <button type="button" onclick="setRating(2)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button>
            <button type="button" onclick="setRating(3)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button>
            <button type="button" onclick="setRating(4)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button>
            <button type="button" onclick="setRating(5)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button>
            <input type="hidden" id="rating-value" name="rating" required>
          </div>
        </div>
        <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Your Feedback <span class="text-brandRed">*</span></label><textarea name="feedback_message" required rows="5" class="w-full bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all resize-none" placeholder="Share your experience or complaint..."></textarea></div>
        <button type="submit" class="w-full bg-slate-900 hover:bg-brandRed text-white font-bold py-3.5 rounded-xl transition-all text-sm uppercase tracking-wider flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
          <i class="fa-solid fa-paper-plane text-xs"></i> Submit Feedback
        </button>
      </form>
    </div>
  </div>
</section>


<!-- WHISTLEBLOWER -->
<section id="whistleblower" class="py-20 bg-slate-900">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-500/20 mb-4"><i class="fa-solid fa-shield-halved text-yellow-400 text-2xl"></i></div>
      <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-3" style="font-family:'Fraunces',serif;"><?= sanitize($whistleData['title'] ?? 'Confidential Reporting') ?></h2>
      <p class="text-slate-400 text-sm max-w-xl mx-auto"><?= sanitize($whistleData['description'] ?? '') ?></p>
    </div>
    <div class="bg-slate-800/50 p-8 md:p-10 rounded-2xl border border-slate-700 backdrop-blur">
      <?php if ($whistleSuccess || ($_GET['submitted'] ?? '') === 'whistleblower'): ?>
      <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl text-sm font-medium mb-6 flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> Your report has been submitted securely. Thank you for helping us maintain integrity.</div>
      <?php elseif (($_GET['error'] ?? '') === 'whistleblower'): ?>
      <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm font-medium mb-6 flex items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i> Your report could not be submitted. Please check the fields and try again.</div>
      <?php endif; ?>
      <form id="whistleblower-form" class="space-y-5" method="POST" action="">
        <input type="hidden" name="form_type" value="whistleblower">
        <div class="flex items-center gap-3 p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-xl"><i class="fa-solid fa-lock text-yellow-400"></i><span class="text-xs text-yellow-300 font-medium"><?= sanitize($whistleData['notice'] ?? 'All reports are encrypted and handled confidentially.') ?></span></div>
        <div class="flex items-center gap-3"><input type="checkbox" id="anonymous" name="anonymous" value="1" class="w-4 h-4 text-brandRed rounded border-slate-500 bg-slate-700 focus:ring-brandRed"><label for="anonymous" class="text-sm text-slate-300 font-medium">Submit anonymously</label></div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Your Name</label><input type="text" name="wb_name" class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all placeholder:text-slate-500" placeholder="Optional if anonymous"></div>
          <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Email</label><input type="email" name="wb_email" class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all placeholder:text-slate-500" placeholder="For follow-up (optional)"></div>
        </div>
        <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Report Type <span class="text-brandRed">*</span></label>
          <select name="report_type" required class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all">
            <option value="">Select type...</option>
            <?php foreach ($whistleData['report_types'] ?? ['Corruption','Fraud','Harassment','Misuse of Funds','Child Abuse / Exploitation','Other Ethical Concern'] as $type): ?>
            <option value="<?= sanitize($type) ?>"><?= sanitize($type) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Description <span class="text-brandRed">*</span></label><textarea name="wb_description" required rows="5" class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all resize-none placeholder:text-slate-500" placeholder="Provide as much detail as possible..."></textarea></div>
        <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Supporting Evidence</label><input type="file" name="evidence" class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-slate-400 focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-brandRed file:text-white file:text-xs file:font-bold" accept=".pdf,.doc,.docx,.jpg,.png"></div>
        <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-slate-900 font-bold py-3.5 rounded-xl transition-all text-sm uppercase tracking-wider flex items-center justify-center gap-2 transform hover:-translate-y-0.5"><i class="fa-solid fa-paper-plane text-xs"></i> Submit Confidential Report</button>
      </form>
    </div>
  </div>
</section>

</main>

<script>
function setRating(val) {
  document.getElementById('rating-value').value = val;
  const stars = document.querySelectorAll('#rating-stars button');
  stars.forEach((btn, i) => {
    if (i < val) {
      btn.classList.remove('text-slate-300');
      btn.classList.add('text-yellow-400');
    } else {
      btn.classList.remove('text-yellow-400');
      btn.classList.add('text-slate-300');
    }
  });
}
</script>

<?php
// Professional confirmation popup shown after the POST + redirect cycle.
$wrcSubmitted = $_GET['submitted'] ?? '';
$wrcFailed    = $_GET['error'] ?? '';
$wrcPopups    = [
    'contact' => [
        'ok'   => ['Message Sent', 'Thank you for reaching out! Our team will review your message and get back to you within 48 hours.', 'contact-form'],
        'fail' => ['Message Not Sent', 'Please check the form fields and try submitting again.', 'contact-form'],
    ],
    'feedback' => [
        'ok'   => ['Thank You for Your Feedback', 'Your feedback has been recorded. It genuinely helps us serve communities better.', 'feedback-form'],
        'fail' => ['Feedback Not Saved', 'Please check the form fields and try submitting again.', 'feedback-form'],
    ],
    'whistleblower' => [
        'ok'   => ['Report Submitted Securely', 'Thank you for helping us maintain integrity. Your report has been logged confidentially.', 'whistleblower-form'],
        'fail' => ['Report Not Submitted', 'Please check the form fields and try submitting again.', 'whistleblower-form'],
    ],
];
if ($wrcSubmitted !== '' || $wrcFailed !== '') {
    $wrcType = $wrcSubmitted !== '' ? $wrcSubmitted : $wrcFailed;
    $wrcFail = $wrcSubmitted === '';
    if (isset($wrcPopups[$wrcType])) {
        $wrcP = $wrcPopups[$wrcType][$wrcFail ? 'fail' : 'ok'];
        $extraScripts = '<script>window.addEventListener("load",function(){'
            . 'WRCModal.' . ($wrcFail ? 'error' : 'success') . '('
            . json_encode($wrcP[0]) . ',' . json_encode($wrcP[1]) . ',function(){'
            . 'var el=document.getElementById(' . json_encode($wrcP[2]) . ');'
            . 'if(el&&el.scrollIntoView)el.scrollIntoView({behavior:"smooth",block:"center"});'
            . '});});</script>';
    }
}
include dirname(__DIR__) . '/includes/footer.php'; ?>
