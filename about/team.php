<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Our Team | WRC About';

$teamHero = getRow( 'homepage_sections', [ 'section_key' => 'team_hero', 'is_active' => 1 ] );
$heroData = json_decode( $teamHero[ 'content' ] ?? '{}', true );

$teamMembers = getRows( 'team_members', "role_type = 'team' AND is_active = 1 ORDER BY sort_order ASC" );

if ( empty( $teamMembers ) ) {
    $teamMembers = getRows( 'team_members', 'is_active = 1 ORDER BY sort_order ASC' );
}

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>

<!-- Hero Section -->
<section class = 'relative w-full bg-ink-900 text-white min-h-[520px] flex items-center overflow-hidden'>
<div class = 'absolute inset-0 z-0'>
<div class = 'absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/80 to-ink-900/40'></div>
<div class = 'absolute inset-0 bg-gradient-to-t from-ink-900/90 via-transparent to-ink-900/50'></div>
</div>

<div class = 'relative z-10 w-full px-4 sm:px-6 lg:px-8 py-10 lg:py-12'>
<div class = 'max-w-7xl mx-auto grid items-center gap-10 lg:grid-cols-2 lg:gap-14'>
<div>
<nav class = 'flex items-center gap-2 text-sm text-white/60 mb-8' aria-label = 'Breadcrumb'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition-colors'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[10px]'></i>
<a href = "<?= $base ?>about/index.php" class = 'hover:text-white transition-colors'>About Us</a>
<i class = 'fa-solid fa-chevron-right text-[10px]'></i>
<span class = 'text-white font-medium'>Our Team</span>
</nav>

<h1 class = 'text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-6 max-w-3xl'>
<?= ( !empty( $teamHero[ 'section_title' ] ) ) ? sanitize( $teamHero[ 'section_title' ] ) : ( !empty( $heroData[ 'section_title' ] ) ? sanitize( $heroData[ 'section_title' ] ) : 'Our Team' ) ?>
</h1>
<p class = 'text-lg md:text-xl text-sand-50/80 max-w-2xl mb-10 leading-relaxed'>
<?= ( !empty( $teamHero[ 'section_subtitle' ] ) ) ? sanitize( $teamHero[ 'section_subtitle' ] ) : ( !empty( $heroData[ 'subtitle' ] ) ? sanitize( $heroData[ 'subtitle' ] ) : ( !empty( $heroData[ 'section_subtitle' ] ) ? sanitize( $heroData[ 'section_subtitle' ] ) : 'Meet the dedicated individuals driving our mission forward and making a lasting impact in communities around the world.' ) ) ?>
</p>
</div>

<!-- The whole group photo, uncropped: the frame keeps the picture's own
     proportions ( 4:3 ) instead of stretching it to cover the hero, so
     every face - standing and seated - stays visible at any width. -->
<div>
<figure class = 'm-0'>
<div class = 'rounded-2xl overflow-hidden border border-white/15 shadow-2xl shadow-black/60 bg-black/30'>
<img src = "<?= $base ?><?= sanitize( $heroData[ 'hero_image' ] ?? 'assets/images/hero.jpg' ) ?>" alt = "<?= sanitize( $heroData[ 'hero_alt' ] ?? 'Welfare & Relief Committee team' ) ?>" class = 'block w-full h-auto'>
</div>
</figure>
</div>
</div>
</div>
</section>

<!-- Team Grid -->
<section class = 'py-12 bg-slate-50'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center max-w-2xl mx-auto mb-8'>
<h2 class = 'text-3xl lg:text-4xl font-display font-bold text-slate-900 mb-4'>Our Team</h2>
<p class = 'text-slate-600'>Passionate professionals committed to delivering meaningful humanitarian impact.</p>
</div>

