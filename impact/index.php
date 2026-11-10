<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Our Impact in Numbers | Welfare & Relief Committee (WRC)';
$metaDescription = "WRC measures the reach, effectiveness, and outcomes of its humanitarian and development work across Pakistan through systematic monitoring, programme reporting, and performance assessment.";

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

// Hero copy (dashboard-managed, approved wording as the fallback). The stored
// heading may still contain an old <br>; strip it before splitting on the comma.
$heroEyebrow = trim((string) ($heroData['eyebrow'] ?? '')) ?: 'Our Impact in Numbers';
$heroHeading = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($heroData['heading'] ?? ''))))
    ?: 'Measuring Progress, Demonstrating Accountability';
$heroIntro1  = trim((string) ($heroData['description'] ?? ''))
    ?: "WRC is committed to measuring the reach, effectiveness, and outcomes of its humanitarian and development initiatives. Through systematic monitoring, programme reporting, and performance assessment, we track progress, identify areas for improvement, and strengthen accountability to our partners, donors, and the communities we serve.";
$heroIntro2  = trim((string) ($heroData['intro2'] ?? ''))
    ?: 'Our impact indicators reflect the scale of our interventions, the services delivered, and the progress achieved across our programme sectors.';

// Split the headline at the first comma so the closing clause can carry the
// gold accent; a headline without a comma stays on one line.
$heroTitleParts = array_map('trim', explode(',', $heroHeading, 2));

// Impact at a Glance indicators
$indicators = array_values(array_filter((array) ($contentData['indicators'] ?? []), 'is_array'));
if (empty($indicators)) {
    $indicators = [
        ['icon' => 'fa-heart-pulse', 'title' => 'Healthcare Services', 'desc' => 'Tracking access to essential healthcare, medical assistance, and community health interventions.'],
        ['icon' => 'fa-graduation-cap', 'title' => 'Education & Skills Development', 'desc' => 'Measuring participation in educational programmes, vocational training, and capacity-building initiatives.'],
        ['icon' => 'fa-hand-holding-heart', 'title' => 'Humanitarian Assistance', 'desc' => 'Monitoring households reached through food assistance, emergency relief, and essential support.'],
        ['icon' => 'fa-faucet-drip', 'title' => 'Water, Sanitation & Hygiene', 'desc' => 'Assessing access to clean water and community-based WASH interventions.'],
        ['icon' => 'fa-people-roof', 'title' => "Women's Empowerment", 'desc' => 'Tracking participation in vocational training, skills development, and livelihood initiatives.'],
    ];
}

$indicatorStyles = [
    ['icon' => 'bg-rose-50 text-brandRed', 'bar' => 'bg-brandRed'],
    ['icon' => 'bg-sky-50 text-sky-600', 'bar' => 'bg-sky-500'],
    ['icon' => 'bg-amber-50 text-amber-600', 'bar' => 'bg-amber-500'],
    ['icon' => 'bg-cyan-50 text-cyan-600', 'bar' => 'bg-cyan-500'],
    ['icon' => 'bg-violet-50 text-violet-600', 'bar' => 'bg-violet-500'],
];

// Dashboard stats
$stats = $contentData['stats'] ?? [
    ['icon' => 'fa-heart-pulse', 'color' => 'brandRed', 'target' => '150000', 'label' => 'Lives Impacted', 'sub' => 'Across 4 provinces since inception'],
    ['icon' => 'fa-faucet-drip', 'color' => 'blue', 'target' => '1200', 'label' => 'Water Wells Installed', 'sub' => 'Clean water access for rural families'],
    ['icon' => 'fa-bowl-food', 'color' => 'amber', 'target' => '85000', 'label' => 'Food Packages Distributed', 'sub' => 'Emergency food relief to families in need'],
    ['icon' => 'fa-stethoscope', 'color' => 'green', 'target' => '12000', 'label' => 'Healthcare Visits', 'sub' => 'Free medical camps & clinical services'],
    ['icon' => 'fa-graduation-cap', 'color' => 'purple', 'target' => '45000', 'label' => 'Children Enrolled', 'sub' => 'Schools & non-formal learning centers'],
    ['icon' => 'fa-map-location-dot', 'color' => 'teal', 'target' => '200', 'label' => 'Communities Served', 'sub' => 'Rural & underserved regions reached'],
];

