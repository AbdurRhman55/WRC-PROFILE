<?php
/**
* WRC News Detail Page
*/
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$slug = $_GET[ 'slug' ] ?? '';

if ( empty( $slug ) ) {
    header( 'Location: ' . $base . 'news/index.php' );
    exit;
}

global $pdo;
$stmt = $pdo->prepare( 'SELECT * FROM news_articles WHERE slug = ? AND is_published = 1' );
$stmt->execute( [ $slug ] );
$article = $stmt->fetch( PDO::FETCH_ASSOC );

if ( !$article ) {
    header( 'Location: ' . $base . 'news/index.php' );
    exit;
}

$cat = $article[ 'category' ] ?? 'story';
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
$catLabel = $catLabels[ $cat ] ?? ucfirst( str_replace( '_', ' ', $cat ) );
$catColor = $catColors[ $cat ] ?? 'bg-slate-500';

$newsImage = !empty( $article[ 'featured_image' ] ) ? $article[ 'featured_image' ] : 'assets/images/hero/hero-slider-1.jpg';
if ( strpos( $newsImage, 'assets/' ) !== 0 && strpos( $newsImage, 'http' ) !== 0 ) {
    $newsImage = 'assets/images/' . $newsImage;
}

// The hero only uses the article photograph as a 30%-opacity backdrop under a
// heavy gradient, so a reader never actually sees the picture. Re-show it at full
// clarity just under the opening paragraph — that is where the long block of text
// needs breaking up. Skipped when the body already carries an <img>.
$bodyHtml = (string) ( $article[ 'content' ] ?? '' );
if ( stripos( $bodyHtml, '<img' ) === false && $newsImage !== '' ) {
    $figureHtml = "\n<figure class = 'ab-figure'>"
                . "\n<img src = \"{$base}{$newsImage}\" alt = \"" . sanitize( $article[ 'title' ] ) . "\" loading = 'lazy' decoding = 'async'>"
                . "\n</figure>\n";
    $firstClose = stripos( $bodyHtml, '</p>' );
    if ( $firstClose !== false ) {
        $bodyHtml = substr( $bodyHtml, 0, $firstClose + 4 ) . $figureHtml . substr( $bodyHtml, $firstClose + 4 );
    } else {
        $bodyHtml = $figureHtml . $bodyHtml;
    }
}

// Related articles ( same category, excluding current )
$relatedStmt = $pdo->prepare( 'SELECT * FROM news_articles WHERE category = ? AND id != ? AND is_published = 1 ORDER BY published_at DESC LIMIT 3' );
$relatedStmt->execute( [ $cat, $article[ 'id' ] ] );
$relatedArticles = $relatedStmt->fetchAll( PDO::FETCH_ASSOC );

// Reading time at a comfortable 220 words per minute.
$readingWords = array_filter( preg_split( '/\s+/', trim( strip_tags( (string) ( $article[ 'content' ] ?? '' ) ) ) ) );
$readingTime = max( 1, (int) ceil( count( $readingWords ) / 220 ) );

// Previous / next article in publication order, so a reader can keep going
// without going back to the list.
$publishedOn = $article[ 'published_at' ] ?? '0000-00-00';
$prevStmt = $pdo->prepare(
    'SELECT slug, title, category, published_at FROM news_articles
      WHERE is_published = 1 AND slug IS NOT NULL AND slug <> ""
        AND ( published_at < ? OR ( published_at = ? AND id < ? ) )
      ORDER BY published_at DESC, id DESC LIMIT 1'
);
$prevStmt->execute( [ $publishedOn, $publishedOn, $article[ 'id' ] ] );
$prevArticle = $prevStmt->fetch( PDO::FETCH_ASSOC );

$nextStmt = $pdo->prepare(
    'SELECT slug, title, category, published_at FROM news_articles
      WHERE is_published = 1 AND slug IS NOT NULL AND slug <> ""
        AND ( published_at > ? OR ( published_at = ? AND id > ? ) )
      ORDER BY published_at ASC, id ASC LIMIT 1'
);
$nextStmt->execute( [ $publishedOn, $publishedOn, $article[ 'id' ] ] );
$nextArticle = $nextStmt->fetch( PDO::FETCH_ASSOC );

