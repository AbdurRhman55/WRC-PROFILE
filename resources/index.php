<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Resources | WRC';
$metaDescription = 'Access WRC audit reports, policies, and registration documents. Transparency and accountability at every level.';

// Fetch hero from dedicated table
$heroRow = getRow( 'resources_hero' );
$heroData = [];
if ( $heroRow ) {
    $decoded = json_decode( $heroRow[ 'content' ] ?? '{}', true );
    $heroData = is_array( $decoded ) ? $decoded : [];
}

// Fetch resources by type
$auditReports = getRows( 'resources', [ 'resource_type' => 'audit_report', 'is_active' => 1 ], 'year DESC' );

// Fetch registration documents from resources table
$registrations = getRows( 'resources', [ 'resource_type' => 'download', 'is_active' => 1 ], 'sort_order ASC' );

// Fetch policies
$policies = getRows( 'resources', [ 'resource_type' => 'policy', 'is_active' => 1 ], 'sort_order ASC' );

// Dynamic stats for hero
$stats = [
    [ 'label' => 'Audit Reports', 'value' => count( $auditReports ) . '+', 'icon' => 'fa-magnifying-glass-chart' ],
    [ 'label' => 'Policies', 'value' => count( $policies ) . '+', 'icon' => 'fa-shield-halved' ],
    [ 'label' => 'Registrations', 'value' => count( $registrations ) . '+', 'icon' => 'fa-stamp' ],
];

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>

<!-- HERO SECTION -->
<section class = 'relative w-full bg-slate-900 text-white overflow-hidden min-h-[480px] md:min-h-[540px] lg:min-h-[580px] flex items-center'>
<div class = 'absolute inset-0'>
<?php
$heroImg = $heroData[ 'hero_image' ] ?? 'assets/images/hero/resources-hero.jpg';
if ( strpos( $heroImg, 'assets/' ) !== 0 && strpos( $heroImg, 'http' ) !== 0 && strpos( $heroImg, 'uploads/' ) !== 0 ) {
    $heroImg = 'assets/images/' . $heroImg;
}
?>
<img src = "<?= $base ?><?= $heroImg ?>" alt = 'Resources' class = 'absolute inset-0 w-full h-full object-cover'>
<div class = 'absolute inset-0 bg-gradient-to-br from-slate-950/70 via-slate-900/50 to-brandRed/20'></div>
<div class = 'absolute top-20 left-10 w-72 h-72 bg-brandRed/5 rounded-full blur-3xl animate-pulse'></div>
<div class = 'absolute bottom-10 right-10 w-96 h-96 bg-violet-500/5 rounded-full blur-3xl animate-pulse' style = 'animation-delay:1s'></div>
</div>
<div class = 'relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24'>
<div class = 'max-w-3xl mx-auto text-center'>
<nav class = 'text-xs text-white/50 mb-6 flex items-center gap-2 justify-center'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<span class = 'text-white/80'>Resources</span>
</nav>
<div class = 'inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-[11px] font-semibold uppercase tracking-widest px-4 py-2 rounded-full mb-6'>
<i class = 'fa-solid fa-folder-open text-white text-xs'></i> <?= $heroData[ 'tagline' ] ?? 'Transparency & Accountability' ?>
</div>
<h1 class = 'text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6' style = "font-family:'Fraunces',serif;">
<?= $heroRow[ 'section_title' ] ?: ( $heroData[ 'title' ] ?? 'Resources & Documents' ) ?>
</h1>
<p class = 'text-lg md:text-xl text-white/70 leading-relaxed mb-10 max-w-2xl mx-auto'>
<?= $heroRow[ 'section_subtitle' ] ?: ( $heroData[ 'subtitle' ] ?? 'Access our audit reports, policies, and registration documents.' ) ?>
</p>
<!-- Dynamic Stats Row -->
<div class = 'grid grid-cols-3 gap-4 mb-10 max-w-xl mx-auto'>
<?php foreach ( $stats as $stat ): ?>
<div class = 'bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-3 text-center hover:bg-white/15 transition-all'>
<i class = "fa-solid <?= $stat['icon'] ?> text-white/80 text-sm mb-1"></i>
<div class = 'text-xl font-extrabold text-white' style = "font-family:'Fraunces',serif;"><?= $stat[ 'value' ] ?></div>
<div class = 'text-[10px] text-white/60 font-medium uppercase tracking-wider'><?= $stat[ 'label' ] ?></div>
</div>
<?php endforeach;
?>
</div>
<div class = 'flex flex-wrap justify-center gap-4'>
<a href = '#audit-reports' class = 'bg-white text-slate-900 hover:bg-slate-100 font-bold text-sm px-8 py-4 rounded-xl shadow-lg transition-all flex items-center gap-2 transform hover:-translate-y-0.5'>
<i class = 'fa-solid fa-magnifying-glass-chart text-brandRed'></i> Audit Reports
</a>
<a href = '#policies' class = 'bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold text-sm px-8 py-4 rounded-xl backdrop-blur-sm transition-all flex items-center gap-2 transform hover:-translate-y-0.5'>
<i class = 'fa-solid fa-shield-halved'></i> Policies
</a>
<a href = '#registration' class = 'bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold text-sm px-8 py-4 rounded-xl backdrop-blur-sm transition-all flex items-center gap-2 transform hover:-translate-y-0.5'>
<i class = 'fa-solid fa-stamp'></i> Registrations
</a>
</div>
</div>
</div>
</section>

