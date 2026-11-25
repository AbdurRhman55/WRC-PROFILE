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

// The marker table stores a few groups without a latitude/longitude pair.
// These are fixed geographic facts (not project data), so they live here as a
// fallback and never override a real coordinate.
$coordFallback = [
    'swabi'     => [34.1200, 72.4700],
    'nmd'       => [33.2000, 70.1000],
    'islamabad' => [33.6844, 73.0479],
];

// Resolve the centre of a coverage area. Combined labels such as
// "Buner, Swabi & Nowshera" are averaged from their member districts so the
// marker lands where the work actually happens, not on the label text.
$findLatLng = static function (string $areaLabel) use ($coordsByName, $coordKey, $coordFallback): ?array {
    $parts = preg_split('/\s*(?:,|&|\band\b|\/)\s*/u', trim($areaLabel), -1, PREG_SPLIT_NO_EMPTY);
    $points = [];
    foreach ((array) $parts as $part) {
        $partKey = $coordKey($part);
        $row = $coordsByName[$partKey] ?? null;
        // The marker table stores the merged districts under the short name "NMD".
        if (!$row && strpos($partKey, 'nmd') === 0) {
            $row = $coordsByName['nmd'] ?? null;
            $partKey = 'nmd';
        }
        // "DI Khan", "D.I. Khan" and "Dera Ismail Khan" are one place.
        if (!$row && ($partKey === 'dikhan' || $partKey === 'deraismailkhan')) {
            $row = $coordsByName['dikhan'] ?? $coordsByName['deraismailkhan'] ?? null;
            $partKey = 'dikhan';
        }

        $lat = ($row && $row['lat'] !== null) ? (float) $row['lat'] : ($coordFallback[$partKey][0] ?? null);
        $lng = ($row && $row['lng'] !== null) ? (float) $row['lng'] : ($coordFallback[$partKey][1] ?? null);
        if ($lat === null || $lng === null) {
            return null;
        }
        $points[] = [$lat, $lng];
    }

    if (empty($points)) {
        return null;
    }
    $lat = array_sum(array_column($points, 0)) / count($points);
    $lng = array_sum(array_column($points, 1)) / count($points);
    return [$lat, $lng];
};

