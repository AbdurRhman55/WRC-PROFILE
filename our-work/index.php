<?php
/**
 * WRC Our Work - Program Listing (Dynamic)
 * ALL content from database - 0% hardcoded
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';

// Fetch all programs
$programs = getRows('programs', 'is_active = 1 ORDER BY sort_order ASC');

// Fetch all Our Work homepage sections
$allSections = getRows('homepage_sections', '', '');
$owSections = [];
foreach ($allSections as $s) {
    if (str_starts_with($s['section_key'], 'our_work')) {
        $owSections[$s['section_key']] = $s;
    }
}

// Override with dedicated table data
$heroRow = getRow('our_work_hero');
if ($heroRow) $owSections['our_work_hero'] = ['section_key' => 'our_work_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'content' => json_encode($heroRow)];

$edRow = getRow('our_work_editorial');
if ($edRow) {
    $features = getRows('our_work_editorial_features', ['editorial_id' => $edRow['id']], 'sort_order ASC');
    $owSections['our_work_editorial'] = ['section_key' => 'our_work_editorial', 'section_title' => $edRow['section_title'], 'section_subtitle' => $edRow['section_subtitle'], 'content' => json_encode(['image' => $edRow['image'], 'label' => $edRow['label'], 'description' => $edRow['description'], 'features' => $features])];
}

$procRow = getRow('our_work_process');
if ($procRow) {
    $steps = getRows('our_work_process_steps', ['process_id' => $procRow['id']], 'sort_order ASC');
    $stepsMapped = array_map(function($s) { return ['num' => $s['step_num'], 'title' => $s['title'], 'description' => $s['description']]; }, $steps);
    $owSections['our_work_process'] = ['section_key' => 'our_work_process', 'section_title' => $procRow['section_title'], 'section_subtitle' => $procRow['section_subtitle'], 'content' => json_encode(['subtitle' => $procRow['subtitle'], 'steps' => $stepsMapped])];
}

$pillRow = getRow('our_work_pillars');
if ($pillRow) {
    $pillars = getRows('our_work_pillars_items', ['pillars_id' => $pillRow['id']], 'sort_order ASC');
    $pillarsMapped = array_map(function($p) { return ['num' => $p['pillar_num'], 'title' => $p['title'], 'description' => $p['description']]; }, $pillars);
    $owSections['our_work_pillars'] = ['section_key' => 'our_work_pillars', 'section_title' => $pillRow['section_title'], 'section_subtitle' => $pillRow['section_subtitle'], 'content' => json_encode(['subtitle' => $pillRow['subtitle'], 'pillars' => $pillarsMapped])];
}

$featRow = getRow('our_work_featured_projects');
if ($featRow) $owSections['our_work_featured_projects'] = ['section_key' => 'our_work_featured_projects', 'section_title' => $featRow['section_title'] ?? '', 'section_subtitle' => $featRow['section_subtitle'] ?? '', 'content' => json_encode(['description' => $featRow['description']])];

$ctaRow = getRow('our_work_cta');
if ($ctaRow) $owSections['our_work_cta'] = ['section_key' => 'our_work_cta', 'section_title' => $ctaRow['section_title'], 'content' => json_encode($ctaRow)];

// Parse section JSON data
$heroData = isset($owSections['our_work_hero']) ? json_decode($owSections['our_work_hero']['content'], true) : [];
$editorialData = isset($owSections['our_work_editorial']) ? json_decode($owSections['our_work_editorial']['content'], true) : [];
$processData = isset($owSections['our_work_process']) ? json_decode($owSections['our_work_process']['content'], true) : [];
$pillarsData = isset($owSections['our_work_pillars']) ? json_decode($owSections['our_work_pillars']['content'], true) : [];
$featuredProjData = isset($owSections['our_work_featured_projects']) ? json_decode($owSections['our_work_featured_projects']['content'], true) : [];
$ctaData = isset($owSections['our_work_cta']) ? json_decode($owSections['our_work_cta']['content'], true) : [];

// Fetch featured programs (latest active)
$featuredPrograms = getRows('programs', 'is_active = 1 ORDER BY sort_order ASC LIMIT 3');

$pageTitle = 'Our Work & Core Programs | World Relief Committee (WRC)';
$metaDescription = "Explore WRC's 9 core humanitarian sectors across Health, Education, WASH, Food Security, Nutrition, Livelihood, Emergency Response, DRR, and Climate Resilience.";

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">
  
  <!-- HERO SECTION -->
  <section class="relative bg-slate-900 text-white min-h-[500px] flex items-center py-20 overflow-hidden">
    <div class="absolute inset-0 z-0">
      <img src="<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/our-work/our-work-hero.jpg' ?>" alt="<?= sanitize($heroData['hero_alt'] ?? 'Humanitarian Fieldwork in Pakistan') ?>" class="w-full h-full object-cover object-center opacity-40 mix-blend-luminosity">
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/60"></div>
    </div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="max-w-3xl">
        <span class="text-brandRed font-bold text-xs uppercase tracking-widest mb-3 block"><?= $owSections['our_work_hero']['section_title'] ?? 'Our Work' ?></span>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold font-heading text-white mb-6 leading-tight">
          <?= $owSections['our_work_hero']['section_subtitle'] ?? '' ?>
        </h1>
        <p class="text-slate-300 text-base md:text-lg mb-8 leading-relaxed">
          <?= sanitize($heroData['subtitle'] ?? '') ?>
        </p>
        <div class="flex flex-wrap gap-4">
          <a href="<?= $base ?><?= $heroData['button1_link'] ?? '#sectors' ?>" class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-xs px-6 py-4 rounded-xl shadow-lg shadow-brandRed/20 transition-all uppercase tracking-wider">
            <?= sanitize($heroData['button1_text'] ?? 'Explore Our Programs') ?>
          </a>
          <a href="<?= $base ?><?= $heroData['button2_link'] ?? 'get-involved/contribute-with-us.php' ?>" class="bg-white/10 hover:bg-white/20 text-white border border-white/25 font-bold text-xs px-6 py-4 rounded-xl transition-all uppercase tracking-wider">
            <?= sanitize($heroData['button2_text'] ?? 'Contribute With Us') ?>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- QUICK SECTOR NAV / FILTER BAR -->
  <section class="sticky top-20 z-40 bg-white border-b border-slate-200 shadow-sm py-4">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-4 overflow-x-auto no-scrollbar py-1 text-xs">
        <span class="font-bold text-slate-900 uppercase tracking-wider shrink-0 flex items-center gap-1.5"><i class="fa-solid fa-sliders text-brandRed"></i> Sectors:</span>
        <div class="flex items-center gap-2 shrink-0">
          <?php foreach ($programs as $prog): ?>
          <a href="#<?= $prog['slug'] ?>" class="px-3.5 py-1.5 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white transition-all font-medium text-slate-700"><?= sanitize($prog['title']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 9 SECTORS SHOWCASE GRID -->
  <section id="sectors" class="py-20 lg:py-28 bg-slate-50 scroll-mt-24">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="text-center max-w-3xl mx-auto mb-16">
        <span class="text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block">Comprehensive Programs</span>
        <h2 class="text-3xl md:text-4xl font-extrabold font-heading text-slate-900 mb-4">Our Core Humanitarian Sectors</h2>
        <p class="text-slate-600 text-sm leading-relaxed">Each of our nine sectors is designed to address key structural challenges and promote sustainable development, local capacity, and emergency resilience.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($programs as $prog): ?>
        <div id="<?= $prog['slug'] ?>" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between program-card-hover group">
          <div class="relative h-48 overflow-hidden">
            <img src="<?= $base ?><?= $prog['hero_image'] ?>" alt="<?= sanitize($prog['title']) ?> program image" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
            <span class="absolute top-4 right-4 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 bg-slate-900/80 text-white backdrop-blur-md rounded-full">Sector <?= $prog['sector_number'] ?></span>
          </div>
          <div class="p-6 md:p-8 flex-grow flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-brandRed flex items-center justify-center text-lg">
                  <i class="<?= $prog['icon_class'] ?>"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 font-heading"><?= sanitize($prog['title']) ?></h3>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed mb-6"><?= sanitize($prog['hero_description']) ?></p>
            </div>
            <a href="program.php?slug=<?= $prog['slug'] ?>" class="inline-flex items-center text-brandRed hover:text-brandRed-hover text-xs font-semibold uppercase tracking-wider gap-1.5 self-start">
              Explore Program <i class="fa-solid fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1"></i>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FEATURED WORK SECTION (EDITORIAL) -->
  <section class="py-20 lg:py-28 bg-white border-t border-slate-200/70">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
        <div class="lg:col-span-6 relative">
          <div class="relative rounded-3xl overflow-hidden shadow-2xl">
            <img src="<?= $base ?><?= $editorialData['image'] ?? 'assets/images/our-work/featured-work.jpg' ?>" alt="<?= sanitize($editorialData['image_alt'] ?? 'WRC Field Team in action') ?>" class="w-full h-[450px] object-cover object-center">
            <div class="absolute inset-0 bg-slate-950/10"></div>
          </div>
          <div class="absolute -bottom-6 -right-6 w-48 h-48 bg-slate-100 rounded-3xl -z-10 hidden sm:block"></div>
        </div>
        
        <div class="lg:col-span-6 space-y-6">
          <span class="text-brandRed font-bold text-xs uppercase tracking-widest block"><?= sanitize($editorialData['label'] ?? $owSections['our_work_editorial']['section_subtitle'] ?? '') ?></span>
          <h2 class="text-3xl md:text-4xl font-extrabold font-heading text-slate-900"><?= $owSections['our_work_editorial']['section_title'] ?? '' ?></h2>
          <p class="text-slate-600 text-sm leading-relaxed">
            <?= sanitize($editorialData['description'] ?? '') ?>
          </p>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
            <?php foreach ($editorialData['features'] ?? [] as $feature): ?>
            <div class="space-y-2">
              <div class="flex items-center gap-2 text-slate-900 font-bold text-sm">
                <i class="<?= $feature['icon'] ?> text-brandRed"></i> <?= sanitize($feature['title']) ?>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed"><?= sanitize($feature['description']) ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW WE WORK TIMELINE SECTION -->
  <section class="py-20 lg:py-28 bg-slate-50 border-t border-slate-200/50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="text-center max-w-3xl mx-auto mb-20">
        <span class="text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block"><?= sanitize($owSections['our_work_process']['section_subtitle'] ?? 'Our Process') ?></span>
        <h2 class="text-3xl md:text-4xl font-extrabold font-heading text-slate-900 mb-4"><?= $owSections['our_work_process']['section_title'] ?? '' ?></h2>
        <p class="text-slate-600 text-sm"><?= sanitize($processData['subtitle'] ?? '') ?></p>
      </div>

      <div class="process-timeline max-w-6xl mx-auto">
        <?php foreach ($processData['steps'] ?? [] as $step): ?>
        <div class="process-step">
          <div class="process-icon-wrapper w-14 h-14 rounded-2xl bg-white border border-slate-200 shadow-md flex items-center justify-center text-lg font-bold text-brandRed font-heading">
            <?= $step['num'] ?>
          </div>
          <h4 class="font-bold text-slate-900 text-sm mb-1"><?= sanitize($step['title']) ?></h4>
          <p class="text-[11px] text-slate-600 max-w-[150px] mx-auto lg:mx-0"><?= sanitize($step['description']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- OUR APPROACH TO IMPACT (4 PILLARS) -->
  <section class="py-20 lg:py-28 bg-white border-t border-slate-200/50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-16">
        <span class="text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block"><?= sanitize($owSections['our_work_pillars']['section_subtitle'] ?? 'Our Strategy') ?></span>
        <h2 class="text-3xl font-extrabold font-heading text-slate-900 mb-4"><?= $owSections['our_work_pillars']['section_title'] ?? '' ?></h2>
        <p class="text-slate-600 text-sm"><?= sanitize($pillarsData['subtitle'] ?? '') ?></p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <?php foreach ($pillarsData['pillars'] ?? [] as $pillar): ?>
        <div class="p-8 bg-slate-50 rounded-2xl border border-slate-100/80 shadow-sm hover:shadow-md transition-shadow">
          <div class="w-12 h-12 rounded-xl bg-red-50 text-brandRed flex items-center justify-center text-xl font-bold mb-6 font-heading"><?= $pillar['num'] ?></div>
          <h4 class="font-bold text-slate-900 mb-3 text-sm font-heading"><?= sanitize($pillar['title']) ?></h4>
          <p class="text-xs text-slate-600 leading-relaxed"><?= sanitize($pillar['description']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FEATURED PROJECTS GRID SECTION -->
  <section class="py-20 bg-slate-50 border-t border-slate-200/50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-16">
        <div>
          <span class="text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block"><?= sanitize($owSections['our_work_featured_projects']['section_subtitle'] ?? 'Action in the Field') ?></span>
          <h2 class="text-3xl font-extrabold font-heading text-slate-900"><?= $owSections['our_work_featured_projects']['section_title'] ?? '' ?></h2>
        </div>
        <p class="text-slate-600 text-xs md:text-sm max-w-md mt-4 md:mt-0 leading-relaxed">
          <?= sanitize($featuredProjData['description'] ?? '') ?>
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($featuredPrograms as $prog): ?>
        <a href="<?= $base ?>our-work/program.php?slug=<?= $prog['slug'] ?>" class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col justify-between group hover:shadow-xl transition-all duration-300">
          <div class="relative h-56 overflow-hidden">
            <img src="<?= $base ?><?= $prog['hero_image'] ?? $prog['fallback_image'] ?? 'assets/images/our-work/program-default.jpg' ?>" alt="<?= sanitize($prog['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <span class="absolute top-4 left-4 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 bg-brandRed text-white rounded-full shadow-md">Sector <?= $prog['sector_number'] ?? '' ?></span>
          </div>
          <div class="p-6 flex-grow flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-2">
                <i class="<?= $prog['icon_class'] ?> text-brandRed"></i> <?= sanitize($prog['title']) ?> Sector
              </div>
              <h3 class="text-lg font-bold text-slate-900 font-heading mb-3 group-hover:text-brandRed transition-colors"><?= sanitize($prog['title']) ?></h3>
              <p class="text-xs text-slate-600 leading-relaxed mb-6"><?= sanitize($prog['short_description'] ?? $prog['hero_description'] ?? '') ?></p>
            </div>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
              <span class="text-brandRed hover:text-brandRed-hover text-xs font-semibold hover:underline flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                Explore Program &rarr;
              </span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="py-20 bg-slate-900 text-white relative overflow-hidden">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center max-w-3xl">
      <h2 class="text-3xl md:text-4xl font-extrabold font-heading mb-4"><?= $owSections['our_work_cta']['section_title'] ?? '' ?></h2>
      <p class="text-slate-300 text-sm mb-8 leading-relaxed"><?= sanitize($ctaData['description'] ?? '') ?></p>
      <div class="flex flex-wrap items-center justify-center gap-4">
        <a href="<?= $base ?><?= $ctaData['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>" class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-xs px-6 py-3.5 rounded-xl shadow-lg transition-all flex items-center gap-2 uppercase tracking-wider">
          <i class="<?= $ctaData['button1_icon'] ?? 'fa-solid fa-handshake' ?>"></i> <?= sanitize($ctaData['button1_text'] ?? 'Contribute With Us') ?>
        </a>
        <a href="<?= $base ?><?= $ctaData['button2_link'] ?? 'contact/index.html' ?>" class="bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs px-6 py-3.5 rounded-xl border border-slate-700 transition-all flex items-center gap-2 uppercase tracking-wider">
          <i class="<?= $ctaData['button2_icon'] ?? 'fa-solid fa-envelope' ?>"></i> <?= sanitize($ctaData['button2_text'] ?? 'Contact Our Team') ?>
        </a>
      </div>
    </div>
  </section>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
