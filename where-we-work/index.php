<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Where We Work - Geographic Coverage | WRC';

// Fetch hero + content from dedicated tables
$heroRow   = getRow('where_we_work_hero');
$contentRow = getRow('where_we_work_content');
$heroData  = $heroRow ? (array)$heroRow : [];
$contentData = $contentRow ? (json_decode($contentRow['content'] ?? '{}', true) ?? []) : [];

// ---------- DYNAMIC: build everything from projects ----------
global $pdo;

// Load all projects
$projectsAll = getRows('projects', ['is_active' => 1], 'sort_order ASC');

// Load province models
$provinceModels = getRows('provinces', ['is_active' => 1], 'sort_order ASC');
$provinceById = [];
foreach ($provinceModels as $pm) { $provinceById[$pm['id']] = $pm; }

// Province key mapping (id => svg/slug key)
$provIdToKey = [1 => 'kp', 2 => 'punjab', 3 => 'sindh', 4 => 'balochistan'];
$provKeyToName = ['kp' => 'Khyber Pakhtunkhwa', 'punjab' => 'Punjab', 'sindh' => 'Sindh', 'balochistan' => 'Balochistan'];
$provKeyToIcon = ['kp' => 'fa-mountain', 'punjab' => 'fa-wheat-awn', 'sindh' => 'fa-water', 'balochistan' => 'fa-sun'];

// Load district coordinates from database
$allCoords = getRows('district_coordinates', [], 'sort_order ASC');
$coordKey = static function (string $name): string {
    return (string) preg_replace('/[^a-z0-9]/', '', strtolower(trim($name)));
};
$coordsByName = [];
foreach ($allCoords as $c) {
    $coordsByName[$coordKey((string) ($c['district'] ?? ''))] = $c;
}

// Dashboard project areas are free text and mirror the WRC portfolio sheet:
// "NMD", "D.I. Khan", "Islamabad & Rawalpindi", "Buner, Swabi & Nowshera".
// Every listed area is one coverage area — exactly how the portfolio reports
// it — so a project's beneficiaries and its count are never split or counted
// twice. Known spellings are normalised and the parts are rebuilt into one
// consistent label.
$locationAliases = [
    'nmd'                    => 'NMD (Newly Merged Districts)',
    'nmds'                   => 'NMD (Newly Merged Districts)',
    'newly merged districts' => 'NMD (Newly Merged Districts)',
    'di khan'                => 'D.I. Khan',
    'd.i. khan'              => 'D.I. Khan',
    'dera ismail khan'       => 'D.I. Khan',
];

$parseProjectArea = static function (string $raw) use ($locationAliases): array {
    $parts = preg_split('/\s*(?:,|&|\band\b|\/)\s*/u', trim($raw), -1, PREG_SPLIT_NO_EMPTY);
    $areas = [];
    foreach ((array) $parts as $part) {
        $part = trim((string) preg_replace('/\s+/', ' ', $part));
        if ($part === '') { continue; }
        $areas[strtolower($part)] = $locationAliases[strtolower($part)] ?? $part;
    }
    $areas = array_values($areas);
    if (empty($areas)) {
        return ['key' => '', 'label' => '', 'parts' => []];
    }

    $sorted = $areas;
    sort($sorted, SORT_NATURAL | SORT_FLAG_CASE);

    // "Buner, Swabi & Nowshera": commas between the first areas, "&" before the last.
    $label = count($areas) === 1
        ? $areas[0]
        : implode(', ', array_slice($areas, 0, -1)) . ' & ' . end($areas);

    return [
        'key'   => implode('|', array_map('strtolower', $sorted)),
        'label' => $label,
        'parts' => $areas,
    ];
};

$findCoordinate = static function (string $areaLabel) use ($coordsByName, $coordKey): ?array {
    $key = $coordKey($areaLabel);
    if (isset($coordsByName[$key])) {
        return $coordsByName[$key];
    }
    // The marker table stores the merged districts under the short name "NMD".
    if (strpos($key, 'nmd') === 0 && isset($coordsByName['nmd'])) {
        return $coordsByName['nmd'];
    }
    // "DI Khan", "D.I. Khan" and "Dera Ismail Khan" are one place.
    if ($key === 'dikhan' || $key === 'deraismailkhan') {
        return $coordsByName['dikhan'] ?? $coordsByName['deraismailkhan'] ?? null;
    }
    // A combined area such as "Islamabad & Rawalpindi" or "Buner, Swabi &
    // Nowshera" falls back to the centre of the districts it joins.
    $parts = preg_split('/\s*(?:,|&|\band\b|\/)\s*/u', $areaLabel, -1, PREG_SPLIT_NO_EMPTY);
    if (count((array) $parts) > 1) {
        $xs = [];
        $ys = [];
        foreach ($parts as $part) {
            $partKey = $coordKey($part);
            if (!isset($coordsByName[$partKey])) { return null; }
            $xs[] = (int) $coordsByName[$partKey]['svg_x'];
            $ys[] = (int) $coordsByName[$partKey]['svg_y'];
        }
        if (!empty($xs)) {
            return [
                'svg_x' => (int) round(array_sum($xs) / count($xs)),
                'svg_y' => (int) round(array_sum($ys) / count($ys)),
            ];
        }
    }
    return null;
};

