<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Registration | WRC';
$metaDescription = 'WRC is a registered organization. View our registration status and download official registration certificates and regulatory documents.';

// Fetch hero from dedicated table
$heroRow = getRow( 'resources_hero' );
$heroData = [];
if ( $heroRow ) {
    $decoded = json_decode( $heroRow[ 'content' ] ?? '{}', true );
    $heroData = is_array( $decoded ) ? $decoded : [];
}

$heroTitle = trim( (string) ( $heroRow[ 'section_title' ] ?? '' ) ) ?: ( trim( (string) ( $heroData[ 'title' ] ?? '' ) ) ?: 'Organizational Registration' );
$heroSubtitle = trim( (string) ( $heroRow[ 'section_subtitle' ] ?? '' ) ) ?: ( trim( (string) ( $heroData[ 'subtitle' ] ?? '' ) ) ?: 'Welfare & Relief Committee (WRC) is a registered organization committed to serving communities through humanitarian support and responsible development initiatives.' );
$heroTagline = trim( (string) ( $heroData[ 'tagline' ] ?? '' ) ) ?: 'Transparency & Accountability';

$cta1Text = trim( (string) ( $heroData[ 'button1_text' ] ?? '' ) ) ?: 'Registration Status';
$cta1Link = trim( (string) ( $heroData[ 'button1_link' ] ?? '' ) ) ?: '#registration';
$cta2Text = array_key_exists( 'button2_text', $heroData ) ? trim( (string) $heroData[ 'button2_text' ] ) : 'Registration Certificates';
$cta2Link = trim( (string) ( $heroData[ 'button2_link' ] ?? '' ) ) ?: '#certificates';

$registrationDocs = getRows( 'resources', [ 'resource_type' => 'download', 'is_active' => 1 ], 'sort_order ASC' );

// Dynamic hero stats (dashboard-managed, registration-focused fallbacks)
$stats = $heroData[ 'stats' ] ?? [];
if ( !is_array( $stats ) || empty( $stats ) ) {
    $stats = [
        [ 'label' => 'Registration Certificates', 'value' => count( $registrationDocs ) . '+', 'icon' => 'fa-file-shield' ],
        [ 'label' => 'Registered Organization', 'value' => 'WRC', 'icon' => 'fa-stamp' ],
        [ 'label' => 'Legal Status', 'value' => 'Registered', 'icon' => 'fa-circle-check' ],
    ];
}

$certHeading = trim( (string) ( $heroData[ 'cert_heading' ] ?? '' ) ) ?: 'Registration Certificates';
$certIntro = trim( (string) ( $heroData[ 'cert_intro' ] ?? '' ) ) ?: 'Download WRC’s official registration certificates and regulatory documents issued by the relevant authorities.';

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

class = 'relative w-full bg-slate-900 text-white overflow-hidden min-h-[440px] md:min-h-[480px] flex items-center'>
<div class = 'absolute inset-0'>
<?php
$heroImg = $heroData[ 'hero_image' ] ?? ( $heroRow[ 'hero_image' ] ?? 'assets/images/hero/resources-hero.jpg' );
if ( strpos( $heroImg, 'assets/' ) !== 0 && strpos( $heroImg, 'http' ) !== 0 && strpos( $heroImg, 'uploads/' ) !== 0 ) {
    $heroImg = 'assets/images/' . $heroImg;
}
?>
<img src = "<?= sanitize( siteUrl( $heroImg ) ) ?>" alt = 'WRC registration banner' fetchpriority = 'high'

class = 'absolute inset-0 w-full h-full object-cover'>
<div class = 'absolute inset-0 bg-gradient-to-br from-slate-950/90 via-slate-900/75 to-brandRed/35'></div>
<div class = 'absolute top-20 left-10 w-72 h-72 bg-brandRed/10 rounded-full blur-3xl'></div>
<div class = 'absolute bottom-10 right-10 w-96 h-96 bg-white/5 rounded-full blur-3xl'></div>
</div>
<div class = 'relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20'>
<div class = 'max-w-3xl mx-auto text-center'>
<nav class = 'text-xs text-white/50 mb-6 flex items-center gap-2 justify-center'>
<a href = "<?= $base ?>index.php" class = 'hover:text-white transition'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[8px]'></i>
<span class = 'text-white/80'>Registration</span>
</nav>
<div

