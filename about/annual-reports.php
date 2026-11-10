<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Annual Reports | Welfare & Relief Committee (WRC)';
$metaDescription = 'Read and download WRC annual reports covering our programmes, partnerships, financial performance, and impact across Pakistan.';

/* -------------------------------------------------------------------------
 * Page copy (dashboard-managed, with the approved wording as the default).
 * ---------------------------------------------------------------------- */
$heroRow = getRow( 'annual_reports_hero' );
$contentRow = getRow( 'annual_reports_content' );

$contentData = [];
if ( $contentRow ) {
    $decoded = json_decode( $contentRow[ 'content' ] ?? '{}', true );
    $contentData = is_array( $decoded ) ? $decoded : [];
}

$heroTitle = trim( (string) ( $heroRow[ 'section_title' ] ?? '' ) );
if ( $heroTitle === '' ) {
    $heroTitle = 'Annual Reports';
}

$heroSubtitle = trim( (string) ( $heroRow[ 'section_subtitle' ] ?? '' ) );
if ( $heroSubtitle === '' ) {
    $heroSubtitle = 'Transparency Through Reporting, Accountability Through Action';
}

$heroImage = trim( (string) ( $heroRow[ 'hero_image' ] ?? '' ) );
if ( $heroImage === '' ) {
    $heroImage = 'assets/images/hero/about-hero.JPG';
}

$introOne = trim( (string) ( $contentData[ 'intro1' ] ?? '' ) );
if ( $introOne === '' ) {
    $introOne = "WRC is committed to transparency, accountability, and responsible resource stewardship. Our annual reports provide an overview of the organization's humanitarian and development initiatives, programme achievements, financial performance, institutional progress, and the impact of our work across communities.";
}

$introTwo = trim( (string) ( $contentData[ 'intro2' ] ?? '' ) );
if ( $introTwo === '' ) {
    $introTwo = "Through regular reporting, we aim to keep our donors, partners, stakeholders, and the communities we serve informed about our activities, progress, challenges, and commitment to continuous improvement.";
}

$sectionTitle = trim( (string) ( $contentRow[ 'section_title' ] ?? '' ) );
if ( $sectionTitle === '' ) {
    $sectionTitle = 'Explore Our Annual Reports';
}

$sectionSubtitle = trim( (string) ( $contentRow[ 'section_subtitle' ] ?? '' ) );
if ( $sectionSubtitle === '' ) {
    $sectionSubtitle = "Access our annual reports to learn more about WRC's programmes, partnerships, organizational developments, and financial performance over the years.";
}

/* -------------------------------------------------------------------------
 * Reports: every published annual-report resource becomes a card. A report
 * only becomes clickable once a file has been attached in the dashboard.
 * ---------------------------------------------------------------------- */
$reports = getRows(
    'resources',
    [ 'resource_type' => 'annual_report', 'is_active' => 1 ],
    'year DESC, sort_order ASC, id DESC'
);

$formatSize = static function ( int $bytes ): string {
    if ( $bytes >= 1048576 ) {
        return number_format( $bytes / 1048576, 1 ) . ' MB';
    }
    if ( $bytes >= 1024 ) {
        return number_format( $bytes / 1024 ) . ' KB';
    }
    return $bytes . ' B';
};

$reportCards = [];
foreach ( $reports as $report ) {
    $path = trim( (string) ( $report[ 'file_path' ] ?? '' ) );
    $isRemote = $path !== '' && preg_match( '~^(?:https?:)?//~i', $path ) === 1;
    $available = false;
    $size = 0;

    if ( $isRemote ) {
        $available = true;
    } elseif ( $path !== '' ) {
        $absolute = SITE_ROOT . '/' . ltrim( $path, '/\\' );
        $available = is_file( $absolute );
        if ( $available ) {
            $size = (int) @filesize( $absolute );
        }
    }

    $year = (int) ( $report[ 'year' ] ?? 0 );
    $description = trim( (string) ( $report[ 'description' ] ?? '' ) );
    if ( $description === '' ) {
        $description = "A comprehensive overview of WRC's programmes, partnerships, financial performance, and community impact for the year.";
    }

    $reportCards[] = [
        'title' => trim( (string) ( $report[ 'title' ] ?? '' ) ),
        'summary' => $description,
        'year' => $year,
        'range' => $year > 0 ? $year . '–' . ( $year + 1 ) : '',
        'path' => $path,
        'url' => $available ? ( $isRemote ? $path : siteUrl( $path ) ) : '',
        'available' => $available,
        'size' => $size > 0 ? $formatSize( $size ) : '',
        'type' => strtoupper( (string) ( $report[ 'file_type' ] ?? 'pdf' ) ),
    ];
}

