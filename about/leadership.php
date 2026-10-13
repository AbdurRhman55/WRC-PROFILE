<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Leadership | WRC About';
$metaDescription = 'Meet the leadership of World Relief Committee (WRC) — our chairman and executive team guiding strategic vision and humanitarian impact.';

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

// Fetch leadership team
$leaders = getRows( 'team_members', "role_type = 'leadership' AND is_active = 1" );
if ( empty( $leaders ) ) {
    $leaders = getRows( 'team_members', 'is_active = 1' );
}

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>

<!-- Hero Section -->
<section class = 'relative w-full bg-ink-900 text-white min-h-[520px] flex items-center overflow-hidden'>
<div class = 'absolute inset-0 z-0'>
<div class = 'absolute inset-0 bg-cover bg-center bg-no-repeat' style = "background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>');"></div>
<div class = 'absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/80 to-ink-900/40'></div>
<div class = 'absolute inset-0 bg-gradient-to-t from-ink-900/90 via-transparent to-ink-900/50'></div>
</div>

<div class = 'relative z-10 w-full px-4 sm:px-6 lg:px-8 py-20 lg:py-28'>
<div class = 'max-w-7xl mx-auto'>
<!-- Breadcrumb -->
<nav class = 'flex items-center gap-2 text-sm text-white/60 mb-8' aria-label = 'Breadcrumb'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition-colors'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[10px]'></i>
<a href = "<?= $base ?>about/index.php" class = 'hover:text-white transition-colors'>About Us</a>
<i class = 'fa-solid fa-chevron-right text-[10px]'></i>
<span class = 'text-white font-medium'>Leadership</span>
</nav>

<h1 class = 'text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-6 max-w-3xl'>
<?= ( !empty( $hero[ 'section_title' ] ) ) ? sanitize( $hero[ 'section_title' ] ) : ( !empty( $heroData[ 'section_title' ] ) ? sanitize( $heroData[ 'section_title' ] ) : 'Our Leadership' ) ?>
</h1>
<p class = 'text-lg md:text-xl text-sand-50/80 max-w-2xl mb-10 leading-relaxed'>
<?= ( !empty( $hero[ 'section_subtitle' ] ) ) ? sanitize( $hero[ 'section_subtitle' ] ) : ( !empty( $heroData[ 'subtitle' ] ) ? sanitize( $heroData[ 'subtitle' ] ) : ( !empty( $heroData[ 'section_subtitle' ] ) ? sanitize( $heroData[ 'section_subtitle' ] ) : 'Meet the dedicated leaders guiding WRC\'s mission to deliver impactful humanitarian aid worldwide.')) ?>
          </p>
        </div>
      </div>
    </section>

    <!-- Chairman's Message -->
<?php
$message = !empty( $chairmanData[ 'paragraph1' ] )
  ? $chairmanData[ 'paragraph1' ]
  : ( $chairmanData[ 'message' ] ?? '' );
$message2 = $chairmanData[ 'paragraph2' ] ?? '';
?>
<section class = 'py-20 bg-white relative overflow-hidden'>
<!-- Background glow effect -->
<div class = 'absolute -top-24 -left-24 w-96 h-96 bg-brandRed/5 rounded-full blur-3xl pointer-events-none'></div>
<div class = 'max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10'>
<div class = 'bg-gradient-to-br from-slate-900 via-slate-900 to-ink-900 text-white rounded-3xl p-8 md:p-14 shadow-2xl relative overflow-hidden border border-slate-800'>
<div class = 'absolute -right-16 -bottom-16 opacity-5 text-white pointer-events-none'>
<i class = 'fa-solid fa-quote-right text-[240px]'></i>
</div>

<div class = 'flex items-center gap-3 mb-6'>
<span class = 'px-3.5 py-1 rounded-full bg-brandRed/20 text-brandRed text-xs font-bold uppercase tracking-widest border border-brandRed/30'>
Leadership Spotlight
</span>
</div>

<div class = 'flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8 mb-10 pb-8 border-b border-white/10'>
<div>
<h2 class = 'text-3xl md:text-4xl font-display font-bold text-white leading-tight'>
<?= ( !empty( $chairman[ 'section_title' ] ) ) ? sanitize( $chairman[ 'section_title' ] ) : 'Message from the Chairman' ?>
</h2>
<p class = 'text-slate-400 text-sm md:text-base mt-2'>
Guiding principles and vision from WRC leadership
</p>
</div>
<div class = 'w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/15'>
<i class = 'fa-solid fa-quote-left text-2xl text-brandRed'></i>
</div>
</div>

<div class = 'space-y-6 mb-12 text-slate-300 leading-relaxed text-base md:text-lg font-normal'>
<?php if ( !empty( $message ) ): ?>
<p><?= sanitize( $message ) ?></p>
<?php endif;
?>
<?php if ( !empty( $message2 ) ): ?>
<p><?= sanitize( $message2 ) ?></p>
<?php endif;
?>
<?php if ( empty( $message ) && empty( $message2 ) ): ?>
<p>
WRC is committed to serving vulnerable communities with compassion, integrity, and transparency. Our leadership team works tirelessly to ensure every program delivers meaningful, measurable, and lasting impact.
</p>
<?php endif;
?>
</div>

<div class = 'pt-6 flex items-center gap-5'>
<?php
$chairPhoto = !empty( $chairmanData[ 'image_path' ] )
  ? $chairmanData[ 'image_path' ]
  : ( $chairmanData[ 'hero_image' ] ?? '' );
