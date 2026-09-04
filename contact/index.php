<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$pageTitle = 'Contact Us & Volunteer - World Relief Committee (WRC)';
$metaDescription = 'Get in touch with World Relief Committee (WRC) for inquiries, volunteer applications, partnerships, or emergency aid requests.';

// Fetch homepage_sections and filter for contact keys
$homepageSections = getRows('homepage_sections', 'is_active = 1 ORDER BY sort_order ASC');
$contactSections = [];
foreach ($homepageSections as $section) {
    $key = $section['section_key'] ?? '';
    if (in_array($key, ['contact_hero', 'contact_content'])) {
        $contactSections[$key] = $section;
        $contactSections[$key]['parsed_content'] = json_decode($section['content'] ?? '{}', true);
    }
}

// Override with dedicated table data
$heroRow = getRow('contact_hero');
if ($heroRow) $contactSections['contact_hero'] = ['section_key' => 'contact_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'parsed_content' => $heroRow];
$contentRow = getRow('contact_content');
if ($contentRow) {
    $decoded = json_decode($contentRow['content'] ?? '{}', true);
    $contactSections['contact_content'] = ['section_key' => 'contact_content', 'section_title' => $contentRow['section_title'], 'section_subtitle' => $contentRow['section_subtitle'], 'parsed_content' => is_array($decoded) ? $decoded : []];
}

// Convenience references
$hero          = $contactSections['contact_hero'] ?? null;
$heroData      = $hero['parsed_content'] ?? [];
$content       = $contactSections['contact_content'] ?? null;
$contentData   = $content['parsed_content'] ?? [];

// Offices data
$offices       = $contentData['offices'] ?? [];
$contactInfo   = $contentData['contact_info'] ?? [];
$feedbackData  = $contentData['feedback'] ?? [];
$whistleData   = $contentData['whistleblower'] ?? [];
$mapEmbed      = $contentData['map_embed'] ?? '';

// Handle contact form submission
$contactSuccess = false;
$feedbackSuccess = false;
$whistleSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'contact') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name && $email && $subject && $message) {
            insertRow('contact_submissions', [
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'type' => 'contact',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $contactSuccess = true;
        }
    } elseif ($formType === 'feedback') {
        $name = trim($_POST['feedback_name'] ?? '');
        $email = trim($_POST['feedback_email'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $rating = (int) ($_POST['rating'] ?? 0);
        $feedbackMsg = trim($_POST['feedback_message'] ?? '');

        if ($category && $feedbackMsg) {
            insertRow('contact_submissions', [
                'name' => $name,
                'email' => $email,
                'subject' => 'Feedback: ' . $category,
                'message' => $feedbackMsg,
                'type' => 'feedback',
                'rating' => $rating,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $feedbackSuccess = true;
        }
    } elseif ($formType === 'whistleblower') {
        $anonymous = isset($_POST['anonymous']) ? 1 : 0;
        $name = $anonymous ? 'Anonymous' : trim($_POST['wb_name'] ?? '');
        $email = $anonymous ? '' : trim($_POST['wb_email'] ?? '');
        $reportType = trim($_POST['report_type'] ?? '');
        $description = trim($_POST['wb_description'] ?? '');

        if ($reportType && $description) {
            insertRow('contact_submissions', [
                'name' => $name,
                'email' => $email,
                'subject' => 'Whistleblower: ' . $reportType,
                'message' => $description,
                'type' => 'whistleblower',
                'is_anonymous' => $anonymous,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $whistleSuccess = true;
        }
    }
    // Redirect to prevent form resubmission
    redirect($_SERVER['PHP_SELF'] . '?submitted=' . $formType);
}

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

  <!-- HERO SECTION -->
  <section class="relative bg-slate-900 py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-30 bg-cover bg-center" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero.jpg' ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 to-slate-900/70"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
      <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2 justify-center">
        <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <span class="text-white">Contact Us</span>
      </nav>
      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/10 text-white border border-white/20 mb-4"><i class="fa-solid fa-envelope text-brandRed"></i> <?= sanitize($heroData['badge'] ?? 'Get In Touch') ?></span>
      <h1 class="text-4xl sm:text-5xl font-extrabold text-white mb-4 font-heading">
        <?= $hero ? sanitize($hero['section_title']) : 'Contact & Feedback Center' ?>
      </h1>
      <p class="text-slate-300 text-base max-w-2xl mx-auto">
        <?= $hero ? sanitize($hero['section_subtitle'] ?? $heroData['subtitle'] ?? '') : 'We value your input. Reach out to us for inquiries, feedback, or to report concerns through our secure channels.' ?>
      </p>
    </div>
  </section>

  <!-- OFFICE LOCATIONS -->
  <section id="offices" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2"><?= sanitize($contentData['offices_subtitle'] ?? 'Our Offices') ?></p>
        <h2 class="text-3xl font-extrabold text-slate-900"><?= sanitize($contentData['offices_title'] ?? 'Office Locations') ?></h2>
        <p class="mt-3 text-slate-500 text-sm max-w-xl mx-auto"><?= sanitize($contentData['offices_description'] ?? 'Visit us at any of our offices across Pakistan. Our doors are open for inquiries, partnerships, and community engagement.') ?></p>
      </div>
      <div class="grid md:grid-cols-3 gap-6">
        <?php foreach ($offices as $office): ?>
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:shadow-lg transition-all group">
          <div class="w-12 h-12 rounded-xl bg-brandRed/10 flex items-center justify-center mb-4 group-hover:bg-brandRed group-hover:text-white transition-all"><i class="fa-solid <?= $office['icon'] ?? 'fa-building' ?> text-brandRed group-hover:text-white text-xl"></i></div>
          <div class="flex items-center gap-2 mb-1">
            <h3 class="font-bold text-slate-900"><?= sanitize($office['name'] ?? '') ?></h3>
            <?php if (!empty($office['badge'])): ?>
            <span class="text-[10px] bg-brandRed text-white px-2 py-0.5 rounded-full font-bold"><?= sanitize($office['badge']) ?></span>
            <?php endif; ?>
          </div>
          <p class="text-sm text-slate-600 mb-4"><?= sanitize($office['address'] ?? '') ?></p>
          <div class="space-y-2 text-sm text-slate-500">
            <?php if (!empty($office['phone'])): ?>
            <div class="flex items-center gap-2"><i class="fa-solid fa-phone text-brandRed text-xs"></i> <?= sanitize($office['phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($office['email'])): ?>
            <div class="flex items-center gap-2"><i class="fa-solid fa-envelope text-brandRed text-xs"></i> <?= sanitize($office['email']) ?></div>
            <?php endif; ?>
            <?php if (!empty($office['hours'])): ?>
            <div class="flex items-center gap-2"><i class="fa-solid fa-clock text-brandRed text-xs"></i> <?= sanitize($office['hours']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CONTACT FORM -->
  <section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-2 gap-12">
        <div>
          <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2"><?= sanitize($contentData['form_subtitle'] ?? 'Send a Message') ?></p>
          <h2 class="text-3xl font-extrabold text-slate-900 mb-4"><?= sanitize($contentData['form_title'] ?? 'Contact Us') ?></h2>
          <p class="text-slate-600 text-sm mb-8"><?= sanitize($contentData['form_description'] ?? 'Have a question or want to learn more about our work? Fill out the form and our team will get back to you within 2 business days.') ?></p>
          <div class="space-y-4">
            <?php foreach ($contactInfo as $info): ?>
            <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200">
              <div class="w-10 h-10 rounded-lg bg-brandRed/10 flex items-center justify-center"><i class="fa-solid <?= $info['icon'] ?? 'fa-phone' ?> text-brandRed"></i></div>
              <div><p class="text-xs text-slate-500 font-medium"><?= sanitize($info['label'] ?? '') ?></p><p class="text-sm font-bold text-slate-900"><?= sanitize($info['value'] ?? '') ?></p></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100">
          <?php if ($contactSuccess || ($_GET['submitted'] ?? '') === 'contact'): ?>
          <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium mb-6"><i class="fa-solid fa-check-circle mr-2"></i>Message sent successfully! We'll get back to you soon.</div>
          <?php endif; ?>
          <form id="contact-form" class="space-y-5" method="POST" action="">
            <input type="hidden" name="form_type" value="contact">
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name <span class="text-brandRed">*</span></label><input type="text" name="name" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Your name"></div>
              <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email <span class="text-brandRed">*</span></label><input type="email" name="email" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="you@email.com"></div>
            </div>
            <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject <span class="text-brandRed">*</span></label><select name="subject" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all text-slate-700"><option value="">Select a subject...</option><?php foreach ($contentData['subjects'] ?? ['General Inquiry','Partnership Proposal','Media Request','Program Information','Other'] as $subject): ?><option value="<?= sanitize($subject) ?>"><?= sanitize($subject) ?></option><?php endforeach; ?></select></div>
            <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Message <span class="text-brandRed">*</span></label><textarea name="message" required rows="5" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all resize-none" placeholder="How can we help you?"></textarea></div>
            <button type="submit" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold py-3.5 rounded-xl transition-colors shadow-md text-sm uppercase tracking-wider">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- FEEDBACK -->
  <section id="feedback" class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-10">
        <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2"><?= sanitize($feedbackData['subtitle'] ?? 'Your Voice Matters') ?></p>
        <h2 class="text-3xl font-extrabold text-slate-900"><?= sanitize($feedbackData['title'] ?? 'Feedback & Complaints') ?></h2>
        <p class="mt-3 text-slate-500 text-sm"><?= sanitize($feedbackData['description'] ?? 'Help us improve our services by sharing your experience or filing a complaint.') ?></p>
      </div>
      <div class="bg-slate-50 p-8 rounded-2xl border border-slate-200">
        <?php if ($feedbackSuccess || ($_GET['submitted'] ?? '') === 'feedback'): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium mb-6"><i class="fa-solid fa-check-circle mr-2"></i>Thank you for your feedback! It helps us serve communities better.</div>
        <?php endif; ?>
        <form id="feedback-form" class="space-y-5" method="POST" action="">
          <input type="hidden" name="form_type" value="feedback">
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Your Name</label><input type="text" name="feedback_name" class="w-full bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Optional"></div>
            <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label><input type="email" name="feedback_email" class="w-full bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Optional"></div>
          </div>
          <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Category <span class="text-brandRed">*</span></label><select name="category" required class="w-full bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all text-slate-700"><option value="">Select category...</option><?php foreach ($feedbackData['categories'] ?? ['General Feedback','Service Quality','Staff Conduct','Suggestion for Improvement','Complaint'] as $cat): ?><option value="<?= sanitize($cat) ?>"><?= sanitize($cat) ?></option><?php endforeach; ?></select></div>
          <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Rating <span class="text-brandRed">*</span></label><div class="flex gap-2" id="rating-stars"><button type="button" onclick="setRating(1)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button><button type="button" onclick="setRating(2)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button><button type="button" onclick="setRating(3)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button><button type="button" onclick="setRating(4)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button><button type="button" onclick="setRating(5)" class="text-2xl text-slate-300 hover:text-yellow-400 transition-colors">&#9733;</button><input type="hidden" id="rating-value" name="rating" required></div></div>
          <div><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Your Feedback <span class="text-brandRed">*</span></label><textarea name="feedback_message" required rows="5" class="w-full bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all resize-none" placeholder="Share your experience or complaint..."></textarea></div>
          <button type="submit" class="w-full bg-slate-900 hover:bg-brandRed text-white font-bold py-3.5 rounded-xl transition-colors text-sm uppercase tracking-wider">Submit Feedback</button>
        </form>
      </div>
    </div>
  </section>

  <!-- WHISTLEBLOWER -->
  <section id="whistleblower" class="py-16 bg-slate-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-500/20 mb-4"><i class="fa-solid fa-shield-halved text-yellow-400 text-2xl"></i></div>
        <h2 class="text-3xl font-extrabold text-white mb-3"><?= sanitize($whistleData['title'] ?? 'Confidential Reporting') ?></h2>
        <p class="text-slate-400 text-sm max-w-xl mx-auto"><?= sanitize($whistleData['description'] ?? 'If you witness corruption, fraud, harassment, or any unethical behavior within WRC, report it securely. Your identity is protected under our Whistleblower Protection Policy.') ?></p>
      </div>
      <div class="bg-slate-800/50 p-8 rounded-2xl border border-slate-700 backdrop-blur">
        <?php if ($whistleSuccess || ($_GET['submitted'] ?? '') === 'whistleblower'): ?>
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl text-sm font-medium mb-6"><i class="fa-solid fa-check-circle mr-2"></i>Your report has been submitted securely. Thank you for helping us maintain integrity.</div>
        <?php endif; ?>
        <form id="whistleblower-form" class="space-y-5" method="POST" action="">
          <input type="hidden" name="form_type" value="whistleblower">
          <div class="flex items-center gap-3 p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-xl"><i class="fa-solid fa-lock text-yellow-400"></i><span class="text-xs text-yellow-300 font-medium"><?= sanitize($whistleData['notice'] ?? 'All reports are encrypted and handled confidentially by our Compliance Team.') ?></span></div>
          <div class="flex items-center gap-3"><input type="checkbox" id="anonymous" name="anonymous" value="1" class="w-4 h-4 text-brandRed rounded border-slate-500 bg-slate-700 focus:ring-brandRed"><label for="anonymous" class="text-sm text-slate-300 font-medium">Submit anonymously (your identity will not be recorded)</label></div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Your Name</label><input type="text" name="wb_name" class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all placeholder:text-slate-500" placeholder="Optional if anonymous"></div>
            <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Email</label><input type="email" name="wb_email" class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all placeholder:text-slate-500" placeholder="For follow-up (optional)"></div>
          </div>
          <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Report Type <span class="text-brandRed">*</span></label><select name="report_type" required class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all"><option value="">Select type...</option><?php foreach ($whistleData['report_types'] ?? ['Corruption','Fraud','Harassment','Misuse of Funds','Child Abuse / Exploitation','Other Ethical Concern'] as $type): ?><option value="<?= sanitize($type) ?>"><?= sanitize($type) ?></option><?php endforeach; ?></select></div>
          <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Description <span class="text-brandRed">*</span></label><textarea name="wb_description" required rows="5" class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all resize-none placeholder:text-slate-500" placeholder="Provide as much detail as possible about the incident..."></textarea></div>
          <div><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Supporting Evidence</label><input type="file" class="w-full bg-slate-700/50 border border-slate-600 px-4 py-3 rounded-xl text-sm text-slate-400 focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-brandRed file:text-white file:text-xs file:font-bold" accept=".pdf,.doc,.docx,.jpg,.png"></div>
          <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-slate-900 font-bold py-3.5 rounded-xl transition-colors text-sm uppercase tracking-wider"><i class="fa-solid fa-paper-plane mr-2"></i>Submit Confidential Report</button>
        </form>
      </div>
    </div>
  </section>

  <!-- MAP -->
  <section class="h-[350px] w-full bg-slate-200">
    <?php if (!empty($mapEmbed)): ?>
      <?= $mapEmbed ?>
    <?php else: ?>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3319.4678854446416!2d71.5818228152063!3d34.02264878069695!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38dfbfd07891722f%3A0x6059515c3bdb02b6!2sPeshawar%2C%20Khyber%20Pakhtunkhwa%2C%20Pakistan!5e0!3m2!1sen!2s!4v1683058882431!5m2!1sen!2s" class="w-full h-full border-0 grayscale opacity-80" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    <?php endif; ?>
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

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
