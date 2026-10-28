<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Get Involved | WRC';
$metaDescription = 'Join Welfare & Relief Committee as a volunteer, explore career opportunities, or become a partner or member.';

// Fetch hero & CTA from dedicated tables
$heroRow = getRow('get_involved_hero');
$ctaRow = getRow('get_involved_cta');

// Fetch section data from dedicated tables
$volunteerRow = getRow('get_involved_volunteer');
$volunteerRequirements = json_decode($volunteerRow['requirements'] ?? '[]', true);

$partnerRow = getRow('get_involved_partner');
$partnerTypes = json_decode($partnerRow['partner_types'] ?? '[]', true);

$membershipRow = getRow('get_involved_membership');
$memberBenefits = json_decode($membershipRow['benefits'] ?? '[]', true);
$memberTypes = json_decode($membershipRow['membership_types'] ?? '[]', true);

// Fetch open job listings
$jobs = getRows('job_listings', 'is_active = 1 ORDER BY id ASC');

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
  <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24">
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
        <a href="#careers" class="bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold text-sm px-8 py-4 rounded-xl backdrop-blur-sm transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
          <i class="fa-solid fa-briefcase"></i> <?= sanitize($heroRow['button2_text'] ?? 'View Careers') ?>
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
      <a href="#careers" class="shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap"><i class="fa-solid fa-briefcase mr-1.5 text-brandRed"></i> Careers</a>
      <a href="#partner" class="shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap"><i class="fa-solid fa-handshake-angle mr-1.5 text-brandRed"></i> Partner</a>
      <a href="#membership" class="shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap"><i class="fa-solid fa-id-card-clip mr-1.5 text-brandRed"></i> Membership</a>
    </div>
  </div>
</section>


<!-- 1. VOLUNTEER REGISTRATION -->
<section id="volunteer" class="py-20 md:py-28 bg-white">
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


