<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Resources Manager';
$activeTab = $_GET['tab'] ?? 'hero';
if (!in_array($activeTab, ['hero', 'reports', 'registrations'])) $activeTab = 'hero';

global $pdo;

// ===== HANDLE HERO SAVE =====
if (isPost() && isset($_POST['save_hero'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('resources.php?tab=hero'); }
    $hero_image = getPost('hero_image');
    $section_title = getPost('section_title');
    $section_subtitle = getPost('section_subtitle');
    $tagline = getPost('tagline');
    $title = getPost('title');
    $subtitle = getPost('subtitle');
    $button1_text = getPost('button1_text');
    $button1_link = getPost('button1_link');
    $button2_text = getPost('button2_text');
    $button2_link = getPost('button2_link');

    $stats = [];
    $statLabels = $_POST['stat_label'] ?? [];
    $statValues = $_POST['stat_value'] ?? [];
    foreach ($statLabels as $i => $label) {
        if (!empty($label) || !empty($statValues[$i])) {
            $stats[] = ['label' => $label, 'value' => $statValues[$i] ?? ''];
        }
    }

    $content = json_encode([
        'hero_image' => $hero_image,
        'tagline' => $tagline,
        'title' => $title,
        'subtitle' => $subtitle,
        'button1_text' => $button1_text,
        'button1_link' => $button1_link,
        'button2_text' => $button2_text,
        'button2_link' => $button2_link,
        'stats' => $stats,
    ], JSON_UNESCAPED_UNICODE);

    $exists = getRow('resources_hero');
    $updateData = ['section_title' => $section_title, 'section_subtitle' => $section_subtitle, 'hero_image' => $hero_image, 'content' => $content];
    if ($exists) { updateRow('resources_hero', $updateData, ['id' => $exists['id']]); } else { insertRow('resources_hero', $updateData); }
    setFlash('success', 'Hero section saved successfully.');
    redirect('resources.php?tab=hero');
}

// ===== HANDLE REPORT ACTIONS =====
if (isPost() && isset($_POST['delete_report'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('resources.php?tab=reports'); }
    $stmt = $pdo->prepare("UPDATE resources SET is_active = 0 WHERE id = ?");
    $stmt->execute([(int) $_POST['delete_report']]);
    setFlash('success', 'Report deactivated.');
    redirect('resources.php?tab=reports');
}
if (isPost() && isset($_POST['restore_report'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('resources.php?tab=reports'); }
    $stmt = $pdo->prepare("UPDATE resources SET is_active = 1 WHERE id = ?");
    $stmt->execute([(int) $_POST['restore_report']]);
    setFlash('success', 'Report restored.');
    redirect('resources.php?tab=reports');
}
if (isPost() && isset($_POST['hard_delete_report'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('resources.php?tab=reports'); }
    $res = getRow('resources', ['id' => (int) $_POST['hard_delete_report']]);
    if ($res && !empty($res['file_path']) && file_exists(__DIR__ . '/../' . $res['file_path'])) { unlink(__DIR__ . '/../' . $res['file_path']); }
    $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
    $stmt->execute([(int) $_POST['hard_delete_report']]);
    setFlash('success', 'Report permanently deleted.');
    redirect('resources.php?tab=reports');
}

// ===== HANDLE REGISTRATION ACTIONS =====
if (isPost() && isset($_POST['delete_reg'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('resources.php?tab=registrations'); }
    $reg = getRow('resources_registration', ['id' => (int) $_POST['delete_reg']]);
    if ($reg && !empty($reg['file_path']) && file_exists(__DIR__ . '/../' . $reg['file_path'])) { unlink(__DIR__ . '/../' . $reg['file_path']); }
    $stmt = $pdo->prepare("DELETE FROM resources_registration WHERE id = ?");
    $stmt->execute([(int) $_POST['delete_reg']]);
    setFlash('success', 'Registration deleted.');
    redirect('resources.php?tab=registrations');
}
if (isPost() && isset($_POST['toggle_reg'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('resources.php?tab=registrations'); }
    $id = (int) $_POST['toggle_reg'];
    $stmt = $pdo->prepare("SELECT is_active FROM resources_registration WHERE id = ?");
    $stmt->execute([$id]);
    $newVal = $stmt->fetchColumn() ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE resources_registration SET is_active = ? WHERE id = ?");
    $stmt->execute([$newVal, $id]);
    setFlash('success', $newVal ? 'Registration activated.' : 'Registration deactivated.');
    redirect('resources.php?tab=registrations');
}

// ===== LOAD DATA =====
$heroRow = getRow('resources_hero');
$heroContent = json_decode($heroRow['content'] ?? '{}', true);

$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'annual_report', 'financial_report', 'audit_report'])) $filter = 'all';

$stats = [];
foreach (['annual_report', 'financial_report', 'audit_report'] as $type) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(is_active=1) as active FROM resources WHERE resource_type = ?");
    $stmt->execute([$type]);
    $stats[$type] = $stmt->fetch(PDO::FETCH_ASSOC);
}
$stats['all'] = ['total' => array_sum(array_column($stats, 'total')), 'active' => array_sum(array_column($stats, 'active'))];

if ($filter !== 'all') {
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE resource_type = ? ORDER BY year DESC, sort_order ASC");
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE resource_type IN ('annual_report','financial_report','audit_report') ORDER BY resource_type, year DESC, sort_order ASC");
    $stmt->execute();
}
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

$regStats = $pdo->query("SELECT COUNT(*) as total, SUM(is_active=1) as active, SUM(is_active=0) as inactive FROM resources_registration")->fetch(PDO::FETCH_ASSOC);
$registrations = getRows('resources_registration', [], 'sort_order ASC');

$typeLabels = ['annual_report' => 'Annual Report', 'financial_report' => 'Financial Report', 'audit_report' => 'Audit Report'];
$typeIcons = ['annual_report' => 'fa-chart-line', 'financial_report' => 'fa-coins', 'audit_report' => 'fa-magnifying-glass-chart'];

include __DIR__ . '/includes/header.php';
?>

<style>
.tab-btn{padding:10px 20px;border-radius:12px;font-size:13px;font-weight:600;color:#64748b;transition:all .15s;cursor:pointer;display:inline-flex;align-items:center;gap:8px;border:2px solid transparent}
.tab-btn:hover{background:#f1f5f9;color:#0e1f27}
.tab-btn.active{background:rgba(202,0,0,.06);color:#ca0000;border-color:rgba(202,0,0,.15)}
.tab-content{display:none}.tab-content.active{display:block}
</style>

<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Resources Manager</h1>
    <p class="text-sm text-slate-500 mt-1">Manage hero section, reports, and registration documents</p>
  </div>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<!-- Tab Navigation -->
<div class="bg-white rounded-2xl border border-slate-200 p-2 mb-6 inline-flex gap-1 flex-wrap">
  <button onclick="switchTab('hero')" class="tab-btn <?= $activeTab === 'hero' ? 'active' : '' ?>" id="tabBtn-hero">
    <i class="fa-solid fa-image text-sm"></i> Hero Section
  </button>
  <button onclick="switchTab('reports')" class="tab-btn <?= $activeTab === 'reports' ? 'active' : '' ?>" id="tabBtn-reports">
    <i class="fa-solid fa-chart-line text-sm"></i> Reports <span class="text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-full font-bold ml-1"><?= $stats['all']['total'] ?></span>
  </button>
  <button onclick="switchTab('registrations')" class="tab-btn <?= $activeTab === 'registrations' ? 'active' : '' ?>" id="tabBtn-registrations">
    <i class="fa-solid fa-stamp text-sm"></i> Registrations <span class="text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-full font-bold ml-1"><?= $regStats['total'] ?></span>
  </button>
</div>


<!-- ==================== HERO TAB ==================== -->
<div class="tab-content <?= $activeTab === 'hero' ? 'active' : '' ?>" id="tab-hero">
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
    <input type="hidden" name="save_hero" value="1">

    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
      <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Hero Image & Branding</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Hero Image</label>
          <input type="text" name="hero_image" value="<?= htmlspecialchars($heroRow['hero_image'] ?? $heroContent['hero_image'] ?? '') ?>" placeholder="assets/images/hero/resources-hero.jpg" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Tagline (Badge)</label>
          <input type="text" name="tagline" value="<?= htmlspecialchars($heroContent['tagline'] ?? '') ?>" placeholder="Transparency & Accountability" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
      <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Hero Content</h2>
      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Section Title (DB)</label>
          <input type="text" name="section_title" value="<?= htmlspecialchars($heroRow['section_title'] ?? '') ?>" placeholder="Resources & Documents" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <p class="text-[11px] text-slate-400 mt-1">Overrides JSON title below if set</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Section Subtitle (DB)</label>
          <textarea name="section_subtitle" rows="2" placeholder="Description text..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($heroRow['section_subtitle'] ?? '') ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Title (JSON)</label>
          <input type="text" name="title" value="<?= htmlspecialchars($heroContent['title'] ?? '') ?>" placeholder="Resources & Documents" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Subtitle (JSON)</label>
          <textarea name="subtitle" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($heroContent['subtitle'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
      <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Buttons</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Button 1 Text</label>
          <input type="text" name="button1_text" value="<?= htmlspecialchars($heroContent['button1_text'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Button 1 Link</label>
          <input type="text" name="button1_link" value="<?= htmlspecialchars($heroContent['button1_link'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Button 2 Text</label>
          <input type="text" name="button2_text" value="<?= htmlspecialchars($heroContent['button2_text'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Button 2 Link</label>
          <input type="text" name="button2_link" value="<?= htmlspecialchars($heroContent['button2_link'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
      <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Stats</h2>
      <div id="heroStatsList" class="space-y-3"></div>
      <button type="button" onclick="addHeroStat()" class="inline-flex items-center gap-2 text-brandRed text-xs font-semibold mt-3 hover:underline"><i class="fa-solid fa-plus text-[10px]"></i> Add Stat</button>
    </div>

    <div class="flex justify-end">
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm"><i class="fa-solid fa-check text-xs mr-1"></i> Save Hero</button>
    </div>
  </form>
</div>


<!-- ==================== REPORTS TAB ==================== -->
<div class="tab-content <?= $activeTab === 'reports' ? 'active' : '' ?>" id="tab-reports">
  <div class="flex items-center justify-between mb-6">
    <div class="text-sm text-slate-500">Manage annual, financial, and audit reports</div>
    <a href="resource-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm">
      <i class="fa-solid fa-plus text-xs"></i> Add Report
    </a>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <a href="?tab=reports&filter=all" class="group bg-white rounded-2xl border border-slate-200 p-4 hover:shadow-lg hover:border-brandRed/30 transition-all <?= $filter === 'all' ? 'ring-2 ring-brandRed/20 border-brandRed/30' : '' ?>">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center group-hover:bg-brandRed/10 transition-colors"><i class="fa-solid fa-layer-group text-slate-500 group-hover:text-brandRed"></i></div>
        <div><div class="text-xl font-bold text-slate-900"><?= $stats['all']['total'] ?></div><div class="text-[11px] text-slate-500 font-medium">All Reports</div></div>
      </div>
    </a>
    <?php foreach (['annual_report', 'financial_report', 'audit_report'] as $type): ?>
    <a href="?tab=reports&filter=<?= $type ?>" class="group bg-white rounded-2xl border border-slate-200 p-4 hover:shadow-lg transition-all <?= $filter === $type ? 'ring-2 ring-brandRed/20 border-brandRed/30' : '' ?>">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl <?= $type === 'annual_report' ? 'bg-blue-50' : ($type === 'financial_report' ? 'bg-emerald-50' : 'bg-amber-50') ?> flex items-center justify-center">
          <i class="fa-solid <?= $typeIcons[$type] ?> <?= $type === 'annual_report' ? 'text-blue-600' : ($type === 'financial_report' ? 'text-emerald-600' : 'text-amber-600') ?>"></i>
        </div>
        <div><div class="text-xl font-bold text-slate-900"><?= $stats[$type]['total'] ?></div><div class="text-[11px] text-slate-500 font-medium"><?= $typeLabels[$type] ?>s</div></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Filter Tabs -->
  <div class="bg-white rounded-2xl border border-slate-200 p-1.5 mb-6 inline-flex gap-1">
    <a href="?tab=reports&filter=all" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= $filter === 'all' ? 'bg-brandRed text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' ?>"><i class="fa-solid fa-layer-group mr-1.5"></i> All</a>
    <?php foreach (['annual_report', 'financial_report', 'audit_report'] as $type): ?>
    <a href="?tab=reports&filter=<?= $type ?>" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= $filter === $type ? 'bg-brandRed text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' ?>"><i class="fa-solid <?= $typeIcons[$type] ?> mr-1.5"></i> <?= $typeLabels[$type] ?>s</a>
    <?php endforeach; ?>
  </div>

  <!-- Search -->
  <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
    <div class="relative max-w-md">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input type="text" id="reportSearch" placeholder="Search reports..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">ID</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Title</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Type</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Year</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Format</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
            <th class="text-right px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="reportTableBody">
          <?php if (empty($resources)): ?>
          <tr><td colspan="7" class="px-5 py-16 text-center"><div class="flex flex-col items-center"><div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4"><i class="fa-solid fa-folder-open text-2xl text-slate-300"></i></div><p class="text-slate-400 text-sm font-medium mb-1">No reports found</p><a href="resource-edit.php" class="mt-3 inline-flex items-center gap-2 bg-brandRed text-white px-4 py-2 rounded-xl text-xs font-semibold hover:bg-red-700 transition-colors"><i class="fa-solid fa-plus text-[10px]"></i> Add Report</a></div></td></tr>
          <?php else: ?>
          <?php foreach ($resources as $i => $r): ?>
          <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors <?= $i % 2 !== 0 ? 'bg-slate-50/50' : '' ?> <?= !$r['is_active'] ? 'opacity-60' : '' ?>">
            <td class="px-5 py-3 text-slate-400 font-mono text-xs"><?= $r['id'] ?></td>
            <td class="px-5 py-3"><div class="font-semibold text-slate-900 max-w-xs truncate"><?= htmlspecialchars($r['title']) ?></div><?php if (!empty($r['description'])): ?><div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5"><?= htmlspecialchars(mb_substr($r['description'], 0, 60)) ?>...</div><?php endif; ?></td>
            <td class="px-5 py-3"><?php $tc = $r['resource_type'] === 'annual_report' ? 'bg-blue-50 text-blue-600 border-blue-200' : ($r['resource_type'] === 'financial_report' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-amber-50 text-amber-600 border-amber-200'); ?><span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full <?= $tc ?> border"><i class="fa-solid <?= $typeIcons[$r['resource_type']] ?? 'fa-file' ?> text-[10px]"></i> <?= $typeLabels[$r['resource_type']] ?? $r['resource_type'] ?></span></td>
            <td class="px-5 py-3 text-slate-700 font-semibold text-xs"><?= $r['year'] ?: '-' ?></td>
            <td class="px-5 py-3"><span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-500"><i class="fa-solid fa-file-pdf text-[10px]"></i> <?= strtoupper($r['file_type'] ?? 'PDF') ?></span></td>
            <td class="px-5 py-3"><?= $r['is_active'] ? '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-200"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active</span>' : '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactive</span>' ?></td>
            <td class="px-5 py-3 text-right"><div class="flex items-center justify-end gap-1.5">
              <?php if (!empty($r['file_path'])): ?><a href="/<?= htmlspecialchars($r['file_path']) ?>" target="_blank" class="text-slate-400 hover:text-emerald-500 transition-colors p-1.5 rounded-lg hover:bg-emerald-50" title="Download"><i class="fa-solid fa-download text-xs"></i></a><?php endif; ?>
              <a href="resource-edit.php?id=<?= $r['id'] ?>" class="text-slate-400 hover:text-blue-500 transition-colors p-1.5 rounded-lg hover:bg-blue-50" title="Edit"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
              <?php if ($r['is_active']): ?>
              <form method="POST" class="inline" onsubmit="return confirm('Deactivate?')"><input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"><input type="hidden" name="delete_report" value="<?= $r['id'] ?>"><button class="text-slate-400 hover:text-amber-500 p-1.5 rounded-lg hover:bg-amber-50"><i class="fa-solid fa-eye-slash text-xs"></i></button></form>
              <?php else: ?>
              <form method="POST" class="inline" onsubmit="return confirm('Restore?')"><input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"><input type="hidden" name="restore_report" value="<?= $r['id'] ?>"><button class="text-slate-400 hover:text-green-500 p-1.5 rounded-lg hover:bg-green-50"><i class="fa-solid fa-eye text-xs"></i></button></form>
              <form method="POST" class="inline" onsubmit="return confirm('Permanently delete?')"><input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"><input type="hidden" name="hard_delete_report" value="<?= $r['id'] ?>"><button class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50"><i class="fa-solid fa-trash text-xs"></i></button></form>
              <?php endif; ?>
            </div></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- ==================== REGISTRATIONS TAB ==================== -->
<div class="tab-content <?= $activeTab === 'registrations' ? 'active' : '' ?>" id="tab-registrations">
  <div class="flex items-center justify-between mb-6">
    <div class="text-sm text-slate-500">Manage legal registrations and certification documents</div>
    <a href="resource-registration-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm">
      <i class="fa-solid fa-plus text-xs"></i> Add Registration
    </a>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-violet-50 flex items-center justify-center"><i class="fa-solid fa-stamp text-violet-600 text-lg"></i></div>
        <div><div class="text-xl font-bold text-slate-900"><?= $regStats['total'] ?></div><div class="text-[11px] text-slate-500 font-medium">Total</div></div>
      </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center"><i class="fa-solid fa-check-circle text-green-600 text-lg"></i></div>
        <div><div class="text-xl font-bold text-slate-900"><?= $regStats['active'] ?></div><div class="text-[11px] text-slate-500 font-medium">Active</div></div>
      </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center"><i class="fa-solid fa-eye-slash text-amber-600 text-lg"></i></div>
        <div><div class="text-xl font-bold text-slate-900"><?= $regStats['inactive'] ?></div><div class="text-[11px] text-slate-500 font-medium">Inactive</div></div>
      </div>
    </div>
  </div>

  <!-- Search -->
  <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
    <div class="relative max-w-md">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input type="text" id="regSearch" placeholder="Search registrations..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">ID</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Registration</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Reg. Number</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Authority</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Date</th>
            <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
            <th class="text-right px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="regTableBody">
          <?php if (empty($registrations)): ?>
          <tr><td colspan="7" class="px-5 py-16 text-center"><div class="flex flex-col items-center"><div class="w-16 h-16 rounded-full bg-violet-100 flex items-center justify-center mb-4"><i class="fa-solid fa-stamp text-2xl text-violet-300"></i></div><p class="text-slate-400 text-sm font-medium mb-1">No registrations found</p><a href="resource-registration-edit.php" class="mt-3 inline-flex items-center gap-2 bg-brandRed text-white px-4 py-2 rounded-xl text-xs font-semibold hover:bg-red-700 transition-colors"><i class="fa-solid fa-plus text-[10px]"></i> Add Registration</a></div></td></tr>
          <?php else: ?>
          <?php foreach ($registrations as $i => $reg): ?>
          <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors <?= $i % 2 !== 0 ? 'bg-slate-50/50' : '' ?> <?= !$reg['is_active'] ? 'opacity-60' : '' ?>">
            <td class="px-5 py-3 text-slate-400 font-mono text-xs"><?= $reg['id'] ?></td>
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center shrink-0"><i class="fa-solid <?= htmlspecialchars($reg['icon'] ?? 'fa-stamp') ?> text-violet-600 text-sm"></i></div>
                <div><div class="font-semibold text-slate-900 text-sm"><?= htmlspecialchars($reg['title']) ?></div><?php if (!empty($reg['badge'])): ?><span class="inline-block text-[10px] bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-bold mt-0.5"><?= htmlspecialchars($reg['badge']) ?></span><?php endif; ?></div>
              </div>
            </td>
            <td class="px-5 py-3 text-slate-600 font-mono text-xs"><?= htmlspecialchars($reg['registration_number'] ?: '-') ?></td>
            <td class="px-5 py-3 text-slate-600 text-xs max-w-[180px] truncate"><?= htmlspecialchars($reg['issuing_authority'] ?: '-') ?></td>
            <td class="px-5 py-3 text-slate-500 text-xs"><?= $reg['issue_date'] ? date('M Y', strtotime($reg['issue_date'])) : '-' ?><?php if (!empty($reg['expiry_date'])): ?><span class="text-[10px] text-amber-600 block">Exp: <?= date('M Y', strtotime($reg['expiry_date'])) ?></span><?php endif; ?></td>
            <td class="px-5 py-3"><?= $reg['is_active'] ? '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-200"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active</span>' : '<span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactive</span>' ?></td>
            <td class="px-5 py-3 text-right"><div class="flex items-center justify-end gap-1.5">
              <?php if (!empty($reg['file_path'])): ?><a href="/<?= htmlspecialchars($reg['file_path']) ?>" target="_blank" class="text-slate-400 hover:text-emerald-500 p-1.5 rounded-lg hover:bg-emerald-50" title="Download"><i class="fa-solid fa-download text-xs"></i></a><?php endif; ?>
              <a href="resource-registration-edit.php?id=<?= $reg['id'] ?>" class="text-slate-400 hover:text-blue-500 p-1.5 rounded-lg hover:bg-blue-50" title="Edit"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
              <form method="POST" class="inline" onsubmit="return confirm('Toggle visibility?')"><input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"><input type="hidden" name="toggle_reg" value="<?= $reg['id'] ?>"><button class="text-slate-400 hover:text-amber-500 p-1.5 rounded-lg hover:bg-amber-50" title="<?= $reg['is_active'] ? 'Deactivate' : 'Activate' ?>"><i class="fa-solid <?= $reg['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?> text-xs"></i></button></form>
              <form method="POST" class="inline" onsubmit="return confirm('Permanently delete?')"><input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>"><input type="hidden" name="delete_reg" value="<?= $reg['id'] ?>"><button class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50"><i class="fa-solid fa-trash text-xs"></i></button></form>
            </div></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<script>
function switchTab(tab) { window.location.href = 'resources.php?tab=' + tab; }

var heroStatsArr = <?= json_encode($heroContent['stats'] ?? [], JSON_HEX_TAG) ?>;
function renderHeroStats() {
  var el = document.getElementById('heroStatsList');
  if (!el) return; el.innerHTML = '';
  heroStatsArr.forEach(function(s, i) {
    el.innerHTML += '<div class="flex items-center gap-3 dynamic-item"><button type="button" class="remove-btn" onclick="heroStatsArr.splice('+i+',1);renderHeroStats()"><i class="fa-solid fa-xmark"></i></button><div class="flex-1"><div class="flex gap-3"><input value="'+(s.label||'').replace(/"/g,'&quot;')+'" onchange="heroStatsArr['+i+'].label=this.value" placeholder="Label" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><input value="'+(s.value||'').replace(/"/g,'&quot;')+'" onchange="heroStatsArr['+i+'].value=this.value" placeholder="Value" class="w-32 px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"></div></div></div>';
  });
}
function addHeroStat() { heroStatsArr.push({label:'',value:''}); renderHeroStats(); }
renderHeroStats();

document.getElementById('reportSearch')?.addEventListener('input', function() {
  var q = this.value.toLowerCase();
  document.querySelectorAll('#reportTableBody tr').forEach(function(row) { if (row.querySelector('td[colspan]')) return; row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
});
document.getElementById('regSearch')?.addEventListener('input', function() {
  var q = this.value.toLowerCase();
  document.querySelectorAll('#regTableBody tr').forEach(function(row) { if (row.querySelector('td[colspan]')) return; row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none'; });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
