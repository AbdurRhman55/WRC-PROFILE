<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'History Milestones';

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('milestones.php');
    }

    if (isset($_POST['delete_id'])) {
        global $pdo;
        $id = (int) $_POST['delete_id'];
        $stmt = $pdo->prepare("UPDATE history_milestones SET is_current = 0 WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Milestone deactivated successfully.');
        redirect('milestones.php');
    }
}

$milestones = getRows('history_milestones', [], 'year DESC, sort_order ASC');

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">History Milestones</h1>
    <p class="text-slate-500 text-sm mt-1">Manage organizational history milestones.</p>
  </div>
  <a href="milestone-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
    <i class="fa-solid fa-plus text-xs"></i> Add New Milestone
  </a>
</div>

<!-- Search -->
<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
  <div class="relative max-w-md">
    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
    <input type="text" id="searchInput" placeholder="Search milestones..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
  </div>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Year</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Title</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Description</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Current</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Sort</th>
          <th class="text-right px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php if (empty($milestones)): ?>
        <tr>
          <td colspan="6" class="px-5 py-12 text-center text-slate-400">
            <i class="fa-solid fa-folder-open text-3xl mb-3 block text-slate-300"></i>
            No milestones found.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($milestones as $i => $milestone): ?>
        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors <?= $i % 2 === 0 ? '' : 'bg-slate-50/50' ?>">
          <td class="px-5 py-3 font-mono font-semibold text-slate-900"><?= (int) $milestone['year'] ?></td>
          <td class="px-5 py-3 font-semibold text-slate-900"><?= htmlspecialchars($milestone['title']) ?></td>
          <td class="px-5 py-3 text-slate-600 max-w-xs truncate" title="<?= htmlspecialchars($milestone['description']) ?>"><?= htmlspecialchars(mb_strimwidth($milestone['description'], 0, 80, '...')) ?></td>
          <td class="px-5 py-3">
            <?php if ($milestone['is_current']): ?>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-brandRed/10 text-brandRed border border-brandRed/20">Current</span>
            <?php else: ?>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-50 text-slate-500 border border-slate-200">Past</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3 text-slate-500 font-mono text-xs"><?= (int) $milestone['sort_order'] ?></td>
          <td class="px-5 py-3 text-right">
            <div class="flex items-center justify-end gap-2">
              <a href="milestone-edit.php?id=<?= $milestone['id'] ?>" class="text-slate-400 hover:text-blue-500 transition-colors p-1.5 rounded-lg hover:bg-blue-50" title="Edit">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
              </a>
              <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to deactivate this milestone?')">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="delete_id" value="<?= $milestone['id'] ?>">
                <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-1.5 rounded-lg hover:bg-red-50" title="Deactivate">
                  <i class="fa-solid fa-trash text-xs"></i>
                </button>
              </form>
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
