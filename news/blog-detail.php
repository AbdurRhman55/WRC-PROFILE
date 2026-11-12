<?php
/**
 * WRC Blog - public detail page.
 *
 * A blog post is a news_articles row with category = 'blog'. The page reads
 * the same editable fields as the dashboard editor, so changes to the title,
 * image, author, tags or body appear here immediately.
 */
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$slug = trim( (string) ( $_GET[ 'slug' ] ?? '' ) );

if ( $slug === '' ) {
    header( 'Location: ' . $base . 'news/blog.php' );
    exit;
}

$post = getBlogPostBySlug( $slug );
if ( !$post ) {
    header( 'Location: ' . $base . 'news/blog.php' );
    exit;
}

global $pdo;

$settings     = getBlogSettings();
$postImage    = getImageUrl( (string) ( $post[ 'featured_image' ] ?? '' ), 'assets/images/hero/news-hero.jpg' );
$postTags     = getBlogTagList( $post[ 'tags' ] ?? '' );
$readingTime  = getBlogReadingTime( (string) ( $post[ 'content' ] ?? '' ) );
$summaryLead  = getBlogExcerpt( $post, 300 );
$authorName   = trim( (string) ( $post[ 'author' ] ?? '' ) ) ?: 'WRC Editorial Team';

$initials = '';
foreach ( preg_split( '/\s+/', $authorName ) as $word ) {
    if ( $word === '' ) {
        continue;
    }
    $initials .= strtoupper( mb_substr( $word, 0, 1 ) );
    if ( mb_strlen( $initials ) >= 2 ) {
        break;
    }
}

// Give every section heading a stable anchor and collect the outline used by
// the sidebar "On this page" navigation. The callback edits headings in place,
// so the rest of the editor's HTML is never rebuilt or re-encoded.
$toc      = [];
$bodyHtml = (string) ( $post[ 'content' ] ?? '' );
$bodyHtml = preg_replace_callback(
    '/<h([23])([^>]*)>(.*?)<\/h\1>/is',
    static function ( array $match ) use ( &$toc ): string {
        $level = (int) $match[ 1 ];
        $attrs = (string) $match[ 2 ];
        $inner = (string) $match[ 3 ];
        $text  = trim( preg_replace( '/\s+/', ' ', html_entity_decode( strip_tags( $inner ), ENT_QUOTES, 'UTF-8' ) ) );

        if ( $text === '' ) {
            return $match[ 0 ];
        }

        if ( preg_match( '/\sid\s*=\s*(["\'])(.*?)\1/i', $attrs, $existing ) ) {
            $id    = trim( $existing[ 2 ] );
            $hasId = true;
        } else {
            $id    = createSlug( $text );
            $hasId = false;
        }
        if ( $id === '' ) {
            $id = 'section';
        }

        $used   = array_column( $toc, 'id' );
        $base   = $id;
        $suffix = 2;
        while ( in_array( $id, $used, true ) ) {
            $id = $base . '-' . $suffix++;
        }

        if ( $hasId ) {
            // A duplicate hand-written id must be rewritten in the heading too,
            // otherwise the sidebar link would point at nothing.
            if ( $id !== $existing[ 2 ] ) {
                $attrs = preg_replace(
                    '/\sid\s*=\s*(["\'])(.*?)\1/i',
                    ' id="' . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . '"',
                    $attrs,
                    1
                );
            }
        } else {
            $attrs .= ' id="' . htmlspecialchars( $id, ENT_QUOTES, 'UTF-8' ) . '"';
        }

        $toc[] = [ 'id' => $id, 'level' => $level, 'text' => $text ];

        return '<h' . $match[ 1 ] . $attrs . '>' . $inner . '</h' . $match[ 1 ] . '>';
    },
    $bodyHtml
);
$bodyHtml = $bodyHtml ?? (string) ( $post[ 'content' ] ?? '' );