$chairPhoto = trim( (string) $chairPhoto );
$chairName = $chairmanData[ 'person_name' ] ?? '';
$chairTitle = $chairmanData[ 'person_title' ] ?? '';
?>
<?php if ( !empty( $chairPhoto ) ): ?>
<img src = "<?= sanitize( getImageUrl( $chairPhoto ) ) ?>" alt = "<?= sanitize($chairName ?: 'Chairman') ?>" class = 'w-16 h-16 rounded-full object-cover border-2 border-brandRed shadow-xl'>
<?php else: ?>
<div class = 'w-16 h-16 rounded-full bg-brandRed/20 border-2 border-brandRed flex items-center justify-center shrink-0 shadow-xl'>
<i class = 'fa-solid fa-user-tie text-2xl text-white'></i>
</div>
<?php endif;
?>
<div>
<p class = 'font-bold text-white text-lg tracking-wide'>
<?= sanitize( $chairName ?: 'Mr. Asad Shah' ) ?>
</p>
<p class = 'text-brandRed text-sm font-semibold tracking-wider uppercase'>
<?= sanitize( $chairTitle ?: 'Chairman of the Board' ) ?>
</p>
</div>
</div>
</div>
</div>
</section>

<!-- Executive Leadership Cards -->
<section class = 'py-20 bg-slate-50 relative'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center max-w-3xl mx-auto mb-16'>
<span class = 'px-4 py-1.5 rounded-full bg-brandRed/10 text-brandRed text-xs font-bold uppercase tracking-widest inline-block mb-3'>
Governance & Executive Board
</span>
<h2 class = 'text-3xl md:text-4xl lg:text-5xl font-display font-extrabold text-slate-900 tracking-tight'>
Executive Leadership
</h2>
<p class = 'mt-4 text-slate-600 text-base md:text-lg'>
Seasoned humanitarians & strategists guiding WRC's mission for sustainable community impact.
          </p>
        </div>

        <?php if (!empty($leaders)): ?>
          <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($leaders as $leader): ?>
              <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden hover:shadow-2xl hover:shadow-slate-900/10 hover:-translate-y-2 transition-all duration-500 group flex flex-col h-full relative">
                <!-- Hover Top Accent Bar -->
                <div class="h-1.5 w-full bg-gradient-to-r from-brandRed via-red-500 to-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>

                <!-- Leader Photo Container -->
                <div class="relative aspect-[4/4.5] overflow-hidden bg-slate-100">
                  <?php if (!empty($leader['photo_path'])): ?>
                    <img src="<?= $base ?><?= sanitize($leader['photo_path']) ?>" alt="<?= sanitize($leader['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out">
                  <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex flex-col items-center justify-center p-6 text-center">
                      <div class="w-20 h-20 rounded-full bg-brandRed/10 border border-brandRed/20 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-user-tie text-3xl text-brandRed"></i>
                      </div>
                      <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Executive Profile</span>
                    </div>
                  <?php endif; ?>

                  <!-- Role Pill Badge -->
                  <div class="absolute top-3 right-3 px-3 py-1 rounded-full bg-slate-900/85 backdrop-blur-md text-[10px] font-bold tracking-widest uppercase text-white shadow-md border border-white/10">
                    <?= sanitize(strtoupper($leader['role_type'] ?? 'Executive')) ?>
                  </div>

                  <!-- Gradient Bottom Overlay -->
                  <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-slate-950/70 to-transparent pointer-events-none opacity-60 group-hover:opacity-80 transition-opacity"></div>
                </div>

                <!-- Card Body -->
                <div class="p-6 flex flex-col flex-grow justify-between bg-white">
                  <div>
                    <h3 class="text-xl font-bold font-display text-slate-900 group-hover:text-brandRed transition-colors duration-300 leading-snug mb-1">
                      <?= sanitize($leader['name'] ?? '') ?>
                    </h3>
                    <p class="text-brandRed text-xs font-semibold uppercase tracking-wider mb-4 flex items-center gap-1.5">
                      <i class="fa-solid fa-briefcase text-[10px] opacity-70"></i>
                      <?= sanitize($leader['title'] ?? 'Executive Leader') ?>
                    </p>
                    <?php if (!empty($leader['bio'])): ?>
                      <p class="text-slate-600 text-sm leading-relaxed mb-6 line-clamp-3">
                        <?= sanitize($leader['bio']) ?>
                      </p>
                    <?php else: ?>
                      <p class="text-slate-400 text-sm italic leading-relaxed mb-6">
                        Dedicated leader driving strategic vision and humanitarian operations.
                      </p>
                    <?php endif; ?>
                  </div>

                  <!-- Footer / Social Contact Links -->
                  <div class="pt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                    <div class="flex items-center gap-2">
                      <?php if (!empty($leader['email'])): ?>
                        <a href="mailto:<?= sanitize($leader['email']) ?>" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white text-slate-500 flex items-center justify-center transition-all duration-300" title="Email <?= sanitize($leader['name'] ?? '') ?>">
                          <i class="fa-solid fa-envelope text-xs"></i>
                        </a>
                      <?php endif; ?>
                      <?php if (!empty($leader['linkedin_url'])): ?>
                        <a href="<?= sanitize($leader['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-500 flex items-center justify-center transition-all duration-300" title="LinkedIn Profile">
                          <i class="fa-brands fa-linkedin-in text-xs"></i>
                        </a>
                      <?php endif; ?>
                      <?php if (empty($leader['email']) && empty($leader['linkedin_url'])): ?>
                        <span class="text-xs text-slate-400 font-medium">World Relief Committee</span>
                      <?php endif; ?>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 group-hover:text-brandRed flex items-center gap-1 transition-colors">
                      Profile <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-16 bg-white rounded-3xl border border-slate-200/80 shadow-sm max-w-xl mx-auto">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-brandRed/10 flex items-center justify-center text-brandRed">
              <i class="fa-solid fa-user-tie text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Leadership Team</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto">Leadership profiles are currently being updated. Please check back soon.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php';
?>