// Build coverage areas from projects. This is the only source for the page: an
// area appears here only when a live project is located there, so stale page
// copy can never invent coverage.
$distinctDistricts = [];
$projectsByProvince = [];
$coveredDistricts = [];
$provinceDistrictSets = [];
foreach ($projectsAll as $proj) {
    $rawDist = trim((string) ($proj['district'] ?? ''));
    if ($rawDist === '') { continue; }

    $area = $parseProjectArea($rawDist);
    if ($area['key'] === '') { continue; }

    // Count the districts behind the areas (a combined area covers several and
    // NMD covers six merged districts), so the headline "Districts Reached"
    // figure stays factual. The helper de-duplicates shared names such as
    // Khyber and Swabi.
    $areaDistricts = wrcPortfolioAreaDistricts($rawDist);
    foreach ($areaDistricts as $districtKey => $districtName) {
        $coveredDistricts[$districtKey] = $districtName;
    }

    $provId = (int) ($proj['province_id'] ?? 1);
    $defaultProvKey = $provIdToKey[$provId] ?? 'kp';
    $defaultProvName = $provinceById[$provId]['name'] ?? ($provKeyToName[$defaultProvKey] ?? 'Khyber Pakhtunkhwa');
    $cat = trim((string) ($proj['category'] ?? 'General'));
    $projectKey = (string) ($proj['slug'] ?? $proj['id'] ?? $proj['title'] ?? '');
    $beneficiaries = (int) ($proj['beneficiaries_count'] ?? 0);
    $projectYear = !empty($proj['start_date']) ? date('Y', strtotime((string) $proj['start_date'])) : '';

    // Islamabad and Rawalpindi belong to the federal capital group.
    $isFederal = false;
    foreach ($area['parts'] as $partName) {
        if (in_array(strtolower(trim($partName)), ['islamabad', 'rawalpindi'], true)) {
            $isFederal = true;
        }
    }

    if ($isFederal) {
        $provKey = 'punjab';
        $provName = 'Punjab / ICT';
    } else {
        $provKey = $defaultProvKey;
        $provName = $defaultProvName;
    }

    // Track the distinct districts per province so the spotlight cards count
    // each place once, exactly like the headline figure.
    if (!isset($provinceDistrictSets[$provKey])) {
        $provinceDistrictSets[$provKey] = [];
    }
    foreach ($areaDistricts as $districtKey => $districtName) {
        $provinceDistrictSets[$provKey][$districtKey] = $districtName;
    }

    $key = $area['key'] . '|' . $provKey;

    if (!isset($distinctDistricts[$key])) {
        $coord = $findCoordinate($area['label']);
        $distinctDistricts[$key] = [
            'district' => $area['label'],
            'district_count' => count($areaDistricts),
            'province' => $provName,
            'province_key' => $provKey,
            'svg_x' => $coord ? (int) $coord['svg_x'] : 0,
            'svg_y' => $coord ? (int) $coord['svg_y'] : 0,
            'has_coord' => (bool) $coord,
            'programs' => [],
            'projects_list' => [],
            'beneficiaries_count' => 0,
            'year' => $projectYear,
        ];
    }

    if (!in_array($cat, $distinctDistricts[$key]['programs'], true)) {
        $distinctDistricts[$key]['programs'][] = $cat;
    }
    $distinctDistricts[$key]['projects_list'][] = [
        'title' => $proj['title'],
        'category' => $cat,
        'status' => $proj['status'] ?? 'active',
        'beneficiaries' => $beneficiaries
    ];
    $distinctDistricts[$key]['beneficiaries_count'] += $beneficiaries;
    if ($projectYear !== '' && ($distinctDistricts[$key]['year'] === '' || (int) $projectYear < (int) $distinctDistricts[$key]['year'])) {
        $distinctDistricts[$key]['year'] = $projectYear;
    }

    if (!isset($projectsByProvince[$provKey])) {
        $projectsByProvince[$provKey] = [
            'name' => $defaultProvName,
            'districts' => [],
            'categories' => [],
            'project_count' => 0,
            'beneficiaries' => 0,
            'project_keys' => [],
            'has_ict' => false,
        ];
    }
    if ($isFederal) {
        $projectsByProvince[$provKey]['has_ict'] = true;
    }
    if (!isset($projectsByProvince[$provKey]['districts'][$area['key']])) {
        $projectsByProvince[$provKey]['districts'][$area['key']] = [
            'name' => $area['label'],
            'district_count' => count($area['parts']),
            'projects' => [],
            'beneficiaries' => 0,
            'year' => $projectYear,
            'svg_x' => $distinctDistricts[$key]['svg_x'],
            'svg_y' => $distinctDistricts[$key]['svg_y'],
        ];
    }
    if (!in_array($proj['title'], $projectsByProvince[$provKey]['districts'][$area['key']]['projects'], true)) {
        $projectsByProvince[$provKey]['districts'][$area['key']]['projects'][] = $proj['title'];
    }
    $districtYear = (string) $projectsByProvince[$provKey]['districts'][$area['key']]['year'];
    if ($projectYear !== '' && ($districtYear === '' || (int) $projectYear < (int) $districtYear)) {
        $projectsByProvince[$provKey]['districts'][$area['key']]['year'] = $projectYear;
    }
    $projectsByProvince[$provKey]['districts'][$area['key']]['beneficiaries'] += $beneficiaries;
    $projectsByProvince[$provKey]['project_keys'][$projectKey] = true;
    $projectsByProvince[$provKey]['beneficiaries'] += $beneficiaries;
    if (!in_array($cat, $projectsByProvince[$provKey]['categories'], true)) {
        $projectsByProvince[$provKey]['categories'][] = $cat;
    }
}