// M&E framework
$mneIntro = trim((string) ($contentData['mne_intro'] ?? ''))
    ?: 'WRC recognizes that effective programme delivery requires continuous monitoring, transparent reporting, and meaningful accountability. Our monitoring and evaluation approach focuses on tracking implementation progress, assessing programme performance, identifying challenges, and documenting results.';

$mneSteps = array_values(array_filter((array) ($contentData['mne_steps'] ?? []), 'is_array'));
if (empty($mneSteps)) {
    $mneSteps = [
        ['label' => '01', 'title' => 'Programme Monitoring', 'desc' => 'Regular monitoring of project activities, implementation progress, timelines, and deliverables against approved project plans.', 'icon' => 'fa-magnifying-glass-chart', 'side' => 'left'],
        ['label' => '02', 'title' => 'Data Collection & Verification', 'desc' => 'Systematic collection and review of programme information, beneficiary records, and supporting documentation to maintain reliable reporting.', 'icon' => 'fa-clipboard-check', 'side' => 'right'],
        ['label' => '03', 'title' => 'Quality Assurance', 'desc' => 'Review of implementation processes and service delivery to identify gaps, strengthen operational standards, and promote continuous improvement.', 'icon' => 'fa-shield-halved', 'side' => 'left'],
        ['label' => '04', 'title' => 'Community Feedback & Accountability', 'desc' => 'Encouraging feedback from beneficiaries and communities to understand their experiences, identify concerns, and improve programme responsiveness.', 'icon' => 'fa-comments', 'side' => 'right'],
        ['label' => '05', 'title' => 'Evaluation & Reporting', 'desc' => 'Periodic assessment of programme achievements, challenges, outputs, and outcomes to support evidence-based decisions and institutional learning.', 'icon' => 'fa-chart-line', 'side' => 'left'],
    ];
}

// SDGs
$sdgs = $contentData['sdgs'] ?? [
    ['number' => '3', 'title' => 'Good Health & Well-Being', 'desc' => 'WRC operates free medical camps, maternal health clinics, and emergency healthcare services reaching underserved populations across Pakistan.', 'color' => 'green-600', 'bg' => 'green', 'progress' => '78', 'tags' => ['12,000+ Healthcare Visits', '45 Medical Camps'], 'icon' => 'fa-heart-pulse'],
    ['number' => '4', 'title' => 'Quality Education', 'desc' => 'Through schools, non-formal learning centers, and orphan education sponsorships, WRC ensures children access quality learning opportunities.', 'color' => 'red-600', 'bg' => 'red', 'progress' => '72', 'tags' => ['45,000+ Children Enrolled', '120 Learning Centers'], 'icon' => 'fa-graduation-cap'],
    ['number' => '6', 'title' => 'Clean Water & Sanitation', 'desc' => 'WRC installs hand pumps, deep tube wells, and water purification systems, while training communities in hygiene practices and sanitation management.', 'color' => 'blue-600', 'bg' => 'blue', 'progress' => '85', 'tags' => ['1,200+ Water Wells', '180 WASH Committees'], 'icon' => 'fa-faucet-drip'],
    ['number' => '13', 'title' => 'Climate Action', 'desc' => 'WRC integrates climate resilience into disaster preparedness, promotes renewable energy access, and supports reforestation and environmental restoration projects.', 'color' => 'emerald-700', 'bg' => 'emerald', 'progress' => '64', 'tags' => ['50,000 Trees Planted', '15 Solar Installations'], 'icon' => 'fa-leaf'],
];

// SDG section copy (dashboard-managed, approved wording as the fallback).
$sdgEyebrow = trim((string) ($contentData['sdg_eyebrow'] ?? '')) ?: 'Global Goals Alignment';
$sdgTitle   = trim((string) ($contentData['sdg_title'] ?? '')) ?: 'Contributing to the United Nations Sustainable Development Goals (SDGs)';
$sdgIntro1  = trim((string) ($contentData['sdg_intro1'] ?? ''))
    ?: "WRC seeks to align its humanitarian and development initiatives with relevant United Nations Sustainable Development Goals (SDGs). Through programmes addressing healthcare, education, livelihoods, women's empowerment, clean water, and humanitarian needs, we aim to contribute to inclusive and sustainable development.";
