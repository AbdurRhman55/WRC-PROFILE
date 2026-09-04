<?php
/**
 * WRC Projects - Listing Page (Dynamic)
 * ALL content from database
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';

// Fetch projects sections from DB
$allSections = getRows('homepage_sections', '', '');
$projSections = [];
foreach ($allSections as $s) {
    if (str_starts_with($s['section_key'], 'projects_') || str_starts_with($s['section_key'], 'project_')) {
        $projSections[$s['section_key']] = $s;
    }
}

// Override with dedicated table data
$heroRow = getRow('projects_hero');
if ($heroRow) $projSections['projects_hero'] = ['section_key' => 'projects_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'content' => json_encode($heroRow)];

$dashRow = getRow('projects_dashboard');
if ($dashRow) {
    $metricsRows = getRows('projects_dashboard_metrics', ['dashboard_id' => $dashRow['id']], 'sort_order ASC');
    $metrics = array_map(function($m) { return ['icon' => $m['metric_icon'], 'color' => $m['metric_color'], 'label' => $m['metric_label'], 'value' => $m['metric_value']]; }, $metricsRows);
    $sectorsRows = getRows('projects_dashboard_sectors', ['dashboard_id' => $dashRow['id']], 'sort_order ASC');
    $sectors = array_map(function($s) { return ['name' => $s['sector_name'], 'color' => $s['sector_color'], 'count' => $s['sector_count'], 'percent' => $s['sector_percent'], 'gradient' => $s['sector_gradient']]; }, $sectorsRows);
    $projSections['projects_dashboard'] = ['section_key' => 'projects_dashboard', 'section_title' => $dashRow['section_title'], 'section_subtitle' => $dashRow['subtitle'] ?? '', 'content' => json_encode(['subtitle' => $dashRow['subtitle'], 'metrics' => $metrics, 'sectors' => $sectors])];
}

$ctaRow = getRow('projects_cta');
if ($ctaRow) $projSections['projects_cta'] = ['section_key' => 'projects_cta', 'section_title' => $ctaRow['section_title'], 'section_subtitle' => $ctaRow['section_subtitle'] ?? '', 'content' => json_encode($ctaRow)];

// Parse section JSON
$heroData = isset($projSections['projects_hero']) ? json_decode($projSections['projects_hero']['content'], true) : [];
$dashData = isset($projSections['projects_dashboard']) ? json_decode($projSections['projects_dashboard']['content'], true) : [];
$ctaData = isset($projSections['projects_cta']) ? json_decode($projSections['projects_cta']['content'], true) : [];

// Fetch projects from DB
$activeProjects = getRows('projects', "status = 'active' ORDER BY sort_order ASC");
$completedProjects = getRows('projects', "status = 'completed' ORDER BY sort_order ASC");

// Compute quick stats
$totalProjects = count($activeProjects) + count($completedProjects);
$totalCompleted = count($completedProjects);
$totalActive = count($activeProjects);
$provinceCount = count(getRows('provinces'));

// Fetch success stories
$successStories = getRows('success_stories', ['is_active' => 1], 'sort_order ASC', 3);

$pageTitle = 'Projects Portfolio & Dashboard - WRC NGO';
$metaDescription = 'Discover WRC active and completed humanitarian projects transforming communities across Pakistan.';

include dirname(__DIR__) . '/includes/header.php';
?>

<style>
  .filter-tab { position: relative; }
  .filter-tab::after { content: ''; position: absolute; bottom: -2px; left: 50%; width: 0; height: 2px; background: #ca0000; transition: all 0.3s ease; transform: translateX(-50%); }
  .filter-tab:hover::after, .filter-tab.active::after { width: 100%; }
  .filter-tab.active { color: #ca0000; font-weight: 700; }
  .progress-bar-fill { transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1); }
  .bar-chart-bar { transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1); }
  .stat-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
  .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
</style>

<main class="flex-grow">

  <!-- HERO SECTION -->
  <section class="relative w-full min-h-[520px] flex items-center overflow-hidden">
    <div class="absolute inset-0">
      <img src="<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero.jpg' ?>" alt="<?= sanitize($heroData['hero_alt'] ?? 'WRC Projects') ?>" class="w-full h-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-r from-ink-900/95 via-ink-900/80 to-ink-900/60"></div>
    </div>
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
      <div class="max-w-2xl">
        <nav class="mb-6 flex items-center gap-2 text-sm text-white/60">
          <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
          <i class="fa-solid fa-chevron-right text-[9px]"></i>
          <span class="text-gold-400">Projects</span>
        </nav>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-5">
          <?= $projSections['projects_hero']['section_title'] ?? 'Our Projects Portfolio' ?><br>
          <span class="text-gold-400">Portfolio</span>
        </h1>
        <p class="text-lg text-white/80 leading-relaxed mb-8 max-w-xl">
          <?= sanitize($heroData['subtitle'] ?? '') ?>
        </p>
        <div class="flex flex-wrap gap-4">
          <a href="<?= $heroData['button1_link'] ?? '#active' ?>" class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm px-6 py-3.5 rounded-xl shadow-lg shadow-brandRed/30 transition-all flex items-center gap-2">
            <i class="fa-solid fa-spinner text-xs"></i> <?= sanitize($heroData['button1_text'] ?? 'Active Projects') ?>
          </a>
          <a href="<?= $heroData['button2_link'] ?? '#dashboard' ?>" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white font-bold text-sm px-6 py-3.5 rounded-xl border border-white/20 transition-all flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-xs"></i> <?= sanitize($heroData['button2_text'] ?? 'View Dashboard') ?>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- QUICK STATS BAR -->
  <section class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-gray-100">
        <div class="py-6 px-4 text-center">
          <div class="text-3xl font-display font-bold text-brandRed"><?= $totalProjects ?>+</div>
          <div class="text-xs text-slate-500 font-medium mt-1 uppercase tracking-wider">Total Projects</div>
        </div>
        <div class="py-6 px-4 text-center">
          <div class="text-3xl font-display font-bold text-teal-500"><?= $totalCompleted ?></div>
          <div class="text-xs text-slate-500 font-medium mt-1 uppercase tracking-wider">Completed</div>
        </div>
        <div class="py-6 px-4 text-center">
          <div class="text-3xl font-display font-bold text-gold-400"><?= $totalActive ?></div>
          <div class="text-xs text-slate-500 font-medium mt-1 uppercase tracking-wider">Active Now</div>
        </div>
        <div class="py-6 px-4 text-center">
          <div class="text-3xl font-display font-bold text-ink-900"><?= $provinceCount ?></div>
          <div class="text-xs text-slate-500 font-medium mt-1 uppercase tracking-wider">Provinces</div>
        </div>
      </div>
    </div>
  </section>

  <!-- FILTER TABS -->
  <section id="portfolio" class="sticky top-20 z-40 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-1 overflow-x-auto py-3 scrollbar-hide">
        <button class="filter-tab active whitespace-nowrap px-4 py-2 text-sm font-semibold text-slate-500 transition-colors rounded-lg hover:bg-gray-50" data-filter="all">
          All Projects
        </button>
        <button class="filter-tab whitespace-nowrap px-4 py-2 text-sm font-semibold text-slate-500 transition-colors rounded-lg hover:bg-gray-50" data-filter="active">
          <i class="fa-solid fa-circle text-[6px] text-green-500 mr-1.5"></i> Active
        </button>
        <button class="filter-tab whitespace-nowrap px-4 py-2 text-sm font-semibold text-slate-500 transition-colors rounded-lg hover:bg-gray-50" data-filter="completed">
          <i class="fa-solid fa-circle-check text-[10px] text-teal-500 mr-1.5"></i> Completed
        </button>
      </div>
    </div>
  </section>

  <!-- ACTIVE PROJECTS -->
  <section id="active" class="py-16 sm:py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-10">
        <div class="w-1.5 h-10 bg-brandRed rounded-full"></div>
        <div>
          <h2 class="text-2xl sm:text-3xl font-display font-bold text-ink-900">Active Projects</h2>
          <p class="text-sm text-slate-500 mt-1">Currently transforming communities across Pakistan</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($activeProjects as $project): ?>
        <a href="<?= $base ?>projects/project-name.php?slug=<?= urlencode($project['slug']) ?>" class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group" data-category="active">
          <div class="relative h-48 overflow-hidden">
            <img src="<?= $base ?><?= $project['hero_image'] ?>" alt="<?= sanitize($project['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
            <span class="absolute top-3 left-3 bg-green-500 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full flex items-center gap-1">
              <i class="fa-solid fa-circle text-[5px] animate-pulse"></i> Active
            </span>
            <span class="absolute top-3 right-3 bg-slate-900/70 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-1 rounded-full">
              <i class="fa-solid fa-location-dot mr-1"></i><?= sanitize($project['location_details']) ?>
            </span>
          </div>
          <div class="p-5">
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
              <span>Since <?= date('M Y', strtotime($project['start_date'])) ?></span>
            </div>
            <h3 class="text-lg font-display font-bold text-ink-900 mb-2 group-hover:text-brandRed transition-colors">
              <?= sanitize($project['title']) ?>
            </h3>
            <p class="text-sm text-slate-500 leading-relaxed mb-4">
              <?= sanitize($project['description']) ?>
            </p>
            <div class="mb-3">
              <div class="flex justify-between text-xs mb-1.5">
                <span class="font-semibold text-slate-600">Progress</span>
                <span class="font-bold text-brandRed"><?= $project['progress_percent'] ?>%</span>
              </div>
              <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="progress-bar-fill h-full bg-gradient-to-r from-brandRed to-red-400 rounded-full" style="width: <?= $project['progress_percent'] ?>%"></div>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-xs text-slate-500"><i class="fa-solid fa-users mr-1"></i><?= number_format($project['beneficiaries_count']) ?> beneficiaries</span>
              <span class="text-brandRed text-xs font-semibold group-hover:underline">View Details <i class="fa-solid fa-arrow-right text-[10px]"></i></span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- COMPLETED PROJECTS -->
  <section id="completed" class="py-16 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-10">
        <div class="w-1.5 h-10 bg-teal-500 rounded-full"></div>
        <div>
          <h2 class="text-2xl sm:text-3xl font-display font-bold text-ink-900">Completed Projects</h2>
          <p class="text-sm text-slate-500 mt-1">Successfully delivered impact across Pakistan</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($completedProjects as $project): ?>
        <a href="<?= $base ?>projects/project-name.php?slug=<?= urlencode($project['slug']) ?>" class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group" data-category="completed">
          <div class="relative h-48 overflow-hidden">
            <img src="<?= $base ?><?= $project['hero_image'] ?>" alt="<?= sanitize($project['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
            <span class="absolute top-3 left-3 bg-teal-500 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full flex items-center gap-1">
              <i class="fa-solid fa-circle-check text-[8px]"></i> Completed
            </span>
            <span class="absolute top-3 right-3 bg-slate-900/70 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-1 rounded-full">
              <i class="fa-solid fa-location-dot mr-1"></i><?= sanitize($project['location_details']) ?>
            </span>
          </div>
          <div class="p-5">
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
              <span>Since <?= date('M Y', strtotime($project['start_date'])) ?></span>
            </div>
            <h3 class="text-lg font-display font-bold text-ink-900 mb-2 group-hover:text-brandRed transition-colors">
              <?= sanitize($project['title']) ?>
            </h3>
            <p class="text-sm text-slate-500 leading-relaxed mb-4">
              <?= sanitize($project['description']) ?>
            </p>
            <div class="mb-3">
              <div class="flex justify-between text-xs mb-1.5">
                <span class="font-semibold text-slate-600">Progress</span>
                <span class="font-bold text-teal-500">100%</span>
              </div>
              <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="progress-bar-fill h-full bg-gradient-to-r from-teal-500 to-teal-400 rounded-full" style="width: 100%"></div>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-xs text-slate-500"><i class="fa-solid fa-users mr-1"></i><?= number_format($project['beneficiaries_count']) ?> beneficiaries</span>
              <span class="text-brandRed text-xs font-semibold group-hover:underline">View Details <i class="fa-solid fa-arrow-right text-[10px]"></i></span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- PROJECT DASHBOARD -->
  <section id="dashboard" class="py-16 sm:py-20 bg-ink-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-2xl sm:text-3xl font-display font-bold mb-3"><?= $projSections['projects_dashboard']['section_title'] ?? '' ?></h2>
        <p class="text-white/60 max-w-lg mx-auto"><?= sanitize($dashData['subtitle'] ?? '') ?></p>
      </div>

      <!-- Metric Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-12">
        <?php foreach ($dashData['metrics'] ?? [] as $metric): ?>
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-5 sm:p-6 text-center stat-card">
          <div class="w-12 h-12 bg-<?= $metric['color'] ?>/20 rounded-xl flex items-center justify-center mx-auto mb-3">
            <i class="<?= $metric['icon'] ?> text-<?= $metric['color'] ?> text-xl"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-display font-bold text-white mb-1"><?= $metric['value'] ?></div>
          <div class="text-xs text-white/50 uppercase tracking-wider font-medium"><?= $metric['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Sector Distribution Bar Chart -->
      <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 sm:p-8">
        <h3 class="text-lg font-display font-bold mb-6 flex items-center gap-2">
          <i class="fa-solid fa-chart-bar text-brandRed"></i> Projects by Sector
        </h3>
        <div class="space-y-5">
          <?php foreach ($dashData['sectors'] ?? [] as $sector): ?>
          <div>
            <div class="flex justify-between text-sm mb-1.5">
              <span class="text-white/80 font-medium"><?= $sector['name'] ?></span>
              <span class="text-white/60 font-semibold"><?= $sector['count'] ?> projects</span>
            </div>
            <div class="w-full h-3 bg-white/10 rounded-full overflow-hidden">
              <div class="bar-chart-bar h-full bg-gradient-to-r <?= $sector['gradient'] ?> rounded-full" style="width: 0%" data-width="<?= $sector['percent'] ?>%"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- SUCCESS STORIES -->
  <section id="stories" class="py-16 sm:py-20 bg-sand-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-2xl sm:text-3xl font-display font-bold text-ink-900 mb-3">Success Stories</h2>
        <p class="text-slate-500 max-w-lg mx-auto">Hear from the communities whose lives have been transformed through our projects.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
        <?php foreach ($successStories as $story): ?>
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-1 mb-4">
            <i class="fa-solid fa-star text-gold-400 text-sm"></i>
            <i class="fa-solid fa-star text-gold-400 text-sm"></i>
            <i class="fa-solid fa-star text-gold-400 text-sm"></i>
            <i class="fa-solid fa-star text-gold-400 text-sm"></i>
            <i class="fa-solid fa-star text-gold-400 text-sm"></i>
          </div>
          <blockquote class="text-slate-600 leading-relaxed mb-6 italic">
            "<?= sanitize($story['quote']) ?>"
          </blockquote>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brandRed/10 rounded-full flex items-center justify-center">
              <i class="fa-solid fa-user text-brandRed text-sm"></i>
            </div>
            <div>
              <div class="font-semibold text-ink-900 text-sm"><?= sanitize($story['person_name']) ?></div>
              <div class="text-xs text-slate-400"><?= sanitize($story['person_title']) ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CTA SECTION -->
  <section class="py-16 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-gradient-to-br from-ink-900 to-ink-800 rounded-3xl p-8 sm:p-12 lg:p-16 text-center relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-brandRed/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-teal-500/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative z-10">
          <div class="w-16 h-16 bg-brandRed/20 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <i class="<?= $ctaData['icon'] ?? 'fa-solid fa-lightbulb' ?> text-brandRed text-2xl"></i>
          </div>
          <h2 class="text-2xl sm:text-3xl font-display font-bold text-white mb-4"><?= $projSections['projects_cta']['section_title'] ?? '' ?></h2>
          <p class="text-white/60 max-w-xl mx-auto mb-8 leading-relaxed">
            <?= sanitize($ctaData['description'] ?? '') ?>
          </p>
          <div class="flex flex-wrap justify-center gap-4">
            <a href="<?= $base ?><?= $ctaData['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>" class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm px-8 py-3.5 rounded-xl shadow-lg shadow-brandRed/30 transition-all flex items-center gap-2">
              <i class="<?= $ctaData['button1_icon'] ?? 'fa-solid fa-handshake' ?> text-xs"></i> <?= sanitize($ctaData['button1_text'] ?? 'Contribute Now') ?>
            </a>
            <a href="<?= $base ?><?= $ctaData['button2_link'] ?? 'contact/index.html' ?>" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white font-bold text-sm px-8 py-3.5 rounded-xl border border-white/20 transition-all flex items-center gap-2">
              <i class="<?= $ctaData['button2_icon'] ?? 'fa-solid fa-envelope' ?> text-xs"></i> <?= sanitize($ctaData['button2_text'] ?? 'Contact Us') ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
  // Filter tabs functionality
  document.querySelectorAll('.filter-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
      document.querySelectorAll('.filter-tab').forEach(function(t) { t.classList.remove('active'); });
      this.classList.add('active');
      var filter = this.getAttribute('data-filter');
      filterProjects(filter);
    });
  });

  function filterProjects(filter) {
    var allCards = document.querySelectorAll('[data-category]');
    var visibleCount = 0;

    allCards.forEach(function(card) {
      var categories = card.getAttribute('data-category').toLowerCase();
      if (filter === 'all' || categories.indexOf(filter.toLowerCase()) !== -1) {
        card.style.display = '';
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
        setTimeout(function() {
          card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, visibleCount * 50);
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    // Show/hide section headers based on visible cards
    var activeSection = document.getElementById('active');
    var completedSection = document.getElementById('completed');
    if (activeSection) {
      var activeCards = activeSection.querySelectorAll('[data-category]');
      var hasVisibleActive = false;
      activeCards.forEach(function(c) { if (c.style.display !== 'none') hasVisibleActive = true; });
      activeSection.style.display = hasVisibleActive ? '' : 'none';
    }
    if (completedSection) {
      var completedCards = completedSection.querySelectorAll('[data-category]');
      var hasVisibleCompleted = false;
      completedCards.forEach(function(c) { if (c.style.display !== 'none') hasVisibleCompleted = true; });
      completedSection.style.display = hasVisibleCompleted ? '' : 'none';
    }

    // Show empty state if no cards visible
    var existingEmpty = document.getElementById('filter-empty-state');
    if (existingEmpty) existingEmpty.remove();

    if (visibleCount === 0) {
      var emptyDiv = document.createElement('div');
      emptyDiv.id = 'filter-empty-state';
      emptyDiv.className = 'col-span-full text-center py-16';
      emptyDiv.innerHTML = '<div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fa-solid fa-folder-open text-slate-300 text-2xl"></i></div><h3 class="text-lg font-display font-bold text-slate-700 mb-2">No projects found</h3><p class="text-sm text-slate-400">No projects match this filter. Try selecting a different category.</p>';
      var targetSection = activeSection && activeSection.style.display !== 'none' ? activeSection : (completedSection && completedSection.style.display !== 'none' ? completedSection : null);
      if (targetSection) {
        var grid = targetSection.querySelector('.grid');
        if (grid) grid.appendChild(emptyDiv);
      }
    }
  }

  // Scroll-triggered animations for bar charts
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        if (entry.target.closest('#dashboard')) {
          document.querySelectorAll('.bar-chart-bar').forEach(function(bar) {
            bar.style.width = bar.getAttribute('data-width');
          });
        }
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('#dashboard').forEach(function(el) { observer.observe(el); });
</script>
