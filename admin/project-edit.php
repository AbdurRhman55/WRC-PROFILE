<?php
require_once __DIR__ . '/config.php';
requireLogin();

// Handle donor sub-table operations
if (isPost() && isset($_POST['add_donor'])) {
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

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('projects.php');
    }

    $title = getPost('title');
    $slug = getPost('slug');
    $status = getPost('status');
    $province_id = getPost('province_id') ? (int) getPost('province_id') : null;
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
    if (!in_array($status, ['active', 'completed', 'upcoming'])) $errors[] = 'Invalid status.';

    foreach (['objectives', 'target_groups', 'timeline', 'gallery_images', 'related_projects'] as $field) {
        $val = ${$field};
        if (!empty($val)) {
            $decoded = json_decode($val, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be valid JSON.';
            }
        }
    }

    if (empty($errors)) {
        global $pdo;

        $objectives_json = !empty($objectives) ? $objectives : null;
        $target_groups_json = !empty($target_groups) ? $target_groups : null;
        $timeline_json = !empty($timeline) ? $timeline : null;
        $gallery_images_json = !empty($gallery_images) ? $gallery_images : null;
        $related_projects_json = !empty($related_projects) ? $related_projects : null;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE projects SET slug=?, title=?, status=?, province_id=?, start_date=?, end_date=?, budget_amount=?, budget_currency=?, beneficiaries_count=?, progress_percent=?, description=?, overview=?, location_details=?, hero_image=?, overview_image=?, objectives=?, target_groups=?, timeline=?, gallery_images=?, related_projects=?, is_active=?, show_in_history=?, sort_order=? WHERE id=?");
            $stmt->execute([$slug, $title, $status, $province_id, $start_date, $end_date, $budget_amount, $budget_currency, $beneficiaries_count, $progress_percent, $description, $overview, $location_details, $hero_image, $overview_image, $objectives_json, $target_groups_json, $timeline_json, $gallery_images_json, $related_projects_json, $is_active, $show_in_history, $sort_order, $project['id']]);
            setFlash('success', 'Project updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO projects (slug, title, status, province_id, start_date, end_date, budget_amount, budget_currency, beneficiaries_count, progress_percent, description, overview, location_details, hero_image, overview_image, objectives, target_groups, timeline, gallery_images, related_projects, is_active, show_in_history, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $title, $status, $province_id, $start_date, $end_date, $budget_amount, $budget_currency, $beneficiaries_count, $progress_percent, $description, $overview, $location_details, $hero_image, $overview_image, $objectives_json, $target_groups_json, $timeline_json, $gallery_images_json, $related_projects_json, $is_active, $show_in_history, $sort_order]);
            setFlash('success', 'Project created successfully.');
        }
        redirect('projects.php');
    }
}

$allProjects = getRows('projects', [], 'sort_order ASC');
$allPrograms = getRows('programs', ['is_active' => 1], 'sort_order ASC');

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

