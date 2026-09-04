<?php
require_once __DIR__ . '/config.php';
requireLogin();

// Handle sub-table operations (interventions, impact_stats, focus_areas)
if (isPost() && isset($_POST['add_sub'])) {
    $table = $_POST['add_sub'];
    $programId = (int) $_GET['id'];
    if (!$programId) { redirect('programs.php'); }
    $validTables = ['program_interventions', 'program_impact_stats', 'program_focus_areas'];
    if (in_array($table, $validTables)) {
        $data = ['program_id' => $programId, 'sort_order' => (int) getPost('sort_order')];
        if ($table === 'program_interventions') {
            $data['title'] = getPost('title');
            $data['description'] = getPost('description');
            $data['icon_class'] = getPost('icon_class');
        } elseif ($table === 'program_impact_stats') {
            $data['stat_value'] = getPost('stat_value');
            $data['stat_label'] = getPost('stat_label');
            $data['icon_class'] = getPost('icon_class');
        } elseif ($table === 'program_focus_areas') {
            $data['title'] = getPost('title');
        }
        if (!empty($data['title']) || !empty($data['stat_value'])) {
            $pdo->prepare("INSERT INTO $table (" . implode(',', array_keys($data)) . ") VALUES (" . implode(',', array_fill(0, count($data), '?')) . ")")->execute(array_values($data));
            setFlash('success', 'Item added!');
        }
    }
    redirect("program-edit.php?id=$programId&section=" . str_replace('program_', '', $table));
}

if (isPost() && isset($_POST['delete_sub'])) {
    $table = $_POST['delete_sub'];
    $subId = (int) $_POST['sub_id'];
    $programId = (int) $_GET['id'];
    $validTables = ['program_interventions', 'program_impact_stats', 'program_focus_areas'];
    if (in_array($table, $validTables) && $subId) {
        $pdo->prepare("DELETE FROM $table WHERE id = ? AND program_id = ?")->execute([$subId, $programId]);
        setFlash('success', 'Item removed.');
    }
    redirect("program-edit.php?id=$programId");
}

$pageTitle = 'Edit Program';
$errors = [];
$program = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $program = getById('programs', (int) $_GET['id']);
    if (!$program) {
        setFlash('error', 'Program not found.');
        redirect('programs.php');
    }
    $pageTitle = 'Edit Program: ' . $program['title'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('programs.php');
    }

    $title = getPost('title');
    $slug = getPost('slug');
    $full_title = getPost('full_title');
    $short_description = getPost('short_description');
    $hero_description = getPost('hero_description');
    $overview = getPost('overview');
    $why_it_matters = getPost('why_it_matters');
    $icon_class = getPost('icon_class');
    $sector_number = getPost('sector_number');
    $hero_image = getPost('hero_image');
    $fallback_image = getPost('fallback_image');
    $focus_areas = getPostRaw('focus_areas');
    $target_groups = getPostRaw('target_groups');
    $related_programs = getPostRaw('related_programs');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int) getPost('sort_order');

    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($slug)) $slug = createSlug($title);

    // Validate JSON fields
    foreach (['focus_areas', 'target_groups', 'related_programs'] as $field) {
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
        $focus_areas_json = !empty($focus_areas) ? $focus_areas : null;
        $target_groups_json = !empty($target_groups) ? $target_groups : null;
        $related_programs_json = !empty($related_programs) ? $related_programs : null;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE programs SET slug=?, title=?, full_title=?, short_description=?, hero_description=?, overview=?, why_it_matters=?, icon_class=?, sector_number=?, hero_image=?, fallback_image=?, focus_areas=?, target_groups=?, related_programs=?, is_active=?, sort_order=? WHERE id=?");
            $stmt->execute([$slug, $title, $full_title, $short_description, $hero_description, $overview, $why_it_matters, $icon_class, $sector_number, $hero_image, $fallback_image, $focus_areas_json, $target_groups_json, $related_programs_json, $is_active, $sort_order, $program['id']]);
            setFlash('success', 'Program updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO programs (slug, title, full_title, short_description, hero_description, overview, why_it_matters, icon_class, sector_number, hero_image, fallback_image, focus_areas, target_groups, related_programs, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $title, $full_title, $short_description, $hero_description, $overview, $why_it_matters, $icon_class, $sector_number, $hero_image, $fallback_image, $focus_areas_json, $target_groups_json, $related_programs_json, $is_active, $sort_order]);
            setFlash('success', 'Program created successfully.');
        }
        redirect('programs.php');
    }
}