$sdgIntro2  = trim((string) ($contentData['sdg_intro2'] ?? ''))
    ?: 'Our programme activities are mapped against relevant SDG targets to demonstrate their contribution and identify opportunities for greater impact.';

// Case Studies - taken from the same stories the dashboard manages, so an edit
// in the admin panel updates the homepage and this page together. The hardcoded
// copies that used to sit here were left behind when this query was introduced:
// the dangling array after the semicolon was a PHP parse error, and on a server
// that hides error output it surfaced as "HTTP ERROR 500" for the whole page.
$caseStudies = getRows('success_stories', 'is_active = 1 ORDER BY sort_order ASC');

// CTA Section
$ctaRow = getRow('homepage_sections', ['section_key' => 'impact_cta']);
$ctaData = $ctaRow ? json_decode($ctaRow['content'] ?? '{}', true) : [];
$ctaTitle = $ctaRow['section_title'] ?? 'Your Support Fuels Every Number';
$ctaSubtitle = $ctaRow['section_subtitle'] ?? 'Behind every statistic is a family, a child, a community whose future is shaped by your generosity. Join us in turning data into dignity.';

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- HERO SECTION -->
<section class="relative isolate w-full min-h-[560px] md:min-h-[620px] flex items-center overflow-hidden bg-ink-900">
  <div class="absolute inset-0 z-0">
    <img src="<?= $base ?><?= sanitize($heroData['hero_image'] ?? 'assets/images/hero/impact-hero.jpg') ?>" alt="WRC programme impact across Pakistan" class="h-full w-full object-cover" />
    <div class="absolute inset-0 bg-gradient-to-r from-ink-900/95 via-ink-900/80 to-ink-900/35"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-transparent to-ink-900/40"></div>
    <div class="absolute -right-24 top-1/2 h-[420px] w-[420px] -translate-y-1/2 rounded-full bg-brandRed/20 blur-3xl"></div>
  </div>
  <div class="relative z-10 mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 md:py-20 lg:px-8 lg:py-24">
    <div class="max-w-3xl">
      <nav class="mb-6 flex items-center gap-2 text-xs text-white/55 sm:text-sm">
        <a href="<?= $base ?>index.php" class="transition-colors hover:text-white">Home</a>
        <i class="fa-solid fa-chevron-right text-[9px]"></i>
        <span class="text-gold-400">Impact</span>
      </nav>
      <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/90 backdrop-blur-sm">
        <i class="fa-solid fa-chart-column text-[10px] text-gold-400"></i> <?= sanitize($heroEyebrow) ?>
      </span>
      <h1 class="mt-5 text-4xl font-display font-bold leading-[1.08] text-white sm:text-5xl lg:text-6xl">
        <?= sanitize($heroTitleParts[0]) ?><?php if (count($heroTitleParts) === 2 && $heroTitleParts[1] !== ''): ?>,<br>
        <span class="text-gold-400"><?= sanitize($heroTitleParts[1]) ?></span><?php endif; ?>
      </h1>
      <p class="mt-6 max-w-2xl text-base leading-relaxed text-white/80 md:text-lg">
        <?= sanitize($heroIntro1) ?>
      </p>
      <p class="mt-5 max-w-2xl border-l-2 border-gold-400/70 pl-4 text-sm font-medium leading-relaxed text-gold-400/95 md:text-base">
        <?= sanitize($heroIntro2) ?>
      </p>
      <div class="mt-9 flex flex-wrap items-center gap-4">
        <a href="#glance" class="flex items-center gap-2 rounded-xl bg-brandRed px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-brandRed/30 transition-all hover:bg-brandRed-hover hover:-translate-y-0.5">
          <i class="fa-solid fa-arrow-down"></i> <?= sanitize($heroData['cta_primary'] ?? 'Impact at a Glance') ?>
        </a>
        <a href="#sdgs" class="flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/20 hover:-translate-y-0.5">
          <i class="fa-solid fa-globe"></i> <?= sanitize($heroData['cta_secondary'] ?? 'SDG Contributions') ?>
        </a>
      </div>
    </div>
  </div>
</section>


