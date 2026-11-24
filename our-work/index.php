<?php
/**
* WRC Our Work - Program Listing ( Dynamic )
* ALL content from database - 0% hardcoded
*/
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();

// Fetch all projects
$projects = getRows( 'projects', [ 'is_active' => 1 ], 'sort_order ASC' );

// Fetch all Our Work homepage sections
$allSections = getRows( 'homepage_sections', '', '' );
$owSections = [];
foreach ( $allSections as $s ) {
    if ( str_starts_with( $s[ 'section_key' ], 'our_work' ) ) {
        $owSections[ $s[ 'section_key' ] ] = $s;
    }
}

// Override with dedicated table data
$heroRow = getRow( 'our_work_hero' );
if ( $heroRow ) $owSections[ 'our_work_hero' ] = [ 'section_key' => 'our_work_hero', 'section_title' => $heroRow[ 'section_title' ] ?? '', 'section_subtitle' => $heroRow[ 'section_subtitle' ] ?? '', 'content' => json_encode( $heroRow ) ];

$edRow = getRow( 'our_work_editorial' );
if ( $edRow ) {
    $features = getRows( 'our_work_editorial_features', [ 'editorial_id' => $edRow[ 'id' ] ], 'sort_order ASC' );
    $owSections[ 'our_work_editorial' ] = [ 'section_key' => 'our_work_editorial', 'section_title' => $edRow[ 'section_title' ], 'section_subtitle' => $edRow[ 'section_subtitle' ], 'content' => json_encode( [ 'image' => $edRow[ 'image' ], 'label' => $edRow[ 'label' ], 'description' => $edRow[ 'description' ], 'features' => $features ] ) ];
}

$procRow = getRow( 'our_work_process' );
if ( $procRow ) {
    $steps = getRows( 'our_work_process_steps', [ 'process_id' => $procRow[ 'id' ] ], 'sort_order ASC' );
    $stepsMapped = array_map( function( $s ) {
        return [ 'num' => $s[ 'step_num' ], 'title' => $s[ 'title' ], 'description' => $s[ 'description' ] ];
    }
    , $steps );
    $owSections[ 'our_work_process' ] = [ 'section_key' => 'our_work_process', 'section_title' => $procRow[ 'section_title' ], 'section_subtitle' => $procRow[ 'section_subtitle' ], 'content' => json_encode( [ 'subtitle' => $procRow[ 'subtitle' ], 'steps' => $stepsMapped ] ) ];
}

$pillRow = getRow( 'our_work_pillars' );
if ( $pillRow ) {
    $pillars = getRows( 'our_work_pillars_items', [ 'pillars_id' => $pillRow[ 'id' ] ], 'sort_order ASC' );
    $pillarsMapped = array_map( function( $p ) {
        return [ 'num' => $p[ 'pillar_num' ], 'title' => $p[ 'title' ], 'description' => $p[ 'description' ] ];
    }
    , $pillars );
    $owSections[ 'our_work_pillars' ] = [ 'section_key' => 'our_work_pillars', 'section_title' => $pillRow[ 'section_title' ], 'section_subtitle' => $pillRow[ 'section_subtitle' ], 'content' => json_encode( [ 'subtitle' => $pillRow[ 'subtitle' ], 'pillars' => $pillarsMapped ] ) ];
}

$featRow = getRow( 'our_work_featured_projects' );
if ( $featRow ) $owSections[ 'our_work_featured_projects' ] = [ 'section_key' => 'our_work_featured_projects', 'section_title' => $featRow[ 'section_title' ] ?? '', 'section_subtitle' => $featRow[ 'section_subtitle' ] ?? '', 'content' => json_encode( [ 'description' => $featRow[ 'description' ] ] ) ];

$ctaRow = getRow( 'our_work_cta' );
if ( $ctaRow ) $owSections[ 'our_work_cta' ] = [ 'section_key' => 'our_work_cta', 'section_title' => $ctaRow[ 'section_title' ], 'content' => json_encode( $ctaRow ) ];

// Dashboard section from dedicated table
$dashRow = getRow( 'our_work_dashboard' );
if ( $dashRow ) {
    $metrics = getRows( 'our_work_dashboard_metrics', [ 'dashboard_id' => $dashRow['id'] ], 'sort_order ASC' );
    $sectors = getRows( 'our_work_dashboard_sectors', [ 'dashboard_id' => $dashRow['id'] ], 'sort_order ASC' );
    $owSections[ 'our_work_dashboard' ] = [
        'section_key' => 'our_work_dashboard',
        'section_title' => $dashRow['section_title'],
        'section_subtitle' => $dashRow['section_subtitle'] ?? '',
        'content' => json_encode( [
            'subtitle' => $dashRow['subtitle'] ?? '',
            'hero_image' => $dashRow['hero_image'] ?? '',
            'metrics' => $metrics,
            'sectors' => $sectors,
        ] )
    ];
}

