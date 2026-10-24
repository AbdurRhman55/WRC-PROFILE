<?php
/**
* WRC Website - Footer Include
* Outputs: Footer section + JS includes + </body></html>
*
* Usage: Set $base before including ( e.g., $base = '../' for sub-pages )
*/

if ( !isset( $base ) ) $base = function_exists( 'siteBaseUrl' ) ? siteBaseUrl() : '/';

// Rebuilt logo: version the URL so the 7-day CDN cache cannot keep serving the old
// mark. Computed here as well in case this file is reached without header.php.
if ( !isset( $logoV ) ) $logoV = @filemtime( dirname( __DIR__ ) . '/assets/images/logo.png' );
?>

<!-- FOOTER -->
<footer class = 'bg-slate-950 text-slate-300 text-sm mt-auto border-t border-slate-800'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14'>
<div class = 'grid grid-cols-1 xl:grid-cols-12 gap-10'>

<div class = 'xl:col-span-4 space-y-5'>
<a href = "<?= $base ?>index.php" class = 'inline-flex items-center gap-3'>
<img src = "<?= $base ?>assets/images/logo.png?v=<?= $logoV ? (string) $logoV : '1' ?>" alt = 'WRC logo' class = 'w-16 h-16 object-contain rounded-2xl bg-white p-2 border border-slate-800' />
<div>
<p class = 'text-lg font-semibold text-white font-heading'>World Relief <br> Committee</p>
</div>
</a>
<p class = 'max-w-md leading-relaxed text-slate-400'>WRC partners with institutions, governments, and field agencies to deliver clean water, health services, education, and emergency relief across vulnerable communities.</p>
<div class = 'flex flex-wrap gap-3'>
<a href = "<?= $base ?>get-involved/contribute-with-us.php" class = 'inline-flex items-center justify-center rounded-full bg-brandRed px-5 py-3 text-white text-sm font-semibold shadow-lg shadow-brandRed/20 hover:bg-[#a30000] transition'>Let’s Work Together</a>
<a href = "<?= $base ?>contact/index.php" class = 'inline-flex items-center justify-center rounded-full border border-slate-700 px-5 py-3 text-slate-200 text-sm hover:bg-slate-800 transition'>Contact Us</a>
</div>
</div>

<div class = 'xl:col-span-2'>
<h4 class = 'text-white font-semibold uppercase tracking-[0.2em] text-xs mb-4'>Explore</h4>
<ul class = 'space-y-3 text-slate-400'>
<li><a href = "<?= $base ?>index.php" class = 'hover:text-white transition'>Home</a></li>
<li><a href = "<?= $base ?>about/index.php" class = 'hover:text-white transition'>About Us</a></li>
<li><a href = "<?= $base ?>our-work/index.php" class = 'hover:text-white transition'>Our Work</a></li>
<li><a href = "<?= $base ?>projects/index.php" class = 'hover:text-white transition'>Projects</a></li>
<li><a href = "<?= $base ?>news/index.php" class = 'hover:text-white transition'>News</a></li>
</ul>
</div>

<div class = 'xl:col-span-3'>
<h4 class = 'text-white font-semibold uppercase tracking-[0.2em] text-xs mb-4'>Our Projects</h4>
<ul class = 'space-y-3 text-slate-400'>
<li><a href = "<?= $base ?>projects/index.php" class = 'hover:text-white transition'>All Projects</a></li>
<li><a href = "<?= $base ?>projects/index.php#active" class = 'hover:text-white transition'>Active Projects</a></li>
<li><a href = "<?= $base ?>projects/index.php#completed" class = 'hover:text-white transition'>Completed Projects</a></li>
<li><a href = "<?= $base ?>get-involved/index.php" class = 'hover:text-white transition'>Get Involved</a></li>
</ul>
</div>

<div class = 'xl:col-span-3'>
<h4 class = 'text-white font-semibold uppercase tracking-[0.2em] text-xs mb-4'>Contact</h4>
<ul class = 'space-y-4 text-slate-400 text-[13px] leading-relaxed'>
<li class = 'flex items-start gap-3'>
<i class = 'fa-solid fa-location-dot mt-1 text-brandRed'></i>
<span><?= getSetting( 'address' ) ?></span>
</li>
<li class = 'flex items-center gap-3'>
<i class = 'fa-solid fa-phone text-brandRed'></i>
<a href = "tel:<?= str_replace(' ', '', getSetting('phone')) ?>" class = 'hover:text-white transition'><?= getSetting( 'phone' ) ?></a>
</li>
<li class = 'flex items-center gap-3'>
<i class = 'fa-solid fa-envelope text-brandRed'></i>
<a href = "mailto:<?= getSetting('email') ?>" class = 'hover:text-white transition'><?= getSetting( 'email' ) ?></a>
</li>
</ul>
</div>

