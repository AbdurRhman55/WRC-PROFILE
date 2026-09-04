<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$pageTitle = 'Who We Are | WRC About';

// Fetch homepage_sections and filter for who-we-are keys
$homepageSections = getRows('homepage_sections', 'is_active = 1 ORDER BY sort_order ASC');
$aboutSections = [];
foreach ($homepageSections as $section) {
    $key = $section['section_key'] ?? '';
    if (in_array($key, ['who_we_are_hero', 'who_we_are_impact', 'why_we_exist'])) {
        $aboutSections[$key] = $section;
        $aboutSections[$key]['parsed_content'] = json_decode($section['content'] ?? '{}', true);
    }
}

// Override with dedicated table data
$heroRow = getRow('who_we_are_hero');
if ($heroRow) {
    $aboutSections['who_we_are_hero'] = ['section_key' => 'who_we_are_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'parsed_content' => $heroRow];
}
$impRow = getRow('who_we_are_impact');
if ($impRow) {
    $items = getRows('who_we_are_impact_items', ['impact_id' => $impRow['id']], 'sort_order ASC');
    $aboutSections['who_we_are_impact'] = ['section_key' => 'who_we_are_impact', 'section_title' => $impRow['section_title'] ?? '', 'section_subtitle' => $impRow['section_subtitle'] ?? '', 'parsed_content' => ['items' => $items]];
}
$whyRow = getRow('why_we_exist');
if ($whyRow) {
    $items = getRows('why_we_exist_items', ['why_id' => $whyRow['id']], 'sort_order ASC');
    $aboutSections['why_we_exist'] = ['section_key' => 'why_we_exist', 'section_title' => $whyRow['section_title'] ?? '', 'section_subtitle' => $whyRow['section_subtitle'] ?? '', 'parsed_content' => ['items' => $items]];
}

// Convenience references
$hero      = $aboutSections['who_we_are_hero'] ?? null;
$heroData  = $hero['parsed_content'] ?? [];
$impact    = $aboutSections['who_we_are_impact'] ?? null;
$impactData = $impact['parsed_content'] ?? [];
$whyExist  = $aboutSections['why_we_exist'] ?? null;
$whyData   = $whyExist['parsed_content'] ?? [];

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

  <!-- HERO SECTION -->
  <section class="relative bg-slate-900 text-white min-h-[420px] flex items-center py-20 overflow-hidden">
    <div class="absolute inset-0 z-0">
      <img src="<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero.jpg' ?>" alt="WRC Team" class="w-full h-full object-cover opacity-30 mix-blend-luminosity">
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/60"></div>
    </div>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2">
        <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <a href="<?= $base ?>about/index.php" class="hover:text-white transition">About Us</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <span class="text-white">Who We Are</span>
      </nav>
      <h1 class="text-4xl md:text-5xl font-extrabold font-heading text-white mb-4">
        <?= (!empty($hero['section_title'])) ? sanitize($hero['section_title']) : (!empty($heroData['section_title']) ? sanitize($heroData['section_title']) : 'Who We Are') ?>
      </h1>
      <p class="text-slate-300 text-base md:text-lg max-w-2xl leading-relaxed">
        <?= (!empty($hero['section_subtitle'])) ? sanitize($hero['section_subtitle']) : (!empty($heroData['subtitle']) ? sanitize($heroData['subtitle']) : (!empty($heroData['section_subtitle']) ? sanitize($heroData['section_subtitle']) : 'WRC is a non-profit humanitarian organization committed to delivering life-saving aid and sustainable development programs to vulnerable communities across Pakistan.')) ?>
      </p>
    </div>
  </section>

  <!-- AREAS OF IMPACT -->
  <?php $impactItems = $impactData['items'] ?? []; ?>
  <section class="py-24 bg-slate-50 border-t border-slate-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16 max-w-2xl mx-auto">
        <h2 class="text-3xl font-display font-bold text-slate-900 mb-4">
          <?= (!empty($impact['section_title'])) ? sanitize($impact['section_title']) : 'Our Areas of Impact' ?>
        </h2>
        <p class="text-slate-600">
          <?= (!empty($impact['section_subtitle'])) ? sanitize($impact['section_subtitle']) : (!empty($impactData['subtitle']) ? sanitize($impactData['subtitle']) : 'We take a holistic approach to community development, addressing immediate needs while building long-term resilience.') ?>
        </p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($impactItems as $item): ?>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-start gap-4 hover:shadow-md transition-shadow group">
          <div class="w-12 h-12 shrink-0 bg-slate-50 group-hover:bg-brandRed/10 rounded-xl flex items-center justify-center transition-colors">
            <i class="fa-solid <?= $item['icon'] ?? 'fa-circle-info' ?> text-slate-400 group-hover:text-brandRed text-lg transition-colors"></i>
          </div>
          <div>
            <h3 class="font-bold text-slate-900 mb-1"><?= sanitize($item['title'] ?? '') ?></h3>
            <p class="text-sm text-slate-500"><?= sanitize($item['description'] ?? '') ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- WHY WE EXIST -->
  <?php $whyItems = $whyData['items'] ?? []; ?>
  <section class="py-24 bg-ink-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
      <h2 class="text-3xl lg:text-4xl font-display font-bold mb-16">
        <?= (!empty($whyExist['section_title'])) ? sanitize($whyExist['section_title']) : 'Why We Exist' ?>
      </h2>
      <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto text-left">
        <?php foreach ($whyItems as $item): ?>
        <div class="space-y-4">
          <i class="fa-solid <?= $item['icon'] ?? 'fa-circle-info' ?> text-3xl text-brandRed"></i>
          <h3 class="text-xl font-bold"><?= sanitize($item['title'] ?? '') ?></h3>
          <p class="text-sand-50/70 text-sm leading-relaxed"><?= sanitize($item['description'] ?? '') ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CTA BANNER -->
  <section class="py-20 bg-slate-950 text-white text-center relative overflow-hidden">
    <div class="absolute inset-0 bg-[url('../assets/images/hero.jpg')] bg-cover bg-center mix-blend-multiply opacity-10"></div>
    <div class="container relative z-10 mx-auto px-4">
      <h2 class="text-3xl md:text-4xl font-display font-bold text-white mb-4 max-w-2xl mx-auto leading-tight">
        Together, we can turn compassion into meaningful action.
      </h2>
      <p class="text-slate-400 text-lg mb-10 max-w-xl mx-auto">Join us in creating lasting change for communities that need it most.</p>
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <a href="<?= $base ?>get-involved/contribute-with-us.php" class="px-8 py-4 rounded-xl bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm shadow-lg shadow-brandRed/30 transition-all uppercase tracking-wider">
          Contribute Now
        </a>
        <a href="<?= $base ?>contact/index.html" class="px-8 py-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm transition-all uppercase tracking-wider">
          Contact Us
        </a>
      </div>
    </div>
  </section>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
