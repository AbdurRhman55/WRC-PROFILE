<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Hero Sliders';

if (isPost() && isset($_POST['delete_id'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('hero-sliders.php'); }
    global $pdo;
    $stmt = $pdo->prepare("UPDATE hero_sliders SET is_active = 0 WHERE id = ?");
    $stmt->execute([(int) $_POST['delete_id']]);
    setFlash('success', 'Slider deactivated.');
    redirect('hero-sliders.php');
}
if (isPost() && isset($_POST['restore_id'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('hero-sliders.php'); }
    global $pdo;
    $stmt = $pdo->prepare("UPDATE hero_sliders SET is_active = 1 WHERE id = ?");
    $stmt->execute([(int) $_POST['restore_id']]);
    setFlash('success', 'Slider restored.');
    redirect('hero-sliders.php');
}
if (isPost() && isset($_POST['hard_delete_id'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('hero-sliders.php'); }
    $s = getRow('hero_sliders', ['id' => (int) $_POST['hard_delete_id']]);
    if ($s && !empty($s['image_path']) && file_exists(__DIR__ . '/../' . $s['image_path'])) { unlink(__DIR__ . '/../' . $s['image_path']); }
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM hero_sliders WHERE id = ?");
    $stmt->execute([(int) $_POST['hard_delete_id']]);
    setFlash('success', 'Slider permanently deleted.');
    redirect('hero-sliders.php');
}

$pageFilter = $_GET['page'] ?? 'all';
$validPages = ['all', 'homepage', 'news', 'about', 'our-work', 'impact', 'get-involved', 'contact', 'where-we-work', 'projects', 'resources', 'contribute'];
if (!in_array($pageFilter, $validPages)) $pageFilter = 'all';

global $pdo;
if ($pageFilter !== 'all') {
    $stmt = $pdo->prepare("SELECT * FROM hero_sliders WHERE page = ? ORDER BY sort_order ASC");
    $stmt->execute([$pageFilter]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM hero_sliders ORDER BY page, sort_order ASC");
    $stmt->execute();
}
$sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageLabels = ['homepage' => 'Homepage', 'news' => 'News & Media', 'about' => 'About', 'our-work' => 'Our Work', 'impact' => 'Impact', 'get-involved' => 'Get Involved', 'contact' => 'Contact', 'where-we-work' => 'Where We Work', 'projects' => 'Projects', 'resources' => 'Resources', 'contribute' => 'Contribute'];
$pageColors = ['homepage' => 'bg-blue-50 text-blue-600', 'news' => 'bg-red-50 text-red-600', 'about' => 'bg-emerald-50 text-emerald-600', 'our-work' => 'bg-amber-50 text-amber-600', 'impact' => 'bg-violet-50 text-violet-600', 'get-involved' => 'bg-pink-50 text-pink-600', 'contact' => 'bg-cyan-50 text-cyan-600', 'where-we-work' => 'bg-orange-50 text-orange-600', 'projects' => 'bg-indigo-50 text-indigo-600', 'resources' => 'bg-teal-50 text-teal-600', 'contribute' => 'bg-rose-50 text-rose-600'];

$pageCounts = [];
$stmt = $pdo->query("SELECT page, COUNT(*) as cnt FROM hero_sliders GROUP BY page");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $pageCounts[$row['page']] = $row['cnt']; }
$total = array_sum($pageCounts);

include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Hero Sliders</h1>
    <p class="text-sm text-slate-500 mt-1">Manage hero sliders for all pages from one place</p>
  </div>
  <a href="hero-slider-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm">
    <i class="fa-solid fa-plus text-xs"></i> Add Slider
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<!-- Page Filter Tabs -->
<div class="bg-white rounded-2xl border border-slate-200 p-2 mb-6 inline-flex gap-1 flex-wrap">
  <a href="?page=all" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= $pageFilter === 'all' ? 'bg-brandRed text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' ?>">
    <i class="fa-solid fa-layer-group mr-1.5"></i> All <span class="ml-1 text-[10px] opacity-70"><?= $total ?></span>
  </a>
  <?php foreach ($pageLabels as $key => $label): ?>
  <?php if (!empty($pageCounts[$key])): ?>
  <a href="?page=<?= $key ?>" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= $pageFilter === $key ? 'bg-brandRed text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' ?>">
    <?= $label ?> <span class="ml-1 text-[10px] opacity-70"><?= $pageCounts[$key] ?></span>
  </a>
  <?php endif; ?>
  <?php endforeach; ?>
</div>

<!-- Search -->
<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
  <div class="relative max-w-md">
    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
    <input type="text" id="searchInput" placeholder="Search sliders..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
  </div>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Preview</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Title</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Page</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">CTA</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Sort</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
          <th class="text-right px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php if (empty($sliders)): ?>
        <tr><td colspan="7" class="px-5 py-16 text-center"><div class="flex flex-col items-center"><div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4"><i class="fa-solid fa-images text-2xl text-slate-300"></i></div><p class="text-slate-400 text-sm font-medium mb-1">No sliders found</p><a href="hero-slider-edit.php" class="mt-3 inline-flex items-center gap-2 bg-brandRed text-white px-4 py-2 rounded-xl text-xs font-semibold hover:bg-red-700 transition-colors"><i class="fa-solid fa-plus text-[10px]"></i> Add Slider</a></div></td></tr>
        <?php else: ?>
        <?php foreach ($sliders as $i => $s): ?>
        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors <?= $i % 2 !== 0 ? 'bg-slate-50/50' : '' ?> <?= !$s['is_active'] ? 'opacity-60' : '' ?>">
          <td class="px-5 py-3">
            <?php if (!empty($s['image_path'])): ?>
            <img src="<?= htmlspecialchars(getImageUrl($s['image_path']), ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-20 h-12 rounded-lg object-cover border border-slate-200">
            <?php else: ?>
            <div class="w-20 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center"><i class="fa-solid fa-image text-slate-300 text-xs"></i></div>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3">
            <div class="font-semibold text-slate-900 max-w-xs truncate"><?= htmlspecialchars($s['title']) ?></div>
            <?php if (!empty($s['badge'])): ?><span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-bold"><?= htmlspecialchars($s['badge']) ?></span><?php endif; ?>
          </td>
          <td class="px-5 py-3"><span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-1 rounded-full <?= $pageColors[$s['page']] ?? 'bg-slate-50 text-slate-600' ?>"><?= $pageLabels[$s['page']] ?? $s['page'] ?></span></td>
          <td class="px-5 py-3 text-slate-600 text-xs"><?= htmlspecialchars($s['cta_text'] ?? '-') ?></td>
          <td class="px-5 py-3 text-slate-500 font-mono text-xs"><?= (int) $s['sort_order'] ?></td>
          <td class="px-5 py-3"><?= $s['is_active'] ? '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-200"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active</span>' : '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactive</span>' ?></td>
          <td class="px-5 py-3 text-right"><div class="flex items-center justify-end gap-1.5">
            <a href="hero-slider-edit.php?id=<?= $s['id'] ?>" class="text-slate-400 hover:text-blue-500 p-1.5 rounded-lg hover:bg-blue-50" title="Edit"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
            <?php if ($s['is_active']): ?>
            <form method="POST" class="inline" onsubmit="return confirm('Deactivate?')"><input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"><input type="hidden" name="delete_id" value="<?= $s['id'] ?>"><button class="text-slate-400 hover:text-amber-500 p-1.5 rounded-lg hover:bg-amber-50"><i class="fa-solid fa-eye-slash text-xs"></i></button></form>
            <?php else: ?>
            <form method="POST" class="inline" onsubmit="return confirm('Restore?')"><input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"><input type="hidden" name="restore_id" value="<?= $s['id'] ?>"><button class="text-slate-400 hover:text-green-500 p-1.5 rounded-lg hover:bg-green-50"><i class="fa-solid fa-eye text-xs"></i></button></form>
            <form method="POST" class="inline" onsubmit="return confirm('Permanently delete?')"><input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"><input type="hidden" name="hard_delete_id" value="<?= $s['id'] ?>"><button class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50"><i class="fa-solid fa-trash text-xs"></i></button></form>
            <?php endif; ?>
          </div></td>
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
  document.querySelectorAll('#tableBody tr').forEach(function(row) { if (row.querySelector('td[colspan]')) return; row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
