<?php
/**
 * WRC - Project photo gallery.
 *
 * Shows every published photograph for one project. Each image carries a
 * caption and a context note so a visitor understands exactly what it shows.
 */
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base  = siteBaseUrl();
$slug  = trim( (string) ( $_GET['slug'] ?? '' ) );
$project = $slug !== '' ? getProjectBySlug( $slug ) : null;

if ( !$project ) {
    header( 'Location: ' . $base . 'news/gallery.php' );
    exit;
}

$partners = wrcProjectPartners( (string) $project['slug'] );
$media    = wrcProjectMedia( (string) $project['slug'] );

$heroPath = trim( (string) ( $project['hero_image'] ?? '' ) );
$summary  = trim( (string) ( $project['overview'] ?? '' ) );
if ( $summary === '' ) {
    $summary = trim( (string) ( $project['description'] ?? '' ) );
}

$partnerNames = array_values( array_filter( array_map( static function ( $partner ) {
    return trim( (string) ( $partner['name'] ?? '' ) );
}, $partners ) ) );

// No uploaded gallery yet? The project's own cover is still a legitimate
// photograph, shown with the project context instead of an empty page.
if ( empty( $media ) && $heroPath !== '' && imageExists( $heroPath ) ) {
    $media[] = [
        'image_path' => $heroPath,
        'caption'    => 'Project cover photograph',
        'context'    => $summary !== '' ? $summary : 'Field documentation from ' . $project['title'] . '.',
    ];
}

$heroImage = $heroPath !== '' && imageExists( $heroPath ) ? $heroPath : ( $media[0]['image_path'] ?? 'assets/images/hero/hero-slider-1.jpg' );

$status    = wrcProjectStatusMeta( (string) ( $project['status'] ?? '' ) );
$yearLabel = wrcProjectYearLabel( $project );
$place     = trim( (string) ( $project['district'] ?? '' ) );
if ( $place === '' ) {
    $place = trim( (string) ( $project['location_details'] ?? '' ) );
}
$beneficiaries = (int) ( $project['beneficiaries_count'] ?? 0 );
$category      = trim( (string) ( $project['category'] ?? '' ) );
$detailUrl     = $base . 'projects/project-name.php?slug=' . rawurlencode( (string) $project['slug'] );

