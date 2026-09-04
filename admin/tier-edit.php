<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Donation Tier';
$errors = [];
$tier = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $tier = getById('donation_tiers', (int) $_GET['id']);
    if (!$tier) {
        setFlash('error', 'Donation tier not found.');
        redirect('tiers.php');
    }
    $pageTitle = 'Edit Tier: ' . $tier['tier_name'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('tiers.php');
    }

    $tier_name = getPost('tier_name');
    $amount = getPostFloat('amount');
    $currency = getPost('currency') ?: 'PKR';
    $description = getPost('description');
    $icon_class = getPost('icon_class');
    $impact_description = getPost('impact_description');
    $sort_order = getPostInt('sort_order');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($tier_name)) $errors[] = 'Tier name is required.';
    if ($amount <= 0) $errors[] = 'Amount must be greater than zero.';

    if (empty($errors)) {
        global $pdo;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE donation_tiers SET tier_name=?, amount=?, currency=?, description=?, icon_class=?, impact_description=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$tier_name, $amount, $currency, $description, $icon_class, $impact_description, $is_active, $sort_order, $tier['id']]);
            setFlash('success', 'Donation tier updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO donation_tiers (tier_name, amount, currency, description, icon_class, impact_description, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tier_name, $amount, $currency, $description, $icon_class, $impact_description, $is_active, $sort_order]);
            setFlash('success', 'Donation tier created successfully.');
        }
        redirect('tiers.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Donation Tier' : 'Add New Donation Tier' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update tier details.' : 'Create a new donation tier level.' ?></p>
  </div>
  <a href="tiers.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Tiers
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
        <label class="block text-sm font-medium text-slate-700 mb-1">Tier Name *</label>
        <input type="text" name="tier_name" value="<?= htmlspecialchars($tier['tier_name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Amount *</label>
        <input type="number" name="amount" value="<?= htmlspecialchars($tier['amount'] ?? '') ?>" step="0.01" min="0.01" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
        <input type="text" name="currency" value="<?= htmlspecialchars($tier['currency'] ?? 'PKR') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Icon Class</label>
        <input type="text" name="icon_class" value="<?= htmlspecialchars($tier['icon_class'] ?? '') ?>" placeholder="e.g. fa-solid fa-heart" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($tier['description'] ?? '') ?></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Impact Description</label>
        <textarea name="impact_description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($tier['impact_description'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($tier['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($tier['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="tiers.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Tier' : 'Create Tier' ?>
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