<!-- 2. CAREERS BOARD -->
<section id="careers" class="py-20 md:py-28 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14">
      <div class="inline-flex items-center gap-2 bg-red-50 text-brandRed text-[11px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-4">
        <i class="fa-solid fa-briefcase"></i> Careers
      </div>
      <h2 class="text-3xl md:text-4xl font-bold font-heading text-slate-900 mb-4" style="font-family:'Fraunces',serif;">Join Our Team</h2>
      <p class="max-w-2xl mx-auto text-slate-600">We're looking for passionate professionals committed to humanitarian service. Build a meaningful career while making an impact on millions of lives.</p>
    </div>

    <div class="space-y-5 max-w-4xl mx-auto">

      <?php if (!empty($jobs)): ?>
        <?php foreach ($jobs as $job):
          $jobContent = json_decode($job['content'] ?? '{}', true);
          $responsibilities = $jobContent['responsibilities'] ?? [];
          $requirements = $jobContent['requirements'] ?? [];
          $jobIcon = $jobContent['icon'] ?? 'fa-briefcase';
        ?>
      <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden career-card">
        <button onclick="toggleCareer(this)" class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
          <div class="flex items-center gap-4 flex-1 min-w-0">
            <div class="w-12 h-12 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center shrink-0 text-lg">
              <i class="fa-solid <?= sanitize($jobIcon) ?>"></i>
            </div>
            <div class="min-w-0">
              <h3 class="font-bold text-slate-900 text-sm"><?= sanitize($job['title']) ?></h3>
              <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                <?php if (!empty($job['location'])): ?>
                <span><i class="fa-solid fa-location-dot mr-1 text-brandRed"></i><?= sanitize($job['location']) ?></span>
                <?php endif; ?>
                <?php if (!empty($job['employment_type'])): ?>
                <span><i class="fa-solid fa-clock mr-1 text-brandRed"></i><?= sanitize($job['employment_type']) ?></span>
                <?php endif; ?>
                <?php if (!empty($job['salary_range'])): ?>
                <span class="font-semibold text-brandRed"><?= sanitize($job['salary_range']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-300 shrink-0 career-chevron"></i>
        </button>
        <div class="career-panel hidden px-6 pb-6 border-t border-slate-100">
          <div class="pt-5 space-y-4">
            <?php if (!empty($job['description'])): ?>
            <div>
              <h4 class="font-semibold text-slate-900 text-xs uppercase tracking-wider mb-2">Role Description</h4>
              <p class="text-sm text-slate-600 leading-relaxed"><?= sanitize($job['description']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($responsibilities)): ?>
            <div>
              <h4 class="font-semibold text-slate-900 text-xs uppercase tracking-wider mb-2">Key Responsibilities</h4>
              <ul class="space-y-1.5 text-sm text-slate-600">
                <?php foreach ($responsibilities as $item): ?>
                <li class="flex items-start gap-2"><i class="fa-solid fa-circle text-[5px] mt-2 text-brandRed"></i> <?= sanitize($item) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
            <?php if (!empty($requirements)): ?>
            <div>
              <h4 class="font-semibold text-slate-900 text-xs uppercase tracking-wider mb-2">Requirements</h4>
              <ul class="space-y-1.5 text-sm text-slate-600">
                <?php foreach ($requirements as $item): ?>
                <li class="flex items-start gap-2"><i class="fa-solid fa-circle text-[5px] mt-2 text-brandRed"></i> <?= sanitize($item) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
            <div class="flex items-center gap-3 pt-2">
              <button onclick="showJobApplyForm('<?= sanitize($job['title']) ?>', '<?= sanitize($job['location'] ?? '') ?>')" class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-xs px-6 py-3 rounded-xl shadow-md transition-all"><i class="fa-solid fa-paper-plane mr-1.5"></i>Apply Now</button>
              <?php if (!empty($job['deadline'])): ?>
              <span class="text-[11px] text-slate-400">Deadline: <span class="font-semibold text-slate-600"><?= formatDate($job['deadline'], 'd M Y') ?></span></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
        <?php endforeach; ?>
      <?php else: ?>
      <div class="text-center py-12 bg-white rounded-2xl border border-slate-200/60">
        <i class="fa-solid fa-briefcase text-4xl text-slate-300 mb-4"></i>
        <p class="text-slate-500 text-sm">No open positions at the moment. Check back soon for new opportunities!</p>
      </div>
      <?php endif; ?>

    </div>

    <!-- JOB APPLICATION FORM (Hidden by default) -->
    <div id="job-apply-section" class="hidden max-w-4xl mx-auto mt-10">
      <div class="bg-white rounded-3xl border border-slate-200/60 p-8 md:p-10 shadow-sm">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-xl font-bold text-slate-900">Apply for <span id="job-apply-title" class="text-brandRed"></span></h3>
            <p class="text-sm text-slate-500">Location: <span id="job-apply-location"></span></p>
          </div>
          <button onclick="hideJobApplyForm()" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>

        <form id="job-apply-form" class="space-y-5" novalidate>
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label for="job-name" class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name <span class="text-brandRed">*</span></label>
              <input type="text" id="job-name" name="name" required placeholder="Your full name" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
            </div>
            <div>
              <label for="job-email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email <span class="text-brandRed">*</span></label>
              <input type="email" id="job-email" name="email" required placeholder="your@email.com" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
            </div>
          </div>
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label for="job-phone" class="block text-xs font-semibold text-slate-700 mb-1.5">Phone <span class="text-brandRed">*</span></label>
              <input type="tel" id="job-phone" name="phone" required placeholder="+92 3XX XXXXXXX" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
            </div>
            <div>
              <label for="job-qualification" class="block text-xs font-semibold text-slate-700 mb-1.5">Highest Qualification <span class="text-brandRed">*</span></label>
              <select id="job-qualification" name="qualification" required class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all">
                <option value="" disabled selected>Select qualification</option>
                <option value="bachelors">Bachelor's Degree</option>
                <option value="masters">Master's Degree</option>
                <option value="phd">PhD</option>
                <option value="diploma">Professional Diploma</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>
          <div>
            <label for="job-experience" class="block text-xs font-semibold text-slate-700 mb-1.5">Years of Experience <span class="text-brandRed">*</span></label>
            <select id="job-experience" name="experience" required class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all">
              <option value="" disabled selected>Select experience</option>
              <option value="0-1">0-1 years</option>
              <option value="1-3">1-3 years</option>
              <option value="3-5">3-5 years</option>
              <option value="5-10">5-10 years</option>
              <option value="10+">10+ years</option>
            </select>
          </div>
          <div>
            <label for="job-cv" class="block text-xs font-semibold text-slate-700 mb-1.5">Upload CV (PDF) <span class="text-brandRed">*</span></label>
            <input type="file" id="job-cv" name="cv" accept=".pdf,.doc,.docx" required class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-brandRed file:text-white file:text-xs file:font-semibold file:cursor-pointer focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div>
            <label for="job-cover" class="block text-xs font-semibold text-slate-700 mb-1.5">Cover Letter</label>
            <textarea id="job-cover" name="cover" rows="4" placeholder="Tell us why you're a great fit for this role..." class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all resize-none"></textarea>
          </div>
          <button type="submit" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm py-3.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane text-xs"></i> Submit Application
          </button>
        </form>
        <div id="job-success" class="hidden mt-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl p-4 flex items-start gap-3">
          <i class="fa-solid fa-circle-check text-green-600 mt-0.5"></i>
          <div><strong>Application submitted!</strong> We will review your application and get back to you within 5-7 business days.</div>
        </div>
      </div>
    </div>

  </div>
</section>


<!-- 3. BECOME A PARTNER -->
<section id="partner" class="py-20 md:py-28 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-16 items-start">

      <!-- LEFT: INFO -->
      <div>
        <div class="inline-flex items-center gap-2 bg-red-50 text-brandRed text-[11px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-4">
          <i class="fa-solid fa-handshake-angle"></i> Partnerships
        </div>
        <h2 class="text-3xl md:text-4xl font-bold font-heading text-slate-900 mb-5" style="font-family:'Fraunces',serif;"><?= sanitize($partnerRow['section_title'] ?? 'Become a Partner') ?></h2>
        <p class="text-slate-600 leading-relaxed mb-6"><?= sanitize($partnerRow['description'] ?? '') ?></p>

        <?php if (!empty($partnerTypes)): ?>
        <div class="grid sm:grid-cols-2 gap-4 mb-6">
          <?php foreach ($partnerTypes as $pt): ?>
          <div class="bg-slate-50 rounded-xl border border-slate-200/60 p-5 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-lg bg-brandRed/10 text-brandRed flex items-center justify-center mb-3 text-lg"><i class="fa-solid <?= sanitize($pt['icon'] ?? 'fa-handshake') ?>"></i></div>
            <h4 class="font-bold text-slate-900 text-sm mb-1"><?= sanitize($pt['title'] ?? '') ?></h4>
            <p class="text-xs text-slate-600 leading-relaxed"><?= sanitize($pt['description'] ?? '') ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT: FORM -->
      <div class="bg-slate-50 rounded-3xl border border-slate-200/60 p-8 md:p-10 shadow-sm">
        <h3 class="text-xl font-bold text-slate-900 mb-1">Partnership Inquiry</h3>
        <p class="text-sm text-slate-500 mb-6">Share your details and partnership idea. Our team will respond within 3 business days.</p>

        <form id="partner-form" class="space-y-5" novalidate>
          <div>
            <label for="p-org" class="block text-xs font-semibold text-slate-700 mb-1.5">Organization Name <span class="text-brandRed">*</span></label>
            <input type="text" id="p-org" name="organization" required placeholder="e.g. Acme Foundation" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label for="p-contact" class="block text-xs font-semibold text-slate-700 mb-1.5">Contact Person <span class="text-brandRed">*</span></label>
              <input type="text" id="p-contact" name="contact" required placeholder="Full name" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
            </div>
            <div>
              <label for="p-email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email <span class="text-brandRed">*</span></label>
              <input type="email" id="p-email" name="email" required placeholder="contact@org.com" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
            </div>
          </div>
          <div>
            <label for="p-type" class="block text-xs font-semibold text-slate-700 mb-1.5">Partnership Type <span class="text-brandRed">*</span></label>
            <select id="p-type" name="type" required class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all">
              <option value="" disabled selected>Select partnership type</option>
              <option value="institutional">Institutional / Academic</option>
              <option value="corporate">Corporate / CSR</option>
              <option value="ngo">International NGO</option>
              <option value="donor">Donor / Funding Agency</option>
              <option value="government">Government Agency</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label for="p-message" class="block text-xs font-semibold text-slate-700 mb-1.5">Message / Proposal <span class="text-brandRed">*</span></label>
            <textarea id="p-message" name="message" rows="4" required placeholder="Describe your partnership idea or inquiry..." class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all resize-none"></textarea>
          </div>
          <button type="submit" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm py-3.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane text-xs"></i> Submit Inquiry
          </button>
        </form>
        <div id="partner-success" class="hidden mt-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl p-4 flex items-start gap-3">
          <i class="fa-solid fa-circle-check text-green-600 mt-0.5"></i>
          <div><strong>Inquiry submitted!</strong> Thank you for your interest. Our Partnerships team will reach out shortly.</div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- 4. MEMBERSHIP -->
<section id="membership" class="py-20 md:py-28 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-16 items-start">

      <!-- LEFT: INFO -->
      <div>
        <div class="inline-flex items-center gap-2 bg-red-50 text-brandRed text-[11px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full mb-4">
          <i class="fa-solid fa-id-card-clip"></i> Membership
        </div>
        <h2 class="text-3xl md:text-4xl font-bold font-heading text-slate-900 mb-5" style="font-family:'Fraunces',serif;"><?= sanitize($membershipRow['section_title'] ?? 'Become a WRC Member') ?></h2>
        <p class="text-slate-600 leading-relaxed mb-6"><?= sanitize($membershipRow['description'] ?? '') ?></p>

        <?php if (!empty($memberBenefits)): ?>
        <div class="space-y-4 mb-6">
          <?php foreach ($memberBenefits as $b): ?>
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-brandRed/10 text-brandRed flex items-center justify-center shrink-0"><i class="fa-solid <?= sanitize($b['icon'] ?? 'fa-check') ?> text-sm"></i></div>
            <div>
              <h4 class="font-bold text-slate-900 text-sm"><?= sanitize($b['title'] ?? '') ?></h4>
              <p class="text-xs text-slate-600 mt-0.5"><?= sanitize($b['description'] ?? '') ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($memberTypes)): ?>
        <div class="bg-white rounded-2xl border border-slate-200/60 p-6">
          <h4 class="font-bold text-slate-900 text-sm mb-3">Membership Types</h4>
          <div class="space-y-3">
            <?php foreach ($memberTypes as $mt): ?>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-sm text-slate-700"><i class="fa-solid fa-user text-brandRed text-xs"></i> <?= sanitize($mt['type'] ?? '') ?></div>
              <span class="text-xs font-semibold text-slate-500"><?= sanitize($mt['price'] ?? '') ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT: FORM -->
      <div class="bg-white rounded-3xl border border-slate-200/60 p-8 md:p-10 shadow-sm">
        <h3 class="text-xl font-bold text-slate-900 mb-1">Membership Registration</h3>
        <p class="text-sm text-slate-500 mb-6">Complete the form below to become a registered WRC member.</p>

        <form id="membership-form" class="space-y-5" novalidate>
          <div>
            <label for="mem-name" class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name <span class="text-brandRed">*</span></label>
            <input type="text" id="mem-name" name="name" required placeholder="e.g. Fatima Bibi" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div>
            <label for="mem-email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address <span class="text-brandRed">*</span></label>
            <input type="email" id="mem-email" name="email" required placeholder="fatima@example.com" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div>
            <label for="mem-phone" class="block text-xs font-semibold text-slate-700 mb-1.5">Phone Number <span class="text-brandRed">*</span></label>
            <input type="tel" id="mem-phone" name="phone" required placeholder="+92 3XX XXXXXXX" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div>
            <label for="mem-address" class="block text-xs font-semibold text-slate-700 mb-1.5">Address <span class="text-brandRed">*</span></label>
            <input type="text" id="mem-address" name="address" required placeholder="City, Province" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all" />
          </div>
          <div>
            <label for="mem-type" class="block text-xs font-semibold text-slate-700 mb-1.5">Membership Type <span class="text-brandRed">*</span></label>
            <select id="mem-type" name="type" required class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-brandRed/30 focus:border-brandRed transition-all">
              <option value="" disabled selected>Select membership type</option>
              <option value="individual">Individual</option>
              <option value="family">Family</option>
              <option value="organizational">Organizational</option>
              <option value="lifetime">Lifetime</option>
            </select>
          </div>
          <button type="submit" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm py-3.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-id-card text-xs"></i> Register as Member
          </button>
        </form>
        <div id="membership-success" class="hidden mt-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl p-4 flex items-start gap-3">
          <i class="fa-solid fa-circle-check text-green-600 mt-0.5"></i>
          <div><strong>Welcome aboard!</strong> Your membership registration has been received. You'll receive a confirmation email with your member ID within 48 hours.</div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- CTA BANNER -->