// Parse section JSON data
$heroData = isset( $owSections[ 'our_work_hero' ] ) ? json_decode( $owSections[ 'our_work_hero' ][ 'content' ], true ) : [];
$editorialData = isset( $owSections[ 'our_work_editorial' ] ) ? json_decode( $owSections[ 'our_work_editorial' ][ 'content' ], true ) : [];
$processData = isset( $owSections[ 'our_work_process' ] ) ? json_decode( $owSections[ 'our_work_process' ][ 'content' ], true ) : [];
$pillarsData = isset( $owSections[ 'our_work_pillars' ] ) ? json_decode( $owSections[ 'our_work_pillars' ][ 'content' ], true ) : [];
$dashboardData = isset( $owSections[ 'our_work_dashboard' ] ) ? json_decode( $owSections[ 'our_work_dashboard' ][ 'content' ], true ) : [];
$featuredProjData = isset( $owSections[ 'our_work_featured_projects' ] ) ? json_decode( $owSections[ 'our_work_featured_projects' ][ 'content' ], true ) : [];
$ctaData = isset( $owSections[ 'our_work_cta' ] ) ? json_decode( $owSections[ 'our_work_cta' ][ 'content' ], true ) : [];

// Fetch featured projects ( latest active )
$featuredProjects = getRows( 'projects', "is_active = 1 AND status = 'active' ORDER BY start_date DESC LIMIT 3" );

$pageTitle = 'Our Work & Core Programs | Welfare & Relief Committee (WRC)';
$metaDescription = "Explore WRC's four programme sectors: Health, WASH, Food & Relief Distribution and Skill Development, delivered across Khyber Pakhtunkhwa and Punjab.";

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>

<!-- HERO SECTION -->
<section class = 'relative bg-ink-900 text-white flex items-center overflow-hidden h-[calc(100svh-7.5rem)] min-h-[430px] lg:h-[calc(100svh-9.5rem)]'>
<div class = 'absolute inset-0 z-0'>
<img src = "<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/our-work/our-work-hero.jpg' ?>" alt = "<?= sanitize($heroData['hero_alt'] ?? 'Humanitarian Fieldwork in Pakistan') ?>" class = 'w-full h-full object-cover object-center'>
<div class = 'absolute inset-0 bg-gradient-to-r from-ink-900/95 via-ink-900/55 to-ink-900/20 lg:from-ink-900/85 lg:via-ink-900/30 lg:to-transparent'></div>
</div>

<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 relative z-10'>
<div class = 'max-w-3xl'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'><?= $owSections[ 'our_work_hero' ][ 'section_title' ] ?? 'Our Work' ?></span>
<h1 class = 'text-4xl md:text-5xl lg:text-5xl xl:text-6xl font-extrabold font-heading text-white mb-4 leading-tight [text-shadow:0_2px_24px_rgba(0,0,0,0.55)]'>
<?= $owSections[ 'our_work_hero' ][ 'section_subtitle' ] ?? '' ?>
</h1>
<p class = 'text-slate-200 text-base md:text-lg mb-6 leading-relaxed [text-shadow:0_1px_12px_rgba(0,0,0,0.65)]'>
<?= sanitize( $heroData[ 'subtitle' ] ?? '' ) ?>
</p>
<div class = 'flex flex-wrap gap-4'>
<a href = "<?= $base ?><?= $heroData['button1_link'] ?? '#sectors' ?>" class = 'bg-brandRed hover:bg-brandRed-hover text-white font-bold text-xs px-6 py-4 rounded-xl shadow-lg shadow-brandRed/20 transition-all uppercase tracking-wider'>
<?= sanitize( $heroData[ 'button1_text' ] ?? 'Explore Our Programs' ) ?>
</a>
<a href = "<?= $base ?><?= $heroData['button2_link'] ?? 'get-involved/contribute-with-us.php' ?>" class = 'bg-white/10 hover:bg-white/20 text-white border border-white/25 font-bold text-xs px-6 py-4 rounded-xl transition-all uppercase tracking-wider'>
<?= sanitize( $heroData[ 'button2_text' ] ?? 'Let’s Work Together' ) ?>
</a>
</div>
</div>
</div>
</section>

<!-- QUICK NAV / FILTER BAR -->
<section class = 'sticky top-20 z-40 bg-white border-b border-slate-200 shadow-sm py-4'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex items-center justify-between gap-4 overflow-x-auto no-scrollbar py-1 text-xs'>
<span class = 'font-bold text-slate-900 uppercase tracking-wider shrink-0 flex items-center gap-1.5'><i class = 'fa-solid fa-sliders text-brandRed'></i> Quick Links:</span>
<div class = 'flex items-center gap-2 shrink-0'>
<a href = '#projects' class = 'px-3.5 py-1.5 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white transition-all font-medium text-slate-700'>Projects</a>
<a href = '#featured' class = 'px-3.5 py-1.5 rounded-full bg-slate-100 hover:bg-brandRed hover:text-white transition-all font-medium text-slate-700'>Featured</a>
<a href = "<?= $base ?>projects/index.php" class = 'px-3.5 py-1.5 rounded-full bg-brandRed text-white hover:bg-brandRed-hover transition-all font-medium'>View All Projects &rarr;
</a>
</div>
</div>
</div>
</section>

