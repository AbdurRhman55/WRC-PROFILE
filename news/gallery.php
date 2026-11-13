<?php
/**
 * WRC - Partner & Donor Photo Galleries
 *
 * Landing page for the media flow:
 *   partner category cards -> partner projects -> project photographs.
 *
 * Everything is read from partner_projects / project_media, which the
 * dashboard maintains. No image path or partner name is hard-coded here.
 */
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();

$partners = wrcGalleryPartners();

// Group cards by category so the page reads as a curated directory rather
// than one long, undifferentiated wall.
$typeOrder = [ 'funding', 'government', 'implementation', 'collaborator' ];
$grouped   = [];
foreach ( $partners as $partner ) {
    $type = (string) ( $partner['partner_type'] ?? 'collaborator' );
    if ( !isset( wrcPartnerTypeMap()[ $type ] ) ) {
        $type = 'collaborator';
    }
    $grouped[ $type ][] = $partner;
}

$totalProjects = 0;
$totalPhotos   = 0;
foreach ( $partners as $partner ) {
    $totalProjects += (int) $partner['project_count'];
    $totalPhotos   += (int) $partner['photo_count'];
}

$heroImage = 'assets/images/projects/health-services.jpg';
foreach ( $partners as $partner ) {
    if ( !empty( $partner['cover_path'] ) && imageExists( (string) $partner['cover_path'] ) ) {
        $heroImage = (string) $partner['cover_path'];
        break;
    }
}

$typeStyles = [
    'funding'        => [ 'chip' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => 'bg-amber-50 text-amber-600', 'bar' => 'bg-amber-400' ],
    'government'     => [ 'chip' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'bg-blue-50 text-blue-600', 'bar' => 'bg-blue-400' ],
    'implementation' => [ 'chip' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => 'bg-emerald-50 text-emerald-600', 'bar' => 'bg-emerald-400' ],
    'collaborator'   => [ 'chip' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'bg-purple-50 text-purple-600', 'bar' => 'bg-purple-400' ],
];

