<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Hero Slider';
$errors = [];
$slider = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $slider = getById('hero_sliders', (int) $_GET['id']);
    if (!$slider) {
        setFlash('error', 'Hero slider not found.');
        redirect('hero-sliders.php');
    }
    $pageTitle = 'Edit Hero Slider: ' . $slider['title'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('hero-sliders.php');
    }

    $title = getPost('title');
    $subtitle = getPost('subtitle');
    $image_path = getPost('image_path');
    $cta_text = getPost('cta_text');
    $cta_link = getPost('cta_link');
    $sort_order = getPostInt('sort_order');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title)) $errors[] = 'Title is required.';

    if (empty($errors)) {
        global $pdo;
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE hero_sliders SET title=?, subtitle=?, image_path=?, cta_text=?, cta_link=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$title, $subtitle, $image_path, $cta_text, $cta_link, $sort_order, $is_active, $slider['id']]);
            setFlash('success', 'Hero slider updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO hero_sliders (title, subtitle, image_path, cta_text, cta_link, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $image_path, $cta_text, $cta_link, $sort_order, $is_active]);
            setFlash('success', 'Hero slider created successfully.');
        }
        redirect('hero-sliders.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Hero Slider' : 'Add New Hero Slider' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update slider content and image.' : 'Create a new hero slider for the homepage.' ?></p>
  </div>
  <a href="hero-sliders.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Sliders
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

  <!-- Content -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Slider Content</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($slider['title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Subtitle</label>
        <textarea name="subtitle" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed leading-relaxed"><?= htmlspecialchars($slider['subtitle'] ?? '') ?></textarea>
      </div>
      <div class="md:col-span-2">
        <?php uploadWidget('image_path', 'Slider Image', $slider['image_path'] ?? '', 'image', 'slider_img'); ?>
      </div>
    </div>
  </div>

  <!-- Call to Action -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Call to Action</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Button Text</label>
        <input type="text" name="cta_text" value="<?= htmlspecialchars($slider['cta_text'] ?? '') ?>" placeholder="e.g. Learn More" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Button Link (URL)</label>
        <input type="url" name="cta_link" value="<?= htmlspecialchars($slider['cta_link'] ?? '') ?>" placeholder="https://..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($slider['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($slider['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="hero-sliders.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Slider' : 'Create Slider' ?>
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
