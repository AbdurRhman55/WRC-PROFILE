<?php
/**
 * WRC Blog - public listing.
 *
 * Every heading, post, topic and image on this page comes from the dashboard:
 * blog_settings powers the hero and introduction, and news_articles rows with
 * category = 'blog' power the featured story, filters and cards.
 */
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base     = siteBaseUrl();
$settings = getBlogSettings();

$search   = trim( (string) ( $_GET[ 'q' ] ?? '' ) );
$tag      = trim( (string) ( $_GET[ 'tag' ] ?? '' ) );
$page     = max( 1, (int) ( $_GET[ 'page' ] ?? 1 ) );
$perPage  = max( 3, min( 24, (int) ( $settings[ 'posts_per_page' ] ?? 6 ) ) );
$filtered = $search !== '' || $tag !== '';

$featured = $filtered ? null : getFeaturedBlogPost();
$exclude  = $featured ? (int) $featured[ 'id' ] : 0;

$total      = countBlogPosts( $tag, $search, $exclude );
$totalPages = max( 1, (int) ceil( $total / $perPage ) );
$page       = min( $page, $totalPages );
$posts      = getBlogPosts( $perPage, ( $page - 1 ) * $perPage, $tag, $search, $exclude );

$allTags  = getBlogTags();
$allCount = countBlogPosts();

$heroImage = getImageUrl( (string) ( $settings[ 'hero_image' ] ?? '' ), 'assets/images/hero/news-hero.jpg' );

$filterUrl = static function ( array $params ): string {
    $params = array_filter( $params, static function ( $value ) {
        return $value !== '' && $value !== null;
    } );
    return 'blog.php' . ( $params ? '?' . http_build_query( $params ) : '' );
};

$ctaHref = (string) ( $settings[ 'cta_link' ] ?? '' );
if ( $ctaHref !== '' && !preg_match( '~^(?:https?:)?//~i', $ctaHref ) ) {
    $ctaHref = $base . ltrim( $ctaHref, '/' );
}
if ( $ctaHref === '' ) {
    $ctaHref = $base . 'get-involved/contribute-with-us.php';
}

$pageTitle = $tag !== ''
    ? sanitize( $tag ) . ' | WRC Blog'
    : ( $search !== '' ? 'Search: ' . sanitize( $search ) . ' | WRC Blog' : 'Blog | Welfare & Relief Committee (WRC)' );

$metaDescription = sanitize( mb_substr( strip_tags( (string) ( $settings[ 'intro_text' ] ?: $settings[ 'hero_subtitle' ] ) ), 0, 160 ) );

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- BLOG HERO -->
<section class="relative overflow-hidden bg-ink-900 text-white">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center opacity-30 scale-105" style="background-image: url('<?= htmlspecialchars( $heroImage, ENT_QUOTES, 'UTF-8' ) ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-ink-900 via-ink-900/90 to-ink-900/50"></div>
        <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(rgba(255,255,255,.35) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.35) 1px, transparent 1px); background-size: 54px 54px;"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-20 sm:pt-16 sm:pb-24">
        <nav class="flex flex-wrap items-center gap-2 text-xs text-white/55 mb-8" aria-label="Breadcrumb">
            <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
            <i class="fa-solid fa-chevron-right text-[8px] text-white/35"></i>
            <a href="<?= $base ?>news/index.php" class="hover:text-white transition-colors">News &amp; Media</a>
            <i class="fa-solid fa-chevron-right text-[8px] text-white/35"></i>
            <span class="text-brandRed font-semibold">Blog</span>
        </nav>

        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-brandRed/90 px-4 py-1.5 text-[10px] font-bold uppercase tracking-[0.2em] text-white shadow-lg shadow-brandRed/20">
                <i class="fa-solid fa-pen-nib text-[10px]"></i> <?= sanitize( $settings[ 'hero_badge' ] ?: 'Insights & Field Notes' ) ?>
            </span>

            <h1 class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-display font-bold leading-[1.08] tracking-tight">
                <?= sanitize( $settings[ 'hero_title' ] ?: 'Stories and ideas from the field' ) ?>
            </h1>

            <p class="mt-5 max-w-2xl text-base sm:text-lg leading-relaxed text-white/70">
                <?= sanitize( $settings[ 'hero_subtitle' ] ?: 'Practical lessons, programme insight and human stories from WRC teams working across Pakistan.' ) ?>
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="#stories" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-ink-900 hover:bg-brandRed hover:text-white transition-all shadow-lg">
                    Browse stories <i class="fa-solid fa-arrow-down text-[10px]"></i>
                </a>
                <a href="<?= $base ?>news/index.php" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-5 py-3 text-sm font-semibold text-white/85 hover:bg-white/15 transition-all">
                    News &amp; Media <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </div>

            <dl class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-4 border-t border-white/10 pt-6">
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/40">Published stories</dt>
                    <dd class="mt-1 text-2xl font-display font-semibold text-white"><?= (int) $allCount ?></dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/40">Topics covered</dt>
                    <dd class="mt-1 text-2xl font-display font-semibold text-white"><?= count( $allTags ) ?></dd>
                </div>
                <div class="max-w-xs">
                    <dt class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/40">Written by</dt>
                    <dd class="mt-1 text-sm font-semibold text-white/85">WRC programme, MEAL and emergency teams</dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<?php if ( $featured ): ?>