<!-- IMPACT AT A GLANCE -->
<section id="glance" class="bg-sand-50 py-16 lg:py-24">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl text-center">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-brandRed">Programme Sectors</p>
      <h2 class="mt-3 text-3xl font-display font-bold text-ink-900 sm:text-4xl">Impact at a Glance</h2>
    </div>

    <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-6">
      <?php foreach ($indicators as $i => $indicator): ?>
      <?php
        $style = $indicatorStyles[$i % count($indicatorStyles)];
        $span  = $i < 3 ? 'lg:col-span-2' : ($i === 4 ? 'md:col-span-2 lg:col-span-3' : 'lg:col-span-3');
      ?>
      <article class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-brandRed/20 hover:shadow-xl <?= $span ?>">
        <span class="absolute inset-x-0 top-0 h-1 <?= $style['bar'] ?>"></span>
        <div class="flex items-start gap-4">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl <?= $style['icon'] ?> transition-transform duration-300 group-hover:scale-105">
            <i class="fa-solid <?= sanitize($indicator['icon'] ?? 'fa-chart-simple') ?> text-lg"></i>
          </span>
          <div>
            <h3 class="text-lg font-bold leading-snug text-ink-900"><?= sanitize($indicator['title'] ?? '') ?></h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600"><?= sanitize($indicator['desc'] ?? '') ?></p>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- IMPACT IN NUMBERS -->
<section id="dashboard" class="relative overflow-hidden bg-ink-900 py-14 lg:py-16">
  <span id="stats" class="sr-only">Beneficiary statistics</span>
  <div class="pointer-events-none absolute -left-24 top-0 h-72 w-72 rounded-full bg-brandRed/20 blur-3xl"></div>
  <div class="pointer-events-none absolute -right-16 bottom-0 h-72 w-72 rounded-full bg-gold-400/10 blur-3xl"></div>
  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-gold-400">Our Reach</p>
        <h2 class="mt-3 text-2xl font-display font-bold text-white sm:text-3xl">Impact in Numbers</h2>
      </div>
      <p class="max-w-md text-sm leading-relaxed text-white/55">Live delivery figures from programme reports across Pakistan.</p>
    </div>

    <div class="mt-10 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
      <?php
      $statTones = [
          'brandRed' => 'text-rose-400 bg-rose-500/10',
          'blue'     => 'text-sky-400 bg-sky-500/10',
          'amber'    => 'text-amber-400 bg-amber-500/10',
          'green'    => 'text-emerald-400 bg-emerald-500/10',
          'purple'   => 'text-violet-400 bg-violet-500/10',
          'teal'     => 'text-teal-300 bg-teal-500/10',
      ];
      foreach ($stats as $stat):
          $tone = $statTones[$stat['color'] ?? ''] ?? 'text-gold-400 bg-white/5';
      ?>
      <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center backdrop-blur-sm transition-colors hover:border-white/20 hover:bg-white/10">
        <span class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl <?= $tone ?>">
          <i class="fa-solid <?= sanitize($stat['icon'] ?? 'fa-chart-simple') ?> text-sm"></i>
        </span>
        <div class="counter-val text-2xl font-display font-bold leading-none text-white sm:text-3xl" data-target="<?= sanitize($stat['target'] ?? '0') ?>">0</div>
        <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.1em] text-white/70"><?= sanitize($stat['label'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- MONITORING, EVALUATION & ACCOUNTABILITY FRAMEWORK -->
