<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Section';
$errors = [];
$section = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $section = getById('homepage_sections', (int) $_GET['id']);
    if (!$section) {
        setFlash('error', 'Section not found.');
        redirect('sections.php');
    }
    $pageTitle = 'Edit Section: ' . ($section['section_title'] ?? $section['section_key']);
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('sections.php');
    }

    $section_key = getPost('section_key');
    $section_title = getPost('section_title');
    $section_subtitle = getPost('section_subtitle');
    $content = getPost('content');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int) getPost('sort_order');

    if (empty($section_key)) $errors[] = 'Section key is required.';
    if (!$isEdit && empty($section_title)) $errors[] = 'Section title is required for new sections.';

    if (!empty($content)) {
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = 'Content must be valid JSON.';
        }
    }

    if (empty($errors)) {
        global $pdo;
        $content_json = !empty($content) ? $content : null;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE homepage_sections SET section_title=?, section_subtitle=?, content=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$section_title, $section_subtitle, $content_json, $sort_order, $is_active, $section['id']]);
            setFlash('success', 'Section updated successfully.');
        } else {
            $existing = getRow('homepage_sections', ['section_key' => $section_key]);
            if ($existing) {
                $errors[] = 'A section with this key already exists.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO homepage_sections (section_key, section_title, section_subtitle, content, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$section_key, $section_title, $section_subtitle, $content_json, $sort_order, $is_active]);
                setFlash('success', 'Section created successfully.');
            }
        }

        if (empty($errors)) {
            redirect('sections.php');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Section' : 'Add New Section' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update section content and settings.' : 'Create a new content section.' ?></p>
  </div>
  <a href="sections.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Sections
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

  <!-- Section Key -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Section Identity</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Section Key *</label>
        <?php if ($isEdit): ?>
        <input type="text" name="section_key" value="<?= htmlspecialchars($section['section_key']) ?>" readonly class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50 text-slate-500 cursor-not-allowed font-mono">
        <p class="text-xs text-slate-400 mt-1">Section key cannot be changed after creation.</p>
        <?php else: ?>
        <input type="text" name="section_key" value="<?= htmlspecialchars($_POST['section_key'] ?? '') ?>" required placeholder="e.g. home_hero, about_intro" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed font-mono">
        <p class="text-xs text-slate-400 mt-1">Use lowercase with underscores. This is a unique identifier.</p>
        <?php endif; ?>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($section['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Section Content</h2>
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Section Title</label>
        <input type="text" name="section_title" value="<?= htmlspecialchars($section['section_title'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Section Subtitle</label>
        <input type="text" name="section_subtitle" value="<?= htmlspecialchars($section['section_subtitle'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Content (JSON)</label>
        <textarea name="content" rows="12" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed leading-relaxed" placeholder='{"key":"value"}'><?= htmlspecialchars($section['content'] ?? '') ?></textarea>
        <p class="text-xs text-slate-400 mt-1">Enter valid JSON. This field stores section-specific configuration and content data.</p>
      </div>
    </div>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="flex items-end">
      <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" <?= ($section['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
        <span class="text-sm font-medium text-slate-700">Active</span>
      </label>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="sections.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Section' : 'Create Section' ?>
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