// Equirectangular projection fitted to the map's 620 x 570 viewBox. The
// constants come from the public-domain province geometry so district pins and
// province shapes share one coordinate system.
$mapProject = static function (float $lat, float $lng): array {
    $kx    = 0.8625136692072575;
    $scale = 39.42694330760481;
    return [
        'x' => 22 + ((($lng - 69.3) * $kx) + 7.265055231974547) * $scale,
        'y' => 23.353689028049814 + ((30.4 - $lat) + 6.603781127497065) * $scale,
    ];
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
    // Report every project under one of the four programme sectors.
    $cat = wrcProgrammeSector((string) ($proj['category'] ?? 'General'));
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
        $latLng = $findLatLng($area['label']);
        $point  = $latLng ? $mapProject($latLng[0], $latLng[1]) : null;
        $distinctDistricts[$key] = [
            'district' => $area['label'],
            'district_count' => count($areaDistricts),
            'province' => $provName,
            'province_key' => $provKey,
            'lat' => $latLng ? $latLng[0] : null,
            'lng' => $latLng ? $latLng[1] : null,
            'svg_x' => $point ? (int) round($point['x']) : 0,
            'svg_y' => $point ? (int) round($point['y']) : 0,
            'has_coord' => (bool) $point,
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
            'district_count' => count($areaDistricts),
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
        'lat'          => $row['lat'],
        'lng'          => $row['lng'],
        'svg_x'        => $row['svg_x'],
        'svg_y'        => $row['svg_y'],
        'programs'     => implode(', ', array_slice($row['programs'], 0, 3)) . (count($row['programs']) > 3 ? ' +' : ''),
        'beneficiaries'=> number_format($row['beneficiaries_count']),
        'beneficiaries_raw' => (int) $row['beneficiaries_count'],
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

    // Build focus areas from the four programme sectors present in this province.
    $focusAreas = [];
    $catIcons = [
        'Health' => ['icon' => 'fa-stethoscope', 'title' => 'Health', 'desc' => 'Medical facilities, health camps, and disease prevention programs.'],
        'WASH' => ['icon' => 'fa-faucet-drip', 'title' => 'WASH', 'desc' => 'Clean water access, sanitation facilities, and hygiene promotion.'],
        'Food & Relief Distribution' => ['icon' => 'fa-bowl-food', 'title' => 'Food & Relief Distribution', 'desc' => 'Food packages, emergency kits and relief supplies for crisis-affected families.'],
        'Skill Development' => ['icon' => 'fa-graduation-cap', 'title' => 'Skill Development', 'desc' => 'Vocational, technical and paramedic training for employable livelihoods.'],
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

// Map data ---------------------------------------------------------------
$provinceSectionsById = [];
foreach ($provinceSections as $ps) {
    $provinceSectionsById[$ps['id']] = $ps;
}

// Every unit drawn on the map, including those without a current project.
$mapProvinceMeta = [
    'balochistan' => ['name' => 'Balochistan', 'short' => 'Balochistan'],
    'sindh'       => ['name' => 'Sindh', 'short' => 'Sindh'],
    'punjab'      => ['name' => 'Punjab / ICT', 'short' => 'Punjab'],
    'kp'          => ['name' => 'Khyber Pakhtunkhwa', 'short' => 'KP'],
    'gilgit'      => ['name' => 'Gilgit-Baltistan', 'short' => 'GB'],
    'ajk'         => ['name' => 'Azad Jammu & Kashmir', 'short' => 'AJK'],
];
$mapProvinceStats = [];
foreach ($mapProvinceMeta as $mapKey => $meta) {
    $section = $provinceSectionsById[$mapKey] ?? null;
    $mapProvinceStats[$mapKey] = [
        'key'            => $mapKey,
        'name'           => $meta['name'],
        'short'          => $meta['short'],
        'active'         => (bool) $section,
        'project_count'  => (int) ($section['project_count'] ?? 0),
        'district_total' => (int) ($section['district_total'] ?? 0),
        'beneficiaries'  => (int) str_replace(',', '', (string) ($section['beneficiaries'] ?? '0')),
    ];
}

// Coverage pins are clustered when they sit close together, so dense areas
// stay readable. Pins from different provinces never merge.
$mapMarkers = [];
foreach ($districts as $districtRow) {
    if ($districtRow['lat'] === null || $districtRow['lng'] === null) {
        continue;
    }

    $point = [
        'x'             => (float) $districtRow['svg_x'],
        'y'             => (float) $districtRow['svg_y'],
        'province_key'  => (string) $districtRow['province_key'],
        'province'      => (string) $districtRow['province'],
        'projects'      => (int) $districtRow['project_count'],
        'beneficiaries' => (int) $districtRow['beneficiaries_raw'],
        'areas'         => [(string) $districtRow['district']],
    ];

    $merged = false;
    foreach ($mapMarkers as &$cluster) {
        if ($cluster['province_key'] !== $point['province_key']) {
            continue;
        }
        if (hypot($cluster['x'] - $point['x'], $cluster['y'] - $point['y']) > 18) {
            continue;
        }

        $oldWeight = max(1, $cluster['projects']);
        $newWeight = max(1, $point['projects']);
        $weight    = $oldWeight + $newWeight;
        $cluster['x'] = (($cluster['x'] * $oldWeight) + ($point['x'] * $newWeight)) / $weight;
        $cluster['y'] = (($cluster['y'] * $oldWeight) + ($point['y'] * $newWeight)) / $weight;
        $cluster['projects']      += $point['projects'];
        $cluster['beneficiaries'] += $point['beneficiaries'];
        foreach ($point['areas'] as $area) {
            if (!in_array($area, $cluster['areas'], true)) {
                $cluster['areas'][] = $area;
            }
        }
        $merged = true;
        break;
    }
    unset($cluster);

    if (!$merged) {
        $mapMarkers[] = $point;
    }
}

// Small clusters sit at the back; the largest coverage hub stays on top.
usort($mapMarkers, fn($a, $b) => $a['projects'] <=> $b['projects']);
foreach ($mapMarkers as $i => &$cluster) {
    $cluster['x']      = round($cluster['x'], 1);
    $cluster['y']      = round($cluster['y'], 1);
    $cluster['radius'] = round(min(17, 8.5 + sqrt(max(1, $cluster['projects'])) * 2), 1);
    $cluster['halo']   = round($cluster['radius'] + 4, 1);
    $cluster['delay']  = round($i * 0.28, 2);
}
unset($cluster);

// Dynamic filter tabs
$filterTabs = [];
foreach ($provinceSections as $ps) {
    $filterTabs[] = ['key' => $ps['id'], 'name' => $ps['name'], 'short' => strtoupper(substr($ps['name'], 0, 2))];
    if ($ps['id'] === 'kp') { $filterTabs[count($filterTabs)-1]['short'] = 'KPK'; }
}

// Province outlines for the coverage map (public-domain geometry).
$mapGeo = require __DIR__ . '/pakistan-map.php';
include dirname(__DIR__) . '/includes/header.php';
?>

<style>
  /* Coverage map */
  .map-canvas {
    position: relative;
    background-color: #f8fafc;
    background-image: radial-gradient(circle at 1px 1px, rgba(148, 163, 184, 0.22) 1px, transparent 0);
    background-size: 20px 20px;
  }
  .map-country { filter: drop-shadow(0 10px 18px rgba(15, 23, 42, 0.10)); }
  .province-path {
    fill: #e9eef5;
    stroke: #ffffff;
    stroke-width: 1.6;
    stroke-linejoin: round;
    transition: fill 0.25s ease, stroke 0.25s ease;
  }
  .province-path.is-inactive:hover { fill: #dfe7f1; }
  .province-path.is-active { fill: #fee2e2; cursor: pointer; }
  .province-path.is-active:hover { fill: #fecaca; stroke: #fca5a5; }
  .province-path:focus-visible { outline: none; stroke: #ca0000; stroke-width: 2.4; }
  .province-label {
    pointer-events: none;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    letter-spacing: 1.1px;
    text-transform: uppercase;
    paint-order: stroke fill;
    stroke: rgba(255, 255, 255, 0.92);
    stroke-width: 2.6px;
  }
  .province-label.is-active { fill: #991b1b; }
  .province-label.is-inactive { fill: #7b8ba1; }
  .province-sub {
    pointer-events: none;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 7px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    fill: #b91c1c;
    paint-order: stroke fill;
    stroke: rgba(255, 255, 255, 0.9);
    stroke-width: 2px;
  }
  .map-label-sea {
    pointer-events: none;
    font-family: 'Inter', sans-serif;
    font-style: italic;
    fill: #9aaec4;
    letter-spacing: 1.8px;
  }
  .map-scale-line { stroke: #94a3b8; stroke-width: 1.6; stroke-linecap: round; }
  .map-scale-text {
    fill: #94a3b8;
    font-family: 'Inter', sans-serif;
    font-size: 7px;
    letter-spacing: 0.4px;
  }
  .map-marker-group { cursor: pointer; }
  .cluster-halo {
    fill: none;
    stroke: #ca0000;
    stroke-width: 1.4;
    opacity: 0;
    transform-box: fill-box;
    transform-origin: center;
    animation: cluster-ping 3.4s cubic-bezier(0, 0, 0.2, 1) infinite;
    pointer-events: none;
  }
  @keyframes cluster-ping {
    0% { transform: scale(0.55); opacity: 0.45; }
    70%, 100% { transform: scale(1.8); opacity: 0; }
  }
  .cluster-dot {
    fill: #ca0000;
    stroke: #ffffff;
    stroke-width: 2.4;
    transition: fill 0.2s ease, transform 0.2s ease;
    transform-box: fill-box;
    transform-origin: center;
  }
  .map-marker-group:hover .cluster-dot,
  .map-marker-group:focus-visible .cluster-dot { fill: #a80000; transform: scale(1.08); }
  .map-marker-group:focus-visible { outline: none; }
  .cluster-count {
    fill: #ffffff;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 9px;
    text-anchor: middle;
    dominant-baseline: central;
    pointer-events: none;
  }
  #map-tooltip { transition: opacity 0.15s ease; }
  @media (prefers-reduced-motion: reduce) {
    .cluster-halo { animation: none; opacity: 0.16; }
    .cluster-dot { transition: none; }
  }

  .district-row:hover { background-color: #fef2f2; }
  .search-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(202,0,0,0.15); border-color: #ca0000; }
  .tab-btn.active { background-color: #ca0000; color: white; }
  .stat-counter { font-variant-numeric: tabular-nums; }
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
  <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-10 md:py-12 lg:py-12">
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


<!-- INTERACTIVE PAKISTAN COVERAGE MAP -->
<section id="map" class="relative overflow-hidden bg-slate-50 py-12 lg:py-16 scroll-mt-24">
  <div class="pointer-events-none absolute -right-24 top-16 h-72 w-72 rounded-full bg-brandRed/5 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-20 bottom-0 h-64 w-64 rounded-full bg-gold-400/10 blur-3xl"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl text-center mb-10">
      <span class="text-xs font-bold text-brandRed uppercase tracking-[0.3em] mb-2 block">Geographic Footprint</span>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-slate-900 mb-3">
        <?= $contentRow ? sanitize($contentRow['section_title'] ?? 'Pakistan Operations Map') : 'Pakistan Operations Map' ?>
      </h2>
      <p class="text-slate-500 max-w-xl mx-auto">
        <?= $contentRow ? sanitize($contentRow['section_subtitle'] ?? 'Click on any province to learn more.') : 'Click on any province to learn more about WRC programs.' ?>
      </p>
    </div>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px] xl:grid-cols-[minmax(0,1fr)_390px]">

      <!-- MAP CARD -->
      <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_50px_-24px_rgba(15,23,42,0.35)]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
          <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-brandRed/10 text-brandRed">
              <i class="fa-solid fa-map-location-dot text-sm"></i>
            </span>
            <div>
              <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Coverage map</div>
              <div class="text-sm font-semibold text-slate-900"><?= count($districts) ?> coverage areas · <?= count($projectsAll) ?> live projects</div>
            </div>
          </div>
          <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-emerald-600">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live portfolio
          </span>
        </div>

        <div id="map-canvas" class="map-canvas px-2 py-3 sm:px-5 sm:py-5">
          <div id="map-tooltip" class="pointer-events-none absolute left-0 top-0 z-30 hidden w-max max-w-[250px] rounded-xl border border-white/10 bg-slate-900/95 px-3.5 py-3 text-left text-white shadow-2xl backdrop-blur">
            <div class="tt-eyebrow text-[9px] font-bold uppercase tracking-[0.18em] text-white/45"></div>
            <div class="tt-title mt-1 text-xs font-semibold leading-snug text-white"></div>
            <div class="tt-meta mt-1 text-[11px] leading-snug text-white/60"></div>
            <div class="tt-hint mt-2 text-[9px] font-bold uppercase tracking-wider text-red-300"></div>
          </div>

          <div class="pointer-events-none absolute right-4 top-4 flex h-8 w-8 flex-col items-center justify-center rounded-full border border-slate-200 bg-white/85 text-slate-400 shadow-sm backdrop-blur">
            <i class="fa-solid fa-arrow-up text-[9px]"></i>
            <span class="text-[7px] font-bold leading-none">N</span>
          </div>

          <svg viewBox="<?= sanitize($mapGeo['viewBox']) ?>" class="relative mx-auto block h-auto w-full max-w-[560px]" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Map of Pakistan showing WRC project coverage by province and district">
            <g class="map-country">
              <?php
              $pathToProvince = ['ict' => 'punjab'];
              foreach ($mapGeo['paths'] as $geoKey => $geoPath):
                  $provinceKey = $pathToProvince[$geoKey] ?? $geoKey;
                  $province    = $mapProvinceStats[$provinceKey] ?? null;
                  if (!$province) { continue; }
                  $isActive = $province['active'];
                  $ariaLabel = $isActive
                      ? $province['name'] . ': ' . $province['project_count'] . ' projects across ' . $province['district_total'] . ' districts'
                      : $province['name'] . ': no current projects';
              ?>
              <path id="path-<?= sanitize($geoKey) ?>"
                class="province-path <?= $isActive ? 'is-active' : 'is-inactive' ?>"
                d="<?= $geoPath ?>"
                data-province="<?= sanitize($provinceKey) ?>"
                data-province-key="<?= $isActive ? sanitize($provinceKey) : '' ?>"
                data-name="<?= sanitize($province['name']) ?>"
                data-projects="<?= (int) $province['project_count'] ?>"
                data-districts="<?= (int) $province['district_total'] ?>"
                data-beneficiaries="<?= number_format($province['beneficiaries']) ?>"
                data-active="<?= $isActive ? '1' : '0' ?>"
                <?= $isActive ? 'tabindex="0" role="button"' : '' ?>
                aria-label="<?= sanitize($ariaLabel) ?>"></path>
              <?php endforeach; ?>
            </g>

            <g class="map-labels">
              <?php foreach ($mapGeo['labels'] as $label): ?>
                <?php
                if ($label['key'] === 'sea') {
                    $labelClass = 'map-label-sea';
                } else {
                    $labelProvince = $mapProvinceStats[$label['key']] ?? null;
                    $labelClass = 'province-label ' . (($labelProvince['active'] ?? false) ? 'is-active' : 'is-inactive');
                }
                $lineGap = $label['size'] + 2;
                ?>
                <text x="<?= $label['x'] ?>" y="<?= $label['y'] ?>" text-anchor="middle" class="<?= $labelClass ?>" font-size="<?= $label['size'] ?>">
                  <?php foreach ($label['lines'] as $lineIndex => $lineText): ?>
                    <tspan x="<?= $label['x'] ?>" dy="<?= $lineIndex === 0 ? 0 : $lineGap ?>"><?= sanitize($lineText) ?></tspan>
                  <?php endforeach; ?>
                </text>
                <?php if ($label['key'] !== 'sea' && !empty($mapProvinceStats[$label['key']]['active'])): ?>
                  <?php
                  $labelProjects = (int) $mapProvinceStats[$label['key']]['project_count'];
                  $subY = $label['y'] + (count($label['lines']) - 1) * $lineGap + 11;
                  ?>
                  <text x="<?= $label['x'] ?>" y="<?= $subY ?>" text-anchor="middle" class="province-sub"><?= $labelProjects ?> <?= $labelProjects === 1 ? 'project' : 'projects' ?></text>
                <?php endif; ?>
              <?php endforeach; ?>
            </g>

            <g class="map-markers">
              <?php foreach ($mapMarkers as $marker): ?>
                <?php
                $markerProjects = (int) $marker['projects'];
                $markerAreas    = implode(', ', $marker['areas']);
                $markerAria     = $markerAreas . ': ' . $markerProjects . ' ' . ($markerProjects === 1 ? 'project' : 'projects') . '. Click to view ' . $marker['province'] . '.';
                ?>
                <g class="map-marker-group"
                  tabindex="0"
                  role="button"
                  data-province="<?= sanitize($marker['province']) ?>"
                  data-province-key="<?= sanitize($marker['province_key']) ?>"
                  data-areas="<?= sanitize($markerAreas) ?>"
                  data-projects="<?= $markerProjects ?>"
                  data-beneficiaries="<?= number_format($marker['beneficiaries']) ?>"
                  aria-label="<?= sanitize($markerAria) ?>">
                  <circle class="cluster-halo" cx="<?= $marker['x'] ?>" cy="<?= $marker['y'] ?>" r="<?= $marker['halo'] ?>" style="animation-delay: <?= $marker['delay'] ?>s"></circle>
                  <circle class="cluster-dot" cx="<?= $marker['x'] ?>" cy="<?= $marker['y'] ?>" r="<?= $marker['radius'] ?>"></circle>
                  <text class="cluster-count" x="<?= $marker['x'] ?>" y="<?= $marker['y'] ?>"><?= $markerProjects ?></text>
                </g>
              <?php endforeach; ?>
            </g>

            <g class="map-scale" transform="translate(526, 548)" aria-hidden="true">
              <line class="map-scale-line" x1="0" y1="0" x2="<?= $mapGeo['scale200'] ?>" y2="0"></line>
              <line class="map-scale-line" x1="0" y1="-3" x2="0" y2="3"></line>
              <line class="map-scale-line" x1="<?= $mapGeo['scale200'] ?>" y1="-3" x2="<?= $mapGeo['scale200'] ?>" y2="3"></line>
              <text class="map-scale-text" x="<?= $mapGeo['scale200'] / 2 ?>" y="-6" text-anchor="middle">200 km</text>
            </g>
          </svg>
        </div>

        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-slate-100 px-5 py-3 text-[11px] text-slate-500 sm:px-6">
          <span class="inline-flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-brandRed ring-2 ring-white shadow"></span>
            Coverage area <span class="text-slate-300">·</span> <span class="text-slate-400">dot size = projects</span>
          </span>
          <span class="inline-flex items-center gap-2">
            <span class="h-2.5 w-4 rounded-[3px] bg-[#fee2e2] ring-1 ring-red-200"></span>
            Active province
          </span>
          <span class="inline-flex items-center gap-2">
            <span class="h-2.5 w-4 rounded-[3px] bg-[#e9eef5] ring-1 ring-slate-200"></span>
            No current projects
          </span>
          <span class="ml-auto hidden text-[10px] text-slate-300 md:inline">Boundaries: geoBoundaries · Public domain</span>
        </div>
      </div>

      <!-- COVERAGE PANEL -->
      <aside class="flex w-full flex-col gap-4">

        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="text-sm font-bold text-slate-900">Active Coverage</h3>
              <p class="mt-1 text-[11px] text-slate-400">Live presence by province</p>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-brandRed/5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-brandRed">
              <?= count($provinceSections) ?> active
            </span>
          </div>

          <div class="mt-4 space-y-3">
            <?php foreach ($provinceSections as $prov): ?>
              <?php $provinceShare = (int) round(($prov['project_count'] / max(1, count($projectsAll))) * 100); ?>
              <a href="#<?= sanitize($prov['id']) ?>" class="group block rounded-2xl border border-slate-100 p-3.5 transition-all hover:border-brandRed/30 hover:bg-brandRed/[0.03]">
                <div class="flex items-start gap-3">
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brandRed/10 text-brandRed">
                    <i class="fa-solid <?= sanitize($prov['icon'] ?? 'fa-map') ?> text-xs"></i>
                  </span>
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                      <span class="truncate text-sm font-semibold text-slate-900 group-hover:text-brandRed"><?= sanitize($prov['name']) ?></span>
                      <span class="shrink-0 text-[10px] font-bold text-slate-400"><?= (int) $prov['project_count'] ?> <?= (int) $prov['project_count'] === 1 ? 'project' : 'projects' ?></span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-x-2 text-[11px] text-slate-500">
                      <span><?= (int) $prov['district_total'] ?> districts</span>
                      <span class="text-slate-300">·</span>
                      <span><?= sanitize($prov['beneficiaries']) ?> reached</span>
                    </div>
                    <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                      <div class="h-full rounded-full bg-gradient-to-r from-brandRed to-red-400" style="width: <?= max(4, $provinceShare) ?>%"></div>
                    </div>
                  </div>
                  <i class="fa-solid fa-arrow-right mt-1 text-[10px] text-slate-300 transition-all group-hover:translate-x-0.5 group-hover:text-brandRed"></i>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
          <p class="mt-3 text-[10px] leading-4 text-slate-400">Bar length shows each province’s share of the live project portfolio.</p>
        </div>

        <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-slate-900">Largest coverage areas</h3>
            <span class="text-[9px] font-bold uppercase tracking-[0.18em] text-slate-400">By projects</span>
          </div>

          <?php
          $topAreas = array_slice($districts, 0, 5);
          $maxAreaProjects = max(1, (int) ($topAreas[0]['project_count'] ?? 1));
          ?>
          <div class="mt-4 space-y-3.5">
            <?php foreach ($topAreas as $areaIndex => $area): ?>
              <div class="flex items-center gap-3">
                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-slate-100 text-[10px] font-bold text-slate-500"><?= $areaIndex + 1 ?></span>
                <div class="min-w-0 flex-1">
                  <div class="flex items-baseline justify-between gap-2">
                    <span class="truncate text-xs font-semibold text-slate-800"><?= sanitize($area['district']) ?></span>
                    <span class="shrink-0 text-[10px] font-semibold text-slate-400"><?= (int) $area['project_count'] ?> <?= (int) $area['project_count'] === 1 ? 'project' : 'projects' ?></span>
                  </div>
                  <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-brandRed/60" style="width: <?= max(6, (int) round(((int) $area['project_count'] / $maxAreaProjects) * 100)) ?>%"></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <a href="#districts" class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-brandRed transition-all hover:gap-3">
            Full district breakdown <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </a>
        </div>

        <a href="#districts" class="group relative overflow-hidden rounded-[1.75rem] bg-ink-900 p-5 text-white shadow-sm transition-transform hover:-translate-y-0.5">
          <span class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-brandRed/25 blur-2xl"></span>
          <div class="relative flex items-center gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10">
              <i class="fa-solid fa-table-list text-sm"></i>
            </span>
            <div class="min-w-0 flex-1">
              <div class="text-sm font-bold">District Coverage</div>
              <div class="mt-0.5 text-[11px] text-white/50"><?= count($districts) ?> areas · search &amp; filter</div>
            </div>
            <i class="fa-solid fa-arrow-right text-xs text-white/40 transition-transform group-hover:translate-x-1"></i>
          </div>
        </a>
      </aside>
    </div>
  </div>
</section>



<!-- ======================== PROVINCE SECTIONS (dynamic from projects) ======================== -->
<?php foreach ($provinceSections as $prov): ?>
<section id="<?= sanitize($prov['id'] ?? '') ?>" class="py-12 <?= ($prov['bg'] ?? '') === 'white' ? 'bg-white' : 'bg-slate-50' ?> scroll-mt-24">
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
<section id="districts" class="py-12 bg-slate-50 scroll-mt-24">
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
  var canvas = document.getElementById('map-canvas');
  var tooltip = document.getElementById('map-tooltip');
  if (!canvas || !tooltip) return;

  var tipEyebrow = tooltip.querySelector('.tt-eyebrow');
  var tipTitle = tooltip.querySelector('.tt-title');
  var tipMeta = tooltip.querySelector('.tt-meta');
  var tipHint = tooltip.querySelector('.tt-hint');

  function hideTip() {
    tooltip.classList.add('hidden');
  }

  function positionTip(e) {
    var rect = canvas.getBoundingClientRect();
    var x = e.clientX - rect.left;
    var y = e.clientY - rect.top;
    var tw = tooltip.offsetWidth;
    var th = tooltip.offsetHeight;
    var left = x - tw / 2;
    var top = y - th - 16;

    if (left < 10) left = 10;
    if (left + tw > rect.width - 10) left = rect.width - tw - 10;
    if (top < 10) top = y + 20;
    if (top + th > rect.height - 10) top = Math.max(10, rect.height - th - 10);

    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';
  }

  function showTip(e, eyebrow, title, meta, hint) {
    tipEyebrow.textContent = eyebrow;
    tipTitle.textContent = title;
    tipMeta.textContent = meta;
    tipHint.textContent = hint || '';
    tipHint.classList.toggle('hidden', !hint);
    tooltip.classList.remove('hidden');
    positionTip(e);
  }

  function plural(count, word) {
    return count + ' ' + word + (count === 1 ? '' : 's');
  }

  function goTo(target) {
    if (!target) return;
    var section = document.getElementById(target);
    if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function focusTip(element) {
    var box = element.getBoundingClientRect();
    return { clientX: box.left + box.width / 2, clientY: box.top + box.height / 2 };
  }

  // Province outlines
  document.querySelectorAll('.province-path').forEach(function(path) {
    var active = path.getAttribute('data-active') === '1';
    var target = active ? path.getAttribute('data-province-key') : '';
    var name = path.getAttribute('data-name') || '';
    var projects = parseInt(path.getAttribute('data-projects') || '0', 10);
    var districts = parseInt(path.getAttribute('data-districts') || '0', 10);
    var beneficiaries = path.getAttribute('data-beneficiaries') || '0';

    function show(e) {
      if (active) {
        showTip(
          e,
          'Active province',
          name,
          plural(projects, 'project') + ' · ' + plural(districts, 'district') + ' · ' + beneficiaries + ' reached',
          'Click to view ' + name
        );
      } else {
        showTip(e, 'Province', name, 'No current projects in the live portfolio', '');
      }
    }

    path.addEventListener('mouseenter', show);
    path.addEventListener('mousemove', positionTip);
    path.addEventListener('mouseleave', hideTip);
    path.addEventListener('focus', function() { show(focusTip(path)); });
    path.addEventListener('blur', hideTip);

    if (active) {
      path.addEventListener('click', function() { goTo(target); });
      path.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          goTo(target);
        }
      });
    }
  });

  // Clustered coverage pins
  document.querySelectorAll('.map-marker-group').forEach(function(marker) {
    var target = marker.getAttribute('data-province-key') || '';
    var province = marker.getAttribute('data-province') || '';
    var areas = marker.getAttribute('data-areas') || '';
    var projects = parseInt(marker.getAttribute('data-projects') || '0', 10);
    var beneficiaries = marker.getAttribute('data-beneficiaries') || '0';

    function show(e) {
      showTip(
        e,
        province,
        areas,
        plural(projects, 'project') + ' · ' + beneficiaries + ' people reached',
        'Click to explore ' + province
      );
    }

    marker.addEventListener('mouseenter', show);
    marker.addEventListener('mousemove', positionTip);
    marker.addEventListener('mouseleave', hideTip);
    marker.addEventListener('focus', function() { show(focusTip(marker)); });
    marker.addEventListener('blur', hideTip);
    marker.addEventListener('click', function() { goTo(target); });
    marker.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        goTo(target);
      }
    });
  });

  // Hide the tooltip when the pointer leaves the map or the page scrolls.
  canvas.addEventListener('mouseleave', hideTip);
  window.addEventListener('scroll', hideTip, { passive: true });

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
