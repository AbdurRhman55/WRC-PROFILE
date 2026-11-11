<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Registration | WRC';
$metaDescription = 'WRC is a registered organization committed to serving communities through humanitarian support and responsible development initiatives.';

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

class = 'relative w-full bg-slate-900 text-white overflow-hidden min-h-[400px] md:min-h-[440px] flex items-center'>
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
<p class = 'text-base md:text-lg text-white/70 leading-relaxed max-w-2xl mx-auto'>
<?= sanitize( $heroSubtitle ) ?>
</p>
</div>
</div>
</section>

<!-- ORGANIZATION REGISTRATION -->
<section id = 'registration' class = 'relative overflow-hidden bg-white py-16 md:py-24'>
<div class = 'pointer-events-none absolute -left-24 top-10 h-80 w-80 rounded-full bg-brandRed/5 blur-3xl'></div>
<div class = 'pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-slate-100 blur-3xl'></div>

<div class = 'relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8'>
<div

class = 'relative overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-50/70 px-6 py-12 sm:px-10 md:px-14 md:py-16 text-center shadow-sm'>
<div class = 'absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-brandRed via-brandRed to-brandRed/40'></div>
<div class = 'pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-brandRed/5'></div>

<div

class = 'relative mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-white text-brandRed shadow-md ring-1 ring-slate-200'>
<span class = 'absolute inset-0 rounded-full border border-dashed border-brandRed/25'></span>
<i class = 'fa-solid fa-stamp text-4xl' aria-hidden = 'true'></i>
</div>

<span

class = 'relative mt-6 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3.5 py-1.5 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700 ring-1 ring-emerald-100'>
<span class = 'h-1.5 w-1.5 rounded-full bg-emerald-500'></span> Registered
</span>

<h2 class = 'relative mt-5 text-3xl md:text-4xl font-extrabold text-slate-900'
style = "font-family:'Fraunces',serif;">Registered Organization</h2>

<p class = 'relative mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600 md:text-lg md:leading-8'>
Welfare &amp; Relief Committee ( WRC ) is a registered organization committed to serving
communities through humanitarian support and responsible development initiatives.
</p>

<div

class = 'relative mt-10 grid gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 sm:grid-cols-3 text-left sm:text-center'>
<div class = 'bg-white px-5 py-5'>
<p class = 'text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400'>Legal Status</p>
<p class = 'mt-1.5 text-sm font-bold text-ink-900'>Registered</p>
</div>
<div class = 'bg-white px-5 py-5'>
<p class = 'text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400'>Registered As</p>
<p class = 'mt-1.5 text-sm font-bold text-ink-900'>Welfare &amp; Relief Committee</p>
</div>
<div class = 'bg-white px-5 py-5'>
<p class = 'text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400'>Commitment</p>
<p class = 'mt-1.5 text-sm font-bold text-ink-900'><?= sanitize( $heroTagline ) ?></p>
</div>
</div>
</div>
</div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
