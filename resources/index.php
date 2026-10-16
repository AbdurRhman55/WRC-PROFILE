<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Resources | WRC';
$metaDescription = 'Access WRC organizational policies and downloadable resources. WRC is a registered organization committed to transparency and accountability.';

// Fetch hero from dedicated table
$heroRow = getRow( 'resources_hero' );
$heroData = [];
if ( $heroRow ) {
    $decoded = json_decode( $heroRow[ 'content' ] ?? '{}', true );
    $heroData = is_array( $decoded ) ? $decoded : [];
}
$heroTitle = $heroRow[ 'section_title' ] ?? '';
$heroTitle = $heroTitle ?: ( $heroData[ 'title' ] ?? 'Resources & Documents' );
$heroSubtitle = $heroRow[ 'section_subtitle' ] ?? '';
$heroSubtitle = $heroSubtitle ?: ( $heroData[ 'subtitle' ] ?? 'Access our policies and resources, and learn about WRC as a registered organization.' );
$heroTagline = $heroData[ 'tagline' ] ?? 'Transparency & Accountability';

// Fetch policies
$policies = getRows( 'resources', [ 'resource_type' => 'policy', 'is_active' => 1 ], 'sort_order ASC' );

// Fetch other downloadable resources
$downloads = getRows( 'resources', [ 'resource_type' => 'download', 'is_active' => 1 ], 'sort_order ASC' );

// Dynamic stats for hero
$stats = [
    [ 'label' => 'Policies', 'value' => count( $policies ) . '+', 'icon' => 'fa-shield-halved' ],
    [ 'label' => 'Documents', 'value' => ( count( $policies ) + count( $downloads ) ) . '+', 'icon' => 'fa-file-lines' ],
    [ 'label' => 'Registered Organization', 'value' => 'WRC', 'icon' => 'fa-stamp' ],
];

include dirname( __DIR__ ) . '/includes/header.php';
?>

<style>
.resources-page [ id ] {
    scroll-margin-top: 6.5rem;
}

.resources-page a:focus-visible {
    outline: 3px solid #ca0000;
    outline-offset: 3px;
}

@media ( prefers-reduced-motion: reduce ) {

    .resources-page *,
    .resources-page *::before,
    .resources-page *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}
</style>

<main class = 'resources-page flex-grow'>

<!-- HERO SECTION -->
<section

class = 'relative w-full bg-slate-900 text-white overflow-hidden min-h-[480px] md:min-h-[540px] lg:min-h-[580px] flex items-center'>
<div class = 'absolute inset-0'>
<?php
$heroImg = $heroData[ 'hero_image' ] ?? 'assets/images/hero/resources-hero.jpg';
if ( strpos( $heroImg, 'assets/' ) !== 0 && strpos( $heroImg, 'http' ) !== 0 && strpos( $heroImg, 'uploads/' ) !== 0 ) {
    $heroImg = 'assets/images/' . $heroImg;
}
?>
<img src = "<?= sanitize( siteUrl( $heroImg ) ) ?>" alt = 'Resources page banner' fetchpriority = 'high'

class = 'absolute inset-0 w-full h-full object-cover'>
<div class = 'absolute inset-0 bg-gradient-to-br from-slate-950/85 via-slate-900/70 to-brandRed/35'></div>
<div class = 'absolute top-20 left-10 w-72 h-72 bg-brandRed/10 rounded-full blur-3xl'></div>
<div class = 'absolute bottom-10 right-10 w-96 h-96 bg-white/5 rounded-full blur-3xl'></div>
</div>
<div class = 'relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 lg:py-24'>
<div class = 'max-w-3xl mx-auto text-center'>
<nav class = 'text-xs text-white/50 mb-6 flex items-center gap-2 justify-center'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<span class = 'text-white/80'>Resources</span>
</nav>
<div