</div>

<div class = 'border-t border-slate-800/70 mt-10 pt-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between text-slate-500 text-xs'>
<p>&copy;
<?= date( 'Y' ) ?> <?= getSetting( 'copyright' ) ?></p>
<div class = 'flex flex-wrap items-center gap-3 justify-center md:justify-end text-slate-400'>
<a href = "<?= $base ?>privacy.php" class = 'hover:text-white transition'>Privacy Policy</a>
<a href = "<?= $base ?>terms.php" class = 'hover:text-white transition'>Terms of Use</a>
<a href = "<?= $base ?>contact/index.php" class = 'hover:text-white transition'>Support</a>
</div>
</div>
</div>
</footer>

<!-- STICKY SOCIAL RAIL
     Fixed to the right edge, vertically centred, on every screen size.
     Each button opens the official WRC profile in a new tab. -->
<aside class = 'social-rail' aria-label = 'WRC on social media'>
<a href = 'https://www.facebook.com/wrc.org.pk' target = '_blank' rel = 'noopener noreferrer' class = 'social-rail-btn' aria-label = 'WRC on Facebook'>
<i class = 'fa-brands fa-facebook-f'></i><span class = 'social-rail-tip' aria-hidden = 'true'>Facebook</span>
</a>
<a href = 'https://www.linkedin.com/company/wrc-pakistan' target = '_blank' rel = 'noopener noreferrer' class = 'social-rail-btn' aria-label = 'WRC on LinkedIn'>
<i class = 'fa-brands fa-linkedin-in'></i><span class = 'social-rail-tip' aria-hidden = 'true'>LinkedIn</span>
</a>
<a href = 'https://www.instagram.com/wrc.pakistan' target = '_blank' rel = 'noopener noreferrer' class = 'social-rail-btn' aria-label = 'WRC on Instagram'>
<i class = 'fa-brands fa-instagram'></i><span class = 'social-rail-tip' aria-hidden = 'true'>Instagram</span>
</a>
<a href = 'https://x.com/WRC_Pakistan' target = '_blank' rel = 'noopener noreferrer' class = 'social-rail-btn' aria-label = 'WRC on X'>
<i class = 'fa-brands fa-x-twitter'></i><span class = 'social-rail-tip' aria-hidden = 'true'>X (Twitter)</span>
</a>
<a href = 'https://www.youtube.com/@WRC-Pakistan' target = '_blank' rel = 'noopener noreferrer' class = 'social-rail-btn' aria-label = 'WRC on YouTube'>
<i class = 'fa-brands fa-youtube'></i><span class = 'social-rail-tip' aria-hidden = 'true'>YouTube</span>
</a>
</aside>

<!-- FEATURED NEWS POPUP
     Shown once per session on first load, pointing at the newest published
     story that has a picture (so it always looks complete). Clicking through
     opens the full article. Skipped on the news pages, where the reader is
     already looking at the news. -->
<?php
$popupArticle = null;
if ( isset( $pdo ) ) {
    try {
        $popupStmt = $pdo->query(
            'SELECT id, slug, title, excerpt, category, published_at, featured_image
               FROM news_articles
              WHERE is_published = 1
                AND slug IS NOT NULL AND slug <> ""
                AND featured_image IS NOT NULL AND featured_image <> ""
              ORDER BY published_at DESC, id DESC
              LIMIT 1'
        );
        $popupArticle = $popupStmt ? $popupStmt->fetch( PDO::FETCH_ASSOC ) : false;
    } catch ( Throwable $popupError ) {
        $popupArticle = false;
    }
}

$popupLabels = [
    'press_release' => 'Press Release',
    'report'        => 'Report',
    'story'         => 'Story',
    'blog'          => 'Blog',
    'event'         => 'Event',
];