// Canonical share URL.
$shareUrl = ( !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' ? 'https' : 'http' )
          . '://' . ( $_SERVER[ 'HTTP_HOST' ] ?? 'wrc.org.pk' )
          . ( $_SERVER[ 'REQUEST_URI' ] ?? ( '/news/' . $slug ) );
$shareUrlEnc = urlencode( $shareUrl );
$shareTitleEnc = urlencode( $article[ 'title' ] );

// A one-line summary, shown above the article so the reader gets the point
// even if they read nothing else.
$summaryLead = trim( (string) ( $article[ 'excerpt' ] ?? '' ) );
if ( $summaryLead === '' ) {
    $firstPara = preg_split( '/\n\s*\n|<\/p>/i', (string) ( $article[ 'content' ] ?? '' ) );
    $summaryLead = trim( strip_tags( (string) ( $firstPara[ 0 ] ?? '' ) ) );
}

$pageTitle = sanitize( $article[ 'title' ] ) . ' | WRC News';
$metaDescription = sanitize( $article[ 'excerpt' ] ?? mb_substr( strip_tags( $article[ 'content' ] ?? '' ), 0, 160 ) );

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>

<!-- ARTICLE HERO -->
<section class = 'relative w-full bg-ink-900 text-white min-h-[400px] flex items-end overflow-hidden'>
<div class = 'absolute inset-0 z-0'>
<div class = 'absolute inset-0 bg-cover bg-center opacity-30' style = "background-image: url('<?= $base ?><?= $newsImage ?>');"></div>
<div class = 'absolute inset-0 bg-gradient-to-t from-ink-900 via-ink-900/80 to-ink-900/40'></div>
</div>
<div class = 'relative z-10 w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 pt-32'>
<div class = 'flex items-center gap-2 mb-4'>
<a href = "<?= $base ?>index.php" class = 'text-white/60 hover:text-white text-sm transition-colors'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[9px] text-white/40'></i>
<a href = "<?= $base ?>news/index.php" class = 'text-white/60 hover:text-white text-sm transition-colors'>News</a>
<i class = 'fa-solid fa-chevron-right text-[9px] text-white/40'></i>
<span class = 'text-brandRed text-sm font-semibold'><?= sanitize( $catLabel ) ?></span>
</div>
<span class = "<?= $catColor ?> text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full inline-block mb-4"><?= sanitize( $catLabel ) ?></span>
<h1 class = 'text-3xl sm:text-4xl lg:text-5xl font-display font-bold leading-tight mb-5'><?= sanitize( $article[ 'title' ] ) ?></h1>
<div class = 'flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/60'>
<span class = 'flex items-center gap-1.5'><i class = 'fa-solid fa-calendar-days text-brandRed/60'></i> <?= formatDate( $article[ 'published_at' ] ?? date( 'Y-m-d' ) ) ?></span>
<?php if ( !empty( $article[ 'author' ] ) ): ?>
<span class = 'flex items-center gap-1.5'><i class = 'fa-solid fa-user-pen text-brandRed/60'></i> <?= sanitize( $article[ 'author' ] ) ?></span>
<?php endif; ?>
<span class = 'flex items-center gap-1.5'><i class = 'fa-solid fa-clock text-brandRed/60'></i> <?= $readingTime ?> min read</span>
</div>
</div>
</section>

<!-- AT A GLANCE: the summary and the key facts, before the reader has to scroll -->
<section class = 'relative z-10 -mt-9'>
<div class = 'max-w-4xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'bg-white rounded-2xl border border-slate-200/80 shadow-[0_16px_40px_-24px_rgba(15,23,42,0.45)] p-5 sm:p-6'>

<div class = 'flex items-center gap-2 mb-3'>
<span class = 'w-6 h-6 rounded-md bg-brandRed/10 text-brandRed flex items-center justify-center shrink-0'>
<i class = 'fa-solid fa-bolt text-[10px]'></i>
</span>
<span class = 'text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400'>In short</span>
</div>

<?php if ( $summaryLead !== '' ): ?>
<p class = 'text-slate-700 text-[15px] sm:text-base leading-relaxed mb-5'><?= sanitize( $summaryLead ) ?></p>
<?php endif; ?>

<dl class = 'grid grid-cols-2 sm:grid-cols-4 gap-px bg-slate-100 rounded-xl overflow-hidden'>
<div class = 'bg-white px-4 py-3'>
<dt class = 'text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1'>Published</dt>
<dd class = 'text-sm font-semibold text-ink-900'><?= formatDate( $article[ 'published_at' ] ?? date( 'Y-m-d' ) ) ?></dd>
</div>
<div class = 'bg-white px-4 py-3'>
<dt class = 'text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1'>Category</dt>
<dd class = 'text-sm font-semibold text-ink-900'><?= sanitize( $catLabel ) ?></dd>
</div>
<div class = 'bg-white px-4 py-3'>
<dt class = 'text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1'>Reading time</dt>
<dd class = 'text-sm font-semibold text-ink-900'><?= $readingTime ?> min</dd>
</div>
<div class = 'bg-white px-4 py-3'>
<dt class = 'text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1'><?= !empty( $article[ 'author' ] ) ? 'Author' : 'Source' ?></dt>
<dd class = 'text-sm font-semibold text-ink-900'><?= sanitize( $article[ 'author' ] ?? 'Welfare & Relief Committee' ) ?></dd>
</div>
</dl>

</div>
</div>
</section>

<!-- ARTICLE CONTENT -->
<section class = 'py-12 sm:py-16 bg-white'>
<div class = 'max-w-4xl mx-auto px-4 sm:px-6 lg:px-8'>
<article class = 'article-body'>
<?= $bodyHtml ?>
</article>

<!-- SHARE -->
<div class = 'mt-12 pt-8 border-t border-slate-200'>
<div class = 'flex flex-wrap items-center justify-between gap-4'>
<span class = 'text-sm font-semibold text-slate-500'>Share this article</span>
<div class = 'flex flex-wrap items-center gap-2'>

<a href = "https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrlEnc ?>" target = '_blank' rel = 'noopener noreferrer' class = 'w-10 h-10 rounded-full bg-slate-100 hover:bg-[#1877F2] hover:text-white text-slate-500 flex items-center justify-center transition-all' aria-label = 'Share on Facebook' title = 'Share on Facebook'>
<i class = 'fa-brands fa-facebook-f text-sm'></i>
</a>

<a href = "https://x.com/intent/tweet?url=<?= $shareUrlEnc ?>&text=<?= $shareTitleEnc ?>" target = '_blank' rel = 'noopener noreferrer' class = 'w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-500 flex items-center justify-center transition-all' aria-label = 'Share on X' title = 'Share on X'>
<i class = 'fa-brands fa-x-twitter text-sm'></i>
</a>

<a href = "https://www.linkedin.com/shareArticle?mini=true&url=<?= $shareUrlEnc ?>&title=<?= $shareTitleEnc ?>" target = '_blank' rel = 'noopener noreferrer' class = 'w-10 h-10 rounded-full bg-slate-100 hover:bg-[#0A66C2] hover:text-white text-slate-500 flex items-center justify-center transition-all' aria-label = 'Share on LinkedIn' title = 'Share on LinkedIn'>
<i class = 'fa-brands fa-linkedin-in text-sm'></i>
</a>

<a href = "https://wa.me/?text=<?= $shareTitleEnc ?>%20<?= $shareUrlEnc ?>" target = '_blank' rel = 'noopener noreferrer' class = 'w-10 h-10 rounded-full bg-slate-100 hover:bg-[#25D366] hover:text-white text-slate-500 flex items-center justify-center transition-all' aria-label = 'Share on WhatsApp' title = 'Share on WhatsApp'>
<i class = 'fa-brands fa-whatsapp text-sm'></i>
</a>

<a href = "mailto:?subject=<?= $shareTitleEnc ?>&body=<?= $shareUrlEnc ?>" class = 'w-10 h-10 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white text-slate-500 flex items-center justify-center transition-all' aria-label = 'Share by email' title = 'Share by email'>
<i class = 'fa-solid fa-envelope text-sm'></i>
</a>

<button type = 'button' id = 'copy-link-btn' data-url = "<?= $shareUrl ?>" class = 'w-10 h-10 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white text-slate-500 flex items-center justify-center transition-all' aria-label = 'Copy link' title = 'Copy link'>
<i class = 'fa-solid fa-link text-sm'></i>
</button>

</div>
</div>
<p id = 'copy-link-status' class = 'text-xs text-brandRed font-semibold mt-3 h-4' role = 'status' aria-live = 'polite'></p>
</div>

<!-- PREVIOUS / NEXT -->
<nav class = 'mt-8 grid gap-4 sm:grid-cols-2' aria-label = 'More articles'>
<?php if ( !empty( $prevArticle ) ): ?>
<a href = "<?= $base ?>news/detail.php?slug=<?= rawurlencode( $prevArticle[ 'slug' ] ) ?>" class = 'group rounded-2xl border border-slate-200 hover:border-brandRed/40 p-5 transition-all'>
<span class = 'text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2 mb-2'>
<i class = 'fa-solid fa-arrow-left text-[9px] group-hover:text-brandRed transition-colors'></i> Previous
</span>
<span class = 'block text-sm font-bold text-ink-900 leading-snug group-hover:text-brandRed transition-colors line-clamp-2'><?= sanitize( $prevArticle[ 'title' ] ) ?></span>
</a>
<?php else: ?>
<div></div>
<?php endif; ?>

<?php if ( !empty( $nextArticle ) ): ?>
<a href = "<?= $base ?>news/detail.php?slug=<?= rawurlencode( $nextArticle[ 'slug' ] ) ?>" class = 'group rounded-2xl border border-slate-200 hover:border-brandRed/40 p-5 transition-all text-right sm:text-right'>
<span class = 'text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2 mb-2 sm:justify-end'>
Next <i class = 'fa-solid fa-arrow-right text-[9px] group-hover:text-brandRed transition-colors'></i>
</span>
<span class = 'block text-sm font-bold text-ink-900 leading-snug group-hover:text-brandRed transition-colors line-clamp-2'><?= sanitize( $nextArticle[ 'title' ] ) ?></span>
</a>
<?php endif; ?>
</nav>

<a href = "<?= $base ?>news/index.php" class = 'mt-8 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brandRed transition-colors'>
<i class = 'fa-solid fa-arrow-left text-xs'></i> Back to all news
</a>

<script>
(function () {
  var btn = document.getElementById('copy-link-btn');
  var status = document.getElementById('copy-link-status');
  if (!btn) return;
  btn.addEventListener('click', function () {
    var url = btn.getAttribute('data-url') || window.location.href;
    var done = function () {
      if (status) { status.textContent = 'Link copied to clipboard.'; window.setTimeout(function () { status.textContent = ''; }, 2500); }
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(done).catch(function () { window.prompt('Copy this link:', url); });
    } else {
      window.prompt('Copy this link:', url);
    }
  });
})();
</script>
</div>
</section>

<!-- RELATED ARTICLES -->
<?php if ( !empty( $relatedArticles ) ): ?>
<section class = 'py-16 bg-slate-50'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<h2 class = 'text-2xl font-display font-bold text-ink-900 mb-8'>Related <span class = 'text-brandRed'>Articles</span></h2>
<div class = 'grid grid-cols-1 md:grid-cols-3 gap-6'>
<?php foreach ( $relatedArticles as $rel ): ?>
<?php
$relImage = !empty( $rel[ 'featured_image' ] ) ? $rel[ 'featured_image' ] : 'assets/images/hero/hero-slider-1.jpg';
if ( strpos( $relImage, 'assets/' ) !== 0 && strpos( $relImage, 'http' ) !== 0 ) {
    $relImage = 'assets/images/' . $relImage;
}
$relCat = $rel[ 'category' ] ?? 'story';
$relCatLabel = $catLabels[ $relCat ] ?? ucfirst( str_replace( '_', ' ', $relCat ) );
$relCatColor = $catColors[ $relCat ] ?? 'bg-slate-500';
?>
<a href = "<?= $base ?>news/detail.php?slug=<?= rawurlencode( (string) $rel['slug'] ) ?>" class = 'bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-brandRed/20 hover:shadow-xl transition-all group program-card-hover block'>
<div class = 'relative h-44 overflow-hidden'>
<img src = "<?= $base ?><?= $relImage ?>" alt = "<?= sanitize($rel['title']) ?>" class = 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500' loading = 'lazy'>
<span class = "absolute top-3 left-3 <?= $relCatColor ?> text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full"><?= sanitize( $relCatLabel ) ?></span>
</div>
<div class = 'p-5'>
<div class = 'text-xs text-slate-400 mb-2'><i class = 'fa-solid fa-calendar-days mr-1'></i> <?= formatDate( $rel[ 'published_at' ] ?? date( 'Y-m-d' ) ) ?></div>
<h3 class = 'text-base font-bold text-ink-900 leading-snug group-hover:text-brandRed transition-colors line-clamp-2'><?= sanitize( $rel[ 'title' ] ) ?></h3>
<p class = 'text-sm text-slate-500 mt-2 line-clamp-2'><?= sanitize( $rel[ 'excerpt' ] ?? '' ) ?></p>
</div>
</a>
<?php endforeach;
?>
</div>
</div>
</section>
<?php endif;
?>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
