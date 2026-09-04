<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$pageTitle = 'Where We Work - Geographic Coverage | WRC';

// Fetch homepage_sections and filter for where-we-work keys
$homepageSections = getRows('homepage_sections', 'is_active = 1 ORDER BY sort_order ASC');
$whereSections = [];
foreach ($homepageSections as $section) {
    $key = $section['section_key'] ?? '';
    if (in_array($key, ['where_we_work_hero', 'where_we_work_content'])) {
        $whereSections[$key] = $section;
        $whereSections[$key]['parsed_content'] = json_decode($section['content'] ?? '{}', true);
    }
}

// Fetch geographic_presence from homepage_sections for stats fallback
$geoPresence = null;
foreach ($homepageSections as $section) {
    if (($section['section_key'] ?? '') === 'geographic_presence') {
        $geoPresence = json_decode($section['content'] ?? '{}', true);
        break;
    }
}

// Override with dedicated table data
$heroRow = getRow('where_we_work_hero');
if ($heroRow) $whereSections['where_we_work_hero'] = ['section_key' => 'where_we_work_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'parsed_content' => $heroRow];
$contentRow = getRow('where_we_work_content');
if ($contentRow) {
    $decoded = json_decode($contentRow['content'] ?? '{}', true);
    $whereSections['where_we_work_content'] = ['section_key' => 'where_we_work_content', 'section_title' => $contentRow['section_title'], 'section_subtitle' => $contentRow['section_subtitle'], 'parsed_content' => is_array($decoded) ? $decoded : []];
}

// Convenience references
$hero     = $whereSections['where_we_work_hero'] ?? null;
$heroData = $hero['parsed_content'] ?? [];
$content  = $whereSections['where_we_work_content'] ?? null;
$contentData = $content['parsed_content'] ?? [];

// Province data from content section
$provinces     = $contentData['provinces'] ?? [];
$quickLinks    = $contentData['quick_links'] ?? [];
$stats         = $contentData['stats'] ?? [];

// District data from dedicated table
$districts = getRows('where_we_work_districts', [], 'sort_order ASC');

include dirname(__DIR__) . '/includes/header.php';
?>

<style>
  .province-path { cursor: pointer; transition: fill 0.3s ease, filter 0.3s ease; }
  .province-path:hover { filter: brightness(1.15); }
  .province-label { pointer-events: none; font-family: 'Inter', sans-serif; font-weight: 600; fill: #1e293b; }
  .province-dot { pointer-events: none; }
  #map-tooltip { transition: opacity 0.2s ease; }
  .district-row:hover { background-color: #fef2f2; }
  .search-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(202,0,0,0.15); border-color: #ca0000; }
  .tab-btn.active { background-color: #ca0000; color: white; }
  .stat-counter { font-variant-numeric: tabular-nums; }
</style>

<main class="flex-grow">

<!-- HERO SECTION -->
<section class="relative w-full bg-slate-900 text-white min-h-[420px] flex items-center overflow-hidden">
  <div class="absolute inset-0 z-0">
    <div class="absolute inset-0 opacity-30 bg-cover bg-center" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero.jpg' ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-900/80 to-slate-900/60"></div>
  </div>
  <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-20">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center gap-2 text-white/60 text-xs font-medium uppercase tracking-widest mb-4">
        <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <span class="text-white">Where We Work</span>
      </div>
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-4">
        <?= $hero ? sanitize($hero['section_title']) : 'Where We Work' ?>
      </h1>
      <p class="text-lg text-white/80 max-w-2xl leading-relaxed mb-8">
        <?= $hero ? sanitize($heroData['subtitle'] ?? $hero['section_subtitle'] ?? '') : 'WRC operates across four provinces of Pakistan, delivering life-saving relief and sustainable development programs to communities most in need.' ?>
      </p>
      <div class="flex flex-wrap gap-3">
        <a href="#map" class="inline-flex items-center gap-2 bg-brandRed hover:bg-brandRed-hover text-white text-sm font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-brandRed/20">
          <i class="fa-solid fa-map-location-dot text-xs"></i> <?= sanitize($heroData['cta_primary'] ?? 'Explore Our Map') ?>
        </a>
        <a href="#districts" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-sm font-semibold px-6 py-3 rounded-xl border border-white/20 transition-all">
          <i class="fa-solid fa-table-list text-xs"></i> <?= sanitize($heroData['cta_secondary'] ?? 'District Coverage') ?>
        </a>
      </div>
    </div>
  </div>
</section>


<!-- STATS OVERVIEW BAR -->
<?php $statItems = $stats['items'] ?? ($geoPresence['stats']['items'] ?? []); ?>
<section class="bg-white border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <?php if (!empty($statItems)): ?>
        <?php foreach ($statItems as $stat): ?>
        <div class="text-center">
          <div class="text-3xl font-bold text-brandRed stat-counter"><?= sanitize($stat['value'] ?? '') ?></div>
          <div class="text-xs text-slate-500 font-medium uppercase tracking-wider mt-1"><?= sanitize($stat['label'] ?? '') ?></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-center">
          <div class="text-3xl font-bold text-brandRed stat-counter">4</div>
          <div class="text-xs text-slate-500 font-medium uppercase tracking-wider mt-1">Provinces Covered</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-brandRed stat-counter">24+</div>
          <div class="text-xs text-slate-500 font-medium uppercase tracking-wider mt-1">Districts Reached</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-brandRed stat-counter">185,000+</div>
          <div class="text-xs text-slate-500 font-medium uppercase tracking-wider mt-1">Total Beneficiaries</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-brandRed stat-counter">9</div>
          <div class="text-xs text-slate-500 font-medium uppercase tracking-wider mt-1">Active Programs</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>


<!-- INTERACTIVE PAKISTAN SVG MAP -->
<section id="map" class="py-20 bg-white scroll-mt-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <span class="text-xs font-bold text-brandRed uppercase tracking-widest mb-2 block">Geographic Footprint</span>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-slate-900 mb-3">
        <?= $content ? sanitize($content['section_title']) : 'Pakistan Operations Map' ?>
      </h2>
      <p class="text-slate-500 max-w-xl mx-auto">
        <?= $content ? sanitize($content['section_subtitle'] ?? $contentData['subtitle'] ?? '') : 'Click on any province to learn more about WRC\'s programs, districts, and impact on the ground.' ?>
      </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-10 items-center">
      <!-- SVG Map -->
      <div class="relative w-full lg:w-2/3 bg-slate-50 rounded-[2rem] border border-slate-200 p-6 sm:p-10 flex items-center justify-center min-h-[450px]">
        <div id="map-tooltip" class="absolute hidden bg-slate-900 text-white text-xs font-semibold px-3 py-2 rounded-lg shadow-xl z-20 pointer-events-none whitespace-nowrap"></div>

        <svg viewBox="0 0 600 700" class="w-full max-w-lg" xmlns="http://www.w3.org/2000/svg">
          <!-- Balochistan -->
          <path id="path-balochistan" class="province-path" data-province="balochistan"
            d="M 60,350 L 80,320 L 110,310 L 140,290 L 170,280 L 200,260 L 230,250 L 250,230 L 260,210 L 270,200 L 280,190 L 290,185 L 310,210 L 320,240 L 330,270 L 340,290 L 350,310 L 360,330 L 370,360 L 380,380 L 390,400 L 400,420 L 390,440 L 380,460 L 370,480 L 350,490 L 330,500 L 310,510 L 290,520 L 270,530 L 250,540 L 230,535 L 210,520 L 190,510 L 170,500 L 150,490 L 130,480 L 110,470 L 90,450 L 75,420 L 65,390 L 60,350 Z"
            fill="#cbd5e1" stroke="#94a3b8" stroke-width="1.5" />
          <text class="province-label" x="210" y="400" text-anchor="middle" font-size="14">Balochistan</text>
          <circle class="province-dot" cx="210" cy="415" r="4" fill="#ca0000" />

          <!-- Sindh -->
          <path id="path-sindh" class="province-path" data-province="sindh"
            d="M 400,420 L 420,400 L 440,390 L 460,380 L 475,370 L 490,355 L 500,340 L 510,320 L 520,300 L 530,290 L 540,280 L 550,270 L 560,280 L 570,300 L 575,320 L 580,350 L 580,380 L 575,410 L 570,440 L 560,470 L 550,490 L 535,505 L 520,520 L 500,530 L 480,535 L 460,530 L 440,520 L 420,510 L 400,500 L 380,490 L 370,480 L 380,460 L 390,440 Z"
            fill="#d1d5db" stroke="#94a3b8" stroke-width="1.5" />
          <text class="province-label" x="480" y="420" text-anchor="middle" font-size="14">Sindh</text>
          <circle class="province-dot" cx="480" cy="435" r="4" fill="#ca0000" />

          <!-- Punjab -->
          <path id="path-punjab" class="province-path" data-province="punjab"
            d="M 310,210 L 330,195 L 350,180 L 370,165 L 390,155 L 410,150 L 430,155 L 450,160 L 470,170 L 490,180 L 510,195 L 520,210 L 530,230 L 540,250 L 550,270 L 540,280 L 530,290 L 520,300 L 510,320 L 500,340 L 490,355 L 475,370 L 460,380 L 440,390 L 420,400 L 400,420 L 390,400 L 380,380 L 370,360 L 360,330 L 350,310 L 340,290 L 330,270 L 320,240 Z"
            fill="#e2e8f0" stroke="#94a3b8" stroke-width="1.5" />
          <text class="province-label" x="430" y="290" text-anchor="middle" font-size="14">Punjab</text>
          <circle class="province-dot" cx="430" cy="305" r="4" fill="#ca0000" />

          <!-- Khyber Pakhtunkhwa -->
          <path id="path-kp" class="province-path" data-province="kp"
            d="M 270,200 L 280,190 L 290,185 L 310,195 L 330,195 L 350,180 L 370,165 L 360,145 L 350,130 L 340,115 L 330,100 L 320,90 L 310,85 L 300,80 L 290,85 L 280,95 L 270,110 L 260,125 L 250,140 L 245,155 L 250,170 L 260,185 L 270,200 Z"
            fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5" />
          <text class="province-label" x="305" y="140" text-anchor="middle" font-size="13">KPK</text>
          <circle class="province-dot" cx="305" cy="155" r="4" fill="#ca0000" />

          <!-- Province legend -->
          <line x1="15" y1="600" x2="25" y2="600" stroke="#cbd5e1" stroke-width="3" stroke-linecap="round" />
          <line x1="15" y1="618" x2="25" y2="618" stroke="#d1d5db" stroke-width="3" stroke-linecap="round" />
          <line x1="15" y1="636" x2="25" y2="636" stroke="#e2e8f0" stroke-width="3" stroke-linecap="round" />
          <line x1="15" y1="654" x2="25" y2="654" stroke="#f1f5f9" stroke-width="3" stroke-linecap="round" />
          <text x="30" y="603" font-size="10" fill="#64748b" font-family="Inter">Balochistan</text>
          <text x="30" y="621" font-size="10" fill="#64748b" font-family="Inter">Sindh</text>
          <text x="30" y="639" font-size="10" fill="#64748b" font-family="Inter">Punjab</text>
          <text x="30" y="657" font-size="10" fill="#64748b" font-family="Inter">KPK</text>
        </svg>
      </div>

      <!-- Province Quick Links -->
      <div class="w-full lg:w-1/3 space-y-4">
        <h3 class="text-lg font-bold text-slate-900 mb-2">Quick Navigation</h3>
        <?php foreach ($quickLinks as $link): ?>
        <a href="#<?= sanitize($link['id'] ?? '') ?>" class="group flex items-center gap-4 p-4 rounded-2xl border border-slate-200 hover:border-brandRed/30 hover:bg-brandRed-light transition-all">
          <div class="w-12 h-12 rounded-xl bg-slate-100 group-hover:bg-brandRed flex items-center justify-center shrink-0 transition-colors">
            <i class="fa-solid <?= sanitize($link['icon'] ?? 'fa-circle-info') ?> text-slate-500 group-hover:text-white transition-colors"></i>
          </div>
          <div>
            <div class="font-bold text-slate-900 group-hover:text-brandRed transition-colors"><?= sanitize($link['name'] ?? '') ?></div>
            <div class="text-xs text-slate-500"><?= sanitize($link['desc'] ?? '') ?></div>
          </div>
          <i class="fa-solid fa-arrow-right text-slate-300 group-hover:text-brandRed ml-auto transition-colors"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>


<!-- ======================== PROVINCE SECTIONS ======================== -->
<?php foreach ($provinces as $prov): ?>
<section id="<?= sanitize($prov['id'] ?? '') ?>" class="py-20 <?= ($prov['bg'] ?? '') === 'white' ? 'bg-white' : 'bg-slate-50' ?> scroll-mt-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-2 text-xs font-bold text-brandRed uppercase tracking-widest mb-2">
      <i class="fa-solid <?= sanitize($prov['icon'] ?? 'fa-circle-info') ?>"></i> Province Spotlight
    </div>
    <h2 class="text-3xl sm:text-4xl font-display font-bold text-slate-900 mb-3"><?= sanitize($prov['name'] ?? '') ?></h2>
    <p class="text-slate-500 max-w-2xl mb-12"><?= sanitize($prov['description'] ?? '') ?></p>

    <!-- Focus Areas Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
      <?php foreach ($prov['focus_areas'] ?? [] as $area): ?>
      <div class="bg-white rounded-[2rem] p-6 border border-slate-200 shadow-sm hover:shadow-md transition-all">
        <div class="w-12 h-12 rounded-xl bg-brandRed/10 flex items-center justify-center mb-4">
          <i class="fa-solid <?= sanitize($area['icon'] ?? 'fa-circle-info') ?> text-brandRed text-xl"></i>
        </div>
        <h3 class="font-bold text-slate-900 mb-2"><?= sanitize($area['title'] ?? '') ?></h3>
        <p class="text-sm text-slate-500 leading-relaxed"><?= sanitize($area['description'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- District Cards -->
    <h3 class="text-lg font-bold text-slate-900 mb-4">Key Districts</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($prov['districts'] ?? [] as $district): ?>
      <div class="bg-white rounded-2xl p-5 border border-slate-200 hover:border-brandRed/30 transition-all">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center"><i class="fa-solid fa-location-dot text-brandRed text-xs"></i></div>
          <span class="font-bold text-slate-900 text-sm"><?= sanitize($district['name'] ?? '') ?></span>
        </div>
        <div class="space-y-2 text-xs text-slate-500">
          <div class="flex justify-between"><span><?= sanitize($district['program'] ?? '') ?></span><span class="font-semibold text-slate-700"><?= sanitize($district['status'] ?? 'Active') ?></span></div>
          <div class="flex justify-between"><span>Beneficiaries</span><span class="font-semibold text-slate-700"><?= sanitize($district['beneficiaries'] ?? '') ?></span></div>
          <div class="flex justify-between"><span>Since</span><span class="font-semibold text-slate-700"><?= sanitize($district['year'] ?? '') ?></span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endforeach; ?>


<!-- ======================== DISTRICT COVERAGE TABLE ======================== -->
<section id="districts" class="py-20 bg-slate-50 scroll-mt-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10">
      <span class="text-xs font-bold text-brandRed uppercase tracking-widest mb-2 block">Detailed Breakdown</span>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-slate-900 mb-3">District Coverage</h2>
      <p class="text-slate-500 max-w-xl mx-auto">Filter and search across all WRC operational districts to find specific program data and beneficiary statistics.</p>
    </div>

    <!-- Filter Tabs -->
    <div class="flex flex-wrap justify-center gap-2 mb-6">
      <button class="tab-btn active px-4 py-2 rounded-xl text-xs font-semibold border border-slate-200 bg-brandRed text-white transition-all" data-filter="all">All Provinces</button>
      <button class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-600 hover:border-brandRed/30 transition-all" data-filter="Khyber Pakhtunkhwa">KPK</button>
      <button class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-600 hover:border-brandRed/30 transition-all" data-filter="Punjab">Punjab</button>
      <button class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-600 hover:border-brandRed/30 transition-all" data-filter="Sindh">Sindh</button>
      <button class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-600 hover:border-brandRed/30 transition-all" data-filter="Balochistan">Balochistan</button>
    </div>

    <!-- Search -->
    <div class="max-w-md mx-auto mb-8">
      <div class="relative">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input id="district-search" type="text" placeholder="Search by district name..." class="search-input w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 transition-all" />
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
              <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">District</th>
              <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Province</th>
              <th class="text-left px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Programs Active</th>
              <th class="text-right px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Beneficiaries</th>
              <th class="text-right px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Year Started</th>
            </tr>
          </thead>
          <tbody id="district-table-body">
            <?php foreach ($districts as $row): ?>
            <tr class="district-row border-b border-slate-100 transition-colors" data-province="<?= sanitize($row['province'] ?? '') ?>">
              <td class="px-6 py-4 font-semibold text-slate-900"><?= sanitize($row['district'] ?? '') ?></td>
              <td class="px-6 py-4 text-slate-600"><?= sanitize($row['province'] ?? '') ?></td>
              <td class="px-6 py-4"><span class="inline-block bg-brandRed/10 text-brandRed text-xs font-semibold px-2.5 py-1 rounded-lg"><?= sanitize($row['programs'] ?? '') ?></span></td>
              <td class="px-6 py-4 text-right font-semibold text-slate-700"><?= sanitize($row['beneficiaries'] ?? '') ?></td>
              <td class="px-6 py-4 text-right text-slate-500"><?= sanitize($row['year'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div id="no-results" class="hidden text-center py-12 text-slate-400 text-sm">
        <i class="fa-solid fa-magnifying-glass text-2xl mb-3 block text-slate-300"></i>
        No districts match your search. Try a different keyword.
      </div>
      <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500">
        <span id="table-count">Showing <?= count($districts) ?> districts</span>
        <div class="flex items-center gap-2">
          <span>Total Beneficiaries:</span>
          <?php
            $totalBen = '185,000+';
            foreach ($statItems as $si) {
              if (isset($si['label']) && stripos($si['label'], 'beneficiar') !== false) { $totalBen = $si['value']; break; }
            }
          ?>
          <span class="font-bold text-brandRed"><?= sanitize($totalBen) ?></span>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<!-- INTERACTIVE MAP + TABLE SCRIPTS -->
<script>
  // Province map click + hover interactions
  (function() {
    var provinces = document.querySelectorAll('.province-path');
    var tooltip = document.getElementById('map-tooltip');
    var originalColors = { kp: '#f1f5f9', punjab: '#e2e8f0', sindh: '#d1d5db', balochistan: '#cbd5e1' };
    var hoverColor = '#ca0000';

    provinces.forEach(function(path) {
      path.addEventListener('mouseenter', function(e) {
        var id = this.getAttribute('data-province');
        this.setAttribute('fill', hoverColor);
        var name = id.charAt(0).toUpperCase() + id.slice(1);
        if (id === 'kp') name = 'Khyber Pakhtunkhwa';
        tooltip.textContent = name + ' \u2014 Click to explore';
        tooltip.classList.remove('hidden');
      });

      path.addEventListener('mousemove', function(e) {
        var mapContainer = document.querySelector('#map .relative');
        var rect = mapContainer.getBoundingClientRect();
        tooltip.style.left = (e.clientX - rect.left + 12) + 'px';
        tooltip.style.top = (e.clientY - rect.top - 36) + 'px';
      });

      path.addEventListener('mouseleave', function() {
        var id = this.getAttribute('data-province');
        this.setAttribute('fill', originalColors[id] || '#e2e8f0');
        tooltip.classList.add('hidden');
      });

      path.addEventListener('click', function() {
        var id = this.getAttribute('data-province');
        var section = document.getElementById(id);
        if (section) {
          section.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
  })();

  // District table filter + search
  (function() {
    var tabs = document.querySelectorAll('.tab-btn');
    var rows = document.querySelectorAll('.district-row');
    var searchInput = document.getElementById('district-search');
    var noResults = document.getElementById('no-results');
    var tableCount = document.getElementById('table-count');
    var activeFilter = 'all';

    function filterTable() {
      var query = searchInput.value.toLowerCase().trim();
      var visible = 0;
      rows.forEach(function(row) {
        var province = row.getAttribute('data-province');
        var district = row.querySelector('td:first-child').textContent.toLowerCase();
        var matchProvince = (activeFilter === 'all' || province === activeFilter);
        var matchSearch = !query || district.indexOf(query) !== -1;
        if (matchProvince && matchSearch) {
          row.style.display = '';
          visible++;
        } else {
          row.style.display = 'none';
        }
      });
      noResults.classList.toggle('hidden', visible > 0);
      tableCount.textContent = 'Showing ' + visible + ' district' + (visible !== 1 ? 's' : '');
    }

    tabs.forEach(function(tab) {
      tab.addEventListener('click', function() {
        tabs.forEach(function(t) { t.classList.remove('active', 'bg-brandRed', 'text-white'); t.classList.add('bg-white', 'text-slate-600'); });
        this.classList.add('active', 'bg-brandRed', 'text-white');
        this.classList.remove('bg-white', 'text-slate-600');
        activeFilter = this.getAttribute('data-filter');
        filterTable();
      });
    });

    searchInput.addEventListener('input', filterTable);
  })();
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