class = 'inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-[11px] font-semibold uppercase tracking-widest px-4 py-2 rounded-full mb-6'>
<i class = 'fa-solid fa-stamp text-white text-xs'></i>
<?= sanitize( $heroTagline ) ?>
</div>
<h1 class = 'text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6'
style = "font-family:'Fraunces',serif;">
<?= sanitize( $heroTitle ) ?>
</h1>
<p class = 'text-base md:text-lg text-white/70 leading-relaxed mb-10 max-w-2xl mx-auto'>
<?= sanitize( $heroSubtitle ) ?>
</p>
<!-- Dynamic Stats Row -->
<div class = 'grid grid-cols-1 min-[480px]:grid-cols-3 gap-3 sm:gap-4 mb-10 max-w-2xl mx-auto'>
<?php foreach ( $stats as $stat ): ?>
<div

class = 'bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-4 py-3 text-center hover:bg-white/15 transition-all'>
<i class = "fa-solid <?= sanitize( $stat['icon'] ?? 'fa-circle-check' ) ?> text-white/80 text-sm mb-1"></i>
<div class = 'text-xl font-extrabold text-white' style = "font-family:'Fraunces',serif;">
<?= sanitize( ( string ) ( $stat[ 'value' ] ?? '' ) ) ?>
</div>
<div class = 'text-[10px] text-white/60 font-medium uppercase tracking-wider'>
<?= sanitize( $stat[ 'label' ] ?? '' ) ?>
</div>
</div>
<?php endforeach;
?>
</div>
<div class = 'flex flex-col sm:flex-row justify-center gap-3 sm:gap-4'>
<?php if ( $cta1Text !== '' ): ?>
<a href = "<?= sanitize( $cta1Link ) ?>"

class = 'bg-white text-slate-900 hover:bg-slate-100 font-bold text-sm px-8 py-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 transform hover:-translate-y-0.5'>
<i class = 'fa-solid fa-stamp text-brandRed'></i> <?= sanitize( $cta1Text ) ?>
</a>
<?php endif; ?>
<?php if ( $cta2Text !== '' ): ?>
<a href = "<?= sanitize( $cta2Link ) ?>"

class = 'bg-white/10 hover:bg-white/20 border border-white/30 text-white font-bold text-sm px-8 py-4 rounded-xl backdrop-blur-sm transition-all flex items-center justify-center gap-2 transform hover:-translate-y-0.5'>
<i class = 'fa-solid fa-file-shield'></i> <?= sanitize( $cta2Text ) ?>
</a>
<?php endif; ?>
</div>
</div>
</div>
</section>

<!-- QUICK NAV -->
<section aria-label = 'Registration sections'

class = 'bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-20 z-40 shadow-sm'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex overflow-x-auto gap-1 py-2 scrollbar-hide justify-center' style = 'scrollbar-width:none;'>
<a href = '#registration' aria-label = 'Jump to registration status'

class = 'shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap'><i

class = 'fa-solid fa-stamp mr-1.5 text-brandRed'></i> Registration Status</a>
<a href = '#certificates' aria-label = 'Jump to registration certificates'

class = 'shrink-0 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-brandRed hover:text-white text-slate-600 transition-all whitespace-nowrap'><i

class = 'fa-solid fa-file-shield mr-1.5 text-brandRed'></i> Certificates</a>
</div>
</div>
</section>

<!-- ORGANIZATION REGISTRATION -->
<section id = 'registration' class = 'relative overflow-hidden py-16 md:py-24 bg-white'>
<div class = 'pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full bg-brandRed/5 blur-3xl'></div>
<div class = 'pointer-events-none absolute -right-24 bottom-0 h-72 w-72 rounded-full bg-slate-100 blur-3xl'></div>
<div class = 'relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
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
Welfare &amp; Relief Committee ( WRC ) is a registered organization committed to serving
communities through humanitarian support and responsible development initiatives.
</p>
</div>
</div>
</div>

<p class = 'mt-6 text-center text-xs text-slate-400'>
Registration certificates and regulatory documents are available for download below.
</p>
</div>
</div>
</section>

<!-- REGISTRATION CERTIFICATES -->
<section id = 'certificates' class = 'py-16 md:py-24 bg-slate-50 border-t border-slate-200'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-10 md:mb-14'>
<div class = 'max-w-2xl'>
<div