$reportCount = count( $reportCards );

$latestRange = '—';
foreach ( $reportCards as $card ) {
    if ( $card[ 'range' ] !== '' ) {
        $latestRange = $card[ 'range' ];
        break;
    }
}

$coveredYears = array_filter( array_map( static function ( array $card ): int {
    return (int) $card[ 'year' ];
}, $reportCards ) );

$coverage = '—';
if ( !empty( $coveredYears ) ) {
    $coverage = min( $coveredYears ) . '–' . ( max( $coveredYears ) + 1 );
}

// Highlight the last word of the page title in brand red, as on the other
// About pages.
$titleWords = explode( ' ', $heroTitle );
$titleLastWord = array_pop( $titleWords );
$heroTitleHtml = sanitize( implode( ' ', $titleWords ) ) . ( !empty( $titleWords ) ? ' ' : '' )
    . '<span class="text-brandRed">' . sanitize( (string) $titleLastWord ) . '</span>';

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class = 'flex-grow'>

<!-- HERO -->
<section class = 'relative isolate overflow-hidden bg-ink-900 text-white'>
<div class = 'absolute inset-0 -z-10'>
<img src = "<?= sanitize( getImageUrl( $heroImage ) ) ?>" alt = '' aria-hidden = 'true'
class = 'h-full w-full object-cover opacity-25'>
<div class = 'absolute inset-0 bg-gradient-to-br from-ink-900/95 via-ink-900/85 to-brandRed/40'></div>
</div>
<div class = 'mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28'>
<nav class = 'mb-8 flex flex-wrap items-center gap-2 text-sm text-white/60' aria-label = 'Breadcrumb'>
<a href = "<?= $base ?>index.php" class = 'transition-colors hover:text-white'>Home</a>
<i class = 'fa-solid fa-chevron-right text-[9px]'></i>
<a href = "<?= $base ?>about/index.php" class = 'transition-colors hover:text-white'>About Us</a>
<i class = 'fa-solid fa-chevron-right text-[9px]'></i>
<span class = 'font-medium text-white'>Annual Reports</span>
</nav>
<span
class = 'inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[10px] font-bold uppercase tracking-[0.2em] text-white/90'>
<span class = 'h-1.5 w-1.5 rounded-full bg-brandRed'></span> Transparency &amp; Accountability
</span>
<h1 class = 'mt-6 max-w-3xl font-display text-4xl font-semibold leading-tight md:text-5xl lg:text-6xl'>
<?= $heroTitleHtml ?>
</h1>
<p class = 'mt-5 max-w-2xl text-lg leading-relaxed text-sand-50/80 md:text-xl'>
<?= sanitize( $heroSubtitle ) ?>
</p>
<div class = 'mt-9 flex flex-col gap-3 sm:flex-row'>
<a href = '#reports'
class = 'inline-flex items-center justify-center gap-2 rounded-xl bg-brandRed px-7 py-3.5 text-sm font-bold uppercase tracking-wider text-white shadow-lg shadow-brandRed/25 transition-all hover:-translate-y-0.5 hover:bg-brandRed-hover'>
<i class = 'fa-solid fa-file-lines'></i> Browse Reports
</a>
<a href = "<?= $base ?>contact/index.php"
class = 'inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/5 px-7 py-3.5 text-sm font-bold uppercase tracking-wider text-white transition-all hover:-translate-y-0.5 hover:bg-white/15'>
<i class = 'fa-solid fa-envelope'></i> Request a Report
</a>
</div>
</div>
</section>

