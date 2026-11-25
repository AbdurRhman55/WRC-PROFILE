<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Leadership | WRC About';
$metaDescription = 'Meet the leadership of Welfare & Relief Committee (WRC) — our chairman and the governance board guiding the organisation.';

// Fetch homepage sections
$homepageSections = getRows( 'homepage_sections', [], 'sort_order ASC' );
$leaderSections = [];
foreach ( $homepageSections as $section ) {
    if ( in_array( $section[ 'section_key' ], [ 'leadership_hero', 'chairman_message' ] ) ) {
        $leaderSections[ $section[ 'section_key' ] ] = $section;
        $leaderSections[ $section[ 'section_key' ] ][ 'parsed_content' ] = json_decode( $section[ 'content' ] ?? '{}', true );
    }
}

$hero = $leaderSections[ 'leadership_hero' ] ?? null;
$heroData = $hero[ 'parsed_content' ] ?? [];
$chairman = $leaderSections[ 'chairman_message' ] ?? null;
$chairmanData = $chairman[ 'parsed_content' ] ?? [];

// Read the complete chairman section managed under Pages > Homepage.
// The dedicated table remains a fallback for existing/migrated installations.
$chairmanRow = getRow( 'chairman_message' );
if ( $chairmanRow ) {
  $chairmanData = array_merge(
    is_array( $chairmanData ) ? $chairmanData : [],
    array_filter( $chairmanRow, static function ( $value ) {
      return $value !== null && $value !== '';
    } )
  );
}
if ( empty( $chairmanData[ 'person_name' ] ) ) {
  $chairmanData[ 'person_name' ] = $chairmanData[ 'name' ] ?? '';
}
if ( empty( $chairmanData[ 'person_title' ] ) ) {
  $chairmanData[ 'person_title' ] = $chairmanData[ 'title' ] ?? '';
}

// Governance & executive designations only. The band below deliberately shows
// roles, never personal names or photos.
$governanceRoles = getRows(
    'team_members',
    "is_active = 1 AND role_type IN ('chairman', 'board', 'executive', 'leadership') AND title != '' ORDER BY sort_order ASC, id ASC"
);
if ( empty( $governanceRoles ) ) {
    $governanceRoles = [
        [ 'title' => $chairmanData['person_title'] ?: 'Chairman of the Board' ],
    ];
}

include dirname( __DIR__ ) . '/includes/header.php';
?>


<main class="flex-grow">

<!-- Hero Section -->
<section class="relative w-full bg-ink-900 text-white min-h-[520px] flex items-center overflow-hidden">
  <div class="absolute inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/80 to-ink-900/40"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-transparent to-ink-900/50"></div>
  </div>

  <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-12 lg:py-12">
    <div class="max-w-7xl mx-auto">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm text-white/60 mb-8" aria-label="Breadcrumb">
        <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a href="<?= $base ?>about/index.php" class="hover:text-white transition-colors">About Us</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-white font-medium">Leadership</span>
      </nav>

      <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-6 max-w-3xl">
        <?= ( !empty( $hero[ 'section_title' ] ) ) ? sanitize( $hero[ 'section_title' ] ) : ( !empty( $heroData[ 'section_title' ] ) ? sanitize( $heroData[ 'section_title' ] ) : 'Our Leadership' ) ?>
      </h1>
      <p class="text-lg md:text-xl text-sand-50/80 max-w-2xl mb-10 leading-relaxed">
        <?= ( !empty( $hero[ 'section_subtitle' ] ) ) ? sanitize( $hero[ 'section_subtitle' ] ) : ( !empty( $heroData[ 'subtitle' ] ) ? sanitize( $heroData[ 'subtitle' ] ) : ( !empty( $heroData[ 'section_subtitle' ] ) ? sanitize( $heroData[ 'section_subtitle' ] ) : 'Meet the dedicated leaders guiding WRC\'s mission to deliver impactful humanitarian aid worldwide.' ) ) ?>
      </p>
    </div>
  </div>
</section>

