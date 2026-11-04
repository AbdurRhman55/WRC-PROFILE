<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Who We Are | WRC About';

// Fetch homepage_sections and filter for who-we-are keys
$homepageSections = getRows( 'homepage_sections', 'is_active = 1 ORDER BY sort_order ASC' );
$aboutSections = [];
foreach ( $homepageSections as $section ) {
    $key = $section[ 'section_key' ] ?? '';
    if ( in_array( $key, [ 'who_we_are_hero', 'who_we_are_impact', 'why_we_exist' ] ) ) {
        $aboutSections[ $key ] = $section;
        $aboutSections[ $key ][ 'parsed_content' ] = json_decode( $section[ 'content' ] ?? '{}', true );
    }
}

// Override with dedicated table data
$heroRow = getRow( 'who_we_are_hero' );
if ( $heroRow ) {
    $aboutSections[ 'who_we_are_hero' ] = [ 'section_key' => 'who_we_are_hero', 'section_title' => $heroRow[ 'section_title' ] ?? '', 'section_subtitle' => $heroRow[ 'section_subtitle' ] ?? '', 'parsed_content' => $heroRow ];
}
$impRow = getRow( 'who_we_are_impact' );
if ( $impRow ) {
    $items = getRows( 'who_we_are_impact_items', [ 'impact_id' => $impRow[ 'id' ] ], 'sort_order ASC' );
    $aboutSections[ 'who_we_are_impact' ] = [ 'section_key' => 'who_we_are_impact', 'section_title' => $impRow[ 'section_title' ] ?? '', 'section_subtitle' => $impRow[ 'section_subtitle' ] ?? '', 'parsed_content' => [ 'items' => $items ] ];
}
$whyRow = getRow( 'why_we_exist' );
if ( $whyRow ) {
    $items = getRows( 'why_we_exist_items', [ 'why_id' => $whyRow[ 'id' ] ], 'sort_order ASC' );
    $aboutSections[ 'why_we_exist' ] = [ 'section_key' => 'why_we_exist', 'section_title' => $whyRow[ 'section_title' ] ?? '', 'section_subtitle' => $whyRow[ 'section_subtitle' ] ?? '', 'parsed_content' => [ 'items' => $items ] ];
}

// Convenience references
$hero      = $aboutSections[ 'who_we_are_hero' ] ?? null;
$heroData  = $hero[ 'parsed_content' ] ?? [];
$impact    = $aboutSections[ 'who_we_are_impact' ] ?? null;
$impactData = $impact[ 'parsed_content' ] ?? [];
$whyExist  = $aboutSections[ 'why_we_exist' ] ?? null;
$whyData   = $whyExist[ 'parsed_content' ] ?? [];

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>

<!-- HERO SECTION -->
<section class = 'relative bg-slate-900 text-white min-h-[420px] flex items-center py-20 overflow-hidden'>
<div class = 'absolute inset-0 z-0'>
<img src = "<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>" alt = 'WRC Team' class = 'w-full h-full object-cover opacity-30 mix-blend-luminosity'>
<div class = 'absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/60'></div>
</div>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 relative z-10'>
<nav class = 'text-xs text-slate-400 mb-6 flex items-center gap-2'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<a href = "<?= $base ?>about/index.php" class = 'hover:text-white transition'>About Us</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<span class = 'text-white'>Who We Are</span>
</nav>
<h1 class = 'text-4xl md:text-5xl font-extrabold font-heading text-white mb-4'>
<?= ( !empty( $hero[ 'section_title' ] ) ) ? sanitize( $hero[ 'section_title' ] ) : ( !empty( $heroData[ 'section_title' ] ) ? sanitize( $heroData[ 'section_title' ] ) : 'Who We Are' ) ?>
</h1>
<p class = 'text-slate-300 text-base md:text-lg max-w-2xl leading-relaxed'>
<?= ( !empty( $hero[ 'section_subtitle' ] ) ) ? sanitize( $hero[ 'section_subtitle' ] ) : ( !empty( $heroData[ 'subtitle' ] ) ? sanitize( $heroData[ 'subtitle' ] ) : ( !empty( $heroData[ 'section_subtitle' ] ) ? sanitize( $heroData[ 'section_subtitle' ] ) : 'WRC is a non-profit humanitarian organization committed to delivering life-saving aid and sustainable development programs to vulnerable communities across Pakistan.' ) ) ?>
</p>
</div>
</section>