// Count each project once per province and keep the agreed province label.
foreach ($projectsByProvince as $pKey => &$provinceData) {
    $provinceData['project_count'] = count($provinceData['project_keys']);
    unset($provinceData['project_keys']);
    if (!empty($provinceData['has_ict'])) {
        $provinceData['name'] = 'Punjab / ICT';
    }
    unset($provinceData['has_ict']);
}
unset($provinceData);

// Serialize districts for table
$districts = [];
foreach ($distinctDistricts as $row) {
    $areaDistrictNames = [];
    if (stripos((string) $row['district'], 'newly merged') !== false) {
        $areaDistrictNames = array_values(wrcPortfolioAreaDistricts((string) $row['district']));
    }
    $districts[] = [
        'district'     => $row['district'],
        'district_names' => $areaDistrictNames,
        'province'     => $row['province'],
        'province_key' => $row['province_key'],
        'svg_x'        => $row['svg_x'],
        'svg_y'        => $row['svg_y'],
        'programs'     => implode(', ', array_slice($row['programs'], 0, 3)) . (count($row['programs']) > 3 ? ' +' : ''),
        'beneficiaries'=> number_format($row['beneficiaries_count']),
        'year'         => $row['year'],
        'project_count'=> count($row['projects_list']),
        'projects_list'=> $row['projects_list'],
    ];
}
usort($districts, fn($a, $b) => $b['project_count'] <=> $a['project_count'] ?: strcmp($a['district'], $b['district']));

// Build province sections for below-map cards
$configProvinces = $contentData['provinces'] ?? [];
$provinceSections = [];
$provOrder = ['kp', 'punjab', 'sindh', 'balochistan'];
foreach ($provOrder as $pIdx => $pKey) {
    if (!isset($projectsByProvince[$pKey])) {
        // No live project in this province: do not display it. Coverage on
        // this page is project-driven, never copied from old page content.
        continue;
    }
    $cfg = $configProvinces[$pIdx] ?? [];
    $pd = $projectsByProvince[$pKey];

    // Build focus areas from project categories
    $focusAreas = [];
    $catIcons = [
        'Health' => ['icon' => 'fa-stethoscope', 'title' => 'Healthcare', 'desc' => 'Medical facilities, health camps, and disease prevention programs.'],
        'Education' => ['icon' => 'fa-graduation-cap', 'title' => 'Education', 'desc' => 'Schools, literacy programs, and technical education initiatives.'],
        'WASH' => ['icon' => 'fa-faucet-drip', 'title' => 'Water & Sanitation', 'desc' => 'Clean water access, sanitation facilities, and hygiene promotion.'],
        'Food Security' => ['icon' => 'fa-bowl-food', 'title' => 'Food Security', 'desc' => 'Emergency food distributions and agricultural support programs.'],
        'Emergency Response' => ['icon' => 'fa-truck-medical', 'title' => 'Emergency Response', 'desc' => 'Rapid deployment of relief supplies and disaster response.'],
        'Agriculture' => ['icon' => 'fa-seedling', 'title' => 'Agriculture', 'desc' => 'Farming support, livestock care, and agribusiness development.'],
        'Shelter' => ['icon' => 'fa-house', 'title' => 'Shelter', 'desc' => 'Emergency shelter and housing reconstruction programs.'],
        'Child Protection' => ['icon' => 'fa-child', 'title' => 'Child Protection', 'desc' => 'Safeguarding children and providing safe spaces.'],
        'Gender Equality' => ['icon' => 'fa-venus', 'title' => 'Gender Equality', 'desc' => "Women's empowerment and gender-based violence prevention."],
        'Climate Resilience' => ['icon' => 'fa-cloud-sun', 'title' => 'Climate Resilience', 'desc' => 'Adaptation programs for climate-vulnerable communities.'],
    ];
    foreach ($pd['categories'] as $cat) {
        $ci = $catIcons[$cat] ?? ['icon' => 'fa-circle-info', 'title' => $cat, 'desc' => $cat . ' programs across the province.'];
        $focusAreas[] = $ci;
    }

    $provinceAreaCards = array_map(function($d) {
        $areaDistrictNames = [];
        if (stripos((string) $d['name'], 'newly merged') !== false) {
            // Spell out the six merged districts so visitors know what "NMD"
            // covers; the headline count already treats them individually.
            $areaDistrictNames = array_values(wrcPortfolioAreaDistricts((string) $d['name']));
        }
        return [
            'name' => $d['name'],
            // A combined area ("Islamabad & Rawalpindi") covers several
            // districts; the province total should still count them all.
            'district_count' => (int) ($d['district_count'] ?? 1),
            'district_names' => $areaDistrictNames,
            'beneficiaries' => number_format($d['beneficiaries']),
            'year' => $d['year'],
            'project_count' => count($d['projects']),
            'projects' => $d['projects'],
        ];
    }, array_values($pd['districts']));
    // Biggest coverage areas first, so the card reads like the table.
    usort($provinceAreaCards, fn($a, $b) => $b['project_count'] <=> $a['project_count'] ?: strcmp($a['name'], $b['name']));

    $provinceSections[] = [
        'id'   => $pKey,
        'name' => $pd['name'],
        'icon' => $cfg['icon'] ?? ($provKeyToIcon[$pKey] ?? 'fa-map'),
        'bg'   => $cfg['bg'] ?? ($pIdx % 2 === 0 ? 'white' : 'slate-50'),
        'description' => $cfg['description'] ?? ('WRC is actively working in ' . $pd['name'] . ', delivering life-saving and sustainable development programs.'),
        'focus_areas' => $focusAreas,
        'project_count' => $pd['project_count'],
        'district_total' => count($provinceDistrictSets[$pKey] ?? []),
        'beneficiaries' => number_format($pd['beneficiaries']),
        'districts' => $provinceAreaCards,
    ];
}

