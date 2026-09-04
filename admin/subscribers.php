<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Newsletter Subscribers';

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('subscribers.php');
    }

    global $pdo;

    if (isset($_POST['toggle_active'])) {
        $id = (int) $_POST['subscriber_id'];
        $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', "Subscriber #$id status toggled.");
        redirect('subscribers.php');
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, email, is_active, subscribed_at FROM newsletter_subscribers ORDER BY subscribed_at DESC");
    $stmt->execute();
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['ID', 'Email', 'Active', 'Subscribed At']);
    foreach ($rows as $row) {
        fputcsv($output, [
            $row['id'],
            $row['email'],
            $row['is_active'] ? 'Yes' : 'No',
            $row['subscribed_at'],
        ]);
    }
    fclose($output);
    exit;
}

$filterActive = $_GET['active'] ?? '';

global $pdo;
$sql = "SELECT * FROM newsletter_subscribers";
$params = [];
if ($filterActive === '1' || $filterActive === '0') {
    $sql .= " WHERE is_active = ?";
    $params[] = (int) $filterActive;
}
$sql .= " ORDER BY subscribed_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subscribers = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">Newsletter Subscribers</h1>
    <p class="text-slate-500 text-sm mt-1">View and manage newsletter subscriber list.</p>
  </div>
  <div class="flex items-center gap-3">
    <a href="?export=csv" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm">
      <i class="fa-solid fa-file-csv text-green-500"></i> Export CSV
    </a>
    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-semibold text-slate-600">
      <i class="fa-solid fa-users text-brandRed"></i>
      <?= count($subscribers) ?> total
    </span>
  </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
  <form method="GET" class="flex flex-col sm:flex-row gap-3">
    <div class="flex-1">
      <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
      <select name="active" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
        <option value="">All</option>
        <option value="1" <?= $filterActive === '1' ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= $filterActive === '0' ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>
    <div class="flex items-end gap-2">
      <button type="submit" class="bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
        <i class="fa-solid fa-filter mr-1"></i> Filter
      </button>
      <?php if ($filterActive !== ''): ?>
      <a href="subscribers.php" class="bg-slate-100 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
        Clear
      </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">ID</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Email</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Subscribed At</th>
          <th class="text-right px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php if (empty($subscribers)): ?>
        <tr>
          <td colspan="5" class="px-5 py-12 text-center text-slate-400">
            <i class="fa-solid fa-users text-3xl mb-3 block text-slate-300"></i>
            No subscribers found.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($subscribers as $i => $sub): ?>
        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors <?= $i % 2 === 0 ? '' : 'bg-slate-50/50' ?>">
          <td class="px-5 py-3 text-slate-500 font-mono text-xs"><?= $sub['id'] ?></td>
          <td class="px-5 py-3 font-semibold text-slate-900"><?= htmlspecialchars($sub['email']) ?></td>
          <td class="px-5 py-3">
            <?php if ($sub['is_active']): ?>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-200">Active</span>
            <?php else: ?>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">Inactive</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3 text-slate-500 text-xs"><?= date('M d, Y g:i A', strtotime($sub['subscribed_at'])) ?></td>
          <td class="px-5 py-3 text-right">
            <form method="POST" class="inline" onsubmit="return confirm('Toggle active status for this subscriber?')">
              <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
              <input type="hidden" name="toggle_active" value="1">
              <input type="hidden" name="subscriber_id" value="<?= $sub['id'] ?>">
              <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors <?= $sub['is_active'] ? 'text-amber-600 hover:bg-amber-50' : 'text-green-600 hover:bg-green-50' ?>" title="<?= $sub['is_active'] ? 'Deactivate' : 'Activate' ?>">
                <i class="fa-solid <?= $sub['is_active'] ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                <?= $sub['is_active'] ? 'Deactivate' : 'Activate' ?>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
  var q = this.value.toLowerCase();
  document.querySelectorAll('#tableBody tr').forEach(function(row) {
    if (row.querySelector('td[colspan]')) return;
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
