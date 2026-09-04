<?php
require __DIR__ . '/config.php';
requireAdmin();

$sdg_goals = getRows('sdg_goals', [], 'goal_number ASC');
$programs = getRows('programs', [], 'sort_order ASC');

// Get contribution counts
$contribCounts = [];
foreach ($sdg_goals as $g) {
    $contribCounts[$g['id']] = getRow("SELECT COUNT(*) AS cnt FROM sdg_contributions WHERE sdg_goal_id = ?", [$g['id']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (int) $_POST['delete_id'];
        $pdo->prepare("DELETE FROM sdg_contributions WHERE sdg_goal_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM sdg_goals WHERE id = ?")->execute([$id]);
        setFlash('success', 'SDG goal and its contributions deleted.');
    }
    header('Location: sdg-goals.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">SDG Goals</h1>
    <p class="text-sm text-slate-500 mt-1">Manage Sustainable Development Goals & contributions</p>
  </div>
  <a href="sdg-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
    <i class="fa-solid fa-plus text-xs"></i> Add SDG Goal
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <?php if (empty($sdg_goals)): ?>
  <div class="text-center py-16">
    <i class="fa-solid fa-globe text-4xl text-slate-200 mb-4"></i>
    <p class="text-slate-400 text-sm">No SDG goals yet.</p>
    <a href="sdg-edit.php" class="mt-3 inline-block text-sm text-brandRed font-semibold hover:underline">Add your first goal &rarr;</a>
  </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <th class="px-4 py-3 text-slate-500 font-semibold">#</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Goal</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Contributions</th>
          <th class="px-4 py-3 text-slate-500 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($sdg_goals as $goal): ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-sm" style="background:<?= $goal['color'] ?: '#2F6F62' ?>">
              <?= $goal['goal_number'] ?>
            </div>
          </td>
          <td class="px-4 py-3">
            <p class="font-semibold text-slate-900"><?= htmlspecialchars($goal['title']) ?></p>
            <?php if ($goal['description']): ?>
            <p class="text-xs text-slate-400 mt-0.5 line-clamp-1"><?= htmlspecialchars($goal['description']) ?></p>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold"><?= ($contribCounts[$goal['id']]['cnt'] ?? 0) ?> contributions</span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="sdg-edit.php?id=<?= $goal['id'] ?>" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">Edit</a>
              <form method="POST" onsubmit="return confirm('Delete this SDG goal and all its contributions?')">
                <input type="hidden" name="delete_id" value="<?= $goal['id'] ?>">
                <button class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
