<?php
/**
 * WRC News Detail Page
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: ' . $base . 'news/index.php');
    exit;
}

global $pdo;
$stmt = $pdo->prepare("SELECT * FROM news_articles WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Location: ' . $base . 'news/index.php');
    exit;
}

$cat = $article['category'] ?? 'story';
$catLabels = [
    'press_release' => 'Press Release',
    'report'        => 'Report',
    'story'         => 'Story',
    'blog'          => 'Blog',
    'event'         => 'Event',
];
$catColors = [
    'press_release' => 'bg-brandRed',
    'report'        => 'bg-teal-500',
    'story'         => 'bg-purple-500',
    'blog'          => 'bg-blue-500',
    'event'         => 'bg-amber-500',
];
$catLabel = $catLabels[$cat] ?? ucfirst(str_replace('_', ' ', $cat));
$catColor = $catColors[$cat] ?? 'bg-slate-500';

$newsImage = !empty($article['featured_image']) ? $article['featured_image'] : 'assets/images/hero/hero-slider-1.jpg';
if (strpos($newsImage, 'assets/') !== 0 && strpos($newsImage, 'http') !== 0) {
    $newsImage = 'assets/images/' . $newsImage;
}

// Related articles (same category, excluding current)
$relatedStmt = $pdo->prepare("SELECT * FROM news_articles WHERE category = ? AND id != ? AND is_published = 1 ORDER BY published_at DESC LIMIT 3");
$relatedStmt->execute([$cat, $article['id']]);
$relatedArticles = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = sanitize($article['title']) . ' | WRC News';
$metaDescription = sanitize($article['excerpt'] ?? mb_substr(strip_tags($article['content'] ?? ''), 0, 160));

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- ARTICLE HERO -->
<section class="relative w-full bg-ink-900 text-white min-h-[400px] flex items-end overflow-hidden">
  <div class="absolute inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('<?= $base ?><?= $newsImage ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-ink-900/80 to-ink-900/40"></div>
  </div>
  <div class="relative z-10 w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 pt-32">
    <div class="flex items-center gap-2 mb-4">
      <a href="<?= $base ?>index.php" class="text-white/60 hover:text-white text-sm transition-colors">Home</a>
      <i class="fa-solid fa-chevron-right text-[9px] text-white/40"></i>
      <a href="<?= $base ?>news/index.php" class="text-white/60 hover:text-white text-sm transition-colors">News</a>
      <i class="fa-solid fa-chevron-right text-[9px] text-white/40"></i>
      <span class="text-brandRed text-sm font-semibold"><?= sanitize($catLabel) ?></span>
    </div>
    <span class="<?= $catColor ?> text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full inline-block mb-4"><?= sanitize($catLabel) ?></span>
    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold leading-tight mb-5"><?= sanitize($article['title']) ?></h1>
    <div class="flex flex-wrap items-center gap-4 text-sm text-white/60">
      <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-brandRed/60"></i> <?= formatDate($article['published_at'] ?? date('Y-m-d')) ?></span>
      <?php if (!empty($article['author'])): ?>
      <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-pen text-brandRed/60"></i> <?= sanitize($article['author']) ?></span>
      <?php endif; ?>
    </div>
  </div>
</section>


<!-- ARTICLE CONTENT -->
<section class="py-16 bg-white">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <article class="prose prose-lg prose-slate max-w-none
      prose-headings:font-display prose-headings:text-ink-900
      prose-h2:text-2xl prose-h2:mt-10 prose-h2:mb-4
      prose-h3:text-xl prose-h3:mt-8 prose-h3:mb-3
      prose-p:text-slate-600 prose-p:leading-relaxed
      prose-a:text-brandRed prose-a:no-underline hover:prose-a:underline
      prose-strong:text-ink-900
      prose-img:rounded-2xl prose-img:shadow-lg
      prose-blockquote:border-brandRed prose-blockquote:bg-slate-50 prose-blockquote:rounded-r-xl prose-blockquote:py-1 prose-blockquote:px-6
      prose-li:text-slate-600">
      <?= $article['content'] ?>
    </article>

    <!-- SHARE -->
    <div class="mt-12 pt-8 border-t border-slate-200">
      <div class="flex items-center justify-between">
        <span class="text-sm font-semibold text-slate-500">Share this article</span>
        <div class="flex gap-3">
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white text-slate-500 flex items-center justify-center transition-all" aria-label="Share on Facebook">
            <i class="fa-brands fa-facebook-f text-sm"></i>
          </a>
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white text-slate-500 flex items-center justify-center transition-all" aria-label="Share on Twitter">
            <i class="fa-brands fa-twitter text-sm"></i>
          </a>
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($article['title']) ?>" target="_blank" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white text-slate-500 flex items-center justify-center transition-all" aria-label="Share on LinkedIn">
            <i class="fa-brands fa-linkedin-in text-sm"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- RELATED ARTICLES -->
<?php if (!empty($relatedArticles)): ?>
<section class="py-16 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-display font-bold text-ink-900 mb-8">Related <span class="text-brandRed">Articles</span></h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($relatedArticles as $rel): ?>
      <?php
        $relImage = !empty($rel['featured_image']) ? $rel['featured_image'] : 'assets/images/hero/hero-slider-1.jpg';
        if (strpos($relImage, 'assets/') !== 0 && strpos($relImage, 'http') !== 0) {
            $relImage = 'assets/images/' . $relImage;
        }
        $relCat = $rel['category'] ?? 'story';
        $relCatLabel = $catLabels[$relCat] ?? ucfirst(str_replace('_', ' ', $relCat));
        $relCatColor = $catColors[$relCat] ?? 'bg-slate-500';
      ?>
      <a href="<?= $base ?>news/<?= sanitize($rel['slug']) ?>" class="bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-brandRed/20 hover:shadow-xl transition-all group program-card-hover block">
        <div class="relative h-44 overflow-hidden">
          <img src="<?= $base ?><?= $relImage ?>" alt="<?= sanitize($rel['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
          <span class="absolute top-3 left-3 <?= $relCatColor ?> text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full"><?= sanitize($relCatLabel) ?></span>
        </div>
        <div class="p-5">
          <div class="text-xs text-slate-400 mb-2"><i class="fa-solid fa-calendar-days mr-1"></i> <?= formatDate($rel['published_at'] ?? date('Y-m-d')) ?></div>
          <h3 class="text-base font-bold text-ink-900 leading-snug group-hover:text-brandRed transition-colors line-clamp-2"><?= sanitize($rel['title']) ?></h3>
          <p class="text-sm text-slate-500 mt-2 line-clamp-2"><?= sanitize($rel['excerpt'] ?? '') ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
