<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Policies & Compliance | WRC About';

$homepageSections = getRows( 'homepage_sections', 'is_active = 1 ORDER BY sort_order ASC' );
$pageSections = [];
foreach ( $homepageSections as $section ) {
    $key = $section[ 'section_key' ] ?? '';
    if ( in_array( $key, [ 'policies_hero', 'policies_content' ] ) ) {
        $pageSections[ $key ] = $section;
        $pageSections[ $key ][ 'parsed_content' ] = json_decode( $section[ 'content' ] ?? '{}', true );
    }
}

// Override with dedicated table data
$heroRow = getRow( 'policies_hero' );
if ( $heroRow ) $pageSections[ 'policies_hero' ] = [ 'section_key' => 'policies_hero', 'section_title' => $heroRow[ 'section_title' ] ?? '', 'section_subtitle' => $heroRow[ 'section_subtitle' ] ?? '', 'parsed_content' => $heroRow ];
$contentRow = getRow( 'policies_content' );
if ( $contentRow ) {
    $decoded = json_decode( $contentRow[ 'content' ] ?? '{}', true );
    $pageSections[ 'policies_content' ] = [ 'section_key' => 'policies_content', 'section_title' => $contentRow[ 'section_title' ] ?? '', 'section_subtitle' => $contentRow[ 'section_subtitle' ] ?? '', 'parsed_content' => is_array( $decoded ) ? $decoded : [] ];
}

$hero = $pageSections[ 'policies_hero' ] ?? null;
$heroData = $hero[ 'parsed_content' ] ?? [];
$content = $pageSections[ 'policies_content' ] ?? null;
$contentData = $content[ 'parsed_content' ] ?? [];

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>
<!-- HERO -->
<section class = 'relative bg-slate-900 text-white min-h-[420px] flex items-center py-20 overflow-hidden'>
<div class = 'absolute inset-0 z-0'>
<img src = "<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>" alt = 'WRC Policies' class = 'w-full h-full object-cover opacity-30 mix-blend-luminosity'>
<div class = 'absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/60'></div>
</div>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 relative z-10'>
<nav class = 'text-xs text-slate-400 mb-6 flex items-center gap-2'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<a href = "<?= $base ?>about/index.php" class = 'hover:text-white transition'>About Us</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<span class = 'text-white'>Policies & Compliance</span>
</nav>
<h1 class = 'text-4xl md:text-5xl font-extrabold font-heading text-white mb-4'>
<?= ( !empty( $hero[ 'section_title' ] ) ) ? sanitize( $hero[ 'section_title' ] ) : ( !empty( $heroData[ 'section_title' ] ) ? sanitize( $heroData[ 'section_title' ] ) : 'Policies <span class="text-brandRed">&</span> Compliance' ) ?>
</h1>
<p class = 'text-slate-300 text-base md:text-lg max-w-2xl leading-relaxed'>
<?= ( !empty( $hero[ 'section_subtitle' ] ) ) ? sanitize( $hero[ 'section_subtitle' ] ) : ( !empty( $heroData[ 'subtitle' ] ) ? sanitize( $heroData[ 'subtitle' ] ) : ( !empty( $heroData[ 'section_subtitle' ] ) ? sanitize( $heroData[ 'section_subtitle' ] ) : 'Our commitment to transparency, accountability, and the highest standards of humanitarian practice.' ) ) ?>
</p>
</div>
</section>

<!-- POLICY CATEGORIES -->
<section class = 'py-16 bg-white'>
<div class = 'max-w-6xl mx-auto px-4 sm:px-6 lg:px-8'>
<?php
$categories = $contentData[ 'categories' ] ?? [
    'Governance' => [
        [ 'title' => 'Governance Policy', 'desc' => 'Framework for organizational governance and decision-making.' ],
        [ 'title' => 'Code of Conduct', 'desc' => 'Standards of behavior expected from all staff and representatives.' ],
        [ 'title' => 'Conflict of Interest Policy', 'desc' => 'Guidelines for identifying and managing conflicts of interest.' ]
    ],
    'Safeguarding' => [
        [ 'title' => 'Child Safeguarding Policy', 'desc' => 'Protecting children from harm in all our programs.' ],
        [ 'title' => 'PSEA Policy', 'desc' => 'Prevention of Sexual Exploitation and Abuse.' ],
        [ 'title' => 'General Safeguarding Policy', 'desc' => 'Ensuring safety and dignity for all beneficiaries.' ]
    ],
    'Financial Management' => [
        [ 'title' => 'Financial Management Policy', 'desc' => 'Standards for financial accountability and reporting.' ],
        [ 'title' => 'Procurement Policy', 'desc' => 'Fair and transparent procurement procedures.' ],
        [ 'title' => 'Anti-Fraud & Anti-Corruption Policy', 'desc' => 'Zero tolerance for fraud and corruption.' ]
    ],
    'Human Resources' => [
        [ 'title' => 'HR Policy', 'desc' => 'Human resource management and staff welfare.' ],
        [ 'title' => 'Workplace Policy', 'desc' => 'Creating a safe and inclusive work environment.' ]
    ]
];
foreach ( $categories as $catName => $policies ): ?>
<div class = 'mb-12 last:mb-0'>
<div class = 'flex items-center gap-4 mb-6'>
<h2 class = 'text-xl font-bold text-slate-900 whitespace-nowrap'><?= sanitize( $catName ) ?></h2>
<span class = 'h-px flex-1 bg-slate-200'></span>
</div>
<div class = 'grid sm:grid-cols-2 lg:grid-cols-3 gap-4'>
<?php foreach ( $policies as $p ): ?>
<div class = 'bg-slate-50 rounded-xl p-5 border border-slate-200 hover:shadow-md transition-shadow'>
<div class = 'flex items-start gap-3'>
<div class = 'w-10 h-10 rounded-lg bg-brandRed/10 flex items-center justify-center flex-shrink-0 mt-0.5'>
<i class = 'fa-solid fa-file-pdf text-brandRed'></i>
</div>
<div>
<h4 class = 'font-semibold text-slate-900 text-sm'><?= sanitize( $p[ 'title' ] ) ?></h4>
<p class = 'text-slate-500 text-xs mt-1 leading-relaxed'><?= sanitize( $p[ 'desc' ] ) ?></p>
</div>
</div>
</div>
<?php endforeach;
?>
</div>
</div>
<?php endforeach;
?>
</div>
</section>
</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