if ( $popupArticle ) {
    // getImageUrl() normalises the stored path, verifies the file still exists
    // on disk and returns an absolute URL. The absolute form is what fixes
    // sub-directory pages: a bare relative "assets/..." resolved against
    // "/about/" to "/about/assets/..." and 404'd even when the file existed.
    $popupImage = getImageUrl( (string) ( $popupArticle[ 'featured_image' ] ?? '' ), 'assets/images/hero.jpg' );
    $popupHref  = $base . 'news/detail.php?slug=' . rawurlencode( $popupArticle[ 'slug' ] );
    $popupLabel = $popupLabels[ $popupArticle[ 'category' ] ] ?? ucfirst( str_replace( '_', ' ', (string) $popupArticle[ 'category' ] ) );
    $popupOnNews = strpos( $_SERVER[ 'SCRIPT_NAME' ] ?? '', '/news/' ) !== false;
}
?>
<?php if ( !empty( $popupArticle ) && empty( $popupOnNews ) ): ?>
<div id = 'news-popup' class = 'news-popup-backdrop' role = 'dialog' aria-modal = 'true' aria-labelledby = 'news-popup-title' hidden>
<div class = 'news-popup' role = 'document'>

<button id = 'news-popup-close' class = 'news-popup-close' aria-label = 'Close'>
<i class = 'fa-solid fa-xmark'></i>
</button>

<div class = 'grid md:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]'>

<div class = 'relative h-44 md:h-full min-h-[170px] overflow-hidden bg-slate-200'>
<img src = "<?= $popupImage ?>" alt = "<?= sanitize( $popupArticle[ 'title' ] ) ?>" class = 'absolute inset-0 w-full h-full object-cover' loading = 'lazy'>
<div class = 'absolute inset-0 bg-gradient-to-t md:bg-gradient-to-r from-ink-900/70 via-ink-900/10 to-transparent'></div>
</div>

<div class = 'p-5 sm:p-6'>
<div class = 'flex flex-wrap items-center gap-2 mb-3'>
<span class = 'bg-brandRed text-white text-[9px] font-bold uppercase tracking-[0.16em] px-2.5 py-1 rounded-full'><?= sanitize( $popupLabel ) ?></span>
<span class = 'text-[11px] text-slate-400'><i class = 'fa-solid fa-calendar-days mr-1'></i><?= formatDate( $popupArticle[ 'published_at' ] ?? date( 'Y-m-d' ) ) ?></span>
</div>

<h2 id = 'news-popup-title' class = 'text-lg sm:text-xl font-display font-bold text-ink-900 leading-snug mb-2 line-clamp-3'><?= sanitize( $popupArticle[ 'title' ] ) ?></h2>

<p class = 'text-sm text-slate-500 leading-relaxed mb-5 line-clamp-3'><?= sanitize( $popupArticle[ 'excerpt' ] ?? '' ) ?></p>

<div class = 'flex flex-wrap items-center gap-3'>
<a href = "<?= $popupHref ?>" class = 'inline-flex items-center gap-2 bg-brandRed hover:bg-brandRed-hover text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all'>
Read Full Story <i class = 'fa-solid fa-arrow-right text-[10px]'></i>
</a>
<a href = "<?= $base ?>news/index.php" class = 'text-xs font-semibold text-slate-500 hover:text-brandRed transition-colors'>All news</a>
</div>
</div>

</div>
</div>
</div>

<script>
(function () {
  var KEY = 'wrc-news-popup-seen';
  var box = document.getElementById('news-popup');
  var closeBtn = document.getElementById('news-popup-close');
  if (!box) return;

  // Once per session: a popup that reappears on every page stops being useful.
  try { if (sessionStorage.getItem(KEY)) return; } catch (e) {}

  var lastFocus = null;

  function open() {
    lastFocus = document.activeElement;
    box.hidden = false;
    requestAnimationFrame(function () { box.classList.add('is-open'); });
    // requestAnimationFrame is suspended while the page is hidden (background
    // tab, restored session). Without this fallback the backdrop would stay at
    // opacity 0 - invisible, yet with the scroll already locked - so mirror the
    // class on a short timer. Adding a class that is present is a no-op, so the
    // transition is never restarted when rAF does fire first.
    setTimeout(function () { box.classList.add('is-open'); }, 100);
    document.body.style.overflow = 'hidden';
    if (closeBtn) closeBtn.focus();
    try { sessionStorage.setItem(KEY, '1'); } catch (e) {}
  }

  function close() {
    box.classList.remove('is-open');
    document.body.style.overflow = '';
    window.setTimeout(function () { box.hidden = true; }, 220);
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  if (closeBtn) closeBtn.addEventListener('click', close);
  box.addEventListener('click', function (e) { if (e.target === box) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !box.hidden) close(); });

  window.setTimeout(open, 900);
})();
</script>
<?php endif; ?>

<!-- Main JavaScript File -->
<script src = "<?= $base ?>assets/js/main.js"></script>
<?php if ( isset( $extraScripts ) ) echo $extraScripts;
?>
</body>
</html>