class = 'inline-flex items-center gap-2 bg-white text-brandRed text-[11px] font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-4 border border-brandRed/10'>
<i class = 'fa-solid fa-file-shield'></i> Verification &amp; Compliance
</div>
<h2 class = 'text-3xl md:text-4xl font-extrabold text-slate-900'
style = "font-family:'Fraunces',serif;"><?= sanitize( $certHeading ) ?></h2>
<p class = 'mt-3 text-slate-500 text-sm md:text-base leading-7'>
<?= sanitize( $certIntro ) ?>
</p>
</div>
<?php if ( !empty( $registrationDocs ) ): ?>
<div class = 'shrink-0 text-sm text-slate-500 font-medium'>
<span class = 'text-brandRed font-bold'><?= count( $registrationDocs ) ?></span> documents available
</div>
<?php endif; ?>
</div>

<?php if ( !empty( $registrationDocs ) ): ?>
<div class = 'grid sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5'>
<?php foreach ( $registrationDocs as $doc ): ?>
<?php
$docType = strtolower( ( string ) ( $doc[ 'file_type' ] ?? 'pdf' ) );
$isImage = in_array( $docType, [ 'jpg', 'jpeg', 'png', 'webp', 'gif' ], true );
$isWord = in_array( $docType, [ 'doc', 'docx' ], true );
$docIcon = $isImage ? 'fa-file-image text-emerald-600' : ( $isWord ? 'fa-file-word text-blue-600' : 'fa-file-pdf text-red-500' );
$docTint = $isImage ? 'bg-emerald-50' : ( $isWord ? 'bg-blue-50' : 'bg-red-50' );
?>
<article

class = 'group flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-lg'>
<div class = 'flex items-start gap-4'>
<div class = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl <?= $docTint ?>'>
<i class = "fa-solid <?= $docIcon ?> text-lg"></i>
</div>
<div class = 'min-w-0'>
<h3 class = 'text-sm font-bold leading-snug text-slate-900'>
<?= sanitize( $doc[ 'title' ] ?? '' ) ?>
</h3>
<p class = 'mt-1 text-[11px] font-medium uppercase tracking-wider text-slate-400'>
<?= sanitize( strtoupper( $docType ) ) ?> · Official Document
</p>
</div>
</div>
<div class = 'mt-5 pt-4 border-t border-slate-100'>
<?php if ( !empty( $doc[ 'file_path' ] ) ): ?>
<a href = "<?= sanitize( siteUrl( $doc['file_path'] ) ) ?>" target = '_blank'
rel = 'noopener noreferrer'

class = 'inline-flex items-center gap-2 text-xs font-bold text-brandRed transition-colors hover:text-brandRed-hover'>
<?= $isImage ? 'View Document' : 'Download Document' ?>
<i class = 'fa-solid <?= $isImage ? 'fa-arrow-up-right-from-square' : 'fa-download' ?> text-[10px]'></i>
</a>
<?php else: ?>
<span class = 'inline-flex items-center gap-2 text-xs font-medium text-slate-400'>
<i class = 'fa-solid fa-clock text-[10px]'></i> Coming Soon
</span>
<?php endif; ?>
</div>
</article>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class = 'text-center py-16 bg-white rounded-2xl border border-slate-200'>
<div class = 'w-16 h-16 rounded-full bg-brandRed/5 flex items-center justify-center mx-auto mb-4'>
<i class = 'fa-solid fa-file-shield text-brandRed text-2xl'></i>
</div>
<p class = 'text-slate-500 text-sm'>Registration documents will be uploaded soon.</p>
</div>
<?php endif; ?>
</div>
</section>

<!-- VERIFICATION CONTACT -->
<section class = 'pb-16 md:pb-24 bg-slate-50'>
<div class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
<div

class = 'relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-10 sm:px-10 md:px-12 text-center md:text-left md:flex md:items-center md:justify-between gap-8'>
<div class = 'pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-brandRed/20 blur-3xl'></div>
<div class = 'relative'>
<h2 class = 'text-xl sm:text-2xl font-bold text-white' style = "font-family:'Fraunces',serif;">Need to verify our registration?</h2>
<p class = 'mt-2 text-sm text-slate-300'>Contact our team for registration details, verification requests, or official documentation.</p>
</div>
<a href = "<?= $base ?>contact/index.php"

class = 'relative mt-6 md:mt-0 inline-flex items-center justify-center gap-2 rounded-xl bg-brandRed px-6 py-3 text-sm font-bold text-white shadow-md transition-all hover:bg-brandRed-hover'>
<i class = 'fa-solid fa-envelope text-xs'></i> Contact Us
</a>
</div>
</div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