<!-- OUR PROJECTS GRID -->
<section id = 'projects' class = 'py-12 lg:py-12 bg-slate-50 scroll-mt-24'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>

<div class = 'text-center max-w-3xl mx-auto mb-8'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'>Our Portfolio</span>
<h2 class = 'text-3xl md:text-4xl font-extrabold font-heading text-slate-900 mb-4'>Active & Completed Projects</h2>
<p class = 'text-slate-600 text-sm leading-relaxed'>Delivering real results through humanitarian projects across health, WASH, food and relief distribution, and skill development.</p>
</div>

<div class = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8'>
<?php foreach ( $projects as $project ): ?>
<a href = "<?= $base ?>projects/project-name.php?slug=<?= urlencode($project['slug']) ?>" class = 'bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between group hover:shadow-xl transition-all duration-300'>
<div class = 'relative h-48 overflow-hidden'>
<img src = "<?= $base ?><?= $project['hero_image'] ?? 'assets/images/water.jpg' ?>" alt = "<?= sanitize($project['title']) ?>" class = 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-105'>
<div class = 'absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent'></div>
<span class = "absolute top-4 left-4 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 <?= $project['status'] === 'active' ? 'bg-emerald-500' : 'bg-slate-600' ?> text-white rounded-full shadow-md"><?= ucfirst( $project[ 'status' ] ) ?></span>
<span class = 'absolute top-4 right-4 text-[10px] font-medium px-2.5 py-1 bg-slate-900/70 backdrop-blur-sm text-white rounded-full'><i class = 'fa-solid fa-location-dot mr-1'></i><?= sanitize( $project[ 'location_details' ] ?? '' ) ?></span>
</div>
<div class = 'p-6 flex-grow flex flex-col justify-between'>
<div>
<h3 class = 'text-lg font-bold text-slate-900 font-heading mb-3 group-hover:text-brandRed transition-colors'><?= sanitize( $project[ 'title' ] ) ?></h3>
<p class = 'text-xs text-slate-600 leading-relaxed mb-4 line-clamp-3'><?= sanitize( $project[ 'description' ] ?? '' ) ?></p>
<?php if ( $project[ 'beneficiaries_count' ] > 0 ): ?>
<div class = 'flex items-center gap-2 text-[10px] text-slate-500 mb-2'>
<i class = 'fa-solid fa-users text-brandRed'></i> <?= number_format( $project[ 'beneficiaries_count' ] ) ?>+ beneficiaries
</div>
<?php endif;
?>
</div>
<div class = 'pt-4 border-t border-slate-100 flex items-center justify-between'>
<span class = 'text-[10px] text-slate-400'>Since <?= date( 'Y', strtotime( $project[ 'start_date' ] ) ) ?></span>
<span class = 'text-brandRed hover:text-brandRed-hover text-xs font-semibold hover:underline flex items-center gap-1 group-hover:translate-x-1 transition-transform'>
View Details <i class = 'fa-solid fa-arrow-right text-[10px]'></i>
</span>
</div>
</div>
</a>
<?php endforeach;
?>
</div>

<div class = 'text-center mt-6'><a href = "<?= $base ?>projects/index.php"

class = 'inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold px-6 py-3 rounded-xl transition-all'>View
All Projects <i class = 'fa-solid fa-arrow-right text-xs'></i></a></div>
</div>
</section>

<!-- FEATURED WORK SECTION ( EDITORIAL ) -->
<section class = 'py-12 lg:py-12 bg-white border-t border-slate-200/70'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center'>
<div class = 'lg:col-span-6 relative'>
<div class = 'relative rounded-3xl overflow-hidden shadow-2xl'>
<img src = "<?= $base ?><?= $editorialData['image'] ?? 'assets/images/our-work/featured-work.jpg' ?>" alt = "<?= sanitize($editorialData['image_alt'] ?? 'WRC Field Team in action') ?>" class = 'w-full h-[450px] object-cover object-center'>
<div class = 'absolute inset-0 bg-slate-950/10'></div>
</div>
<div class = 'absolute -bottom-6 -right-6 w-48 h-48 bg-slate-100 rounded-3xl -z-10 hidden sm:block'></div>
</div>

<div class = 'lg:col-span-6 space-y-6'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest block'><?= sanitize( $editorialData[ 'label' ] ?? $owSections[ 'our_work_editorial' ][ 'section_subtitle' ] ?? '' ) ?></span>
<h2 class = 'text-3xl md:text-4xl font-extrabold font-heading text-slate-900'><?= $owSections[ 'our_work_editorial' ][ 'section_title' ] ?? '' ?></h2>
<p class = 'text-slate-600 text-sm leading-relaxed'>
<?= sanitize( $editorialData[ 'description' ] ?? '' ) ?>
</p>

<div class = 'grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4'>
<?php foreach ( $editorialData[ 'features' ] ?? [] as $feature ): ?>
<div class = 'space-y-2'>
<div class = 'flex items-center gap-2 text-slate-900 font-bold text-sm'>
<i class = "<?= $feature['icon'] ?> text-brandRed"></i> <?= sanitize( $feature[ 'title' ] ) ?>
</div>
<p class = 'text-xs text-slate-600 leading-relaxed'><?= sanitize( $feature[ 'description' ] ) ?></p>
</div>
<?php endforeach;
?>
</div>
</div>
</div>
</div>
</section>