<!-- INTRODUCTION -->
<section class = 'bg-white py-16 lg:py-24'>
<div class = 'mx-auto grid max-w-7xl grid-cols-1 gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8'>
<div class = 'lg:col-span-7'>
<p class = 'text-[11px] font-bold uppercase tracking-[0.25em] text-brandRed'>Why We Report</p>
<div class = 'mt-5 space-y-5 text-base leading-8 text-slate-600 sm:text-lg'>
<p><?= sanitize( $introOne ) ?></p>
<p><?= sanitize( $introTwo ) ?></p>
</div>
</div>
<div class = 'lg:col-span-5'>
<div class = 'relative overflow-hidden rounded-3xl border border-slate-200 bg-sand-50 p-7 sm:p-8'>
<div class = 'absolute -right-12 -top-12 h-36 w-36 rounded-full bg-brandRed/5'></div>
<div class = 'relative'>
<p class = 'text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400'>At a Glance</p>
<dl class = 'mt-5 divide-y divide-slate-200'>
<div class = 'flex items-baseline justify-between gap-4 py-4 first:pt-0'>
<dt class = 'text-xs font-semibold uppercase tracking-wider text-slate-500'>Report editions</dt>
<dd class = 'font-display text-2xl font-semibold text-slate-900'><?= (int) $reportCount ?></dd>
</div>
<div class = 'flex items-baseline justify-between gap-4 py-4'>
<dt class = 'text-xs font-semibold uppercase tracking-wider text-slate-500'>Latest edition</dt>
<dd class = 'font-display text-2xl font-semibold text-slate-900'><?= sanitize( $latestRange ) ?></dd>
</div>
<div class = 'flex items-baseline justify-between gap-4 py-4 last:pb-0'>
<dt class = 'text-xs font-semibold uppercase tracking-wider text-slate-500'>Years covered</dt>
<dd class = 'font-display text-2xl font-semibold text-slate-900'><?= sanitize( $coverage ) ?></dd>
</div>
</dl>
</div>
</div>
</div>
</div>
</section>

<!-- REPORTS -->
<section id = 'reports' class = 'scroll-mt-24 bg-sand-50 py-16 lg:py-24'>
<div class = 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8'>
<div class = 'flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between'>
<div class = 'max-w-2xl'>
<p class = 'text-[11px] font-bold uppercase tracking-[0.25em] text-brandRed'>Report Archive</p>
<h2 class = 'mt-3 font-display text-3xl font-semibold text-slate-900 lg:text-4xl'>
<?= sanitize( $sectionTitle ) ?>
</h2>
<p class = 'mt-4 leading-relaxed text-slate-600'>
<?= sanitize( $sectionSubtitle ) ?>
</p>
</div>
<?php if ( $reportCount > 0 ): ?>
<div class = 'shrink-0 self-start rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm lg:self-auto'>
<span class = 'font-display text-3xl font-semibold text-brandRed'><?= (int) $reportCount ?></span>
<span class = 'ml-2 text-xs font-semibold uppercase tracking-wider text-slate-500'>
<?= $reportCount === 1 ? 'Edition' : 'Editions' ?>
</span>
</div>
<?php endif; ?>
</div>

