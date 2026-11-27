<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Get Involved | WRC';
$metaDescription = 'Join Welfare & Relief Committee as a volunteer or support our humanitarian work across Pakistan.';

// Fetch hero & CTA from dedicated tables
$heroRow = getRow('get_involved_hero');
$ctaRow = getRow('get_involved_cta');

// Fetch section data from dedicated tables
$volunteerRow = getRow('get_involved_volunteer');
$volunteerRequirements = json_decode($volunteerRow['requirements'] ?? '[]', true);

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- HERO SECTION -->
<section class="relative w-full bg-slate-900 text-white overflow-hidden min-h-[480px] md:min-h-[540px] flex items-center">
  <div class="absolute inset-0">
    <?php
      $heroImg = $heroRow['hero_image'] ?? 'assets/images/hero/get-involved-hero.jpg';
      if (strpos($heroImg, 'assets/') !== 0 && strpos($heroImg, 'http') !== 0 && strpos($heroImg, 'uploads/') !== 0) {
          $heroImg = 'assets/images/' . $heroImg;
      }
    ?>
    <img src="<?= $base ?><?= $heroImg ?>" alt="Get Involved" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-br from-brandRed/50 via-slate-900/60 to-slate-900/80"></div>
  </div>
  <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-12 lg:py-12">
    <div class="max-w-3xl mx-auto text-center">
      <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-[11px] font-semibold uppercase tracking-widest px-4 py-2 rounded-full mb-6">
        <i class="fa-solid fa-hand-holding-heart text-white text-xs"></i> <?= sanitize($heroRow['badge'] ?? 'Join Our Mission') ?>
      </div>
      <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6" style="font-family:'Fraunces',serif;">
        <?= sanitize($heroRow['section_title'] ?? 'Get Involved') ?>
      </h1>
      <p class="text-lg md:text-xl text-white/75 leading-relaxed mb-10 max-w-2xl mx-auto">
        <?= sanitize($heroRow['section_subtitle'] ?? '') ?>
      </p>
      <div class="flex flex-wrap justify-center gap-4">
        <a href="#volunteer" class="bg-white text-slate-900 hover:bg-slate-100 font-bold text-sm px-8 py-4 rounded-xl shadow-lg transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
          <i class="fa-solid fa-hands-holding-child text-brandRed"></i> <?= sanitize($heroRow['button1_text'] ?? 'Volunteer Now') ?>
        </a>
      </div>
    </div>
  </div>
</section>


<!-- QUICK NAV STRIP -->
<section class="bg-white border-b border-slate-200 sticky top-20 z-40">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex overflow-x-auto gap-1 py-2 scrollbar-hide" style="scrollbar-width:none;">
      <a href="#volunteer" class="shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap"><i class="fa-solid fa-hands-holding-child mr-1.5 text-brandRed"></i> Volunteer</a>
    </div>
  </div>
</section>