<!-- QUICK NAV -->
<section class = 'bg-white border-b border-slate-200 sticky top-20 z-40'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex overflow-x-auto gap-1 py-2 scrollbar-hide' style = 'scrollbar-width:none;'>
<a href = '#audit-reports' class = 'shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap'><i class = 'fa-solid fa-magnifying-glass-chart mr-1.5 text-amber-500'></i> Audit Reports</a>
<a href = '#policies' class = 'shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap'><i class = 'fa-solid fa-shield-halved mr-1.5 text-blue-500'></i> Policies</a>
<a href = '#registration' class = 'shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap'><i class = 'fa-solid fa-stamp mr-1.5 text-violet-500'></i> Registrations</a>
</div>
</div>
</section>

<!-- AUDIT REPORTS -->
<section id = 'audit-reports' class = 'py-20 md:py-28 bg-white'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-14'>
<div>
<div class = 'inline-flex items-center gap-2 bg-amber-50 text-amber-600 text-[11px] font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-4'>
<i class = 'fa-solid fa-magnifying-glass-chart'></i> Audit Reports
</div>
<h2 class = 'text-3xl md:text-4xl font-extrabold text-slate-900' style = "font-family:'Fraunces',serif;">External Audit Reports</h2>
<p class = 'mt-3 text-slate-500 text-sm max-w-xl'>Independent external audit reports conducted by certified auditing firms for transparency and compliance.</p>
</div>
<?php if ( !empty( $auditReports ) ): ?>
<div class = 'text-sm text-slate-500 font-medium shrink-0'>
<span class = 'text-amber-600 font-bold'><?= count( $auditReports ) ?></span> reports available
</div>
<?php endif;
?>
</div>

