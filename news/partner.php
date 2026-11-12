<?php
/**
 * WRC - One partner's project galleries.
 *
 * Shows every project linked to the partner selected on news/gallery.php.
 * Each card links to the project gallery with its photographs and context.
 */
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base  = siteBaseUrl();
$slug  = trim( (string) ( $_GET['slug'] ?? '' ) );
$partner = $slug !== '' ? wrcPartnerBySlug( $slug ) : null;

if ( !$partner ) {
    header( 'Location: ' . $base . 'news/gallery.php' );
    exit;
}

$projects = wrcPartnerProjects( (int) $partner['id'] );
if ( empty( $projects ) ) {
    // A partner with no linked project has no gallery to show. Hidden gallery
    // cards must not leave an empty page reachable by a direct URL.
    header( 'Location: ' . $base . 'news/gallery.php' );
    exit;
}
$cover    = wrcPartnerCoverPath( $partner, $projects );
$info     = wrcPartnerTypeInfo( (string) ( $partner['partner_type'] ?? 'collaborator' ) );

$sectors   = [];
$districts = [];
$years     = [];
$beneficiaries = 0;
foreach ( $projects as $project ) {
    if ( !empty( $project['category'] ) ) {
        $sectors[ (string) $project['category'] ] = true;
    }
    $place = trim( (string) ( $project['district'] ?? '' ) );
    if ( $place !== '' ) {
        $districts[ $place ] = true;
    }
    $yearLabel = wrcProjectYearLabel( $project );
    if ( $yearLabel !== '' ) {
        foreach ( preg_split( '/[–-]/', $yearLabel ) as $year ) {
            $years[] = (int) $year;
        }
    }
    $beneficiaries += (int) ( $project['beneficiaries_count'] ?? 0 );
}

$yearRange = '';
if ( $years ) {
    $min = min( $years );
    $max = max( $years );
    $yearRange = $min === $max ? (string) $min : $min . '–' . $max;
}

$logo    = trim( (string) ( $partner['logo_path'] ?? '' ) );
$website = trim( (string) ( $partner['website_url'] ?? '' ) );
$description = trim( (string) ( $partner['description'] ?? '' ) );
if ( $description === '' ) {
    $description = 'A valued WRC partner supporting humanitarian work across Pakistan.';
}

$typeStyles = [
    'funding'        => 'bg-amber-50 text-amber-700 border-amber-200',
    'government'     => 'bg-blue-50 text-blue-700 border-blue-200',
    'implementation' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'collaborator'   => 'bg-purple-50 text-purple-700 border-purple-200',
];
$typeChip = $typeStyles[ (string) $partner['partner_type'] ] ?? $typeStyles['collaborator'];