<!-- HOW WE WORK TIMELINE SECTION -->
<section class = 'py-12 lg:py-12 bg-slate-50 border-t border-slate-200/50'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>

<div class = 'text-center max-w-3xl mx-auto mb-10'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'><?= sanitize( $owSections[ 'our_work_process' ][ 'section_subtitle' ] ?? 'Our Process' ) ?></span>
<h2 class = 'text-3xl md:text-4xl font-extrabold font-heading text-slate-900 mb-4'><?= $owSections[ 'our_work_process' ][ 'section_title' ] ?? '' ?></h2>
<p class = 'text-slate-600 text-sm'><?= sanitize( $processData[ 'subtitle' ] ?? '' ) ?></p>
</div>

<div class = 'process-timeline max-w-6xl mx-auto'>
<?php foreach ( $processData[ 'steps' ] ?? [] as $step ): ?>
<div class = 'process-step'>
<div class = 'process-icon-wrapper w-14 h-14 rounded-2xl bg-white border border-slate-200 shadow-md flex items-center justify-center text-lg font-bold text-brandRed font-heading'>
<?= $step[ 'num' ] ?>
</div>
<h4 class = 'font-bold text-slate-900 text-sm mb-1'><?= sanitize( $step[ 'title' ] ) ?></h4>
<p class = 'text-[11px] text-slate-600 max-w-[150px] mx-auto lg:mx-0'><?= sanitize( $step[ 'description' ] ) ?></p>
</div>
<?php endforeach;
?>
</div>

</div>
</section>

<!-- UNIFIED IMPACT DASHBOARD -->
<section id = 'dashboard' class = 'relative bg-sand-50 py-12 lg:py-16'>
<div class = 'absolute inset-0 bg-gradient-to-b from-sand-50 via-white to-sand-50'></div>
<div class = 'pointer-events-none absolute -left-24 top-0 h-72 w-72 rounded-full bg-brandRed/10 blur-3xl'></div>
<div class = 'pointer-events-none absolute -right-16 bottom-0 h-72 w-72 rounded-full bg-gold-400/10 blur-3xl'></div>

<div class = 'relative container mx-auto px-4 sm:px-6 lg:px-8'>
<!-- Section Header -->
<div class = 'mx-auto max-w-3xl text-center mb-10 lg:mb-12'>
<p class = 'text-xs font-semibold uppercase tracking-[0.35em] text-brandRed'>Unified Impact Dashboard</p>
<h2 class = 'mt-3 text-3xl font-display font-bold text-ink-900 sm:text-4xl'><?= $owSections['our_work_dashboard']['section_title'] ?? 'Impact at a Glance — Live Data' ?></h2>
<p class = 'mt-5 text-sm leading-7 text-slate-600 sm:text-base'>
Real-time beneficiary figures aggregated from active projects across all programme sectors.
Each card combines sector context with live counters that update automatically as projects are added or completed.
</p>
</div>

<?php
$beneficiaries = wrcBeneficiaryFigures();
$coverage = wrcProjectImpactSummary();
$dashMetrics = $dashboardData['metrics'] ?? [];
$dashSectors = $dashboardData['sectors'] ?? [];
?>

<!-- Summary Bar -->
<div class = 'mx-auto max-w-5xl mb-10 lg:mb-12'>
<div class = 'grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6'>
<!-- Total Beneficiaries -->
<div class = 'relative rounded-2xl bg-white p-5 lg:p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-shadow duration-300'>
<div class = 'absolute inset-0 bg-gradient-to-br from-brandRed/5 to-transparent rounded-2xl'></div>
<div class = 'relative flex items-center gap-4'>
<div class = 'flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brandRed/10 text-brandRed'>
<i class = 'fa-solid fa-users text-2xl'></i>
</div>
<div>
<p class = 'text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500'>Total Reach</p>
<div class = 'counter-val text-3xl font-display font-bold leading-none text-ink-900' data-target = '<?= $beneficiaries['total'] ?>'>0</div>
<p class = 'mt-1 text-[11px] text-slate-500'>Beneficiaries across all sectors</p>
</div>
</div>
</div>

<!-- Active Projects -->
<div class = 'relative rounded-2xl bg-white p-5 lg:p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-shadow duration-300'>
<div class = 'absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent rounded-2xl'></div>
<div class = 'relative flex items-center gap-4'>
<div class = 'flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600'>
<i class = 'fa-solid fa-folder-open text-2xl'></i>
</div>
<div>
<p class = 'text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500'>Active Projects</p>
<div class = 'counter-val text-3xl font-display font-bold leading-none text-ink-900' data-target = '<?= $coverage['projects'] ?>'>0</div>
<p class = 'mt-1 text-[11px] text-slate-500'>Currently implementing</p>
</div>
</div>
</div>

