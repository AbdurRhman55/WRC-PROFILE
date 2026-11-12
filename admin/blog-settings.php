<?php
/**
 * WRC Admin - blog landing-page settings.
 *
 * Controls the hero, introduction and call to action on news/blog.php. The
 * posts themselves are managed on blog.php.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Blog Settings';
$errors    = [];

global $pdo;

$row = $pdo->query( 'SELECT * FROM blog_settings ORDER BY id ASC LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
if ( !$row ) {
    $pdo->exec(
        "INSERT INTO blog_settings
            (hero_badge, hero_title, hero_subtitle, hero_image, intro_eyebrow, intro_title, intro_text, cta_text, cta_link, posts_per_page)
         VALUES
            ('Insights & Field Notes', 'Stories and ideas from the field',
             'Practical lessons, programme insight and human stories from WRC teams working across Pakistan.',
             'assets/images/hero/news-hero.jpg', 'The WRC Blog',
             'What we are learning, as we learn it',
             'Long-form reflections from our programme, monitoring and emergency teams.',
             'Partner with WRC', 'get-involved/contribute-with-us.php', 6)"
    );
    $row = $pdo->query( 'SELECT * FROM blog_settings ORDER BY id ASC LIMIT 1' )->fetch( PDO::FETCH_ASSOC );
}

$form = [
    'hero_badge'     => (string) ( $row[ 'hero_badge' ] ?? '' ),
    'hero_title'     => (string) ( $row[ 'hero_title' ] ?? '' ),
    'hero_subtitle'  => (string) ( $row[ 'hero_subtitle' ] ?? '' ),
    'hero_image'     => (string) ( $row[ 'hero_image' ] ?? '' ),
    'intro_eyebrow'  => (string) ( $row[ 'intro_eyebrow' ] ?? '' ),
    'intro_title'    => (string) ( $row[ 'intro_title' ] ?? '' ),
    'intro_text'     => (string) ( $row[ 'intro_text' ] ?? '' ),
    'cta_text'       => (string) ( $row[ 'cta_text' ] ?? '' ),
    'cta_link'       => (string) ( $row[ 'cta_link' ] ?? '' ),
    'posts_per_page' => (int) ( $row[ 'posts_per_page' ] ?? 6 ),
];

if ( isPost() ) {
    if ( !isset( $_POST[ 'csrf_token' ] ) || !verifyCSRF( $_POST[ 'csrf_token' ] ) ) {
        setFlash( 'error', 'Invalid security token.' );
        redirect( 'blog-settings.php' );
    }

    $form[ 'hero_badge' ]     = getPost( 'hero_badge' );
    $form[ 'hero_title' ]     = getPost( 'hero_title' );
    $form[ 'hero_subtitle' ]  = getPost( 'hero_subtitle' );
    $form[ 'hero_image' ]     = getPost( 'hero_image' );
    $form[ 'intro_eyebrow' ]  = getPost( 'intro_eyebrow' );
    $form[ 'intro_title' ]    = getPost( 'intro_title' );
    $form[ 'intro_text' ]     = getPost( 'intro_text' );
    $form[ 'cta_text' ]       = getPost( 'cta_text' );
    $form[ 'cta_link' ]       = getPost( 'cta_link' );
    $form[ 'posts_per_page' ] = max( 3, min( 24, (int) getPost( 'posts_per_page', '6' ) ) );

    if ( $form[ 'hero_title' ] === '' ) {
        $errors[] = 'The hero title is required.';
    }

    if ( empty( $errors ) ) {
        $stmt = $pdo->prepare(
            'UPDATE blog_settings
                SET hero_badge = ?, hero_title = ?, hero_subtitle = ?, hero_image = ?,
                    intro_eyebrow = ?, intro_title = ?, intro_text = ?,
                    cta_text = ?, cta_link = ?, posts_per_page = ?
              WHERE id = ?'
        );
        $stmt->execute( [
            $form[ 'hero_badge' ],
            $form[ 'hero_title' ],
            $form[ 'hero_subtitle' ],
            $form[ 'hero_image' ],
            $form[ 'intro_eyebrow' ],
            $form[ 'intro_title' ],
            $form[ 'intro_text' ],
            $form[ 'cta_text' ],
            $form[ 'cta_link' ],
            $form[ 'posts_per_page' ],
            (int) $row[ 'id' ],
        ] );

        setFlash( 'success', 'Blog page settings updated.' );
        redirect( 'blog-settings.php' );
    }
}

$previewImage = getImageUrl( $form[ 'hero_image' ], 'assets/images/hero/news-hero.jpg' );

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">Blog Page Settings</h1>
    <p class="text-slate-500 text-sm mt-1">Edit the hero, introduction and call to action on the public blog landing page.</p>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <a href="../news/blog.php" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
      <i class="fa-solid fa-eye text-xs text-slate-400"></i> View blog
    </a>
    <a href="blog.php" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition-colors hover:text-slate-900">
      <i class="fa-solid fa-arrow-left text-xs"></i> Back to Blog
    </a>
  </div>
</div>

<?php if ( !empty( $errors ) ): ?>
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
  <div class="mb-1 flex items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span class="text-sm font-semibold">Please fix the following:</span></div>
  <ul class="mt-1 list-inside list-disc text-sm">
    <?php foreach ( $errors as $error ): ?><li><?= htmlspecialchars( $error ) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <div class="space-y-6">
      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900">Hero section</h2>
        <div class="space-y-4">
          <div>
            <label for="heroBadge" class="mb-1 block text-sm font-medium text-slate-700">Eyebrow / badge</label>
            <input id="heroBadge" type="text" name="hero_badge" value="<?= htmlspecialchars( $form[ 'hero_badge' ], ENT_QUOTES, 'UTF-8' ) ?>" placeholder="Insights & Field Notes" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
          </div>
          <div>
            <label for="heroTitle" class="mb-1 block text-sm font-medium text-slate-700">Hero title *</label>
            <input id="heroTitle" type="text" name="hero_title" value="<?= htmlspecialchars( $form[ 'hero_title' ], ENT_QUOTES, 'UTF-8' ) ?>" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
          </div>
          <div>
            <label for="heroSubtitle" class="mb-1 block text-sm font-medium text-slate-700">Hero subtitle</label>
            <textarea id="heroSubtitle" name="hero_subtitle" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm leading-relaxed focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20"><?= htmlspecialchars( $form[ 'hero_subtitle' ], ENT_QUOTES, 'UTF-8' ) ?></textarea>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900">Introduction above the story grid</h2>
        <div class="space-y-4">
          <div>
            <label for="introEyebrow" class="mb-1 block text-sm font-medium text-slate-700">Eyebrow</label>
            <input id="introEyebrow" type="text" name="intro_eyebrow" value="<?= htmlspecialchars( $form[ 'intro_eyebrow' ], ENT_QUOTES, 'UTF-8' ) ?>" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
          </div>
          <div>
            <label for="introTitle" class="mb-1 block text-sm font-medium text-slate-700">Section title</label>
            <input id="introTitle" type="text" name="intro_title" value="<?= htmlspecialchars( $form[ 'intro_title' ], ENT_QUOTES, 'UTF-8' ) ?>" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
          </div>
          <div>
            <label for="introText" class="mb-1 block text-sm font-medium text-slate-700">Introduction</label>
            <textarea id="introText" name="intro_text" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm leading-relaxed focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20"><?= htmlspecialchars( $form[ 'intro_text' ], ENT_QUOTES, 'UTF-8' ) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <?php uploadWidget( 'hero_image', 'Hero background image', $form[ 'hero_image' ], 'image', 'blogHeroImage' ); ?>
        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
          <img src="<?= htmlspecialchars( $previewImage, ENT_QUOTES, 'UTF-8' ) ?>" alt="Blog hero preview" class="h-32 w-full object-cover">
        </div>
        <p class="mt-2 text-xs text-slate-400">Shown behind the hero copy with a dark overlay. A wide landscape image works best.</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900">Call to action</h2>
        <div class="space-y-4">
          <div>
            <label for="ctaText" class="mb-1 block text-sm font-medium text-slate-700">Button label</label>
            <input id="ctaText" type="text" name="cta_text" value="<?= htmlspecialchars( $form[ 'cta_text' ], ENT_QUOTES, 'UTF-8' ) ?>" placeholder="Partner with WRC" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
          </div>
          <div>
            <label for="ctaLink" class="mb-1 block text-sm font-medium text-slate-700">Button link</label>
            <input id="ctaLink" type="text" name="cta_link" value="<?= htmlspecialchars( $form[ 'cta_link' ], ENT_QUOTES, 'UTF-8' ) ?>" placeholder="get-involved/contribute-with-us.php" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
            <p class="mt-1 text-xs text-slate-400">Relative to the site root, or a full https:// URL.</p>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <label for="postsPerPage" class="mb-1 block text-sm font-medium text-slate-700">Stories per page</label>
        <input id="postsPerPage" type="number" name="posts_per_page" min="3" max="24" value="<?= (int) $form[ 'posts_per_page' ] ?>" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
        <p class="mt-1 text-xs text-slate-400">Between 3 and 24. The featured story is shown separately.</p>

        <button type="submit" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brandRed px-5 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-brandRed-hover">
          <i class="fa-solid fa-check text-xs"></i> Save settings
        </button>
      </div>
    </div>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