<!-- 1. Chairman's Message (FINALIZED & UNCHANGED) -->
<?php
$message = !empty( $chairmanData[ 'paragraph1' ] )
  ? $chairmanData[ 'paragraph1' ]
  : ( $chairmanData[ 'message' ] ?? '' );
$message2 = $chairmanData[ 'paragraph2' ] ?? '';
?>
<section class="py-12 bg-white relative overflow-hidden" id="chairmans-message">
  <!-- Background glow effect -->
  <div class="absolute -top-24 -left-24 w-96 h-96 bg-brandRed/5 rounded-full blur-3xl pointer-events-none"></div>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-ink-900 text-white rounded-3xl p-8 md:p-14 shadow-2xl relative overflow-hidden border border-slate-800">
      <div class="absolute -right-16 -bottom-16 opacity-5 text-white pointer-events-none">
        <i class="fa-solid fa-quote-right text-[240px]"></i>
      </div>

      <div class="flex items-center gap-3 mb-6">
        <span class="px-3.5 py-1 rounded-full bg-brandRed/20 text-brandRed text-xs font-bold uppercase tracking-widest border border-brandRed/30">
          Leadership Spotlight
        </span>
      </div>

      <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8 mb-10 pb-8 border-b border-white/10">
        <div>
          <h2 class="text-3xl md:text-4xl font-display font-bold text-white leading-tight">
            <?= ( !empty( $chairman[ 'section_title' ] ) ) ? sanitize( $chairman[ 'section_title' ] ) : 'Message from the Chairman' ?>
          </h2>
          <p class="text-slate-400 text-sm md:text-base mt-2">
            Guiding principles and vision from WRC leadership
          </p>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/15">
          <i class="fa-solid fa-quote-left text-2xl text-brandRed"></i>
        </div>
      </div>

      <div class="space-y-6 mb-6 text-slate-300 leading-relaxed text-base md:text-lg font-normal">
        <?php if ( !empty( $message ) ): ?>
          <p><?= sanitize( $message ) ?></p>
        <?php endif; ?>
        <?php if ( !empty( $message2 ) ): ?>
          <p><?= sanitize( $message2 ) ?></p>
        <?php endif; ?>
        <?php if ( empty( $message ) && empty( $message2 ) ): ?>
          <p>
            WRC is committed to serving vulnerable communities with compassion, integrity, and transparency. Our leadership team works tirelessly to ensure every program delivers meaningful, measurable, and lasting impact.
          </p>
        <?php endif; ?>
      </div>

      <div class="pt-6 flex items-center gap-5">
        <?php
        $chairPhoto = !empty( $chairmanData[ 'image_path' ] )
          ? $chairmanData[ 'image_path' ]
          : ( $chairmanData[ 'hero_image' ] ?? '' );
        $chairPhoto = trim( (string) $chairPhoto );
        $chairName = $chairmanData[ 'person_name' ] ?? '';
        $chairTitle = $chairmanData[ 'person_title' ] ?? '';
        ?>
        <?php if ( !empty( $chairPhoto ) ): ?>
          <img src="<?= sanitize( getImageUrl( $chairPhoto, 'assets/images/chairman.svg' ) ) ?>" alt="<?= sanitize($chairName ?: 'Chairman') ?>" class="w-16 h-16 rounded-full object-cover border-2 border-brandRed shadow-xl">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full bg-brandRed/20 border-2 border-brandRed flex items-center justify-center shrink-0 shadow-xl">
            <i class="fa-solid fa-user-tie text-2xl text-white"></i>
          </div>
        <?php endif; ?>
        <div>
          <p class="font-bold text-white text-lg tracking-wide">
            <?= sanitize( $chairName ?: 'Mr. Asad Shah' ) ?>
          </p>
          <p class="text-brandRed text-sm font-semibold tracking-wider uppercase">
            <?= sanitize( $chairTitle ?: 'Chairman of the Board' ) ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 2. Governance & Executive Board (SEPARATE SECTION) -->