<!-- Districts Covered -->
<div class = 'relative rounded-2xl bg-white p-5 lg:p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-shadow duration-300'>
<div class = 'absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent rounded-2xl'></div>
<div class = 'relative flex items-center gap-4'>
<div class = 'flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600'>
<i class = 'fa-solid fa-map-location-dot text-2xl'></i>
</div>
<div>
<p class = 'text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500'>Districts Reached</p>
<div class = 'counter-val text-3xl font-display font-bold leading-none text-ink-900' data-target = '<?= $coverage['districts'] ?>'>0</div>
<p class = 'mt-1 text-[11px] text-slate-500'>Communities served</p>
</div>
</div>
</div>

<!-- Partners -->
<div class = 'relative rounded-2xl bg-white p-5 lg:p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-shadow duration-300'>
<div class = 'absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent rounded-2xl'></div>
<div class = 'relative flex items-center gap-4'>
<div class = 'flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600'>
<i class = 'fa-solid fa-handshake text-2xl'></i>
</div>
<div>
<p class = 'text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500'>Partners</p>
<div class = 'counter-val text-3xl font-display font-bold leading-none text-ink-900' data-target = '<?= $coverage['partners'] ?>'>0</div>
<p class = 'mt-1 text-[11px] text-slate-500'>Funding & implementation</p>
</div>
</div>
</div>
</div>
</div>

<!-- Sector Cards Grid -->
<div class = 'grid gap-6 lg:gap-8 md:grid-cols-2 lg:grid-cols-3'>
<?php
$sectorMapping = [
'Health' => ['key' => 'health', 'label' => 'Healthcare Services', 'icon' => 'fa-heart-pulse'],
'Skill Development' => ['key' => 'skills', 'label' => 'Education & Skills', 'icon' => 'fa-graduation-cap'],
'Food & Relief Distribution' => ['key' => 'food', 'label' => 'Humanitarian Aid', 'icon' => 'fa-hand-holding-heart'],
'WASH' => ['key' => 'wash', 'label' => 'Water & Sanitation', 'icon' => 'fa-faucet-drip'],
];
$sectorColors = [
'health'      => ['primary' => 'rose-500', 'light' => 'rose-50', 'dark' => 'rose-700', 'iconBg' => 'bg-rose-50 text-rose-600', 'bar' => 'bg-rose-500'],
'skills'      => ['primary' => 'violet-500', 'light' => 'violet-50', 'dark' => 'violet-700', 'iconBg' => 'bg-violet-50 text-violet-600', 'bar' => 'bg-violet-500'],
'food'        => ['primary' => 'amber-500', 'light' => 'amber-50', 'dark' => 'amber-700', 'iconBg' => 'bg-amber-50 text-amber-600', 'bar' => 'bg-amber-500'],
'wash'        => ['primary' => 'cyan-500', 'light' => 'cyan-50', 'dark' => 'cyan-700', 'iconBg' => 'bg-cyan-50 text-cyan-600', 'bar' => 'bg-cyan-500'],
];

$sectorStatMap = [
'Health' => ['key' => 'health', 'label' => 'Health', 'sub' => 'Medical camps, clinics & emergency care'],
'Skill Development' => ['key' => 'skills', 'label' => 'Skill Development', 'sub' => 'Vocational, technical & paramedic training'],
'Food & Relief Distribution' => ['key' => 'food', 'label' => 'Food & Relief Distribution', 'sub' => 'Food packages, emergency kits & relief supplies'],
'WASH' => ['key' => 'wash', 'label' => 'WASH', 'sub' => 'Clean water, sanitation & hygiene'],
];

$index = 0;
foreach ($dashSectors as $sector):
$sectorName = $sector['sector_name'] ?? '';
if (!isset($sectorMapping[$sectorName])) continue;
$mapping = $sectorMapping[$sectorName];
$stat = $sectorStatMap[$sectorName];
$colors = $sectorColors[$mapping['key']];
$isLast = ($index === count($dashSectors) - 1 && count($dashSectors) % 3 === 1);
$spanClass = $isLast ? 'lg:col-span-3 lg:max-w-4xl lg:mx-auto' : '';
?>
<article class = 'group relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-<?= str_replace('500', '200', $colors['primary']) ?> hover:shadow-xl <?= $spanClass ?>' data-sector = '<?= $mapping['key'] ?>'>
<span class = 'absolute inset-x-0 top-0 h-1 <?= $colors['bar'] ?>'></span>
<div class = 'absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-gradient-to-b from-<?= $colors['primary'] ?>/5 to-transparent'></div>

<div class = 'relative p-6 lg:p-7'>
<div class = 'flex items-start gap-4 mb-5'>
<div class = 'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl <?= $colors['iconBg'] ?> transition-transform duration-300 group-hover:scale-105'>
<i class = 'fa-solid <?= $mapping['icon'] ?> text-xl'></i>
</div>
<div class = 'flex-1 min-w-0'>
<span class = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.1em] <?= $colors['light'] ?> text-<?= str_replace('500', '700', $colors['primary']) ?>'>
<span class = 'h-1.5 w-1.5 rounded-full <?= str_replace('500', '', $colors['primary']) ?> animate-pulse'></span>
<?= $mapping['label'] ?>
</span>
</div>
</div>