// Provinces highlighted on the map: only those with a live project.
$activeProvinces = array_keys($projectsByProvince);

// Dynamic stats
$totalDistricts = count($coveredDistricts);
// Headline total uses the canonical published figure so this page, the Impact
// page, the projects dashboard and the homepage never disagree.
$totalBeneficiaries = wrcBeneficiaryTotal();
$statItems = [
    ['label' => 'Provinces Covered', 'value' => (string) count($provinceSections)],
    ['label' => 'Districts Reached', 'value' => (string) $totalDistricts],
    ['label' => 'Beneficiaries Reached', 'value' => number_format($totalBeneficiaries)],
    ['label' => 'Total Projects', 'value' => (string) count($projectsAll)],
];

// Dynamic quick links (from all available province sections)
$quickLinks = [];
foreach ($provinceSections as $ps) {
    $quickLinks[] = [
        'id'   => $ps['id'],
        'name' => $ps['name'],
        'icon' => $ps['icon'] ?? 'fa-map',
        'desc' => is_numeric(str_replace(',', '', $ps['beneficiaries']))
            ? $ps['project_count'] . ' project' . ($ps['project_count'] !== 1 ? 's' : '') . ' · ' . $ps['beneficiaries'] . ' beneficiaries'
            : $ps['project_count'] . ' districts · ' . $ps['beneficiaries'],
    ];
}
// Add districts table link
$quickLinks[] = ['id' => 'districts', 'name' => 'District Coverage', 'icon' => 'fa-layer-group', 'desc' => 'Full breakdown by district & program'];

// Dynamic filter tabs
$filterTabs = [];
foreach ($provinceSections as $ps) {
    $filterTabs[] = ['key' => $ps['id'], 'name' => $ps['name'], 'short' => strtoupper(substr($ps['name'], 0, 2))];
    if ($ps['id'] === 'kp') { $filterTabs[count($filterTabs)-1]['short'] = 'KPK'; }
}
include dirname(__DIR__) . '/includes/header.php';
?>

