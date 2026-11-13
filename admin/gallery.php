<?php
/**
 * WRC Admin - Project gallery hub.
 *
 * Partner cards -> that partner's projects -> project-media.php for uploads.
 * Also surfaces projects with no partner link so no gallery becomes unreachable.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$base = siteBaseUrl();

global $pdo;

$pageTitle = 'Project Galleries';
$hasLinks = wrcTableExists('partner_projects');
$hasMedia = wrcTableExists('project_media');

$partners = getRows('partners', ['is_active' => 1], 'sort_order ASC, name ASC');

$projectCounts = [];
$photoCounts = [];
if ($hasLinks) {
    foreach ($pdo->query('SELECT partner_id, COUNT(*) AS c FROM partner_projects GROUP BY partner_id') as $row) {
        $projectCounts[(int) $row['partner_id']] = (int) $row['c'];
    }
    if ($hasMedia) {
        foreach ($pdo->query(
            'SELECT pp.partner_id, COUNT(DISTINCT pm.id) AS c
               FROM partner_projects pp
               JOIN project_media pm ON pm.project_slug = pp.project_slug AND pm.is_active = 1
              GROUP BY pp.partner_id'
        ) as $row) {
            $photoCounts[(int) $row['partner_id']] = (int) $row['c'];
        }
    }
} else {
    // Older databases: fall back to the donor sub-table for a useful count.
    try {
        foreach ($pdo->query(
            'SELECT p.id, COUNT(DISTINCT pd.project_id) AS c
               FROM partners p
               JOIN project_donors pd ON LOWER(pd.donor_name) = LOWER(p.name)
              GROUP BY p.id'
        ) as $row) {
            $projectCounts[(int) $row['id']] = (int) $row['c'];
        }
    } catch (Throwable $error) {
        // Counts are optional; the page still opens.
    }
}

$selectedPartner = null;
$partnerId = isset($_GET['partner']) ? (int) $_GET['partner'] : 0;
if ($partnerId > 0) {
    $selectedPartner = getById('partners', $partnerId);
    if (!$selectedPartner) {
        setFlash('error', 'Partner not found.');
        redirect('gallery.php');
    }
}

$partnerProjects = [];
if ($selectedPartner) {
    $partnerProjects = wrcPartnerProjects((int) $selectedPartner['id']);
    if (empty($partnerProjects)) {
        // Admin view should still show linked projects even if the public helper
        // filters for active projects only.
        $stmt = $pdo->prepare(
            'SELECT pr.*, 0 AS media_count
               FROM partner_projects pp
               JOIN projects pr ON pr.slug = pp.project_slug
              WHERE pp.partner_id = ?
              ORDER BY pp.sort_order ASC, pr.sort_order ASC, pr.id ASC'
        );
        $stmt->execute([(int) $selectedPartner['id']]);
        $partnerProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($hasMedia) {
            $count = $pdo->prepare('SELECT COUNT(*) FROM project_media WHERE project_slug = ? AND is_active = 1');
            foreach ($partnerProjects as &$projectRow) {
                $count->execute([(string) $projectRow['slug']]);
                $projectRow['media_count'] = (int) $count->fetchColumn();
            }
            unset($projectRow);
        }
    }
}

$unlinkedProjects = [];
if ($hasLinks) {
    $unlinkedProjects = $pdo->query(
        'SELECT pr.*,
                (SELECT COUNT(*) FROM project_media pm WHERE pm.project_slug = pr.slug AND pm.is_active = 1) AS media_count
           FROM projects pr
          WHERE pr.is_active = 1
            AND pr.slug NOT IN (SELECT project_slug FROM partner_projects)
          ORDER BY pr.sort_order ASC, pr.id ASC'
    )->fetchAll(PDO::FETCH_ASSOC);
}

$totalProjects = (int) $pdo->query('SELECT COUNT(*) FROM projects WHERE is_active = 1')->fetchColumn();
$totalPhotos = $hasMedia ? (int) $pdo->query('SELECT COUNT(*) FROM project_media WHERE is_active = 1')->fetchColumn() : 0;

$typeStyles = [
    'funding' => 'bg-amber-50 text-amber-700 border-amber-200',
    'government' => 'bg-blue-50 text-blue-700 border-blue-200',
    'implementation' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'collaborator' => 'bg-purple-50 text-purple-700 border-purple-200',
];

include __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">
      <a href="gallery.php" class="hover:text-brandRed transition-colors">Project Galleries</a>
      <?php if ($selectedPartner): ?>
        <i class="fa-solid fa-chevron-right text-[7px]"></i>
        <span class="text-slate-600 normal-case tracking-normal"><?= htmlspecialchars((string) $selectedPartner['name']) ?></span>
      <?php endif; ?>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $selectedPartner ? 'Partner Projects' : 'Project Galleries' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $selectedPartner ? 'Open a project to upload and edit its photographs.' : 'Choose a partner, then a project, to manage its photographs and context.' ?></p>
  </div>
  <div class="flex items-center gap-3 shrink-0">
    <a href="../news/gallery.php" target="_blank" class="inline-flex items-center gap-2 bg-ink-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-brandRed transition-colors">
      <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Public gallery
    </a>
  </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-2xl border border-slate-200 p-4">
    <p class="text-2xl font-bold text-slate-900"><?= count($partners) ?></p>
    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Active partners</p>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-4">
    <p class="text-2xl font-bold text-slate-900"><?= $totalProjects ?></p>
    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Active projects</p>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-4">
    <p class="text-2xl font-bold text-slate-900"><?= $totalPhotos ?></p>
    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Live photographs</p>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 p-4">
    <p class="text-2xl font-bold <?= $unlinkedProjects ? 'text-amber-600' : 'text-emerald-600' ?>"><?= count($unlinkedProjects) ?></p>
    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Unlinked projects</p>
  </div>
</div>

<?php if (!empty($partners) && !$selectedPartner): ?>
<div class="relative mb-5">
  <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
  <input type="text" id="partnerSearch" placeholder="Search partners..." class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
</div>
<?php endif; ?>

<?php if (!$selectedPartner): ?>
  <!-- PARTNER CARDS -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="partnerCards">
    <?php foreach ($partners as $partner): ?>
      <?php
        $pid = (int) $partner['id'];
        $pc = (int) ($projectCounts[$pid] ?? 0);
        $ph = (int) ($photoCounts[$pid] ?? 0);
        $type = (string) ($partner['partner_type'] ?? 'collaborator');
        $typeInfo = wrcPartnerTypeInfo($type);
        $typeChip = $typeStyles[$type] ?? $typeStyles['collaborator'];
        $logo = trim((string) ($partner['logo_path'] ?? ''));
        $cover = trim((string) ($partner['cover_image'] ?? ''));
        $initials = wrcPartnerInitials((string) $partner['name']);
      ?>
      <div class="partner-admin-card bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-brandRed/30 hover:shadow-xl transition-all" data-search="<?= htmlspecialchars(strtolower($partner['name'] . ' ' . $typeInfo['label'])) ?>">
        <div class="relative h-28 bg-ink-900 overflow-hidden">
          <?php if ($cover !== '' && imageExists($cover)): ?>
            <img src="<?= $base ?><?= htmlspecialchars($cover, ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover opacity-80">
          <?php else: ?>
            <div class="absolute inset-0 bg-gradient-to-br from-ink-800 to-black"></div>
            <span class="absolute right-2 -bottom-4 font-display font-semibold text-6xl text-white/10"><?= htmlspecialchars($initials) ?></span>
          <?php endif; ?>
          <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 to-transparent"></div>
          <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full border <?= $typeChip ?> bg-white/95 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider">
            <i class="fa-solid <?= htmlspecialchars($typeInfo['icon']) ?> text-[8px]"></i><?= htmlspecialchars($typeInfo['short']) ?>
          </span>
        </div>
        <div class="p-4 -mt-6 relative">
          <div class="w-11 h-11 rounded-xl bg-white border border-slate-200 shadow-sm p-1.5 flex items-center justify-center overflow-hidden mb-2">
            <?php if ($logo !== '' && imageExists($logo)): ?>
              <img src="<?= $base ?><?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-contain">
            <?php else: ?>
              <span class="text-xs font-bold text-slate-700"><?= htmlspecialchars($initials) ?></span>
            <?php endif; ?>
          </div>
          <h3 class="text-sm font-bold text-slate-900 truncate"><?= htmlspecialchars((string) $partner['name']) ?></h3>
          <p class="text-[11px] text-slate-400 mt-1 line-clamp-2"><?= htmlspecialchars((string) ($partner['description'] ?? 'No profile yet.')) ?></p>
          <div class="flex items-center gap-3 mt-3 text-[10px] font-semibold text-slate-400">
            <span><i class="fa-solid fa-folder-open text-brandRed/60 mr-1"></i><?= $pc ?> projects</span>
            <span><i class="fa-solid fa-camera-retro text-brandRed/60 mr-1"></i><?= $ph ?> photos</span>
          </div>
          <div class="flex items-center gap-2 mt-4">
            <a href="gallery.php?partner=<?= $pid ?>" class="flex-1 inline-flex items-center justify-center gap-2 bg-brandRed text-white text-xs font-semibold px-3 py-2.5 rounded-xl hover:bg-brandRed-hover transition-colors">
              Open projects <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </a>
            <a href="partner-edit.php?id=<?= $pid ?>" class="w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-brandRed hover:border-brandRed/30 flex items-center justify-center transition-colors" title="Edit partner">
              <i class="fa-solid fa-pen-to-square text-xs"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($partners)): ?>
  <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
    <i class="fa-solid fa-handshake text-3xl text-slate-300 mb-3 block"></i>
    <h3 class="text-base font-bold text-slate-800">No partners yet</h3>
    <p class="text-sm text-slate-400 mt-1">Add a partner first, then link the projects it delivered.</p>
    <a href="partner-edit.php" class="inline-flex items-center gap-2 mt-4 bg-brandRed text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-brandRed-hover transition-colors">Add partner</a>
  </div>
  <?php endif; ?>

  <?php if ($unlinkedProjects): ?>
  <div class="mt-8">
    <div class="flex items-center gap-2 mb-3">
      <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-link-slash text-xs"></i></span>
      <div>
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Projects without a partner</h2>
        <p class="text-[11px] text-slate-400">Their galleries still work, but they do not appear under any partner card.</p>
      </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden">
      <?php foreach ($unlinkedProjects as $project): ?>
      <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-3.5">
        <img src="<?= htmlspecialchars(getImageUrl((string) $project['hero_image']), ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full sm:w-16 h-14 rounded-lg object-cover border border-slate-200 shrink-0">
        <div class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars((string) $project['title']) ?></p>
          <p class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars((string) ($project['category'] ?? 'Project')) ?><?= !empty($project['district']) ? ' · ' . htmlspecialchars((string) $project['district']) : '' ?> · <?= (int) ($project['media_count'] ?? 0) ?> photos</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <a href="project-media.php?slug=<?= rawurlencode((string) $project['slug']) ?>" class="inline-flex items-center gap-2 bg-brandRed text-white text-xs font-semibold px-3.5 py-2 rounded-lg hover:bg-brandRed-hover transition-colors"><i class="fa-solid fa-camera-retro text-[10px]"></i> Photos</a>
          <a href="partner-edit.php" class="text-[11px] font-semibold text-slate-500 hover:text-brandRed transition-colors px-2 py-2">Link to a partner</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

<?php else: ?>
  <!-- ONE PARTNER: PROJECT CARDS -->
  <?php
    $type = (string) ($selectedPartner['partner_type'] ?? 'collaborator');
    $typeInfo = wrcPartnerTypeInfo($type);
    $typeChip = $typeStyles[$type] ?? $typeStyles['collaborator'];
    $spCover = trim((string) ($selectedPartner['cover_image'] ?? ''));
    $spLogo = trim((string) ($selectedPartner['logo_path'] ?? ''));
  ?>
  <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6 flex flex-col lg:flex-row lg:items-center gap-5">
    <div class="relative w-full lg:w-48 h-32 rounded-xl overflow-hidden bg-ink-900 shrink-0 flex items-center justify-center">
      <?php if ($spCover !== '' && imageExists($spCover)): ?>
        <img src="<?= $base ?><?= htmlspecialchars($spCover, ENT_QUOTES, 'UTF-8') ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
      <?php else: ?>
        <span class="font-display font-semibold text-4xl text-white/15"><?= htmlspecialchars(wrcPartnerInitials((string) $selectedPartner['name'])) ?></span>
      <?php endif; ?>
      <?php if ($spLogo !== '' && imageExists($spLogo)): ?>
        <span class="absolute bottom-2 left-2 w-10 h-10 rounded-lg bg-white p-1.5 shadow"><img src="<?= $base ?><?= htmlspecialchars($spLogo, ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-contain"></span>
      <?php endif; ?>
    </div>
    <div class="min-w-0 flex-1">
      <span class="inline-flex items-center gap-1.5 rounded-full border <?= $typeChip ?> px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider mb-2"><i class="fa-solid <?= htmlspecialchars($typeInfo['icon']) ?> text-[9px]"></i><?= htmlspecialchars($typeInfo['short']) ?></span>
      <h2 class="text-xl font-bold text-slate-900"><?= htmlspecialchars((string) $selectedPartner['name']) ?></h2>
      <p class="text-sm text-slate-500 mt-1 max-w-2xl"><?= htmlspecialchars((string) ($selectedPartner['description'] ?? 'No profile written yet.')) ?></p>
      <div class="flex flex-wrap items-center gap-3 mt-3 text-[11px] text-slate-400">
        <span><i class="fa-solid fa-folder-open text-brandRed/60 mr-1"></i><?= count($partnerProjects) ?> project<?= count($partnerProjects) === 1 ? '' : 's' ?></span>
        <span><i class="fa-solid fa-camera-retro text-brandRed/60 mr-1"></i><?= (int) ($photoCounts[(int) $selectedPartner['id']] ?? 0) ?> photographs</span>
        <?php if (!empty($selectedPartner['slug'])): ?>
        <a href="../news/partner.php?slug=<?= rawurlencode((string) $selectedPartner['slug']) ?>" target="_blank" class="text-brandRed hover:underline font-semibold">Preview partner page <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <a href="partner-edit.php?id=<?= (int) $selectedPartner['id'] ?>" class="inline-flex items-center justify-center gap-2 border border-slate-200 text-slate-600 text-sm font-semibold px-4 py-2.5 rounded-xl hover:border-brandRed/30 hover:text-brandRed transition-colors shrink-0">
      <i class="fa-solid fa-pen-to-square text-xs"></i> Edit &amp; link projects
    </a>
  </div>

  <div class="flex items-center justify-between gap-3 mb-3">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Projects delivered with this partner</h2>
    <a href="gallery.php" class="text-[11px] font-semibold text-slate-500 hover:text-brandRed transition-colors"><i class="fa-solid fa-arrow-left text-[9px] mr-1"></i> All partners</a>
  </div>

  <?php if (empty($partnerProjects)): ?>
  <div class="text-center py-14 bg-white rounded-2xl border border-slate-200">
    <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-3 block"></i>
    <h3 class="text-base font-bold text-slate-800">No projects linked</h3>
    <p class="text-sm text-slate-400 mt-1">Tick projects in the partner editor to show them here and on the public partner page.</p>
    <a href="partner-edit.php?id=<?= (int) $selectedPartner['id'] ?>" class="inline-flex items-center gap-2 mt-4 bg-brandRed text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-brandRed-hover transition-colors">Link projects</a>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
    <?php foreach ($partnerProjects as $project): ?>
      <?php
        $statusMeta = wrcProjectStatusMeta((string) ($project['status'] ?? ''));
        $yearLabel = wrcProjectYearLabel($project);
        $mediaCount = (int) ($project['media_count'] ?? 0);
        $place = trim((string) ($project['district'] ?? ''));
      ?>
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col sm:flex-row">
        <div class="relative sm:w-48 h-40 sm:h-auto shrink-0 bg-slate-200 overflow-hidden">
          <img src="<?= htmlspecialchars(getImageUrl((string) $project['hero_image']), ENT_QUOTES, 'UTF-8') ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
          <span class="absolute top-2 left-2 inline-flex items-center gap-1.5 rounded-full border <?= $statusMeta['classes'] ?> bg-white/95 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"><span class="w-1.5 h-1.5 rounded-full <?= $statusMeta['dot'] ?>"></span><?= htmlspecialchars($statusMeta['label']) ?></span>
        </div>
        <div class="p-4 flex flex-col flex-1 min-w-0">
          <div class="flex items-center gap-2 text-[10px] font-semibold text-slate-400">
            <span class="text-brandRed"><?= htmlspecialchars((string) ($project['category'] ?? 'Project')) ?></span>
            <?php if ($yearLabel !== ''): ?><span>·</span><span><?= htmlspecialchars($yearLabel) ?></span><?php endif; ?>
            <?php if ($place !== ''): ?><span>·</span><span class="truncate"><?= htmlspecialchars($place) ?></span><?php endif; ?>
          </div>
          <h3 class="text-sm font-bold text-slate-900 leading-snug mt-1.5 line-clamp-2"><?= htmlspecialchars((string) $project['title']) ?></h3>
          <div class="flex items-center gap-3 mt-2 text-[11px] font-semibold <?= $mediaCount > 0 ? 'text-emerald-600' : 'text-amber-600' ?>">
            <span><i class="fa-solid fa-camera-retro mr-1"></i><?= $mediaCount ?> photo<?= $mediaCount === 1 ? '' : 's' ?></span>
            <?php if ($mediaCount === 0): ?><span class="text-[10px] font-normal text-slate-400">Cover image only</span><?php endif; ?>
          </div>
          <div class="mt-auto pt-3 flex items-center gap-2">
            <a href="project-media.php?slug=<?= rawurlencode((string) $project['slug']) ?>" class="flex-1 inline-flex items-center justify-center gap-2 bg-brandRed text-white text-xs font-semibold px-3 py-2.5 rounded-xl hover:bg-brandRed-hover transition-colors">
              <i class="fa-solid fa-camera-retro text-[10px]"></i> Manage photos
            </a>
            <a href="../news/project-gallery.php?slug=<?= rawurlencode((string) $project['slug']) ?>" target="_blank" class="w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-brandRed hover:border-brandRed/30 flex items-center justify-center transition-colors" title="Preview gallery">
              <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
            </a>
            <a href="project-edit.php?id=<?= (int) $project['id'] ?>" class="w-10 h-10 rounded-xl border border-slate-200 text-slate-500 hover:text-brandRed hover:border-brandRed/30 flex items-center justify-center transition-colors" title="Edit project">
              <i class="fa-solid fa-pen-to-square text-[11px]"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

<?php endif; ?>

<script>
(function () {
  var search = document.getElementById('partnerSearch');
  var cards = Array.prototype.slice.call(document.querySelectorAll('.partner-admin-card'));
  if (!search || !cards.length) return;

  search.addEventListener('input', function () {
    var query = this.value.toLowerCase().trim();
    cards.forEach(function (card) {
      card.style.display = (card.getAttribute('data-search') || '').indexOf(query) !== -1 ? '' : 'none';
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
