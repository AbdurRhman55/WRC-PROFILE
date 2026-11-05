<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Leadership & Team | WRC About';
$metaDescription = 'Meet the leadership of Welfare & Relief Committee (WRC) — our chairman, governance board, executive leadership, and dedicated operational team.';

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

// 1. Governance & Executive Board Members
// Role type 'chairman' or 'board', or title containing 'Board'
$boardMembers = getRows( 'team_members', "is_active = 1 AND (role_type IN ('chairman', 'board') OR title LIKE '%Board%') ORDER BY sort_order ASC, id ASC" );
if ( empty( $boardMembers ) ) {
    $boardMembers = [
        [
            'name' => $chairmanData['person_name'] ?: 'Mr. Asad Shah',
            'title' => $chairmanData['person_title'] ?: 'Chairman of the Board',
            'role_type' => 'chairman',
            'bio' => 'Guiding strategic governance, fiduciary accountability, and institutional policy to advance WRC’s humanitarian mission.',
            'photo_path' => !empty($chairmanData['image_path']) ? $chairmanData['image_path'] : ($chairmanData['hero_image'] ?? 'assets/images/uploads/images-1788330057.jpg'),
            'email' => '',
            'linkedin_url' => '',
        ]
    ];
}

// 2. Executive Leadership (CEO, Executive Director and senior management)
// Excludes Programme Assistant, IT Manager, coordinators, and operational staff
$execLeaders = getRows( 'team_members', "is_active = 1 AND (role_type IN ('executive', 'leadership')) AND title NOT LIKE '%Assistant%' AND title NOT LIKE '%IT Manager%' AND title NOT LIKE '%Coordinator%' AND title NOT LIKE '%Officer%' AND title NOT LIKE '%Board%' AND role_type != 'chairman' ORDER BY sort_order ASC, id ASC" );

// 3. Our Team – The People Behind Our Mission
// Programme, IT, administration and operational staff (excludes placeholder '[Name]')
$teamStaff = getRows( 'team_members', "is_active = 1 AND (role_type = 'staff' OR title LIKE '%Assistant%' OR title LIKE '%IT Manager%' OR title LIKE '%Coordinator%' OR title LIKE '%Officer%') AND name != '[Name]' AND name != '' AND role_type NOT IN ('chairman', 'board') ORDER BY sort_order ASC, id ASC" );

include dirname( __DIR__ ) . '/includes/header.php';

/**
 * Helper to render team profile cards consistently across all profiles.
 * Matches the exact styling, sizing, and structure of about/team.php.
 */
function renderProfileCard( array $member, string $badgeText = 'Profile' ): void {
    $photo = trim( (string) ( $member['photo_path'] ?? '' ) );
    $hasPhoto = $photo !== '' && imageExists( $photo );
    $imgUrl = getImageUrl( $photo, 'assets/images/chairman.svg' );
    $name = $member['full_name'] ?? $member['name'] ?? '';
    $title = $member['role_title'] ?? $member['title'] ?? 'Team Member';
    $bio = $member['description'] ?? $member['bio'] ?? '';
    $email = $member['email'] ?? '';
    $linkedin = $member['linkedin_url'] ?? '';
    ?>
    <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden hover:shadow-2xl hover:shadow-slate-900/10 hover:-translate-y-2 transition-all duration-500 group flex flex-col h-full relative">
      <!-- Top Accent Line -->
      <div class="h-1.5 w-full bg-gradient-to-r from-brandRed via-red-500 to-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

      <!-- Member Image Container: exact aspect-[4/4.5] as team.php -->
      <div class="relative aspect-[4/4.5] w-full overflow-hidden bg-slate-100">
        <?php if ( $hasPhoto ): ?>
          <img src="<?= sanitize( $imgUrl ) ?>" alt="<?= sanitize( $name ) ?>" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out">
        <?php else: ?>
          <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex flex-col items-center justify-center p-6 text-center">
            <div class="w-20 h-20 rounded-full bg-brandRed/10 border border-brandRed/20 flex items-center justify-center mb-3">
              <i class="fa-solid fa-user text-3xl text-brandRed"></i>
            </div>
            <span class="text-xs text-slate-400 font-medium uppercase tracking-wider font-heading">WRC Profile</span>
          </div>
        <?php endif; ?>

        <!-- Role Badge -->
        <div class="absolute top-3 right-3 px-3 py-1 rounded-full bg-slate-900/85 backdrop-blur-md text-[10px] font-bold tracking-widest uppercase text-white shadow-md border border-white/10">
          <?= sanitize( strtoupper( $badgeText ) ) ?>
        </div>
      </div>

      <!-- Member Content -->
      <div class="p-6 flex flex-col flex-grow justify-between bg-white">
        <div>
          <h3 class="text-xl font-bold font-display text-slate-900 group-hover:text-brandRed transition-colors duration-300 leading-snug mb-1">
            <?= sanitize( $name ) ?>
          </h3>
          <p class="text-brandRed text-xs font-semibold uppercase tracking-wider mb-4 flex items-center gap-1.5">
            <i class="fa-solid fa-id-badge text-[10px] opacity-70"></i>
            <?= sanitize( $title ) ?>
          </p>
          <?php if ( !empty( $bio ) ): ?>
            <p class="text-slate-600 text-sm leading-relaxed mb-6 line-clamp-3">
              <?= sanitize( $bio ) ?>
            </p>
          <?php else: ?>
            <p class="text-slate-400 text-sm italic leading-relaxed mb-6">
              Dedicated professional committed to WRC's humanitarian mission and community impact.
            </p>
          <?php endif; ?>
        </div>

        <!-- Footer Social Links -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
          <div class="flex items-center gap-2">
            <?php if ( !empty( $email ) ): ?>
              <a href="mailto:<?= sanitize( $email ) ?>" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white text-slate-500 flex items-center justify-center transition-all duration-300" aria-label="Email <?= sanitize( $name ) ?>" title="Email <?= sanitize( $name ) ?>">
                <i class="fa-solid fa-envelope text-xs"></i>
              </a>
            <?php endif; ?>
            <?php if ( !empty( $linkedin ) ): ?>
              <a href="<?= sanitize( $linkedin ) ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-500 flex items-center justify-center transition-all duration-300" aria-label="LinkedIn" title="LinkedIn Profile">
                <i class="fa-brands fa-linkedin-in text-xs"></i>
              </a>
            <?php endif; ?>
            <?php if ( empty( $email ) && empty( $linkedin ) ): ?>
              <span class="text-xs text-slate-400 font-medium">Welfare &amp; Relief Committee</span>
            <?php endif; ?>
          </div>
          <span class="text-xs font-semibold text-slate-400 group-hover:text-brandRed flex items-center gap-1 transition-colors">
            Profile <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
          </span>
        </div>
      </div>
    </div>
    <?php
}
?>

