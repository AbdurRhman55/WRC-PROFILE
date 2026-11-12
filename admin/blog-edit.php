<?php
/**
 * WRC Admin - create/edit a blog post.
 *
 * The editor is deliberately focused on writing: a wide story canvas, a
 * publish sidebar, topic chips and a featured-image picker. Posts are stored
 * in news_articles with category = 'blog'.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'New Blog Post';
$errors    = [];
$post      = null;
$isEdit    = isset( $_GET[ 'id' ] ) && (int) $_GET[ 'id' ] > 0;
$postId    = $isEdit ? (int) $_GET[ 'id' ] : 0;

if ( $isEdit ) {
    $post = getById( 'news_articles', $postId );
    if ( !$post ) {
        setFlash( 'error', 'Blog post not found.' );
        redirect( 'blog.php' );
    }
    if ( ( $post[ 'category' ] ?? '' ) !== 'blog' ) {
        setFlash( 'error', 'That article is not a blog post. Open it from News & Media instead.' );
        redirect( 'blog.php' );
    }
    $pageTitle = 'Edit Post: ' . $post[ 'title' ];
}

$form = [
    'title'          => (string) ( $post[ 'title' ] ?? '' ),
    'slug'           => (string) ( $post[ 'slug' ] ?? '' ),
    'author'         => (string) ( $post[ 'author' ] ?? '' ),
    'published_date' => (string) ( $post[ 'published_at' ] ?? date( 'Y-m-d' ) ),
    'hero_image'     => (string) ( $post[ 'featured_image' ] ?? '' ),
    'excerpt'        => (string) ( $post[ 'excerpt' ] ?? '' ),
    'content'        => (string) ( $post[ 'content' ] ?? '' ),
    'tags'           => (string) ( $post[ 'tags' ] ?? '' ),
    'is_published'   => (int) ( $post[ 'is_published' ] ?? 1 ),
    'is_featured'    => (int) ( $post[ 'is_featured' ] ?? 0 ),
];

if ( isPost() ) {
    if ( !isset( $_POST[ 'csrf_token' ] ) || !verifyCSRF( $_POST[ 'csrf_token' ] ) ) {
        setFlash( 'error', 'Invalid security token.' );
        redirect( 'blog.php' );
    }

    $form[ 'title' ]          = getPost( 'title' );
    $form[ 'slug' ]           = getPost( 'slug' );
    $form[ 'author' ]         = getPost( 'author' );
    $form[ 'published_date' ] = getPost( 'published_date' ) ?: date( 'Y-m-d' );
    $form[ 'hero_image' ]     = getPost( 'hero_image' );
    $form[ 'excerpt' ]        = getPost( 'excerpt' );
    $form[ 'content' ]        = getPost( 'content' );
    $form[ 'tags' ]           = getPost( 'tags' );
    $form[ 'is_published' ]   = isset( $_POST[ 'is_published' ] ) ? 1 : 0;
    $form[ 'is_featured' ]    = isset( $_POST[ 'is_featured' ] ) ? 1 : 0;

    if ( $form[ 'title' ] === '' ) {
        $errors[] = 'A title is required.';
    }

    $form[ 'slug' ] = createSlug( $form[ 'slug' ] !== '' ? $form[ 'slug' ] : $form[ 'title' ] );
    if ( $form[ 'slug' ] === '' ) {
        $errors[] = 'A URL slug is required.';
    } elseif ( !checkSlugUnique( 'news_articles', $form[ 'slug' ], $postId ) ) {
        $errors[] = 'Another article already uses that slug.';
    }

    $cleanTags = [];
    foreach ( explode( ',', $form[ 'tags' ] ) as $tag ) {
        $tag = trim( preg_replace( '/\s+/', ' ', $tag ) );
        if ( $tag === '' ) {
            continue;
        }
        $tag = mb_substr( $tag, 0, 40 );
        $key = strtolower( $tag );
        if ( !isset( $cleanTags[ $key ] ) ) {
            $cleanTags[ $key ] = $tag;
        }
        if ( count( $cleanTags ) >= 8 ) {
            break;
        }
    }
    $form[ 'tags' ] = implode( ',', array_values( $cleanTags ) );

    if ( empty( $errors ) ) {
        global $pdo;

        if ( $form[ 'is_featured' ] ) {
            $pdo->prepare( "UPDATE news_articles SET is_featured = 0 WHERE category = 'blog' AND id <> ?" )
                ->execute( [ $postId ] );
        }

        if ( $isEdit ) {
            $stmt = $pdo->prepare(
                "UPDATE news_articles
                    SET slug = ?, title = ?, category = 'blog', author = ?, published_at = ?,
                        featured_image = ?, excerpt = ?, content = ?, tags = ?,
                        is_published = ?, is_featured = ?
                  WHERE id = ?"
            );
            $stmt->execute( [
                $form[ 'slug' ],
                $form[ 'title' ],
                $form[ 'author' ],
                $form[ 'published_date' ] ?: null,
                $form[ 'hero_image' ],
                $form[ 'excerpt' ],
                $form[ 'content' ],
                $form[ 'tags' ],
                $form[ 'is_published' ],
                $form[ 'is_featured' ],
                $postId,
            ] );
            setFlash( 'success', 'Blog post updated successfully.' );
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO news_articles
                    (slug, title, category, author, published_at, featured_image, excerpt, content, tags, is_published, is_featured)
                 VALUES (?, ?, 'blog', ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute( [
                $form[ 'slug' ],
                $form[ 'title' ],
                $form[ 'author' ],
                $form[ 'published_date' ] ?: null,
                $form[ 'hero_image' ],
                $form[ 'excerpt' ],
                $form[ 'content' ],
                $form[ 'tags' ],
                $form[ 'is_published' ],
                $form[ 'is_featured' ],
            ] );
            setFlash( 'success', 'Blog post created successfully.' );
        }

        redirect( 'blog.php' );
    }
}

$existingTags = getBlogTags();

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Blog Post' : 'Write a Blog Post' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update the story, topics and publishing settings.' : 'Publish a new story to the WRC blog.' ?></p>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <?php if ( $isEdit ): ?>
    <a href="../news/blog-detail.php?slug=<?= rawurlencode( (string) ( $post[ 'slug' ] ?? '' ) ) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
      <i class="fa-solid fa-eye text-xs text-slate-400"></i> View post
    </a>
    <?php endif; ?>
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
      <!-- Story details -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900">Story details</h2>
        <div class="space-y-4">
          <div>
            <label for="titleInput" class="mb-1 block text-sm font-medium text-slate-700">Title *</label>
            <input id="titleInput" type="text" name="title" value="<?= htmlspecialchars( $form[ 'title' ], ENT_QUOTES, 'UTF-8' ) ?>" required placeholder="A clear, specific headline" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-900 focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
          </div>

          <div>
            <label for="slugInput" class="mb-1 block text-sm font-medium text-slate-700">URL slug</label>
            <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 focus-within:border-brandRed focus-within:ring-2 focus-within:ring-brandRed/20">
              <span class="shrink-0 select-none font-mono text-[11px] text-slate-400">slug:</span>
              <input id="slugInput" type="text" name="slug" value="<?= htmlspecialchars( $form[ 'slug' ], ENT_QUOTES, 'UTF-8' ) ?>" placeholder="auto-generated-from-title" class="w-full bg-transparent py-2.5 font-mono text-xs text-slate-700 focus:outline-none">
            </div>
            <p class="mt-1 text-xs text-slate-400">Leave blank to generate it from the title. Use lowercase words separated by hyphens.</p>
          </div>

          <div>
            <label for="excerptInput" class="mb-1 block text-sm font-medium text-slate-700">Short summary</label>
            <textarea id="excerptInput" name="excerpt" rows="3" maxlength="600" placeholder="One or two sentences for the blog cards and search results." class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm leading-relaxed focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20"><?= htmlspecialchars( $form[ 'excerpt' ], ENT_QUOTES, 'UTF-8' ) ?></textarea>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
          <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Story content</h2>
          <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-400">
            <span><span id="wordCountOut">0</span> words</span>
            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
            <span>~<span id="readTimeOut">1</span> min read</span>
          </div>
        </div>

        <div class="blog-toolbar mb-3 flex flex-wrap items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1.5" role="toolbar" aria-label="Formatting">
          <button type="button" data-tag="h2" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Heading 2">H2</button>
          <button type="button" data-tag="h3" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Heading 3">H3</button>
          <span class="mx-1 h-5 w-px bg-slate-200"></span>
          <button type="button" data-tag="strong" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Bold"><i class="fa-solid fa-bold"></i></button>
          <button type="button" data-tag="em" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Italic"><i class="fa-solid fa-italic"></i></button>
          <button type="button" data-tag="a" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Link"><i class="fa-solid fa-link"></i></button>
          <span class="mx-1 h-5 w-px bg-slate-200"></span>
          <button type="button" data-tag="p" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Paragraph"><i class="fa-solid fa-paragraph"></i></button>
          <button type="button" data-tag="ul" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Bulleted list"><i class="fa-solid fa-list-ul"></i></button>
          <button type="button" data-tag="ol" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
          <button type="button" data-tag="blockquote" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Pull quote"><i class="fa-solid fa-quote-left"></i></button>
          <button type="button" data-tag="img" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Insert image"><i class="fa-solid fa-image"></i></button>
        </div>

        <label for="contentInput" class="sr-only">Story content</label>
        <textarea id="contentInput" name="content" rows="22" placeholder="Write the story here. HTML is supported — use the toolbar for headings, lists, quotes and images." class="w-full rounded-xl border border-slate-200 px-4 py-3 font-mono text-sm leading-relaxed text-slate-700 focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20"><?= htmlspecialchars( $form[ 'content' ], ENT_QUOTES, 'UTF-8' ) ?></textarea>
        <p class="mt-2 text-xs text-slate-400">HTML is supported. Keep paragraphs short for readability; images should use a path such as <code class="rounded bg-slate-100 px-1 py-0.5">assets/images/projects/example.jpg</code>.</p>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900">Publishing</h2>
        <div class="space-y-4">
          <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition-colors">
            <input type="checkbox" name="is_published" value="1" <?= $form[ 'is_published' ] ? 'checked' : '' ?> class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
            <span>
              <span class="block text-sm font-semibold text-slate-800">Published</span>
              <span class="mt-0.5 block text-xs text-slate-400">Visible on the public blog.</span>
            </span>
          </label>

          <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition-colors">
            <input type="checkbox" name="is_featured" value="1" <?= $form[ 'is_featured' ] ? 'checked' : '' ?> class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
            <span>
              <span class="block text-sm font-semibold text-slate-800">Featured story</span>
              <span class="mt-0.5 block text-xs text-slate-400">Pins the post to the top of the blog.</span>
            </span>
          </label>

          <div>
            <label for="publishedDate" class="mb-1 block text-sm font-medium text-slate-700">Publish date</label>
            <input id="publishedDate" type="date" name="published_date" value="<?= htmlspecialchars( $form[ 'published_date' ], ENT_QUOTES, 'UTF-8' ) ?>" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
          </div>

          <div>
            <label for="authorInput" class="mb-1 block text-sm font-medium text-slate-700">Author / team</label>
            <input id="authorInput" type="text" name="author" value="<?= htmlspecialchars( $form[ 'author' ], ENT_QUOTES, 'UTF-8' ) ?>" placeholder="e.g. WRC MEAL Unit" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
          </div>

          <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brandRed px-5 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-brandRed-hover">
            <i class="fa-solid fa-check text-xs"></i> <?= $isEdit ? 'Update Post' : 'Create Post' ?>
          </button>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <?php uploadWidget( 'hero_image', 'Featured image', $form[ 'hero_image' ], 'image', 'blogHeroImage' ); ?>
        <p class="mt-2 text-xs text-slate-400">Shown on the blog card and as the story hero. A wide landscape image works best.</p>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <label for="tagsInput" class="mb-1 block text-sm font-medium text-slate-700">Topics</label>
        <input id="tagsInput" type="text" name="tags" value="<?= htmlspecialchars( $form[ 'tags' ], ENT_QUOTES, 'UTF-8' ) ?>" placeholder="WASH, Community, Sustainability" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
        <p class="mt-1 text-xs text-slate-400">Comma separated, up to 8 topics. The first topic is shown on the card.</p>

        <?php if ( !empty( $existingTags ) ): ?>
        <p class="mb-2 mt-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Existing topics</p>
        <div class="flex flex-wrap gap-1.5">
          <?php foreach ( $existingTags as $tag => $count ): ?>
          <button type="button" class="tag-suggestion rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 transition-colors hover:bg-brandRed hover:text-white" data-tag="<?= htmlspecialchars( $tag, ENT_QUOTES, 'UTF-8' ) ?>"><?= htmlspecialchars( $tag ) ?> <span class="text-[9px] opacity-60"><?= (int) $count ?></span></button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Writing tip</p>
        <p class="mt-2 text-xs leading-relaxed text-slate-500">Lead with the reader&rsquo;s question, use one idea per paragraph, and close with what changed or what comes next.</p>
      </div>
    </div>
  </div>

  <div class="flex flex-wrap items-center justify-end gap-3">
    <a href="blog.php" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">Cancel</a>
    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brandRed px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brandRed-hover">
      <i class="fa-solid fa-check text-xs"></i> <?= $isEdit ? 'Update Post' : 'Create Post' ?>
    </button>
  </div>
</form>

<script>
(function () {
  var titleInput = document.getElementById('titleInput');
  var slugInput = document.getElementById('slugInput');
  var contentInput = document.getElementById('contentInput');
  var wordCountOut = document.getElementById('wordCountOut');
  var readTimeOut = document.getElementById('readTimeOut');
  var tagsInput = document.getElementById('tagsInput');

  if (titleInput && slugInput) {
    titleInput.addEventListener('input', function () {
      if (!slugInput.dataset.manual && slugInput.value.trim() === '') {
        slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
      }
    });
    slugInput.addEventListener('input', function () {
      this.dataset.manual = '1';
    });
  }

  function refreshStats() {
    if (!contentInput) return;
    var text = contentInput.value.replace(/<[^>]*>/g, ' ').replace(/&[a-z#0-9]+;/gi, ' ');
    var words = text.trim() === '' ? [] : text.trim().split(/\s+/);
    if (wordCountOut) wordCountOut.textContent = words.length;
    if (readTimeOut) readTimeOut.textContent = Math.max(1, Math.ceil(words.length / 220));
  }

  if (contentInput) {
    contentInput.addEventListener('input', refreshStats);
    refreshStats();
  }

  function insertAround(before, after, placeholder) {
    if (!contentInput) return;
    var start = contentInput.selectionStart;
    var end = contentInput.selectionEnd;
    var selected = contentInput.value.slice(start, end) || placeholder || '';
    contentInput.setRangeText(before + selected + after, start, end, 'end');
    contentInput.focus();
    refreshStats();
  }

  function insertBlock(html) {
    if (!contentInput) return;
    var start = contentInput.selectionStart;
    contentInput.setRangeText(html, start, contentInput.selectionEnd, 'end');
    contentInput.focus();
    refreshStats();
  }

  document.querySelectorAll('.blog-toolbar [data-tag]').forEach(function (button) {
    button.addEventListener('click', function () {
      var tag = button.getAttribute('data-tag');
      if (tag === 'a') {
        var url = window.prompt('Link URL:', 'https://');
        if (url) insertAround('<a href="' + url.replace(/"/g, '&quot;') + '">', '</a>', 'link text');
      } else if (tag === 'img') {
        var src = window.prompt('Image path or URL:', 'assets/images/projects/');
        if (src) insertBlock('\n<figure class="ab-figure"><img src="' + src.replace(/"/g, '&quot;') + '" alt="" loading="lazy"></figure>\n');
      } else if (tag === 'ul' || tag === 'ol') {
        insertBlock('\n<' + tag + '>\n  <li>First point</li>\n  <li>Second point</li>\n</' + tag + '>\n');
      } else if (tag === 'blockquote') {
        insertAround('<blockquote>', '</blockquote>', 'A memorable line from the story');
      } else {
        insertAround('<' + tag + '>', '</' + tag + '>', tag === 'h2' || tag === 'h3' ? 'Section heading' : '');
      }
    });
  });

  document.querySelectorAll('.tag-suggestion').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!tagsInput) return;
      var tag = button.getAttribute('data-tag');
      var current = tagsInput.value.split(',').map(function (item) { return item.trim(); }).filter(Boolean);
      var exists = current.some(function (item) { return item.toLowerCase() === tag.toLowerCase(); });
      if (!exists && current.length < 8) {
        current.push(tag);
        tagsInput.value = current.join(', ');
      }
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
