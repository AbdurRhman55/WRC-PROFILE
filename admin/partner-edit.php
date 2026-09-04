<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Partner';
$errors = [];
$partner = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $partner = getById('partners', (int) $_GET['id']);
    if (!$partner) {
        setFlash('error', 'Partner not found.');
        redirect('partners.php');
    }
    $pageTitle = 'Edit Partner: ' . $partner['name'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('partners.php');
    }

    $name = getPost('name');
    $logo_path = getPost('logo_path');
    $website_url = getPost('website_url');
    $partner_type = getPost('partner_type');
    $sort_order = getPostInt('sort_order');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) $errors[] = 'Name is required.';
    if (!in_array($partner_type, ['funding', 'government'])) $errors[] = 'Invalid partner type.';
    if (!empty($website_url) && !filter_var($website_url, FILTER_VALIDATE_URL)) $errors[] = 'Invalid website URL.';

    if (empty($errors)) {
        global $pdo;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE partners SET name=?, logo_path=?, website_url=?, partner_type=?, is_active=?, sort_order=? WHERE id=?");
            $stmt->execute([$name, $logo_path, $website_url, $partner_type, $is_active, $sort_order, $partner['id']]);
            setFlash('success', 'Partner updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO partners (name, logo_path, website_url, partner_type, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $logo_path, $website_url, $partner_type, $is_active, $sort_order]);
            setFlash('success', 'Partner created successfully.');
        }
        redirect('partners.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Partner' : 'Add New Partner' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update partner details.' : 'Add a new organizational partner.' ?></p>
  </div>
  <a href="partners.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Partners
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
        <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
        <input type="text" name="name" value="<?= htmlspecialchars($partner['name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Partner Type *</label>
        <select name="partner_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <option value="funding" <?= ($partner['partner_type'] ?? '') === 'funding' ? 'selected' : '' ?>>Funding Partners</option>
          <option value="government" <?= ($partner['partner_type'] ?? '') === 'government' ? 'selected' : '' ?>>Government & Implementation Partners</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Website URL</label>
        <input type="url" name="website_url" value="<?= htmlspecialchars($partner['website_url'] ?? '') ?>" placeholder="https://example.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <!-- Logo -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Logo</h2>
    <?php uploadWidget('logo_path', 'Partner Logo', $partner['logo_path'] ?? '', 'image', 'partner_logo'); ?>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($partner['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($partner['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="partners.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Partner' : 'Create Partner' ?>
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
