<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Article';
$errors = [];
$article = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $article = getById('news_articles', (int) $_GET['id']);
    if (!$article) {
        setFlash('error', 'Article not found.');
        redirect('news.php');
    }
    $pageTitle = 'Edit Article: ' . $article['title'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('news.php');
    }

    $title = getPost('title');
    $slug = getPost('slug');
    $category = getPost('category');
    $author = getPost('author');
    $published_date = getPost('published_date') ?: null;
    $hero_image = getPost('hero_image');
    $excerpt = getPost('excerpt');
    $content = getPost('content');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $sort_order = (int) getPost('sort_order');

    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($slug)) $slug = createSlug($title);
    $validCategories = ['press_release', 'report', 'story', 'blog', 'event'];
    if (!in_array($category, $validCategories)) $errors[] = 'Invalid category.';

    if (empty($errors)) {
        global $pdo;
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE news_articles SET slug=?, title=?, category=?, author=?, published_at=?, featured_image=?, excerpt=?, content=?, is_published=?, sort_order=? WHERE id=?");
            $stmt->execute([$slug, $title, $category, $author, $published_date, $hero_image, $excerpt, $content, $is_published, $sort_order, $article['id']]);
            setFlash('success', 'Article updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO news_articles (slug, title, category, author, published_at, featured_image, excerpt, content, is_published, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $title, $category, $author, $published_date, $hero_image, $excerpt, $content, $is_published, $sort_order]);
            setFlash('success', 'Article created successfully.');
        }
        redirect('news.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Article' : 'Add New Article' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update article details.' : 'Write a new news article.' ?></p>
  </div>
  <a href="news.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to News
  </a>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
  <div class="flex items-center gap-2 mb-1"><i class="fa-solid fa-circle-exclamation"></i><span class="font-semibold text-sm">Please fix the following errors:</span></div>
  <ul class="list-disc list-inside text-sm mt-1">
    <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <!-- Basic Info -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Article Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($article['title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" id="titleInput">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($article['slug'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed font-mono" id="slugInput">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Category *</label>
        <select name="category" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <?php
          $cats = ['press_release' => 'Press Release', 'report' => 'Report', 'story' => 'Story', 'blog' => 'Blog', 'event' => 'Event'];
          foreach ($cats as $val => $label):
          ?>
          <option value="<?= $val ?>" <?= ($article['category'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Author</label>
        <input type="text" name="author" value="<?= htmlspecialchars($article['author'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Published Date</label>
        <input type="date" name="published_date" value="<?= htmlspecialchars($article['published_at'] ?? date('Y-m-d')) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <?php uploadWidget('hero_image', 'Featured Image', $article['featured_image'] ?? '', 'image', 'news_hero_img'); ?>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Excerpt</label>
        <textarea name="excerpt" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Content</h2>
    <div>
      <textarea name="content" rows="12" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed leading-relaxed"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
      <p class="text-xs text-slate-400 mt-1">HTML content is supported.</p>
    </div>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($article['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_published" value="1" <?= ($article['is_published'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Published</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="news.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Article' : 'Create Article' ?>
    </button>
  </div>
</form>

<script>
document.getElementById('titleInput')?.addEventListener('input', function() {
  const slugInput = document.getElementById('slugInput');
  if (slugInput && !slugInput.dataset.manual) {
    slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  }
});
document.getElementById('slugInput')?.addEventListener('input', function() {
  this.dataset.manual = '1';
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
