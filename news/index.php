<?php
/**
 * WRC News & Media Center (Dynamic)
 * Sections: Hero, Latest News, Blog, Partner Photo Galleries, Video Gallery
 *
 * Partner-driven project galleries are the single photo experience (see
 * news/gallery.php). The legacy photo_gallery archive was removed so the
 * same project photographs are not listed twice on this page.
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();

// Fetch hero slides from dedicated table
$heroSlides = getRows('hero_sliders', ['page' => 'news', 'is_active' => 1], 'sort_order ASC');
if (empty($heroSlides)) {
    $heroSlides = [['title' => 'News & Media Center', 'subtitle' => 'Stay updated with the latest stories, reports, and media from our field operations.', 'image_path' => 'assets/images/hero/hero-slider-1.jpg', 'badge' => 'News & Media', 'cta_text' => 'Latest News', 'cta_link' => '#news']];
}

// Fetch active news articles. Blog posts have their own section below, so they
// are deliberately kept out of the news grid to avoid showing the same story twice.
$news = getRows('news_articles', "is_published = 1 AND category <> 'blog' ORDER BY published_at DESC");

// Latest blog stories for the preview strip below the news grid.
$blogSettings = getBlogSettings();
$blogPreview  = getBlogPosts(3);

// Partner-driven gallery preview. The full directory lives on news/gallery.php,
// but a short strip here keeps the News & Media landing page visually current.
$galleryPartners = wrcGalleryPartners();
$galleryPartnerPreview = [];
foreach ($galleryPartners as $galleryPartner) {
    if ((int) $galleryPartner['project_count'] < 1) {
        continue;
    }
    $galleryPartnerPreview[] = $galleryPartner;
    if (count($galleryPartnerPreview) >= 6) {
        break;
    }
}

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
                    <a href="<?= $base ?>news/gallery.php" class="bg-white/15 hover:bg-white/25 backdrop-blur-sm text-white font-bold text-sm px-8 py-4 rounded-xl border border-white/25 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
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
        $itemUrl = $cat === 'blog'
            ? $base . 'news/blog-detail.php?slug=' . rawurlencode((string) ($item['slug'] ?? ''))
            : $base . 'news/detail.php?slug=' . rawurlencode((string) ($item['slug'] ?? ''));
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
          <a href="<?= htmlspecialchars($itemUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-brandRed text-sm font-semibold hover:underline flex items-center gap-1">Read More <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if (!empty($blogPreview)): ?>
<!-- FROM THE BLOG -->
<section id="blog" class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-10">
      <div class="max-w-2xl">
        <span class="text-brandRed text-xs font-bold uppercase tracking-widest"><?= sanitize($blogSettings['intro_eyebrow'] ?: 'The WRC Blog') ?></span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">From the <span class="text-brandRed">Blog</span></h2>
        <p class="text-slate-500 mt-3"><?= sanitize(mb_substr((string) ($blogSettings['intro_text'] ?? ''), 0, 170)) ?></p>
      </div>
      <a href="<?= $base ?>news/blog.php" class="inline-flex shrink-0 items-center gap-2 bg-ink-900 hover:bg-brandRed text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
        Visit the blog <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($blogPreview as $blogPost): ?>
      <?php
        $blogPostImage   = getImageUrl((string) ($blogPost['featured_image'] ?? ''), 'assets/images/hero/news-hero.jpg');
        $blogPostTags    = getBlogTagList($blogPost['tags'] ?? '');
        $blogPostRead    = getBlogReadingTime((string) ($blogPost['content'] ?? ''));
        $blogPostExcerpt = getBlogExcerpt($blogPost, 140);
        $blogPostUrl     = getBlogPostUrl($blogPost);
      ?>
      <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300 hover:-translate-y-0.5 hover:border-brandRed/30 hover:shadow-xl">
        <a href="<?= $blogPostUrl ?>" class="relative block aspect-[16/10] overflow-hidden bg-ink-900">
          <img src="<?= htmlspecialchars($blogPostImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= sanitize($blogPost['title'] ?? 'Blog story') ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-ink-900/60 via-transparent to-transparent"></div>
          <?php if (!empty($blogPostTags)): ?>
          <span class="absolute left-4 top-4 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-ink-900 backdrop-blur"><?= sanitize($blogPostTags[0]) ?></span>
          <?php endif; ?>
          <span class="absolute bottom-4 right-4 rounded-full bg-ink-900/80 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur"><?= $blogPostRead ?> min</span>
        </a>
        <div class="p-5">
          <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-400">
            <span><i class="fa-solid fa-calendar-days mr-1 text-brandRed/60"></i><?= formatDate($blogPost['published_at'] ?? date('Y-m-d')) ?></span>
            <?php if (!empty($blogPost['author'])): ?>
            <span><i class="fa-solid fa-user-pen mr-1 text-brandRed/60"></i><?= sanitize($blogPost['author']) ?></span>
            <?php endif; ?>
          </div>
          <h3 class="mt-2 text-base font-bold text-ink-900 leading-snug group-hover:text-brandRed transition-colors line-clamp-2"><a href="<?= $blogPostUrl ?>"><?= sanitize($blogPost['title'] ?? '') ?></a></h3>
          <p class="mt-2 text-sm text-slate-500 leading-relaxed line-clamp-2"><?= sanitize($blogPostExcerpt) ?></p>
          <a href="<?= $blogPostUrl ?>" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-[0.1em] text-brandRed hover:gap-2.5 transition-all">Read story <i class="fa-solid fa-arrow-right text-[9px]"></i></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- PARTNER PHOTO GALLERIES -->
<section id="photos" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
      <div>
        <span class="text-brandRed text-xs font-bold uppercase tracking-widest">Visual Stories</span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-ink-900 mt-2">Partner <span class="text-brandRed">Photo Galleries</span></h2>
        <p class="text-slate-500 mt-3 max-w-2xl">Choose a partner or donor to explore the projects they supported, then open any project to see its photographs with full context.</p>
      </div>
      <a href="<?= $base ?>news/gallery.php" class="inline-flex items-center gap-2 bg-ink-900 hover:bg-brandRed text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shrink-0">
        Explore all galleries <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
    </div>

    <?php if (!empty($galleryPartnerPreview)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($galleryPartnerPreview as $galleryPartner): ?>
      <?php
        $partnerCover = (string) ($galleryPartner['cover_path'] ?? '');
        $partnerLogo  = trim((string) ($galleryPartner['logo_path'] ?? ''));
        $partnerType  = (string) ($galleryPartner['partner_type'] ?? 'collaborator');
        $partnerInfo  = $galleryPartner['type_info'];
      ?>
      <a href="<?= $base ?>news/partner.php?slug=<?= rawurlencode((string) $galleryPartner['slug']) ?>" class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-brandRed/30 hover:shadow-xl transition-all duration-300 flex flex-col">
        <div class="relative aspect-[16/10] bg-ink-900 overflow-hidden">
          <?php if ($partnerCover !== '' && imageExists($partnerCover)): ?>
          <img src="<?= $base ?><?= htmlspecialchars($partnerCover, ENT_QUOTES, 'UTF-8') ?>" alt="<?= sanitize($galleryPartner['name']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
          <?php else: ?>
          <div class="absolute inset-0 bg-gradient-to-br from-ink-800 to-black"></div>
          <span class="absolute inset-0 flex items-center justify-center font-display font-semibold text-6xl text-white/10"><?= sanitize($galleryPartner['initials']) ?></span>
          <?php endif; ?>
          <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-ink-900/15 to-transparent"></div>
          <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ink-900 shadow-sm">
            <i class="fa-solid <?= htmlspecialchars($partnerInfo['icon']) ?> text-brandRed text-[9px]"></i><?= htmlspecialchars($partnerInfo['short']) ?>
          </span>
          <div class="absolute left-4 right-4 bottom-4 flex items-end justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-10 h-10 rounded-xl bg-white shadow-lg p-1.5 flex items-center justify-center overflow-hidden shrink-0">
                <?php if ($partnerLogo !== '' && imageExists($partnerLogo)): ?>
                <img src="<?= $base ?><?= htmlspecialchars($partnerLogo, ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-contain">
                <?php else: ?>
                <span class="font-display font-semibold text-xs text-ink-900"><?= sanitize($galleryPartner['initials']) ?></span>
                <?php endif; ?>
              </span>
              <div class="min-w-0">
                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-white/60 mb-0.5">Partner</p>
                <h3 class="text-white font-display font-semibold text-base leading-tight truncate"><?= sanitize($galleryPartner['name']) ?></h3>
              </div>
            </div>
            <span class="w-8 h-8 rounded-full bg-white/15 border border-white/20 flex items-center justify-center text-white shrink-0 group-hover:bg-brandRed group-hover:border-brandRed transition-colors">
              <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </span>
          </div>
        </div>
        <div class="p-5 flex-1 flex flex-col">
          <p class="text-sm text-slate-500 leading-relaxed line-clamp-2"><?= sanitize($galleryPartner['description'] ?? 'A valued WRC partner supporting humanitarian work across Pakistan.') ?></p>
          <div class="mt-auto pt-4 flex items-center justify-between text-[11px] font-semibold text-slate-400 border-t border-slate-100 mt-4">
            <span><strong class="text-ink-900"><?= (int) $galleryPartner['project_count'] ?></strong> project<?= (int) $galleryPartner['project_count'] === 1 ? '' : 's' ?></span>
            <span><strong class="text-ink-900"><?= (int) $galleryPartner['photo_count'] ?></strong> photo<?= (int) $galleryPartner['photo_count'] === 1 ? '' : 's' ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-14 bg-slate-50 rounded-2xl border border-slate-200">
      <i class="fa-solid fa-images text-4xl text-slate-300 mb-4 block"></i>
      <h3 class="text-lg font-bold text-slate-700 mb-2">Partner galleries are being prepared</h3>
      <p class="text-sm text-slate-400">Project photographs will appear here as soon as they are published.</p>
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
                  <a href="<?= $base ?>news/gallery.php" class="bg-white/15 hover:bg-white/25 backdrop-blur-sm text-white font-bold text-sm px-8 py-4 rounded-xl border border-white/25 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
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
