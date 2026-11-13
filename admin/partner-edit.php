<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Add Partner';
$errors = [];
$partner = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $partner = getById('partners', (int) $_GET['id']);
    if (!$partner) {
        setFlash('error', 'Partner not found.');
        redirect('partners.php');
    }
    $pageTitle = 'Edit Partner: ' . $partner['name'];
}

$partnerTypes = wrcPartnerTypeMap();
$hasProjectLinks = wrcTableExists('partner_projects');

// Projects available for linking. The media count gives the editor an instant
// view of which galleries still need photographs.
$projects = getRows('projects', ['is_active' => 1], 'sort_order ASC, id ASC');
$mediaCounts = [];
if (wrcTableExists('project_media')) {
    global $pdo;
    foreach ($pdo->query('SELECT project_slug, COUNT(*) AS c FROM project_media WHERE is_active = 1 GROUP BY project_slug') as $row) {
        $mediaCounts[(string) $row['project_slug']] = (int) $row['c'];
    }
}

$linkedProjectSlugs = [];
if ($partner && $hasProjectLinks) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT project_slug FROM partner_projects WHERE partner_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([(int) $partner['id']]);
    $linkedProjectSlugs = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('partners.php');
    }

    $name = getPost('name');
    $slug = createSlug(getPost('slug') !== '' ? getPost('slug') : $name);
    $logo_path = getPost('logo_path');
    $cover_image = getPost('cover_image');
    $website_url = getPost('website_url');
    $partner_type = getPost('partner_type');
    $description = getPost('description');
    $sort_order = getPostInt('sort_order');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $postedSlugs = array_map('strval', (array) ($_POST['project_slugs'] ?? []));
    $postedSlugs = array_values(array_unique(array_filter(array_map('trim', $postedSlugs))));
    $linkedProjectSlugs = $postedSlugs;

    if (empty($name)) $errors[] = 'Name is required.';
    if ($slug === '') $errors[] = 'Slug could not be generated. Please enter one manually.';
    if (!in_array($partner_type, array_keys($partnerTypes), true)) $errors[] = 'Invalid partner type.';
    if (!empty($website_url) && !filter_var($website_url, FILTER_VALIDATE_URL)) $errors[] = 'Invalid website URL.';
    if ($slug !== '' && !checkSlugUnique('partners', $slug, $partner['id'] ?? 0)) $errors[] = 'That slug is already used by another partner.';

    // Two records for one organisation split its project gallery in two, so a
    // new partner may not reuse a name that already exists under another
    // spelling ("Health Department" vs "Department of Health").
    if ($name !== '' && function_exists('wrcPartnerAliasKey')) {
        global $pdo;
        $aliasKey = wrcPartnerAliasKey($name);
        $existingPartners = $pdo->query('SELECT id, name FROM partners ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($existingPartners as $existingPartner) {
            if ((int) $existingPartner['id'] === (int) ($partner['id'] ?? 0)) {
                continue;
            }
            if (wrcPartnerAliasKey((string) $existingPartner['name']) === $aliasKey) {
                $errors[] = 'This organisation already exists as "' . $existingPartner['name'] . '". Edit that partner instead of creating a duplicate.';
                break;
            }
        }
    }


    if (empty($errors)) {
        global $pdo;

        $data = [
            'name' => $name,
            'slug' => $slug,
            'logo_path' => $logo_path,
            'website_url' => $website_url,
            'partner_type' => $partner_type,
            'is_active' => $is_active,
            'sort_order' => $sort_order,
        ];
        if (tableHasColumn('partners', 'description')) {
            $data['description'] = $description;
        }
        if (tableHasColumn('partners', 'cover_image')) {
            $data['cover_image'] = $cover_image;
        }

        if ($isEdit) {
            updateRow('partners', $data, ['id' => (int) $partner['id']]);
            $partnerId = (int) $partner['id'];
            setFlash('success', 'Partner details updated.');
        } else {
            $partnerId = insertRow('partners', $data);
            setFlash('success', 'Partner created successfully.');
        }

        // Replace the project checklist selection with exactly what the editor
        // chose. Only real, active project slugs are accepted.
        if ($hasProjectLinks && $partnerId > 0) {
            $validSlugs = $pdo->query('SELECT slug FROM projects WHERE is_active = 1')->fetchAll(PDO::FETCH_COLUMN);
            $validSlugs = array_map('strval', $validSlugs);
            $selected = array_values(array_intersect($postedSlugs, $validSlugs));

            $pdo->prepare('DELETE FROM partner_projects WHERE partner_id = ?')->execute([$partnerId]);
            if ($selected) {
                $insert = $pdo->prepare('INSERT IGNORE INTO partner_projects (partner_id, project_slug, sort_order) VALUES (?, ?, ?)');
                foreach ($selected as $index => $projectSlug) {
                    $insert->execute([$partnerId, $projectSlug, $index + 1]);
                }
            }

            setFlash('success', 'Partner saved with ' . count($selected) . ' linked project' . (count($selected) === 1 ? '' : 's') . '.');
        }

        redirect('partners.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Partner' : 'Add New Partner' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update profile details, cover image and linked project galleries.' : 'Create a partner profile and link the projects it delivered.' ?></p>
  </div>
  <div class="flex items-center gap-3">
    <?php if ($isEdit && !empty($partner['slug'])): ?>
    <a href="../news/partner.php?slug=<?= rawurlencode((string) $partner['slug']) ?>" target="_blank" class="inline-flex items-center gap-2 text-slate-500 hover:text-brandRed text-sm font-medium transition-colors">
      <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> Preview
    </a>
    <?php endif; ?>
    <a href="partners.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
      <i class="fa-solid fa-arrow-left text-xs"></i> Back to Partners
    </a>
  </div>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
  <div class="flex items-center gap-2 mb-1"><i class="fa-solid fa-circle-exclamation"></i><span class="font-semibold text-sm">Please fix the following errors:</span></div>
  <ul class="list-disc list-inside text-sm mt-1">
    <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<?php if (!$hasProjectLinks): ?>
<div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
  <i class="fa-solid fa-triangle-exclamation mr-1"></i>
  The project-gallery tables are not available yet. Run <code class="font-mono text-xs bg-amber-100 px-1.5 py-0.5 rounded">database/seed_media_gallery.php</code> to enable project linking and photo management.
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <!-- Basic Info -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <div class="flex items-center gap-2 mb-5">
      <span class="w-8 h-8 rounded-lg bg-brandRed/10 text-brandRed flex items-center justify-center"><i class="fa-solid fa-circle-info text-xs"></i></span>
      <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Basic Information</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
        <input type="text" name="name" id="partnerName" value="<?= htmlspecialchars($partner['name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Slug (public URL)</label>
        <input type="text" name="slug" id="partnerSlug" value="<?= htmlspecialchars($partner['slug'] ?? '') ?>" placeholder="auto-generated-from-name" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        <p class="text-[11px] text-slate-400 mt-1">Used in <span class="font-mono">news/partner.php?slug=...</span>. Leave blank to generate it from the name.</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Partner Category *</label>
        <select name="partner_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <?php foreach ($partnerTypes as $typeKey => $typeInfo): ?>
          <option value="<?= htmlspecialchars($typeKey) ?>" <?= ($partner['partner_type'] ?? 'funding') === $typeKey ? 'selected' : '' ?>><?= htmlspecialchars($typeInfo['label']) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="text-[11px] text-slate-400 mt-1">Controls which category section the partner appears in on the public gallery.</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Website URL</label>
        <input type="url" name="website_url" value="<?= htmlspecialchars($partner['website_url'] ?? '') ?>" placeholder="https://example.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>

    <div class="mt-4">
      <label class="block text-sm font-medium text-slate-700 mb-1">Short Profile</label>
      <textarea name="description" rows="3" placeholder="Describe the partner's role and the work delivered with WRC. This text appears on the gallery card and partner page." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($partner['description'] ?? '') ?></textarea>
      <div class="flex items-center justify-between mt-1">
        <p class="text-[11px] text-slate-400">Keep it to one or two sentences for the cleanest card layout.</p>
        <span class="text-[11px] text-slate-300" id="descriptionCount">0 / 280</span>
      </div>
    </div>
  </div>

  <!-- Images -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <div class="flex items-center gap-2 mb-5">
      <span class="w-8 h-8 rounded-lg bg-brandRed/10 text-brandRed flex items-center justify-center"><i class="fa-solid fa-images text-xs"></i></span>
      <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Brand &amp; Cover Images</h2>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div>
        <?php uploadWidget('logo_path', 'Partner Logo', $partner['logo_path'] ?? '', 'image', 'partner_logo'); ?>
        <p class="text-[11px] text-slate-400 -mt-1">Transparent PNG or SVG works best. Shown as a small badge on cards.</p>
      </div>
      <div>
        <?php uploadWidget('cover_image', 'Cover Image', $partner['cover_image'] ?? '', 'image', 'partner_cover'); ?>
        <p class="text-[11px] text-slate-400 -mt-1">Wide photograph used as the card background. If left blank, the first linked project's cover is used.</p>
      </div>
    </div>
  </div>

  <?php if ($hasProjectLinks): ?>
  <!-- Linked Projects -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
      <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-lg bg-brandRed/10 text-brandRed flex items-center justify-center"><i class="fa-solid fa-folder-open text-xs"></i></span>
        <div>
          <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Linked Project Galleries</h2>
          <p class="text-[11px] text-slate-400 mt-0.5">Tick every project this partner delivered. Each project has its own photo gallery.</p>
        </div>
      </div>
      <span class="inline-flex items-center gap-2 self-start sm:self-auto rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
        <i class="fa-solid fa-check text-brandRed text-[9px]"></i>
        <span id="selectedProjectCount"><?= count($linkedProjectSlugs) ?></span> selected
      </span>
    </div>

    <div class="relative mb-4">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input type="text" id="projectSearch" placeholder="Search projects by title, category or location..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
    </div>

    <div class="rounded-2xl border border-slate-200 divide-y divide-slate-100 max-h-[420px] overflow-y-auto" id="projectChecklist">
      <?php foreach ($projects as $project): ?>
      <?php
        $projectSlug = (string) $project['slug'];
        $checked = in_array($projectSlug, $linkedProjectSlugs, true);
        $mediaCount = (int) ($mediaCounts[$projectSlug] ?? 0);
        $yearLabel = wrcProjectYearLabel($project);
        $searchText = strtolower($project['title'] . ' ' . ($project['category'] ?? '') . ' ' . ($project['district'] ?? ''));
      ?>
      <div class="project-check-row flex items-center gap-3 p-3 hover:bg-slate-50 transition-colors" data-search="<?= htmlspecialchars($searchText) ?>">
        <label class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer">
        <input type="checkbox" name="project_slugs[]" value="<?= htmlspecialchars($projectSlug) ?>" <?= $checked ? 'checked' : '' ?> class="project-checkbox w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20 shrink-0">
        <img src="<?= htmlspecialchars(getImageUrl((string) $project['hero_image']), ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-12 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
        <span class="min-w-0 flex-1">
          <span class="block text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars((string) $project['title']) ?></span>
          <span class="block text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars((string) ($project['category'] ?? 'Project')) ?><?= $yearLabel !== '' ? ' · ' . htmlspecialchars($yearLabel) : '' ?><?= !empty($project['district']) ? ' · ' . htmlspecialchars((string) $project['district']) : '' ?></span>
        </span>
        </label>
        <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full <?= $mediaCount > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' ?> px-2.5 py-1 text-[10px] font-semibold shrink-0">
          <i class="fa-solid fa-camera-retro text-[9px]"></i><?= $mediaCount ?> photo<?= $mediaCount === 1 ? '' : 's' ?>
        </span>
        <?php if ($isEdit): ?>
        <a href="project-media.php?slug=<?= rawurlencode($projectSlug) ?>" class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-semibold text-brandRed hover:underline shrink-0" title="Manage this project's photos">
          Manage <i class="fa-solid fa-arrow-right text-[8px]"></i>
        </a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>

      <?php if (empty($projects)): ?>
      <p class="p-6 text-center text-sm text-slate-400">No active projects found. Add projects first.</p>
      <?php endif; ?>
    </div>

    <div class="flex flex-wrap items-center gap-3 mt-3">
      <button type="button" id="selectVisible" class="text-[11px] font-semibold text-slate-500 hover:text-brandRed transition-colors">Select all visible</button>
      <span class="text-slate-200">|</span>
      <button type="button" id="clearAll" class="text-[11px] font-semibold text-slate-500 hover:text-brandRed transition-colors">Clear all</button>
      <span class="text-[11px] text-slate-400 sm:ml-auto">Tip: link the projects first, then upload photographs from each project's gallery.</span>
    </div>
  </div>
  <?php endif; ?>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <div class="flex items-center gap-2 mb-5">
      <span class="w-8 h-8 rounded-lg bg-brandRed/10 text-brandRed flex items-center justify-center"><i class="fa-solid fa-sliders text-xs"></i></span>
      <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Display Settings</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($partner['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        <p class="text-[11px] text-slate-400 mt-1">Lower numbers appear first inside a category.</p>
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($partner['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active on the public gallery</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="partners.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Save Changes' : 'Create Partner' ?>
    </button>
  </div>
</form>

<script>
(function () {
  var nameInput = document.getElementById('partnerName');
  var slugInput = document.getElementById('partnerSlug');
  var slugTouched = slugInput && slugInput.value.trim() !== '';

  function slugify(value) {
    return value.toLowerCase().trim()
      .replace(/[^a-z0-9\-]+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }

  if (nameInput && slugInput) {
    nameInput.addEventListener('input', function () {
      if (!slugTouched) slugInput.value = slugify(this.value);
    });
    slugInput.addEventListener('input', function () { slugTouched = this.value.trim() !== ''; });
  }

  var description = document.querySelector('textarea[name="description"]');
  var descriptionCount = document.getElementById('descriptionCount');
  function refreshDescriptionCount() {
    if (!description || !descriptionCount) return;
    var length = description.value.length;
    descriptionCount.textContent = length + ' / 280';
    descriptionCount.classList.toggle('text-red-400', length > 280);
    descriptionCount.classList.toggle('text-slate-300', length <= 280);
  }
  if (description) { description.addEventListener('input', refreshDescriptionCount); refreshDescriptionCount(); }

  var search = document.getElementById('projectSearch');
  var rows = Array.prototype.slice.call(document.querySelectorAll('.project-check-row'));
  var selectedCount = document.getElementById('selectedProjectCount');
  var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.project-checkbox'));

  function refreshSelected() {
    if (selectedCount) selectedCount.textContent = checkboxes.filter(function (box) { return box.checked; }).length;
  }
  checkboxes.forEach(function (box) { box.addEventListener('change', refreshSelected); });

  if (search) {
    search.addEventListener('input', function () {
      var query = this.value.toLowerCase().trim();
      rows.forEach(function (row) {
        row.style.display = (row.getAttribute('data-search') || '').indexOf(query) !== -1 ? '' : 'none';
      });
    });
  }

  var selectVisible = document.getElementById('selectVisible');
  if (selectVisible) {
    selectVisible.addEventListener('click', function () {
      rows.forEach(function (row) {
        if (row.style.display !== 'none') {
          var box = row.querySelector('.project-checkbox');
          if (box) box.checked = true;
        }
      });
      refreshSelected();
    });
  }

  var clearAll = document.getElementById('clearAll');
  if (clearAll) {
    clearAll.addEventListener('click', function () {
      checkboxes.forEach(function (box) { box.checked = false; });
      refreshSelected();
    });
  }

  refreshSelected();
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
