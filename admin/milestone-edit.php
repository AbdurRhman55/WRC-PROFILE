<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Milestone';
$errors = [];
$milestone = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $milestone = getById('history_milestones', (int) $_GET['id']);
    if (!$milestone) {
        setFlash('error', 'Milestone not found.');
        redirect('milestones.php');
    }
    $pageTitle = 'Edit Milestone: ' . $milestone['title'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('milestones.php');
    }

    $year = getPostInt('year');
    $title = getPost('title');
    $description = getPost('description');
    $is_current = isset($_POST['is_current']) ? 1 : 0;
    $sort_order = getPostInt('sort_order');

    if (empty($title)) $errors[] = 'Title is required.';
    if ($year < 1900 || $year > 2100) $errors[] = 'Year must be between 1900 and 2100.';

    if (empty($errors)) {
        global $pdo;

        if ($is_current) {
            $stmt = $pdo->prepare("UPDATE history_milestones SET is_current = 0");
            $stmt->execute();
        }

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE history_milestones SET year=?, title=?, description=?, is_current=?, sort_order=? WHERE id=?");
            $stmt->execute([$year, $title, $description, $is_current, $sort_order, $milestone['id']]);
            setFlash('success', 'Milestone updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO history_milestones (year, title, description, is_current, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$year, $title, $description, $is_current, $sort_order]);
            setFlash('success', 'Milestone created successfully.');
        }
        redirect('milestones.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Milestone' : 'Add New Milestone' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update milestone details.' : 'Create a new history milestone.' ?></p>
  </div>
  <a href="milestones.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Milestones
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
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Basic Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Year *</label>
        <input type="number" name="year" value="<?= (int) ($milestone['year'] ?? date('Y')) ?>" required min="1900" max="2100" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($milestone['title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="4" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($milestone['description'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($milestone['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_current" value="1" <?= ($milestone['is_current'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Mark as Current Milestone</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="milestones.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Milestone' : 'Create Milestone' ?>
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