$publishedOn = (string) ( $post[ 'published_at' ] ?? date( 'Y-m-d' ) );

$prevStmt = $pdo->prepare(
    "SELECT slug, title, published_at FROM news_articles
      WHERE category = 'blog' AND is_published = 1 AND slug IS NOT NULL AND slug <> ''
        AND ( published_at < ? OR ( published_at = ? AND id < ? ) )
      ORDER BY published_at DESC, id DESC LIMIT 1"
);
$prevStmt->execute( [ $publishedOn, $publishedOn, (int) $post[ 'id' ] ] );
$prevPost = $prevStmt->fetch( PDO::FETCH_ASSOC );

$nextStmt = $pdo->prepare(
    "SELECT slug, title, published_at FROM news_articles
      WHERE category = 'blog' AND is_published = 1 AND slug IS NOT NULL AND slug <> ''
        AND ( published_at > ? OR ( published_at = ? AND id > ? ) )
      ORDER BY published_at ASC, id ASC LIMIT 1"
);
$nextStmt->execute( [ $publishedOn, $publishedOn, (int) $post[ 'id' ] ] );
$nextPost = $nextStmt->fetch( PDO::FETCH_ASSOC );

$relatedPosts = getRelatedBlogPosts( (int) $post[ 'id' ], 3 );

$scheme   = ( !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' ? 'https' : 'http' );
$host     = $_SERVER[ 'HTTP_HOST' ] ?? 'wrc.org.pk';
$shareUrl = $scheme . '://' . $host . ( $_SERVER[ 'REQUEST_URI' ] ?? ( '/news/blog-detail.php?slug=' . rawurlencode( $slug ) ) );
$shareUrlEnc   = urlencode( $shareUrl );
$shareTitleEnc = urlencode( (string) $post[ 'title' ] );

$pageTitle       = sanitize( $post[ 'title' ] ) . ' | WRC Blog';
$metaDescription = sanitize( getBlogExcerpt( $post, 160 ) );

$schema = [
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => (string) $post[ 'title' ],
    'description'      => getBlogExcerpt( $post, 200 ),
    'image'            => [ $postImage ],
    'datePublished'    => date( 'c', strtotime( $publishedOn ) ),
    'dateModified'     => date( 'c', strtotime( (string) ( $post[ 'updated_at' ] ?? $publishedOn ) ) ),
    'author'           => [
        '@type' => 'Organization',
        'name'  => $authorName,
    ],
    'publisher'        => [
        '@type' => 'Organization',
        'name'  => 'Welfare & Relief Committee',
        'logo'  => [
            '@type' => 'ImageObject',
            'url'   => siteUrl( 'assets/images/logo.png' ),
        ],
    ],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id'   => $shareUrl,
    ],
];

