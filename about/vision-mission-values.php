<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Vision, Mission & Values | WRC About';

// Fetch homepage_sections
$homepageSections = getRows( 'homepage_sections', 'is_active = 1 ORDER BY sort_order ASC' );
$pageSections = [];
foreach ( $homepageSections as $section ) {
    $key = $section[ 'section_key' ] ?? '';
    if ( in_array( $key, [ 'vision_mission_hero', 'vision_mission_content' ] ) ) {
        $pageSections[ $key ] = $section;
        $pageSections[ $key ][ 'parsed_content' ] = json_decode( $section[ 'content' ] ?? '{}', true );
    }
}

// Override with dedicated table data
$heroRow = getRow( 'vision_mission_hero' );
if ( $heroRow ) $pageSections[ 'vision_mission_hero' ] = [ 'section_key' => 'vision_mission_hero', 'section_title' => $heroRow[ 'section_title' ] ?? '', 'section_subtitle' => $heroRow[ 'section_subtitle' ] ?? '', 'parsed_content' => $heroRow ];
$contentRow = getRow( 'vision_mission_content' );
if ( $contentRow ) {
    $decoded = json_decode( $contentRow[ 'content' ] ?? '{}', true );
    $pageSections[ 'vision_mission_content' ] = [ 'section_key' => 'vision_mission_content', 'section_title' => $contentRow[ 'section_title' ] ?? '', 'section_subtitle' => $contentRow[ 'section_subtitle' ] ?? '', 'parsed_content' => is_array( $decoded ) ? $decoded : [] ];
}

$hero = $pageSections[ 'vision_mission_hero' ] ?? null;
$heroData = $hero[ 'parsed_content' ] ?? [];
$content = $pageSections[ 'vision_mission_content' ] ?? null;
$contentData = $content[ 'parsed_content' ] ?? [];

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>
<!-- HERO -->
<section class = 'relative bg-slate-900 text-white min-h-[420px] flex items-center py-20 overflow-hidden'>
<div class = 'absolute inset-0 z-0'>
<img src = "<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>" alt = 'WRC Vision' class = 'w-full h-full object-cover opacity-30 mix-blend-luminosity'>
<div class = 'absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/60'></div>
</div>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 relative z-10'>
<nav class = 'text-xs text-slate-400 mb-6 flex items-center gap-2'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<a href = "<?= $base ?>about/index.php" class = 'hover:text-white transition'>About Us</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<span class = 'text-white'>Vision, Mission & Values</span>
</nav>
<h1 class = 'text-4xl md:text-5xl font-extrabold font-heading text-white mb-4'>
<?= ( !empty( $hero[ 'section_title' ] ) ) ? sanitize( $hero[ 'section_title' ] ) : ( !empty( $heroData[ 'section_title' ] ) ? sanitize( $heroData[ 'section_title' ] ) : 'Vision, Mission <span class="text-brandRed">&</span> Values' ) ?>
</h1>
<p class = 'text-slate-300 text-base md:text-lg max-w-2xl leading-relaxed'>
<?= ( !empty( $hero[ 'section_subtitle' ] ) ) ? sanitize( $hero[ 'section_subtitle' ] ) : ( !empty( $heroData[ 'subtitle' ] ) ? sanitize( $heroData[ 'subtitle' ] ) : ( !empty( $heroData[ 'section_subtitle' ] ) ? sanitize( $heroData[ 'section_subtitle' ] ) : 'The principles that guide our humanitarian work and define our commitment to the communities we serve.' ) ) ?>
</p>
</div>
</section>

<!-- VISION & MISSION -->
<section class = 'py-16 bg-white'>
<div class = 'max-w-6xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'grid md:grid-cols-2 gap-8'>
<?php
$visionItems = $contentData[ 'vision_items' ] ?? [
    [ 'title' => 'Our Vision', 'text' => 'A world where every community is resilient, self-reliant, and has access to essential services including healthcare, education, clean water, and economic opportunities.' ],
    [ 'title' => 'Our Mission', 'text' => 'To deliver compassionate, transparent, and effective humanitarian assistance and sustainable development programs that empower vulnerable communities across Pakistan.' ]
];
foreach ( $visionItems as $item ): ?>
<div class = 'bg-slate-50 rounded-2xl p-8 border border-slate-100'>
<h3 class = 'text-xl font-bold text-slate-900 mb-4'><?= sanitize( $item[ 'title' ] ) ?></h3>
<p class = 'text-slate-600 leading-relaxed'><?= sanitize( $item[ 'text' ] ) ?></p>
</div>
<?php endforeach;
?>
</div>
</div>
</section>

<!-- CORE VALUES -->
<section class = 'py-16 bg-slate-50'>
<div class = 'max-w-6xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center mb-12'>
<h2 class = 'text-3xl font-extrabold text-slate-900'>Core Values</h2>
<p class = 'text-slate-500 mt-3'>The foundation of everything we do</p>
</div>
<div class = 'grid sm:grid-cols-2 lg:grid-cols-3 gap-6'>
<?php
$values = $contentData[ 'core_values' ] ?? [
    [ 'icon' => 'fa-hand-holding-heart', 'title' => 'Integrity', 'desc' => 'We uphold the highest standards of honesty and transparency in all our operations.' ],
    [ 'icon' => 'fa-heart', 'title' => 'Compassion', 'desc' => 'We approach every situation with empathy and genuine care for human dignity.' ],
    [ 'icon' => 'fa-clipboard-check', 'title' => 'Accountability', 'desc' => 'We are responsible to our donors, partners, and the communities we serve.' ],
    [ 'icon' => 'fa-people-group', 'title' => 'Inclusion', 'desc' => 'We ensure equitable access to assistance regardless of gender, ethnicity, or background.' ],
    [ 'icon' => 'fa-handshake', 'title' => 'Respect', 'desc' => 'We honor the culture, dignity, and rights of every individual and community.' ],
    [ 'icon' => 'fa-leaf', 'title' => 'Sustainability', 'desc' => 'We design programs that create lasting positive change beyond our direct involvement.' ]
];
foreach ( $values as $v ): ?>
<div class = 'bg-white rounded-xl p-6 border border-slate-200 hover:shadow-lg transition-shadow'>
<div class = 'w-12 h-12 rounded-xl bg-brandRed/10 flex items-center justify-center mb-4'>
<i class = "fa-solid <?= $v['icon'] ?> text-brandRed text-lg"></i>
</div>
<h4 class = 'font-bold text-slate-900 mb-2'><?= sanitize( $v[ 'title' ] ) ?></h4>
<p class = 'text-slate-500 text-sm leading-relaxed'><?= sanitize( $v[ 'desc' ] ) ?></p>
</div>
<?php endforeach;
?>
</div>
</div>
</section>
</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