<!-- 1. VOLUNTEER REGISTRATION -->
<section id="volunteer" class="py-12 md:py-12 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-16 items-start">

      <!-- LEFT CONTENT -->
      <div>
        <div class="inline-flex items-center gap-2 bg-red-50 text-brandRed text-[11px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-4">
          <i class="fa-solid fa-hands-holding-child"></i> Volunteer
        </div>
        <h2 class="text-3xl md:text-4xl font-bold font-heading text-slate-900 mb-5" style="font-family:'Fraunces',serif;"><?= sanitize($volunteerRow['section_title'] ?? 'Make a Hands-On Difference') ?></h2>
        <p class="text-slate-600 leading-relaxed mb-6"><?= sanitize($volunteerRow['description'] ?? '') ?></p>

        <?php if (!empty($volunteerRequirements)): ?>
        <div class="bg-slate-50 rounded-2xl border border-slate-200/60 p-6 mb-6">
          <h3 class="font-bold text-slate-900 mb-3 text-sm flex items-center gap-2"><i class="fa-solid fa-clipboard-check text-brandRed"></i> Requirements</h3>
          <ul class="space-y-2.5 text-sm text-slate-600">
            <?php foreach ($volunteerRequirements as $req): ?>
            <li class="flex items-start gap-2.5"><i class="fa-solid fa-check text-brandRed mt-0.5 text-xs"></i> <?= sanitize($req) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($volunteerRow['stat1_value'])): ?>
        <div class="grid grid-cols-3 gap-4">
          <div class="bg-red-50 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-brandRed"><?= sanitize($volunteerRow['stat1_value']) ?></p>
            <p class="text-xs text-slate-600 mt-1"><?= sanitize($volunteerRow['stat1_label']) ?></p>
          </div>
          <div class="bg-red-50 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-brandRed"><?= sanitize($volunteerRow['stat2_value']) ?></p>
            <p class="text-xs text-slate-600 mt-1"><?= sanitize($volunteerRow['stat2_label']) ?></p>
          </div>
          <div class="bg-red-50 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-brandRed"><?= sanitize($volunteerRow['stat3_value']) ?></p>
            <p class="text-xs text-slate-600 mt-1"><?= sanitize($volunteerRow['stat3_label']) ?></p>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT FORM -->
      <div class="bg-slate-50 rounded-3xl border border-slate-200/60 p-8 md:p-10 shadow-sm">
        <h3 class="text-xl font-bold text-slate-900 mb-1">Volunteer Registration</h3>
        <p class="text-sm text-slate-500 mb-6">Fill out the form below and our team will reach out within 48 hours.</p>

        <form id="volunteer-form" class="space-y-5" novalidate>
          <div>
            <label for="vol-name" class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name <span class="text-brandRed">*</span></label>
            <input type="text" id="vol-name" name="name" required placeholder="e.g. Ahmed Khan" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div>
            <label for="vol-email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address <span class="text-brandRed">*</span></label>
            <input type="email" id="vol-email" name="email" required placeholder="ahmed@example.com" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div>
            <label for="vol-phone" class="block text-xs font-semibold text-slate-700 mb-1.5">Phone Number <span class="text-brandRed">*</span></label>
            <input type="tel" id="vol-phone" name="phone" required placeholder="+92 3XX XXXXXXX" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div>
            <label for="vol-skills" class="block text-xs font-semibold text-slate-700 mb-1.5">Skills / Interests <span class="text-brandRed">*</span></label>
            <select id="vol-skills" name="skills" required class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all">
              <option value="" disabled selected>Select your skills or interests</option>
              <option value="teaching">Teaching & Education</option>
              <option value="medical">Medical & Healthcare</option>
              <option value="wash">WASH & Sanitation</option>
              <option value="food">Food Distribution</option>
              <option value="admin">Administration & Data Entry</option>
              <option value="logistics">Logistics & Transport</option>
              <option value="fundraising">Fundraising & Outreach</option>
              <option value="it">IT & Digital Support</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label for="vol-availability" class="block text-xs font-semibold text-slate-700 mb-1.5">Availability <span class="text-brandRed">*</span></label>
            <select id="vol-availability" name="availability" required class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all">
              <option value="" disabled selected>Select your availability</option>
              <option value="weekdays">Weekdays (Mon-Fri)</option>
              <option value="weekends">Weekends (Sat-Sun)</option>
              <option value="flexible">Flexible Schedule</option>
              <option value="project">Project-Based Only</option>
            </select>
          </div>
          <button type="submit" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm py-3.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane text-xs"></i> Submit Application
          </button>
        </form>
        <div id="volunteer-success" class="hidden mt-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl p-4 flex items-start gap-3">
          <i class="fa-solid fa-circle-check text-green-600 mt-0.5"></i>
          <div><strong>Thank you!</strong> Your volunteer application has been submitted successfully. We will contact you soon.</div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- CTA BANNER -->
<section class="bg-gradient-to-br from-brandRed to-brandRed-hover py-10">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
    <h2 class="text-2xl md:text-3xl font-bold mb-4" style="font-family:'Fraunces',serif;"><?= sanitize($ctaRow['section_title'] ?? 'Ready to Make an Impact?') ?></h2>
    <p class="text-white/80 text-sm mb-8 max-w-xl mx-auto"><?= sanitize($ctaRow['description'] ?? '') ?></p>
    <div class="flex flex-wrap justify-center gap-3">
      <a href="<?= $base ?><?= $ctaRow['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>" class="bg-white text-brandRed hover:bg-slate-100 font-bold text-sm px-7 py-3.5 rounded-xl shadow-lg transition-all"><i class="fa-solid fa-handshake mr-2"></i><?= sanitize($ctaRow['button1_text'] ?? 'Donate Now') ?></a>
      <a href="#volunteer" class="bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold text-sm px-7 py-3.5 rounded-xl backdrop-blur-sm transition-all"><i class="fa-solid fa-hands-holding-child mr-2"></i><?= sanitize($ctaRow['button2_text'] ?? 'Volunteer') ?></a>
    </div>
  </div>
</section>

</main>

<!-- PAGE-SPECIFIC JS -->
<?php
$extraScripts = <<<SCRIPT
<script>
(function() {

  // --- Generic Form Handler ---
  function handleForm(formId, successId, formType) {
    var form = document.getElementById(formId);
    if (!form) return;
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      var btn = form.querySelector('button[type="submit"]');
      var originalText = btn ? btn.innerHTML : '';
      if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Submitting...';

      var formData = new FormData(form);
      formData.append('form_type', formType);

      fetch(wrcSiteBase() + 'includes/form-submit.php', {
        method: 'POST',
        body: formData
      }).then(function(r) { return r.json(); }).then(function(data) {
        if (btn) btn.innerHTML = originalText;
        if (data.success) {
          form.classList.add('hidden');
          WRCModal.success(data.title, data.message, function() {
            var ok = document.getElementById(successId);
            if (ok) ok.classList.remove('hidden');
          });
        } else {
          WRCModal.error('Submission Failed', data.message || 'Something went wrong. Please try again.');
        }
      }).catch(function() {
        if (btn) btn.innerHTML = originalText;
        WRCModal.error('Network Error', 'We could not reach the server. Please check your connection and try again.');
      });
    });
  }

  handleForm('volunteer-form', 'volunteer-success', 'volunteer');

})();
</script>
SCRIPT;
include dirname(__DIR__) . '/includes/footer.php';
?>