class = 'inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-[11px] font-semibold uppercase tracking-widest px-4 py-2 rounded-full mb-6'>
<i class = 'fa-solid fa-folder-open text-white text-xs'></i>
<?= sanitize( $heroTagline ) ?>
</div>
<h1 class = 'text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6'
style = "font-family:'Fraunces',serif;">
<?= sanitize( $heroTitle ) ?>
</h1>
<p class = 'text-lg md:text-xl text-white/70 leading-relaxed mb-10 max-w-2xl mx-auto'>
<?= sanitize( $heroSubtitle ) ?>
</p>
<!-- Dynamic Stats Row -->
<div class = 'grid grid-cols-1 min-[480px]:grid-cols-3 gap-3 sm:gap-4 mb-10 max-w-2xl mx-auto'>
<?php foreach ( $stats as $stat ): ?>
<div

class = 'bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-4 py-3 text-center hover:bg-white/15 transition-all'>
<i class = "fa-solid <?= $stat['icon'] ?> text-white/80 text-sm mb-1"></i>
<div class = 'text-xl font-extrabold text-white' style = "font-family:'Fraunces',serif;">
<?= sanitize( ( string ) $stat[ 'value' ] ) ?>
</div>
<div class = 'text-[10px] text-white/60 font-medium uppercase tracking-wider'>
<?= sanitize( $stat[ 'label' ] ) ?>
</div>
</div>
<?php endforeach;
?>
</div>
<div class = 'flex flex-wrap justify-center gap-4'>
<a href = '#policies'

class = 'bg-white text-slate-900 hover:bg-slate-100 font-bold text-sm px-8 py-4 rounded-xl shadow-lg transition-all flex items-center gap-2 transform hover:-translate-y-0.5'>
<i class = 'fa-solid fa-shield-halved text-brandRed'></i> Policies
</a>
<a href = '#registration'

class = 'bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold text-sm px-8 py-4 rounded-xl backdrop-blur-sm transition-all flex items-center gap-2 transform hover:-translate-y-0.5'>
<i class = 'fa-solid fa-stamp'></i> Registration Status
</a>
</div>
</div>
</div>
</section>

<!-- QUICK NAV -->
<section aria-label = 'Resources sections'

class = 'bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-20 z-40 shadow-sm'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex overflow-x-auto gap-1 py-2 scrollbar-hide' style = 'scrollbar-width:none;'>
<a href = '#policies' aria-label = 'Jump to organizational policies'

class = 'shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap'><i

class = 'fa-solid fa-shield-halved mr-1.5 text-blue-500'></i> Policies</a>
<a href = '#registration' aria-label = 'Jump to registration status'

class = 'shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap'><i

class = 'fa-solid fa-stamp mr-1.5 text-brandRed'></i> Registration Status</a>
</div>
</div>
</section>

<!-- POLICIES -->
<section id = 'policies' class = 'py-20 md:py-28 bg-slate-50'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-14'>
<div>
<div

class = 'inline-flex items-center gap-2 bg-blue-50 text-blue-600 text-[11px] font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-4'>
<i class = 'fa-solid fa-shield-halved'></i> Organizational Policies
</div>
<h2 class = 'text-3xl md:text-4xl font-extrabold text-slate-900'
style = "font-family:'Fraunces',serif;">Policies & Guidelines</h2>
<p class = 'mt-3 text-slate-500 text-sm max-w-xl'>Our organizational policies ensuring good
governance, transparency, accountability, and ethical practices across all operations.</p>
</div>
<?php if ( !empty( $policies ) ): ?>
<div class = 'text-sm text-slate-500 font-medium shrink-0'>
<span class = 'text-blue-600 font-bold'>
<?= count( $policies ) ?>
</span> policies available
</div>
<?php endif;
?>
</div>

<?php if ( !empty( $policies ) ): ?>
<div class = 'grid md:grid-cols-2 lg:grid-cols-3 gap-6'>
<?php foreach ( $policies as $policy ): ?>
<?php
$policyType = strtolower( ( string ) ( $policy[ 'file_type' ] ?? 'pdf' ) );
$policyTypeLabel = strtoupper( $policyType );
$policyFileIcon = $policyType === 'pdf' ? 'pdf text-red-500' : 'word text-blue-500';
?>
<div