<!-- AREAS OF IMPACT -->
<?php
$impactItems   = $impactData[ 'items' ] ?? [];
$impactTotal   = max( 1, count( $impactItems ) );
// One accent per sector keeps the six cards visually distinct without adding
// another editable field to the dashboard.
$sectorAccents = [ '#c8102e', '#0f766e', '#0369a1', '#b45309', '#6d28d9', '#be123c' ];
$impactIntro   = 'We take a comprehensive approach to community development, addressing immediate humanitarian needs while implementing sustainable solutions that strengthen communities and improve quality of life. Our work focuses on key sectors where strategic interventions can create meaningful and lasting impact.';
?>
<section class = 'py-24 bg-slate-50 border-t border-slate-200'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center mb-16 max-w-3xl mx-auto'>
<div class = 'inline-flex items-center gap-2 bg-white border border-slate-200 text-brandRed text-[11px] font-bold uppercase tracking-[0.18em] px-4 py-1.5 rounded-full mb-5 shadow-sm'>
<i class = 'fa-solid fa-layer-group'></i> What We Do
</div>
<h2 class = 'text-3xl md:text-4xl font-display font-bold text-slate-900 mb-4'>
<?= ( !empty( $impact[ 'section_title' ] ) ) ? sanitize( $impact[ 'section_title' ] ) : 'Our Areas of Impact' ?>
</h2>
<p class = 'text-slate-600 leading-relaxed'>
<?= ( !empty( $impact[ 'section_subtitle' ] ) ) ? sanitize( $impact[ 'section_subtitle' ] ) : sanitize( $impactIntro ) ?>
</p>
</div>

<div class = 'grid sm:grid-cols-2 lg:grid-cols-3 gap-7'>
<?php foreach ( $impactItems as $index => $item ):
  $accent      = $sectorAccents[ $index % count( $sectorAccents ) ];
  $sectorImage = trim( (string) ( $item[ 'image' ] ?? '' ) );
  $sectorIcon  = $item[ 'icon' ] ?? 'fa-circle-info';
?>
<article class = 'group relative flex flex-col bg-white rounded-2xl border border-slate-200/70 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden'>
<div class = 'relative h-52 bg-slate-100 overflow-hidden'>
<?php if ( $sectorImage !== '' ): ?>
<img src = "<?= $base . ltrim( $sectorImage, '/' ) ?>" alt = "<?= sanitize( $item[ 'title' ] ?? '' ) ?>" loading = 'lazy' class = 'w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105'>
<?php else: ?>
<div class = 'w-full h-full flex items-center justify-center' style = 'background:linear-gradient(135deg, <?= $accent ?>26, <?= $accent ?>08)'>
<i class = "fa-solid <?= sanitize( $sectorIcon ) ?> text-5xl" style = 'color:<?= $accent ?>66'></i>
</div>
<?php endif; ?>
<div class = 'absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-900/10 to-transparent'></div>
<span class = 'absolute top-4 right-4 inline-flex items-center justify-center w-11 h-11 rounded-xl text-white shadow-lg' style = 'background:<?= $accent ?>'>
<i class = "fa-solid <?= sanitize( $sectorIcon ) ?>"></i>
</span>
<span class = 'absolute bottom-4 left-5 text-[11px] font-bold uppercase tracking-[0.2em] text-white/80'>
<?= sprintf( '%02d', $index + 1 ) ?> <span class = 'text-white/40'>/</span> <?= sprintf( '%02d', $impactTotal ) ?>
</span>
</div>
<div class = 'flex-1 p-6'>
<h3 class = 'font-bold text-slate-900 text-lg mb-2'><?= sanitize( $item[ 'title' ] ?? '' ) ?></h3>
<p class = 'text-sm text-slate-600 leading-relaxed'><?= sanitize( $item[ 'description' ] ?? '' ) ?></p>
</div>
<span class = 'block h-1 w-full' style = 'background:<?= $accent ?>'></span>
</article>
<?php endforeach;
?>
</div>
</div>
</section>