<form method="POST" class="space-y-6">
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
        <label class="block text-sm font-medium text-slate-700 mb-1">Location Details</label>
        <input type="text" name="location_details" value="<?= htmlspecialchars($project['location_details'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
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
        <textarea name="description" rows="4" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Overview</label>
        <textarea name="overview" rows="5" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($project['overview'] ?? '') ?></textarea>
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
          <label class="text-sm font-medium text-slate-700">Objectives</label>
          <button type="button" onclick="addObjective()" class="text-xs font-semibold text-blue-600 hover:text-blue-700"><i class="fa-solid fa-plus"></i> Add Objective</button>
        </div>
        <input type="hidden" name="objectives" id="objectivesInput" value="<?= htmlspecialchars($project['objectives'] ?? '[]') ?>">
        <div id="objectivesList" class="space-y-2"></div>
      </div>

      <!-- Target Groups (Chip with Icon) -->
      <div>
        <label class="text-sm font-medium text-slate-700 mb-1.5 block">Target Groups</label>
        <p class="text-xs text-slate-400 mb-2">Type group name, then icon class (e.g. fa-solid fa-users), press Enter</p>
        <input type="hidden" name="target_groups" id="targetGroupsInput" value="<?= htmlspecialchars($project['target_groups'] ?? '[]') ?>">
        <div class="chip-input-wrap rounded-xl border border-slate-200 focus-within:ring-2 focus-within:ring-brandRed/20 focus-within:border-brandRed transition-all p-2" id="targetGroupsWrap">
          <div class="flex flex-wrap gap-1.5" id="targetGroupsChips"></div>
          <div class="flex items-center gap-2">
            <input type="text" id="tgNameField" placeholder="Group name" class="flex-1 min-w-[100px] px-2 py-1.5 text-sm outline-none bg-transparent">
            <input type="text" id="tgIconField" placeholder="fa-solid fa-users" class="w-44 px-2 py-1.5 text-xs outline-none bg-transparent border-l border-slate-200">
            <button type="button" onclick="addTargetGroup()" class="text-xs bg-slate-100 px-2 py-1 rounded-lg hover:bg-slate-200"><i class="fa-solid fa-plus"></i></button>
          </div>
        </div>
      </div>

      <!-- Timeline (Dynamic Rows) -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="text-sm font-medium text-slate-700">Timeline</label>
          <button type="button" onclick="addTimeline()" class="text-xs font-semibold text-blue-600 hover:text-blue-700"><i class="fa-solid fa-plus"></i> Add Phase</button>
        </div>
        <input type="hidden" name="timeline" id="timelineInput" value="<?= htmlspecialchars($project['timeline'] ?? '[]') ?>">
        <div id="timelineList" class="space-y-2"></div>
      </div>

      <!-- Gallery Images -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="text-sm font-medium text-slate-700">Gallery Images</label>
          <button type="button" onclick="addGallery()" class="text-xs font-semibold text-blue-600 hover:text-blue-700"><i class="fa-solid fa-plus"></i> Add Image</button>
        </div>
        <input type="hidden" name="gallery_images" id="galleryInput" value="<?= htmlspecialchars($project['gallery_images'] ?? '[]') ?>">
        <div id="galleryList" class="space-y-2"></div>
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

<style>
.dynamic-row{display:flex;gap:8px;align-items:flex-start;padding:10px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;position:relative}
.dynamic-row input,.dynamic-row textarea{flex:1;padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none}
.dynamic-row input:focus,.dynamic-row textarea:focus{border-color:#ca0000;ring:2px}
.dynamic-row .dr-remove{position:absolute;top:8px;right:8px;width:24px;height:24px;border-radius:50%;background:#fee2e2;color:#991b1b;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px}
.dynamic-row .dr-remove:hover{background:#fecaca}
.chip-input-wrap{display:flex;flex-wrap:wrap;gap:6px;min-height:42px;align-items:center}
.chip-tag{display:inline-flex;align-items:center;gap:5px;background:#dbeafe;color:#1e40af;padding:4px 10px;border-radius:9999px;font-size:12px;font-weight:600;line-height:1}
.chip-tag button{background:none;border:none;color:#1e40af;cursor:pointer;padding:0;font-size:14px;line-height:1;opacity:.6}
.chip-tag button:hover{opacity:1}
.rp-item{transition:all .15s}
</style>

<script>
// === Objectives ===
var objectives = [];
try { objectives = JSON.parse(document.getElementById('objectivesInput').value); if (!Array.isArray(objectives)) objectives = []; } catch(e) { objectives = []; }

function renderObjectives() {
  var el = document.getElementById('objectivesList');
  el.innerHTML = '';
  objectives.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-row"><input type="text" placeholder="Title" value="'+item.title.replace(/"/g,'&quot;')+'" oninput="objectives['+i+'].title=this.value"><textarea rows="2" placeholder="Description" oninput="objectives['+i+'].description=this.value">'+item.description.replace(/</g,'&lt;')+'</textarea><button type="button" class="dr-remove" onclick="objectives.splice('+i+',1);renderObjectives()"><i class="fa-solid fa-xmark"></i></button></div>';
  });
  document.getElementById('objectivesInput').value = JSON.stringify(objectives);
}

function addObjective() {
  objectives.push({title:'',description:''});
  renderObjectives();
}
renderObjectives();

// === Target Groups (Chip with Icon) ===
var tgItems = [];
try { tgItems = JSON.parse(document.getElementById('targetGroupsInput').value); if (!Array.isArray(tgItems)) tgItems = []; } catch(e) { tgItems = []; }

function renderTG() {
  var el = document.getElementById('targetGroupsChips');
  el.innerHTML = '';
  tgItems.forEach(function(item, i) {
    el.innerHTML += '<span class="chip-tag"><i class="'+(item.icon||'fa-solid fa-users')+'" style="font-size:11px"></i> <span>'+item.name.replace(/</g,'&lt;')+'</span> <button type="button" onclick="tgItems.splice('+i+',1);renderTG()">&times;</button></span>';
  });
  document.getElementById('targetGroupsInput').value = JSON.stringify(tgItems);
}

function addTargetGroup() {
  var name = document.getElementById('tgNameField').value.trim();
  var icon = document.getElementById('tgIconField').value.trim() || 'fa-solid fa-users';
  if (name) {
    tgItems.push({name:name, icon:icon});
    document.getElementById('tgNameField').value = '';
    document.getElementById('tgIconField').value = '';
    renderTG();
  }
}

document.getElementById('tgNameField').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') { e.preventDefault(); addTargetGroup(); }
});
renderTG();

// === Timeline ===
var timeline = [];
try { timeline = JSON.parse(document.getElementById('timelineInput').value); if (!Array.isArray(timeline)) timeline = []; } catch(e) { timeline = []; }

function renderTimeline() {
  var el = document.getElementById('timelineList');
  el.innerHTML = '';
  timeline.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-row"><input type="text" placeholder="Date (e.g. Jan 2025)" value="'+item.date.replace(/"/g,'&quot;')+'" oninput="timeline['+i+'].date=this.value" style="max-width:140px"><input type="text" placeholder="Title" value="'+item.title.replace(/"/g,'&quot;')+'" oninput="timeline['+i+'].title=this.value"><textarea rows="1" placeholder="Description" oninput="timeline['+i+'].description=this.value">'+item.description.replace(/</g,'&lt;')+'</textarea><button type="button" class="dr-remove" onclick="timeline.splice('+i+',1);renderTimeline()"><i class="fa-solid fa-xmark"></i></button></div>';
  });
  document.getElementById('timelineInput').value = JSON.stringify(timeline);
}

