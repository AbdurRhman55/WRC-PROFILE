<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Campaign';
$errors = [];
$campaign = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $campaign = getById('campaigns', (int) $_GET['id']);
    if (!$campaign) {
        setFlash('error', 'Campaign not found.');
        redirect('campaigns.php');
    }
    $pageTitle = 'Edit Campaign: ' . $campaign['title'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('campaigns.php');
    }

    $title = getPost('title');
    $description = getPost('description');
    $goal_amount = getPostFloat('goal_amount');
    $raised_amount = getPostFloat('raised_amount');
    $currency = getPost('currency') ?: 'PKR';
    $image_path = getPost('image_path');
    $status = getPost('status');
    $sort_order = getPostInt('sort_order');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title)) $errors[] = 'Title is required.';
    if (!in_array($status, ['active', 'completed', 'paused'])) $errors[] = 'Invalid status.';
    if ($goal_amount < 0) $errors[] = 'Goal amount cannot be negative.';
    if ($raised_amount < 0) $errors[] = 'Raised amount cannot be negative.';

    if (empty($errors)) {
        global $pdo;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE campaigns SET title=?, description=?, goal_amount=?, raised_amount=?, currency=?, image_path=?, status=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$title, $description, $goal_amount, $raised_amount, $currency, $image_path, $status, $is_active, $sort_order, $campaign['id']]);
            setFlash('success', 'Campaign updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO campaigns (title, description, goal_amount, raised_amount, currency, image_path, status, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $goal_amount, $raised_amount, $currency, $image_path, $status, $is_active, $sort_order]);
            setFlash('success', 'Campaign created successfully.');
        }
        redirect('campaigns.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Campaign' : 'Add New Campaign' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update campaign details.' : 'Create a new fundraising campaign.' ?></p>
  </div>
  <a href="campaigns.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Campaigns
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
        <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($campaign['title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status *</label>
        <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <option value="active" <?= ($campaign['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="completed" <?= ($campaign['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
          <option value="paused" <?= ($campaign['status'] ?? '') === 'paused' ? 'selected' : '' ?>>Paused</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="5" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($campaign['description'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Funding -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Funding</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Goal Amount</label>
        <input type="number" name="goal_amount" value="<?= htmlspecialchars($campaign['goal_amount'] ?? '0') ?>" step="0.01" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Raised Amount</label>
        <input type="number" name="raised_amount" value="<?= htmlspecialchars($campaign['raised_amount'] ?? '0') ?>" step="0.01" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
        <input type="text" name="currency" value="<?= htmlspecialchars($campaign['currency'] ?? 'PKR') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <!-- Image -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Image</h2>
    <?php uploadWidget('image_path', 'Campaign Image', $campaign['image_path'] ?? '', 'image', 'campaign_img'); ?>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($campaign['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($campaign['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="campaigns.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Campaign' : 'Create Campaign' ?>
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
