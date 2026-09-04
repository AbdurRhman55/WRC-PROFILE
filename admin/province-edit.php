<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Province';
$errors = [];
$province = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $province = getById('provinces', (int) $_GET['id']);
    if (!$province) {
        setFlash('error', 'Province not found.');
        redirect('provinces.php');
    }
    $pageTitle = 'Edit Province: ' . $province['name'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('provinces.php');
    }

    $name = getPost('name');
    $slug = getPost('slug');
    $description = getPost('description');
    $icon_class = getPost('icon_class');
    $beneficiaries_count = getPostInt('beneficiaries_count');
    $sort_order = getPostInt('sort_order');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($slug)) $slug = generateSlug($name);

    if (!checkSlugUnique('provinces', $slug, $isEdit ? $province['id'] : 0)) {
        $errors[] = 'Slug already exists. Please choose a unique slug.';
    }

    if (empty($errors)) {
        global $pdo;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE provinces SET name=?, slug=?, description=?, icon_class=?, beneficiaries_count=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$name, $slug, $description, $icon_class, $beneficiaries_count, $sort_order, $is_active, $province['id']]);
            setFlash('success', 'Province updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO provinces (name, slug, description, icon_class, beneficiaries_count, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $icon_class, $beneficiaries_count, $sort_order, $is_active]);
            setFlash('success', 'Province created successfully.');
        }
        redirect('provinces.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Province' : 'Add New Province' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update province details.' : 'Create a new province.' ?></p>
  </div>
  <a href="provinces.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Provinces
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
        <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
        <input type="text" name="name" value="<?= htmlspecialchars($province['name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" id="nameInput">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($province['slug'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed font-mono" id="slugInput">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" rows="4" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($province['description'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Details -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Icon Class</label>
        <input type="text" name="icon_class" value="<?= htmlspecialchars($province['icon_class'] ?? '') ?>" placeholder="fa-solid fa-map-pin" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Beneficiaries Count</label>
        <input type="number" name="beneficiaries_count" value="<?= (int) ($province['beneficiaries_count'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($province['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <!-- Status -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Status</h2>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="is_active" value="1" <?= ($province['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
      <span class="text-sm font-medium text-slate-700">Active</span>
    </label>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="provinces.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Province' : 'Create Province' ?>
    </button>
  </div>
</form>

<script>
document.getElementById('nameInput')?.addEventListener('input', function() {
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
