<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Resource';
$errors = [];
$resource = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $resource = getById('resources', (int) $_GET['id']);
    if (!$resource) {
        setFlash('error', 'Resource not found.');
        redirect('resources.php');
    }
    $pageTitle = 'Edit: ' . $resource['title'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('resources.php');
    }

    $title = getPost('title');
    $resource_type = getPost('resource_type');
    $description = getPost('description');
    $file_path = getPost('file_path');
    $file_type = getPost('file_type');
    $year = getPost('year') ?: null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int) getPost('sort_order');

    if (empty($title)) $errors[] = 'Title is required.';
    $validTypes = ['annual_report', 'financial_report', 'audit_report'];
    if (!in_array($resource_type, $validTypes)) $errors[] = 'Invalid resource type.';

    if (empty($errors)) {
        global $pdo;
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE resources SET title=?, resource_type=?, description=?, file_path=?, file_type=?, year=?, is_active=?, sort_order=? WHERE id=?");
            $stmt->execute([$title, $resource_type, $description, $file_path, $file_type, $year, $is_active, $sort_order, $resource['id']]);
            setFlash('success', 'Resource updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO resources (title, resource_type, description, file_path, file_type, year, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $resource_type, $description, $file_path, $file_type, $year, $is_active, $sort_order]);
            setFlash('success', 'Resource created successfully.');
        }
        redirect('resources.php');
    }
}

$typeLabels = ['annual_report' => 'Annual Report', 'financial_report' => 'Financial Report', 'audit_report' => 'Audit Report'];
$typeIcons = ['annual_report' => 'fa-chart-line', 'financial_report' => 'fa-coins', 'audit_report' => 'fa-magnifying-glass-chart'];
$typeDescs = ['annual_report' => 'Yearly impact and program overview reports', 'financial_report' => 'Income, expenditure, and fund utilization statements', 'audit_report' => 'Independent external audit reports by certified firms'];
$currentType = $resource['resource_type'] ?? 'annual_report';

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?= $isEdit ? 'Edit Resource' : 'Add New Resource' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update report details and file.' : 'Upload a new annual, financial, or audit report.' ?></p>
  </div>
  <a href="resources.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Resources
  </a>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
  <div class="flex items-center gap-2 mb-1"><i class="fa-solid fa-circle-exclamation"></i><span class="font-semibold text-sm">Please fix the following errors:</span></div>
  <ul class="list-disc list-inside text-sm mt-1"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <!-- Report Type Selection -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Report Type</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <?php foreach ($typeLabels as $type => $label): ?>
      <label class="group relative cursor-pointer">
        <input type="radio" name="resource_type" value="<?= $type ?>" <?= $currentType === $type ? 'checked' : '' ?> class="peer sr-only" onchange="updateTypeHint(this.value)">
        <div class="border-2 border-slate-200 rounded-xl p-4 text-center transition-all peer-checked:border-brandRed peer-checked:bg-brandRed/5 hover:border-slate-300 hover:shadow-sm">
          <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center mx-auto mb-2 peer-checked:bg-brandRed/10 transition-colors">
            <i class="fa-solid <?= $typeIcons[$type] ?> text-slate-400 peer-checked:text-brandRed transition-colors text-lg"></i>
          </div>
          <div class="font-semibold text-slate-700 text-sm"><?= $label ?></div>
          <div class="text-[11px] text-slate-400 mt-1"><?= $typeDescs[$type] ?></div>
        </div>
      </label>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Report Details -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Report Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($resource['title'] ?? '') ?>" required placeholder="e.g. Annual Report 2024" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Year *</label>
        <input type="number" name="year" value="<?= htmlspecialchars($resource['year'] ?? date('Y')) ?>" min="2000" max="2099" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">File Format</label>
        <select name="file_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <option value="pdf" <?= ($resource['file_type'] ?? 'pdf') === 'pdf' ? 'selected' : '' ?>>PDF</option>
          <option value="zip" <?= ($resource['file_type'] ?? '') === 'zip' ? 'selected' : '' ?>>ZIP</option>
          <option value="doc" <?= ($resource['file_type'] ?? '') === 'doc' ? 'selected' : '' ?>>DOC</option>
          <option value="xls" <?= ($resource['file_type'] ?? '') === 'xls' ? 'selected' : '' ?>>XLS</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="3" placeholder="Brief description of this report..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($resource['description'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- File Upload -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">File</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <?php uploadWidget('file_path', 'Report File (PDF, DOC, XLS)', $resource['file_path'] ?? '', 'file', 'file_path_input'); ?>
      </div>
    </div>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($resource['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        <p class="text-[11px] text-slate-400 mt-1">Lower numbers appear first</p>
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($resource['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Published (visible on website)</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="resources.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Report' : 'Create Report' ?>
    </button>
  </div>
</form>

<script>
function updateTypeHint(type) {
  // Visual feedback is handled by peer-checked CSS
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