$allPrograms = getRows('programs', [], 'sort_order ASC');

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Program' : 'Add New Program' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update program details.' : 'Create a new program sector.' ?></p>
  </div>
  <a href="programs.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Programs
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
        <input type="text" name="title" value="<?= htmlspecialchars($program['title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" id="titleInput">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($program['slug'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed font-mono" id="slugInput">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Full Title</label>
        <input type="text" name="full_title" value="<?= htmlspecialchars($program['full_title'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Icon Class</label>
        <input type="text" name="icon_class" value="<?= htmlspecialchars($program['icon_class'] ?? '') ?>" placeholder="fa-solid fa-heart" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sector Number</label>
        <input type="text" name="sector_number" value="<?= htmlspecialchars($program['sector_number'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <!-- Descriptions -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Descriptions</h2>
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Short Description</label>
        <textarea name="short_description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($program['short_description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Hero Description</label>
        <textarea name="hero_description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($program['hero_description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Overview</label>
        <textarea name="overview" rows="5" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($program['overview'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Why It Matters</label>
        <textarea name="why_it_matters" rows="4" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($program['why_it_matters'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Images -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Images</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php uploadWidget('hero_image', 'Hero Image', $program['hero_image'] ?? '', 'image', 'program_hero_img'); ?>
      <?php uploadWidget('fallback_image', 'Fallback Image', $program['fallback_image'] ?? '', 'image', 'program_fallback_img'); ?>
    </div>
  </div>

  <!-- Friendly Data Fields -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Program Details</h2>
    <div class="space-y-5">

      <!-- Focus Areas (Chip Input) -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Focus Areas</label>
        <p class="text-xs text-slate-400 mb-2">Type a name and press Enter to add</p>
        <input type="hidden" name="focus_areas" id="focusAreasInput" value="<?= htmlspecialchars($program['focus_areas'] ?? '[]') ?>">
        <div class="chip-input-wrap rounded-xl border border-slate-200 focus-within:ring-2 focus-within:ring-brandRed/20 focus-within:border-brandRed transition-all p-2" id="focusAreasWrap">
          <div class="flex flex-wrap gap-1.5" id="focusAreasChips"></div>
          <input type="text" id="focusAreasField" placeholder="Type and press Enter..." class="flex-1 min-w-[120px] px-2 py-1.5 text-sm outline-none bg-transparent">
        </div>
      </div>

      <!-- Target Groups (Chip Input) -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Target Groups</label>
        <p class="text-xs text-slate-400 mb-2">Type a group name and press Enter to add</p>
        <input type="hidden" name="target_groups" id="targetGroupsInput" value="<?= htmlspecialchars($program['target_groups'] ?? '[]') ?>">
        <div class="chip-input-wrap rounded-xl border border-slate-200 focus-within:ring-2 focus-within:ring-brandRed/20 focus-within:border-brandRed transition-all p-2" id="targetGroupsWrap">
          <div class="flex flex-wrap gap-1.5" id="targetGroupsChips"></div>
          <input type="text" id="targetGroupsField" placeholder="Type and press Enter..." class="flex-1 min-w-[120px] px-2 py-1.5 text-sm outline-none bg-transparent">
        </div>
      </div>

      <!-- Related Programs (Multi-Select) -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Related Programs</label>
        <p class="text-xs text-slate-400 mb-2">Hold Ctrl/Cmd to select multiple programs</p>
        <input type="hidden" name="related_programs" id="relatedProgramsInput" value="<?= htmlspecialchars($program['related_programs'] ?? '[]') ?>">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2" id="relatedProgramsList">
          <?php
          $relatedIds = json_decode($program['related_programs'] ?? '[]', true) ?: [];
          foreach ($allPrograms as $prog):
            if ($isEdit && $prog['id'] == $program['id']) continue;
          ?>
          <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition-colors rp-item <?= in_array($prog['id'], $relatedIds) ? 'bg-blue-50 border-blue-300' : '' ?>" data-id="<?= $prog['id'] ?>">
            <input type="checkbox" class="rp-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500/20" <?= in_array($prog['id'], $relatedIds) ? 'checked' : '' ?>>
            <span class="text-sm text-slate-700"><?= htmlspecialchars($prog['title']) ?></span>
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
        <input type="number" name="sort_order" value="<?= (int) ($program['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($program['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="programs.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Program' : 'Create Program' ?>
    </button>
  </div>
</form>

<style>
.chip-input-wrap{display:flex;flex-wrap:wrap;gap:6px;min-height:42px;align-items:center}
.chip-tag{display:inline-flex;align-items:center;gap:5px;background:#fee2e2;color:#991b1b;padding:4px 10px;border-radius:9999px;font-size:12px;font-weight:600;line-height:1}
.chip-tag button{background:none;border:none;color:#991b1b;cursor:pointer;padding:0;font-size:14px;line-height:1;opacity:.6}
.chip-tag button:hover{opacity:1}
.chip-tag.blue{background:#dbeafe;color:#1e40af}
.chip-tag.blue button{color:#1e40af}
.rp-item{transition:all .15s}
</style>

<script>
// Chip Input Helper
function initChipInput(config) {
  const hidden = document.getElementById(config.hiddenId);
  const field = document.getElementById(config.fieldId);
  const chips = document.getElementById(config.chipsId);
  let items = [];
  try { items = JSON.parse(hidden.value); if (!Array.isArray(items)) items = []; } catch(e) { items = []; }

  function render() {
    chips.innerHTML = '';
    items.forEach(function(item, i) {
      const tag = document.createElement('span');
      tag.className = 'chip-tag ' + (config.colorClass || '');
      tag.innerHTML = '<span>' + item.replace(/</g,'&lt;') + '</span><button type="button" data-i="'+i+'">&times;</button>';
      chips.appendChild(tag);
    });
    hidden.value = JSON.stringify(items);
  }

  field.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      var val = field.value.trim();
      if (val && items.indexOf(val) === -1) {
        items.push(val);
        field.value = '';
        render();
      }
    }
  });

  chips.addEventListener('click', function(e) {
    if (e.target.tagName === 'BUTTON') {
      items.splice(parseInt(e.target.dataset.i), 1);
      render();
    }
  });

  render();
}

initChipInput({ hiddenId:'focusAreasInput', fieldId:'focusAreasField', chipsId:'focusAreasChips' });
initChipInput({ hiddenId:'targetGroupsInput', fieldId:'targetGroupsField', chipsId:'targetGroupsChips', colorClass:'blue' });

// Related Programs Checkboxes
(function() {
  var hidden = document.getElementById('relatedProgramsInput');
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
<?php
$programId = $program['id'];
$interventions = getRows('program_interventions', ['program_id' => $programId], 'sort_order ASC');
$impactStats = getRows('program_impact_stats', ['program_id' => $programId], 'sort_order ASC');
$focusAreasList = getRows('program_focus_areas', ['program_id' => $programId], 'sort_order ASC');
?>

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

<!-- ===== Program Sub-tables ===== -->

<!-- Interventions -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 mt-6" id="section-interventions">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Interventions (<?= count($interventions) ?>)</h2>
    <button onclick="document.getElementById('add-intervention-form').classList.toggle('hidden')" class="inline-flex items-center gap-1 bg-teal-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-teal-700 transition-colors">
      <i class="fa-solid fa-plus text-xs"></i> Add
    </button>
  </div>
  <?php if (!empty($interventions)): ?>
  <div class="space-y-2 mb-4">
    <?php foreach ($interventions as $int): ?>
    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
      <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center shrink-0"><i class="<?= htmlspecialchars($int['icon_class'] ?: 'fa-solid fa-check') ?> text-teal-500 text-xs"></i></div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($int['title']) ?></p>
        <?php if ($int['description']): ?><p class="text-xs text-slate-400 mt-0.5 line-clamp-1"><?= htmlspecialchars($int['description']) ?></p><?php endif; ?>
      </div>
      <span class="text-xs text-slate-400 shrink-0">#<?= $int['sort_order'] ?></span>
      <form method="POST" onsubmit="return confirm('Delete this intervention?')">
        <input type="hidden" name="delete_sub" value="program_interventions">
        <input type="hidden" name="sub_id" value="<?= $int['id'] ?>">
        <button class="px-2 py-1 rounded-lg bg-red-50 text-red-500 text-xs hover:bg-red-100 transition-colors"><i class="fa-solid fa-trash text-xs"></i></button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form id="add-intervention-form" method="POST" class="hidden border-t border-slate-100 pt-4 mt-2">
    <input type="hidden" name="add_sub" value="program_interventions">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <div class="md:col-span-2">
        <input type="text" name="title" placeholder="Intervention title *" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
      </div>
      <div>
        <input type="text" name="icon_class" placeholder="fa-solid fa-heart" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
      </div>
      <div>
        <input type="number" name="sort_order" placeholder="Order" value="0" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
      </div>
      <div class="md:col-span-4">
        <textarea name="description" rows="2" placeholder="Description (optional)" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></textarea>
      </div>
    </div>
    <button type="submit" class="mt-2 inline-flex items-center gap-1 bg-teal-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-teal-700 transition-colors"><i class="fa-solid fa-plus text-xs"></i> Save Intervention</button>
  </form>
</div>

<!-- Impact Stats -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 mt-6" id="section-impact-stats">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Impact Statistics (<?= count($impactStats) ?>)</h2>
    <button onclick="document.getElementById('add-impact-form').classList.toggle('hidden')" class="inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors">
      <i class="fa-solid fa-plus text-xs"></i> Add
    </button>
  </div>
  <?php if (!empty($impactStats)): ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-4">
    <?php foreach ($impactStats as $stat): ?>
    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 relative group">
      <div class="flex items-center gap-2 mb-1">
        <i class="<?= htmlspecialchars($stat['icon_class'] ?: 'fa-solid fa-chart-bar') ?> text-blue-400 text-xs"></i>
        <span class="text-lg font-bold text-slate-900"><?= htmlspecialchars($stat['stat_value']) ?></span>
        <form method="POST" class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Delete this stat?')">
          <input type="hidden" name="delete_sub" value="program_impact_stats">
          <input type="hidden" name="sub_id" value="<?= $stat['id'] ?>">
          <button class="px-1.5 py-0.5 rounded bg-red-50 text-red-500 text-xs hover:bg-red-100"><i class="fa-solid fa-trash text-xs"></i></button>
        </form>
      </div>
      <p class="text-xs text-slate-500"><?= htmlspecialchars($stat['stat_label']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form id="add-impact-form" method="POST" class="hidden border-t border-slate-100 pt-4 mt-2">
    <input type="hidden" name="add_sub" value="program_impact_stats">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <div>
        <input type="text" name="stat_value" placeholder="Value (e.g. 50,000+) *" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
      </div>
      <div class="md:col-span-2">
        <input type="text" name="stat_label" placeholder="Label (e.g. People Reached) *" required class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
      </div>
      <div class="grid grid-cols-2 gap-2">
        <input type="text" name="icon_class" placeholder="fa-solid fa-users" class="px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
        <input type="number" name="sort_order" placeholder="Order" value="0" class="px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
      </div>
    </div>
    <button type="submit" class="mt-2 inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors"><i class="fa-solid fa-plus text-xs"></i> Save Stat</button>
  </form>
</div>

<!-- Focus Areas (DB) -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 mt-6" id="section-focus-areas">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Focus Areas (<?= count($focusAreasList) ?>)</h2>
    <button onclick="document.getElementById('add-focus-form').classList.toggle('hidden')" class="inline-flex items-center gap-1 bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-amber-700 transition-colors">
      <i class="fa-solid fa-plus text-xs"></i> Add
    </button>
  </div>
  <?php if (!empty($focusAreasList)): ?>
  <div class="flex flex-wrap gap-2 mb-4">
    <?php foreach ($focusAreasList as $fa): ?>
    <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-full pl-3 pr-1 py-1">
      <span class="text-sm font-medium text-amber-800"><?= htmlspecialchars($fa['title']) ?></span>
      <form method="POST" onsubmit="return confirm('Remove this focus area?')">
        <input type="hidden" name="delete_sub" value="program_focus_areas">
        <input type="hidden" name="sub_id" value="<?= $fa['id'] ?>">
        <button class="w-5 h-5 rounded-full bg-amber-200 text-amber-700 text-xs hover:bg-amber-300 flex items-center justify-center">&times;</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form id="add-focus-form" method="POST" class="hidden border-t border-slate-100 pt-4 mt-2">
    <input type="hidden" name="add_sub" value="program_focus_areas">
    <div class="flex gap-3">
      <input type="text" name="title" placeholder="Focus area title *" required class="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
      <input type="number" name="sort_order" placeholder="Order" value="0" class="w-20 px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
      <button type="submit" class="inline-flex items-center gap-1 bg-amber-600 text-white px-3 py-2 rounded-lg text-xs font-semibold hover:bg-amber-700 transition-colors"><i class="fa-solid fa-plus text-xs"></i> Add</button>
    </div>
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
