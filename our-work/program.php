<?php
/**
* WRC Our Work - Single Program Detail ( Dynamic )
* Usage: program.php?slug = health
*/
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$slug = $_GET[ 'slug' ] ?? '';
if ( empty( $slug ) ) {
    header( 'Location: index.php' );
    exit;
}

$program = getProgramBySlug( $slug );
if ( !$program ) {
    header( 'Location: index.php' );
    exit;
}

$base = siteBaseUrl();
$pageTitle = sanitize( $program[ 'full_title' ] ) . ' | WRC Our Work';
$metaDescription = sanitize( $program[ 'hero_description' ] );

$interventions = getRows( 'program_interventions', [ 'program_id' => $program[ 'id' ] ], 'sort_order ASC' );
$focusAreas = json_decode( $program[ 'focus_areas' ], true ) ?? [];
$targetGroups = json_decode( $program[ 'target_groups' ], true ) ?? [];
$relatedSlugs = json_decode( $program[ 'related_programs' ], true ) ?? [];

// Get related programs
$relatedPrograms = [];
if ( !empty( $relatedSlugs ) ) {
    $placeholders = implode( ',', array_fill( 0, count( $relatedSlugs ), '?' ) );
    global $pdo;
    $stmt = $pdo->prepare( "SELECT * FROM programs WHERE slug IN ($placeholders) AND is_active = 1" );
    $stmt->execute( $relatedSlugs );
    $relatedPrograms = $stmt->fetchAll();
}

// Get projects for this program
$projects = getRows( 'projects', [ 'program_id' => $program[ 'id' ], 'is_active' => 1 ], 'sort_order ASC', 3 );

// Get all programs for navigation
$allPrograms = getRows( 'programs', 'is_active = 1 ORDER BY sort_order ASC' );

// Fetch program detail sections from DB
$allSections = getRows( 'homepage_sections', '', '' );
$pgSections = [];
foreach ( $allSections as $s ) {
    if ( str_starts_with( $s[ 'section_key' ], 'program_' ) ) {
        $pgSections[ $s[ 'section_key' ] ] = $s;
    }
}
$implData = isset( $pgSections[ 'program_implementation' ] ) ? json_decode( $pgSections[ 'program_implementation' ][ 'content' ], true ) : [];
$ctaData = isset( $pgSections[ 'program_cta' ] ) ? json_decode( $pgSections[ 'program_cta' ][ 'content' ], true ) : [];

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>

<!-- BREADCRUMBS -->
<div class = 'bg-slate-100 border-b border-slate-200/60 py-3'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<nav class = 'flex items-center gap-2 text-xs text-slate-500'>
<a href = "<?= $base ?>index.php" class = 'hover:text-brandRed transition'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<a href = 'index.php' class = 'hover:text-brandRed transition'>Our Work</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<span class = 'text-slate-900 font-medium'><?= sanitize( $program[ 'title' ] ) ?></span>
</nav>
</div>
</div>

<!-- PROGRAM HERO -->
<section class = 'relative bg-slate-900 text-white min-h-[380px] flex items-center py-16 overflow-hidden'>
<div class = 'absolute inset-0 bg-cover bg-center opacity-40 mix-blend-luminosity' style = "background-image: url('<?= htmlspecialchars(getImageUrl($program['hero_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>');"></div>
<div class = 'absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/65'></div>

<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 relative z-10'>
<div class = 'max-w-3xl'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-3 block'>Our Work / <?= sanitize( $program[ 'title' ] ) ?></span>
<h1 class = 'text-3xl md:text-4xl lg:text-5xl font-extrabold font-heading text-white mb-4 leading-tight'>
<?= sanitize( $program[ 'full_title' ] ) ?>
</h1>
<p class = 'text-slate-300 text-sm md:text-base leading-relaxed max-w-2xl'>
<?= sanitize( $program[ 'hero_description' ] ) ?>
</p>
</div>
</div>
</section>

<!-- OVERVIEW & WHY IT MATTERS -->
<section class = 'py-16 lg:py-24 bg-white'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center'>

<div class = 'lg:col-span-7 space-y-6'>
<div>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'>Strategic Overview</span>
<h2 class = 'text-2xl md:text-3xl font-extrabold font-heading text-slate-900 mb-4'>Program Overview & Objectives</h2>
<p class = 'text-slate-600 text-sm leading-relaxed'>
<?= sanitize( $program[ 'overview' ] ) ?>
</p>
</div>

<div class = 'pt-4 border-t border-slate-100'>
<h3 class = 'text-lg font-bold text-slate-900 font-heading mb-3'>Why This Matters</h3>
<p class = 'text-slate-600 text-sm leading-relaxed'>
<?= sanitize( $program[ 'why_it_matters' ] ) ?>
</p>
</div>