<?php if ( !empty( $teamMembers ) ): ?>
<div class = 'grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6'>
<?php foreach ( $teamMembers as $member ): ?>
<?php
$memberName  = trim( (string) ( $member[ 'full_name' ] ?? $member[ 'name' ] ?? '' ) );
$memberTitle = trim( (string) ( $member[ 'role_title' ] ?? $member[ 'title' ] ?? '' ) );
$memberTitle = $memberTitle !== '' ? $memberTitle : 'Team Member';
$memberPhoto = trim( (string) ( $member[ 'photo_path' ] ?? '' ) );

// Monogram fallback for members without a portrait: first letters of up to
// two meaningful words, skipping honorifics.
$initials = '';
if ( $memberName !== '' ) {
    $words = preg_split( '/\s+/', $memberName ) ?: [];
    foreach ( $words as $word ) {
        $word = trim( $word, ".," );
        if ( $word === '' || in_array( strtolower( $word ), [ 'mr', 'mrs', 'ms', 'dr', 'engr', 'sir' ], true ) ) {
            continue;
        }
        $initials .= strtoupper( mb_substr( $word, 0, 1 ) );
        if ( mb_strlen( $initials ) >= 2 ) {
            break;
        }
    }
    if ( $initials === '' ) {
        $initials = strtoupper( mb_substr( $memberName, 0, 1 ) );
    }
}
?>
<article class = 'group relative flex flex-col overflow-hidden rounded-xl sm:rounded-2xl bg-white border border-slate-200/80 shadow-[0_2px_8px_-2px_rgba(15,23,42,0.05)] hover:shadow-[0_12px_28px_-6px_rgba(15,23,42,0.12)] hover:border-slate-300 hover:-translate-y-1 transition-all duration-300'>

<!-- Accent rule: reveals on hover -->
<span class = 'absolute inset-x-0 top-0 z-10 h-[3px] origin-left scale-x-0 bg-gradient-to-r from-brandRed via-red-500 to-amber-400 transition-transform duration-300 group-hover:scale-x-100' aria-hidden = 'true'></span>

<!-- Member Image -->
<div class = 'relative aspect-square sm:aspect-[4/4.2] overflow-hidden bg-gradient-to-b from-slate-100 to-slate-200/80'>
<?php if ( $memberPhoto !== '' ): ?>
<img src = "<?= $base ?><?= sanitize( $memberPhoto ) ?>" alt = "<?= sanitize( $memberName ) ?>" loading = 'lazy' class = 'h-full w-full object-cover object-top transition-transform duration-500 ease-out group-hover:scale-105'>
<?php else: ?>
<div class = 'flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200 p-4'>
<div class = 'flex h-12 w-12 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-brandRed/10 ring-1 ring-brandRed/20 text-brandRed font-display font-bold text-base sm:text-xl shadow-inner transition-transform duration-300 group-hover:scale-110'>
<?= sanitize( $initials ) ?>
</div>
<span class = 'mt-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400'>WRC Team</span>
</div>
<?php endif;
?>
<span class = 'pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-slate-950/25 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100' aria-hidden = 'true'></span>
</div>

<!-- Name & Designation only -->
<div class = 'flex flex-1 flex-col items-center justify-center px-3.5 py-4 sm:px-4 sm:py-5 text-center bg-white'>
<h3 class = 'font-display text-sm sm:text-base font-bold text-slate-900 group-hover:text-brandRed transition-colors duration-200 leading-snug line-clamp-1'>
<?= sanitize( $memberName ) ?>
</h3>
<p class = 'mt-1 text-[11px] sm:text-xs font-medium text-slate-500 leading-tight line-clamp-1'>
<?= sanitize( $memberTitle ) ?>
</p>
</div>
</article>
<?php endforeach;
?>
</div>
<?php else: ?>
<div class = 'text-center py-12 text-slate-400'>
<i class = 'fa-solid fa-users text-4xl mb-4'></i>
<p>Team information will be available soon.</p>
</div>
<?php endif;
?>

<div class = 'mt-8 text-center'>
<p class = 'text-slate-500 text-sm italic'>More team members will be updated here soon.</p>
</div>
</div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