$pageTitle       = $project['title'] . ' — Photo Gallery | WRC';
$metaDescription = mb_substr( strip_tags( $summary ), 0, 155 );

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class="flex-grow bg-slate-50">

  <!-- PROJECT HERO -->
  <section class="relative isolate overflow-hidden bg-ink-900 text-white">
    <img src="<?= wrcMediaUrl( (string) $heroImage ) ?>" alt="<?= htmlspecialchars( (string) $project['title'] ) ?>" class="absolute inset-0 w-full h-full object-cover opacity-55" fetchpriority="high">
    <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-ink-900/55 to-ink-900/25"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-16 sm:pt-14 sm:pb-20 min-h-[500px] flex flex-col justify-end">
      <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/50 mb-auto" aria-label="Breadcrumb">
        <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
        <i class="fa-solid fa-chevron-right text-[7px]"></i>
        <a href="<?= $base ?>news/index.php" class="hover:text-white transition-colors">News &amp; Media</a>
        <i class="fa-solid fa-chevron-right text-[7px]"></i>
        <a href="<?= $base ?>news/gallery.php" class="hover:text-white transition-colors">Photo Galleries</a>
        <?php if ( $partners && !empty( $partners[0]['slug'] ) ): ?>
          <i class="fa-solid fa-chevron-right text-[7px]"></i>
          <a href="<?= $base ?>news/partner.php?slug=<?= rawurlencode( (string) $partners[0]['slug'] ) ?>" class="hover:text-white transition-colors"><?= htmlspecialchars( (string) $partners[0]['name'] ) ?></a>
        <?php endif; ?>
      </nav>

      <div class="max-w-4xl mt-12">
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <span class="inline-flex items-center gap-1.5 rounded-full bg-brandRed px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white">
            <i class="fa-solid fa-images text-[9px]"></i> Project Gallery
          </span>
          <?php if ( $category !== '' ): ?>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/20 backdrop-blur px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/90">
              <i class="fa-solid fa-tag text-[9px]"></i><?= htmlspecialchars( $category ) ?>
            </span>
          <?php endif; ?>
          <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 border border-white/20 backdrop-blur px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/90">
            <span class="w-1.5 h-1.5 rounded-full <?= $status['dot'] ?>"></span><?= htmlspecialchars( $status['label'] ) ?>
          </span>
        </div>

        <h1 class="font-display font-semibold text-3xl sm:text-4xl lg:text-5xl leading-[1.08] max-w-4xl"><?= htmlspecialchars( (string) $project['title'] ) ?></h1>

        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-white/70">
          <?php if ( $place !== '' ): ?>
            <span class="inline-flex items-center gap-2"><i class="fa-solid fa-location-dot text-brandRed"></i><?= htmlspecialchars( $place ) ?></span>
          <?php endif; ?>
          <?php if ( $yearLabel !== '' ): ?>
            <span class="inline-flex items-center gap-2"><i class="fa-solid fa-calendar-days text-brandRed"></i><?= htmlspecialchars( $yearLabel ) ?></span>
          <?php endif; ?>
          <?php if ( $beneficiaries > 0 ): ?>
            <span class="inline-flex items-center gap-2"><i class="fa-solid fa-users text-brandRed"></i><?= number_format( $beneficiaries ) ?> beneficiaries</span>
          <?php endif; ?>
          <span class="inline-flex items-center gap-2"><i class="fa-solid fa-camera-retro text-brandRed"></i><?= count( $media ) ?> photograph<?= count( $media ) === 1 ? '' : 's' ?></span>
        </div>
      </div>
    </div>
  </section>

  <!-- OVERVIEW + PARTNERS -->
  <section class="relative z-10 -mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-900/5 p-6 sm:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
          <div class="lg:col-span-7">
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-brandRed">About this gallery</span>
            <h2 class="font-display text-2xl sm:text-3xl font-semibold text-ink-900 mt-2 mb-3">The story behind these photographs</h2>
            <p class="text-sm sm:text-base text-slate-500 leading-relaxed"><?= htmlspecialchars( $summary !== '' ? $summary : 'Field photographs documenting the delivery and impact of this WRC project.' ) ?></p>

            <?php if ( $partnerNames ): ?>
              <div class="mt-5">
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 mb-2">Delivered in partnership with</p>
                <div class="flex flex-wrap gap-2">
                  <?php foreach ( $partners as $partner ): ?>
                    <?php if ( !empty( $partner['slug'] ) ): ?>
                      <a href="<?= $base ?>news/partner.php?slug=<?= rawurlencode( (string) $partner['slug'] ) ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 hover:border-brandRed/30 hover:text-brandRed transition-colors">
                        <i class="fa-solid <?= htmlspecialchars( $partner['type_info']['icon'] ?? 'fa-handshake' ) ?> text-[10px] text-brandRed/70"></i>
                        <?= htmlspecialchars( (string) $partner['name'] ) ?>
                      </a>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
                        <i class="fa-solid fa-handshake text-[10px] text-brandRed/70"></i>
                        <?= htmlspecialchars( (string) $partner['name'] ) ?>
                      </span>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <div class="lg:col-span-5 lg:border-l lg:border-slate-100 lg:pl-10">
            <div class="grid grid-cols-2 gap-4">
              <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                <p class="font-display text-2xl font-semibold text-ink-900"><?= number_format( count( $media ) ) ?></p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Photographs</p>
              </div>
              <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                <p class="font-display text-2xl font-semibold text-ink-900"><?= count( $partners ) ?></p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Partner<?= count( $partners ) === 1 ? '' : 's' ?></p>
              </div>
              <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                <p class="font-display text-2xl font-semibold text-ink-900"><?= $beneficiaries > 0 ? number_format( $beneficiaries ) : '—' ?></p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Beneficiaries</p>
              </div>
              <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                <p class="font-display text-2xl font-semibold text-ink-900"><?= htmlspecialchars( $yearLabel !== '' ? $yearLabel : '—' ) ?></p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Period</p>
              </div>
            </div>

            <a href="<?= $detailUrl ?>" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-ink-900 px-4 py-3 text-sm font-semibold text-white hover:bg-brandRed transition-colors">
              Read the full project report <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PHOTOGRAPHS -->
  <section class="py-14 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-8">
        <div>
          <span class="text-brandRed text-[10px] font-bold uppercase tracking-[0.2em]">Visual documentation</span>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold text-ink-900 mt-2">Inside this project</h2>
          <p class="text-sm text-slate-500 mt-2">Open any photograph to read what it shows and why it matters.</p>
        </div>
        <?php if ( $partners && !empty( $partners[0]['slug'] ) ): ?>
          <a href="<?= $base ?>news/partner.php?slug=<?= rawurlencode( (string) $partners[0]['slug'] ) ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brandRed transition-colors">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to <?= htmlspecialchars( (string) $partners[0]['name'] ) ?>
          </a>
        <?php endif; ?>
      </div>

      <?php if ( empty( $media ) ): ?>
        <div class="max-w-xl mx-auto text-center py-16 bg-white rounded-3xl border border-slate-200">
          <span class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4"><i class="fa-solid fa-camera-retro"></i></span>
          <h3 class="font-display text-xl font-semibold text-ink-900">No photographs published yet</h3>
          <p class="text-sm text-slate-500 mt-1">This project's gallery is being prepared. Please check back soon.</p>
          <a href="<?= $detailUrl ?>" class="inline-flex items-center gap-2 mt-5 bg-brandRed text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-brandRed-hover transition-colors">Open project details</a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-7" id="project-media-grid">
          <?php foreach ( $media as $index => $item ): ?>
            <?php
              $caption = trim( (string) ( $item['caption'] ?? '' ) );
              $context = trim( (string) ( $item['context'] ?? '' ) );
              if ( $caption === '' ) {
                  $caption = 'Photograph ' . ( $index + 1 );
              }
              if ( $context === '' ) {
                  $context = 'Documentation from ' . $project['title'] . '.';
              }
              $src = wrcMediaUrl( (string) $item['image_path'] );
            ?>
            <article class="project-media-card group bg-white rounded-3xl border border-slate-200 overflow-hidden hover:border-brandRed/30 hover:shadow-2xl hover:shadow-slate-900/10 transition-all duration-300">
              <button type="button" class="media-open relative block w-full text-left aspect-[4/3] overflow-hidden bg-slate-100" data-index="<?= $index ?>" data-src="<?= htmlspecialchars( $src, ENT_QUOTES, 'UTF-8' ) ?>" data-caption="<?= htmlspecialchars( $caption, ENT_QUOTES, 'UTF-8' ) ?>" data-context="<?= htmlspecialchars( $context, ENT_QUOTES, 'UTF-8' ) ?>" aria-label="Open <?= htmlspecialchars( $caption, ENT_QUOTES, 'UTF-8' ) ?>">
                <img src="<?= htmlspecialchars( $src, ENT_QUOTES, 'UTF-8' ) ?>" alt="<?= htmlspecialchars( $caption, ENT_QUOTES, 'UTF-8' ) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
                <span class="absolute inset-0 bg-gradient-to-t from-ink-900/50 via-transparent to-transparent opacity-70"></span>
                <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ink-900 shadow-sm">
                  <i class="fa-solid fa-camera-retro text-brandRed text-[9px]"></i><?= sprintf( '%02d', $index + 1 ) ?>
                </span>
                <span class="absolute right-4 bottom-4 w-10 h-10 rounded-full bg-white/15 border border-white/25 backdrop-blur flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity">
                  <i class="fa-solid fa-expand text-xs"></i>
                </span>
              </button>

              <div class="p-5 sm:p-6">
                <h3 class="font-display text-lg sm:text-xl font-semibold text-ink-900 leading-snug"><?= htmlspecialchars( $caption ) ?></h3>
                <p class="text-sm text-slate-500 leading-relaxed mt-2"><?= htmlspecialchars( $context ) ?></p>
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                  <span class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Photograph <?= sprintf( '%02d', $index + 1 ) ?> of <?= sprintf( '%02d', count( $media ) ) ?></span>
                  <button type="button" class="media-open inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-brandRed hover:text-brandRed-hover transition-colors" data-index="<?= $index ?>" data-src="<?= htmlspecialchars( $src, ENT_QUOTES, 'UTF-8' ) ?>" data-caption="<?= htmlspecialchars( $caption, ENT_QUOTES, 'UTF-8' ) ?>" data-context="<?= htmlspecialchars( $context, ENT_QUOTES, 'UTF-8' ) ?>">
                    View <i class="fa-solid fa-expand text-[9px]"></i>
                  </button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- CLOSING CTA -->
  <section class="pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
        <div>
          <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-brandRed">Continue exploring</span>
          <h2 class="font-display text-2xl font-semibold text-ink-900 mt-1">See more projects and photographs</h2>
          <p class="text-sm text-slate-500 mt-1">Browse the complete partner gallery or return to the project report.</p>
        </div>
        <div class="flex flex-wrap gap-3 shrink-0">
          <a href="<?= $base ?>news/gallery.php" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:border-brandRed/30 hover:text-brandRed transition-colors">
            <i class="fa-solid fa-images text-[11px]"></i> All galleries
          </a>
          <a href="<?= $detailUrl ?>" class="inline-flex items-center gap-2 rounded-xl bg-brandRed px-5 py-2.5 text-sm font-semibold text-white hover:bg-brandRed-hover transition-colors">
            Project report <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </a>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- LIGHTBOX -->