<div class = 'pt-4'>
<h4 class = 'text-xs font-bold text-slate-500 uppercase tracking-wider mb-3'>Key Focus Areas</h4>
<div class = 'flex flex-wrap gap-2'>
<?php foreach ( $focusAreas as $area ): ?>
<span class = 'px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-medium rounded-full'><?= sanitize( $area ) ?></span>
<?php endforeach;
?>
</div>
</div>
</div>

<div class = 'lg:col-span-5 relative'>
<div class = 'relative rounded-3xl overflow-hidden shadow-xl'>
<img src = "<?= htmlspecialchars(getImageUrl($program['hero_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt = "<?= sanitize($program['title']) ?>" class = 'w-full h-[350px] object-cover'>
<div class = 'absolute inset-0 bg-slate-950/10'></div>
</div>
<div class = 'absolute -bottom-4 -right-4 w-32 h-32 bg-slate-50 rounded-3xl -z-10 hidden sm:block'></div>
</div>

</div>
</div>
</section>

<!-- WHAT WE DO ( INTERVENTIONS ) -->
<?php if ( !empty( $interventions ) ): ?>
<section class = 'py-16 lg:py-20 bg-slate-50 border-t border-slate-200/60'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center max-w-3xl mx-auto mb-16'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'>Our Actions</span>
<h2 class = 'text-2xl md:text-3xl font-extrabold font-heading text-slate-900'>Key Program Interventions</h2>
<p class = 'text-slate-600 text-sm mt-2'>The core activities and strategic services WRC provides under this sector.</p>
</div>

<div class = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8'>
<?php foreach ( $interventions as $intervention ): ?>
<div class = 'bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm hover:shadow-md transition-all'>
<div class = 'w-11 h-11 rounded-xl bg-red-50 text-brandRed flex items-center justify-center text-lg mb-4'>
<i class = "<?= $intervention['icon_class'] ?>"></i>
</div>
<h3 class = 'text-base font-bold text-slate-900 mb-2 font-heading'><?= sanitize( $intervention[ 'title' ] ) ?></h3>
<p class = 'text-xs text-slate-600 leading-relaxed'><?= sanitize( $intervention[ 'description' ] ) ?></p>
</div>
<?php endforeach;
?>
</div>
</div>
</section>
<?php endif;
?>

<!-- HOW WE IMPLEMENT -->
<section class = 'py-16 bg-white border-t border-slate-200/60'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center max-w-3xl mx-auto mb-16'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'><?= $pgSections[ 'program_implementation' ][ 'section_subtitle' ] ?? 'Implementation Path' ?></span>
<h2 class = 'text-2xl md:text-3xl font-extrabold font-heading text-slate-900'><?= $pgSections[ 'program_implementation' ][ 'section_title' ] ?? '' ?></h2>
<p class = 'text-slate-600 text-sm mt-2'><?= sanitize( $implData[ 'subtitle' ] ?? '' ) ?></p>
</div>

<div class = 'grid grid-cols-1 md:grid-cols-4 gap-6'>
<?php foreach ( $implData[ 'steps' ] ?? [] as $step ): ?>
<div class = 'p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center'>
<div class = 'w-10 h-10 rounded-xl bg-red-50 text-brandRed flex items-center justify-center text-base font-bold mx-auto mb-4 font-heading'><?= $step[ 'num' ] ?></div>
<h4 class = 'font-bold text-slate-900 mb-2 text-sm'><?= sanitize( $step[ 'title' ] ) ?></h4>
<p class = 'text-xs text-slate-600 leading-relaxed'><?= sanitize( $step[ 'description' ] ) ?></p>
</div>
<?php endforeach;
?>
</div>
</div>
</section>

<!-- WHO WE SERVE -->
<?php if ( !empty( $targetGroups ) ): ?>
<section class = 'py-16 bg-slate-50 border-t border-slate-200/60'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 text-center'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'>Target Groups</span>
<h2 class = 'text-2xl md:text-3xl font-extrabold font-heading text-slate-900 mb-4'>Who We Serve</h2>
<p class = 'text-slate-600 text-sm max-w-2xl mx-auto mb-8'>We focus our activities on helping the most vulnerable community segments.</p>

<div class = 'flex flex-wrap justify-center gap-3 max-w-3xl mx-auto'>
<?php foreach ( $targetGroups as $group ): ?>
<span class = 'px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-full shadow-sm'><?= sanitize( $group ) ?></span>
<?php endforeach;
?>
</div>
</div>
</section>
<?php endif;
?>

<!-- PROGRAM PROJECTS -->
<?php if ( !empty( $projects ) ): ?>
<section class = 'py-16 bg-white border-t border-slate-200/60'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center max-w-3xl mx-auto mb-16'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'>Field Work</span>
<h2 class = 'text-2xl md:text-3xl font-extrabold font-heading text-slate-900'>Featured Projects</h2>
<p class = 'text-slate-600 text-sm mt-2'>Ongoing and completed initiatives addressing local needs.</p>
</div>