<!-- WHY WE EXIST -->
<?php
$whyItems = $whyData[ 'items' ] ?? [];
$whyIntro = 'We believe that meaningful development begins with recognizing the potential of every individual and creating an environment where communities can thrive. Our work is guided by principles that place people, dignity, accountability, and long-term progress at the Centre of every intervention.';
?>
<section class = 'py-24 bg-ink-900 text-white relative overflow-hidden'>
<div class = 'absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white via-transparent to-transparent'></div>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 relative z-10'>
<div class = 'text-center max-w-3xl mx-auto mb-14'>
<div class = 'inline-flex items-center gap-2 bg-white/5 border border-white/10 text-brandRed text-[11px] font-bold uppercase tracking-[0.18em] px-4 py-1.5 rounded-full mb-5'>
<i class = 'fa-solid fa-compass'></i> Our Principles
</div>
<h2 class = 'text-3xl lg:text-4xl font-display font-bold mb-4'>
<?= ( !empty( $whyExist[ 'section_title' ] ) ) ? sanitize( $whyExist[ 'section_title' ] ) : 'Why We Exist' ?>
</h2>
<p class = 'text-sand-50/70 leading-relaxed'>
<?= ( !empty( $whyExist[ 'section_subtitle' ] ) ) ? sanitize( $whyExist[ 'section_subtitle' ] ) : sanitize( $whyIntro ) ?>
</p>
</div>
<div class = 'grid grid-cols-2 gap-6 max-w-4xl mx-auto text-left'>
<?php foreach ( $whyItems as $index => $item ): ?>
<div class = 'relative bg-white/[0.04] border border-white/10 rounded-2xl p-8 hover:bg-white/[0.07] hover:border-white/20 transition-all duration-300'>
<span class = 'absolute top-6 right-7 text-4xl font-display font-bold text-white/[0.07] select-none'><?= sprintf( '%02d', $index + 1 ) ?></span>
<div class = 'w-12 h-12 rounded-xl bg-brandRed/15 text-brandRed flex items-center justify-center mb-5'>
<i class = "fa-solid <?= sanitize( $item[ 'icon' ] ?? 'fa-circle-info' ) ?> text-lg"></i>
</div>
<h3 class = 'text-lg font-bold mb-2'><?= sanitize( $item[ 'title' ] ?? '' ) ?></h3>
<p class = 'text-sand-50/70 text-sm leading-relaxed'><?= sanitize( $item[ 'description' ] ?? '' ) ?></p>
</div>
<?php endforeach;
?>
</div>
</div>
</section>

<!-- CTA BANNER -->
<section class = 'py-20 bg-slate-950 text-white text-center relative overflow-hidden'>
<div class = "absolute inset-0 bg-[url('../assets/images/hero/about-hero.jpg')] bg-cover bg-center mix-blend-multiply opacity-10"></div>
<div class = 'container relative z-10 mx-auto px-4'>
<h2 class = 'text-3xl md:text-4xl font-display font-bold text-white mb-4 max-w-2xl mx-auto leading-tight'>
Together, we can turn compassion into meaningful action.
</h2>
<p class = 'text-slate-400 text-lg mb-10 max-w-xl mx-auto'>Join us in creating lasting change for communities that need it most.</p>
<div class = 'flex flex-col sm:flex-row justify-center gap-4'>
<a href = "<?= $base ?>get-involved/contribute-with-us.php" class = 'px-8 py-4 rounded-xl bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm shadow-lg shadow-brandRed/30 transition-all uppercase tracking-wider'>
Let’s Work Together
</a>
<a href = "<?= $base ?>contact/index.php" class = 'px-8 py-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm transition-all uppercase tracking-wider'>
Contact Us
</a>
</div>
</div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
