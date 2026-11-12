<?php
/**
 * WRC Admin - Blog manager.
 *
 * Blog posts are news_articles rows with category = 'blog'. Keeping the same
 * table means permissions, image uploads and the public news ticker all keep
 * working, while this screen gives editors blog-specific controls.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Blog';

if ( isPost() ) {
    if ( !isset( $_POST[ 'csrf_token' ] ) || !verifyCSRF( $_POST[ 'csrf_token' ] ) ) {
        setFlash( 'error', 'Invalid security token.' );
        redirect( 'blog.php' );
    }

    global $pdo;

    if ( isset( $_POST[ 'delete_id' ] ) ) {
        $stmt = $pdo->prepare( "DELETE FROM news_articles WHERE id = ? AND category = 'blog'" );
        $stmt->execute( [ (int) $_POST[ 'delete_id' ] ] );
        setFlash( 'success', 'Blog post deleted successfully.' );
        redirect( 'blog.php' );
    }

    if ( isset( $_POST[ 'toggle_publish_id' ] ) ) {
        $stmt = $pdo->prepare( "UPDATE news_articles SET is_published = IF(is_published = 1, 0, 1) WHERE id = ? AND category = 'blog'" );
        $stmt->execute( [ (int) $_POST[ 'toggle_publish_id' ] ] );
        setFlash( 'success', 'Post visibility updated.' );
        redirect( 'blog.php' );
    }

    if ( isset( $_POST[ 'toggle_featured_id' ] ) ) {
        $id = (int) $_POST[ 'toggle_featured_id' ];
        $current = $pdo->prepare( "SELECT is_featured FROM news_articles WHERE id = ? AND category = 'blog'" );
        $current->execute( [ $id ] );
        $isFeatured = (int) $current->fetchColumn();

        if ( $isFeatured ) {
            $pdo->prepare( 'UPDATE news_articles SET is_featured = 0 WHERE id = ?' )->execute( [ $id ] );
        } else {
            // A single featured story keeps the blog landing page unambiguous.
            $pdo->exec( "UPDATE news_articles SET is_featured = 0 WHERE category = 'blog'" );
            $pdo->prepare( 'UPDATE news_articles SET is_featured = 1 WHERE id = ?' )->execute( [ $id ] );
        }

        setFlash( 'success', $isFeatured ? 'Post removed from the featured slot.' : 'Post is now the featured story.' );
        redirect( 'blog.php' );
    }
}

global $pdo;

$total     = (int) $pdo->query( "SELECT COUNT(*) FROM news_articles WHERE category = 'blog'" )->fetchColumn();
$published = (int) $pdo->query( "SELECT COUNT(*) FROM news_articles WHERE category = 'blog' AND is_published = 1" )->fetchColumn();
$drafts    = $total - $published;
$featured  = (int) $pdo->query( "SELECT COUNT(*) FROM news_articles WHERE category = 'blog' AND is_featured = 1" )->fetchColumn();

$status = trim( (string) ( $_GET[ 'status' ] ?? 'all' ) );
$search = trim( (string) ( $_GET[ 'q' ] ?? '' ) );

$where  = [ "category = 'blog'" ];
$params = [];

if ( $status === 'published' ) {
    $where[] = 'is_published = 1';
} elseif ( $status === 'draft' ) {
    $where[] = 'is_published = 0';
} elseif ( $status === 'featured' ) {
    $where[] = 'is_featured = 1';
}

if ( $search !== '' ) {
    $where[]  = '(title LIKE ? OR excerpt LIKE ? OR author LIKE ? OR tags LIKE ?)';
    $needle   = '%' . $search . '%';
    $params[] = $needle;
    $params[] = $needle;
    $params[] = $needle;
    $params[] = $needle;
}

$stmt = $pdo->prepare(
    'SELECT * FROM news_articles WHERE ' . implode( ' AND ', $where )
    . ' ORDER BY is_featured DESC, published_at DESC, id DESC'
);
$stmt->execute( $params );
$posts = $stmt->fetchAll( PDO::FETCH_ASSOC );

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">Blog</h1>
    <p class="text-slate-500 text-sm mt-1">Write, publish and feature stories from the WRC blog.</p>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <a href="blog-settings.php" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
      <i class="fa-solid fa-sliders text-xs text-slate-400"></i> Page settings
    </a>
    <a href="blog-edit.php" class="inline-flex items-center gap-2 rounded-xl bg-brandRed px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brandRed-hover">
      <i class="fa-solid fa-plus text-xs"></i> New Post
    </a>
  </div>
</div>

<!-- Stats -->
<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
  <div class="rounded-2xl border border-slate-200 bg-white p-5">
    <div class="flex items-center justify-between">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total posts</span>
      <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="fa-solid fa-pen-nib text-xs"></i></span>
    </div>
    <p class="mt-3 text-2xl font-bold text-slate-900"><?= $total ?></p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5">
    <div class="flex items-center justify-between">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Published</span>
      <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-50 text-green-600"><i class="fa-solid fa-circle-check text-xs"></i></span>
    </div>
    <p class="mt-3 text-2xl font-bold text-slate-900"><?= $published ?></p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5">
    <div class="flex items-center justify-between">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Drafts</span>
      <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><i class="fa-solid fa-file-pen text-xs"></i></span>
    </div>
    <p class="mt-3 text-2xl font-bold text-slate-900"><?= $drafts ?></p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5">
    <div class="flex items-center justify-between">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Featured</span>
      <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brandRed/10 text-brandRed"><i class="fa-solid fa-star text-xs"></i></span>
    </div>
    <p class="mt-3 text-2xl font-bold text-slate-900"><?= $featured ?></p>
  </div>
</div>

<!-- Filters -->
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4">
  <form method="GET" action="blog.php" class="flex flex-col gap-3 lg:flex-row lg:items-center">
    <div class="relative flex-1">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
      <input type="text" name="q" value="<?= htmlspecialchars( $search, ENT_QUOTES, 'UTF-8' ) ?>" placeholder="Search posts, topics or authors..." class="w-full rounded-xl border border-slate-200 py-2.5 pl-10 pr-4 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <select name="status" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-600 focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All posts</option>
        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Drafts</option>
        <option value="featured" <?= $status === 'featured' ? 'selected' : '' ?>>Featured</option>
      </select>
      <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-ink-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brandRed transition-colors">
        <i class="fa-solid fa-filter text-[10px]"></i> Filter
      </button>
      <?php if ( $search !== '' || $status !== 'all' ): ?>
      <a href="blog.php" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-50 transition-colors">Clear <i class="fa-solid fa-xmark text-[9px]"></i></a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Table -->
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50">
          <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Story</th>
          <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Topics</th>
          <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Author</th>
          <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Published</th>
          <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Status</th>
          <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">Featured</th>
          <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ( empty( $posts ) ): ?>
        <tr>
          <td colspan="7" class="px-5 py-16 text-center">
            <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fa-solid fa-pen-nib text-xl"></i></span>
            <h3 class="text-base font-bold text-slate-700">No blog posts found</h3>
            <p class="mt-1 text-sm text-slate-400"><?= ( $search !== '' || $status !== 'all' ) ? 'Try adjusting the filters.' : 'Write the first story to get the blog started.' ?></p>
            <a href="blog-edit.php" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brandRed px-5 py-2.5 text-sm font-semibold text-white hover:bg-brandRed-hover transition-colors"><i class="fa-solid fa-plus text-xs"></i> New Post</a>
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ( $posts as $index => $post ): ?>
        <?php
        $postImage = getImageUrl( (string) ( $post[ 'featured_image' ] ?? '' ), 'assets/images/hero/news-hero.jpg' );
        $postTags  = getBlogTagList( $post[ 'tags' ] ?? '' );
        ?>
        <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50 <?= $index % 2 === 0 ? '' : 'bg-slate-50/40' ?>">
          <td class="px-5 py-4">
            <div class="flex items-center gap-3">
              <span class="h-12 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-200">
                <img src="<?= htmlspecialchars( $postImage, ENT_QUOTES, 'UTF-8' ) ?>" alt="" class="h-full w-full object-cover">
              </span>
              <div class="min-w-0">
                <a href="blog-edit.php?id=<?= (int) $post[ 'id' ] ?>" class="block truncate text-sm font-semibold text-slate-900 hover:text-brandRed transition-colors"><?= htmlspecialchars( (string) $post[ 'title' ] ) ?></a>
                <p class="mt-0.5 truncate font-mono text-[10px] text-slate-400">/<?= htmlspecialchars( (string) $post[ 'slug' ] ) ?></p>
              </div>
            </div>
          </td>
          <td class="px-5 py-4">
            <?php if ( $postTags ): ?>
            <div class="flex flex-wrap gap-1">
              <?php foreach ( array_slice( $postTags, 0, 2 ) as $postTag ): ?>
              <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500"><?= htmlspecialchars( $postTag ) ?></span>
              <?php endforeach; ?>
              <?php if ( count( $postTags ) > 2 ): ?>
              <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-400">+<?= count( $postTags ) - 2 ?></span>
              <?php endif; ?>
            </div>
            <?php else: ?>
            <span class="text-xs text-slate-300">—</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-4 text-slate-600"><?= htmlspecialchars( (string) ( $post[ 'author' ] ?: '—' ) ) ?></td>
          <td class="px-5 py-4 text-xs text-slate-500"><?= $post[ 'published_at' ] ? date( 'M d, Y', strtotime( (string) $post[ 'published_at' ] ) ) : '—' ?></td>
          <td class="px-5 py-4">
            <?php if ( $post[ 'is_published' ] ): ?>
            <span class="inline-flex items-center gap-1 rounded-full border border-green-200 bg-green-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-green-600"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Published</span>
            <?php else: ?>
            <span class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-600"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Draft</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-4 text-center">
            <form method="POST" class="inline">
              <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
              <input type="hidden" name="toggle_featured_id" value="<?= (int) $post[ 'id' ] ?>">
              <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition-colors <?= $post[ 'is_featured' ] ? 'bg-brandRed/10 text-brandRed hover:bg-brandRed/20' : 'text-slate-300 hover:bg-slate-100 hover:text-amber-500' ?>" title="<?= $post[ 'is_featured' ] ? 'Remove from featured' : 'Feature this post' ?>">
                <i class="fa-solid fa-star text-xs"></i>
              </button>
            </form>
          </td>
          <td class="px-5 py-4">
            <div class="flex items-center justify-end gap-1.5">
              <a href="../news/blog-detail.php?slug=<?= rawurlencode( (string) $post[ 'slug' ] ) ?>" target="_blank" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700" title="View post">
                <i class="fa-solid fa-eye text-xs"></i>
              </a>
              <a href="blog-edit.php?id=<?= (int) $post[ 'id' ] ?>" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-blue-50 hover:text-blue-500" title="Edit post">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
              </a>
              <form method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="toggle_publish_id" value="<?= (int) $post[ 'id' ] ?>">
                <button type="submit" class="rounded-lg p-1.5 transition-colors <?= $post[ 'is_published' ] ? 'text-slate-400 hover:bg-amber-50 hover:text-amber-600' : 'text-slate-400 hover:bg-green-50 hover:text-green-600' ?>" title="<?= $post[ 'is_published' ] ? 'Move to draft' : 'Publish post' ?>">
                  <i class="fa-solid <?= $post[ 'is_published' ] ? 'fa-eye-slash' : 'fa-eye' ?> text-xs"></i>
                </button>
              </form>
              <form method="POST" class="inline" onsubmit="return confirm('Delete this blog post permanently? This cannot be undone.');">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="delete_id" value="<?= (int) $post[ 'id' ] ?>">
                <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-500" title="Delete post">
                  <i class="fa-solid fa-trash text-xs"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ( !empty( $posts ) ): ?>
<p class="mt-4 text-right text-xs text-slate-400">Showing <?= count( $posts ) ?> post<?= count( $posts ) === 1 ? '' : 's' ?><?= ( $search !== '' || $status !== 'all' ) ? ' matching your filters' : '' ?>.</p>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