<h3 class = 'text-lg font-bold leading-snug text-ink-900 mb-2'><?= $sectorName ?></h3>
<p class = 'text-sm leading-relaxed text-slate-600 mb-5'>
<?php
$descriptions = [
'Health' => 'Delivering essential healthcare, medical camps, and hospital services to underserved communities across Pakistan.',
'Skill Development' => 'Vocational training, paramedic courses, and women\'s skills programs building sustainable livelihoods.',
'Food & Relief Distribution' => 'Emergency food packages, relief supplies, and humanitarian assistance for families in crisis.',
'WASH' => 'Clean water access, hand pump installation, and sanitation infrastructure for rural communities.',
];
echo $descriptions[$sectorName] ?? '';
?>
</p>

<div class = 'relative rounded-2xl border border-slate-100 bg-<?= str_replace('500', '50', $colors['primary']) ?> p-4 lg:p-5 transition-all duration-300 group-hover:border-<?= str_replace('500', '200', $colors['primary']) ?>'>
<div class = 'flex items-end justify-between gap-4 flex-wrap'>
<div class = 'flex-1 min-w-[140px]'>
<p class = 'text-[10px] font-semibold uppercase tracking-[0.16em] text-<?= str_replace('500', '500', $colors['primary']) ?>'>Live Beneficiary Count</p>
<div class = 'mt-1 flex items-baseline gap-2'>
<div class = 'counter-val font-display text-3xl lg:text-4xl font-bold leading-none text-ink-900' data-target = '<?= $beneficiaries[$stat['key']] ?>'>0</div>
<span class = 'text-sm font-medium text-slate-400'>+</span>
</div>
<p class = 'mt-2 text-[11px] leading-4 text-slate-500'><?= $stat['sub'] ?></p>
</div>
<div class = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-lg font-bold text-<?= str_replace('500', '600', $colors['primary']) ?> ring-1 ring-slate-100'>
<i class = 'fa-solid <?= $mapping['icon'] ?>'></i>
</div>
</div>

<div class = 'mt-4 h-1.5 bg-slate-100 rounded-full overflow-hidden'>
<div class = 'h-full <?= $colors['bar'] ?> rounded-full transition-all duration-1000' style = 'width: 0%' data-progress-width = '100%'></div>
</div>
</div>

<div class = 'mt-5 pt-4 border-t border-slate-100'>
<a href = '<?= $base ?>projects/index.php?sector=<?= strtolower($mapping['key']) ?>' class = 'inline-flex items-center gap-1.5 text-sm font-medium text-<?= str_replace('500', '600', $colors['primary']) ?> hover:text-<?= str_replace('500', '700', $colors['primary']) ?> transition-colors'>
Explore <?= $mapping['label'] ?> Projects
<i class = 'fa-solid fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1'></i>
</a>
</div>
</div>
</article>
<?php
$index++;
endforeach;

// Add Provinces/Geographic Reach card if not present
$hasProvinces = false;
foreach ($dashSectors as $sector) {
if (($sector['sector_name'] ?? '') === 'Geographic Reach') $hasProvinces = true;
}
if (!$hasProvinces):
$colors = ['primary' => 'teal-500', 'light' => 'teal-50', 'dark' => 'teal-700', 'iconBg' => 'bg-teal-50 text-teal-600', 'bar' => 'bg-teal-500'];
?>
<article class = 'group relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-teal-200 hover:shadow-xl' data-sector = 'provinces'>
<span class = 'absolute inset-x-0 top-0 h-1 bg-teal-500'></span>
<div class = 'absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-gradient-to-b from-teal-500/5 to-transparent'></div>

<div class = 'relative p-6 lg:p-7'>
<div class = 'flex items-start gap-4 mb-5'>
<div class = 'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 transition-transform duration-300 group-hover:scale-105'>
<i class = 'fa-solid fa-map-location-dot text-xl'></i>
</div>
<div class = 'flex-1 min-w-0'>
<span class = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.1em] bg-teal-50 text-teal-700'>
<span class = 'h-1.5 w-1.5 rounded-full bg-teal-500 animate-pulse'></span>
Geographic Reach
</span>
</div>
</div>

<h3 class = 'text-lg font-bold leading-snug text-ink-900 mb-2'>Provinces Covered</h3>
<p class = 'text-sm leading-relaxed text-slate-600 mb-5'>Active programme presence across Pakistan\'s provinces, delivering services where they are needed most.</p>

<div class = 'relative rounded-2xl border border-slate-100 bg-teal-50 p-4 lg:p-5 transition-all duration-300 group-hover:border-teal-200'>
<div class = 'flex items-end justify-between gap-4 flex-wrap'>
<div class = 'flex-1 min-w-[140px]'>
<p class = 'text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-500'>Live Coverage Count</p>
<div class = 'mt-1 flex items-baseline gap-2'>
<div class = 'counter-val font-display text-3xl lg:text-4xl font-bold leading-none text-ink-900' data-target = '<?= $coverage['provinces'] ?>'>0</div>
<span class = 'text-sm font-medium text-slate-400'>+</span>
</div>
<p class = 'mt-2 text-[11px] leading-4 text-slate-500'>Across our project areas</p>
</div>
<div class = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-lg font-bold text-teal-600 ring-1 ring-slate-100'>
<i class = 'fa-solid fa-map-location-dot'></i>
</div>
</div>

