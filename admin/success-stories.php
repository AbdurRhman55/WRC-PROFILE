<?php
require_once __DIR__ . '/config.php';
requireAdmin();

$success_stories = getRows('success_stories', [], 'sort_order ASC, created_at DESC');
$sectors = ['health' => 'Health', 'livelihoods' => 'Livelihoods', 'wash' => 'WASH', 'protection' => 'Protection', 'disaster-risk' => 'Disaster Risk Reduction', 'governance' => 'Governance'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
            setFlash('error', 'Invalid security token.');
            redirect('success-stories.php');
        }
        $id = (int) $_POST['delete_id'];
        $story = getRow('success_stories', ['id' => $id]);
        if ($story && $story['image_path'] && file_exists(__DIR__ . '/../' . $story['image_path'])) {
            unlink(__DIR__ . '/../' . $story['image_path']);
        }
        $pdo->prepare("DELETE FROM success_stories WHERE id = ?")->execute([$id]);
        setFlash('success', 'Success story deleted.');
    }
    header('Location: success-stories.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Success Stories</h1>
    <p class="text-sm text-slate-500 mt-1">Manage testimonials and beneficiary stories</p>
  </div>
  <a href="success-story-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
    <i class="fa-solid fa-plus text-xs"></i> Add Story
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <?php if (empty($success_stories)): ?>
  <div class="text-center py-16">
    <i class="fa-solid fa-quote-left text-4xl text-slate-200 mb-4"></i>
    <p class="text-slate-400 text-sm">No success stories yet.</p>
    <a href="success-story-edit.php" class="mt-3 inline-block text-sm text-brandRed font-semibold hover:underline">Add your first story &rarr;</a>
  </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <th class="px-4 py-3 text-slate-500 font-semibold">Story</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Sector</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Status</th>
          <th class="px-4 py-3 text-slate-500 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($success_stories as $story): ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-amber-50 overflow-hidden shrink-0">
                <?php if ($story['image_path']): ?>
                <img src="/<?= $story['image_path'] ?>" alt="" class="w-full h-full object-cover">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-user text-amber-300 text-xs"></i></div>
                <?php endif; ?>
              </div>
              <div>
                <p class="font-semibold text-slate-900"><?= htmlspecialchars($story['person_name']) ?></p>
                <p class="text-xs text-slate-400"><?= htmlspecialchars($story['person_title'] ?? '') ?> <?= $story['location'] ? '— ' . htmlspecialchars($story['location']) : '' ?></p>
              </div>
            </div>
          </td>
          <td class="px-4 py-3">
            <?php if ($story['sector']): ?>
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full bg-teal-50 text-teal-700"><?= $sectors[$story['sector']] ?? $story['sector'] ?></span>
            <?php else: ?>
            <span class="text-slate-400">—</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <span class="w-2 h-2 rounded-full inline-block <?= $story['is_active'] ? 'bg-green-400' : 'bg-slate-300' ?>"></span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="success-story-edit.php?id=<?= $story['id'] ?>" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">Edit</a>
              <form method="POST" onsubmit="return confirm('Delete this story?')">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="delete_id" value="<?= $story['id'] ?>">
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
