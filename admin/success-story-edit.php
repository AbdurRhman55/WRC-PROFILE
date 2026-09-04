<?php
require __DIR__ . '/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$story = $isEdit ? getRow('success_stories', ['id' => $id]) : null;
$sectors = ['health' => 'Health', 'livelihoods' => 'Livelihoods', 'wash' => 'WASH', 'protection' => 'Protection', 'disaster-risk' => 'Disaster Risk Reduction', 'governance' => 'Governance'];

if ($isEdit && !$story) {
    setFlash('error', 'Story not found.');
    header('Location: success-stories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'person_name'  => getPost('person_name'),
        'person_title' => getPost('person_title'),
        'location'     => getPost('location'),
        'quote'        => getPost('quote'),
        'sector'       => getPost('sector'),
        'sector_badge' => getPost('sector_badge'),
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'   => getPostInt('sort_order'),
    ];

    if (!empty($_POST['image_path'])) {
        $data['image_path'] = $_POST['image_path'];
    } elseif (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $data['image_path'] = handleUpload($_FILES['image_file'], 'images');
    }

    if (!$data['person_name'] || !$data['quote']) {
        setFlash('error', 'Name and quote are required.');
    } else {
        if ($isEdit) {
            updateRow('success_stories', $data, ['id' => $id]);
            setFlash('success', 'Story updated successfully!');
        } else {
            insertRow('success_stories', $data);
            setFlash('success', 'Story created successfully!');
        }
        header('Location: success-stories.php');
        exit;
    }
}

$pageTitle = $isEdit ? 'Edit Story' : 'New Story';
include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?= $pageTitle ?></h1>
    <p class="text-sm text-slate-500 mt-1"><?= $isEdit ? 'Update story details' : 'Add a new beneficiary success story' ?></p>
  </div>
  <a href="success-stories.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="space-y-6">
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Story Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Person Name *</label>
        <input type="text" name="person_name" value="<?= htmlspecialchars($story['person_name'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Title / Role</label>
        <input type="text" name="person_title" value="<?= htmlspecialchars($story['person_title'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($story['location'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Sector</label>
        <select name="sector" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <option value="">— None —</option>
          <?php foreach ($sectors as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($story['sector'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Quote / Story *</label>
        <textarea name="quote" rows="4" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required><?= htmlspecialchars($story['quote'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Photo</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Upload Image</label>
        <input type="file" name="image_file" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brandRed file:text-white hover:file:bg-red-700">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Or Image URL</label>
        <input type="text" name="image_path" value="<?= htmlspecialchars($story['image_path'] ?? '') ?>" placeholder="assets/images/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
    <?php if (!empty($story['image_path'])): ?>
    <div class="mt-4">
      <img src="/<?= $story['image_path'] ?>" alt="" class="w-32 h-32 object-cover rounded-xl border border-slate-200">
    </div>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= $story['sort_order'] ?? 0 ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($story['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-semibold text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <button type="submit" class="inline-flex items-center gap-2 bg-brandRed text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
      <i class="fa-solid fa-check text-xs"></i> <?= $isEdit ? 'Update Story' : 'Create Story' ?>
    </button>
    <a href="success-stories.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Cancel</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