<?php if ( !empty( $auditReports ) ): ?>
<div class = 'grid md:grid-cols-2 lg:grid-cols-4 gap-6'>
<?php foreach ( $auditReports as $report ): ?>
<div class = 'group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-amber-300 transition-all duration-300'>
<div class = 'h-44 bg-gradient-to-br from-amber-50 via-amber-100/50 to-amber-50 flex items-center justify-center relative overflow-hidden'>
<div class = 'absolute inset-0 bg-gradient-to-br from-amber-0 to-amber-50 group-hover:to-amber-100/50 transition-all'></div>
<div class = 'relative text-center'>
<i class = 'fa-solid fa-magnifying-glass-chart text-amber-600 text-4xl mb-2 opacity-80'></i>
<div class = 'text-3xl font-extrabold text-amber-700' style = "font-family:'Fraunces',serif;"><?= sanitize( $report[ 'year' ] ?? '' ) ?></div>
</div>
</div>
<div class = 'p-5'>
<h3 class = 'font-bold text-slate-900 text-sm mb-2 group-hover:text-amber-600 transition-colors'><?= sanitize( $report[ 'title' ] ?? '' ) ?></h3>
<p class = 'text-slate-500 text-xs leading-relaxed mb-4 line-clamp-2'><?= sanitize( $report[ 'description' ] ?? '' ) ?></p>
<div class = 'flex items-center justify-between pt-3 border-t border-slate-100'>
<span class = 'text-[11px] text-slate-400 font-medium'><i class = 'fa-solid fa-file-pdf mr-1 text-amber-600'></i> <?= strtoupper( $report[ 'file_type' ] ?? 'PDF' ) ?></span>
<?php if ( !empty( $report[ 'file_path' ] ) ): ?>
<a href = "<?= $base ?><?= $report['file_path'] ?>" target = '_blank' class = 'inline-flex items-center gap-1.5 bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all'>
<i class = 'fa-solid fa-download text-[10px]'></i> Download
</a>
<?php else: ?>
<span class = 'text-[11px] text-slate-400 font-medium'><i class = 'fa-solid fa-clock mr-1'></i> Coming Soon</span>
<?php endif;
?>
</div>
</div>
</div>
<?php endforeach;
?>
</div>
<?php else: ?>
<div class = 'text-center py-16 bg-slate-50 rounded-2xl border border-slate-200'>
<div class = 'w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4'><i class = 'fa-solid fa-magnifying-glass-chart text-amber-600 text-2xl'></i></div>
<p class = 'text-slate-500 text-sm'>Audit reports will be uploaded soon.</p>
</div>
<?php endif;
?>
</div>
</section>

<!-- POLICIES -->
<section id = 'policies' class = 'py-20 md:py-28 bg-slate-50'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-14'>
<div>
<div class = 'inline-flex items-center gap-2 bg-blue-50 text-blue-600 text-[11px] font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-4'>
<i class = 'fa-solid fa-shield-halved'></i> Organizational Policies
</div>
<h2 class = 'text-3xl md:text-4xl font-extrabold text-slate-900' style = "font-family:'Fraunces',serif;">Policies & Guidelines</h2>
<p class = 'mt-3 text-slate-500 text-sm max-w-xl'>Our organizational policies ensuring good governance, transparency, accountability, and ethical practices across all operations.</p>
</div>
<?php if ( !empty( $policies ) ): ?>
<div class = 'text-sm text-slate-500 font-medium shrink-0'>
<span class = 'text-blue-600 font-bold'><?= count( $policies ) ?></span> policies available
</div>
<?php endif;
?>
</div>

<?php if ( !empty( $policies ) ): ?>
<div class = 'grid md:grid-cols-2 lg:grid-cols-3 gap-6'>
<?php foreach ( $policies as $policy ): ?>
<div class = 'group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-blue-300 transition-all duration-300'>
<div class = 'h-32 bg-gradient-to-br from-blue-50 via-blue-100/50 to-blue-50 flex items-center justify-center relative overflow-hidden'>
<div class = 'absolute inset-0 bg-gradient-to-br from-blue-0 to-blue-50 group-hover:to-blue-100/50 transition-all'></div>
<div class = 'relative text-center'>
<i class = 'fa-solid fa-shield-halved text-blue-600 text-4xl mb-2 opacity-80'></i>
<div class = 'text-sm font-bold text-blue-700 uppercase tracking-wider'><?= strtoupper( $policy[ 'file_type' ] ?? 'PDF' ) ?></div>
</div>
</div>
<div class = 'p-5'>
<h3 class = 'font-bold text-slate-900 text-sm mb-2 group-hover:text-blue-600 transition-colors'><?= sanitize( $policy[ 'title' ] ?? '' ) ?></h3>
<p class = 'text-slate-500 text-xs leading-relaxed mb-4 line-clamp-2'><?= sanitize( $policy[ 'description' ] ?? '' ) ?></p>
<div class = 'flex items-center justify-between pt-3 border-t border-slate-100'>
<span class = 'text-[11px] text-slate-400 font-medium'><i class = "fa-solid fa-file-<?= $policy['file_type'] === 'pdf' ? 'pdf text-red-500' : 'word text-blue-500' ?> mr-1"></i> <?= strtoupper( $policy[ 'file_type' ] ?? 'PDF' ) ?></span>
<?php if ( !empty( $policy[ 'file_path' ] ) ): ?>
<a href = "<?= $base ?><?= $policy['file_path'] ?>" target = '_blank' class = 'inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all'>
<i class = 'fa-solid fa-download text-[10px]'></i> Download
</a>
<?php else: ?>
<span class = 'text-[11px] text-slate-400 font-medium'><i class = 'fa-solid fa-clock mr-1'></i> Coming Soon</span>
<?php endif;
?>
</div>
</div>
</div>
<?php endforeach;
?>
</div>
<?php else: ?>
<div class = 'text-center py-16 bg-white rounded-2xl border border-slate-200'>
<div class = 'w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4'><i class = 'fa-solid fa-shield-halved text-blue-600 text-2xl'></i></div>
<p class = 'text-slate-500 text-sm'>Policies will be uploaded soon.</p>
</div>
<?php endif;
?>
</div>
</section>

