<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Partners';
global $pdo;

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('partners.php');
    }

    if (isset($_POST['delete_id'])) {
        $id = (int) $_POST['delete_id'];
        $pdo->prepare("DELETE FROM partners WHERE id = ?")->execute([$id]);
        if (wrcTableExists('partner_projects')) {
            $pdo->prepare("DELETE FROM partner_projects WHERE partner_id = ?")->execute([$id]);
        }
        setFlash('success', 'Partner deleted successfully.');
        redirect('partners.php');
    }
}

$partners = getRows('partners', [], 'sort_order ASC, name ASC');

$projectCounts = [];
$photoCounts = [];
if (wrcTableExists('partner_projects')) {
    foreach ($pdo->query('SELECT partner_id, COUNT(*) AS c FROM partner_projects GROUP BY partner_id') as $row) {
        $projectCounts[(int) $row['partner_id']] = (int) $row['c'];
    }
    if (wrcTableExists('project_media')) {
        foreach ($pdo->query(
            'SELECT pp.partner_id, COUNT(DISTINCT pm.id) AS c
               FROM partner_projects pp
               JOIN project_media pm ON pm.project_slug = pp.project_slug AND pm.is_active = 1
              GROUP BY pp.partner_id'
        ) as $row) {
            $photoCounts[(int) $row['partner_id']] = (int) $row['c'];
        }
    }
}

$typeColors = [
    'funding' => 'bg-amber-50 text-amber-600 border-amber-200',
    'government' => 'bg-blue-50 text-blue-600 border-blue-200',
    'implementation' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
    'collaborator' => 'bg-purple-50 text-purple-600 border-purple-200',
];

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">Partners &amp; Donors</h1>
    <p class="text-slate-500 text-sm mt-1">Manage profiles, cover images and the project galleries linked to each partner.</p>
  </div>
  <div class="flex items-center gap-3">
    <a href="gallery.php" class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-semibold hover:border-brandRed/30 hover:text-brandRed transition-colors">
      <i class="fa-solid fa-images text-xs"></i> Project Galleries
    </a>
    <a href="partner-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-plus text-xs"></i> Add Partner
    </a>
  </div>
</div>

<!-- Search -->
<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
  <div class="relative max-w-md">
    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
    <input type="text" id="searchInput" placeholder="Search partners..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
  </div>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Partner</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Category</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Galleries</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
          <th class="text-right px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php if (empty($partners)): ?>
        <tr>
          <td colspan="5" class="px-5 py-12 text-center text-slate-400">
            <i class="fa-solid fa-handshake text-3xl mb-3 block text-slate-300"></i>
            No partners found.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($partners as $i => $partner): ?>
        <?php
          $pid = (int) $partner['id'];
          $pc = (int) ($projectCounts[$pid] ?? 0);
          $ph = (int) ($photoCounts[$pid] ?? 0);
          $type = (string) ($partner['partner_type'] ?? 'collaborator');
          $typeInfo = wrcPartnerTypeInfo($type);
          $typeColor = $typeColors[$type] ?? 'bg-slate-50 text-slate-600 border-slate-200';
          $logo = trim((string) ($partner['logo_path'] ?? ''));
          $cover = trim((string) ($partner['cover_image'] ?? ''));
          $thumb = $cover !== '' && imageExists($cover) ? $cover : $logo;
        ?>
        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors <?= $i % 2 === 0 ? '' : 'bg-slate-50/50' ?>">
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <?php if ($thumb !== '' && imageExists($thumb)): ?>
              <img src="<?= htmlspecialchars(getImageUrl($thumb), ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-11 h-11 rounded-xl object-cover border border-slate-200 bg-white shrink-0">
              <?php else: ?>
              <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs shrink-0"><?= htmlspecialchars(wrcPartnerInitials((string) $partner['name'])) ?></div>
              <?php endif; ?>
              <div class="min-w-0">
                <p class="font-semibold text-slate-900 truncate"><?= htmlspecialchars((string) $partner['name']) ?></p>
                <p class="text-[11px] text-slate-400 font-mono truncate"><?= htmlspecialchars((string) ($partner['slug'] ?? '—')) ?></p>
              </div>
            </div>
          </td>
          <td class="px-5 py-3">
            <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wider px-2 py-1 rounded-full border <?= $typeColor ?>">
              <i class="fa-solid <?= htmlspecialchars($typeInfo['icon']) ?> text-[9px]"></i><?= htmlspecialchars($typeInfo['short']) ?>
            </span>
          </td>
          <td class="px-5 py-3">
            <a href="gallery.php?partner=<?= $pid ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-brandRed transition-colors">
              <span><i class="fa-solid fa-folder-open text-brandRed/60 mr-1"></i><?= $pc ?></span>
              <span class="text-slate-200">/</span>
              <span><i class="fa-solid fa-camera-retro text-brandRed/60 mr-1"></i><?= $ph ?></span>
            </a>
          </td>
          <td class="px-5 py-3">
            <?php if ($partner['is_active']): ?>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-200">Active</span>
            <?php else: ?>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-200">Inactive</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3 text-right">
            <div class="flex items-center justify-end gap-1.5">
              <a href="gallery.php?partner=<?= $pid ?>" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-brandRed hover:underline px-2 py-1.5" title="Manage galleries">
                Galleries
              </a>
              <a href="partner-edit.php?id=<?= $pid ?>" class="text-slate-400 hover:text-blue-500 transition-colors p-1.5 rounded-lg hover:bg-blue-50" title="Edit">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
              </a>
              <form method="POST" class="inline" onsubmit="return confirm('Delete this partner? Its project links will be removed, but project photographs will be kept.')">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="delete_id" value="<?= $pid ?>">
                <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-1.5 rounded-lg hover:bg-red-50" title="Delete">
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