$pageTitle       = 'Partner & Donor Photo Galleries | WRC';
$metaDescription = 'Explore WRC project photographs by partner and donor. Browse the projects each partner supported, then view field images with full context.';

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class="flex-grow bg-slate-50">

  <!-- HERO -->
  <section class="relative isolate overflow-hidden bg-ink-900 text-white">
    <img src="<?= $base ?><?= htmlspecialchars( $heroImage, ENT_QUOTES, 'UTF-8' ) ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-40" fetchpriority="high">
    <div class="absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/85 to-ink-900/35"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-24 sm:pt-14 sm:pb-28">
      <nav class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/50 mb-8" aria-label="Breadcrumb">
        <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
        <i class="fa-solid fa-chevron-right text-[7px]"></i>
        <a href="<?= $base ?>news/index.php" class="hover:text-white transition-colors">News &amp; Media</a>
        <i class="fa-solid fa-chevron-right text-[7px]"></i>
        <span class="text-white/90">Photo Galleries</span>
      </nav>

      <div class="max-w-3xl">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-[0.2em] bg-brandRed text-white mb-5">
          <i class="fa-solid fa-images"></i> Visual Archive
        </span>
        <h1 class="font-display font-semibold text-4xl sm:text-5xl lg:text-6xl leading-[1.08] mb-5">
          Partner &amp; Donor<br><span class="text-white/70">Photo Galleries</span>
        </h1>
        <p class="text-base sm:text-lg text-white/70 leading-relaxed max-w-2xl">
          Every WRC project is delivered with a partner. Choose a partner below to explore the projects you helped fund, then open any project to see its photographs and the story behind each image.
        </p>
      </div>
    </div>
  </section>

  <!-- SUMMARY / FILTER BAR -->
  <section class="relative z-10 -mt-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-900/5 p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center gap-5 lg:gap-8">
          <div class="flex items-center gap-3 shrink-0">
            <span class="w-11 h-11 rounded-2xl bg-brandRed/10 text-brandRed flex items-center justify-center"><i class="fa-solid fa-layer-group"></i></span>
            <div>
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Browse by partner</p>
              <p class="text-sm font-semibold text-ink-900"><?= count( $partners ) ?> active partners &amp; donors</p>
            </div>
          </div>

          <div class="flex flex-wrap gap-2 lg:ml-auto" id="partner-filters">
            <button type="button" class="partner-filter active inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold transition-all" data-filter="all">
              All <span class="rounded-full bg-white/20 px-1.5 py-0.5 text-[10px] leading-none"><?= count( $partners ) ?></span>
            </button>
            <?php foreach ( $typeOrder as $type ): ?>
              <?php if ( empty( $grouped[ $type ] ) ) continue; ?>
              <?php $info = wrcPartnerTypeInfo( $type ); ?>
              <button type="button" class="partner-filter inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold transition-all" data-filter="<?= htmlspecialchars( $type ) ?>">
                <i class="fa-solid <?= htmlspecialchars( $info['icon'] ) ?> text-[10px]"></i>
                <?= htmlspecialchars( $info['short'] ) ?>
                <span class="rounded-full bg-white/20 px-1.5 py-0.5 text-[10px] leading-none"><?= count( $grouped[ $type ] ) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-5 pt-5 border-t border-slate-100 grid grid-cols-3 gap-3">
          <div class="text-center sm:text-left">
            <p class="font-display text-2xl sm:text-3xl font-semibold text-ink-900"><?= number_format( count( $partners ) ) ?></p>
            <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400">Partners</p>
          </div>
          <div class="text-center sm:text-left">
            <p class="font-display text-2xl sm:text-3xl font-semibold text-ink-900"><?= number_format( $totalProjects ) ?></p>
            <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400">Project links</p>
          </div>
          <div class="text-center sm:text-left">
            <p class="font-display text-2xl sm:text-3xl font-semibold text-ink-900"><?= number_format( $totalPhotos ) ?></p>
            <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-slate-400">Photographs</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PARTNER CATEGORIES -->
  <section class="py-14 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <?php if ( empty( $partners ) ): ?>
        <div class="max-w-xl mx-auto text-center py-20 bg-white rounded-3xl border border-slate-200">
          <span class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-5"><i class="fa-solid fa-images text-xl"></i></span>
          <h2 class="font-display text-2xl font-semibold text-ink-900 mb-2">Galleries are being prepared</h2>
          <p class="text-sm text-slate-500 mb-6">Partner projects and photographs will appear here as soon as they are published from the dashboard.</p>
          <a href="<?= $base ?>news/index.php" class="inline-flex items-center gap-2 bg-brandRed text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-brandRed-hover transition-colors">News &amp; Media <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
        </div>
      <?php else: ?>

        <?php foreach ( $typeOrder as $type ): ?>
          <?php if ( empty( $grouped[ $type ] ) ) continue; ?>
          <?php
            $info  = wrcPartnerTypeInfo( $type );
            $style = $typeStyles[ $type ] ?? $typeStyles['collaborator'];
            $count = count( $grouped[ $type ] );
          ?>
          <div class="partner-category mb-14 last:mb-0" data-category="<?= htmlspecialchars( $type ) ?>">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">
              <div class="flex items-start gap-4">
                <span class="w-12 h-12 rounded-2xl <?= $style['icon'] ?> flex items-center justify-center shrink-0 shadow-sm">
                  <i class="fa-solid <?= htmlspecialchars( $info['icon'] ) ?>"></i>
                </span>
                <div>
                  <div class="flex items-center gap-2.5">
                    <h2 class="font-display text-2xl sm:text-3xl font-semibold text-ink-900"><?= htmlspecialchars( $info['label'] ) ?></h2>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border <?= $style['chip'] ?>"><?= $count ?></span>
                  </div>
                  <p class="text-sm text-slate-500 mt-1 max-w-2xl"><?= htmlspecialchars( $info['blurb'] ) ?></p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              <?php foreach ( $grouped[ $type ] as $partner ): ?>
                <?php
                  $cover   = (string) ( $partner['cover_path'] ?? '' );
                  $logo    = trim( (string) ( $partner['logo_path'] ?? '' ) );
                  $name    = (string) $partner['name'];
                  $desc    = trim( (string) ( $partner['description'] ?? '' ) );
                  if ( $desc === '' ) {
                      $desc = 'A valued WRC partner supporting humanitarian work across Pakistan.';
                  }
                  $pCount  = (int) $partner['project_count'];
                  $phCount = (int) $partner['photo_count'];
                  $partnerUrl = $base . 'news/partner.php?slug=' . rawurlencode( (string) $partner['slug'] );
                ?>
                <a href="<?= $partnerUrl ?>" class="partner-card group relative flex flex-col bg-white rounded-3xl border border-slate-200 overflow-hidden hover:border-brandRed/30 hover:shadow-2xl hover:shadow-slate-900/10 hover:-translate-y-1 transition-all duration-300" data-type="<?= htmlspecialchars( $type ) ?>">
                  <div class="relative aspect-[16/10] overflow-hidden bg-ink-900">
                    <?php if ( $cover !== '' && imageExists( $cover ) ): ?>
                      <img src="<?= $base ?><?= htmlspecialchars( $cover, ENT_QUOTES, 'UTF-8' ) ?>" alt="<?= htmlspecialchars( $name ) ?> project photograph" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
                    <?php else: ?>
                      <div class="absolute inset-0 bg-gradient-to-br from-ink-800 via-ink-900 to-black"></div>
                      <span class="absolute -right-4 -bottom-8 font-display font-semibold text-[9rem] leading-none text-white/5 select-none"><?= htmlspecialchars( $partner['initials'] ) ?></span>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-ink-900/25 to-transparent"></div>

                    <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full border <?= $style['chip'] ?> bg-white/95 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider shadow-sm">
                      <i class="fa-solid <?= htmlspecialchars( $info['icon'] ) ?> text-[9px]"></i><?= htmlspecialchars( $info['short'] ) ?>
                    </span>

                    <div class="absolute left-4 right-4 bottom-4 flex items-end justify-between gap-3">
                      <div class="flex items-center gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-white shadow-lg p-1.5 shrink-0 flex items-center justify-center overflow-hidden">
                          <?php if ( $logo !== '' && imageExists( $logo ) ): ?>
                            <img src="<?= $base ?><?= htmlspecialchars( $logo, ENT_QUOTES, 'UTF-8' ) ?>" alt="" class="w-full h-full object-contain">
                          <?php else: ?>
                            <span class="font-display font-semibold text-sm text-ink-900"><?= htmlspecialchars( $partner['initials'] ) ?></span>
                          <?php endif; ?>
                        </div>
                        <div class="min-w-0">
                          <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-white/60 mb-0.5">Partner</p>
                          <h3 class="text-white font-display font-semibold text-lg leading-tight truncate"><?= htmlspecialchars( $name ) ?></h3>
                        </div>
                      </div>
                      <span class="w-9 h-9 rounded-full bg-white/15 border border-white/20 backdrop-blur flex items-center justify-center text-white shrink-0 group-hover:bg-brandRed group-hover:border-brandRed transition-colors">
                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                      </span>
                    </div>
                  </div>

                  <div class="p-5 flex flex-col flex-1">
                    <p class="text-sm text-slate-500 leading-relaxed line-clamp-2"><?= htmlspecialchars( $desc ) ?></p>
                    <div class="mt-auto pt-4 flex items-center justify-between gap-3 text-[11px] font-semibold text-slate-400">
                      <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-folder-open text-brandRed/70"></i><strong class="text-ink-900"><?= number_format( $pCount ) ?></strong> project<?= $pCount === 1 ? '' : 's' ?></span>
                      <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-camera-retro text-brandRed/70"></i><strong class="text-ink-900"><?= number_format( $phCount ) ?></strong> photo<?= $phCount === 1 ? '' : 's' ?></span>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>
  </section>

  <!-- BACK TO NEWS -->
  <section class="pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <a href="<?= $base ?>news/index.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brandRed transition-colors">
          <i class="fa-solid fa-arrow-left text-[10px]"></i> Back to News &amp; Media
        </a>
      </div>
    </div>
  </section>
</main>

<style>
  .partner-filter{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0}
  .partner-filter:hover{background:#e2e8f0;color:#0f172a}
  .partner-filter.active{background:#ca0000;color:#fff;border-color:#ca0000;box-shadow:0 8px 18px -8px rgba(202,0,0,.55)}
</style>

<script>
(function () {
  var filters = document.querySelectorAll('.partner-filter');
  var sections = document.querySelectorAll('.partner-category');
  var cards = document.querySelectorAll('.partner-card');

  filters.forEach(function (button) {
    button.addEventListener('click', function () {
      var filter = this.getAttribute('data-filter');

      filters.forEach(function (item) { item.classList.remove('active'); });
      this.classList.add('active');

      sections.forEach(function (section) {
        var match = filter === 'all' || section.getAttribute('data-category') === filter;
        section.style.display = match ? '' : 'none';
        if (match && filter !== 'all') {
          section.animate(
            [{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'translateY(0)' }],
            { duration: 260, easing: 'ease-out' }
          );
        }
      });

      cards.forEach(function (card) {
        card.style.display = filter === 'all' || card.getAttribute('data-type') === filter ? '' : 'none';
      });
    });
  });
})();
</script>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
