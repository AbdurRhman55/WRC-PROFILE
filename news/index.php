<?php
/**
 * WRC News & Media Center (Dynamic)
 * Sections: Hero, Latest News, Photo Gallery, Video Gallery
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();

// Fetch hero slides from dedicated table
$heroSlides = getRows('hero_sliders', ['page' => 'news', 'is_active' => 1], 'sort_order ASC');
if (empty($heroSlides)) {
    $heroSlides = [['title' => 'News & Media Center', 'subtitle' => 'Stay updated with the latest stories, reports, and media from our field operations.', 'image_path' => 'assets/images/hero/hero-slider-1.jpg', 'badge' => 'News & Media', 'cta_text' => 'Latest News', 'cta_link' => '#news']];
}

// Fetch all active news articles
$news = getRows('news_articles', 'is_published = 1 ORDER BY published_at DESC');

// Fetch photos from DB
$photos = getRows('photo_gallery', ['is_active' => 1], 'sort_order ASC');

// Normalise every photo path once, and skip rows whose file is missing so the
// gallery can never render a broken tile.
$galleryItems = [];
foreach ($photos as $photo) {
    $photoPath = trim((string) ($photo['image_path'] ?? ''));
    if ($photoPath === '') {
        continue;
    }
    if (strpos($photoPath, 'assets/') !== 0 && strpos($photoPath, 'http') !== 0 && strpos($photoPath, 'uploads/') !== 0) {
        $photoPath = 'uploads/photos/' . $photoPath;
    }
    if (strpos($photoPath, 'http') !== 0 && !is_file(dirname(__DIR__) . '/' . $photoPath)) {
        continue;
    }
    $photo['image_path'] = $photoPath;
    $galleryItems[] = $photo;
}

// Group the gallery into the sections each photo was tagged with at upload time.
$photoSections = [];
foreach ($galleryItems as $photo) {
    $section = strtolower(trim((string) ($photo['category'] ?? '')));
    if ($section === '') {
        $section = 'general';
    }
    $photo['section'] = $section;
    $photoSections[$section][] = $photo;
}
ksort($photoSections, SORT_NATURAL | SORT_FLAG_CASE);

// Sections actually shown in the gallery drive the filter buttons.
$photoCategories = array_keys($photoSections);

// Display labels + icons per section (unknown sections fall back to ucfirst).
$photoSectionLabels = [
    'community'    => 'Community',
    'education'    => 'Education',
    'general'      => 'General',
    'health'       => 'Health',
    'meetings'     => 'Meetings',
    'partnerships' => 'Partnerships',
    'relief'       => 'Relief',
    'wash'         => 'WASH',
];
$photoSectionIcons = [
    'community'    => 'fa-people-roof',
    'education'    => 'fa-graduation-cap',
    'general'      => 'fa-images',
    'health'       => 'fa-heart-pulse',
    'meetings'     => 'fa-people-group',
    'partnerships' => 'fa-handshake',
    'relief'       => 'fa-hand-holding-heart',
    'wash'         => 'fa-droplet',
];
$photoSectionLabel = static function (string $section) use ($photoSectionLabels): string {
    return $photoSectionLabels[$section] ?? ucfirst($section);
};
$photoSectionIcon = static function (string $section) use ($photoSectionIcons): string {
    return $photoSectionIcons[$section] ?? 'fa-layer-group';
};

// Fetch videos from DB
$videos = getRows('video_gallery', ['is_active' => 1], 'sort_order ASC');

// Category color map for news
$categoryColors = [
    'press_release' => 'bg-brandRed',
    'report'        => 'bg-teal-500',
    'story'         => 'bg-purple-500',
    'blog'          => 'bg-blue-500',
    'event'         => 'bg-amber-500',
];
$categoryLabels = [
    'press_release' => 'Press Release',
    'report'        => 'Report',
    'story'         => 'Story',
    'blog'          => 'Blog',
    'event'         => 'Event',
];

$pageTitle = 'News & Media Center | WRC';
$metaDescription = 'Stay informed with the latest updates from WRC. Browse news articles, photo galleries, and video reports from the field.';

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- HERO SLIDER - FULL SCREEN -->
<section class="relative w-full h-screen bg-ink-900 text-white overflow-hidden group" id="news-hero">

    <!-- Slider Wrapper -->
    <div id="news-hero-slider" class="absolute inset-0 z-0 w-full h-full transition-transform duration-1000 ease-in-out flex">
        <?php foreach ($heroSlides as $i => $slide): ?>
        <?php
            $slideImage = !empty($slide['image_path']) ? $slide['image_path'] : 'assets/images/hero/hero-slider-1.jpg';
            if (strpos($slideImage, 'assets/') !== 0 && strpos($slideImage, 'http') !== 0 && strpos($slideImage, 'uploads/') !== 0) {
                $slideImage = 'assets/images/' . $slideImage;
            }
        ?>
        <div class="w-full h-full flex-shrink-0 relative">
            <img src="<?= $base ?><?= $slideImage ?>" alt="<?= sanitize($slide['title'] ?? 'Slide') ?>" class="absolute inset-0 w-full h-full object-cover" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Bottom Gradient for text readability only -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent z-[1] pointer-events-none"></div>

    <!-- Slider Navigation (Arrows) -->
    <button id="news-slider-prev" class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center rounded-full bg-white/15 hover:bg-brandRed/90 backdrop-blur-md border border-white/20 text-white opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-lg" aria-label="Previous slide">
        <i class="fa-solid fa-chevron-left text-lg"></i>
    </button>
    <button id="news-slider-next" class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center rounded-full bg-white/15 hover:bg-brandRed/90 backdrop-blur-md border border-white/20 text-white opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-lg" aria-label="Next slide">
        <i class="fa-solid fa-chevron-right text-lg"></i>
    </button>

    <!-- Slider Pagination (Dots) -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex items-center gap-3" id="news-slider-dots">
        <?php foreach ($heroSlides as $i => $slide): ?>
        <button class="<?= $i === 0 ? 'w-4 h-3 rounded-full bg-brandRed shadow-md' : 'w-3 h-3 rounded-full bg-white/40 hover:bg-white/70' ?> transition-all duration-300" data-index="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>

    <!-- Slide Content - Centered vertically -->
    <div class="absolute inset-0 z-10 flex items-center pointer-events-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div id="news-hero-content" class="max-w-3xl pointer-events-auto">
                <?php $firstSlide = $heroSlides[0]; ?>
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest bg-brandRed/90 text-white mb-4 backdrop-blur-sm">
                    <i class="fa-solid fa-newspaper"></i> <?= sanitize($firstSlide['badge'] ?? 'News & Media') ?>
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-display font-bold leading-[1.1] mb-4 drop-shadow-lg">
                    <?= sanitize($firstSlide['title'] ?? 'News & Media Center') ?>
                </h1>
                <p class="text-lg sm:text-xl text-white/80 leading-relaxed mb-8 max-w-xl drop-shadow-sm">
                    <?= sanitize($firstSlide['subtitle'] ?? 'Stay updated with the latest stories from the field.') ?>
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="<?= $base ?><?= $firstSlide['cta_link'] ?? '#news' ?>" class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm px-8 py-4 rounded-xl shadow-lg shadow-brandRed/30 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                        <?= sanitize($firstSlide['cta_text'] ?? 'Latest News') ?> <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                    <a href="#photos" class="bg-white/15 hover:bg-white/25 backdrop-blur-sm text-white font-bold text-sm px-8 py-4 rounded-xl border border-white/25 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                        Photo Gallery <i class="fa-solid fa-images text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LATEST NEWS -->
<section id="news" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
      <div>
        <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Stay Updated</span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Latest <span class="text-brandRed">News</span></h2>
        <p class="text-slate-500 mt-3">Recent stories, reports, and updates from our operations.</p>
      </div>
    </div>

    <?php if (empty($news)): ?>
    <div class="text-center py-16 bg-slate-50 rounded-2xl">
      <i class="fa-solid fa-newspaper text-4xl text-slate-300 mb-4 block"></i>
      <h3 class="text-lg font-bold text-slate-700 mb-2">No news articles yet</h3>
      <p class="text-sm text-slate-400">Check back soon for the latest updates.</p>
    </div>
    <?php else: ?>
    <?php
      // The same article the load popup promotes — flag it in the list so a
      // reader who dismissed the popup can still find it again.
      $featuredSlug = null;
      if (isset($pdo)) {
        try {
          $featuredStmt = $pdo->query(
            'SELECT slug FROM news_articles
              WHERE is_published = 1 AND slug IS NOT NULL AND slug <> \'\'
                AND featured_image IS NOT NULL AND featured_image <> \'\'
              ORDER BY published_at DESC, id DESC LIMIT 1'
          );
          $featuredSlug = $featuredStmt ? $featuredStmt->fetchColumn() : null;
        } catch (Throwable $e) {
          $featuredSlug = null;
        }
      }
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
      <?php foreach ($news as $item): ?>
      <?php
        $cat = $item['category'] ?? 'story';
        $catColor = $categoryColors[$cat] ?? 'bg-slate-500';
        $catLabel = $categoryLabels[$cat] ?? ucfirst(str_replace('_', ' ', $cat));
        $newsImage = !empty($item['featured_image']) ? $item['featured_image'] : 'assets/images/hero/hero-slider-1.jpg';
        if (strpos($newsImage, 'assets/') !== 0 && strpos($newsImage, 'http') !== 0) {
            $newsImage = 'assets/images/' . $newsImage;
        }
      ?>
      <article class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 hover:border-brandRed/20 hover:shadow-xl transition-all group program-card-hover">
        <div class="relative h-52 overflow-hidden">
          <img src="<?= $base ?><?= $newsImage ?>" alt="<?= sanitize($item['title'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
          <span class="absolute top-3 left-3 <?= $catColor ?> text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full"><?= sanitize($catLabel) ?></span>
          <?php if (!empty($featuredSlug) && ($item['slug'] ?? '') === $featuredSlug): ?>
          <span class="absolute top-3 right-3 bg-ink-900 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full flex items-center gap-1.5 shadow-lg">
            <i class="fa-solid fa-star text-[9px] text-amber-400"></i> Featured
          </span>
          <?php endif; ?>
        </div>
        <div class="p-6">
          <div class="flex items-center gap-3 text-xs text-slate-400 mb-3">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-brandRed/60"></i> <?= formatDate($item['published_at'] ?? date('Y-m-d')) ?></span>
            <?php if (!empty($item['author'])): ?>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-pen text-brandRed/60"></i> <?= sanitize($item['author']) ?></span>
            <?php endif; ?>
          </div>
          <h3 class="text-lg font-bold text-ink-900 leading-snug mb-2 group-hover:text-brandRed transition-colors line-clamp-2"><?= sanitize($item['title'] ?? '') ?></h3>
          <p class="text-sm text-slate-500 leading-relaxed mb-4 line-clamp-3"><?= sanitize($item['excerpt'] ?? '') ?></p>
          <a href="<?= $base ?>news/detail.php?slug=<?= sanitize($item['slug'] ?? '#') ?>" class="text-brandRed text-sm font-semibold hover:underline flex items-center gap-1">Read More <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>


<!-- PHOTO GALLERY -->
<section id="photos" class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
      <div>
        <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Visual Stories</span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Photo <span class="text-brandRed">Gallery</span></h2>
        <p class="text-slate-500 mt-3">Moments captured from our field operations and community events.</p>
      </div>
      <?php if (!empty($photoCategories)): ?>
      <div class="flex flex-wrap gap-2 shrink-0">
        <button class="photo-filter-btn active bg-brandRed text-white text-xs font-semibold px-4 py-2 rounded-full transition-all" data-filter="all">All</button>
        <?php foreach ($photoCategories as $cat): ?>
        <button class="photo-filter-btn bg-slate-200 text-slate-600 text-xs font-semibold px-4 py-2 rounded-full hover:bg-brandRed hover:text-white transition-all" data-filter="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($photoSectionLabel($cat)) ?></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if (empty($photoSections)): ?>
    <div class="text-center py-16 bg-white rounded-2xl">
      <i class="fa-solid fa-images text-4xl text-slate-300 mb-4 block"></i>
      <h3 class="text-lg font-bold text-slate-700 mb-2">No photos yet</h3>
      <p class="text-sm text-slate-400">Photo gallery will be populated soon.</p>
    </div>
    <?php else: ?>
    <div id="photo-grid">
      <?php foreach ($photoSections as $section => $sectionPhotos): ?>
      <?php
        $sectionLabel = $photoSectionLabel($section);
        $sectionIcon  = $photoSectionIcon($section);
        $sectionCount = count($sectionPhotos);
      ?>
      <!-- SECTION: <?= sanitize($sectionLabel) ?> -->
      <div class="photo-section mb-10" data-section="<?= htmlspecialchars($section) ?>">
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-200">
          <span class="w-10 h-10 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center shrink-0">
            <i class="fa-solid <?= $sectionIcon ?>"></i>
          </span>
          <div class="min-w-0">
            <h3 class="text-base sm:text-lg font-display font-bold text-ink-900 leading-tight"><?= sanitize($sectionLabel) ?></h3>
            <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider mt-0.5"><?= $sectionCount ?> <?= $sectionCount === 1 ? 'Photo' : 'Photos' ?> &middot; Section</p>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <?php foreach ($sectionPhotos as $photo): ?>
          <div class="gallery-card" data-category="<?= htmlspecialchars($section) ?>">
            <?php $photoYear = photoYear((string) ($photo['image_path'] ?? '')); ?>
            <div class="gallery-item aspect-square bg-slate-200 rounded-xl overflow-hidden cursor-pointer group" data-src="<?= $base ?><?= $photo['image_path'] ?>" data-caption="<?= htmlspecialchars($photo['caption'] ?? $photo['alt_text'] ?? '') ?>">
              <img src="<?= $base ?><?= $photo['image_path'] ?>" alt="<?= sanitize($photo['alt_text'] ?? $photo['caption'] ?? $sectionLabel) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
              <span class="absolute top-2 left-2 z-10 bg-white/95 backdrop-blur-sm text-ink-900 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md shadow-sm flex items-center gap-1.5">
                <i class="fa-solid <?= $sectionIcon ?> text-brandRed text-[9px]"></i><?= sanitize($sectionLabel) ?>
              </span>
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                <i class="fa-solid fa-expand text-white text-xl"></i>
              </div>
            </div>
            <div class="mt-2.5 flex items-start gap-2">
              <span class="w-1.5 h-1.5 rounded-full bg-brandRed mt-1.5 shrink-0"></span>
              <div class="min-w-0">
                <p class="text-xs font-semibold text-ink-900 truncate"><?= sanitize($sectionLabel) ?><?= $photoYear !== null ? ' &middot; <span class="text-brandRed font-bold">' . $photoYear . '</span>' : '' ?></p>
                <?php if (!empty($photo['caption'])): ?>
                <p class="text-[11px] text-slate-400 truncate mt-0.5"><?= sanitize($photo['caption']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>


<!-- VIDEO GALLERY -->
<section id="videos" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Watch & Learn</span>
      <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Video <span class="text-brandRed">Gallery</span></h2>
      <p class="text-slate-500 mt-3 max-w-2xl mx-auto">Watch stories from the field and see the impact of your support in action.</p>
    </div>

    <?php if (empty($videos)): ?>
    <div class="text-center py-16 bg-slate-50 rounded-2xl">
      <i class="fa-solid fa-video text-4xl text-slate-300 mb-4 block"></i>
      <h3 class="text-lg font-bold text-slate-700 mb-2">No videos yet</h3>
      <p class="text-sm text-slate-400">Video gallery will be populated soon.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
      <?php foreach ($videos as $video): ?>
      <?php
        $thumbPath = !empty($video['thumbnail_path']) ? $video['thumbnail_path'] : 'assets/images/hero/hero-slider-1.jpg';
        if (strpos($thumbPath, 'assets/') !== 0 && strpos($thumbPath, 'http') !== 0 && strpos($thumbPath, 'uploads/') !== 0) {
            $thumbPath = 'uploads/videos/' . $thumbPath;
        }
        $videoUrl = $video['video_url'] ?? '';
        $isYouTube = preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $ytMatch);
        $embedUrl = $isYouTube ? "https://www.youtube.com/embed/{$ytMatch[1]}" : $videoUrl;
      ?>
      <div class="bg-slate-50 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100 program-card-hover group">
        <div class="relative aspect-video bg-slate-200 overflow-hidden video-card" data-video-url="<?= htmlspecialchars($embedUrl) ?>">
          <img src="<?= $base ?><?= $thumbPath ?>" alt="<?= sanitize($video['title'] ?? 'Video') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
          <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors flex items-center justify-center">
            <button class="w-16 h-16 rounded-full bg-brandRed/90 hover:bg-brandRed text-white flex items-center justify-center shadow-lg shadow-brandRed/30 transition-all hover:scale-110 video-play-btn" aria-label="Play video">
              <i class="fa-solid fa-play text-xl ml-1"></i>
            </button>
          </div>
          <?php if (!empty($video['duration'])): ?>
          <div class="absolute bottom-3 left-3 bg-black/60 text-white text-[10px] font-semibold px-2.5 py-1 rounded-md">
            <i class="fa-solid fa-clock mr-1"></i> <?= sanitize($video['duration']) ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-bold text-ink-900 mb-1.5 group-hover:text-brandRed transition-colors line-clamp-2"><?= sanitize($video['title'] ?? '') ?></h3>
          <?php if (!empty($video['description'])): ?>
          <p class="text-sm text-slate-500 leading-relaxed line-clamp-2"><?= sanitize($video['description']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

</main>

<!-- VIDEO MODAL -->
<div id="video-modal" class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm hidden items-center justify-center p-4" role="dialog" aria-modal="true">
  <button id="video-modal-close" class="absolute top-6 right-6 text-white/50 hover:text-white text-3xl z-10 transition-colors w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Close">
    <i class="fa-solid fa-xmark"></i>
  </button>
  <div class="w-full max-w-4xl">
    <div class="relative aspect-video bg-black rounded-xl overflow-hidden">
      <iframe id="video-iframe" src="" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
  </div>
</div>

<!-- PHOTO LIGHTBOX -->
<div id="lightbox-modal" class="fixed inset-0 z-[100] bg-black/95 backdrop-blur-sm hidden items-center justify-center p-4" role="dialog" aria-modal="true">
  <button id="lightbox-close" class="absolute top-6 right-6 text-white/50 hover:text-white text-3xl z-10 transition-colors w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Close lightbox">
    <i class="fa-solid fa-xmark"></i>
  </button>
  <button id="lightbox-prev" class="absolute left-4 lg:left-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white text-2xl z-10 transition-all w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Previous image">
    <i class="fa-solid fa-chevron-left"></i>
  </button>
  <button id="lightbox-next" class="absolute right-4 lg:right-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white text-2xl z-10 transition-all w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Next image">
    <i class="fa-solid fa-chevron-right"></i>
  </button>
  <div class="max-w-5xl max-h-[85vh] flex flex-col items-center gap-4">
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[75vh] object-contain rounded-xl">
    <p id="lightbox-caption" class="text-white/70 text-sm text-center"></p>
    <span id="lightbox-counter" class="text-white/40 text-xs font-mono"></span>
  </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
(function(){
  // === NEWS HERO SLIDER ===
  const slider = document.getElementById('news-hero-slider');
  const prevBtn = document.getElementById('news-slider-prev');
  const nextBtn = document.getElementById('news-slider-next');
  const dots = document.querySelectorAll('#news-slider-dots button');
  const slides = slider ? slider.querySelectorAll(':scope > div') : [];
  const slideContents = <?= json_encode(array_map(function($s) {
      return [
          'badge' => $s['badge'] ?? 'News & Media',
          'title' => $s['title'] ?? '',
          'subtitle' => $s['subtitle'] ?? '',
          'cta_text' => $s['cta_text'] ?? 'Latest News',
          'cta_link' => $s['cta_link'] ?? '#news',
      ];
  }, $heroSlides)) ?>;
  let currentSlide = 0;
  const slideCount = slides.length;
  let slideInterval;
  const contentEl = document.getElementById('news-hero-content');

  function renderContent(index) {
      if (!contentEl || !slideContents[index]) return;
      const s = slideContents[index];
      contentEl.style.opacity = '0';
      contentEl.style.transform = 'translateY(15px)';
      setTimeout(() => {
          contentEl.innerHTML = `
              <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest bg-brandRed/90 text-white mb-4 backdrop-blur-sm">
                  <i class="fa-solid fa-newspaper"></i> ${s.badge}
              </span>
              <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-display font-bold leading-[1.1] mb-4 drop-shadow-lg">
                  ${s.title}
              </h1>
              <p class="text-lg sm:text-xl text-white/80 leading-relaxed mb-8 max-w-xl drop-shadow-sm">
                  ${s.subtitle}
              </p>
              <div class="flex flex-wrap gap-4">
                  <a href="<?= $base ?>${s.cta_link}" class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm px-8 py-4 rounded-xl shadow-lg shadow-brandRed/30 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                      ${s.cta_text} <i class="fa-solid fa-arrow-right text-xs"></i>
                  </a>
                  <a href="#photos" class="bg-white/15 hover:bg-white/25 backdrop-blur-sm text-white font-bold text-sm px-8 py-4 rounded-xl border border-white/25 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                      Photo Gallery <i class="fa-solid fa-images text-xs"></i>
                  </a>
              </div>
          `;
          contentEl.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
          contentEl.style.opacity = '1';
          contentEl.style.transform = 'translateY(0)';
      }, 200);
  }

  function updateSlider() {
      if (slider) {
          slider.style.transform = `translateX(-${currentSlide * 100}%)`;
          dots.forEach((dot, index) => {
              dot.className = index === currentSlide
                  ? 'w-4 h-3 rounded-full bg-brandRed shadow-md transition-all duration-300'
                  : 'w-3 h-3 rounded-full bg-white/40 hover:bg-white/70 transition-all duration-300';
          });
          renderContent(currentSlide);
      }
  }

  function nextSlide() { currentSlide = (currentSlide + 1) % slideCount; updateSlider(); }
  function prevSlide() { currentSlide = (currentSlide - 1 + slideCount) % slideCount; updateSlider(); }
  function startSlider() { slideInterval = setInterval(nextSlide, 5000); }
  function resetSlider() { clearInterval(slideInterval); startSlider(); }

  if (nextBtn && prevBtn && dots.length > 0) {
      nextBtn.addEventListener('click', () => { nextSlide(); resetSlider(); });
      prevBtn.addEventListener('click', () => { prevSlide(); resetSlider(); });
      dots.forEach(dot => {
          dot.addEventListener('click', function() {
              currentSlide = parseInt(this.dataset.index);
              updateSlider();
              resetSlider();
          });
      });
      startSlider();
  }

  // Touch support for slider
  let touchStartX = 0;
  const heroEl = document.getElementById('news-hero');
  if (heroEl) {
      heroEl.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
      heroEl.addEventListener('touchend', e => {
          const diff = touchStartX - e.changedTouches[0].clientX;
          if (Math.abs(diff) > 50) {
              diff > 0 ? nextSlide() : prevSlide();
              resetSlider();
          }
      }, { passive: true });
  }

  // === PHOTO GALLERY FILTER ===
  var photoFilterBtns = document.querySelectorAll('.photo-filter-btn');
  var photoCards = document.querySelectorAll('#photo-grid .gallery-card');

  photoFilterBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      photoFilterBtns.forEach(function(b) { b.classList.remove('active', 'bg-brandRed', 'text-white'); b.classList.add('bg-slate-200', 'text-slate-600'); });
      this.classList.add('active', 'bg-brandRed', 'text-white');
      this.classList.remove('bg-slate-200', 'text-slate-600');
      
      var filter = this.getAttribute('data-filter');
      photoCards.forEach(function(card) {
        if (filter === 'all' || card.getAttribute('data-category') === filter) {
          card.style.display = '';
          card.style.opacity = '0';
          card.style.transform = 'scale(0.9)';
          setTimeout(function() {
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
          }, 50);
        } else {
          card.style.display = 'none';
        }
      });

      // Hide any section block that no longer has a visible photo.
      document.querySelectorAll('#photo-grid .photo-section').forEach(function(section) {
        var shown = section.querySelector('.gallery-card:not([style*="display: none"])');
        section.style.display = shown ? '' : 'none';
      });
    });
  });

  // === PHOTO LIGHTBOX ===
  var lightboxModal = document.getElementById('lightbox-modal');
  var lightboxImg = document.getElementById('lightbox-img');
  var lightboxCaption = document.getElementById('lightbox-caption');
  var lightboxCounter = document.getElementById('lightbox-counter');
  var lightboxItems = document.querySelectorAll('#photo-grid .gallery-item');
  var lightboxCurrent = 0;
  var visiblePhotos = Array.from(lightboxItems);

  function openLightbox(index) {
    lightboxCurrent = index;
    var item = visiblePhotos[lightboxCurrent];
    lightboxImg.src = item.dataset.src || '';
    lightboxCaption.textContent = item.dataset.caption || '';
    lightboxCounter.textContent = (lightboxCurrent + 1) + ' / ' + visiblePhotos.length;
    lightboxModal.classList.remove('hidden');
    lightboxModal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    lightboxModal.classList.add('hidden');
    lightboxModal.classList.remove('flex');
    document.body.style.overflow = '';
  }

  lightboxItems.forEach(function(item, i) {
    item.addEventListener('click', function() {
      visiblePhotos = Array.from(document.querySelectorAll('#photo-grid .gallery-card:not([style*="display: none"]) .gallery-item'));
      var visIndex = visiblePhotos.indexOf(item);
      openLightbox(visIndex >= 0 ? visIndex : 0);
    });
  });

  document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
  document.getElementById('lightbox-prev').addEventListener('click', function() { lightboxCurrent = (lightboxCurrent - 1 + visiblePhotos.length) % visiblePhotos.length; openLightbox(lightboxCurrent); });
  document.getElementById('lightbox-next').addEventListener('click', function() { lightboxCurrent = (lightboxCurrent + 1) % visiblePhotos.length; openLightbox(lightboxCurrent); });
  lightboxModal.addEventListener('click', function(e) { if (e.target === lightboxModal) closeLightbox(); });
  document.addEventListener('keydown', function(e) {
    if (lightboxModal.classList.contains('hidden')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') { lightboxCurrent = (lightboxCurrent - 1 + visiblePhotos.length) % visiblePhotos.length; openLightbox(lightboxCurrent); }
    if (e.key === 'ArrowRight') { lightboxCurrent = (lightboxCurrent + 1) % visiblePhotos.length; openLightbox(lightboxCurrent); }
  });

  // === VIDEO MODAL ===
  var videoModal = document.getElementById('video-modal');
  var videoIframe = document.getElementById('video-iframe');

  document.querySelectorAll('.video-play-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var card = this.closest('.video-card');
      var url = card.getAttribute('data-video-url');
      if (url) {
        videoIframe.src = url;
        videoModal.classList.remove('hidden');
        videoModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  document.getElementById('video-modal-close').addEventListener('click', function() {
    videoIframe.src = '';
    videoModal.classList.add('hidden');
    videoModal.classList.remove('flex');
    document.body.style.overflow = '';
  });

  videoModal.addEventListener('click', function(e) {
    if (e.target === videoModal) {
      videoIframe.src = '';
      videoModal.classList.add('hidden');
      videoModal.classList.remove('flex');
      document.body.style.overflow = '';
    }
  });

  document.addEventListener('keydown', function(e) {
    if (!videoModal.classList.contains('hidden') && e.key === 'Escape') {
      videoIframe.src = '';
      videoModal.classList.add('hidden');
      videoModal.classList.remove('flex');
      document.body.style.overflow = '';
    }
  });
})();
</script>