<style>
  .province-path { cursor: pointer; transition: fill 0.4s ease, filter 0.3s ease, stroke-width 0.3s ease; }
  .province-path:hover { filter: brightness(1.1); stroke-width: 2.5; }
  .province-path.active { stroke: #ca0000; stroke-width: 2; }
  .province-label { pointer-events: none; font-family: 'Inter', sans-serif; font-weight: 600; fill: #1e293b; }
  .province-dot { pointer-events: none; }
  #map-tooltip { transition: opacity 0.2s ease; pointer-events: none; }
  .district-row:hover { background-color: #fef2f2; }
  .search-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(202,0,0,0.15); border-color: #ca0000; }
  .tab-btn.active { background-color: #ca0000; color: white; }
  .stat-counter { font-variant-numeric: tabular-nums; }
  .district-marker { cursor: pointer; transition: r 0.2s ease; }
  .district-marker:hover { r: 8; }
  @keyframes pulse-ring {
    0% { r: 6; opacity: 0.7; }
    100% { r: 14; opacity: 0; }
  }
  .marker-pulse { animation: pulse-ring 2s ease-out infinite; pointer-events: none; }
  .district-tooltip { transition: opacity 0.15s ease; pointer-events: none; }
  .province-card:hover { border-color: rgba(202,0,0,0.3); box-shadow: 0 4px 20px rgba(202,0,0,0.08); }
</style>

<main class="flex-grow">

<!-- HERO SECTION -->
<section class="relative w-full bg-slate-900 text-white min-h-[480px] md:min-h-[540px] lg:min-h-[580px] flex items-center overflow-hidden">
  <div class="absolute inset-0 z-0">
    <div class="absolute inset-0 opacity-50 bg-cover bg-center" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/where-we-work-hero.jpg' ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/50 to-transparent"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-slate-900/30"></div>
  </div>
  <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center gap-2 text-white/60 text-xs font-medium uppercase tracking-widest mb-4">
        <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <span class="text-white">Where We Work</span>
      </div>
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-4">
        <?= $heroRow ? sanitize($heroData['section_title'] ?? 'Where We Work') : 'Where We Work' ?>
      </h1>
      <p class="text-lg text-white/80 max-w-2xl leading-relaxed mb-8">
        <?php
        // Coverage copy is project-driven. A dashboard subtitle that still says
        // "four provinces" would contradict the map, table and counters below,
        // so the province list is rebuilt from the live portfolio.
        $heroSubtitle = $heroRow ? (string) ($heroData['subtitle'] ?? $heroData['section_subtitle'] ?? '') : 'Our presence across Pakistan, serving communities in need.';
        if (preg_match('/four\s+provinces/i', $heroSubtitle)) {
            $coverageNames = array_values(array_filter(array_map(
                static fn(array $section): string => trim((string) ($section['name'] ?? '')),
                $provinceSections
            )));
            if ($coverageNames) {
                $coveragePhrase = count($coverageNames) === 1
                    ? $coverageNames[0]
                    : implode(', ', array_slice($coverageNames, 0, -1)) . ' and ' . end($coverageNames);
                $heroSubtitle = preg_replace('/across\s+four\s+provinces\s+of\s+Pakistan/i', 'in ' . $coveragePhrase, $heroSubtitle);
                $heroSubtitle = preg_replace('/four\s+provinces/i', $coveragePhrase, $heroSubtitle);
            }
        }
        echo sanitize($heroSubtitle);
        ?>
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
<section class="bg-white border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <?php foreach ($statItems as $stat): ?>
      <div class="text-center">
        <div class="text-3xl font-bold text-brandRed stat-counter"><?= sanitize($stat['value'] ?? '') ?></div>
        <div class="text-xs text-slate-500 font-medium uppercase tracking-wider mt-1"><?= sanitize($stat['label'] ?? '') ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- INTERACTIVE PAKISTAN SVG MAP -->
<section id="map" class="py-20 bg-white scroll-mt-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <span class="text-xs font-bold text-brandRed uppercase tracking-widest mb-2 block">Geographic Footprint</span>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-slate-900 mb-3">
        <?= $contentRow ? sanitize($contentRow['section_title'] ?? 'Pakistan Operations Map') : 'Pakistan Operations Map' ?>
      </h2>
      <p class="text-slate-500 max-w-xl mx-auto">
        <?= $contentRow ? sanitize($contentRow['section_subtitle'] ?? 'Click on any province to learn more.') : 'Click on any province to learn more about WRC programs.' ?>
      </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-10 items-start">
      <!-- SVG Map -->
      <div class="relative w-full lg:w-2/3 bg-slate-50 rounded-[2rem] border border-slate-200 p-6 sm:p-10 flex items-center justify-center min-h-[500px]">
        <div id="map-tooltip" class="absolute hidden bg-slate-900 text-white text-xs font-semibold px-3 py-2 rounded-lg shadow-xl z-20 whitespace-nowrap"></div>

        <svg viewBox="0 0 600 700" class="w-full max-w-lg" xmlns="http://www.w3.org/2000/svg">
          <!-- Balochistan -->
          <path id="path-balochistan" class="province-path <?= in_array('balochistan', $activeProvinces) ? 'active' : '' ?>"
            data-province="balochistan"
            d="M 60,350 L 80,320 L 110,310 L 140,290 L 170,280 L 200,260 L 230,250 L 250,230 L 260,210 L 270,200 L 280,190 L 290,185 L 310,210 L 320,240 L 330,270 L 340,290 L 350,310 L 360,330 L 370,360 L 380,380 L 390,400 L 400,420 L 390,440 L 380,460 L 370,480 L 350,490 L 330,500 L 310,510 L 290,520 L 270,530 L 250,540 L 230,535 L 210,520 L 190,510 L 170,500 L 150,490 L 130,480 L 110,470 L 90,450 L 75,420 L 65,390 L 60,350 Z"
            fill="<?= in_array('balochistan', $activeProvinces) ? '#fecaca' : '#cbd5e1' ?>" stroke="#94a3b8" stroke-width="1.5" />
          <text class="province-label" x="210" y="400" text-anchor="middle" font-size="14">Balochistan</text>
          <circle class="province-dot" cx="210" cy="415" r="4" fill="#ca0000" />

          <!-- Sindh -->
          <path id="path-sindh" class="province-path <?= in_array('sindh', $activeProvinces) ? 'active' : '' ?>"
            data-province="sindh"
            d="M 400,420 L 420,400 L 440,390 L 460,380 L 475,370 L 490,355 L 500,340 L 510,320 L 520,300 L 530,290 L 540,280 L 550,270 L 560,280 L 570,300 L 575,320 L 580,350 L 580,380 L 575,410 L 570,440 L 560,470 L 550,490 L 535,505 L 520,520 L 500,530 L 480,535 L 460,530 L 440,520 L 420,510 L 400,500 L 380,490 L 370,480 L 380,460 L 390,440 Z"
            fill="<?= in_array('sindh', $activeProvinces) ? '#fecaca' : '#d1d5db' ?>" stroke="#94a3b8" stroke-width="1.5" />
          <text class="province-label" x="480" y="420" text-anchor="middle" font-size="14">Sindh</text>
          <circle class="province-dot" cx="480" cy="435" r="4" fill="#ca0000" />

          <!-- Punjab -->
          <path id="path-punjab" class="province-path <?= in_array('punjab', $activeProvinces) ? 'active' : '' ?>"
            data-province="punjab"
            d="M 310,210 L 330,195 L 350,180 L 370,165 L 390,155 L 410,150 L 430,155 L 450,160 L 470,170 L 490,180 L 510,195 L 520,210 L 530,230 L 540,250 L 550,270 L 540,280 L 530,290 L 520,300 L 510,320 L 500,340 L 490,355 L 475,370 L 460,380 L 440,390 L 420,400 L 400,420 L 390,400 L 380,380 L 370,360 L 360,330 L 350,310 L 340,290 L 330,270 L 320,240 Z"
            fill="<?= in_array('punjab', $activeProvinces) ? '#fecaca' : '#e2e8f0' ?>" stroke="#94a3b8" stroke-width="1.5" />
          <text class="province-label" x="430" y="290" text-anchor="middle" font-size="14">Punjab</text>
          <circle class="province-dot" cx="430" cy="305" r="4" fill="#ca0000" />

          <!-- Khyber Pakhtunkhwa -->
          <path id="path-kp" class="province-path <?= in_array('kp', $activeProvinces) ? 'active' : '' ?>"
            data-province="kp"
            d="M 270,200 L 280,190 L 290,185 L 310,195 L 330,195 L 350,180 L 370,165 L 360,145 L 350,130 L 340,115 L 330,100 L 320,90 L 310,85 L 300,80 L 290,85 L 280,95 L 270,110 L 260,125 L 250,140 L 245,155 L 250,170 L 260,185 L 270,200 Z"
            fill="<?= in_array('kp', $activeProvinces) ? '#fecaca' : '#f1f5f9' ?>" stroke="#94a3b8" stroke-width="1.5" />
          <text class="province-label" x="305" y="140" text-anchor="middle" font-size="13">KPK</text>
          <circle class="province-dot" cx="305" cy="155" r="4" fill="#ca0000" />

          <!-- Dynamic District Markers -->
          <?php foreach ($distinctDistricts as $dk => $dd): ?>
          <?php if (empty($dd['has_coord'])) { continue; } ?>
          <g class="district-marker-group" data-district="<?= sanitize($dd['district']) ?>" data-province="<?= sanitize($dd['province']) ?>" data-province-key="<?= sanitize($dd['province_key']) ?>" data-projects="<?= count($dd['projects_list']) ?>" data-beneficiaries="<?= number_format($dd['beneficiaries_count']) ?>">
            <circle class="marker-pulse" cx="<?= (int)$dd['svg_x'] ?>" cy="<?= (int)$dd['svg_y'] ?>" r="6" fill="#ca0000" opacity="0.4" />
            <circle class="district-marker" cx="<?= (int)$dd['svg_x'] ?>" cy="<?= (int)$dd['svg_y'] ?>" r="5" fill="#ca0000" stroke="white" stroke-width="2" />
          </g>
          <?php endforeach; ?>

          <!-- Map Legend -->
          <g transform="translate(15, 580)">
            <rect x="0" y="0" width="165" height="90" rx="8" fill="white" stroke="#e2e8f0" stroke-width="1"/>
            <circle cx="18" cy="18" r="5" fill="#ca0000" stroke="white" stroke-width="1.5"/>
            <text x="30" y="22" font-size="10" fill="#475569" font-family="Inter">Active District</text>
            <line x1="10" y1="42" x2="26" y2="42" stroke="#fecaca" stroke-width="8" stroke-linecap="round"/>
            <text x="30" y="46" font-size="10" fill="#475569" font-family="Inter">Active Province</text>
            <line x1="10" y1="62" x2="26" y2="62" stroke="#e2e8f0" stroke-width="8" stroke-linecap="round"/>
            <text x="30" y="66" font-size="10" fill="#475569" font-family="Inter">Inactive Province</text>
          </g>
        </svg>
      </div>

      <!-- Quick Navigation (dynamic from projects) -->
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


<!-- ======================== PROVINCE SECTIONS (dynamic from projects) ======================== -->
<?php foreach ($provinceSections as $prov): ?>
<section id="<?= sanitize($prov['id'] ?? '') ?>" class="py-20 <?= ($prov['bg'] ?? '') === 'white' ? 'bg-white' : 'bg-slate-50' ?> scroll-mt-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-2 text-xs font-bold text-brandRed uppercase tracking-widest mb-2">
      <i class="fa-solid <?= sanitize($prov['icon'] ?? 'fa-circle-info') ?>"></i> Province Spotlight
    </div>
    <h2 class="text-3xl sm:text-4xl font-display font-bold text-slate-900 mb-3"><?= sanitize($prov['name'] ?? '') ?></h2>
    <p class="text-slate-500 max-w-2xl mb-6"><?= sanitize($prov['description'] ?? '') ?></p>

    <!-- Province Stats Bar -->
    <div class="flex flex-wrap gap-4 mb-10">
      <div class="bg-white rounded-xl px-5 py-3 border border-slate-200 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center"><i class="fa-solid fa-folder-open text-brandRed text-xs"></i></div>
        <div><span class="font-bold text-slate-900 text-sm"><?= $prov['project_count'] ?></span> <span class="text-xs text-slate-500">Projects</span></div>
      </div>
      <div class="bg-white rounded-xl px-5 py-3 border border-slate-200 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center"><i class="fa-solid fa-location-dot text-brandRed text-xs"></i></div>
        <div><span class="font-bold text-slate-900 text-sm"><?= (int) ($prov['district_total'] ?? 0) ?></span> <span class="text-xs text-slate-500">Districts</span></div>
      </div>
      <div class="bg-white rounded-xl px-5 py-3 border border-slate-200 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center"><i class="fa-solid fa-users text-brandRed text-xs"></i></div>
        <div><span class="font-bold text-slate-900 text-sm"><?= $prov['beneficiaries'] ?></span> <span class="text-xs text-slate-500">Beneficiaries</span></div>
      </div>
    </div>

    <!-- Focus Areas Cards (from project categories) -->
    <?php if (!empty($prov['focus_areas'])): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
      <?php foreach ($prov['focus_areas'] as $area): ?>
      <div class="bg-white rounded-[2rem] p-6 border border-slate-200 shadow-sm hover:shadow-md transition-all">
        <div class="w-12 h-12 rounded-xl bg-brandRed/10 flex items-center justify-center mb-4">
          <i class="fa-solid <?= sanitize($area['icon'] ?? 'fa-circle-info') ?> text-brandRed text-xl"></i>
        </div>
        <h3 class="font-bold text-slate-900 mb-2"><?= sanitize($area['title'] ?? '') ?></h3>
        <p class="text-sm text-slate-500 leading-relaxed"><?= sanitize($area['desc'] ?? $area['description'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- District Cards -->
    <h3 class="text-lg font-bold text-slate-900 mb-4">Key Districts</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($prov['districts'] ?? [] as $district): ?>
      <div class="province-card bg-white rounded-2xl p-5 border border-slate-200 transition-all">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center"><i class="fa-solid fa-location-dot text-brandRed text-xs"></i></div>
          <span class="font-bold text-slate-900 text-sm"><?= sanitize($district['name'] ?? '') ?></span>
        </div>
        <?php if (!empty($district['district_names'])): ?>
        <p class="text-[11px] text-slate-500 leading-relaxed mb-2"><?= sanitize(implode(', ', $district['district_names'])) ?></p>
        <?php endif; ?>
        <div class="space-y-2 text-xs text-slate-500">
          <div class="flex justify-between"><span><?= $district['project_count'] ?> <?= $district['project_count'] == 1 ? 'Project' : 'Projects' ?></span><span class="font-semibold text-slate-700">Active</span></div>
          <div class="flex justify-between"><span>Beneficiaries</span><span class="font-semibold text-slate-700"><?= sanitize($district['beneficiaries'] ?? '') ?></span></div>
          <div class="flex justify-between"><span>Since</span><span class="font-semibold text-slate-700"><?= sanitize($district['year'] ?? '') ?></span></div>
        </div>
        <?php if (!empty($district['projects'])): ?>
        <div class="mt-3 pt-3 border-t border-slate-100">
          <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Projects</div>
          <?php foreach (array_slice($district['projects'], 0, 2) as $projTitle): ?>
          <div class="text-xs text-slate-600 leading-snug mb-1 truncate" title="<?= sanitize($projTitle) ?>"><?= sanitize($projTitle) ?></div>
          <?php endforeach; ?>
          <?php if (count($district['projects']) > 2): ?>
          <div class="text-[10px] text-brandRed font-semibold">+<?= count($district['projects']) - 2 ?> more</div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
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

    <!-- Dynamic Filter Tabs -->
    <div class="flex flex-wrap justify-center gap-2 mb-6">
      <button class="tab-btn active px-4 py-2 rounded-xl text-xs font-semibold border border-slate-200 bg-brandRed text-white transition-all" data-filter="all">All Provinces</button>
      <?php foreach ($filterTabs as $tab): ?>
      <button class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-600 hover:border-brandRed/30 transition-all" data-filter="<?= sanitize($tab['key']) ?>"><?= sanitize($tab['name']) ?></button>
      <?php endforeach; ?>
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
              <th class="text-center px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Projects</th>
              <th class="text-right px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Beneficiaries</th>
              <th class="text-right px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Year Started</th>
            </tr>
          </thead>
          <tbody id="district-table-body">
            <?php foreach ($districts as $row): ?>
            <tr class="district-row border-b border-slate-100 transition-colors" data-province="<?= sanitize($row['province_key'] ?? '') ?>">
              <td class="px-6 py-4 font-semibold text-slate-900">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-brandRed shrink-0"></span>
                  <?= sanitize($row['district'] ?? '') ?>
                </div>
                <?php if (!empty($row['district_names'])): ?>
                <p class="mt-1 pl-4 text-[11px] font-normal text-slate-400 leading-relaxed"><?= sanitize(implode(', ', $row['district_names'])) ?></p>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4 text-slate-600"><?= sanitize($row['province'] ?? '') ?></td>
              <td class="px-6 py-4"><span class="inline-block bg-brandRed/10 text-brandRed text-xs font-semibold px-2.5 py-1 rounded-lg"><?= sanitize($row['programs'] ?? '') ?></span></td>
              <td class="px-6 py-4 text-center"><span class="inline-block bg-slate-100 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded-lg"><?= $row['project_count'] ?></span></td>
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
        <span id="table-count">Showing <?= count($districts) ?> coverage areas</span>
        <div class="flex items-center gap-2">
          <span>Total Beneficiaries:</span>
          <span class="font-bold text-brandRed"><?= $statItems[2]['value'] ?></span>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<!-- INTERACTIVE MAP + TABLE SCRIPTS -->
<script>
(function() {
  // Province map click + hover
  var provinces = document.querySelectorAll('.province-path');
  var tooltip = document.getElementById('map-tooltip');
  var originalFill = {};
  var activeProvinces = <?= json_encode($activeProvinces) ?>;
  provinces.forEach(function(p) {
    originalFill[p.getAttribute('data-province')] = p.getAttribute('fill');
  });

  provinces.forEach(function(path) {
    path.addEventListener('mouseenter', function(e) {
      var id = this.getAttribute('data-province');
      if (activeProvinces.indexOf(id) === -1) {
        this.setAttribute('fill', '#d1d5db');
      } else {
        this.setAttribute('fill', '#f87171');
      }
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
      this.setAttribute('fill', originalFill[id] || '#e2e8f0');
      tooltip.classList.add('hidden');
    });
    path.addEventListener('click', function() {
      var id = this.getAttribute('data-province');
      var section = document.getElementById(id);
      if (section) section.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // District marker hover tooltips
  var markers = document.querySelectorAll('.district-marker-group');
  markers.forEach(function(mg) {
    var dist = mg.getAttribute('data-district');
    var prov = mg.getAttribute('data-province');
    var projects = mg.getAttribute('data-projects');
    var beneficiaries = mg.getAttribute('data-beneficiaries');
    mg.addEventListener('mouseenter', function(e) {
      tooltip.textContent = dist + ' (' + prov + ') \u2022 ' + projects + ' project' + (projects > 1 ? 's' : '') + ' \u2022 ' + beneficiaries + ' beneficiaries';
      tooltip.classList.remove('hidden');
    });
    mg.addEventListener('mousemove', function(e) {
      var mapContainer = document.querySelector('#map .relative');
      var rect = mapContainer.getBoundingClientRect();
      tooltip.style.left = (e.clientX - rect.left + 12) + 'px';
      tooltip.style.top = (e.clientY - rect.top - 36) + 'px';
    });
    mg.addEventListener('mouseleave', function() {
      tooltip.classList.add('hidden');
    });
    mg.addEventListener('click', function() {
      var provSlug = this.getAttribute('data-province-key') || '';
      var section = document.getElementById(provSlug);
      if (section) section.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // District table filter + search
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
    tableCount.textContent = 'Showing ' + visible + ' coverage area' + (visible !== 1 ? 's' : '');
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