<?php
$featuredImage   = getImageUrl( (string) ( $featured[ 'featured_image' ] ?? '' ), 'assets/images/hero/news-hero.jpg' );
$featuredTags    = getBlogTagList( $featured[ 'tags' ] ?? '' );
$featuredRead    = getBlogReadingTime( (string) ( $featured[ 'content' ] ?? '' ) );
$featuredExcerpt = getBlogExcerpt( $featured, 260 );
$featuredUrl     = getBlogPostUrl( $featured );
?>
<!-- FEATURED STORY -->
<section class="relative z-20 -mt-10 pb-4 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <article class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_24px_70px_-32px_rgba(15,23,42,0.45)]">
            <div class="grid lg:grid-cols-[1.08fr_0.92fr]">
                <a href="<?= $featuredUrl ?>" class="relative block min-h-[280px] lg:min-h-[430px] overflow-hidden bg-ink-900">
                    <img src="<?= htmlspecialchars( $featuredImage, ENT_QUOTES, 'UTF-8' ) ?>" alt="<?= sanitize( $featured[ 'title' ] ?? 'Featured blog story' ) ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" loading="eager">
                    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/75 via-ink-900/10 to-transparent"></div>
                    <span class="absolute left-5 top-5 inline-flex items-center gap-2 rounded-full bg-brandRed px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-white shadow-lg">
                        <i class="fa-solid fa-star text-[9px]"></i> Featured story
                    </span>
                    <span class="absolute bottom-5 left-5 inline-flex items-center gap-2 rounded-full bg-white/95 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-ink-900 backdrop-blur">
                        <i class="fa-solid fa-clock text-brandRed text-[9px]"></i> <?= $featuredRead ?> min read
                    </span>
                </a>

                <div class="flex flex-col justify-center p-6 sm:p-9 lg:p-10">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-400 mb-4">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-brandRed/70"></i> <?= formatDate( $featured[ 'published_at' ] ?? date( 'Y-m-d' ) ) ?></span>
                        <?php if ( !empty( $featured[ 'author' ] ) ): ?>
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-pen text-brandRed/70"></i> <?= sanitize( $featured[ 'author' ] ) ?></span>
                        <?php endif; ?>
                    </div>

                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-display font-bold leading-tight text-ink-900 group-hover:text-brandRed transition-colors">
                        <a href="<?= $featuredUrl ?>"><?= sanitize( $featured[ 'title' ] ?? '' ) ?></a>
                    </h2>

                    <p class="mt-4 text-sm sm:text-base leading-relaxed text-slate-500"><?= sanitize( $featuredExcerpt ) ?></p>

                    <?php if ( $featuredTags ): ?>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <?php foreach ( $featuredTags as $featuredTag ): ?>
                        <a href="<?= htmlspecialchars( $filterUrl( [ 'tag' => $featuredTag ] ), ENT_QUOTES, 'UTF-8' ) ?>" class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500 hover:bg-brandRed hover:text-white transition-colors"><?= sanitize( $featuredTag ) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="<?= $featuredUrl ?>" class="inline-flex items-center gap-2 rounded-xl bg-brandRed px-5 py-3 text-sm font-bold text-white shadow-lg shadow-brandRed/20 hover:bg-brandRed-hover transition-all">
                            Read the story <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                        <span class="text-xs font-semibold text-slate-400">Editors&rsquo; pick</span>
                    </div>
                </div>
            </div>
        </article>
    </div>
</section>
<?php endif; ?>