<div class = 'mt-4 h-1.5 bg-slate-100 rounded-full overflow-hidden'>
<div class = 'h-full bg-teal-500 rounded-full transition-all duration-1000' style = 'width: 0%' data-progress-width = '100%'></div>
</div>
</div>

<div class = 'mt-5 pt-4 border-t border-slate-100'>
<a href = '<?= $base ?>where-we-work/index.php' class = 'inline-flex items-center gap-1.5 text-sm font-medium text-teal-600 hover:text-teal-700 transition-colors'>
Explore Geographic Reach
<i class = 'fa-solid fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1'></i>
</a>
</div>
</div>
</article>
<?php endif; ?>
</div>

<!-- Methodology Note -->
<div class = 'mt-10 lg:mt-12 mx-auto max-w-5xl'>
<div class = 'rounded-2xl border border-slate-200 bg-white p-5 lg:p-6'>
<div class = 'flex items-start gap-4'>
<div class = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brandRed/10 text-brandRed'>
<i class = 'fa-solid fa-circle-info text-sm'></i>
</div>
<div class = 'flex-1'>
<h4 class = 'font-semibold text-ink-900'>Methodology & Data Integrity</h4>
<p class = 'mt-2 text-sm leading-relaxed text-slate-600'>
Beneficiary figures are aggregated from the <strong>projects</strong> table — each active project contributes its reported <code class = 'bg-slate-100 px-1.5 py-0.5 rounded text-[11px] font-mono'>beneficiaries_count</code>.
Sector totals follow WRC's approved programme mapping (Health, WASH, Food & Relief Distribution, Skill Development).
The total reflects <em>unique people reached</em>, not service contacts — a beneficiary counted in multiple projects appears once in the headline total.
Data refreshes automatically when projects are added, edited, or completed in the dashboard.
</p>
</div>
</div>
</div>
</div>
</div>
</section>

<!-- OUR APPROACH TO IMPACT ( 4 PILLARS ) -->
<section class = 'py-12 lg:py-12 bg-white border-t border-slate-200/50'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center max-w-3xl mx-auto mb-8'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'><?= sanitize( $owSections[ 'our_work_pillars' ][ 'section_subtitle' ] ?? 'Our Strategy' ) ?></span>
<h2 class = 'text-3xl font-extrabold font-heading text-slate-900 mb-4'><?= $owSections[ 'our_work_pillars' ][ 'section_title' ] ?? '' ?></h2>
<p class = 'text-slate-600 text-sm'><?= sanitize( $pillarsData[ 'subtitle' ] ?? '' ) ?></p>
</div>

<div class = 'grid grid-cols-1 md:grid-cols-4 gap-6'>
<?php foreach ( $pillarsData[ 'pillars' ] ?? [] as $pillar ): ?>
<div class = 'p-8 bg-slate-50 rounded-2xl border border-slate-100/80 shadow-sm hover:shadow-md transition-shadow'>
<div class = 'w-12 h-12 rounded-xl bg-red-50 text-brandRed flex items-center justify-center text-xl font-bold mb-6 font-heading'><?= $pillar[ 'num' ] ?></div>
<h4 class = 'font-bold text-slate-900 mb-3 text-sm font-heading'><?= sanitize( $pillar[ 'title' ] ) ?></h4>
<p class = 'text-xs text-slate-600 leading-relaxed'><?= sanitize( $pillar[ 'description' ] ) ?></p>
</div>
<?php endforeach;
?>
</div>
</div>
</section>

<!-- FEATURED PROJECTS GRID SECTION -->
<section id = 'featured' class = 'py-12 bg-slate-50 border-t border-slate-200/50 scroll-mt-24'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>

<div class = 'flex flex-col md:flex-row md:items-end justify-between mb-8'>
<div>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'><?= sanitize( $owSections[ 'our_work_featured_projects' ][ 'section_subtitle' ] ?? 'Action in the Field' ) ?></span>
<h2 class = 'text-3xl font-extrabold font-heading text-slate-900'><?= $owSections[ 'our_work_featured_projects' ][ 'section_title' ] ?? 'Featured Projects' ?></h2>
</div>
<p class = 'text-slate-600 text-xs md:text-sm max-w-md mt-4 md:mt-0 leading-relaxed'>
<?= sanitize( $featuredProjData[ 'description' ] ?? 'Our latest active projects making a real difference in communities.' ) ?>
</p>
</div>