<section id="mne" class="bg-white py-16 lg:py-24">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl text-center">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-brandRed">How We Ensure Quality</p>
      <h2 class="mt-3 text-3xl font-display font-bold leading-tight text-ink-900 sm:text-4xl">Monitoring, Evaluation &amp; Accountability Framework</h2>
      <p class="mt-5 text-sm leading-7 text-slate-600 sm:text-base"><?= sanitize($mneIntro) ?></p>
    </div>

    <div class="relative mx-auto mt-14 max-w-4xl">
      <div class="absolute bottom-0 left-5 top-0 hidden w-px -translate-x-1/2 bg-gradient-to-b from-transparent via-slate-200 to-transparent md:left-1/2 md:block"></div>
      <div class="absolute bottom-0 left-[19px] top-0 w-px bg-gradient-to-b from-transparent via-slate-200 to-transparent md:hidden"></div>

      <div class="space-y-10 md:space-y-0">
        <?php foreach ($mneSteps as $i => $step): ?>
        <div class="relative md:grid md:grid-cols-2 md:items-center md:gap-x-16 <?= $i < count($mneSteps) - 1 ? 'md:pb-14' : '' ?>">
          <?php if (($step['side'] ?? 'left') === 'left'): ?>
          <div class="pl-16 md:pl-0 md:pr-16 md:text-right">
            <span class="inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.28em] text-brandRed md:justify-end">
              <span class="font-display text-sm text-brandRed"><?= sanitize($step['label'] ?? sprintf('%02d', $i + 1)) ?></span>
              <span class="h-px w-8 bg-brandRed/40"></span>
            </span>
            <h3 class="mt-3 text-xl font-bold text-ink-900 md:text-2xl"><?= sanitize($step['title'] ?? '') ?></h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-600 md:text-[15px]"><?= sanitize($step['desc'] ?? '') ?></p>
          </div>
          <div class="hidden md:block"></div>
          <?php else: ?>
          <div class="hidden md:block"></div>
          <div class="pl-16 md:pl-16">
            <span class="inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.28em] text-brandRed">
              <span class="font-display text-sm text-brandRed"><?= sanitize($step['label'] ?? sprintf('%02d', $i + 1)) ?></span>
              <span class="h-px w-8 bg-brandRed/40"></span>
            </span>
            <h3 class="mt-3 text-xl font-bold text-ink-900 md:text-2xl"><?= sanitize($step['title'] ?? '') ?></h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-600 md:text-[15px]"><?= sanitize($step['desc'] ?? '') ?></p>
          </div>
          <?php endif; ?>
          <span class="absolute left-0 top-1 flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-brandRed text-white shadow-lg shadow-brandRed/25 md:left-1/2 md:top-1/2 md:-translate-x-1/2 md:-translate-y-1/2">
            <i class="fa-solid <?= sanitize($step['icon'] ?? 'fa-circle-check') ?> text-xs"></i>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>


<!-- SDG CONTRIBUTION MAP -->
<section id="sdgs" class="relative overflow-hidden bg-slate-50 py-16 lg:py-24">
  <div class="pointer-events-none absolute -left-32 top-10 h-80 w-80 rounded-full bg-brandRed/5 blur-3xl"></div>
  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl text-center">
      <span class="inline-flex items-center gap-2 rounded-full border border-brandRed/15 bg-white px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-brandRed shadow-sm">
        <i class="fa-solid fa-globe text-[10px]"></i> <?= sanitize($sdgEyebrow) ?>
      </span>
      <h2 class="mt-5 text-3xl font-display font-bold leading-[1.18] text-ink-900 sm:text-4xl"><?= sanitize($sdgTitle) ?></h2>
    </div>
    <div class="mx-auto mt-7 grid max-w-5xl gap-5 text-sm leading-7 text-slate-600 sm:text-base md:grid-cols-2 md:gap-10">
      <p><?= sanitize($sdgIntro1) ?></p>
      <p class="md:border-l md:border-slate-200 md:pl-10"><?= sanitize($sdgIntro2) ?></p>
    </div>

    <div class="mt-12 grid gap-6 md:grid-cols-2">
      <?php foreach ($sdgs as $sdg): ?>
      <article class="group relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
        <div class="flex items-stretch">
          <div class="relative flex w-24 shrink-0 flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-<?= $sdg['bg'] ?>-500 to-<?= $sdg['bg'] ?>-700 p-4 text-white sm:w-28">
            <span class="pointer-events-none absolute -right-1 -top-3 font-display text-6xl font-bold text-white/10"><?= sanitize($sdg['number']) ?></span>
            <i class="fa-solid <?= sanitize($sdg['icon']) ?> mb-2 text-2xl sm:text-3xl"></i>
            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/75">SDG</span>
            <span class="font-display text-2xl font-bold leading-none"><?= sanitize($sdg['number']) ?></span>
          </div>
          <div class="flex-1 p-5 sm:p-6">
            <h3 class="text-lg font-bold leading-snug text-ink-900"><?= sanitize($sdg['title']) ?></h3>
            <p class="mt-2 text-sm leading-6 text-slate-600"><?= sanitize($sdg['desc']) ?></p>

            <div class="mt-5">
              <div class="flex items-center justify-between text-xs">
                <span class="font-medium text-slate-500">Progress toward target</span>
                <span class="font-semibold text-<?= $sdg['color'] ?>"><?= sanitize($sdg['progress']) ?>%</span>
              </div>
              <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-gradient-to-r from-<?= $sdg['bg'] ?>-400 to-<?= $sdg['bg'] ?>-600" style="width: <?= (int) $sdg['progress'] ?>%;"></div>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <?php foreach ($sdg['tags'] as $tag): ?>
              <span class="rounded-full border border-<?= $sdg['bg'] ?>-100 bg-<?= $sdg['bg'] ?>-50 px-2.5 py-1 text-[11px] font-medium text-<?= $sdg['bg'] ?>-700"><?= sanitize($tag) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- CASE STUDIES -->