<main class="flex-grow">

<!-- Hero Section -->
<section class="relative w-full bg-ink-900 text-white min-h-[520px] flex items-center overflow-hidden">
  <div class="absolute inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/80 to-ink-900/40"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-transparent to-ink-900/50"></div>
  </div>

  <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
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
<section class="py-20 bg-white relative overflow-hidden" id="chairmans-message">
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

      <div class="space-y-6 mb-12 text-slate-300 leading-relaxed text-base md:text-lg font-normal">
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
<section class="py-20 bg-slate-50 relative border-t border-slate-200/80" id="governance-board">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-16">
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

    <?php if ( !empty( $boardMembers ) ): ?>
      <div class="flex flex-wrap justify-center gap-8">
        <?php foreach ( $boardMembers as $member ): ?>
          <div class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(25%-1.5rem)] max-w-sm flex flex-col">
            <?php renderProfileCard( $member, 'Governance & Board' ); ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- 3. Executive Leadership (SEPARATE SECTION - EXCLUDES PROGRAMME ASSISTANT & IT MANAGER) -->
<section class="py-20 bg-white relative border-t border-slate-200/80" id="executive-leadership">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-16">
      <span class="px-4 py-1.5 rounded-full bg-brandRed/10 text-brandRed text-xs font-bold uppercase tracking-widest inline-block mb-3">
        Executive Leadership
      </span>
      <h2 class="text-3xl md:text-4xl lg:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
        Executive Leadership
      </h2>
      <p class="mt-4 text-slate-600 text-base md:text-lg">
        CEO, Executive Director and senior management steering WRC's strategic programs, operations, and community impact.
      </p>
    </div>

    <?php if ( !empty( $execLeaders ) ): ?>
      <div class="flex flex-wrap justify-center gap-8">
        <?php foreach ( $execLeaders as $leader ): ?>
          <div class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(25%-1.5rem)] max-w-sm flex flex-col">
            <?php renderProfileCard( $leader, 'Executive Leadership' ); ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-12 bg-slate-50 rounded-3xl border border-slate-200/80 max-w-xl mx-auto">
        <p class="text-slate-500 text-sm">Executive profiles are currently being updated.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- 4. Our Team – The People Behind Our Mission (SEPARATE SECTION FOR OPERATIONAL & PROGRAMME STAFF) -->
<section class="py-20 bg-slate-50 relative border-t border-slate-200/80" id="our-team">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-16">
      <span class="px-4 py-1.5 rounded-full bg-brandRed/10 text-brandRed text-xs font-bold uppercase tracking-widest inline-block mb-3">
        Our Team
      </span>
      <h2 class="text-3xl md:text-4xl lg:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
        The People Behind Our Mission
      </h2>
      <p class="mt-4 text-slate-600 text-base md:text-lg leading-relaxed">
        Behind every successful initiative is a dedicated team working with commitment, professionalism, and a shared sense of responsibility. At WRC, our team brings together diverse skills and expertise to support programme delivery, strengthen institutional capacity, and serve communities with dedication.
      </p>
    </div>

    <?php if ( !empty( $teamStaff ) ): ?>
      <div class="flex flex-wrap justify-center gap-8">
        <?php foreach ( $teamStaff as $staff ): ?>
          <div class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(25%-1.5rem)] max-w-sm flex flex-col">
            <?php renderProfileCard( $staff, 'Our Team' ); ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-12 bg-white rounded-3xl border border-slate-200/80 max-w-xl mx-auto">
        <p class="text-slate-500 text-sm">Operational and programme staff profiles are currently being updated.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