<div class = 'grid grid-cols-1 md:grid-cols-3 gap-8'>
<?php foreach ( $featuredProjects as $project ): ?>
<a href = "<?= $base ?>projects/project-name.php?slug=<?= urlencode($project['slug']) ?>" class = 'bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col justify-between group hover:shadow-xl transition-all duration-300'>
<div class = 'relative h-56 overflow-hidden'>
<img src = "<?= $base ?><?= $project['hero_image'] ?? 'assets/images/water.jpg' ?>" alt = "<?= sanitize($project['title']) ?>" class = 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500'>
<span class = "absolute top-4 left-4 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 <?= $project['status'] === 'active' ? 'bg-emerald-500' : 'bg-slate-600' ?> text-white rounded-full shadow-md"><?= ucfirst( $project[ 'status' ] ) ?></span>
</div>
<div class = 'p-6 flex-grow flex flex-col justify-between'>
<div>
<div class = 'flex items-center gap-2 text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-2'>
<i class = 'fa-solid fa-location-dot text-brandRed'></i> <?= sanitize( $project[ 'location_details' ] ?? 'Pakistan' ) ?>
</div>
<h3 class = 'text-lg font-bold text-slate-900 font-heading mb-3 group-hover:text-brandRed transition-colors'><?= sanitize( $project[ 'title' ] ) ?></h3>
<p class = 'text-xs text-slate-600 leading-relaxed mb-6 line-clamp-2'><?= sanitize( $project[ 'description' ] ?? '' ) ?></p>
</div>
<div class = 'pt-4 border-t border-slate-100 flex items-center justify-between'>
<span class = 'text-[10px] text-slate-400'>Since <?= date( 'Y', strtotime( $project[ 'start_date' ] ) ) ?></span>
<span class = 'text-brandRed hover:text-brandRed-hover text-xs font-semibold hover:underline flex items-center gap-1 group-hover:translate-x-1 transition-transform'>
View Details <i class = 'fa-solid fa-arrow-right text-[10px]'></i>
</span>
</div>
</div>
</a>
<?php endforeach;
?>
</div>

</div>
</section>

<!-- CTA BANNER -->
<section class = 'py-12 bg-slate-900 text-white relative overflow-hidden'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center max-w-3xl'>
<h2 class = 'text-3xl md:text-4xl font-extrabold font-heading mb-4'><?= $owSections[ 'our_work_cta' ][ 'section_title' ] ?? '' ?></h2>
<p class = 'text-slate-300 text-sm mb-8 leading-relaxed'><?= sanitize( $ctaData[ 'description' ] ?? '' ) ?></p>
<div class = 'flex flex-wrap items-center justify-center gap-4'>
<a href = "<?= $base ?><?= $ctaData['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>" class = 'bg-brandRed hover:bg-brandRed-hover text-white font-bold text-xs px-6 py-3.5 rounded-xl shadow-lg transition-all flex items-center gap-2 uppercase tracking-wider'>
<i class = "<?= $ctaData['button1_icon'] ?? 'fa-solid fa-handshake' ?>"></i> <?= sanitize( $ctaData[ 'button1_text' ] ?? 'Let’s Work Together' ) ?>
</a>
<a href = "<?= $base ?><?= $ctaData['button2_link'] ?? 'contact/index.php' ?>" class = 'bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs px-6 py-3.5 rounded-xl border border-slate-700 transition-all flex items-center gap-2 uppercase tracking-wider'>
<i class = "<?= $ctaData['button2_icon'] ?? 'fa-solid fa-envelope' ?>"></i> <?= sanitize( $ctaData[ 'button2_text' ] ?? 'Contact Our Team' ) ?>
</a>
</div>
</div>
</section>

</main>

<!-- Counter Animation -->
<?php
$counterScript = <<<'JS'
<script>
  (function () {
    const counters = document.querySelectorAll('.counter-val');
    const progressBars = document.querySelectorAll('[data-progress-width]');

    function animateCounter(el) {
      const target = parseInt(el.getAttribute('data-target'), 10);
      const duration = 2000;
      const startTime = performance.now();
      const suffix = target >= 1000 ? '+' : '';

      function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
      }

      function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeOutExpo(progress);
        const current = Math.floor(easedProgress * target);

        if (target >= 1000) {
          el.textContent = current.toLocaleString() + suffix;
        } else {
          el.textContent = current + suffix;
        }

        if (progress < 1) {
          requestAnimationFrame(update);
        }
      }

      requestAnimationFrame(update);
    }

    // Animate progress bars
    function animateProgressBars() {
      progressBars.forEach(function(bar) {
        const targetWidth = bar.getAttribute('data-progress-width');
        bar.style.width = targetWidth;
      });
    }

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          // Animate counter
          if (entry.target.classList.contains('counter-val')) {
            animateCounter(entry.target);
          }
          // Animate progress bars when the dashboard section is visible
          if (entry.target.id === 'dashboard') {
            animateProgressBars();
          }
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.2,
      rootMargin: '0px 0px -50px 0px'
    });

    counters.forEach(function (counter) {
      observer.observe(counter);
    });

    // Also observe the dashboard section for progress bars
    const dashboardSection = document.getElementById('dashboard');
    if (dashboardSection) {
      observer.observe(dashboardSection);
    }
  })();
</script>
JS;
?>

<?php
$extraScripts = $counterScript;
include dirname( __DIR__ ) . '/includes/footer.php';
?>
