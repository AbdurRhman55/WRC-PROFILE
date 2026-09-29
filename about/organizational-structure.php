<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$pageTitle = 'Organizational Structure | WRC About';

$homepageSections = getRows('homepage_sections', [], 'sort_order ASC');
$pageSections = [];
foreach ($homepageSections as $section) {
    $key = $section['section_key'] ?? '';
    if (in_array($key, ['org_structure_hero', 'org_structure_content'])) {
        $pageSections[$key] = $section;
        $pageSections[$key]['parsed_content'] = json_decode($section['content'] ?? '{}', true);
    }
}

// Override with dedicated table data
$heroRow = getRow('org_structure_hero');
if ($heroRow) $pageSections['org_structure_hero'] = ['section_key' => 'org_structure_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'parsed_content' => $heroRow];
$contentRow = getRow('org_structure_content');
if ($contentRow) {
    $decoded = json_decode($contentRow['content'] ?? '{}', true);
    $pageSections['org_structure_content'] = ['section_key' => 'org_structure_content', 'section_title' => $contentRow['section_title'] ?? '', 'section_subtitle' => $contentRow['section_subtitle'] ?? '', 'parsed_content' => is_array($decoded) ? $decoded : []];
}

$hero = $pageSections['org_structure_hero'] ?? null;
$heroData = $hero['parsed_content'] ?? [];
$content = $pageSections['org_structure_content'] ?? null;
$contentData = $content['parsed_content'] ?? [];

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

  <!-- HERO SECTION -->
  <section class="relative bg-slate-900 text-white min-h-[420px] flex items-center py-20 overflow-hidden">
    <div class="absolute inset-0 z-0">
      <img src="<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>" alt="WRC Structure" class="w-full h-full object-cover opacity-30 mix-blend-luminosity">
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/60"></div>
    </div>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2">
        <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <a href="<?= $base ?>about/index.php" class="hover:text-white transition">About Us</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <span class="text-white">Organizational Structure</span>
      </nav>
      <h1 class="text-4xl md:text-5xl font-extrabold font-heading text-white mb-4">
        <?= (!empty($hero['section_title'])) ? sanitize($hero['section_title']) : (!empty($heroData['section_title']) ? sanitize($heroData['section_title']) : 'Organizational Structure') ?>
      </h1>
      <p class="text-slate-300 text-base md:text-lg max-w-2xl leading-relaxed">
        <?= (!empty($hero['section_subtitle'])) ? sanitize($hero['section_subtitle']) : (!empty($heroData['subtitle']) ? sanitize($heroData['subtitle']) : (!empty($heroData['section_subtitle']) ? sanitize($heroData['section_subtitle']) : 'A clear governance hierarchy ensuring accountability, efficiency, and effective humanitarian service delivery at every level.')) ?>
      </p>
    </div>
  </section>

  <!-- ORG CHART -->
  <section class="py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl font-display font-bold text-slate-900 mb-4">Governance Hierarchy</h2>
        <p class="text-slate-600 max-w-2xl mx-auto">Our organizational structure ensures clear lines of authority, accountability, and communication from governance to field implementation.</p>
      </div>

      <div class="flex flex-col items-center gap-0 max-w-3xl mx-auto">
        
        <!-- Tier 1: Board of Directors -->
        <div class="w-full max-w-2xl bg-ink-900 text-white text-center py-6 px-6 rounded-xl shadow-lg">
          <h3 class="font-bold text-lg mb-1"><i class="fa-solid fa-users-rectangle mr-2"></i> Board of Directors</h3>
          <p class="text-xs text-slate-300">Strategic Oversight & Governance</p>
        </div>
        <div class="w-0.5 h-8 bg-slate-300 border-l border-dashed"></div>

        <!-- Tier 2: Chairman -->
        <div class="w-full max-w-xl bg-ink-800 text-white text-center py-5 px-6 rounded-xl shadow-md">
          <h3 class="font-bold text-lg mb-1"><i class="fa-solid fa-user-tie mr-2"></i> Chairman</h3>
          <p class="text-xs text-slate-300">Organizational Leadership & Representation</p>
        </div>
        <div class="w-0.5 h-8 bg-slate-300 border-l border-dashed"></div>

        <!-- Tier 3: Executive Director -->
        <div class="w-full max-w-lg bg-brandRed text-white text-center py-5 px-6 rounded-xl shadow-lg">
          <h3 class="font-bold text-lg mb-1"><i class="fa-solid fa-briefcase mr-2"></i> Executive Director</h3>
          <p class="text-xs text-white/80">Day-to-Day Management & Program Oversight</p>
        </div>
        <div class="w-0.5 h-8 bg-slate-300 border-l border-dashed"></div>

        <!-- Tier 4: Director Level -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full max-w-3xl">
          <div class="bg-white border-2 border-slate-200 text-center py-4 px-4 rounded-xl shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-1"><i class="fa-solid fa-calculator text-brandRed mr-1"></i> Finance & Admin</h3>
            <p class="text-[10px] text-slate-500">Director of Finance & Administration</p>
          </div>
          <div class="bg-white border-2 border-slate-200 text-center py-4 px-4 rounded-xl shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-1"><i class="fa-solid fa-diagram-project text-brandRed mr-1"></i> Programs</h3>
            <p class="text-[10px] text-slate-500">Director of Programs & Development</p>
          </div>
          <div class="bg-white border-2 border-slate-200 text-center py-4 px-4 rounded-xl shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-1"><i class="fa-solid fa-truck text-brandRed mr-1"></i> Operations</h3>
            <p class="text-[10px] text-slate-500">Head of Operations & Logistics</p>
          </div>
        </div>
        <div class="w-0.5 h-8 bg-slate-300 border-l border-dashed"></div>

        <!-- Tier 5: Field Implementation -->
        <div class="w-full max-w-2xl bg-brandRed/5 border-2 border-brandRed/20 text-center py-5 px-6 rounded-xl shadow-sm">
          <h3 class="font-bold text-brandRed text-lg mb-1"><i class="fa-solid fa-map-location-dot mr-2"></i> Field & Community Implementation</h3>
          <p class="text-xs text-slate-600">Regional Offices, Community Mobilizers, Field Staff</p>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