<!-- REGISTRATION & CERTIFICATIONS -->
<section id = 'registration' class = 'py-20 md:py-28 bg-white'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-14'>
<div>
<div class = 'inline-flex items-center gap-2 bg-violet-50 text-violet-600 text-[11px] font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-4'>
<i class = 'fa-solid fa-stamp'></i> Registration & Certifications
</div>
<h2 class = 'text-3xl md:text-4xl font-extrabold text-slate-900' style = "font-family:'Fraunces',serif;">Official Registrations</h2>
<p class = 'mt-3 text-slate-500 text-sm max-w-xl'>Our legal registrations, certifications, and regulatory compliance documents with relevant government authorities.</p>
</div>
<?php if ( !empty( $registrations ) ): ?>
<div class = 'text-sm text-slate-500 font-medium shrink-0'>
<span class = 'text-violet-600 font-bold'><?= count( $registrations ) ?></span> documents
</div>
<?php endif;
?>
</div>

<?php if ( !empty( $registrations ) ): ?>
<div class = 'grid md:grid-cols-2 lg:grid-cols-3 gap-6'>
<?php foreach ( $registrations as $reg ): ?>
<div class = 'group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-violet-300 transition-all duration-300'>
<div class = 'h-32 bg-gradient-to-br from-violet-50 via-violet-100/50 to-violet-50 flex items-center justify-center relative overflow-hidden'>
<div class = 'absolute inset-0 bg-gradient-to-br from-violet-0 to-violet-50 group-hover:to-violet-100/50 transition-all'></div>
<div class = 'relative text-center'>
<i class = 'fa-solid fa-certificate text-violet-600 text-4xl mb-2 opacity-80'></i>
<div class = 'text-sm font-bold text-violet-700 uppercase tracking-wider'>Certificate</div>
</div>
</div>
<div class = 'p-5'>
<h3 class = 'font-bold text-slate-900 text-sm mb-2 group-hover:text-violet-600 transition-colors'><?= sanitize( $reg[ 'title' ] ?? '' ) ?></h3>
<p class = 'text-slate-500 text-xs leading-relaxed mb-4 line-clamp-2'><?= sanitize( $reg[ 'description' ] ?? '' ) ?></p>
<div class = 'flex items-center justify-between pt-3 border-t border-slate-100'>
<span class = 'text-[11px] text-slate-400 font-medium'><i class = "fa-solid fa-file-<?= $reg['file_type'] === 'pdf' ? 'pdf text-red-500' : 'word text-blue-500' ?> mr-1"></i> <?= strtoupper( $reg[ 'file_type' ] ?? 'PDF' ) ?></span>
<?php if ( !empty( $reg[ 'file_path' ] ) ): ?>
<a href = "<?= $base ?><?= $reg['file_path'] ?>" target = '_blank' class = 'inline-flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all'>
<i class = 'fa-solid fa-download text-[10px]'></i> Download
</a>
<?php else: ?>
<span class = 'text-[11px] text-slate-400 font-medium'><i class = 'fa-solid fa-clock mr-1'></i> Coming Soon</span>
<?php endif;
?>
</div>
</div>
</div>
<?php endforeach;
?>
</div>
<?php else: ?>
<div class = 'text-center py-16 bg-slate-50 rounded-2xl border border-slate-200'>
<div class = 'w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mx-auto mb-4'><i class = 'fa-solid fa-stamp text-violet-600 text-2xl'></i></div>
<p class = 'text-slate-500 text-sm'>Registration documents will be uploaded soon.</p>
</div>
<?php endif;
?>
</div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
