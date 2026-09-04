<?php
require __DIR__ . '/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$goal = $isEdit ? getRow('sdg_goals', ['id' => $id]) : null;

if ($isEdit && !$goal) {
    setFlash('error', 'SDG goal not found.');
    header('Location: sdg-goals.php');
    exit;
}

$programs = getRows('programs', [], 'sort_order ASC');
$contributions = $isEdit ? getRows('sdg_contributions', ['sdg_goal_id' => $id], '', 0) : [];

// Handle add/edit contribution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_goal') {
        $goal_number = getPostInt('goal_number');
        $data = [
            'goal_number' => $goal_number,
            'title'       => getPost('title'),
            'description' => getPost('description'),
            'color'       => getPost('color'),
            'sort_order'  => getPostInt('sort_order'),
        ];

        if (!$data['title'] || !$goal_number) {
            setFlash('error', 'Goal number and title are required.');
        } else {
            if ($isEdit) {
                updateRow('sdg_goals', $data, ['id' => $id]);
                setFlash('success', 'SDG goal updated!');
            } else {
                $existing = getRow('sdg_goals', ['goal_number' => $goal_number]);
                if ($existing) {
                    setFlash('error', "Goal number $goal_number already exists.");
                } else {
                    insertRow('sdg_goals', $data);
                    setFlash('success', 'SDG goal created!');
                }
            }
            header('Location: sdg-goals.php');
            exit;
        }
    } elseif ($_POST['action'] === 'add_contribution' && $isEdit) {
        $cdata = [
            'sdg_goal_id'        => $id,
            'contribution_text'  => getPost('contribution_text'),
            'progress_percent'   => getPostInt('progress_percent'),
            'program_id'         => getPostInt('program_id') ?: null,
        ];
        if (!$cdata['contribution_text']) {
            setFlash('error', 'Contribution text is required.');
        } else {
            insertRow('sdg_contributions', $cdata);
            setFlash('success', 'Contribution added!');
        }
        header("Location: sdg-edit.php?id=$id");
        exit;
    } elseif ($_POST['action'] === 'delete_contribution' && $isEdit) {
        $cid = (int) $_POST['contribution_id'];
        $pdo->prepare("DELETE FROM sdg_contributions WHERE id = ? AND sdg_goal_id = ?")->execute([$cid, $id]);
        setFlash('success', 'Contribution removed.');
        header("Location: sdg-edit.php?id=$id");
        exit;
    }
}

$pageTitle = $isEdit ? 'Edit SDG Goal' : 'New SDG Goal';
include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?= $pageTitle ?></h1>
    <p class="text-sm text-slate-500 mt-1"><?= $isEdit ? 'Update goal and manage contributions' : 'Add a new UN Sustainable Development Goal' ?></p>
  </div>
  <a href="sdg-goals.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<form method="POST">
  <input type="hidden" name="action" value="save_goal">
  <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Goal Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Goal Number (1-17) *</label>
        <input type="number" name="goal_number" min="1" max="17" value="<?= $goal['goal_number'] ?? '' ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Color</label>
        <input type="color" name="color" value="<?= htmlspecialchars($goal['color'] ?? '#2F6F62') ?>" class="w-full h-10 rounded-xl border border-slate-200 cursor-pointer">
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($goal['title'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($goal['description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= $goal['sort_order'] ?? 0 ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <div class="flex items-center gap-3 mb-8">
    <button type="submit" class="inline-flex items-center gap-2 bg-brandRed text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
      <i class="fa-solid fa-check text-xs"></i> <?= $isEdit ? 'Update Goal' : 'Create Goal' ?>
    </button>
    <a href="sdg-goals.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Cancel</a>
  </div>
</form>

<?php if ($isEdit): ?>
<!-- Contributions -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
  <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Contributions (<?= count($contributions) ?>)</h2>

  <?php if (!empty($contributions)): ?>
  <div class="space-y-3 mb-6">
    <?php foreach ($contributions as $c): ?>
    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
      <div class="flex-1 min-w-0">
        <p class="text-sm text-slate-700"><?= htmlspecialchars($c['contribution_text']) ?></p>
        <div class="flex items-center gap-3 mt-1">
          <span class="text-xs text-slate-400"><?= $c['progress_percent'] ?>% progress</span>
          <?php if ($c['program_id']): ?>
          <?php $prog = getRow('programs', ['id' => $c['program_id']]); ?>
          <?php if ($prog): ?>
          <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full"><?= htmlspecialchars($prog['title']) ?></span>
          <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <form method="POST" class="ml-4 shrink-0" onsubmit="return confirm('Remove this contribution?')">
        <input type="hidden" name="action" value="delete_contribution">
        <input type="hidden" name="contribution_id" value="<?= $c['id'] ?>">
        <button class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors">Remove</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Add contribution form -->
  <form method="POST" class="border-t border-slate-100 pt-4">
    <input type="hidden" name="action" value="add_contribution">
    <h3 class="text-xs font-semibold text-slate-600 mb-3">Add Contribution</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Contribution Text *</label>
        <textarea name="contribution_text" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required></textarea>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Related Program</label>
        <select name="program_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <option value="">— None —</option>
          <?php foreach ($programs as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Progress %</label>
        <input type="number" name="progress_percent" min="0" max="100" value="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
    <button type="submit" class="inline-flex items-center gap-2 bg-teal-600 text-white px-4 py-2 rounded-xl text-xs font-semibold hover:bg-teal-700 transition-colors">
      <i class="fa-solid fa-plus text-xs"></i> Add Contribution
    </button>
  </form>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
