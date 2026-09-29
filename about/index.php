<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$pageTitle = 'About Us - World Relief Committee (WRC)';
$metaDescription = 'Learn about World Relief Committee (WRC) - our mission, vision, values, governance, and commitment to transparency in delivering humanitarian aid.';

// Read from dedicated tables
$hero = getRow('about_hero');
$mission = getRow('about_mission');
$missionItems = $mission ? getRows('about_mission_items', ['mission_id' => $mission['id']], 'sort_order ASC') : [];
$governance = getRow('about_governance');
$governanceItems = $governance ? getRows('about_governance_items', ['governance_id' => $governance['id']], 'sort_order ASC') : [];
$transparency = getRow('about_transparency');
$transparencyStats = $transparency ? getRows('about_transparency_stats', ['transparency_id' => $transparency['id']], 'sort_order ASC') : [];
$cta = getRow('about_cta');

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

    <!-- Hero Section -->
    <section class="relative w-full bg-ink-900 text-white min-h-[480px] md:min-h-[540px] lg:min-h-[580px] flex items-center overflow-hidden">
      <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?= $base ?><?= $hero['hero_image'] ?? 'assets/images/about-hero.jpg' ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-ink-900/80 via-ink-900/50 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-ink-900/80 via-transparent to-ink-900/30"></div>
      </div>

      <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24">
        <div class="max-w-7xl mx-auto">
          <!-- Breadcrumb -->
          <nav class="flex items-center gap-2 text-sm text-white/60 mb-8" aria-label="Breadcrumb">
            <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-white font-medium">About Us</span>
          </nav>

          <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-6 max-w-3xl">
            <?= (!empty($hero['section_title'])) ? sanitize($hero['section_title']) : 'About World Relief Committee' ?>
          </h1>
          <p class="text-lg md:text-xl text-sand-50/80 max-w-2xl mb-10 leading-relaxed">
            <?= (!empty($hero['subtitle'])) ? sanitize($hero['subtitle']) : (!empty($hero['section_subtitle']) ? sanitize($hero['section_subtitle']) : 'Dedicated to serving vulnerable communities through compassionate action, transparent governance, and sustainable development since 2005.') ?>
          </p>
          <div class="flex flex-col sm:flex-row gap-4">
            <a href="<?= $base ?><?= $hero['button1_link'] ?? 'about/who-we-are.php' ?>" class="px-8 py-4 rounded-xl bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm shadow-lg shadow-brandRed/30 transition-all uppercase tracking-wider text-center">
              <?= $hero ? sanitize($hero['button1_text'] ?? 'Who We Are') : 'Who We Are' ?>
            </a>
            <a href="<?= $base ?><?= $hero['button2_link'] ?? 'about/leadership.php' ?>" class="px-8 py-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm transition-all uppercase tracking-wider text-center">
              <?= $hero ? sanitize($hero['button2_text'] ?? 'Our Leadership') : 'Our Leadership' ?>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Mission / Vision / Values -->
    <section class="py-16 lg:py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
          <?php foreach ($missionItems as $item): ?>
          <div class="text-center p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-all">
            <div class="w-16 h-16 mx-auto mb-6 rounded-2xl <?= $item['icon_bg'] ?? 'bg-brandRed/10' ?> flex items-center justify-center">
              <i class="fa-solid <?= $item['icon'] ?? 'fa-bullseye' ?> text-2xl <?= $item['icon_color'] ?? 'text-brandRed' ?>"></i>
            </div>
            <h3 class="text-xl font-display font-bold text-slate-900 mb-4"><?= sanitize($item['title'] ?? '') ?></h3>
            <p class="text-slate-600 leading-relaxed text-sm">
              <?= sanitize($item['description'] ?? '') ?>
            </p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Governance Preview -->
    <section class="py-20 lg:py-28 bg-white">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <h2 class="text-3xl lg:text-4xl font-display font-bold text-slate-900 mb-4"><?= (!empty($governance['section_title'])) ? sanitize($governance['section_title']) : 'People & Governance' ?></h2>
          <p class="text-slate-600"><?= (!empty($governance['section_subtitle'])) ? sanitize($governance['section_subtitle']) : 'Guided by responsible leadership and a dedicated team, ensuring effective and transparent implementation of all our programs.' ?></p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
          <?php foreach ($governanceItems as $item): ?>
          <a href="<?= $base ?><?= $item['link'] ?? '#' ?>" class="group bg-slate-50 p-8 rounded-2xl border border-slate-100 hover:border-brandRed/30 hover:bg-white hover:shadow-lg transition-all text-center">
            <i class="fa-solid <?= $item['icon'] ?? 'fa-circle-info' ?> text-3xl text-slate-400 group-hover:text-brandRed transition-colors mb-4"></i>
            <h3 class="text-lg font-bold text-slate-900 mb-2"><?= sanitize($item['title'] ?? '') ?></h3>
            <p class="text-sm text-slate-500 mb-6"><?= sanitize($item['description'] ?? '') ?></p>
            <span class="text-brandRed font-semibold text-xs tracking-wider uppercase group-hover:underline"><?= sanitize($item['link_text'] ?? 'Learn More') ?> &rarr;</span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Transparency & Trust -->
    <section class="py-20 lg:py-28 bg-ink-900 text-white">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-12">
          <div class="md:w-1/2 space-y-6">
            <span class="text-brandRed font-bold text-[10px] tracking-widest uppercase"><?= (!empty($transparency['badge'])) ? sanitize($transparency['badge']) : 'Accountability' ?></span>
            <h2 class="text-3xl lg:text-4xl font-display font-bold"><?= (!empty($transparency['section_title'])) ? sanitize($transparency['section_title']) : 'Transparency & Trust' ?></h2>
            <p class="text-sand-50/80 leading-relaxed text-lg">
              <?= (!empty($transparency['section_subtitle'])) ? sanitize($transparency['section_subtitle']) : 'We believe that strengthening trust through transparency, accountability, and responsible governance is essential to our humanitarian mission.' ?>
            </p>
            <div class="flex flex-wrap gap-4 pt-4">
              <a href="<?= $base ?><?= $transparency['button1_link'] ?? 'about/policies-compliance.php' ?>" class="px-6 py-3 rounded-lg bg-white/10 hover:bg-white hover:text-ink-900 border border-white/20 transition-all font-semibold text-sm">
                <?= sanitize($transparency['button1_text'] ?? 'View Policies') ?>
              </a>
              <a href="<?= $base ?><?= $transparency['button2_link'] ?? 'about/annual-reports.php' ?>" class="px-6 py-3 rounded-lg bg-white/10 hover:bg-white hover:text-ink-900 border border-white/20 transition-all font-semibold text-sm">
                <?= sanitize($transparency['button2_text'] ?? 'View Annual Reports') ?>
              </a>
            </div>
          </div>
          <div class="md:w-1/2 grid grid-cols-2 gap-4">
            <?php foreach ($transparencyStats as $stat): ?>
            <div class="bg-white/5 p-6 rounded-2xl border border-white/10">
              <h4 class="text-3xl font-display font-bold text-brandRed mb-2"><?= sanitize($stat['stat_value'] ?? '') ?></h4>
              <p class="text-sm text-sand-50/70"><?= sanitize($stat['stat_label'] ?? '') ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Final CTA -->
    <section class="py-24 bg-brandRed text-center relative overflow-hidden">
      <div class="absolute inset-0 bg-[url('../assets/images/hero/about-hero.jpg')] bg-cover bg-center mix-blend-multiply opacity-20"></div>
      <div class="container relative z-10 mx-auto px-4">
        <h2 class="text-3xl md:text-5xl font-display font-bold text-white mb-8 max-w-3xl mx-auto leading-tight">
          <?= (!empty($cta['section_title'])) ? sanitize($cta['section_title']) : 'Together, we can turn community needs into meaningful action.' ?>
        </h2>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <a href="<?= $base ?><?= $cta['button1_link'] ?? 'projects/index.php' ?>" class="px-8 py-4 rounded-xl bg-white text-brandRed font-bold text-sm shadow-lg hover:bg-sand-50 transition-colors uppercase tracking-wider">
            <?= sanitize($cta['button1_text'] ?? 'Explore Our Projects') ?>
          </a>
          <a href="<?= $base ?><?= $cta['button2_link'] ?? 'contact/index.php' ?>" class="px-8 py-4 rounded-xl bg-transparent border-2 border-white text-white font-bold text-sm hover:bg-white/10 transition-colors uppercase tracking-wider">
            <?= sanitize($cta['button2_text'] ?? 'Contact Us') ?>
          </a>
        </div>
      </div>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
