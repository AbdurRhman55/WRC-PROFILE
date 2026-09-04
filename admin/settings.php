<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Site Settings';

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('settings.php');
    }

    global $pdo;
    $settings = $_POST['settings'] ?? [];
    $updated = 0;

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([trim($value), $key]);
        $updated += $stmt->rowCount();
    }

    setFlash('success', "Settings updated successfully. ($updated changed)");
    redirect('settings.php');
}

$allSettings = getRows('site_settings', [], 'setting_group ASC, setting_key ASC');
$grouped = [];
foreach ($allSettings as $s) {
    $grouped[$s['setting_group']][] = $s;
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8">
  <h1 class="text-2xl font-bold text-slate-900 font-heading">Site Settings</h1>
  <p class="text-slate-500 text-sm mt-1">Manage global site configuration, contact info, and announcements.</p>
</div>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <?php foreach ($grouped as $group => $items): ?>
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $group))) ?></h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <?php foreach ($items as $item): ?>
      <div class="<?= strlen($item['setting_value'] ?? '') > 80 ? 'md:col-span-2' : '' ?>">
        <label class="block text-sm font-medium text-slate-700 mb-1"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['setting_key']))) ?></label>
        <?php if (strlen($item['setting_value'] ?? '') > 80): ?>
          <textarea name="settings[<?= htmlspecialchars($item['setting_key']) ?>]" rows="3"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($item['setting_value'] ?? '') ?></textarea>
        <?php else: ?>
          <input type="text" name="settings[<?= htmlspecialchars($item['setting_key']) ?>]"
            value="<?= htmlspecialchars($item['setting_value'] ?? '') ?>"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        <?php endif; ?>
        <p class="text-[11px] text-slate-400 mt-0.5">Key: <code class="bg-slate-100 px-1 rounded"><?= htmlspecialchars($item['setting_key']) ?></code></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <div class="flex items-center justify-end gap-3">
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> Save Settings
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
