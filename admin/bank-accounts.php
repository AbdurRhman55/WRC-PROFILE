<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Bank Accounts';

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('bank-accounts.php');
    }

    if (isset($_POST['delete_id'])) {
        global $pdo;
        $id = (int) $_POST['delete_id'];
        $stmt = $pdo->prepare("UPDATE bank_accounts SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Bank account deactivated successfully.');
        redirect('bank-accounts.php');
    }
}

$accounts = getRows('bank_accounts', [], 'sort_order ASC');

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">Bank Accounts</h1>
    <p class="text-slate-500 text-sm mt-1">Manage bank account details for donations.</p>
  </div>
  <a href="bank-account-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
    <i class="fa-solid fa-plus text-xs"></i> Add New Account
  </a>
</div>

<!-- Search -->
<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
  <div class="relative max-w-md">
    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
    <input type="text" id="searchInput" placeholder="Search bank accounts..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
  </div>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Bank Name</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Account Title</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Account Number</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">IBAN</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Active</th>
          <th class="text-right px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php if (empty($accounts)): ?>
        <tr>
          <td colspan="6" class="px-5 py-12 text-center text-slate-400">
            <i class="fa-solid fa-folder-open text-3xl mb-3 block text-slate-300"></i>
            No bank accounts found.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($accounts as $i => $account): ?>
        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors <?= $i % 2 === 0 ? '' : 'bg-slate-50/50' ?>">
          <td class="px-5 py-3 font-semibold text-slate-900"><?= htmlspecialchars($account['bank_name']) ?></td>
          <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($account['account_title']) ?></td>
          <td class="px-5 py-3 text-slate-600 font-mono text-xs"><?= htmlspecialchars($account['account_number']) ?></td>
          <td class="px-5 py-3 text-slate-600 font-mono text-xs"><?= htmlspecialchars($account['iban'] ?? '-') ?></td>
          <td class="px-5 py-3">
            <?php if ($account['is_active']): ?>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-200">Active</span>
            <?php else: ?>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-200">Inactive</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3 text-right">
            <div class="flex items-center justify-end gap-2">
              <a href="bank-account-edit.php?id=<?= $account['id'] ?>" class="text-slate-400 hover:text-blue-500 transition-colors p-1.5 rounded-lg hover:bg-blue-50" title="Edit">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
              </a>
              <?php if ($account['is_active']): ?>
              <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to deactivate this bank account?')">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="delete_id" value="<?= $account['id'] ?>">
                <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-1.5 rounded-lg hover:bg-red-50" title="Deactivate">
                  <i class="fa-solid fa-trash text-xs"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
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
  const q = this.value.toLowerCase();
  document.querySelectorAll('#tableBody tr').forEach(row => {
    if (row.querySelector('td[colspan]')) return;
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