<section class="py-12 bg-slate-50 relative border-t border-slate-200/80" id="governance-board">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-8">
      <span class="px-4 py-1.5 rounded-full bg-brandRed/10 text-brandRed text-xs font-bold uppercase tracking-widest inline-block mb-3">
        Strategic Governance
      </span>
      <h2 class="text-3xl md:text-4xl lg:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
        Governance &amp; Executive Board
      </h2>
      <p class="mt-4 text-slate-600 text-base md:text-lg">
        Board members, designations and governance roles providing strategic guidance, fiduciary accountability, and institutional integrity.
      </p>
    </div>

    <!-- Full-width designation band: roles only, never names or photos -->
    <div class="relative overflow-hidden rounded-[2rem] bg-ink-900 ring-1 ring-white/10 shadow-[0_28px_70px_-30px_rgba(2,6,23,0.65)]">
      <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brandRed via-amber-400 to-brandRed"></div>
      <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brandRed/20 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-32 -left-20 h-72 w-72 rounded-full bg-amber-400/10 blur-3xl"></div>
      <div class="pointer-events-none absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 1px 1px, #ffffff 1px, transparent 0); background-size: 22px 22px;"></div>
      <i class="fa-solid fa-scale-balanced pointer-events-none absolute -bottom-10 -right-4 text-[230px] leading-none text-white/[0.04]"></i>

      <div class="relative flex flex-col gap-8 p-6 sm:p-8 lg:p-10 xl:flex-row xl:items-center xl:gap-12">
        <div class="max-w-sm shrink-0">
          <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-white/60">
            <i class="fa-solid fa-landmark text-brandRed"></i> Board of Directors
          </span>
          <h3 class="mt-4 font-display text-2xl font-bold leading-tight text-white sm:text-3xl">
            Stewardship, <span class="text-brandRed">accountability</span> and trust.
          </h3>
          <p class="mt-3 text-sm leading-relaxed text-white/55">
            Strategic direction, fiduciary oversight and institutional integrity behind every WRC programme.
          </p>
          <div class="mt-6 flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/35">
            <span class="h-px w-8 bg-gradient-to-r from-brandRed to-transparent"></span>
            Governance &middot; Executive Management
          </div>
        </div>

        <div class="grid flex-1 grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
          <?php foreach ( $governanceRoles as $role ): ?>
            <?php
            $roleTitle = trim( (string) ( $role['title'] ?? '' ) );
            if ( $roleTitle === '' ) {
                continue;
            }
            $roleLower = strtolower( $roleTitle );
            if ( strpos( $roleLower, 'chair' ) !== false ) {
                $roleIcon  = 'fa-user-tie';
                $roleRemit = 'Board leadership';
            } elseif ( strpos( $roleLower, 'finance' ) !== false ) {
                $roleIcon  = 'fa-calculator';
                $roleRemit = 'Fiscal stewardship';
            } elseif ( strpos( $roleLower, 'program' ) !== false ) {
                $roleIcon  = 'fa-diagram-project';
                $roleRemit = 'Programme delivery';
            } elseif ( strpos( $roleLower, 'project' ) !== false ) {
                $roleIcon  = 'fa-list-check';
                $roleRemit = 'Project execution';
            } elseif ( strpos( $roleLower, 'it ' ) !== false || strpos( $roleLower, 'information' ) !== false ) {
                $roleIcon  = 'fa-server';
                $roleRemit = 'Systems & security';
            } else {
                $roleIcon  = 'fa-id-badge';
                $roleRemit = 'Leadership';
            }
            ?>
            <div class="group rounded-2xl border border-white/10 bg-white/[0.04] p-4 transition-colors hover:border-brandRed/40 hover:bg-white/[0.07]">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-brandRed/25 bg-brandRed/15 text-brandRed">
                <i class="fa-solid <?= $roleIcon ?> text-xs"></i>
              </div>
              <div class="mt-3 text-sm font-bold leading-tight text-white"><?= sanitize( $roleTitle ) ?></div>
              <div class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-white/40"><?= $roleRemit ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
