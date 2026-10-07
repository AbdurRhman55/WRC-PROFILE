<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Impact & SDG Contributions | WRC';

// Fetch homepage_sections for impact page
$homepageSections = getRows('homepage_sections', 'is_active = 1 ORDER BY sort_order ASC');
$impactSections = [];
foreach ($homepageSections as $section) {
    $key = $section['section_key'] ?? '';
    if (in_array($key, ['impact_hero', 'impact_content'])) {
        $impactSections[$key] = $section;
        $impactSections[$key]['parsed_content'] = json_decode($section['content'] ?? '{}', true);
    }
}

// Override with dedicated table data
$heroRow = getRow('impact_hero');
if ($heroRow) $impactSections['impact_hero'] = ['section_key' => 'impact_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'parsed_content' => $heroRow];
$contentRow = getRow('impact_content');
if ($contentRow) {
    $decoded = json_decode($contentRow['content'] ?? '{}', true);
    $impactSections['impact_content'] = ['section_key' => 'impact_content', 'section_title' => $contentRow['section_title'], 'section_subtitle' => $contentRow['section_subtitle'], 'parsed_content' => is_array($decoded) ? $decoded : []];
}

// Convenience references
$hero        = $impactSections['impact_hero'] ?? null;
$heroData    = $hero['parsed_content'] ?? [];
$content     = $impactSections['impact_content'] ?? null;
$contentData = $content['parsed_content'] ?? [];

// Dashboard stats
$stats = $contentData['stats'] ?? [
    ['icon' => 'fa-heart-pulse', 'color' => 'brandRed', 'target' => '150000', 'label' => 'Lives Impacted', 'sub' => 'Across 4 provinces since inception'],
    ['icon' => 'fa-faucet-drip', 'color' => 'blue', 'target' => '1200', 'label' => 'Water Wells Installed', 'sub' => 'Clean water access for rural families'],
    ['icon' => 'fa-bowl-food', 'color' => 'amber', 'target' => '85000', 'label' => 'Food Packages Distributed', 'sub' => 'Emergency food relief to families in need'],
    ['icon' => 'fa-stethoscope', 'color' => 'green', 'target' => '12000', 'label' => 'Healthcare Visits', 'sub' => 'Free medical camps & clinical services'],
    ['icon' => 'fa-graduation-cap', 'color' => 'purple', 'target' => '45000', 'label' => 'Children Enrolled', 'sub' => 'Schools & non-formal learning centers'],
    ['icon' => 'fa-map-location-dot', 'color' => 'teal', 'target' => '200', 'label' => 'Communities Served', 'sub' => 'Rural & underserved regions reached'],
];

// M&E steps
$mneSteps = $contentData['mne_steps'] ?? [
    ['label' => 'Step 01', 'title' => 'Third-Party Audits', 'desc' => 'Independent auditors verify financial integrity, program delivery, and beneficiary outcomes against internationally recognized standards.', 'icon' => 'fa-stamp', 'side' => 'left'],
    ['label' => 'Step 02', 'title' => 'Real-Time Data Tracking', 'desc' => 'Digital dashboards capture field data as it happens, enabling rapid response to emerging needs and evidence-based decision making.', 'icon' => 'fa-database', 'side' => 'right'],
    ['label' => 'Step 03', 'title' => 'Quality Standards (ISO Compliance)', 'desc' => 'Programs are aligned with ISO 9001 and ISO 22000 quality management systems to ensure consistent, reliable delivery across all sectors.', 'icon' => 'fa-certificate', 'side' => 'left'],
    ['label' => 'Step 04', 'title' => 'Community Feedback Loops', 'desc' => 'Beneficiary satisfaction surveys, community scorecards, and grievance mechanisms ensure programs remain responsive and community-driven.', 'icon' => 'fa-comments', 'side' => 'right'],
    ['label' => 'Step 05', 'title' => 'Annual Impact Assessments', 'desc' => 'Comprehensive annual evaluations synthesize data across sectors to measure long-term outcomes and inform strategic planning.', 'icon' => 'fa-chart-line', 'side' => 'left'],
];

// SDGs
$sdgs = $contentData['sdgs'] ?? [
    ['number' => '3', 'title' => 'Good Health & Well-Being', 'desc' => 'WRC operates free medical camps, maternal health clinics, and emergency healthcare services reaching underserved populations across Pakistan.', 'color' => 'green-600', 'bg' => 'green', 'progress' => '78', 'tags' => ['12,000+ Healthcare Visits', '45 Medical Camps'], 'icon' => 'fa-heart-pulse'],
    ['number' => '4', 'title' => 'Quality Education', 'desc' => 'Through schools, non-formal learning centers, and orphan education sponsorships, WRC ensures children access quality learning opportunities.', 'color' => 'red-600', 'bg' => 'red', 'progress' => '72', 'tags' => ['45,000+ Children Enrolled', '120 Learning Centers'], 'icon' => 'fa-graduation-cap'],
    ['number' => '6', 'title' => 'Clean Water & Sanitation', 'desc' => 'WRC installs hand pumps, deep tube wells, and water purification systems, while training communities in hygiene practices and sanitation management.', 'color' => 'blue-600', 'bg' => 'blue', 'progress' => '85', 'tags' => ['1,200+ Water Wells', '180 WASH Committees'], 'icon' => 'fa-faucet-drip'],
    ['number' => '13', 'title' => 'Climate Action', 'desc' => 'WRC integrates climate resilience into disaster preparedness, promotes renewable energy access, and supports reforestation and environmental restoration projects.', 'color' => 'emerald-700', 'bg' => 'emerald', 'progress' => '64', 'tags' => ['50,000 Trees Planted', '15 Solar Installations'], 'icon' => 'fa-leaf'],
];

// Case Studies
$caseStudies = $contentData['case_studies'] ?? [
    [
        'image' => '../assets/images/water.jpg',
        'category' => 'WASH',
        'categoryColor' => 'blue-500',
        'title' => 'Clean Water for Nowshera: A Community Transformed',
        'desc' => 'In 2023, WRC installed 35 deep tube wells across 12 villages in Nowshera District, providing clean drinking water to over 8,000 residents previously dependent on contaminated sources.',
        'stats' => [['val' => '8,200', 'label' => 'People Served'], ['val' => '35', 'label' => 'Wells Built'], ['val' => '12', 'label' => 'Villages']],
        'quote' => '"Before the wells, my children walked 3 km daily for water that made them sick. Now they have clean water at school and more time to study."',
        'author' => '— Fatima Bibi, Mother of 4, Nowshera',
        'outcome' => 'Outcome: Waterborne diseases reduced by 62% in target villages.',
    ],
    [
        'image' => '../assets/images/hero-slider-3.jpg',
        'category' => 'Education',
        'categoryColor' => 'red-600',
        'title' => 'Bright Futures: Orphan Education in Peshawar',
        'desc' => 'WRC\'s orphan sponsorship program enrolled 1,200 children in Peshawar, providing school kits, uniforms, tuition support, and after-school tutoring for 18 months.',
        'stats' => [['val' => '1,200', 'label' => 'Children Enrolled'], ['val' => '92%', 'label' => 'Pass Rate'], ['val' => '8', 'label' => 'Schools']],
        'quote' => '"I want to become a doctor. WRC helped me stay in school when my family could not afford it. I am now first in my class."',
        'author' => '— Ayesha Khan, Student, Grade 8',
        'outcome' => 'Outcome: 92% exam pass rate; dropout rate reduced from 34% to 4%.',
    ],
    [
        'image' => '../assets/images/food.jpg',
        'category' => 'Food Security',
        'categoryColor' => 'amber-500',
        'title' => 'Emergency Flood Relief in Sindh: Feeding Thousands',
        'desc' => 'During the 2023 Pakistan floods, WRC distributed 12,000 emergency food packages and set up 8 community kitchens in Sukkhur and Dadu districts within 72 hours.',
        'stats' => [['val' => '12,000', 'label' => 'Food Packages'], ['val' => '48,000', 'label' => 'People Fed'], ['val' => '72 hrs', 'label' => 'Response Time']],
        'quote' => '"When the flood destroyed everything, WRC arrived with food for our entire village within three days. They saved us from starvation."',
        'author' => '— Muhammad Raza, Farmer, Sukkur',
        'outcome' => 'Outcome: Zero malnutrition deaths in target areas; 100% distribution transparency.',
    ],
];

// CTA Section
$ctaRow = getRow('homepage_sections', ['section_key' => 'impact_cta']);
$ctaData = $ctaRow ? json_decode($ctaRow['content'] ?? '{}', true) : [];
$ctaTitle = $ctaRow['section_title'] ?? 'Your Support Fuels Every Number';
$ctaSubtitle = $ctaRow['section_subtitle'] ?? 'Behind every statistic is a family, a child, a community whose future is shaped by your generosity. Join us in turning data into dignity.';

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- HERO SECTION -->
<section class="relative w-full bg-slate-900 text-white min-h-[480px] md:min-h-[540px] lg:min-h-[580px] flex items-center overflow-hidden">
  <div class="absolute inset-0 z-0">
    <div class="absolute inset-0 opacity-50 bg-cover bg-center" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/impact-hero.jpg' ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-brandRed/60 via-slate-900/50 to-slate-950/80"></div>
  </div>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-16 md:py-20 lg:py-24">
    <div class="max-w-3xl">
      <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2">
        <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <span class="text-white">Impact</span>
      </nav>
      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/10 backdrop-blur-sm text-white border border-white/20 mb-6">
        <i class="fa-solid fa-chart-column text-white"></i> <?= $hero ? sanitize($hero['section_title']) : 'Impact & SDG Center' ?>
      </span>
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6 font-heading drop-shadow-md">
        <?= $hero ? sanitize($heroData['heading'] ?? 'Measuring What Matters,<br>Changing What Counts.') : 'Measuring What Matters,<br>Changing What Counts.' ?>
      </h1>
      <p class="text-base sm:text-lg text-white/80 mb-8 leading-relaxed drop-shadow-sm max-w-2xl">
        <?= $hero ? sanitize($heroData['description'] ?? $hero['section_subtitle'] ?? 'Every data point represents a life touched. Our Impact & SDG Center tracks outcomes, ensures accountability, and drives continuous improvement across every WRC program.') : 'Every data point represents a life touched. Our Impact & SDG Center tracks outcomes, ensures accountability, and drives continuous improvement across every WRC program.' ?>
      </p>
      <div class="flex flex-wrap gap-4 items-center">
        <a href="#dashboard" class="bg-white hover:bg-slate-100 text-slate-900 font-bold text-sm px-7 py-3.5 rounded-xl shadow-lg transition-all flex items-center gap-2 transform hover:-translate-y-1">
          <i class="fa-solid fa-arrow-down"></i> <?= $heroData['cta_primary'] ?? 'View Impact Dashboard' ?>
        </a>
        <a href="#sdgs" class="bg-white/10 hover:bg-white/25 backdrop-blur-md text-white border border-white/30 font-semibold text-sm px-6 py-3.5 rounded-xl transition-all flex items-center gap-2 transform hover:-translate-y-1">
          <i class="fa-solid fa-globe"></i> <?= $heroData['cta_secondary'] ?? 'SDG Contributions' ?>
        </a>
      </div>
    </div>
  </div>
</section>


<!-- IMPACT METRICS DASHBOARD -->
<section id="dashboard" class="bg-slate-950 py-16 lg:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 lg:mb-16">
      <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-3">Our Impact in Numbers</p>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Impact Metrics Dashboard</h2>
      <p class="mt-4 mx-auto max-w-2xl text-slate-400 text-sm sm:text-base leading-7">Real-time tracking of WRC's reach, outcomes, and community transformation across all program sectors.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 lg:gap-8">
      <?php foreach ($stats as $stat): ?>
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-[2rem] p-6 lg:p-8 text-center group hover:bg-white/10 hover:border-brandRed/30 transition-all duration-300">
        <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-<?= $stat['color'] ?>/15 flex items-center justify-center group-hover:scale-110 transition-transform">
          <i class="fa-solid <?= $stat['icon'] ?> text-2xl text-<?= $stat['color'] ?>-400"></i>
        </div>
        <div class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-2 font-heading counter-val" data-target="<?= $stat['target'] ?>">0</div>
        <p class="text-slate-400 text-sm font-medium"><?= sanitize($stat['label']) ?></p>
        <p class="text-slate-500 text-xs mt-1"><?= sanitize($stat['sub']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- MONITORING & EVALUATION FRAMEWORK -->
<section id="mne" class="bg-white py-16 lg:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 lg:mb-16">
      <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-3">How We Ensure Quality</p>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Monitoring &amp; Evaluation Framework</h2>
      <p class="mt-4 mx-auto max-w-2xl text-slate-600 text-sm sm:text-base leading-7">A rigorous, multi-layered M&amp;E system that tracks every intervention from delivery to long-term outcome.</p>
    </div>

    <div class="relative max-w-4xl mx-auto">
      <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-px -translate-x-1/2 bg-slate-200"></div>
      <div class="md:hidden absolute left-[19px] top-0 bottom-0 w-px bg-slate-200"></div>

      <div class="space-y-10 md:space-y-0">
        <?php foreach ($mneSteps as $i => $step): ?>
        <div class="relative md:grid md:grid-cols-2 md:gap-x-16 md:items-center <?= $i < count($mneSteps) - 1 ? 'md:pb-16' : '' ?>">
          <?php if ($step['side'] === 'left'): ?>
          <div class="pl-14 md:pl-0 md:pr-16 md:text-right">
            <span class="text-sm uppercase tracking-[0.28em] font-semibold text-brandRed"><?= sanitize($step['label']) ?></span>
            <h3 class="mt-2 text-xl md:text-2xl font-semibold text-slate-900"><?= sanitize($step['title']) ?></h3>
            <p class="mt-3 text-sm md:text-[15px] text-slate-600 leading-relaxed"><?= sanitize($step['desc']) ?></p>
          </div>
          <div class="hidden md:block"></div>
          <?php else: ?>
          <div class="hidden md:block"></div>
          <div class="pl-14 md:pl-16">
            <span class="text-sm uppercase tracking-[0.28em] font-semibold text-brandRed"><?= sanitize($step['label']) ?></span>
            <h3 class="mt-2 text-xl md:text-2xl font-semibold text-slate-900"><?= sanitize($step['title']) ?></h3>
            <p class="mt-3 text-sm md:text-[15px] text-slate-600 leading-relaxed"><?= sanitize($step['desc']) ?></p>
          </div>
          <?php endif; ?>
          <span class="absolute left-[13px] md:left-1/2 top-1 md:top-1/2 -translate-x-1/2 md:-translate-y-1/2 w-10 h-10 rounded-full bg-brandRed flex items-center justify-center z-10 shadow-lg shadow-brandRed/30">
            <i class="fa-solid <?= $step['icon'] ?> text-white text-sm"></i>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>


<!-- SDG CONTRIBUTION MAP -->
<section id="sdgs" class="bg-slate-50 py-16 lg:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 lg:mb-16">
      <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-3">Global Goals Alignment</p>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">SDG Contribution Map</h2>
      <p class="mt-4 mx-auto max-w-2xl text-slate-600 text-sm sm:text-base leading-7">WRC programs directly contribute to the United Nations Sustainable Development Goals, creating measurable progress toward a more equitable world.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
      <?php foreach ($sdgs as $sdg): ?>
      <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden group hover:shadow-xl transition-all duration-300">
        <div class="flex items-stretch">
          <div class="w-28 sm:w-32 shrink-0 bg-<?= $sdg['bg'] ?>-600 flex flex-col items-center justify-center p-4 text-white">
            <i class="fa-solid <?= $sdg['icon'] ?> text-3xl sm:text-4xl mb-2"></i>
            <span class="text-xs font-bold uppercase tracking-wider">SDG <?= sanitize($sdg['number']) ?></span>
            <span class="text-[10px] text-white/80 text-center mt-1 leading-tight"><?= nl2br(sanitize($sdg['title'])) ?></span>
          </div>
          <div class="flex-1 p-5 sm:p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-2"><?= sanitize($sdg['title']) ?></h3>
            <p class="text-slate-600 text-sm leading-6 mb-4"><?= sanitize($sdg['desc']) ?></p>
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">Progress toward target</span>
                <span class="font-semibold text-<?= $sdg['color'] ?>"><?= sanitize($sdg['progress']) ?>%</span>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-2">
                <div class="bg-<?= $sdg['color'] ?> h-2 rounded-full animate-[progress_1.5s_ease-out_forwards]" style="width: <?= (int) $sdg['progress'] ?>%;"></div>
              </div>
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
              <?php foreach ($sdg['tags'] as $tag): ?>
              <span class="px-2.5 py-1 rounded-full bg-<?= $sdg['bg'] ?>-50 text-<?= $sdg['bg'] ?>-700 text-[11px] font-medium border border-<?= $sdg['bg'] ?>-100"><?= sanitize($tag) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- CASE STUDIES -->
<section id="case-studies" class="bg-white py-16 lg:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 lg:mb-16">
      <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-3">Real Stories, Real Impact</p>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Case Studies</h2>
      <p class="mt-4 mx-auto max-w-2xl text-slate-600 text-sm sm:text-base leading-7">In-depth look at how WRC programs transform lives, backed by data and beneficiary voices.</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
      <?php foreach ($caseStudies as $cs): ?>
      <article class="bg-slate-50 rounded-[2rem] border border-slate-200 overflow-hidden group hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col">
        <div class="relative h-56 overflow-hidden">
          <img src="<?= $cs['image'] ?>" alt="<?= sanitize($cs['title']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
          <div class="absolute top-4 left-4">
            <span class="px-3 py-1 rounded-full bg-<?= $cs['categoryColor'] ?> text-white text-[11px] font-semibold uppercase tracking-wider"><?= sanitize($cs['category']) ?></span>
          </div>
          <div class="absolute bottom-4 left-4 right-4">
            <h3 class="text-xl font-bold text-white leading-tight"><?= sanitize($cs['title']) ?></h3>
          </div>
        </div>
        <div class="p-5 sm:p-6 flex-1 flex flex-col">
          <p class="text-slate-600 text-sm leading-6 mb-4"><?= sanitize($cs['desc']) ?></p>

          <div class="grid grid-cols-3 gap-3 mb-4">
            <?php foreach ($cs['stats'] as $csStat): ?>
            <div class="text-center p-2 bg-white rounded-xl border border-slate-100">
              <div class="text-lg font-bold text-slate-900"><?= sanitize($csStat['val']) ?></div>
              <div class="text-[10px] text-slate-500"><?= sanitize($csStat['label']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="bg-white border-l-4 border-brandRed rounded-r-xl p-3 mb-4">
            <p class="text-slate-600 text-xs italic leading-relaxed"><?= sanitize($cs['quote']) ?></p>
            <p class="text-slate-900 text-xs font-semibold mt-2"><?= sanitize($cs['author']) ?></p>
          </div>

          <div class="mt-auto pt-3 border-t border-slate-200">
            <p class="text-[11px] text-green-600 font-semibold"><i class="fa-solid fa-check-circle mr-1"></i> <?= sanitize($cs['outcome']) ?></p>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- CTA BANNER -->
<section class="bg-brandRed py-14 lg:py-16">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4 font-heading"><?= sanitize($ctaTitle) ?></h2>
    <p class="text-white/80 text-sm sm:text-base max-w-2xl mx-auto mb-8 leading-relaxed"><?= sanitize($ctaSubtitle) ?></p>
    <div class="flex flex-wrap justify-center gap-4">
      <a href="<?= $base ?><?= $ctaData['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>" class="bg-white hover:bg-slate-100 text-brandRed font-bold text-sm px-8 py-3.5 rounded-xl shadow-lg transition-all flex items-center gap-2 transform hover:-translate-y-1">
        <i class="fa-solid fa-handshake"></i> <?= sanitize($ctaData['button1_text'] ?? 'Contribute With Us') ?>
      </a>
      <a href="<?= $base ?><?= $ctaData['button2_link'] ?? 'contact/index.php' ?>" class="bg-white/10 hover:bg-white/25 backdrop-blur-md text-white border border-white/30 font-semibold text-sm px-7 py-3.5 rounded-xl transition-all flex items-center gap-2 transform hover:-translate-y-1">
        <i class="fa-solid fa-phone"></i> <?= sanitize($ctaData['button2_text'] ?? 'Contact Us') ?>
      </a>
    </div>
  </div>
</section>

</main>

<!-- Counter Animation -->
<?php
$counterScript = <<<'JS'
<script>
  (function () {
    const counters = document.querySelectorAll('.counter-val');

    function animateCounter(el) {
      const target = parseInt(el.getAttribute('data-target'), 10);
      const duration = 2000;
      const startTime = performance.now();
      const suffix = target >= 1000 ? '+' : '';

      function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
      }

      function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeOutExpo(progress);
        const current = Math.floor(easedProgress * target);

        if (target >= 1000) {
          el.textContent = current.toLocaleString() + suffix;
        } else {
          el.textContent = current + suffix;
        }

        if (progress < 1) {
          requestAnimationFrame(update);
        }
      }

      requestAnimationFrame(update);
    }

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.3,
      rootMargin: '0px 0px -50px 0px'
    });

    counters.forEach(function (counter) {
      observer.observe(counter);
    });
  })();
</script>
JS;
?>

<?php
$extraScripts = $counterScript;
include dirname(__DIR__) . '/includes/footer.php';
?>