<section id="case-studies" class="bg-white py-16 lg:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 lg:mb-16">
      <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-3">Real Stories, Real Impact</p>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900">Case Studies</h2>
      <p class="mt-4 mx-auto max-w-2xl text-slate-600 text-sm sm:text-base leading-7">In-depth look at how WRC programs transform lives, backed by data and beneficiary voices.</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
      <?php foreach ($caseStudies as $cs): ?>
      <?php
        // These rows come from success_stories, which has a different shape from
        // the old hardcoded array: there is no title/desc/stats/outcome column.
        // The person's name is the headline, the sector is the badge, and the
        // testimonial is the body.
        $csPhoto = trim((string) ($cs['image_path'] ?? ''));
        $csBadge = trim((string) ($cs['sector_badge'] ?? '')) ?: trim((string) ($cs['sector'] ?? ''));
        $csMeta  = implode(' · ', array_filter([
            trim((string) ($cs['person_title'] ?? '')),
            trim((string) ($cs['location'] ?? '')),
        ]));
      ?>
      <article class="bg-slate-50 rounded-[2rem] border border-slate-200 overflow-hidden group hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col">
        <div class="relative h-56 overflow-hidden bg-slate-200">
          <?php if ($csPhoto !== ''): ?>
          <img src="<?= $base ?><?= sanitize($csPhoto) ?>" alt="<?= sanitize($cs['person_name']) ?>" loading="lazy"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
          <?php endif; ?>
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
          <?php if ($csBadge !== ''): ?>
          <div class="absolute top-4 left-4">
            <span class="px-3 py-1 rounded-full bg-brandRed text-white text-[11px] font-semibold uppercase tracking-wider"><?= sanitize($csBadge) ?></span>
          </div>
          <?php endif; ?>
          <div class="absolute bottom-4 left-4 right-4">
            <h3 class="text-xl font-bold text-white leading-tight"><?= sanitize($cs['person_name']) ?></h3>
          </div>
        </div>
        <div class="p-5 sm:p-6 flex-1 flex flex-col">
          <div class="bg-white border-l-4 border-brandRed rounded-r-xl p-4">
            <p class="text-slate-600 text-sm italic leading-relaxed"><?= sanitize($cs['quote']) ?></p>
          </div>

          <div class="mt-auto pt-4">
            <p class="text-slate-900 text-xs font-semibold"><?= sanitize($cs['person_name']) ?></p>
            <?php if ($csMeta !== ''): ?>
            <p class="text-slate-500 text-[11px] mt-0.5"><?= sanitize($csMeta) ?></p>
            <?php endif; ?>
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
    <h2 class="text-3xl sm:text-4xl font-display font-bold text-white mb-4"><?= sanitize($ctaTitle) ?></h2>
    <p class="text-white/80 text-sm sm:text-base max-w-2xl mx-auto mb-8 leading-relaxed"><?= sanitize($ctaSubtitle) ?></p>
    <div class="flex flex-wrap justify-center gap-4">
      <a href="<?= $base ?><?= $ctaData['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>" class="bg-white hover:bg-slate-100 text-brandRed font-bold text-sm px-8 py-3.5 rounded-xl shadow-lg transition-all flex items-center gap-2 transform hover:-translate-y-1">
        <i class="fa-solid fa-handshake"></i> <?= sanitize($ctaData['button1_text'] ?? 'Let’s Work Together') ?>
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
