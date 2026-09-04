<?php
/**
 * WRC News & Media Center (Dynamic)
 * News articles, press releases, events, galleries, blogs
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';

// Fetch all homepage_sections and filter for news_hero
$allSections = getRows('homepage_sections', '', '');
$newsSections = [];
foreach ($allSections as $section) {
    if (strpos($section['section_key'], 'news_') === 0) {
        $newsSections[$section['section_key']] = $section;
        $newsSections[$section['section_key']]['parsed_content'] = json_decode($section['content'] ?? '{}', true);
    }
}

// Override with dedicated table data
$heroRow = getRow('news_hero');
if ($heroRow) $newsSections['news_hero'] = ['section_key' => 'news_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'parsed_content' => $heroRow];

// Hero data
$heroSection = $newsSections['news_hero'] ?? null;
$heroData = $heroSection['parsed_content'] ?? [];

// Fetch all active news articles ordered by published date
$news = getRows('news_articles', 'is_published = 1 ORDER BY published_at DESC');
$news = array_map(function($n) { $n['published_date'] = $n['published_at'] ?? ''; return $n; }, $news);

// Filter by category
$pressReleases = array_filter($news, function ($n) {
    return ($n['category'] ?? '') === 'press_release';
});
$blogs = array_filter($news, function ($n) {
    return ($n['category'] ?? '') === 'blog';
});

// Category color map
$categoryColors = [
    'relief'        => 'bg-brandRed',
    'wash'          => 'bg-teal-500',
    'health'        => 'bg-blue-500',
    'education'     => 'bg-amber-500',
    'food_security' => 'bg-green-500',
    'nutrition'     => 'bg-purple-500',
    'livelihood'    => 'bg-orange-500',
    'press_release' => 'bg-brandRed',
    'blog'          => 'bg-brandRed',
    'event'         => 'bg-teal-500',
];

$categoryLabels = [
    'relief'        => 'Relief',
    'wash'          => 'WASH',
    'health'        => 'Health',
    'education'     => 'Education',
    'food_security' => 'Food Security',
    'nutrition'     => 'Nutrition',
    'livelihood'    => 'Livelihood',
    'press_release' => 'Press Release',
    'blog'          => 'Blog',
    'event'         => 'Event',
];

// Press release icon/color map based on sub_type or default
$pressIconMap = [
    'official_statement' => ['icon' => 'fa-bullhorn', 'bg' => 'bg-brandRed/10', 'color' => 'text-brandRed', 'btnBg' => 'bg-brandRed', 'btnHover' => 'hover:bg-brandRed-hover'],
    'annual_report'      => ['icon' => 'fa-file-lines', 'bg' => 'bg-teal-500/10', 'color' => 'text-teal-500', 'btnBg' => 'bg-teal-500', 'btnHover' => 'hover:bg-teal-600'],
    'partnership'        => ['icon' => 'fa-handshake', 'bg' => 'bg-gold-400/10', 'color' => 'text-gold-500', 'btnBg' => 'bg-gold-500', 'btnHover' => 'hover:bg-gold-600'],
    'emergency_appeal'   => ['icon' => 'fa-triangle-exclamation', 'bg' => 'bg-blue-500/10', 'color' => 'text-blue-500', 'btnBg' => 'bg-blue-500', 'btnHover' => 'hover:bg-blue-600'],
];

$pageTitle = 'News & Media Center | WRC';
$metaDescription = 'Stay informed with the latest updates from WRC. Browse news articles, press releases, event calendars, photo galleries, and video reports from the field.';

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- ============================================================ -->
<!-- HERO SECTION -->
<!-- ============================================================ -->
<section class="relative w-full bg-ink-900 text-white min-h-[420px] flex items-center overflow-hidden">
  <div class="absolute inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero.jpg' ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-ink-900 via-ink-800/95 to-brandRed/20"></div>
  </div>
  <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
    <div class="max-w-3xl">
      <div class="flex items-center gap-2 mb-4">
        <a href="<?= $base ?>index.php" class="text-white/60 hover:text-white text-sm transition-colors">Home</a>
        <i class="fa-solid fa-chevron-right text-[9px] text-white/40"></i>
        <span class="text-brandRed text-sm font-semibold">News & Media</span>
      </div>
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-bold leading-tight mb-5">
        <?= $heroSection ? sanitize($heroSection['section_title']) : 'News & <span class="text-brandRed">Media</span> Center' ?>
      </h1>
      <p class="text-lg text-white/70 max-w-2xl leading-relaxed mb-8">
        <?= $heroSection ? sanitize($heroSection['section_subtitle'] ?? $heroData['subtitle'] ?? '') : 'Stay informed with the latest updates from WRC. Browse our news articles, press releases, event calendars, photo galleries, and video reports from the field.' ?>
      </p>
      <div class="flex flex-wrap gap-3">
        <a href="#news" class="bg-brandRed hover:bg-brandRed-hover text-white text-sm font-semibold px-6 py-3 rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-brandRed/20">
          <i class="fa-solid fa-newspaper text-xs"></i> <?= sanitize($heroData['button1_text'] ?? 'Latest News') ?>
        </a>
        <a href="#press" class="bg-white/10 hover:bg-white/20 text-white text-sm font-semibold px-6 py-3 rounded-xl border border-white/20 transition-all flex items-center gap-2">
          <i class="fa-solid fa-bullhorn text-xs"></i> <?= sanitize($heroData['button2_text'] ?? 'Press Releases') ?>
        </a>
      </div>
    </div>
  </div>
  <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-slate-50 to-transparent"></div>
</section>


<!-- ============================================================ -->
<!-- LATEST NEWS GRID -->
<!-- ============================================================ -->
<section id="news" class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
      <div>
        <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Stay Updated</span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Latest <span class="text-brandRed">News</span></h2>
      </div>
      <a href="#" class="text-brandRed text-sm font-semibold hover:underline flex items-center gap-1 shrink-0">
        View All News <i class="fa-solid fa-arrow-right text-xs"></i>
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
      <?php foreach ($news as $item): ?>
      <?php
        $cat = $item['category'] ?? 'relief';
        $catColor = $categoryColors[$cat] ?? 'bg-brandRed';
        $catLabel = $categoryLabels[$cat] ?? ucfirst(str_replace('_', ' ', $cat));
        $image = !empty($item['hero_image']) ? $item['hero_image'] : 'assets/images/hero.jpg';
        if (strpos($image, 'assets/') !== 0 && strpos($image, 'http') !== 0) {
            $image = 'assets/images/' . $image;
        }
      ?>
      <article class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group border border-slate-100 hover:border-brandRed/10 program-card-hover">
        <div class="relative h-52 overflow-hidden">
          <img src="<?= $base ?><?= $image ?>" alt="<?= sanitize($item['title'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
          <span class="absolute top-3 left-3 <?= $catColor ?> text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full"><?= sanitize($catLabel) ?></span>
        </div>
        <div class="p-5">
          <div class="flex items-center gap-3 text-xs text-slate-400 mb-3">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-brandRed/60"></i> <?= formatDate($item['published_date'] ?? date('Y-m-d')) ?></span>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-user text-brandRed/60"></i> <?= sanitize($item['author'] ?? 'WRC Staff') ?></span>
          </div>
          <h3 class="text-lg font-bold text-ink-900 leading-snug mb-2 group-hover:text-brandRed transition-colors"><?= sanitize($item['title'] ?? '') ?></h3>
          <p class="text-sm text-slate-500 leading-relaxed mb-4"><?= sanitize($item['description'] ?? '') ?></p>
          <a href="<?= $base ?>news/<?= sanitize($item['slug'] ?? '#') ?>" class="text-brandRed text-sm font-semibold hover:underline flex items-center gap-1">Read More <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ============================================================ -->
<!-- PRESS RELEASES FEED -->
<!-- ============================================================ -->
<section id="press" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Official Statements</span>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Press <span class="text-brandRed">Releases</span></h2>
      <p class="text-slate-500 mt-3 max-w-2xl mx-auto">Official statements, reports, and announcements from the World Relief Committee.</p>
    </div>

    <div class="space-y-5 max-w-4xl mx-auto">
      <?php foreach ($pressReleases as $pr): ?>
      <?php
        $subType = $pr['sub_type'] ?? 'official_statement';
        $iconData = $pressIconMap[$subType] ?? $pressIconMap['official_statement'];
        $typeLabel = str_replace('_', ' ', ucfirst($subType));
      ?>
      <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 hover:border-brandRed/20 hover:shadow-md transition-all group program-card-hover">
        <div class="flex flex-col md:flex-row md:items-center gap-5">
          <div class="shrink-0 flex items-center justify-center w-16 h-16 rounded-xl <?= $iconData['bg'] ?> <?= $iconData['color'] ?>">
            <i class="fa-solid <?= $iconData['icon'] ?> text-xl"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 text-xs text-slate-400 mb-1.5">
              <span class="flex items-center gap-1"><i class="fa-solid fa-calendar-days"></i> <?= formatDate($pr['published_date'] ?? date('Y-m-d'), 'F d, Y') ?></span>
              <span class="text-slate-300">|</span>
              <span class="<?= $iconData['color'] ?> font-semibold"><?= sanitize($typeLabel) ?></span>
            </div>
            <h3 class="text-lg font-bold text-ink-900 group-hover:text-brandRed transition-colors mb-1.5"><?= sanitize($pr['title'] ?? '') ?></h3>
            <p class="text-sm text-slate-500 leading-relaxed"><?= sanitize($pr['description'] ?? '') ?></p>
          </div>
          <?php if (!empty($pr['pdf_url'])): ?>
          <a href="<?= $base ?><?= $pr['pdf_url'] ?>" class="shrink-0 inline-flex items-center gap-2 <?= $iconData['btnBg'] ?> text-white text-xs font-semibold px-5 py-2.5 rounded-xl <?= $iconData['btnHover'] ?> transition-all shadow-sm">
            <i class="fa-solid fa-file-pdf"></i> Download PDF
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ============================================================ -->
<!-- EVENTS CALENDAR (Hardcoded - no events table yet) -->
<!-- ============================================================ -->
<section id="events" class="py-20 bg-sand-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Mark Your Calendar</span>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Upcoming <span class="text-brandRed">Events</span></h2>
      <p class="text-slate-500 mt-3 max-w-2xl mx-auto">Join us at our upcoming events and be part of the change.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-7">

      <!-- Event Card 1 -->
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100 hover:border-brandRed/10 program-card-hover group">
        <div class="relative h-48 bg-gradient-to-br from-brandRed to-brandRed-hover overflow-hidden">
          <div class="absolute inset-0 flex items-center justify-center">
            <i class="fa-solid fa-truck-medical text-white/20 text-7xl"></i>
          </div>
          <div class="absolute inset-0 flex flex-col items-center justify-center text-white z-10">
            <span class="text-5xl font-bold font-display leading-none">15</span>
            <span class="text-sm font-semibold uppercase tracking-wider mt-1">September 2026</span>
          </div>
        </div>
        <div class="p-6">
          <div class="flex items-center gap-2 text-xs text-slate-400 mb-3">
            <i class="fa-solid fa-location-dot text-brandRed/60"></i>
            <span>Peshawar Sports Complex</span>
          </div>
          <h3 class="text-lg font-bold text-ink-900 mb-2 group-hover:text-brandRed transition-colors">Humanitarian Aid Drive</h3>
          <p class="text-sm text-slate-500 leading-relaxed mb-4">Join us for a day of community service as we pack and distribute emergency relief kits to displaced families. Volunteers of all ages welcome.</p>
          <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
            <i class="fa-solid fa-clock text-brandRed/60"></i>
            <span>9:00 AM - 4:00 PM (PKT)</span>
          </div>
          <a href="#" class="inline-flex items-center gap-2 bg-brandRed text-white text-xs font-semibold px-5 py-2.5 rounded-xl hover:bg-brandRed-hover transition-all shadow-sm">
            <i class="fa-solid fa-hand-pointer text-[10px]"></i> Register Now
          </a>
        </div>
      </div>

      <!-- Event Card 2 -->
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100 hover:border-brandRed/10 program-card-hover group">
        <div class="relative h-48 bg-gradient-to-br from-teal-500 to-teal-600 overflow-hidden">
          <div class="absolute inset-0 flex items-center justify-center">
            <i class="fa-solid fa-users text-white/20 text-7xl"></i>
          </div>
          <div class="absolute inset-0 flex flex-col items-center justify-center text-white z-10">
            <span class="text-5xl font-bold font-display leading-none">08</span>
            <span class="text-sm font-semibold uppercase tracking-wider mt-1">October 2026</span>
          </div>
        </div>
        <div class="p-6">
          <div class="flex items-center gap-2 text-xs text-slate-400 mb-3">
            <i class="fa-solid fa-location-dot text-teal-500/60"></i>
            <span>WRC Head Office, Peshawar</span>
          </div>
          <h3 class="text-lg font-bold text-ink-900 mb-2 group-hover:text-brandRed transition-colors">Volunteer Orientation Workshop</h3>
          <p class="text-sm text-slate-500 leading-relaxed mb-4">A comprehensive orientation for new and existing volunteers covering WRC's mission, field protocols, safety guidelines, and community engagement strategies.</p>
          <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
            <i class="fa-solid fa-clock text-teal-500/60"></i>
            <span>10:00 AM - 1:00 PM (PKT)</span>
          </div>
          <a href="#" class="inline-flex items-center gap-2 bg-teal-500 text-white text-xs font-semibold px-5 py-2.5 rounded-xl hover:bg-teal-600 transition-all shadow-sm">
            <i class="fa-solid fa-hand-pointer text-[10px]"></i> Register Now
          </a>
        </div>
      </div>

      <!-- Event Card 3 -->
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100 hover:border-brandRed/10 program-card-hover group">
        <div class="relative h-48 bg-gradient-to-br from-gold-400 to-gold-600 overflow-hidden">
          <div class="absolute inset-0 flex items-center justify-center">
            <i class="fa-solid fa-champagne-glasses text-white/20 text-7xl"></i>
          </div>
          <div class="absolute inset-0 flex flex-col items-center justify-center text-white z-10">
            <span class="text-5xl font-bold font-display leading-none">20</span>
            <span class="text-sm font-semibold uppercase tracking-wider mt-1">December 2026</span>
          </div>
        </div>
        <div class="p-6">
          <div class="flex items-center gap-2 text-xs text-slate-400 mb-3">
            <i class="fa-solid fa-location-dot text-gold-500/60"></i>
            <span>Marbella Wedding Hall, Peshawar</span>
          </div>
          <h3 class="text-lg font-bold text-ink-900 mb-2 group-hover:text-brandRed transition-colors">Annual Gala & Fundraiser</h3>
          <p class="text-sm text-slate-500 leading-relaxed mb-4">WRC's flagship fundraising event celebrating a year of impact. Enjoy dinner, live entertainment, and exclusive stories from the field while supporting our mission.</p>
          <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
            <i class="fa-solid fa-clock text-gold-500/60"></i>
            <span>6:30 PM - 10:00 PM (PKT)</span>
          </div>
          <a href="#" class="inline-flex items-center gap-2 bg-gold-500 text-white text-xs font-semibold px-5 py-2.5 rounded-xl hover:bg-gold-600 transition-all shadow-sm">
            <i class="fa-solid fa-hand-pointer text-[10px]"></i> Register Now
          </a>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ============================================================ -->
<!-- PHOTO GALLERY (Hardcoded - no photos table yet) -->
<!-- ============================================================ -->
<section id="photos" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
      <div>
        <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Visual Stories</span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Photo <span class="text-brandRed">Gallery</span></h2>
        <p class="text-slate-500 mt-3">Moments captured from our field operations and community events.</p>
      </div>
      <div class="flex flex-wrap gap-2 shrink-0">
        <button class="photo-filter-btn active bg-brandRed text-white text-xs font-semibold px-4 py-2 rounded-full transition-all" data-filter="all">All</button>
        <button class="photo-filter-btn bg-slate-100 text-slate-600 text-xs font-semibold px-4 py-2 rounded-full hover:bg-brandRed hover:text-white transition-all" data-filter="relief">Relief</button>
        <button class="photo-filter-btn bg-slate-100 text-slate-600 text-xs font-semibold px-4 py-2 rounded-full hover:bg-brandRed hover:text-white transition-all" data-filter="wash">WASH</button>
        <button class="photo-filter-btn bg-slate-100 text-slate-600 text-xs font-semibold px-4 py-2 rounded-full hover:bg-brandRed hover:text-white transition-all" data-filter="health">Health</button>
        <button class="photo-filter-btn bg-slate-100 text-slate-600 text-xs font-semibold px-4 py-2 rounded-full hover:bg-brandRed hover:text-white transition-all" data-filter="education">Education</button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="photo-grid">
      <div class="gallery-item aspect-square bg-slate-200" data-src="<?= $base ?>assets/images/hero.jpg" data-category="relief">
        <img src="<?= $base ?>assets/images/hero.jpg" alt="Flood relief operations" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="gallery-item aspect-square bg-slate-200" data-src="<?= $base ?>assets/images/water.jpg" data-category="wash">
        <img src="<?= $base ?>assets/images/water.jpg" alt="Clean water distribution" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="gallery-item aspect-square bg-slate-200" data-src="<?= $base ?>assets/images/food.jpg" data-category="relief">
        <img src="<?= $base ?>assets/images/food.jpg" alt="Food pack delivery" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="gallery-item aspect-square bg-slate-200" data-src="<?= $base ?>assets/images/hero-slider-2.jpg" data-category="education">
        <img src="<?= $base ?>assets/images/hero-slider-2.jpg" alt="School distribution drive" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="gallery-item aspect-square bg-slate-200" data-src="<?= $base ?>assets/images/hero-slider-3.jpg" data-category="health">
        <img src="<?= $base ?>assets/images/hero-slider-3.jpg" alt="Medical camp services" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="gallery-item aspect-square bg-slate-200" data-src="<?= $base ?>assets/images/hero.jpg" data-category="wash">
        <img src="<?= $base ?>assets/images/hero.jpg" alt="Water filtration plant" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="gallery-item aspect-square bg-slate-200" data-src="<?= $base ?>assets/images/food.jpg" data-category="relief">
        <img src="<?= $base ?>assets/images/food.jpg" alt="Community relief gathering" class="w-full h-full object-cover" loading="lazy">
      </div>
      <div class="gallery-item aspect-square bg-slate-200" data-src="<?= $base ?>assets/images/water.jpg" data-category="education">
        <img src="<?= $base ?>assets/images/water.jpg" alt="Education support program" class="w-full h-full object-cover" loading="lazy">
      </div>
    </div>
  </div>
</section>


<!-- ============================================================ -->
<!-- VIDEO GALLERY (Hardcoded - no videos table yet) -->
<!-- ============================================================ -->
<section id="videos" class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Watch & Learn</span>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Video <span class="text-brandRed">Gallery</span></h2>
      <p class="text-slate-500 mt-3 max-w-2xl mx-auto">Watch stories from the field and see the impact of your support in action.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-7">

      <!-- Video Card 1 -->
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100 program-card-hover group">
        <div class="relative aspect-video bg-slate-200 overflow-hidden">
          <img src="<?= $base ?>assets/images/hero.jpg" alt="WRC documentary" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
          <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors flex items-center justify-center">
            <button class="w-16 h-16 rounded-full bg-brandRed/90 hover:bg-brandRed text-white flex items-center justify-center shadow-lg shadow-brandRed/30 transition-all hover:scale-110" aria-label="Play video">
              <i class="fa-solid fa-play text-xl ml-1"></i>
            </button>
          </div>
          <div class="absolute bottom-3 left-3 bg-black/60 text-white text-[10px] font-semibold px-2.5 py-1 rounded-md">
            <i class="fa-solid fa-clock mr-1"></i> 3:42
          </div>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-bold text-ink-900 mb-1.5 group-hover:text-brandRed transition-colors">WRC in Action: Emergency Flood Response 2026</h3>
          <p class="text-sm text-slate-500 leading-relaxed">A documentary look at WRC's rapid-response teams deploying across Sindh during the 2026 monsoon floods.</p>
        </div>
      </div>

      <!-- Video Card 2 -->
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100 program-card-hover group">
        <div class="relative aspect-video bg-slate-200 overflow-hidden">
          <img src="<?= $base ?>assets/images/water.jpg" alt="Water project video" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
          <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors flex items-center justify-center">
            <button class="w-16 h-16 rounded-full bg-brandRed/90 hover:bg-brandRed text-white flex items-center justify-center shadow-lg shadow-brandRed/30 transition-all hover:scale-110" aria-label="Play video">
              <i class="fa-solid fa-play text-xl ml-1"></i>
            </button>
          </div>
          <div class="absolute bottom-3 left-3 bg-black/60 text-white text-[10px] font-semibold px-2.5 py-1 rounded-md">
            <i class="fa-solid fa-clock mr-1"></i> 5:18
          </div>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-bold text-ink-900 mb-1.5 group-hover:text-brandRed transition-colors">Clean Water, Brighter Futures: WASH Project Highlights</h3>
          <p class="text-sm text-slate-500 leading-relaxed">See how solar-powered water systems are transforming remote communities in Khyber Pakhtunkhwa.</p>
        </div>
      </div>

      <!-- Video Card 3 -->
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100 program-card-hover group">
        <div class="relative aspect-video bg-slate-200 overflow-hidden">
          <img src="<?= $base ?>assets/images/hero-slider-2.jpg" alt="Education initiative video" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
          <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors flex items-center justify-center">
            <button class="w-16 h-16 rounded-full bg-brandRed/90 hover:bg-brandRed text-white flex items-center justify-center shadow-lg shadow-brandRed/30 transition-all hover:scale-110" aria-label="Play video">
              <i class="fa-solid fa-play text-xl ml-1"></i>
            </button>
          </div>
          <div class="absolute bottom-3 left-3 bg-black/60 text-white text-[10px] font-semibold px-2.5 py-1 rounded-md">
            <i class="fa-solid fa-clock mr-1"></i> 4:05
          </div>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-bold text-ink-900 mb-1.5 group-hover:text-brandRed transition-colors">Back to School: Education for Every Child in Pakistan</h3>
          <p class="text-sm text-slate-500 leading-relaxed">Follow our team as they deliver school kits and set up learning centers in remote Balochistan villages.</p>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ============================================================ -->
<!-- BLOGS SECTION -->
<!-- ============================================================ -->
<section id="blogs" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
      <div>
        <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Insights & Perspectives</span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">From Our <span class="text-brandRed">Blog</span></h2>
      </div>
      <a href="#" class="text-brandRed text-sm font-semibold hover:underline flex items-center gap-1 shrink-0">
        View All Posts <i class="fa-solid fa-arrow-right text-xs"></i>
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-7">
      <?php foreach ($blogs as $blog): ?>
      <?php
        $blogCat = $blog['sub_type'] ?? 'opinion';
        $blogCatColors = [
            'opinion'     => 'bg-brandRed',
            'perspective' => 'bg-teal-500',
            'story'       => 'bg-purple-500',
            'analysis'    => 'bg-blue-500',
        ];
        $blogCatLabels = [
            'opinion'     => 'Opinion',
            'perspective' => 'Perspective',
            'story'       => 'Story',
            'analysis'    => 'Analysis',
        ];
        $blogColor = $blogCatColors[$blogCat] ?? 'bg-brandRed';
        $blogLabel = $blogCatLabels[$blogCat] ?? ucfirst($blogCat);
        $blogImage = !empty($blog['hero_image']) ? $blog['hero_image'] : 'assets/images/food.jpg';
        if (strpos($blogImage, 'assets/') !== 0 && strpos($blogImage, 'http') !== 0) {
            $blogImage = 'assets/images/' . $blogImage;
        }
      ?>
      <article class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 hover:border-brandRed/20 hover:shadow-xl transition-all group program-card-hover">
        <div class="relative h-52 overflow-hidden">
          <img src="<?= $base ?><?= $blogImage ?>" alt="<?= sanitize($blog['title'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
          <span class="absolute top-3 left-3 <?= $blogColor ?> text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full"><?= sanitize($blogLabel) ?></span>
        </div>
        <div class="p-6">
          <div class="flex items-center gap-3 text-xs text-slate-400 mb-3">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-brandRed/60"></i> <?= formatDate($blog['published_date'] ?? date('Y-m-d')) ?></span>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-pen text-brandRed/60"></i> <?= sanitize($blog['author'] ?? 'WRC Staff') ?></span>
          </div>
          <h3 class="text-lg font-bold text-ink-900 leading-snug mb-2 group-hover:text-brandRed transition-colors"><?= sanitize($blog['title'] ?? '') ?></h3>
          <p class="text-sm text-slate-500 leading-relaxed mb-4"><?= sanitize($blog['description'] ?? '') ?></p>
          <a href="<?= $base ?>news/<?= sanitize($blog['slug'] ?? '#') ?>" class="text-brandRed text-sm font-semibold hover:underline flex items-center gap-1">Read Post <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