<!-- STORY INDEX -->
<section id="stories" class="bg-slate-50 py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between mb-8">
            <div class="max-w-2xl">
                <span class="text-brandRed text-[11px] font-bold uppercase tracking-[0.2em]"><?= sanitize( $settings[ 'intro_eyebrow' ] ?: 'The WRC Blog' ) ?></span>
                <h2 class="mt-2 text-3xl sm:text-4xl font-display font-bold text-ink-900"><?= sanitize( $settings[ 'intro_title' ] ?: 'What we are learning, as we learn it' ) ?></h2>
                <?php if ( !empty( $settings[ 'intro_text' ] ) ): ?>
                <p class="mt-3 text-sm sm:text-base leading-relaxed text-slate-500"><?= sanitize( $settings[ 'intro_text' ] ) ?></p>
                <?php endif; ?>
            </div>

            <form action="blog.php" method="get" class="w-full lg:w-[360px] shrink-0" role="search">
                <?php if ( $tag !== '' ): ?>
                <input type="hidden" name="tag" value="<?= htmlspecialchars( $tag, ENT_QUOTES, 'UTF-8' ) ?>">
                <?php endif; ?>
                <label for="blog-search" class="sr-only">Search the blog</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="blog-search" type="search" name="q" value="<?= htmlspecialchars( $search, ENT_QUOTES, 'UTF-8' ) ?>" placeholder="Search stories, topics or authors..." class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-24 text-sm text-ink-900 placeholder:text-slate-400 shadow-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg bg-ink-900 px-4 py-2 text-xs font-bold text-white hover:bg-brandRed transition-colors">Search</button>
                </div>
            </form>
        </div>

        <?php if ( $allTags ): ?>
        <div class="mb-8 flex flex-wrap items-center gap-2">
            <span class="mr-1 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Topics</span>
            <a href="<?= htmlspecialchars( $filterUrl( [ 'q' => $search ] ), ENT_QUOTES, 'UTF-8' ) ?>" class="rounded-full px-4 py-2 text-xs font-semibold transition-colors <?= $tag === '' ? 'bg-ink-900 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:border-brandRed/30 hover:text-brandRed' ?>">All stories</a>
            <?php foreach ( $allTags as $topic => $topicCount ): ?>
            <a href="<?= htmlspecialchars( $filterUrl( [ 'q' => $search, 'tag' => $topic ] ), ENT_QUOTES, 'UTF-8' ) ?>" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold transition-colors <?= $tag === $topic ? 'bg-brandRed text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:border-brandRed/30 hover:text-brandRed' ?>">
                <?= sanitize( $topic ) ?>
                <span class="<?= $tag === $topic ? 'text-white/70' : 'text-slate-400' ?> text-[10px]"><?= (int) $topicCount ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ( $search !== '' || $tag !== '' ): ?>
        <div class="mb-8 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">
            <i class="fa-solid fa-filter text-brandRed text-xs"></i>
            <span>
                Showing
                <?php if ( $tag !== '' ): ?><strong class="text-ink-900"><?= sanitize( $tag ) ?></strong><?php endif; ?>
                <?php if ( $tag !== '' && $search !== '' ): ?> matching <?php endif; ?>
                <?php if ( $search !== '' ): ?>&ldquo;<strong class="text-ink-900"><?= sanitize( $search ) ?></strong>&rdquo;<?php endif; ?>
                &middot; <?= (int) $total ?> result<?= $total === 1 ? '' : 's' ?>
            </span>
            <a href="blog.php" class="ml-auto inline-flex items-center gap-1.5 text-xs font-bold text-brandRed hover:underline">Clear filters <i class="fa-solid fa-xmark text-[9px]"></i></a>
        </div>
        <?php endif; ?>

        <?php if ( empty( $posts ) ): ?>
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brandRed/10 text-brandRed">
                <i class="fa-solid fa-pen-nib text-xl"></i>
            </span>
            <h3 class="text-xl font-display font-bold text-ink-900">No stories found</h3>
            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500">
                <?= ( $search !== '' || $tag !== '' ) ? 'Try a different topic or a broader search term.' : 'The first blog stories are being prepared. Check back soon for field notes and programme insight.' ?>
            </p>
            <?php if ( $search !== '' || $tag !== '' ): ?>
            <a href="blog.php" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-brandRed px-5 py-3 text-sm font-bold text-white hover:bg-brandRed-hover transition-colors">View all stories <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ( $posts as $post ): ?>
            <?php
            $postImage   = getImageUrl( (string) ( $post[ 'featured_image' ] ?? '' ), 'assets/images/hero/news-hero.jpg' );
            $postTags    = getBlogTagList( $post[ 'tags' ] ?? '' );
            $postRead    = getBlogReadingTime( (string) ( $post[ 'content' ] ?? '' ) );
            $postExcerpt = getBlogExcerpt( $post, 165 );
            $postUrl     = getBlogPostUrl( $post );
            ?>
            <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white transition-all duration-300 hover:-translate-y-0.5 hover:border-brandRed/30 hover:shadow-xl">
                <a href="<?= $postUrl ?>" class="relative block aspect-[16/10] overflow-hidden bg-ink-900">
                    <img src="<?= htmlspecialchars( $postImage, ENT_QUOTES, 'UTF-8' ) ?>" alt="<?= sanitize( $post[ 'title' ] ?? 'Blog story' ) ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/55 via-transparent to-transparent"></div>
                    <?php if ( $postTags ): ?>
                    <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-ink-900 backdrop-blur">
                        <i class="fa-solid fa-tag text-brandRed text-[8px]"></i> <?= sanitize( $postTags[ 0 ] ) ?>
                    </span>
                    <?php endif; ?>
                    <span class="absolute bottom-4 right-4 inline-flex items-center gap-1.5 rounded-full bg-ink-900/80 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur">
                        <i class="fa-solid fa-clock text-[8px]"></i> <?= $postRead ?> min
                    </span>
                </a>

                <div class="flex flex-1 flex-col p-5 sm:p-6">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-400">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-brandRed/60"></i> <?= formatDate( $post[ 'published_at' ] ?? date( 'Y-m-d' ) ) ?></span>
                        <?php if ( !empty( $post[ 'author' ] ) ): ?>
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-pen text-brandRed/60"></i> <?= sanitize( $post[ 'author' ] ) ?></span>
                        <?php endif; ?>
                    </div>

                    <h3 class="mt-3 text-lg font-bold leading-snug text-ink-900 transition-colors group-hover:text-brandRed">
                        <a href="<?= $postUrl ?>"><?= sanitize( $post[ 'title' ] ?? '' ) ?></a>
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-slate-500 line-clamp-3"><?= sanitize( $postExcerpt ) ?></p>

                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-4 mt-5">
                        <a href="<?= $postUrl ?>" class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-[0.1em] text-brandRed hover:gap-2.5 transition-all">
                            Read story <i class="fa-solid fa-arrow-right text-[9px]"></i>
                        </a>
                        <?php if ( count( $postTags ) > 1 ): ?>
                        <span class="text-[10px] font-semibold text-slate-400"><?= count( $postTags ) ?> topics</span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ( $totalPages > 1 ): ?>
        <nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Blog pagination">
            <a href="<?= htmlspecialchars( $filterUrl( [ 'q' => $search, 'tag' => $tag, 'page' => max( 1, $page - 1 ) ] ), ENT_QUOTES, 'UTF-8' ) ?>" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 <?= $page <= 1 ? 'pointer-events-none opacity-40' : 'hover:border-brandRed/30 hover:text-brandRed' ?> transition-colors">
                <i class="fa-solid fa-arrow-left text-[9px]"></i> Previous
            </a>

            <?php for ( $p = 1; $p <= $totalPages; $p++ ): ?>
            <a href="<?= htmlspecialchars( $filterUrl( [ 'q' => $search, 'tag' => $tag, 'page' => $p ] ), ENT_QUOTES, 'UTF-8' ) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-xs font-bold transition-colors <?= $p === $page ? 'bg-brandRed text-white shadow-lg shadow-brandRed/20' : 'border border-slate-200 bg-white text-slate-600 hover:border-brandRed/30 hover:text-brandRed' ?>" <?= $p === $page ? 'aria-current="page"' : '' ?>><?= $p ?></a>
            <?php endfor; ?>

            <a href="<?= htmlspecialchars( $filterUrl( [ 'q' => $search, 'tag' => $tag, 'page' => min( $totalPages, $page + 1 ) ] ), ENT_QUOTES, 'UTF-8' ) ?>" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 <?= $page >= $totalPages ? 'pointer-events-none opacity-40' : 'hover:border-brandRed/30 hover:text-brandRed' ?> transition-colors">
                Next <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </a>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-ink-900 px-6 py-10 sm:px-10 sm:py-12 lg:px-14">
            <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-brandRed/25 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-brandRed">Work with us</span>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-display font-bold text-white">Have a story, a lesson or a partnership to share?</h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/60">WRC works with donors, government departments, local organisations and field teams. If our work overlaps with yours, we would like to hear from you.</p>
                </div>
                <div class="flex flex-wrap gap-3 shrink-0">
                    <a href="<?= htmlspecialchars( $ctaHref, ENT_QUOTES, 'UTF-8' ) ?>" class="inline-flex items-center gap-2 rounded-xl bg-brandRed px-5 py-3 text-sm font-bold text-white shadow-lg shadow-brandRed/20 hover:bg-brandRed-hover transition-colors">
                        <?= sanitize( $settings[ 'cta_text' ] ?: 'Partner with WRC' ) ?> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                    <a href="<?= $base ?>contact/index.php" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-5 py-3 text-sm font-semibold text-white/85 hover:bg-white/15 transition-colors">
                        Contact us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
