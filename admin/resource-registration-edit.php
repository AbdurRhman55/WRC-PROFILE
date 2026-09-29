<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Registration';
$errors = [];
$reg = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $reg = getById('resources_registration', (int) $_GET['id']);
    if (!$reg) {
        setFlash('error', 'Registration not found.');
        redirect('resources.php?tab=registrations');
    }
    $pageTitle = 'Edit: ' . $reg['title'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('resources.php?tab=registrations');
    }

    $title = getPost('title');
    $description = getPost('description');
    $registration_number = getPost('registration_number');
    $issuing_authority = getPost('issuing_authority');
    $issue_date = getPost('issue_date') ?: null;
    $expiry_date = getPost('expiry_date') ?: null;
    $document_type = getPost('document_type');
    $file_path = getPost('file_path');
    $icon = getPost('icon');
    $badge = getPost('badge');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int) getPost('sort_order');

    if (empty($title)) $errors[] = 'Title is required.';
    $validDocTypes = ['certificate', 'registration', 'license', 'accreditation', 'compliance'];
    if (!in_array($document_type, $validDocTypes)) $errors[] = 'Invalid document type.';

    if (empty($errors)) {
        global $pdo;
        $data = compact('title', 'description', 'registration_number', 'issuing_authority', 'issue_date', 'expiry_date', 'document_type', 'file_path', 'icon', 'badge', 'is_active', 'sort_order');

        if ($isEdit) {
            $sets = [];
            $vals = [];
            foreach ($data as $k => $v) {
                $sets[] = "$k = ?";
                $vals[] = $v;
            }
            $vals[] = $reg['id'];
            $stmt = $pdo->prepare("UPDATE resources_registration SET " . implode(', ', $sets) . " WHERE id = ?");
            $stmt->execute($vals);
            setFlash('success', 'Registration updated successfully.');
        } else {
            $cols = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $stmt = $pdo->prepare("INSERT INTO resources_registration ($cols) VALUES ($placeholders)");
            $stmt->execute(array_values($data));
            setFlash('success', 'Registration created successfully.');
        }
        redirect('resources.php?tab=registrations');
    }
}

$docTypes = ['certificate' => 'Certificate', 'registration' => 'Registration', 'license' => 'License', 'accreditation' => 'Accreditation', 'compliance' => 'Compliance'];
$iconPresets = [
    ['icon' => 'fa-stamp', 'label' => 'Stamp'],
    ['icon' => 'fa-certificate', 'label' => 'Certificate'],
    ['icon' => 'fa-award', 'label' => 'Award'],
    ['icon' => 'fa-receipt', 'label' => 'Receipt'],
    ['icon' => 'fa-file-invoice-dollar', 'label' => 'Invoice'],
    ['icon' => 'fa-shield-halved', 'label' => 'Shield'],
    ['icon' => 'fa-badge-check', 'label' => 'Badge'],
    ['icon' => 'fa-clipboard-check', 'label' => 'Clipboard'],
];

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?= $isEdit ? 'Edit Registration' : 'Add New Registration' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update registration details and document.' : 'Add a legal registration or certification document.' ?></p>
  </div>
  <a href="resources.php?tab=registrations" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Registrations
  </a>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
  <div class="flex items-center gap-2 mb-1"><i class="fa-solid fa-circle-exclamation"></i><span class="font-semibold text-sm">Please fix the following errors:</span></div>
  <ul class="list-disc list-inside text-sm mt-1"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <!-- Basic Info -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Registration Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($reg['title'] ?? '') ?>" required placeholder="e.g. NGO Registration Certificate" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Registration Number</label>
        <input type="text" name="registration_number" value="<?= htmlspecialchars($reg['registration_number'] ?? '') ?>" placeholder="e.g. SW/NGO/12345" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Issuing Authority</label>
        <input type="text" name="issuing_authority" value="<?= htmlspecialchars($reg['issuing_authority'] ?? '') ?>" placeholder="e.g. Social Welfare Department" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="3" placeholder="Brief description of this registration or certification..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($reg['description'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Document Type & Icon -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Classification</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Document Type</label>
        <select name="document_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <?php foreach ($docTypes as $val => $label): ?>
          <option value="<?= $val ?>" <?= ($reg['document_type'] ?? 'certificate') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Badge Text</label>
        <input type="text" name="badge" value="<?= htmlspecialchars($reg['badge'] ?? '') ?>" placeholder="e.g. Registered, Certified, Active" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        <p class="text-[11px] text-slate-400 mt-1">Small label shown next to the title</p>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-2">Icon</label>
        <input type="hidden" name="icon" id="iconInput" value="<?= htmlspecialchars($reg['icon'] ?? 'fa-stamp') ?>">
        <div class="flex flex-wrap gap-2" id="iconPicker">
          <?php foreach ($iconPresets as $preset): ?>
          <button type="button" onclick="pickIcon('<?= $preset['icon'] ?>', this)" class="icon-pick w-11 h-11 rounded-xl border-2 border-slate-200 flex items-center justify-center hover:border-brandRed hover:bg-brandRed/5 transition-all <?= ($reg['icon'] ?? 'fa-stamp') === $preset['icon'] ? 'border-brandRed bg-brandRed/5' : '' ?>" title="<?= $preset['label'] ?>">
            <i class="fa-solid <?= $preset['icon'] ?> text-slate-600"></i>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Dates -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Dates</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Issue Date</label>
        <input type="date" name="issue_date" value="<?= htmlspecialchars($reg['issue_date'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Expiry Date</label>
        <input type="date" name="expiry_date" value="<?= htmlspecialchars($reg['expiry_date'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        <p class="text-[11px] text-slate-400 mt-1">Leave blank if no expiry</p>
      </div>
    </div>
  </div>

  <!-- File Upload -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Document File</h2>
    <?php uploadWidget('file_path', 'Registration Document (PDF, Image)', $reg['file_path'] ?? '', 'file', 'file_path_input'); ?>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($reg['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        <p class="text-[11px] text-slate-400 mt-1">Lower numbers appear first</p>
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($reg['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Published (visible on website)</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="resources.php?tab=registrations" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Registration' : 'Create Registration' ?>
    </button>
  </div>
</form>

<script>
function pickIcon(icon, btn) {
  document.getElementById('iconInput').value = icon;
  document.querySelectorAll('.icon-pick').forEach(b => { b.classList.remove('border-brandRed', 'bg-brandRed/5'); b.classList.add('border-slate-200'); });
  btn.classList.add('border-brandRed', 'bg-brandRed/5');
  btn.classList.remove('border-slate-200');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