class = 'group bg-white rounded-3xl border border-slate-200 overflow-hidden hover:-translate-y-1 hover:shadow-xl hover:border-blue-300 transition-all duration-300'>
<div

class = 'h-32 bg-gradient-to-br from-blue-50 via-blue-100/50 to-blue-50 flex items-center justify-center relative overflow-hidden'>
<div

class = 'absolute inset-0 bg-gradient-to-br from-blue-0 to-blue-50 group-hover:to-blue-100/50 transition-all'>
</div>
<div class = 'relative text-center'>
<i class = 'fa-solid fa-shield-halved text-blue-600 text-4xl mb-2 opacity-80'></i>
<div class = 'text-sm font-bold text-blue-700 uppercase tracking-wider'>
<?= sanitize( $policyTypeLabel ) ?>
</div>
</div>
</div>
<div class = 'p-5'>
<h3 class = 'font-bold text-slate-900 text-sm mb-2 group-hover:text-blue-600 transition-colors'>
<?= sanitize( $policy[ 'title' ] ?? '' ) ?>
</h3>
<p class = 'text-slate-500 text-xs leading-relaxed mb-4 line-clamp-2'>
<?= sanitize( $policy[ 'description' ] ?? '' ) ?>
</p>
<div class = 'flex items-center justify-between pt-3 border-t border-slate-100'>
<span class = 'text-[11px] text-slate-400 font-medium'><i

class = "fa-solid fa-file-<?= $policyFileIcon ?> mr-1"></i>
<?= sanitize( $policyTypeLabel ) ?>
</span>
<?php if ( !empty( $policy[ 'file_path' ] ) ): ?>
<a href = "<?= sanitize( siteUrl( $policy['file_path'] ) ) ?>" target = '_blank'
rel = 'noopener noreferrer'

class = 'inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all'>
<i class = 'fa-solid fa-download text-[10px]'></i> Download
</a>
<?php else: ?>
<span class = 'text-[11px] text-slate-400 font-medium'><i class = 'fa-solid fa-clock mr-1'></i>
Coming Soon</span>
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
<div class = 'w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4'><i

class = 'fa-solid fa-shield-halved text-blue-600 text-2xl'></i></div>
<p class = 'text-slate-500 text-sm'>Policies will be uploaded soon.</p>
</div>
<?php endif;
?>
</div>
</section>

<!-- ORGANIZATION REGISTRATION -->
<section id = 'registration' class = 'py-20 md:py-28 bg-white'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'max-w-4xl mx-auto'>
<div class = 'text-center mb-10'>
<div

class = 'inline-flex items-center gap-2 bg-brandRed/5 text-brandRed text-[11px] font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-4'>
<i class = 'fa-solid fa-stamp'></i> Organization Status
</div>
<h2 class = 'text-3xl md:text-4xl font-extrabold text-slate-900'
style = "font-family:'Fraunces',serif;">Registered Organization</h2>
</div>

<div

class = 'relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 p-8 sm:p-10 md:p-12 shadow-sm'>
<div class = 'absolute left-0 top-0 h-full w-1.5 bg-brandRed'></div>
<div class = 'absolute -right-12 -top-12 h-48 w-48 rounded-full bg-brandRed/5'></div>
<div

class = 'relative flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-8 text-center sm:text-left'>
<div

class = 'flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white text-brandRed shadow-sm ring-1 ring-slate-200'>
<i class = 'fa-solid fa-stamp text-3xl' aria-hidden = 'true'></i>
</div>
<div>
<span

class = 'inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700 ring-1 ring-emerald-100'>
<span class = 'h-1.5 w-1.5 rounded-full bg-emerald-500'></span> Registered
</span>
<p class = 'mt-4 text-base leading-7 text-slate-600 sm:text-lg'>
World Relief Committee ( WRC ) is a registered organization committed to serving
communities through humanitarian support and responsible development initiatives.
</p>
</div>
</div>
</div>
</div>
</div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>