function addTimeline() {
  timeline.push({date:'',title:'',description:'',icon:'fa-solid fa-flag'});
  renderTimeline();
}
renderTimeline();

// === Gallery ===
var gallery = [];
try { gallery = JSON.parse(document.getElementById('galleryInput').value); if (!Array.isArray(gallery)) gallery = []; } catch(e) { gallery = []; }

function renderGallery() {
  var el = document.getElementById('galleryList');
  el.innerHTML = '';
  gallery.forEach(function(item, i) {
    el.innerHTML += '<div class="dynamic-row"><input type="text" placeholder="Image path (e.g. assets/images/photo.jpg)" value="'+item.path.replace(/"/g,'&quot;')+'" oninput="gallery['+i+'].path=this.value"><input type="text" placeholder="Caption" value="'+item.caption.replace(/"/g,'&quot;')+'" oninput="gallery['+i+'].caption=this.value"><button type="button" class="dr-remove" onclick="gallery.splice('+i+',1);renderGallery()"><i class="fa-solid fa-xmark"></i></button></div>';
  });
  document.getElementById('galleryInput').value = JSON.stringify(gallery);
}

function addGallery() {
  gallery.push({path:'',caption:''});
  renderGallery();
}
renderGallery();

// === Related Projects Checkboxes ===
(function() {
  var hidden = document.getElementById('relatedProjectsInput');
  var items = [];
  try { items = JSON.parse(hidden.value); if (!Array.isArray(items)) items = []; } catch(e) { items = []; }

  document.querySelectorAll('.rp-item').forEach(function(label) {
    var cb = label.querySelector('.rp-checkbox');
    var id = parseInt(label.dataset.id);

    if (items.indexOf(id) !== -1) { label.classList.add('bg-blue-50','border-blue-300'); }
    else { label.classList.remove('bg-blue-50','border-blue-300'); }

    cb.addEventListener('change', function() {
      if (cb.checked) { items.push(id); label.classList.add('bg-blue-50','border-blue-300'); }
      else { items = items.filter(function(v){ return v !== id; }); label.classList.remove('bg-blue-50','border-blue-300'); }
      hidden.value = JSON.stringify(items);
    });
  });
})();
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

<?php include __DIR__ . '/includes/footer.php'; ?>
