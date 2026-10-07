<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Annual Reports | WRC About';

$homepageSections = getRows( 'homepage_sections', 'is_active = 1 ORDER BY sort_order ASC' );
$pageSections = [];
foreach ( $homepageSections as $section ) {
    $key = $section[ 'section_key' ] ?? '';
    if ( in_array( $key, [ 'annual_reports_hero', 'annual_reports_content' ] ) ) {
        $pageSections[ $key ] = $section;
        $pageSections[ $key ][ 'parsed_content' ] = json_decode( $section[ 'content' ] ?? '{}', true );
    }
}

// Override with dedicated table data
$heroRow = getRow( 'annual_reports_hero' );
if ( $heroRow ) $pageSections[ 'annual_reports_hero' ] = [ 'section_key' => 'annual_reports_hero', 'section_title' => $heroRow[ 'section_title' ] ?? '', 'section_subtitle' => $heroRow[ 'section_subtitle' ] ?? '', 'parsed_content' => $heroRow ];
$contentRow = getRow( 'annual_reports_content' );
if ( $contentRow ) {
    $decoded = json_decode( $contentRow[ 'content' ] ?? '{}', true );
    $pageSections[ 'annual_reports_content' ] = [ 'section_key' => 'annual_reports_content', 'section_title' => $contentRow[ 'section_title' ] ?? '', 'section_subtitle' => $contentRow[ 'section_subtitle' ] ?? '', 'parsed_content' => is_array( $decoded ) ? $decoded : [] ];
}

$hero = $pageSections[ 'annual_reports_hero' ] ?? null;
$heroData = $hero[ 'parsed_content' ] ?? [];
$content = $pageSections[ 'annual_reports_content' ] ?? null;
$contentData = $content[ 'parsed_content' ] ?? [];

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>
<!-- HERO -->
<section class = 'relative bg-slate-900 text-white min-h-[420px] flex items-center py-20 overflow-hidden'>
<div class = 'absolute inset-0 z-0'>
<img src = "<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>"
alt = 'WRC Reports' class = 'w-full h-full object-cover opacity-30 mix-blend-luminosity'>
<div class = 'absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/60'></div>
</div>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 relative z-10'>
<nav class = 'text-xs text-slate-400 mb-6 flex items-center gap-2'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<a href = "<?= $base ?>about/index.php" class = 'hover:text-white transition'>About Us</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<span class = 'text-white'>Annual Reports</span>
</nav>
<h1 class = 'text-4xl md:text-5xl font-extrabold font-heading text-white mb-4'>
<?= ( !empty( $hero[ 'section_title' ] ) ) ? sanitize( $hero[ 'section_title' ] ) : ( !empty( $heroData[ 'section_title' ] ) ? sanitize( $heroData[ 'section_title' ] ) : 'Annual <span class="text-brandRed">Reports</span>' ) ?>
</h1>
<p class = 'text-slate-300 text-base md:text-lg max-w-2xl leading-relaxed'>
<?= ( !empty( $hero[ 'section_subtitle' ] ) ) ? sanitize( $hero[ 'section_subtitle' ] ) : ( !empty( $heroData[ 'subtitle' ] ) ? sanitize( $heroData[ 'subtitle' ] ) : ( !empty( $heroData[ 'section_subtitle' ] ) ? sanitize( $heroData[ 'section_subtitle' ] ) : 'Comprehensive overviews of our humanitarian operations, financial performance, and community impact.' ) ) ?>
</p>
</div>
</section>

<!-- FEATURED REPORT -->
<section class = 'py-16 bg-white'>
<div class = 'max-w-6xl mx-auto px-4 sm:px-6 lg:px-8'>
<?php
$featured = $contentData[ 'featured' ] ?? [
    'title' => '2023 Annual Report',
    'subtitle' => 'A Year of Resilience and Impact',
    'stats' => [
        [ 'value' => '450K+', 'label' => 'Beneficiaries Reached' ],
        [ 'value' => '120+', 'label' => 'Projects Completed' ],
        [ 'value' => '32', 'label' => 'Districts Covered' ]
    ]
];
?>
<div class = 'bg-slate-50 rounded-2xl p-8 border border-slate-200 mb-12'>
<div class = 'flex items-center gap-3 mb-4'>
<span class = 'bg-brandRed text-white text-xs font-bold px-3 py-1 rounded-full'>Featured</span>
<h2 class = 'text-2xl font-extrabold text-slate-900'><?= sanitize( $featured[ 'title' ] ) ?></h2>
</div>
<p class = 'text-slate-500 mb-6'><?= sanitize( $featured[ 'subtitle' ] ) ?></p>
<div class = 'grid grid-cols-3 gap-4'>
<?php foreach ( $featured[ 'stats' ] as $stat ): ?>
<div class = 'text-center p-4 bg-white rounded-xl border border-slate-100'>
<div class = 'text-2xl font-extrabold text-brandRed'><?= sanitize( $stat[ 'value' ] ) ?></div>
<div class = 'text-xs text-slate-500 mt-1'><?= sanitize( $stat[ 'label' ] ) ?></div>
</div>
<?php endforeach;
?>
</div>
</div>
</div>
</section>

<!-- REPORT ARCHIVE -->
<section class = 'py-12 bg-slate-50'>
<div class = 'max-w-6xl mx-auto px-4 sm:px-6 lg:px-8'>
<h2 class = 'text-2xl font-extrabold text-slate-900 mb-8'>Report Archive</h2>
<div class = 'grid sm:grid-cols-2 lg:grid-cols-3 gap-6'>
<?php
$archive = $contentData[ 'archive' ] ?? [
    [ 'year' => '2022', 'title' => '2022 Annual Report', 'desc' => 'Expanding reach and deepening impact across Pakistan.' ],
    [ 'year' => '2021', 'title' => '2021 Annual Report', 'desc' => 'COVID-19 response and community resilience building.' ],
    [ 'year' => '2020', 'title' => '2020 Annual Report', 'desc' => 'Emergency response and adaptive programming.' ]
];
foreach ( $archive as $report ): ?>
<div class = 'bg-white rounded-xl p-6 border border-slate-200 hover:shadow-lg transition-shadow'>
<div class = 'w-14 h-14 rounded-xl bg-brandRed/10 flex items-center justify-center mb-4'>
<i class = 'fa-solid fa-file-invoice text-brandRed text-xl'></i>
</div>
<span

class = 'text-[10px] uppercase tracking-widest text-slate-400 font-semibold'><?= sanitize( $report[ 'year' ] ) ?></span>
<h3 class = 'text-lg font-bold text-slate-900 mt-1 mb-2'><?= sanitize( $report[ 'title' ] ) ?></h3>
<p class = 'text-slate-500 text-sm leading-relaxed'><?= sanitize( $report[ 'desc' ] ) ?></p>
</div>
<?php endforeach;
?>
</div>
</div>
</section>
</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>