<?php if ( !empty( $reportCards ) ): ?>
<div class = 'mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3'>
<?php foreach ( $reportCards as $index => $card ): ?>
<article
class = 'group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:border-brandRed/30 hover:shadow-xl'>
<div class = 'relative overflow-hidden bg-ink-900 px-6 py-7 text-white'>
<div class = 'absolute inset-0 bg-gradient-to-br from-ink-900 via-ink-800 to-ink-900'></div>
<div class = 'absolute -right-8 -top-8 h-24 w-24 rounded-full border border-white/5'></div>
<div class = 'relative flex items-start justify-between gap-4'>
<div>
<div class = 'flex flex-wrap items-center gap-2'>
<p class = 'text-[10px] font-semibold uppercase tracking-[0.2em] text-white/50'>Annual Report</p>
<?php if ( $index === 0 ): ?>
<span
class = 'rounded-full bg-brandRed px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest text-white'>Latest</span>
<?php endif; ?>
</div>
<p class = 'mt-2 font-display text-3xl font-semibold leading-none text-white'>
<?= $card[ 'range' ] !== '' ? sanitize( $card[ 'range' ] ) : '&mdash;' ?>
</p>
</div>
<div class = 'rounded-xl border border-white/15 bg-white/10 px-3 py-2 text-center'>
<i class = 'fa-solid fa-file-pdf text-lg text-brandRed'></i>
<p class = 'mt-1 text-[9px] font-bold tracking-[0.15em] text-white/70'><?= sanitize( $card[ 'type' ] ) ?></p>
</div>
</div>
</div>
<div class = 'flex flex-1 flex-col p-6'>
<h3
class = 'font-display text-lg font-semibold leading-snug text-slate-900 transition-colors group-hover:text-brandRed'>
<?= sanitize( $card[ 'title' ] ) ?>
</h3>
<p class = 'mt-3 flex-1 text-sm leading-relaxed text-slate-500'>
<?= sanitize( $card[ 'summary' ] ) ?>
</p>
<div class = 'mt-6 flex flex-wrap items-center gap-3'>
<?php if ( $card[ 'available' ] ): ?>
<a href = "<?= sanitize( $card[ 'url' ] ) ?>" target = '_blank' rel = 'noopener noreferrer'
class = 'inline-flex items-center gap-2 rounded-lg bg-brandRed px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-brandRed-hover'>
<i class = 'fa-solid fa-file-pdf'></i> Open PDF
<i class = 'fa-solid fa-arrow-up-right-from-square text-[10px] opacity-80'></i>
</a>
<a href = "<?= sanitize( $card[ 'url' ] ) ?>" download
class = 'inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition-colors hover:border-brandRed/30 hover:text-brandRed'
title = 'Download this report' aria-label = 'Download <?= sanitize( $card[ 'title' ] ) ?>'>
<i class = 'fa-solid fa-download text-xs'></i>
</a>
<?php else: ?>
<span
class = 'inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3.5 py-2.5 text-xs font-semibold text-slate-500'>
<i class = 'fa-solid fa-clock'></i> PDF coming soon
</span>
<?php endif; ?>
</div>
<div class = 'mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] font-medium text-slate-400'>
<span><i class = 'fa-solid fa-file-pdf mr-1'></i> <?= sanitize( $card[ 'type' ] ) ?></span>
<?php if ( $card[ 'size' ] !== '' ): ?>
<span><i class = 'fa-solid fa-hard-drive mr-1'></i> <?= sanitize( $card[ 'size' ] ) ?></span>
<?php endif; ?>
<?php if ( !$card[ 'available' ] ): ?>
<span class = 'text-amber-600'><i class = 'fa-solid fa-hourglass-half mr-1'></i> Not yet available</span>
<?php endif; ?>
</div>
</div>
</article>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class = 'mt-12 rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center'>
<div class = 'mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100'>
<i class = 'fa-solid fa-file-circle-plus text-2xl text-slate-300'></i>
</div>
<h3 class = 'font-display text-xl font-semibold text-slate-900'>Annual reports are being finalised</h3>
<p class = 'mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500'>
Our next edition is currently in preparation. Please check back soon, or contact us if you need a report from an
earlier year.
</p>
<a href = "<?= $base ?>contact/index.php"
class = 'mt-6 inline-flex items-center gap-2 rounded-lg bg-brandRed px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-brandRed-hover'>
<i class = 'fa-solid fa-envelope'></i> Contact Us
</a>
</div>
<?php endif; ?>
</div>
</section>

<!-- RELATED DOCUMENTS -->
<section class = 'relative overflow-hidden bg-ink-900 py-16 text-white'>
<div class = 'absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-800 to-ink-900'></div>
<div class = 'relative mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8'>
<div class = 'max-w-2xl'>
<h2 class = 'font-display text-2xl font-semibold md:text-3xl'>Questions about our reports?</h2>
<p class = 'mt-2 text-sm leading-relaxed text-sand-50/70'>
Our team is happy to help with previous editions, financial statements, or any clarification you need.
</p>
</div>
<div class = 'flex shrink-0 flex-wrap gap-3'>
<a href = "<?= $base ?>contact/index.php"
class = 'inline-flex items-center gap-2 rounded-xl bg-brandRed px-6 py-3 text-sm font-bold uppercase tracking-wider text-white transition-colors hover:bg-brandRed-hover'>
<i class = 'fa-solid fa-envelope'></i> Contact Us
</a>
<a href = "<?= $base ?>about/policies-compliance.php"
class = 'inline-flex items-center gap-2 rounded-xl border border-white/25 px-6 py-3 text-sm font-bold uppercase tracking-wider text-white transition-colors hover:bg-white/10'>
<i class = 'fa-solid fa-shield-halved'></i> Policies &amp; Compliance
</a>
</div>
</div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php';
?>