$extraHead = '<meta property="og:type" content="article">' . "\n"
    . '<meta property="og:title" content="' . sanitize( $post[ 'title' ] ) . '">' . "\n"
    . '<meta property="og:description" content="' . sanitize( getBlogExcerpt( $post, 200 ) ) . '">' . "\n"
    . '<meta property="og:url" content="' . htmlspecialchars( $shareUrl, ENT_QUOTES, 'UTF-8' ) . '">' . "\n"
    . '<meta property="og:image" content="' . htmlspecialchars( $postImage, ENT_QUOTES, 'UTF-8' ) . '">' . "\n"
    . '<meta name="twitter:card" content="summary_large_image">' . "\n"
    . '<script type="application/ld+json">'
    . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT )
    . '</script>';

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- POST HERO -->
<section class="relative isolate flex min-h-[520px] items-end overflow-hidden bg-ink-900 text-white">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center opacity-25" style="background-image: url('<?= htmlspecialchars( $postImage, ENT_QUOTES, 'UTF-8' ) ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-ink-900/75 via-ink-900/90 to-ink-900"></div>
        <div class="absolute inset-0 opacity-[0.05]" style="background-image: linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px); background-size: 56px 56px;"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 pt-32 sm:pb-20">
        <nav class="mb-8 flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/45" aria-label="Breadcrumb">
            <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
            <i class="fa-solid fa-chevron-right text-[7px] text-white/30"></i>
            <a href="<?= $base ?>news/index.php" class="hover:text-white transition-colors">News &amp; Media</a>
            <i class="fa-solid fa-chevron-right text-[7px] text-white/30"></i>
            <a href="<?= $base ?>news/blog.php" class="hover:text-white transition-colors">Blog</a>
            <i class="fa-solid fa-chevron-right text-[7px] text-white/30"></i>
            <span class="text-white/75">Story</span>
        </nav>

        <div class="mx-auto w-full max-w-[47rem] lg:mx-0">
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <a href="<?= $base ?>news/blog.php" class="inline-flex items-center gap-2 rounded-full bg-brandRed px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.16em] text-white shadow-lg shadow-brandRed/20">
                    <i class="fa-solid fa-pen-nib text-[9px]"></i> Blog
                </a>
                <?php foreach ( $postTags as $postTag ): ?>
                <a href="<?= $base ?>news/blog.php?tag=<?= rawurlencode( $postTag ) ?>" class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-white/70 backdrop-blur transition-colors hover:border-brandRed hover:bg-brandRed hover:text-white"><?= sanitize( $postTag ) ?></a>
                <?php endforeach; ?>
            </div>

            <h1 class="font-display text-[2.1rem] font-semibold leading-[1.12] tracking-[-0.02em] sm:text-[2.6rem] lg:text-[3.15rem]"><?= sanitize( $post[ 'title' ] ?? '' ) ?></h1>

            <p class="mt-6 max-w-2xl text-base leading-relaxed text-white/65 sm:text-lg"><?= sanitize( $summaryLead ) ?></p>

            <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-white/15 bg-white/10 font-display text-sm font-semibold tracking-wide text-white backdrop-blur"><?= sanitize( $initials ?: 'WR' ) ?></span>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-white/40">Written by</p>
                        <p class="mt-0.5 text-sm font-semibold text-white"><?= sanitize( $authorName ) ?></p>
                    </div>
                </div>
                <span class="hidden h-9 w-px bg-white/15 sm:block"></span>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-medium text-white/55">
                    <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-brandRed/70"></i><?= formatDate( $publishedOn ) ?></span>
                    <span class="h-1 w-1 rounded-full bg-white/30"></span>
                    <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-clock text-brandRed/70"></i><?= $readingTime ?> min read</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- POST BODY -->
<section class="bg-white pb-16 pt-10 sm:pb-20 sm:pt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[minmax(0,47rem)_minmax(250px,300px)] lg:justify-between lg:gap-14">

            <article class="mx-auto w-full min-w-0 max-w-[47rem] lg:mx-0">
                <!-- Cover -->
                <figure class="mb-10">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-[0_30px_70px_-42px_rgba(15,23,42,0.65)] sm:rounded-3xl">
                        <img src="<?= htmlspecialchars( $postImage, ENT_QUOTES, 'UTF-8' ) ?>" alt="<?= sanitize( $post[ 'title' ] ?? 'Blog story' ) ?>" class="aspect-[16/9] w-full object-cover" fetchpriority="high">
                    </div>
                    <figcaption class="mt-3 flex flex-wrap items-center justify-between gap-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                        <span><i class="fa-solid fa-camera-retro mr-1.5 text-brandRed/70"></i>WRC field documentation</span>
                        <span><?= formatDate( $publishedOn ) ?></span>
                    </figcaption>
                </figure>

                <div class="article-body blog-article-body">
                    <?= $bodyHtml ?>
                </div>

                <!-- Tags + share -->
                <div class="mt-12 border-t border-slate-200 pt-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Filed under</p>
                            <div class="flex flex-wrap gap-2">
                                <?php if ( $postTags ): ?>
                                <?php foreach ( $postTags as $postTag ): ?>
                                <a href="<?= $base ?>news/blog.php?tag=<?= rawurlencode( $postTag ) ?>" class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-brandRed hover:text-white"><?= sanitize( $postTag ) ?></a>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-500">Field notes</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="sm:text-right">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Share this story</p>
                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrlEnc ?>" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-all hover:bg-[#1877F2] hover:text-white" aria-label="Share on Facebook" title="Share on Facebook"><i class="fa-brands fa-facebook-f text-sm"></i></a>
                                <a href="https://x.com/intent/tweet?url=<?= $shareUrlEnc ?>&text=<?= $shareTitleEnc ?>" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-all hover:bg-slate-900 hover:text-white" aria-label="Share on X" title="Share on X"><i class="fa-brands fa-x-twitter text-sm"></i></a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $shareUrlEnc ?>&title=<?= $shareTitleEnc ?>" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-all hover:bg-[#0A66C2] hover:text-white" aria-label="Share on LinkedIn" title="Share on LinkedIn"><i class="fa-brands fa-linkedin-in text-sm"></i></a>
                                <a href="https://wa.me/?text=<?= $shareTitleEnc ?>%20<?= $shareUrlEnc ?>" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-all hover:bg-[#25D366] hover:text-white" aria-label="Share on WhatsApp" title="Share on WhatsApp"><i class="fa-brands fa-whatsapp text-sm"></i></a>
                                <button type="button" id="copy-link-btn" data-url="<?= htmlspecialchars( $shareUrl, ENT_QUOTES, 'UTF-8' ) ?>" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-all hover:bg-brandRed hover:text-white" aria-label="Copy link" title="Copy link"><i class="fa-solid fa-link text-sm"></i></button>
                            </div>
                            <p id="copy-link-status" class="mt-2 h-4 text-xs font-semibold text-brandRed" role="status" aria-live="polite"></p>
                        </div>
                    </div>
                </div>

                <!-- Author -->
                <div class="mt-10 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-ink-900 font-display text-lg font-semibold text-white"><?= sanitize( $initials ?: 'WR' ) ?></span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Written by</p>
                        <h2 class="mt-1 text-lg font-display font-bold text-ink-900"><?= sanitize( $authorName ) ?></h2>
                        <p class="mt-1 text-sm leading-relaxed text-slate-500">This story comes from WRC&rsquo;s programme, monitoring and emergency teams. For interviews, corrections or partnership enquiries, please <a href="<?= $base ?>contact/index.php" class="font-semibold text-brandRed hover:underline">contact the communications team</a>.</p>
                    </div>
                </div>

                <!-- Previous / next -->
                <?php if ( $prevPost || $nextPost ): ?>
                <nav class="mt-8 grid gap-4 sm:grid-cols-2" aria-label="More blog posts">
                    <?php if ( $prevPost ): ?>
                    <a href="<?= getBlogPostUrl( $prevPost ) ?>" class="group rounded-2xl border border-slate-200 p-5 transition-all hover:border-brandRed/40">
                        <span class="mb-2 flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                            <i class="fa-solid fa-arrow-left text-[9px] transition-colors group-hover:text-brandRed"></i> Previous story
                        </span>
                        <span class="block text-sm font-bold leading-snug text-ink-900 transition-colors group-hover:text-brandRed line-clamp-2"><?= sanitize( $prevPost[ 'title' ] ) ?></span>
                    </a>
                    <?php else: ?>
                    <div></div>
                    <?php endif; ?>

                    <?php if ( $nextPost ): ?>
                    <a href="<?= getBlogPostUrl( $nextPost ) ?>" class="group rounded-2xl border border-slate-200 p-5 text-right transition-all hover:border-brandRed/40">
                        <span class="mb-2 flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400 sm:justify-end">
                            Next story <i class="fa-solid fa-arrow-right text-[9px] transition-colors group-hover:text-brandRed"></i>
                        </span>
                        <span class="block text-sm font-bold leading-snug text-ink-900 transition-colors group-hover:text-brandRed line-clamp-2"><?= sanitize( $nextPost[ 'title' ] ) ?></span>
                    </a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>

                <a href="<?= $base ?>news/blog.php" class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition-colors hover:text-brandRed">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Back to the blog
                </a>
            </article>

            <!-- SIDEBAR -->
            <aside class="mx-auto w-full max-w-[47rem] space-y-5 self-start lg:mx-0 lg:sticky lg:top-32">
                <?php if ( count( $toc ) >= 2 ): ?>
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">On this page</p>
                        <span class="text-[10px] font-semibold text-slate-400"><?= count( $toc ) ?> sections</span>
                    </div>
                    <nav class="mt-3" aria-label="On this page">
                        <?php foreach ( $toc as $item ): ?>
                        <a href="#<?= htmlspecialchars( $item[ 'id' ], ENT_QUOTES, 'UTF-8' ) ?>" data-toc-link class="toc-link<?= $item[ 'level' ] === 3 ? ' toc-link-sub' : '' ?>">
                            <span class="toc-dot"></span>
                            <span><?= sanitize( $item[ 'text' ] ) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </nav>
                    <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4 text-[11px] font-semibold text-slate-400">
                        <span><i class="fa-solid fa-calendar-days mr-1 text-brandRed/70"></i><?= formatDate( $publishedOn ) ?></span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span><i class="fa-solid fa-clock mr-1 text-brandRed/70"></i><?= $readingTime ?> min read</span>
                    </div>
                </div>
                <?php else: ?>
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">In this story</p>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brandRed/10 text-brandRed"><i class="fa-solid fa-calendar-days text-xs"></i></span>
                            <span><?= formatDate( $publishedOn ) ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brandRed/10 text-brandRed"><i class="fa-solid fa-clock text-xs"></i></span>
                            <span><?= $readingTime ?> minute read</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brandRed/10 text-brandRed"><i class="fa-solid fa-user-pen text-xs"></i></span>
                            <span class="truncate"><?= sanitize( $authorName ) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="rounded-2xl bg-ink-900 p-5 text-white">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brandRed text-white"><i class="fa-solid fa-pen-nib text-xs"></i></span>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/50"><?= sanitize( $settings[ 'intro_eyebrow' ] ?: 'The WRC Blog' ) ?></p>
                    </div>
                    <h2 class="mt-3 font-display text-lg font-semibold leading-snug"><?= sanitize( $settings[ 'intro_title' ] ?: 'What we are learning, as we learn it' ) ?></h2>
                    <p class="mt-2 text-xs leading-relaxed text-white/60"><?= sanitize( mb_substr( (string) ( $settings[ 'intro_text' ] ?? '' ), 0, 180 ) ) ?></p>
                    <a href="<?= $base ?>news/blog.php" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-brandRed hover:gap-2.5 transition-all">Read all stories <i class="fa-solid fa-arrow-right text-[9px]"></i></a>
                </div>

                <?php if ( $postTags ): ?>
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Explore topics</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <?php foreach ( $postTags as $postTag ): ?>
                        <a href="<?= $base ?>news/blog.php?tag=<?= rawurlencode( $postTag ) ?>" class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-brandRed hover:text-white transition-colors"><?= sanitize( $postTag ) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="rounded-2xl border border-brandRed/15 bg-brandRed/5 p-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brandRed">Work with WRC</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">We welcome partnerships with donors, government departments and local organisations.</p>
                    <a href="<?= $base ?>get-involved/contribute-with-us.php" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brandRed px-4 py-2.5 text-xs font-bold text-white hover:bg-brandRed-hover transition-colors">Let&rsquo;s work together <i class="fa-solid fa-arrow-right text-[9px]"></i></a>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- RELATED -->
<?php if ( $relatedPosts ): ?>
<section class="bg-slate-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="text-brandRed text-[11px] font-bold uppercase tracking-[0.2em]">Keep reading</span>
                <h2 class="mt-1 text-2xl sm:text-3xl font-display font-bold text-ink-900">More from the <span class="text-brandRed">blog</span></h2>
            </div>
            <a href="<?= $base ?>news/blog.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brandRed transition-colors">View all stories <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <?php foreach ( $relatedPosts as $related ): ?>
            <?php
            $relatedImage   = getImageUrl( (string) ( $related[ 'featured_image' ] ?? '' ), 'assets/images/hero/news-hero.jpg' );
            $relatedTags    = getBlogTagList( $related[ 'tags' ] ?? '' );
            $relatedRead    = getBlogReadingTime( (string) ( $related[ 'content' ] ?? '' ) );
            $relatedExcerpt = getBlogExcerpt( $related, 130 );
            $relatedUrl     = getBlogPostUrl( $related );
            ?>
            <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300 hover:-translate-y-0.5 hover:border-brandRed/30 hover:shadow-xl">
                <a href="<?= $relatedUrl ?>" class="relative block aspect-[16/10] overflow-hidden bg-ink-900">
                    <img src="<?= htmlspecialchars( $relatedImage, ENT_QUOTES, 'UTF-8' ) ?>" alt="<?= sanitize( $related[ 'title' ] ?? 'Blog story' ) ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/55 via-transparent to-transparent"></div>
                    <?php if ( $relatedTags ): ?>
                    <span class="absolute left-4 top-4 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-ink-900 backdrop-blur"><?= sanitize( $relatedTags[ 0 ] ) ?></span>
                    <?php endif; ?>
                    <span class="absolute bottom-4 right-4 rounded-full bg-ink-900/80 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur"><?= $relatedRead ?> min</span>
                </a>
                <div class="p-5">
                    <div class="text-[11px] text-slate-400"><i class="fa-solid fa-calendar-days mr-1 text-brandRed/60"></i> <?= formatDate( $related[ 'published_at' ] ?? date( 'Y-m-d' ) ) ?></div>
                    <h3 class="mt-2 text-base font-bold leading-snug text-ink-900 transition-colors group-hover:text-brandRed line-clamp-2"><a href="<?= $relatedUrl ?>"><?= sanitize( $related[ 'title' ] ?? '' ) ?></a></h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 line-clamp-2"><?= sanitize( $relatedExcerpt ) ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
(function () {
  var btn = document.getElementById('copy-link-btn');
  var status = document.getElementById('copy-link-status');
  if (!btn) return;
  btn.addEventListener('click', function () {
    var url = btn.getAttribute('data-url') || window.location.href;
    var done = function () {
      if (status) {
        status.textContent = 'Link copied to clipboard.';
        window.setTimeout(function () { status.textContent = ''; }, 2500);
      }
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(done).catch(function () { window.prompt('Copy this link:', url); });
    } else {
      window.prompt('Copy this link:', url);
    }
  });
})();

(function () {
  var links = Array.prototype.slice.call(document.querySelectorAll('[data-toc-link]'));
  if (!links.length) return;

  var items = links.map(function (link) {
    var id = link.getAttribute('href').replace(/^#/, '');
    return { link: link, el: document.getElementById(id) };
  }).filter(function (item) { return item.el; });
  if (!items.length) return;

  var current = null;

  function setActive(item) {
    if (item === current) return;
    current = item;
    links.forEach(function (link) { link.classList.remove('active'); });
    if (item) item.link.classList.add('active');
  }

  function update() {
    var active = items[0];
    items.forEach(function (item) {
      if (item.el.getBoundingClientRect().top <= 150) active = item;
    });
    if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
      active = items[items.length - 1];
    }
    setActive(active);
  }

  function onScroll() {
    update();
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  update();
})();
</script>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