<section class="bg-gradient-to-br from-brandRed to-brandRed-hover py-16">
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

  // --- Careers Accordion ---
  window.toggleCareer = function(btn) {
    var card = btn.closest('.career-card');
    var panel = card.querySelector('.career-panel');
    var chevron = card.querySelector('.career-chevron');
    var isOpen = !panel.classList.contains('hidden');

    document.querySelectorAll('.career-card').forEach(function(c) {
      c.querySelector('.career-panel').classList.add('hidden');
      c.querySelector('.career-chevron').style.transform = 'rotate(0deg)';
    });

    if (!isOpen) {
      panel.classList.remove('hidden');
      chevron.style.transform = 'rotate(180deg)';
    }
  };

  // --- Job Apply Form ---
  window.showJobApplyForm = function(title, location) {
    document.getElementById('job-apply-title').textContent = title;
    document.getElementById('job-apply-location').textContent = location;
    document.getElementById('job-apply-section').classList.remove('hidden');
    document.getElementById('job-success').classList.add('hidden');
    document.getElementById('job-apply-form').reset();
    document.getElementById('job-apply-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
  };

  window.hideJobApplyForm = function() {
    document.getElementById('job-apply-section').classList.add('hidden');
  };

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
  handleForm('partner-form', 'partner-success', 'partner');
  handleForm('membership-form', 'membership-success', 'membership');

  // Job apply form special handler
  var jobForm = document.getElementById('job-apply-form');
  if (jobForm) {
    jobForm.addEventListener('submit', function(e) {
      e.preventDefault();
      if (!jobForm.checkValidity()) { jobForm.reportValidity(); return; }
      var btn = jobForm.querySelector('button[type="submit"]');
      var originalText = btn ? btn.innerHTML : '';
      if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Submitting...';

      var formData = new FormData(jobForm);
      formData.append('form_type', 'job_apply');
      formData.append('position', document.getElementById('job-apply-title').textContent);

      fetch(wrcSiteBase() + 'includes/form-submit.php', {
        method: 'POST',
        body: formData
      }).then(function(r) { return r.json(); }).then(function(data) {
        if (btn) btn.innerHTML = originalText;
        if (data.success) {
          document.getElementById('job-apply-section').classList.add('hidden');
          WRCModal.success(data.title, data.message, function() {
            document.getElementById('job-success').classList.remove('hidden');
          });
        } else {
          WRCModal.error('Application Failed', data.message || 'Something went wrong.');
        }
      }).catch(function() {
        if (btn) btn.innerHTML = originalText;
        WRCModal.error('Network Error', 'We could not reach the server. Please check your connection and try again.');
      });
    });
  }

})();
</script>
SCRIPT;
include dirname(__DIR__) . '/includes/footer.php';
?>