$pageTitle       = $partner['name'] . ' — Project Galleries | WRC';
$metaDescription = mb_substr( strip_tags( $description ), 0, 155 );

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class="flex-grow bg-slate-50">

  <!-- PARTNER HEADER -->
  <section class="relative overflow-hidden bg-white border-b border-slate-200">
    <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-brandRed/5 blur-3xl pointer-events-none"></div>
    <div class="absolute left-1/2 top-0 h-full w-px bg-slate-100 hidden lg:block pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
      <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 mb-8" aria-label="Breadcrumb">
        <a href="<?= $base ?>index.php" class="hover:text-brandRed transition-colors">Home</a>
        <i class="fa-solid fa-chevron-right text-[7px]"></i>
        <a href="<?= $base ?>news/index.php" class="hover:text-brandRed transition-colors">News &amp; Media</a>
        <i class="fa-solid fa-chevron-right text-[7px]"></i>
        <a href="<?= $base ?>news/gallery.php" class="hover:text-brandRed transition-colors">Photo Galleries</a>
        <i class="fa-solid fa-chevron-right text-[7px]"></i>
        <span class="text-slate-700 normal-case tracking-normal"><?= htmlspecialchars( $partner['name'] ) ?></span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
        <div class="lg:col-span-7">
          <span class="inline-flex items-center gap-2 rounded-full border <?= $typeChip ?> px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] mb-5">
            <i class="fa-solid <?= htmlspecialchars( $info['icon'] ) ?> text-[9px]"></i><?= htmlspecialchars( $info['short'] ) ?> Partner
          </span>
          <h1 class="font-display font-semibold text-4xl sm:text-5xl lg:text-[3.5rem] leading-[1.05] text-ink-900">
            <?= htmlspecialchars( $partner['name'] ) ?>
          </h1>
          <p class="mt-5 text-base sm:text-lg text-slate-500 leading-relaxed max-w-2xl"><?= htmlspecialchars( $description ) ?></p>

          <div class="mt-7 flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600">
              <i class="fa-solid fa-folder-open text-brandRed"></i> <?= (int) $partner['project_count'] ?> project<?= (int) $partner['project_count'] === 1 ? '' : 's' ?>
            </span>
            <span class="inline-flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600">
              <i class="fa-solid fa-camera-retro text-brandRed"></i> <?= (int) $partner['photo_count'] ?> photograph<?= (int) $partner['photo_count'] === 1 ? '' : 's' ?>
            </span>
            <?php if ( $yearRange !== '' ): ?>
              <span class="inline-flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600">
                <i class="fa-solid fa-calendar-days text-brandRed"></i> <?= htmlspecialchars( $yearRange ) ?>
              </span>
            <?php endif; ?>
            <?php if ( $website !== '' ): ?>
              <a href="<?= htmlspecialchars( $website, ENT_QUOTES, 'UTF-8' ) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-ink-900 px-3.5 py-2 text-xs font-semibold text-white hover:bg-brandRed transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Website
              </a>
            <?php endif; ?>
          </div>
        </div>

        <div class="lg:col-span-5">
          <div class="relative aspect-[4/3] rounded-3xl overflow-hidden bg-ink-900 shadow-2xl shadow-slate-900/15">
            <?php if ( $cover !== '' ): ?>
              <img src="<?= $base ?><?= htmlspecialchars( $cover, ENT_QUOTES, 'UTF-8' ) ?>" alt="<?= htmlspecialchars( $partner['name'] ) ?> project photograph" class="absolute inset-0 w-full h-full object-cover">
            <?php else: ?>
              <div class="absolute inset-0 bg-gradient-to-br from-ink-800 to-black"></div>
              <span class="absolute inset-0 flex items-center justify-center font-display font-semibold text-[9rem] text-white/10"><?= htmlspecialchars( $partner['initials'] ) ?></span>
            <?php endif; ?>
            <div class="absolute inset-0 bg-gradient-to-t from-ink-900/85 via-transparent to-transparent"></div>

            <div class="absolute left-5 right-5 bottom-5 flex items-center gap-3">
              <div class="w-14 h-14 rounded-2xl bg-white shadow-xl p-2 flex items-center justify-center overflow-hidden shrink-0">
                <?php if ( $logo !== '' && imageExists( $logo ) ): ?>
                  <img src="<?= $base ?><?= htmlspecialchars( $logo, ENT_QUOTES, 'UTF-8' ) ?>" alt="<?= htmlspecialchars( $partner['name'] ) ?> logo" class="w-full h-full object-contain">
                <?php else: ?>
                  <span class="font-display font-semibold text-base text-ink-900"><?= htmlspecialchars( $partner['initials'] ) ?></span>
                <?php endif; ?>
              </div>
              <div class="min-w-0">
                <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-white/60">Partner since</p>
                <p class="text-white font-semibold text-sm"><?= htmlspecialchars( $yearRange !== '' ? $yearRange : (string) date( 'Y' ) ) ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- STAT STRIP -->
      <div class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-px rounded-2xl overflow-hidden border border-slate-200 bg-slate-200">
        <div class="bg-white px-5 py-4">
          <p class="font-display text-2xl font-semibold text-ink-900"><?= number_format( (int) $partner['project_count'] ) ?></p>
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Projects</p>
        </div>
        <div class="bg-white px-5 py-4">
          <p class="font-display text-2xl font-semibold text-ink-900"><?= number_format( (int) $partner['photo_count'] ) ?></p>
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Photographs</p>
        </div>
        <div class="bg-white px-5 py-4">
          <p class="font-display text-2xl font-semibold text-ink-900"><?= htmlspecialchars( (string) count( $sectors ) ) ?></p>
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Sectors covered</p>
        </div>
        <div class="bg-white px-5 py-4">
          <p class="font-display text-2xl font-semibold text-ink-900"><?= number_format( $beneficiaries ) ?></p>
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Beneficiaries reached</p>
        </div>
      </div>
    </div>
  </section>

  <!-- PROJECTS -->
  <section class="py-14 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-8">
        <div>
          <span class="text-brandRed text-[10px] font-bold uppercase tracking-[0.2em]">Project archive</span>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold text-ink-900 mt-2">Delivered with <?= htmlspecialchars( $partner['name'] ) ?></h2>
          <p class="text-sm text-slate-500 mt-2">Choose a project to open its photographs and the context behind each image.</p>
        </div>
        <a href="<?= $base ?>news/gallery.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brandRed transition-colors">
          <i class="fa-solid fa-arrow-left text-[10px]"></i> All partners
        </a>
      </div>

      <?php if ( empty( $projects ) ): ?>
        <div class="text-center py-16 bg-white rounded-3xl border border-slate-200">
          <span class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4"><i class="fa-solid fa-folder-open"></i></span>
          <h3 class="font-display text-xl font-semibold text-ink-900">No projects linked yet</h3>
          <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Projects delivered with this partner will appear here once they are linked from the dashboard.</p>
          <a href="<?= $base ?>news/gallery.php" class="inline-flex items-center gap-2 mt-5 bg-brandRed text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-brandRed-hover transition-colors">Back to galleries</a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <?php foreach ( $projects as $project ): ?>
            <?php
              $status      = wrcProjectStatusMeta( (string) ( $project['status'] ?? '' ) );
              $projectUrl  = $base . 'news/project-gallery.php?slug=' . rawurlencode( (string) $project['slug'] );
              $hero        = trim( (string) ( $project['hero_image'] ?? '' ) );
              $yearLabel   = wrcProjectYearLabel( $project );
              $mediaCount  = (int) ( $project['media_count'] ?? 0 );
              $place       = trim( (string) ( $project['district'] ?? '' ) );
              if ( $place === '' ) {
                  $place = trim( (string) ( $project['location_details'] ?? '' ) );
              }
              $summary     = trim( (string) ( $project['overview'] ?? '' ) );
              if ( $summary === '' ) {
                  $summary = trim( (string) ( $project['description'] ?? '' ) );
              }
            ?>
            <a href="<?= $projectUrl ?>" class="group flex flex-col bg-white rounded-3xl border border-slate-200 overflow-hidden hover:border-brandRed/30 hover:shadow-2xl hover:shadow-slate-900/10 transition-all duration-300">
              <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
                <img src="<?= wrcMediaUrl( $hero ) ?>" alt="<?= htmlspecialchars( (string) $project['title'] ) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-900/80 via-ink-900/10 to-transparent"></div>

                <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                  <span class="inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ink-900 shadow-sm">
                    <i class="fa-solid fa-tag text-brandRed text-[9px]"></i><?= htmlspecialchars( (string) ( $project['category'] ?? 'Project' ) ) ?>
                  </span>
                  <span class="inline-flex items-center gap-1.5 rounded-full border <?= $status['classes'] ?> bg-white/95 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full <?= $status['dot'] ?>"></span><?= htmlspecialchars( $status['label'] ) ?>
                  </span>
                </div>

                <div class="absolute left-4 right-4 bottom-4 flex items-end justify-between gap-3">
                  <div class="min-w-0">
                    <?php if ( $yearLabel !== '' ): ?>
                      <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-white/60 mb-1"><?= htmlspecialchars( $yearLabel ) ?></p>
                    <?php endif; ?>
                    <h3 class="font-display text-xl sm:text-2xl font-semibold text-white leading-snug line-clamp-2"><?= htmlspecialchars( (string) $project['title'] ) ?></h3>
                  </div>
                  <span class="w-10 h-10 rounded-full bg-white/15 border border-white/20 backdrop-blur flex items-center justify-center text-white shrink-0 group-hover:bg-brandRed group-hover:border-brandRed transition-colors">
                    <i class="fa-solid fa-images text-xs"></i>
                  </span>
                </div>
              </div>

              <div class="p-5 sm:p-6 flex flex-col flex-1">
                <p class="text-sm text-slate-500 leading-relaxed line-clamp-2"><?= htmlspecialchars( mb_substr( strip_tags( $summary ), 0, 180 ) ) ?></p>

                <div class="mt-5 grid grid-cols-2 gap-3 text-xs">
                  <?php if ( $place !== '' ): ?>
                    <span class="flex items-start gap-2 text-slate-500"><i class="fa-solid fa-location-dot text-brandRed/70 mt-0.5"></i><span class="line-clamp-1"><?= htmlspecialchars( $place ) ?></span></span>
                  <?php endif; ?>
                  <span class="flex items-start gap-2 text-slate-500"><i class="fa-solid fa-camera-retro text-brandRed/70 mt-0.5"></i><span><strong class="text-ink-900"><?= number_format( $mediaCount ) ?></strong> photograph<?= $mediaCount === 1 ? '' : 's' ?></span></span>
                </div>

                <div class="mt-auto pt-5 flex items-center justify-between border-t border-slate-100 mt-5">
                  <span class="text-xs font-bold uppercase tracking-wider text-brandRed">View project gallery</span>
                  <i class="fa-solid fa-arrow-right text-brandRed text-xs group-hover:translate-x-1 transition-transform"></i>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php
    // A small, useful cross-link strip: three other partners with galleries.
    $others = [];
    foreach ( wrcGalleryPartners() as $other ) {
        if ( (int) $other['id'] === (int) $partner['id'] || (int) $other['project_count'] < 1 ) {
            continue;
        }
        $others[] = $other;
        if ( count( $others ) >= 3 ) {
            break;
        }
    }
  ?>
  <?php if ( $others ): ?>
    <section class="pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8">
          <div class="flex items-center justify-between gap-4 mb-5">
            <div>
              <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-brandRed">Keep exploring</span>
              <h2 class="font-display text-2xl font-semibold text-ink-900 mt-1">More partner galleries</h2>
            </div>
            <a href="<?= $base ?>news/gallery.php" class="hidden sm:inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brandRed transition-colors">View all <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <?php foreach ( $others as $other ): ?>
              <?php $otherCover = (string) ( $other['cover_path'] ?? '' ); ?>
              <a href="<?= $base ?>news/partner.php?slug=<?= rawurlencode( (string) $other['slug'] ) ?>" class="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3 hover:border-brandRed/30 hover:bg-white transition-colors">
                <span class="relative w-14 h-14 rounded-xl overflow-hidden bg-ink-900 shrink-0">
                  <?php if ( $otherCover !== '' && imageExists( $otherCover ) ): ?>
                    <img src="<?= $base ?><?= htmlspecialchars( $otherCover, ENT_QUOTES, 'UTF-8' ) ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                  <?php else: ?>
                    <span class="absolute inset-0 flex items-center justify-center font-display font-semibold text-white/70 text-sm"><?= htmlspecialchars( $other['initials'] ) ?></span>
                  <?php endif; ?>
                </span>
                <span class="min-w-0">
                  <span class="block text-sm font-semibold text-ink-900 truncate group-hover:text-brandRed transition-colors"><?= htmlspecialchars( $other['name'] ) ?></span>
                  <span class="block text-[11px] text-slate-400 mt-0.5"><?= (int) $other['project_count'] ?> project<?= (int) $other['project_count'] === 1 ? '' : 's' ?> &middot; <?= (int) $other['photo_count'] ?> photos</span>
                </span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>
</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
