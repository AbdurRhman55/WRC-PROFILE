<?php
require_once __DIR__ . '/config.php';
requireLogin();

// Handle donor sub-table operations
if (isPost() && isset($_POST['add_donor'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('projects.php');
    }
    $projectId = (int) $_GET['id'];
    if (!$projectId) { redirect('projects.php'); }
    $donorName = getPost('donor_name');
    if ($donorName) {
        $pdo->prepare("INSERT INTO project_donors (project_id, donor_name, donor_type, amount) VALUES (?, ?, ?, ?)")->execute([
            $projectId, $donorName, getPost('donor_type'), getPost('amount') ?: null
        ]);
        setFlash('success', 'Donor added!');
    }
    redirect("project-edit.php?id=$projectId");
}

if (isPost() && isset($_POST['delete_donor'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('projects.php');
    }
    $donorId = (int) $_POST['donor_id'];
    $projectId = (int) $_GET['id'];
    if ($donorId) {
        $pdo->prepare("DELETE FROM project_donors WHERE id = ? AND project_id = ?")->execute([$donorId, $projectId]);
        setFlash('success', 'Donor removed.');
    }
    redirect("project-edit.php?id=$projectId");
}

$pageTitle = 'Edit Project';
$errors = [];
$project = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $project = getById('projects', (int) $_GET['id']);
    if (!$project) {
        setFlash('error', 'Project not found.');
        redirect('projects.php');
    }
    $pageTitle = 'Edit Project: ' . $project['title'];
}

$provinces = getRows('provinces', ['is_active' => 1], 'sort_order ASC');

// Taxonomy state used by the cascading selects. The POSTed values win so a
// validation error never loses the administrator's selection.
wrcEnsureProjectTaxonomy();

$organization_id = (int) ($project['organization_id'] ?? 0);
$category_id     = (int) ($project['category_id'] ?? 0);
$subcategory_id  = (int) ($project['subcategory_id'] ?? 0);

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('projects.php');
    }

    $title = getPost('title');
    $slug = getPost('slug');
    $status = getPost('status');
    $category = getPost('category');
    $organization_id = (int) getPost('organization_id');
    $category_id     = (int) getPost('category_id');
    $subcategory_id  = (int) getPost('subcategory_id');
    $province_id = getPost('province_id') ? (int) getPost('province_id') : null;
    $district = getPost('district');
    $start_date = getPost('start_date') ?: null;
    $end_date = getPost('end_date') ?: null;
    $budget_amount = getPost('budget_amount') ? (float) getPost('budget_amount') : null;
    $budget_currency = getPost('budget_currency') ?: 'PKR';
    $beneficiaries_count = (int) getPost('beneficiaries_count');
    $progress_percent = min(100, max(0, (int) getPost('progress_percent')));
    $description = getPost('description');
    $overview = getPost('overview');
    $location_details = getPost('location_details');
    $hero_image = getPost('hero_image');
    $overview_image = getPost('overview_image');
    $objectives = getPostRaw('objectives');
    $target_groups = getPostRaw('target_groups');
    $timeline = getPostRaw('timeline');
    $gallery_images = getPostRaw('gallery_images');
    $related_projects = getPostRaw('related_projects');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $show_in_history = isset($_POST['show_in_history']) ? 1 : 0;
    $sort_order = (int) getPost('sort_order');

    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($slug)) $slug = createSlug($title);
    if (!checkSlugUnique('projects', $slug, $isEdit ? (int) $_GET['id'] : 0)) $errors[] = 'A project with this slug already exists.';
    if (!in_array($status, ['active', 'completed', 'upcoming'])) $errors[] = 'Invalid status.';

    // Organisation -> Category -> Sub-category. The category decides the public
    // programme sector, so a project can never end up in a filter that does not
    // exist on the website.
    $selectedCategory    = $category_id > 0 ? wrcProjectCategoryById($category_id) : null;
    $selectedSubcategory = $subcategory_id > 0 ? wrcProjectSubcategoryById($subcategory_id) : null;

    if ($organization_id <= 0) {
        $errors[] = 'Select the organisation this project belongs to.';
    }
    if (!$selectedCategory) {
        $errors[] = 'Select a project category.';
    } elseif ((int) $selectedCategory['organization_id'] !== $organization_id) {
        $errors[] = 'The selected category does not belong to that organisation.';
    }
    if ($selectedSubcategory && (!$selectedCategory || (int) $selectedSubcategory['category_id'] !== (int) $selectedCategory['id'])) {
        $errors[] = 'The selected sub-category does not belong to that category.';
    }

    if ($selectedCategory) {
        $category = (string) $selectedCategory['sector'];
    } elseif ($category === '') {
        $category = 'General';
    }

    foreach (['objectives', 'target_groups', 'timeline', 'gallery_images', 'related_projects'] as $field) {
        $val = ${$field};
        if (!empty($val)) {
            $decoded = json_decode($val, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be valid JSON.';
            }
        }
    }

    // Clean and normalize JSON fields
    $objectives_json = null;
    if (!empty($objectives)) {
        $decObj = json_decode($objectives, true);
        if (is_array($decObj)) {
            $cleanObj = [];
            foreach ($decObj as $o) {
                if (is_array($o)) {
                    $t = trim((string)($o['title'] ?? ''));
                    $d = trim((string)($o['description'] ?? ''));
                    if ($t !== '' || $d !== '') {
                        $cleanObj[] = ['title' => $t ?: $d, 'description' => $t ? $d : ''];
                    }
                } elseif (is_string($o) && trim($o) !== '') {
                    $cleanObj[] = ['title' => trim($o), 'description' => ''];
                }
            }
            if (!empty($cleanObj)) {
                $objectives_json = json_encode($cleanObj, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    $target_groups_json = null;
    if (!empty($target_groups)) {
        $decTg = json_decode($target_groups, true);
        if (is_array($decTg)) {
            $cleanTg = [];
            foreach ($decTg as $tg) {
                if (is_array($tg)) {
                    $n = trim((string)($tg['name'] ?? ''));
                    $ic = trim((string)($tg['icon'] ?? 'fa-solid fa-users'));
                    if ($n !== '') {
                        $cleanTg[] = ['name' => $n, 'icon' => $ic ?: 'fa-solid fa-users'];
                    }
                } elseif (is_string($tg) && trim($tg) !== '') {
                    $cleanTg[] = ['name' => trim($tg), 'icon' => 'fa-solid fa-users'];
                }
            }
            if (!empty($cleanTg)) {
                $target_groups_json = json_encode($cleanTg, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    $timeline_json = null;
    if (!empty($timeline)) {
        $decTime = json_decode($timeline, true);
        if (is_array($decTime)) {
            $cleanTime = [];
            foreach ($decTime as $t) {
                if (is_array($t)) {
                    $dt = trim((string)($t['date'] ?? ''));
                    $ti = trim((string)($t['title'] ?? ''));
                    $de = trim((string)($t['description'] ?? ''));
                    $ic = trim((string)($t['icon'] ?? 'fa-solid fa-flag'));
                    if ($dt !== '' || $ti !== '' || $de !== '') {
                        $cleanTime[] = ['date' => $dt, 'title' => $ti, 'description' => $de, 'icon' => $ic ?: 'fa-solid fa-flag'];
                    }
                }
            }
            if (!empty($cleanTime)) {
                $timeline_json = json_encode($cleanTime, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    $gallery_images_json = null;
    if (!empty($gallery_images)) {
        $decGal = json_decode($gallery_images, true);
        if (is_array($decGal)) {
            $cleanGal = [];
            foreach ($decGal as $g) {
                if (is_array($g)) {
                    $p = trim((string)($g['path'] ?? ''));
                    $c = trim((string)($g['caption'] ?? ''));
                    if ($p !== '') {
                        $cleanGal[] = ['path' => $p, 'caption' => $c];
                    }
                } elseif (is_string($g) && trim($g) !== '') {
                    $cleanGal[] = ['path' => trim($g), 'caption' => ''];
                }
            }
            if (!empty($cleanGal)) {
                $gallery_images_json = json_encode($cleanGal, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    $related_projects_json = null;
    if (!empty($related_projects)) {
        $decRel = json_decode($related_projects, true);
        if (is_array($decRel) && !empty($decRel)) {
            $related_projects_json = json_encode(array_values(array_map('intval', $decRel)));
        }
    }

    if (empty($errors)) {
        global $pdo;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE projects SET slug=?, title=?, status=?, category=?, organization_id=?, category_id=?, subcategory_id=?, province_id=?, district=?, start_date=?, end_date=?, budget_amount=?, budget_currency=?, beneficiaries_count=?, progress_percent=?, description=?, overview=?, location_details=?, hero_image=?, overview_image=?, objectives=?, target_groups=?, timeline=?, gallery_images=?, related_projects=?, is_active=?, show_in_history=?, sort_order=? WHERE id=?");
            $stmt->execute([$slug, $title, $status, $category, $organization_id ?: null, $category_id ?: null, $subcategory_id ?: null, $province_id, $district, $start_date, $end_date, $budget_amount, $budget_currency, $beneficiaries_count, $progress_percent, $description, $overview, $location_details, $hero_image, $overview_image, $objectives_json, $target_groups_json, $timeline_json, $gallery_images_json, $related_projects_json, $is_active, $show_in_history, $sort_order, $project['id']]);
            setFlash('success', 'Project updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (slug, title, status, category, organization_id, category_id, subcategory_id, province_id, district, start_date, end_date, budget_amount, budget_currency, beneficiaries_count, progress_percent, description, overview, location_details, hero_image, overview_image, objectives, target_groups, timeline, gallery_images, related_projects, is_active, show_in_history, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $title, $status, $category, $organization_id ?: null, $category_id ?: null, $subcategory_id ?: null, $province_id, $district, $start_date, $end_date, $budget_amount, $budget_currency, $beneficiaries_count, $progress_percent, $description, $overview, $location_details, $hero_image, $overview_image, $objectives_json, $target_groups_json, $timeline_json, $gallery_images_json, $related_projects_json, $is_active, $show_in_history, $sort_order]);
            setFlash('success', 'Project created successfully.');
        }

        // Keep every published beneficiary figure in step with this project.
        try {
            wrcSyncBeneficiaryStats($pdo);
        } catch (Throwable $error) {
            // A stats refresh must never block a saved project.
        }

        redirect('projects.php');
    }
}

$allProjects = getRows('projects', [], 'sort_order ASC');
$allPrograms = getRows('programs', ['is_active' => 1], 'sort_order ASC');

// Taxonomy data for the cascading selects and the quick-add dialogs.
$taxonomyTree      = wrcProjectTaxonomyTree(false);
$sectorOptions     = wrcProjectSectorOptions();
$formCategories    = $organization_id > 0 ? wrcProjectCategories($organization_id, false) : [];
$formSubcategories = $category_id > 0 ? wrcProjectSubcategories($category_id, false) : [];

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Project' : 'Add New Project' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update project details.' : 'Create a new project.' ?></p>
  </div>
  <a href="projects.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Projects
  </a>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
  <div class="flex items-center gap-2 mb-1"><i class="fa-solid fa-circle-exclamation"></i><span class="font-semibold text-sm">Please fix the following errors:</span></div>
  <ul class="list-disc list-inside text-sm mt-1">
    <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="POST" id="projectForm" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <!-- Basic Info -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Basic Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($project['title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" id="titleInput">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($project['slug'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed font-mono" id="slugInput">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status *</label>
        <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <option value="active" <?= ($project['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="completed" <?= ($project['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
          <option value="upcoming" <?= ($project['status'] ?? '') === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Province</label>
        <select name="province_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <option value="">-- None --</option>
          <?php foreach ($provinces as $prov): ?>
          <option value="<?= $prov['id'] ?>" <?= ($project['province_id'] ?? '') == $prov['id'] ? 'selected' : '' ?>><?= htmlspecialchars($prov['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">District</label>
        <input type="text" name="district" value="<?= htmlspecialchars($project['district'] ?? '') ?>" placeholder="e.g. Peshawar" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Location Details</label>
        <input type="text" name="location_details" value="<?= htmlspecialchars($project['location_details'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <!-- Organisation & Classification -->
  <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-start gap-3">
        <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-brandRed ring-1 ring-slate-200"><i class="fa-solid fa-sitemap text-xs"></i></span>
        <div>
          <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Organisation &amp; Categories</h2>
          <p class="mt-0.5 text-xs text-slate-500">Pick the organisation first — its own categories and sub-categories load next.</p>
        </div>
      </div>
      <a href="project-categories.php" class="inline-flex items-center gap-1.5 self-start rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-600 transition-colors hover:border-brandRed/40 hover:text-brandRed sm:self-auto">
        <i class="fa-solid fa-sliders text-[10px]"></i> Manage categories
      </a>
    </div>

    <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-3">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Organisation *</label>
        <select name="organization_id" id="organizationSelect" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <option value="">-- Select organisation --</option>
          <?php foreach ($taxonomyTree as $orgNode): ?>
          <option value="<?= (int) $orgNode['id'] ?>" <?= $organization_id === (int) $orgNode['id'] ? 'selected' : '' ?>><?= htmlspecialchars($orgNode['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="mt-1.5 text-[11px] text-slate-400">Donor or implementing partner.</p>
      </div>

      <div>
        <div class="mb-1 flex items-center justify-between gap-2">
          <label class="block text-sm font-medium text-slate-700">Category *</label>
          <button type="button" id="quickAddCategory" class="inline-flex items-center gap-1 text-[11px] font-semibold text-brandRed transition-colors hover:underline">
            <i class="fa-solid fa-plus text-[9px]"></i> New
          </button>
        </div>
        <select name="category_id" id="categorySelect" required <?= $organization_id > 0 ? '' : 'disabled' ?> class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed disabled:bg-slate-50 disabled:text-slate-400">
          <option value=""><?= $organization_id > 0 ? '-- Select category --' : '-- Select organisation first --' ?></option>
          <?php foreach ($formCategories as $formCategory): ?>
          <option value="<?= (int) $formCategory['id'] ?>" <?= $category_id === (int) $formCategory['id'] ? 'selected' : '' ?>><?= htmlspecialchars($formCategory['name']) ?><?= (int) $formCategory['is_active'] ? '' : ' (inactive)' ?></option>
          <?php endforeach; ?>
        </select>
        <p class="mt-1.5 text-[11px] text-slate-400">Only this organisation's categories.</p>
      </div>

      <div>
        <div class="mb-1 flex items-center justify-between gap-2">
          <label class="block text-sm font-medium text-slate-700">Sub-category</label>
          <button type="button" id="quickAddSubcategory" <?= $category_id > 0 ? '' : 'disabled' ?> class="inline-flex items-center gap-1 text-[11px] font-semibold text-brandRed transition-colors hover:underline disabled:cursor-not-allowed disabled:text-slate-300">
            <i class="fa-solid fa-plus text-[9px]"></i> New
          </button>
        </div>
        <select name="subcategory_id" id="subcategorySelect" <?= $category_id > 0 ? '' : 'disabled' ?> class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed disabled:bg-slate-50 disabled:text-slate-400">
          <option value="">-- Optional --</option>
          <?php foreach ($formSubcategories as $formSubcategory): ?>
          <option value="<?= (int) $formSubcategory['id'] ?>" <?= $subcategory_id === (int) $formSubcategory['id'] ? 'selected' : '' ?>><?= htmlspecialchars($formSubcategory['name']) ?><?= (int) $formSubcategory['is_active'] ? '' : ' (inactive)' ?></option>
          <?php endforeach; ?>
        </select>
        <p class="mt-1.5 text-[11px] text-slate-400">Optional detail inside the category.</p>
      </div>
    </div>

    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-3 text-[11px] sm:flex-row sm:items-center">
      <span class="inline-flex items-center gap-1.5 text-slate-500">
        <i class="fa-solid fa-bullseye text-[10px] text-slate-400"></i>
        Public programme sector:
        <span id="sectorBadge" class="rounded-full bg-white px-2 py-0.5 font-bold text-slate-700 ring-1 ring-slate-200"><?= htmlspecialchars($project['category'] ?? '—') ?></span>
      </span>
      <span class="text-slate-400 sm:ml-auto">Drives the website's project filters and sector counters.</span>
    </div>
  </div>

  <!-- Dates & Budget -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Dates & Budget</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($project['start_date'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($project['end_date'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Budget Amount</label>
        <input type="number" name="budget_amount" value="<?= htmlspecialchars($project['budget_amount'] ?? '') ?>" step="0.01" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Budget Currency</label>
        <input type="text" name="budget_currency" value="<?= htmlspecialchars($project['budget_currency'] ?? 'PKR') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Beneficiaries Count</label>
        <input type="number" name="beneficiaries_count" value="<?= (int) ($project['beneficiaries_count'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Progress %</label>
        <input type="number" name="progress_percent" value="<?= (int) ($project['progress_percent'] ?? 0) ?>" min="0" max="100" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <!-- Descriptions -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Descriptions</h2>
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <div class="mb-1.5 flex flex-wrap items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1.5" data-rich-toolbar="projectDescriptionInput" role="toolbar" aria-label="Description formatting">
          <button type="button" data-wrap="<strong>" data-unwrap="</strong>" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Bold"><i class="fa-solid fa-bold"></i></button>
          <button type="button" data-wrap="<em>" data-unwrap="</em>" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Italic"><i class="fa-solid fa-italic"></i></button>
          <button type="button" data-wrap="<u>" data-unwrap="</u>" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Underline"><i class="fa-solid fa-underline"></i></button>
          <span class="mx-1 h-4 w-px bg-slate-200"></span>
          <button type="button" data-wrap='<span style="font-size:1.5em">' data-unwrap="</span>" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Larger text">A+</button>
          <button type="button" data-wrap='<span style="font-size:1.2em">' data-unwrap="</span>" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Large text">A</button>
          <button type="button" data-wrap='<span style="font-size:0.8em">' data-unwrap="</span>" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Smaller text">A&minus;</button>
        </div>
        <textarea id="projectDescriptionInput" name="description" rows="4" placeholder="Short summary shown on the project page. Use the toolbar for bold, italic, underline or text size." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Overview</label>
        <div class="mb-1.5 flex flex-wrap items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1.5" data-rich-toolbar="projectOverviewInput" role="toolbar" aria-label="Overview formatting">
          <button type="button" data-wrap="<strong>" data-unwrap="</strong>" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Bold"><i class="fa-solid fa-bold"></i></button>
          <button type="button" data-wrap="<em>" data-unwrap="</em>" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Italic"><i class="fa-solid fa-italic"></i></button>
          <button type="button" data-wrap="<u>" data-unwrap="</u>" class="rounded-lg px-2.5 py-1.5 text-xs text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Underline"><i class="fa-solid fa-underline"></i></button>
          <span class="mx-1 h-4 w-px bg-slate-200"></span>
          <button type="button" data-wrap='<span style="font-size:1.5em">' data-unwrap="</span>" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Larger text">A+</button>
          <button type="button" data-wrap='<span style="font-size:1.2em">' data-unwrap="</span>" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Large text">A</button>
          <button type="button" data-wrap='<span style="font-size:0.8em">' data-unwrap="</span>" class="rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brandRed transition-colors" title="Smaller text">A&minus;</button>
        </div>
        <textarea id="projectOverviewInput" name="overview" rows="5" placeholder="Full project overview shown under About This Project. Use the toolbar for bold, italic, underline or text size." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($project['overview'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Images -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Images</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php uploadWidget('hero_image', 'Hero Image', $project['hero_image'] ?? '', 'image', 'project_hero_img'); ?>
      <?php uploadWidget('overview_image', 'Overview Image', $project['overview_image'] ?? '', 'image', 'project_overview_img'); ?>
    </div>
  </div>

  <!-- Friendly Data Fields -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Project Details</h2>
    <div class="space-y-6">

      <!-- Objectives (Dynamic Rows) -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <div>
            <label class="text-sm font-semibold text-slate-900">Project Objectives</label>
            <p class="text-xs text-slate-500">Key targets and aims of this project (shown on the project detail page).</p>
          </div>
          <button type="button" onclick="addObjective()" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-red-50 text-brandRed hover:bg-brandRed hover:text-white px-3 py-1.5 rounded-lg transition-colors">
            <i class="fa-solid fa-plus text-[10px]"></i> Add Objective
          </button>
        </div>
        <input type="hidden" name="objectives" id="objectivesInput" value="<?= htmlspecialchars($project['objectives'] ?? '[]') ?>">
        <div id="objectivesList" class="space-y-3"></div>
      </div>

      <!-- Target Groups (Chip with Icon) -->
      <div>
        <label class="text-sm font-semibold text-slate-900 mb-1 block">Target Groups (Who Benefits)</label>
        <p class="text-xs text-slate-500 mb-2">Type group name and optional icon (e.g. <code>fa-solid fa-users</code>), then click Add.</p>
        <input type="hidden" name="target_groups" id="targetGroupsInput" value="<?= htmlspecialchars($project['target_groups'] ?? '[]') ?>">
        <div class="chip-input-wrap rounded-xl border border-slate-200 focus-within:ring-2 focus-within:ring-brandRed/20 focus-within:border-brandRed transition-all p-2.5 bg-slate-50/50" id="targetGroupsWrap">
          <div class="flex flex-wrap gap-1.5 mb-2" id="targetGroupsChips"></div>
          <div class="flex items-center gap-2">
            <input type="text" id="tgNameField" placeholder="Group name (e.g. Women & Children)" class="flex-1 min-w-[140px] px-3 py-1.5 text-sm outline-none bg-white border border-slate-200 rounded-lg">
            <input type="text" id="tgIconField" placeholder="Icon (fa-solid fa-users)" class="w-48 px-3 py-1.5 text-xs outline-none bg-white border border-slate-200 rounded-lg">
            <button type="button" onclick="addTargetGroup()" class="text-xs bg-slate-200 hover:bg-brandRed hover:text-white px-3 py-1.5 rounded-lg transition-colors font-medium">Add</button>
          </div>
        </div>
      </div>

      <!-- Timeline (Dynamic Rows) -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <div>
            <label class="text-sm font-semibold text-slate-900">Project Timeline & Milestones</label>
            <p class="text-xs text-slate-500">Chronological project journey and milestones.</p>
          </div>
          <button type="button" onclick="addTimeline()" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-red-50 text-brandRed hover:bg-brandRed hover:text-white px-3 py-1.5 rounded-lg transition-colors">
            <i class="fa-solid fa-plus text-[10px]"></i> Add Milestone
          </button>
        </div>
        <input type="hidden" name="timeline" id="timelineInput" value="<?= htmlspecialchars($project['timeline'] ?? '[]') ?>">
        <div id="timelineList" class="space-y-3"></div>
      </div>

      <!-- Gallery Images / Visual Documentation -->
      <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
          <div>
            <label class="text-sm font-semibold text-slate-900">Project Visualization / Gallery Images</label>
            <p class="text-xs text-slate-500">Visual documentation photos shown on the project page.</p>
          </div>
          <div class="flex items-center gap-2">
            <?php if ($isEdit && !empty($project['slug'])): ?>
            <a href="project-media.php?slug=<?= rawurlencode($project['slug']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-brandRed px-2.5 py-1 rounded-lg border border-slate-200 hover:border-brandRed/40 transition-colors">
              <i class="fa-solid fa-images text-[10px]"></i> Full Media Manager
            </a>
            <?php endif; ?>
            <button type="button" onclick="addGallery()" class="inline-flex items-center gap-1.5 text-xs font-semibold bg-red-50 text-brandRed hover:bg-brandRed hover:text-white px-3 py-1.5 rounded-lg transition-colors">
              <i class="fa-solid fa-plus text-[10px]"></i> Add Image
            </button>
          </div>
        </div>
        <input type="hidden" name="gallery_images" id="galleryInput" value="<?= htmlspecialchars($project['gallery_images'] ?? '[]') ?>">
        <div id="galleryList" class="space-y-3"></div>
      </div>

      <!-- Related Projects -->
      <div>
        <label class="text-sm font-medium text-slate-700 mb-2 block">Related Projects</label>
        <input type="hidden" name="related_projects" id="relatedProjectsInput" value="<?= htmlspecialchars($project['related_projects'] ?? '[]') ?>">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2" id="relatedProjectsList">
          <?php
          $relatedPIds = json_decode($project['related_projects'] ?? '[]', true) ?: [];
          foreach ($allProjects as $rp):
            if ($isEdit && $rp['id'] == $project['id']) continue;
          ?>
          <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors rp-item <?= in_array($rp['id'], $relatedPIds) ? 'bg-blue-50 border-blue-300' : '' ?>" data-id="<?= $rp['id'] ?>">
            <input type="checkbox" class="rp-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500/20" <?= in_array($rp['id'], $relatedPIds) ? 'checked' : '' ?>>
            <span class="text-sm text-slate-700 truncate"><?= htmlspecialchars($rp['title']) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($project['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($project['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="show_in_history" value="1" <?= ($project['show_in_history'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Show on History Page</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="projects.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Project' : 'Create Project' ?>
    </button>
  </div>
</form>

<!-- Quick-add taxonomy modal -->
<div id="taxonomyModal" class="fixed inset-0 z-[120] hidden">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-taxonomy-close></div>
  <div class="relative mx-auto mt-24 w-[92%] max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
    <div class="flex items-start gap-3 border-b border-slate-100 px-5 py-4">
      <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-red-50 text-brandRed">
        <i id="taxonomyModalIcon" class="fa-solid fa-folder-plus text-xs"></i>
      </span>
      <div class="min-w-0">
        <h3 id="taxonomyModalTitle" class="text-sm font-bold text-slate-900">Add category</h3>
        <p id="taxonomyModalHint" class="text-[11px] text-slate-500">Created inside the selected organisation.</p>
      </div>
      <button type="button" class="ml-auto rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-50 hover:text-slate-700" data-taxonomy-close>
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <div class="space-y-3 px-5 py-4">
      <div id="taxonomyCategoryFields" class="space-y-3">
        <div>
          <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Organisation</label>
          <select id="modalOrganization" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
            <option value="">-- Select organisation --</option>
            <?php foreach ($taxonomyTree as $orgNode): ?>
            <option value="<?= (int) $orgNode['id'] ?>"><?= htmlspecialchars($orgNode['name']) ?></option>
            <?php endforeach; ?>
            <option value="__new">+ New organisation…</option>
          </select>
        </div>
        <div id="modalNewOrgWrap" class="hidden">
          <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">New organisation name</label>
          <input type="text" id="modalNewOrg" placeholder="e.g. UNICEF" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
        </div>
        <div>
          <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Category name</label>
          <input type="text" id="modalCategoryName" placeholder="e.g. Ramadan Food Packs" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
        </div>
        <div>
          <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Programme sector</label>
          <select id="modalSector" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
            <?php foreach ($sectorOptions as $sectorOption): ?>
            <option value="<?= htmlspecialchars($sectorOption) ?>"><?= htmlspecialchars($sectorOption) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="mt-1 text-[10px] text-slate-400">Shown on the website as this project's sector.</p>
        </div>
      </div>

      <div id="taxonomySubFields" class="hidden space-y-3">
        <div class="rounded-xl bg-slate-50 px-3 py-2 text-[12px] text-slate-600">
          <span class="text-slate-400">Under</span>
          <span id="modalCategoryPath" class="font-semibold text-slate-800">—</span>
        </div>
        <div>
          <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sub-category name</label>
          <input type="text" id="modalSubName" placeholder="e.g. Winter Package" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brandRed focus:outline-none focus:ring-2 focus:ring-brandRed/20">
        </div>
      </div>

      <p id="taxonomyModalError" class="hidden rounded-lg bg-red-50 px-3 py-2 text-[12px] font-medium text-red-600"></p>
    </div>

    <div class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3">
      <button type="button" data-taxonomy-close class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[12px] font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
      <button type="button" id="taxonomyModalSave" class="rounded-lg bg-brandRed px-4 py-2 text-[12px] font-semibold text-white transition-colors hover:bg-brandRed-hover disabled:opacity-60">
        <i class="fa-solid fa-check mr-1 text-[10px]"></i> Save
      </button>
    </div>
  </div>
</div>

<style>
.dynamic-row{display:flex;gap:10px;align-items:flex-start;padding:12px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;position:relative;transition:all .15s}
.dynamic-row:hover{background:#f1f5f9;border-color:#cbd5e1}
.dynamic-row input,.dynamic-row textarea{padding:7px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;outline:none;background:#fff}
.dynamic-row input:focus,.dynamic-row textarea:focus{border-color:#ca0000;box-shadow:0 0 0 2px rgba(202,0,0,0.15)}
.dynamic-row .dr-remove{width:26px;height:26px;border-radius:50%;background:#fee2e2;color:#991b1b;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;shrink:0}
.dynamic-row .dr-remove:hover{background:#fecaca}
.chip-input-wrap{display:flex;flex-wrap:wrap;gap:6px;min-height:42px;align-items:center}
.chip-tag{display:inline-flex;align-items:center;gap:6px;background:#dbeafe;color:#1e40af;padding:5px 12px;border-radius:9999px;font-size:12px;font-weight:600;line-height:1}
.chip-tag button{background:none;border:none;color:#1e40af;cursor:pointer;padding:0;font-size:14px;line-height:1;opacity:.7}
.chip-tag button:hover{opacity:1}
.gallery-thumb-box{width:64px;height:64px;border-radius:8px;border:1px solid #cbd5e1;overflow:hidden;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.gallery-thumb-box img{width:100%;height:100%;object-fit:cover}
.rp-item{transition:all .15s}
</style>

<script>
function safeStr(val) {
  if (val === null || val === undefined) return '';
  return String(val);
}

function escapeHtmlAttr(str) {
  return safeStr(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// === Rich text toolbars (Description / Overview) ===
// Each toolbar wraps the current selection in the requested tag so bold,
// italic, underline and text-size edits are stored as HTML and render
// formatted on the project detail page.
document.querySelectorAll('[data-rich-toolbar]').forEach(function (toolbar) {
  var input = document.getElementById(toolbar.getAttribute('data-rich-toolbar'));
  if (!input) return;
  toolbar.querySelectorAll('[data-wrap]').forEach(function (button) {
    button.addEventListener('click', function () {
      var before = button.getAttribute('data-wrap');
      var after = button.getAttribute('data-unwrap') || '';
      var start = input.selectionStart;
      var end = input.selectionEnd;
      var selected = input.value.slice(start, end);
      input.setRangeText(before + selected + after, start, end, 'end');
      input.focus();
      input.setSelectionRange(start + before.length, start + before.length + selected.length);
    });
  });
});

// === Objectives ===
var objectives = [];
try {
  var rawObj = JSON.parse(document.getElementById('objectivesInput').value || '[]');
  if (Array.isArray(rawObj)) {
    objectives = rawObj.map(function(item) {
      if (typeof item === 'string') return { title: item, description: '' };
      return { title: safeStr(item.title), description: safeStr(item.description) };
    });
  }
} catch(e) { objectives = []; }

function syncObjectives() {
  var rows = document.querySelectorAll('#objectivesList .dynamic-row');
  var list = [];
  rows.forEach(function(r) {
    var title = safeStr(r.querySelector('.obj-title')?.value).trim();
    var desc = safeStr(r.querySelector('.obj-desc')?.value).trim();
    if (title || desc) {
      list.push({ title: title, description: desc });
    }
  });
  document.getElementById('objectivesInput').value = JSON.stringify(list);
  objectives = list;
}

function renderObjectives() {
  var el = document.getElementById('objectivesList');
  el.innerHTML = '';
  if (!objectives.length) {
    el.innerHTML = '<p class="text-xs text-slate-400 italic py-2">No objectives added yet. Click &ldquo;Add Objective&rdquo; above.</p>';
  } else {
    objectives.forEach(function(item, i) {
      var num = (i + 1 < 10 ? '0' : '') + (i + 1);
      el.innerHTML += '<div class="dynamic-row" data-index="' + i + '">' +
        '<span class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold shrink-0 mt-1">' + num + '</span>' +
        '<div class="flex-1 space-y-2">' +
          '<input type="text" class="obj-title w-full font-medium" placeholder="Objective Title (e.g. Clean Drinking Water Access)" value="' + escapeHtmlAttr(item.title) + '" oninput="syncObjectives()">' +
          '<textarea rows="2" class="obj-desc w-full text-slate-600" placeholder="Objective Description & Impact Details..." oninput="syncObjectives()">' + escapeHtmlAttr(item.description) + '</textarea>' +
        '</div>' +
        '<button type="button" class="dr-remove" title="Remove Objective" onclick="objectives.splice(' + i + ',1);renderObjectives();syncObjectives();"><i class="fa-solid fa-xmark"></i></button>' +
      '</div>';
    });
  }
  document.getElementById('objectivesInput').value = JSON.stringify(objectives);
}

function addObjective() {
  syncObjectives();
  objectives.push({ title: '', description: '' });
  renderObjectives();
  setTimeout(function() {
    var rows = document.querySelectorAll('#objectivesList .dynamic-row');
    if (rows.length) {
      var last = rows[rows.length - 1].querySelector('.obj-title');
      if (last) last.focus();
    }
  }, 50);
}
renderObjectives();

// === Target Groups ===
var tgItems = [];
try {
  var rawTg = JSON.parse(document.getElementById('targetGroupsInput').value || '[]');
  if (Array.isArray(rawTg)) {
    tgItems = rawTg.map(function(item) {
      if (typeof item === 'string') return { name: item, icon: 'fa-solid fa-users' };
      return { name: safeStr(item.name), icon: safeStr(item.icon) || 'fa-solid fa-users' };
    });
  }
} catch(e) { tgItems = []; }

function syncTG() {
  document.getElementById('targetGroupsInput').value = JSON.stringify(tgItems);
}

function renderTG() {
  var el = document.getElementById('targetGroupsChips');
  el.innerHTML = '';
  tgItems.forEach(function(item, i) {
    el.innerHTML += '<span class="chip-tag">' +
      '<i class="' + escapeHtmlAttr(item.icon || 'fa-solid fa-users') + '" style="font-size:11px"></i> ' +
      '<span>' + escapeHtmlAttr(item.name) + '</span> ' +
      '<button type="button" onclick="tgItems.splice(' + i + ',1);renderTG();syncTG();">&times;</button>' +
    '</span>';
  });
  syncTG();
}

function addTargetGroup() {
  var name = document.getElementById('tgNameField').value.trim();
  var icon = document.getElementById('tgIconField').value.trim() || 'fa-solid fa-users';
  if (name) {
    tgItems.push({ name: name, icon: icon });
    document.getElementById('tgNameField').value = '';
    document.getElementById('tgIconField').value = '';
    renderTG();
    syncTG();
  }
}

document.getElementById('tgNameField').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') { e.preventDefault(); addTargetGroup(); }
});
renderTG();

// === Timeline ===
var timeline = [];
try {
  var rawTime = JSON.parse(document.getElementById('timelineInput').value || '[]');
  if (Array.isArray(rawTime)) {
    timeline = rawTime.map(function(item) {
      return {
        date: safeStr(item.date),
        title: safeStr(item.title),
        description: safeStr(item.description),
        icon: safeStr(item.icon) || 'fa-solid fa-flag'
      };
    });
  }
} catch(e) { timeline = []; }

function syncTimeline() {
  var rows = document.querySelectorAll('#timelineList .dynamic-row');
  var list = [];
  rows.forEach(function(r) {
    var date = safeStr(r.querySelector('.timeline-date')?.value).trim();
    var title = safeStr(r.querySelector('.timeline-title')?.value).trim();
    var desc = safeStr(r.querySelector('.timeline-desc')?.value).trim();
    if (date || title || desc) {
      list.push({ date: date, title: title, description: desc, icon: 'fa-solid fa-flag' });
    }
  });
  document.getElementById('timelineInput').value = JSON.stringify(list);
  timeline = list;
}

function renderTimeline() {
  var el = document.getElementById('timelineList');
  el.innerHTML = '';
  if (!timeline.length) {
    el.innerHTML = '<p class="text-xs text-slate-400 italic py-2">No timeline milestones added. Click &ldquo;Add Milestone&rdquo; above.</p>';
  } else {
    timeline.forEach(function(item, i) {
      el.innerHTML += '<div class="dynamic-row" data-index="' + i + '">' +
        '<input type="text" class="timeline-date font-medium" placeholder="e.g. Q1 2025" value="' + escapeHtmlAttr(item.date) + '" oninput="syncTimeline()" style="max-width:130px">' +
        '<div class="flex-1 space-y-2">' +
          '<input type="text" class="timeline-title w-full font-medium" placeholder="Milestone / Phase Title" value="' + escapeHtmlAttr(item.title) + '" oninput="syncTimeline()">' +
          '<textarea rows="1" class="timeline-desc w-full text-slate-600" placeholder="Phase description..." oninput="syncTimeline()">' + escapeHtmlAttr(item.description) + '</textarea>' +
        '</div>' +
        '<button type="button" class="dr-remove" title="Remove" onclick="timeline.splice(' + i + ',1);renderTimeline();syncTimeline();"><i class="fa-solid fa-xmark"></i></button>' +
      '</div>';
    });
  }
  document.getElementById('timelineInput').value = JSON.stringify(timeline);
}

function addTimeline() {
  syncTimeline();
  timeline.push({ date: '', title: '', description: '', icon: 'fa-solid fa-flag' });
  renderTimeline();
  setTimeout(function() {
    var rows = document.querySelectorAll('#timelineList .dynamic-row');
    if (rows.length) {
      var last = rows[rows.length - 1].querySelector('.timeline-title');
      if (last) last.focus();
    }
  }, 50);
}
renderTimeline();

// === Gallery / Visual Documentation ===
var gallery = [];
try {
  var rawGal = JSON.parse(document.getElementById('galleryInput').value || '[]');
  if (Array.isArray(rawGal)) {
    gallery = rawGal.map(function(item) {
      if (typeof item === 'string') return { path: item, caption: '' };
      return { path: safeStr(item.path), caption: safeStr(item.caption) };
    });
  }
} catch(e) { gallery = []; }

function syncGallery() {
  var rows = document.querySelectorAll('#galleryList .dynamic-row');
  var list = [];
  rows.forEach(function(r) {
    var path = safeStr(r.querySelector('.gallery-path')?.value).trim();
    var caption = safeStr(r.querySelector('.gallery-caption')?.value).trim();
    if (path) {
      list.push({ path: path, caption: caption });
    }
  });
  document.getElementById('galleryInput').value = JSON.stringify(list);
  gallery = list;
}

function updateGalleryThumb(index, path) {
  var row = document.querySelector('#galleryList .dynamic-row[data-index="' + index + '"]');
  if (!row) return;
  var thumbBox = row.querySelector('.gallery-thumb-box');
  if (!thumbBox) return;
  if (path && path.trim()) {
    var p = path.trim();
    var src = /^(https?:)?\/\//i.test(p) ? p : (window.location.origin + '/' + p.replace(/^\/+/, ''));
    thumbBox.innerHTML = '<img src="' + escapeHtmlAttr(src) + '" alt="Thumbnail" onerror="this.parentElement.innerHTML=\'<i class=\\\'fa-solid fa-image text-slate-400\\\'></i>\'">';
  } else {
    thumbBox.innerHTML = '<i class="fa-solid fa-image text-slate-400"></i>';
  }
}

function uploadGalleryFile(file, index) {
  if (!file) return;
  var row = document.querySelector('#galleryList .dynamic-row[data-index="' + index + '"]');
  var thumbBox = row ? row.querySelector('.gallery-thumb-box') : null;
  if (thumbBox) thumbBox.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-brandRed"></i>';

  var fd = new FormData();
  fd.append('file', file);
  fd.append('type', 'image');

  fetch('ajax-upload.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success && res.path) {
        if (gallery[index]) gallery[index].path = res.path;
        if (row) {
          var input = row.querySelector('.gallery-path');
          if (input) input.value = res.path;
        }
        updateGalleryThumb(index, res.path);
        syncGallery();
      } else {
        alert(res.message || 'Image upload failed.');
        updateGalleryThumb(index, gallery[index] ? gallery[index].path : '');
      }
    })
    .catch(function() {
      alert('Upload failed. Check network or server configuration.');
      updateGalleryThumb(index, gallery[index] ? gallery[index].path : '');
    });
}

function renderGallery() {
  var el = document.getElementById('galleryList');
  el.innerHTML = '';
  if (!gallery.length) {
    el.innerHTML = '<p class="text-xs text-slate-400 italic py-2">No visual documentation images yet. Click &ldquo;Add Image&rdquo; above.</p>';
  } else {
    gallery.forEach(function(item, i) {
      var p = safeStr(item.path).trim();
      var thumbHtml = '<i class="fa-solid fa-image text-slate-400"></i>';
      if (p) {
        var src = /^(https?:)?\/\//i.test(p) ? p : (window.location.origin + '/' + p.replace(/^\/+/, ''));
        thumbHtml = '<img src="' + escapeHtmlAttr(src) + '" alt="Thumbnail" onerror="this.parentElement.innerHTML=\'<i class=\\\'fa-solid fa-image text-slate-400\\\'></i>\'">';
      }

      el.innerHTML += '<div class="dynamic-row" data-index="' + i + '">' +
        '<div class="gallery-thumb-box">' + thumbHtml + '</div>' +
        '<div class="flex-1 space-y-2">' +
          '<div class="flex items-center gap-2">' +
            '<input type="text" class="gallery-path flex-1 text-xs font-mono" placeholder="Image path (e.g. uploads/images/project.jpg)" value="' + escapeHtmlAttr(item.path) + '" oninput="updateGalleryThumb(' + i + ', this.value); syncGallery();">' +
            '<label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-brandRed hover:text-white text-xs font-medium cursor-pointer transition-colors shrink-0">' +
              '<i class="fa-solid fa-cloud-arrow-up text-[10px]"></i> Upload' +
              '<input type="file" accept="image/*" class="hidden" onchange="uploadGalleryFile(this.files[0], ' + i + ')">' +
            '</label>' +
          '</div>' +
          '<input type="text" class="gallery-caption w-full text-xs" placeholder="Caption / description of this photo" value="' + escapeHtmlAttr(item.caption) + '" oninput="syncGallery()">' +
        '</div>' +
        '<button type="button" class="dr-remove" title="Remove" onclick="gallery.splice(' + i + ',1);renderGallery();syncGallery();"><i class="fa-solid fa-xmark"></i></button>' +
      '</div>';
    });
  }
  document.getElementById('galleryInput').value = JSON.stringify(gallery);
}

function addGallery() {
  syncGallery();
  gallery.push({ path: '', caption: '' });
  renderGallery();
}
renderGallery();

// === Related Projects Checkboxes ===
(function() {
  var hidden = document.getElementById('relatedProjectsInput');
  var items = [];
  try { items = JSON.parse(hidden.value || '[]'); if (!Array.isArray(items)) items = []; } catch(e) { items = []; }

  document.querySelectorAll('.rp-item').forEach(function(label) {
    var cb = label.querySelector('.rp-checkbox');
    var id = parseInt(label.dataset.id);

    if (items.indexOf(id) !== -1) { label.classList.add('bg-blue-50','border-blue-300'); }
    else { label.classList.remove('bg-blue-50','border-blue-300'); }

    cb.addEventListener('change', function() {
      if (cb.checked) {
        if (items.indexOf(id) === -1) items.push(id);
        label.classList.add('bg-blue-50','border-blue-300');
      } else {
        items = items.filter(function(v){ return v !== id; });
        label.classList.remove('bg-blue-50','border-blue-300');
      }
      hidden.value = JSON.stringify(items);
    });
  });
})();

// === Form Submission Final Sync ===
var pForm = document.getElementById('projectForm');
if (pForm) {
  pForm.addEventListener('submit', function() {
    syncObjectives();
    syncTimeline();
    syncGallery();
    syncTG();
  });
}
</script>

<?php if ($isEdit): ?>
<?php $donors = getRows('project_donors', ['project_id' => $project['id']], '', 0); ?>

<!-- Project Donors -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 mt-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Donors / Partners (<?= count($donors) ?>)</h2>
    <button onclick="document.getElementById('add-donor-form').classList.toggle('hidden')" class="inline-flex items-center gap-1 bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-amber-700 transition-colors">
      <i class="fa-solid fa-plus text-xs"></i> Add Donor
    </button>
  </div>

  <?php if (!empty($donors)): ?>
  <div class="space-y-2 mb-4">
    <?php foreach ($donors as $d): ?>
    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fa-solid fa-hand-holding-dollar text-amber-500 text-xs"></i></div>
        <div>
          <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($d['donor_name']) ?></p>
          <div class="flex items-center gap-2 mt-0.5">
            <?php if ($d['donor_type']): ?><span class="text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-200 text-slate-600"><?= htmlspecialchars($d['donor_type']) ?></span><?php endif; ?>
            <?php if ($d['amount']): ?><span class="text-xs font-bold text-green-700">$<?= number_format($d['amount'], 2) ?></span><?php endif; ?>
          </div>
        </div>
      </div>
      <form method="POST" onsubmit="return confirm('Remove this donor?')">
        <input type="hidden" name="delete_donor" value="1">
        <input type="hidden" name="donor_id" value="<?= $d['id'] ?>">
        <button class="px-2 py-1 rounded-lg bg-red-50 text-red-500 text-xs hover:bg-red-100 transition-colors"><i class="fa-solid fa-trash text-xs"></i></button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form id="add-donor-form" method="POST" class="hidden border-t border-slate-100 pt-4 mt-2">
    <input type="hidden" name="add_donor" value="1">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <div class="md:col-span-2">
        <input type="text" name="donor_name" placeholder="Donor / Partner name *" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
      </div>
      <div>
        <input type="text" name="donor_type" placeholder="Type (e.g. UN, NGO)" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
      </div>
      <div>
        <input type="number" name="amount" step="0.01" placeholder="Amount (USD)" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
      </div>
    </div>
    <button type="submit" class="mt-2 inline-flex items-center gap-1 bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-amber-700 transition-colors"><i class="fa-solid fa-plus text-xs"></i> Save Donor</button>
  </form>
</div>
<?php endif; ?>

<script>
document.getElementById('titleInput')?.addEventListener('input', function() {
  const slugInput = document.getElementById('slugInput');
  if (slugInput && !slugInput.dataset.manual) {
    slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  }
});
document.getElementById('slugInput')?.addEventListener('input', function() {
  this.dataset.manual = '1';
});
</script>

<script>
// === Organisation -> Category -> Sub-category cascade + quick-add ===
(function () {
  var taxonomy = <?= json_encode($taxonomyTree, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  var csrfToken = <?= json_encode(generateCSRF()) ?>;
  var endpoint = 'ajax-taxonomy.php';

  var orgSelect = document.getElementById('organizationSelect');
  var catSelect = document.getElementById('categorySelect');
  var subSelect = document.getElementById('subcategorySelect');
  var sectorBadge = document.getElementById('sectorBadge');
  var btnAddCat = document.getElementById('quickAddCategory');
  var btnAddSub = document.getElementById('quickAddSubcategory');

  if (!orgSelect || !catSelect || !subSelect) return;

  var modal = document.getElementById('taxonomyModal');
  var modalTitle = document.getElementById('taxonomyModalTitle');
  var modalHint = document.getElementById('taxonomyModalHint');
  var modalIcon = document.getElementById('taxonomyModalIcon');
  var modalOrg = document.getElementById('modalOrganization');
  var modalNewOrgWrap = document.getElementById('modalNewOrgWrap');
  var modalNewOrg = document.getElementById('modalNewOrg');
  var modalCatFields = document.getElementById('taxonomyCategoryFields');
  var modalSubFields = document.getElementById('taxonomySubFields');
  var modalCatName = document.getElementById('modalCategoryName');
  var modalSector = document.getElementById('modalSector');
  var modalSubName = document.getElementById('modalSubName');
  var modalPath = document.getElementById('modalCategoryPath');
  var modalError = document.getElementById('taxonomyModalError');
  var modalSave = document.getElementById('taxonomyModalSave');
  var modalMode = 'category';

  var disabledSelectClass = 'disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed';
  var baseSelectClass = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed ';

  function orgById(id) {
    id = parseInt(id, 10) || 0;
    for (var i = 0; i < taxonomy.length; i++) {
      if (parseInt(taxonomy[i].id, 10) === id) return taxonomy[i];
    }
    return null;
  }

  function categoryById(orgId, catId) {
    var org = orgById(orgId);
    if (!org) return null;
    catId = parseInt(catId, 10) || 0;
    for (var i = 0; i < org.categories.length; i++) {
      if (parseInt(org.categories[i].id, 10) === catId) return org.categories[i];
    }
    return null;
  }

  function setDisabled(select, disabled) {
    select.disabled = !!disabled;
    select.classList.toggle('bg-slate-50', !!disabled);
    select.classList.toggle('text-slate-400', !!disabled);
    select.classList.toggle('cursor-not-allowed', !!disabled);
  }

  function rebuildSelect(select, items, placeholder, selectedId) {
    selectedId = parseInt(selectedId, 10) || 0;
    select.innerHTML = '';
    var first = document.createElement('option');
    first.value = '';
    first.textContent = placeholder;
    select.appendChild(first);

    items.forEach(function (item) {
      var opt = document.createElement('option');
      opt.value = String(item.id);
      opt.textContent = item.name + (parseInt(item.is_active, 10) === 0 ? ' (inactive)' : '');
      if (parseInt(item.id, 10) === selectedId) opt.selected = true;
      select.appendChild(opt);
    });
  }

  function currentSector() {
    var orgId = parseInt(orgSelect.value, 10) || 0;
    var catId = parseInt(catSelect.value, 10) || 0;
    var category = categoryById(orgId, catId);
    if (sectorBadge) {
      sectorBadge.textContent = category && category.sector ? category.sector : '—';
    }
  }

  function fillCategories(orgId, selectedId, keepSub) {
    orgId = parseInt(orgId, 10) || 0;
    var org = orgById(orgId);
    var categories = org ? org.categories : [];

    if (!orgId) {
      rebuildSelect(catSelect, [], '-- Select organisation first --', 0);
      setDisabled(catSelect, true);
      rebuildSelect(subSelect, [], '-- Optional --', 0);
      setDisabled(subSelect, true);
      if (btnAddCat) btnAddCat.disabled = false;
      if (btnAddSub) btnAddSub.disabled = true;
      if (sectorBadge) sectorBadge.textContent = '—';
      return;
    }

    rebuildSelect(catSelect, categories, '-- Select category --', selectedId);
    setDisabled(catSelect, false);
    if (btnAddCat) btnAddCat.disabled = false;

    var nextCatId = parseInt(catSelect.value, 10) || 0;
    if (keepSub && nextCatId === (parseInt(selectedId, 10) || 0)) {
      fillSubcategories(nextCatId, keepSub);
    } else {
      fillSubcategories(nextCatId, 0);
    }
    currentSector();
  }

  function fillSubcategories(catId, selectedId) {
    catId = parseInt(catId, 10) || 0;
    var orgId = parseInt(orgSelect.value, 10) || 0;
    var category = categoryById(orgId, catId);
    var subcategories = category ? category.subcategories : [];

    if (!catId) {
      rebuildSelect(subSelect, [], '-- Optional --', 0);
      setDisabled(subSelect, true);
      if (btnAddSub) btnAddSub.disabled = true;
      return;
    }

    rebuildSelect(subSelect, subcategories, '-- Optional --', selectedId);
    setDisabled(subSelect, false);
    if (btnAddSub) btnAddSub.disabled = false;
    currentSector();
  }

  orgSelect.addEventListener('change', function () {
    fillCategories(this.value, 0, false);
  });

  catSelect.addEventListener('change', function () {
    fillSubcategories(this.value, 0);
  });

  // ---- Modal ----
  function openModal(mode) {
    modalMode = mode;
    modalError.classList.add('hidden');
    modalError.textContent = '';

    if (mode === 'category') {
      modalTitle.textContent = 'Add category';
      modalHint.textContent = 'Created inside the selected organisation.';
      modalIcon.className = 'fa-solid fa-folder-plus text-xs';
      modalCatFields.classList.remove('hidden');
      modalSubFields.classList.add('hidden');
      modalCatName.value = '';

      var orgId = parseInt(orgSelect.value, 10) || 0;
      if (modalOrg.querySelector('option[value="' + orgId + '"]')) {
        modalOrg.value = String(orgId);
      }
      modalNewOrgWrap.classList.add('hidden');
      modalNewOrg.value = '';
      // Default the sector to the currently selected category's sector.
      var category = categoryById(orgId, parseInt(catSelect.value, 10) || 0);
      if (category && modalSector.querySelector('option[value="' + category.sector + '"]')) {
        modalSector.value = category.sector;
      }
      modalCatName.focus();
    } else {
      modalTitle.textContent = 'Add sub-category';
      modalHint.textContent = 'Created inside the selected category.';
      modalIcon.className = 'fa-solid fa-tags text-xs';
      modalCatFields.classList.add('hidden');
      modalSubFields.classList.remove('hidden');

      var org = orgById(orgSelect.value);
      var cat = categoryById(orgSelect.value, catSelect.value);
      modalPath.textContent = (org ? org.name : '—') + ' › ' + (cat ? cat.name : '—');
      modalSubName.value = '';
      modalSubName.focus();
    }

    modal.classList.remove('hidden');
  }

  function closeModal() {
    modal.classList.add('hidden');
  }

  if (btnAddCat) btnAddCat.addEventListener('click', function () { openModal('category'); });
  if (btnAddSub) btnAddSub.addEventListener('click', function () { openModal('subcategory'); });

  modal.querySelectorAll('[data-taxonomy-close]').forEach(function (el) {
    el.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
  });

  modalOrg.addEventListener('change', function () {
    modalNewOrgWrap.classList.toggle('hidden', this.value !== '__new');
    if (this.value === '__new') modalNewOrg.focus();
  });

  function showModalError(message) {
    modalError.textContent = message;
    modalError.classList.remove('hidden');
  }

  modalSave.addEventListener('click', function () {
    var body = new FormData();
    body.append('csrf_token', csrfToken);
    body.append('action', modalMode === 'category' ? 'add_category' : 'add_subcategory');

    var orgId = parseInt(orgSelect.value, 10) || 0;
    var catId = parseInt(catSelect.value, 10) || 0;

    if (modalMode === 'category') {
      var name = modalCatName.value.trim();
      if (!name) { showModalError('Enter a category name.'); return; }

      body.append('name', name);
      body.append('sector', modalSector.value);
      if (modalOrg.value === '__new') {
        var newOrg = modalNewOrg.value.trim();
        if (!newOrg) { showModalError('Enter the new organisation name.'); return; }
        body.append('organization_name', newOrg);
      } else if (modalOrg.value) {
        body.append('organization_id', modalOrg.value);
      } else {
        showModalError('Choose an organisation.');
        return;
      }
    } else {
      var subName = modalSubName.value.trim();
      if (catId <= 0) { closeModal(); return; }
      if (!subName) { showModalError('Enter a sub-category name.'); return; }
      body.append('name', subName);
      body.append('category_id', String(catId));
    }

    modalSave.disabled = true;
    var originalHtml = modalSave.innerHTML;
    modalSave.textContent = 'Saving…';

    fetch(endpoint, { method: 'POST', body: body, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        if (!data.success) {
          showModalError(data.message || 'Could not save.');
          return;
        }

        if (modalMode === 'category') {
          var org = data.organization || {};
          var category = data.category || {};
          var node = orgById(org.id);
          if (!node) {
            node = { id: org.id, name: org.name, is_active: 1, categories: [] };
            taxonomy.push(node);
            var newOpt = document.createElement('option');
            newOpt.value = String(org.id);
            newOpt.textContent = org.name;
            orgSelect.appendChild(newOpt);
          }
          node.categories.push({
            id: category.id,
            name: category.name,
            sector: category.sector,
            is_active: 1,
            subcategories: []
          });

          orgSelect.value = String(org.id);
          fillCategories(org.id, category.id, false);

          if (modalOrg.querySelector('option[value="' + org.id + '"]') === null) {
            var opt = document.createElement('option');
            opt.value = String(org.id);
            opt.textContent = org.name;
            modalOrg.insertBefore(opt, modalOrg.querySelector('option[value="__new"]'));
          }
        } else {
          var sub = data.subcategory || {};
          var thisOrgId = parseInt(orgSelect.value, 10) || 0;
          var thisCat = categoryById(thisOrgId, catId);
          if (thisCat) {
            thisCat.subcategories.push({ id: sub.id, name: sub.name, is_active: 1 });
          }
          fillSubcategories(catId, sub.id);
        }

        closeModal();
      })
      .catch(function () {
        showModalError('Network error. Please try again.');
      })
      .finally(function () {
        modalSave.disabled = false;
        modalSave.innerHTML = originalHtml;
      });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