<div id="media-lightbox" class="fixed inset-0 z-[100] hidden bg-black/95 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Project photograph viewer">
  <button type="button" id="media-close" class="absolute top-5 right-5 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 border border-white/15 text-white flex items-center justify-center transition-colors" aria-label="Close viewer">
    <i class="fa-solid fa-xmark"></i>
  </button>
  <button type="button" id="media-prev" class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 border border-white/15 text-white flex items-center justify-center transition-colors" aria-label="Previous photograph">
    <i class="fa-solid fa-chevron-left"></i>
  </button>
  <button type="button" id="media-next" class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 border border-white/15 text-white flex items-center justify-center transition-colors" aria-label="Next photograph">
    <i class="fa-solid fa-chevron-right"></i>
  </button>

  <div class="h-full w-full max-w-6xl mx-auto p-4 sm:p-8 flex items-center justify-center">
    <div class="w-full max-h-[92vh] overflow-y-auto rounded-2xl overflow-hidden bg-ink-900 grid grid-cols-1 lg:grid-cols-[1fr_340px] shadow-2xl">
      <div class="flex items-center justify-center bg-black min-h-[45vh] lg:min-h-[70vh]">
        <img id="media-image" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="" class="max-h-[48vh] lg:max-h-[78vh] w-auto max-w-full object-contain">
      </div>
      <div class="p-5 sm:p-7 flex flex-col">
        <div class="flex items-center justify-between gap-3 mb-4">
          <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-brandRed">Photograph</span>
          <span id="media-counter" class="text-[11px] font-mono text-white/40"></span>
        </div>
        <h3 id="media-caption" class="font-display text-xl font-semibold text-white leading-snug"></h3>
        <p id="media-context" class="text-sm text-white/60 leading-relaxed mt-3"></p>
        <div class="mt-auto pt-6 border-t border-white/10">
          <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-white/30">Project</p>
          <p class="text-xs text-white/70 mt-1"><?= htmlspecialchars( (string) $project['title'] ) ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .project-media-card .media-open:focus-visible{outline:2px solid #ca0000;outline-offset:-2px}
  #media-lightbox.is-open{display:block}
  @media (max-width:1023px){#media-lightbox .grid{max-height:92vh}}
</style>

<script>
(function () {
  var triggers = Array.prototype.slice.call(document.querySelectorAll('.media-open'));
  var box = document.getElementById('media-lightbox');
  if (!triggers.length || !box) return;

  var image = document.getElementById('media-image');
  var caption = document.getElementById('media-caption');
  var context = document.getElementById('media-context');
  var counter = document.getElementById('media-counter');
  var current = 0;

  var slides = triggers.filter(function (trigger) {
    return trigger.classList.contains('media-open') && trigger.getAttribute('data-index') !== null;
  }).reduce(function (list, trigger) {
    var index = parseInt(trigger.getAttribute('data-index'), 10);
    if (isNaN(index) || list[index]) return list;
    list[index] = {
      src: trigger.getAttribute('data-src') || '',
      caption: trigger.getAttribute('data-caption') || '',
      context: trigger.getAttribute('data-context') || ''
    };
    return list;
  }, []).filter(Boolean);

  if (!slides.length) return;

  function render(index) {
    current = (index + slides.length) % slides.length;
    var slide = slides[current];
    image.src = slide.src;
    image.alt = slide.caption;
    caption.textContent = slide.caption;
    context.textContent = slide.context;
    counter.textContent = (current + 1) + ' / ' + slides.length;
  }

  function open(index) {
    render(index);
    box.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    box.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      open(parseInt(this.getAttribute('data-index'), 10) || 0);
    });
  });

  document.getElementById('media-close').addEventListener('click', close);
  document.getElementById('media-prev').addEventListener('click', function () { render(current - 1); });
  document.getElementById('media-next').addEventListener('click', function () { render(current + 1); });
  box.addEventListener('click', function (event) { if (event.target === box) close(); });

  document.addEventListener('keydown', function (event) {
    if (!box.classList.contains('is-open')) return;
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowLeft') render(current - 1);
    if (event.key === 'ArrowRight') render(current + 1);
  });

  var touchX = 0;
  box.addEventListener('touchstart', function (event) { touchX = event.touches[0].clientX; }, { passive: true });
  box.addEventListener('touchend', function (event) {
    var diff = touchX - event.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) { diff > 0 ? render(current + 1) : render(current - 1); }
  }, { passive: true });
})();
</script>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