<div class = 'flex flex-col items-center gap-6'>
<?php foreach ( $projects as $project ): ?>
<div class = 'bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col md:flex-row group hover:shadow-xl transition-all duration-300 w-full max-w-3xl'>
<div class = 'relative h-56 md:h-auto md:w-80 overflow-hidden shrink-0'>
<img src = "<?= htmlspecialchars(getImageUrl($project['hero_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt = "<?= sanitize($project['title']) ?>" class = 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500'>
<span class = 'absolute top-4 left-4 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 bg-brandRed text-white rounded-full shadow-md'>Active</span>
</div>
<div class = 'p-6 flex-grow flex flex-col justify-between'>
<div>
<h3 class = 'text-lg font-bold text-slate-900 font-heading mb-2 group-hover:text-brandRed transition-colors'><?= sanitize( $project[ 'title' ] ) ?></h3>
<p class = 'text-xs text-slate-600 leading-relaxed mb-4'><?= sanitize( $project[ 'description' ] ) ?></p>
</div>
<div class = 'flex items-center justify-between text-xs text-slate-500'>
<span><i class = 'fa-solid fa-location-dot mr-1'></i><?= sanitize( $project[ 'location_details' ] ) ?></span>
<span><?= number_format( $project[ 'beneficiaries_count' ] ) ?> beneficiaries</span>
</div>
</div>
</div>
<?php endforeach;
?>
</div>
</div>
</section>
<?php endif;
?>

<!-- RELATED PROGRAMS -->
<?php if ( !empty( $relatedPrograms ) ): ?>
<section class = 'py-16 bg-slate-50 border-t border-slate-200/60'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'text-center max-w-3xl mx-auto mb-16'>
<span class = 'text-brandRed font-bold text-xs uppercase tracking-widest mb-2 block'>Interconnected Sectors</span>
<h2 class = 'text-2xl md:text-3xl font-extrabold font-heading text-slate-900'>Related Programs</h2>
<p class = 'text-slate-600 text-sm mt-2'>Explore other related WRC sectors that operate in alignment with this program.</p>
</div>

<div class = 'grid grid-cols-1 md:grid-cols-3 gap-6'>
<?php foreach ( $relatedPrograms as $related ): ?>
<a href = "program.php?slug=<?= $related['slug'] ?>" class = 'bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden group hover:shadow-xl transition-all duration-300'>
<div class = 'relative h-44 overflow-hidden'>
<img src = "<?= htmlspecialchars(getImageUrl($related['hero_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt = "<?= sanitize($related['title']) ?>" class = 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500'>
<div class = 'absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent'></div>
<span class = 'absolute top-3 right-3 text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 bg-slate-900/80 text-white backdrop-blur-md rounded-full'>Sector <?= $related[ 'sector_number' ] ?></span>
</div>
<div class = 'p-5'>
<div class = 'flex items-center gap-2 mb-2'>
<div class = 'w-8 h-8 rounded-lg bg-red-50 text-brandRed flex items-center justify-center text-sm'>
<i class = "<?= $related['icon_class'] ?>"></i>
</div>
<h3 class = 'text-base font-bold text-slate-900 font-heading'><?= sanitize( $related[ 'title' ] ) ?></h3>
</div>
<p class = 'text-xs text-slate-600 leading-relaxed line-clamp-2'><?= sanitize( $related[ 'short_description' ] ) ?></p>
</div>
</a>
<?php endforeach;
?>
</div>
</div>
</section>
<?php endif;
?>

<!-- CTA BANNER -->
<section class = 'py-16 bg-slate-900 text-white relative overflow-hidden'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl relative z-10'>
<h2 class = 'text-2xl md:text-3xl font-extrabold font-heading mb-3'><?= $pgSections[ 'program_cta' ][ 'section_title' ] ?? '' ?></h2>
<p class = 'text-slate-300 text-sm mb-6 leading-relaxed'><?= sanitize( $ctaData[ 'description' ] ?? '' ) ?></p>
<div class = 'flex flex-wrap items-center justify-center gap-3'>
<a href = "<?= $base ?><?= $ctaData['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>" class = 'bg-brandRed hover:bg-brandRed-hover text-white font-bold text-xs px-5 py-3.5 rounded-xl shadow-lg transition flex items-center gap-2 uppercase tracking-wider'>
<i class = "<?= $ctaData['button1_icon'] ?? 'fa-solid fa-handshake' ?>"></i> <?= sanitize( $ctaData[ 'button1_text' ] ?? 'Support Initiatives' ) ?>
</a>
<a href = "<?= $base ?><?= $ctaData['button2_link'] ?? 'contact/index.php' ?>" class = 'bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs px-5 py-3.5 rounded-xl border border-slate-700 transition flex items-center gap-2 uppercase tracking-wider'>
<i class = "<?= $ctaData['button2_icon'] ?? 'fa-solid fa-envelope' ?>"></i> <?= sanitize( $ctaData[ 'button2_text' ] ?? 'Contact Us' ) ?>
</a>
</div>
</div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
