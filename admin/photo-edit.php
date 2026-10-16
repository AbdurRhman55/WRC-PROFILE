<?php
require_once __DIR__ . '/config.php';
requireLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$photo = $isEdit ? getRow('photo_gallery', ['id' => $id]) : null;

if ($isEdit && !$photo) { setFlash('error', 'Photo not found.'); redirect('photos.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('photos.php'); }

    $data = [
        'image_path' => getPost('image_path'),
        'alt_text'   => getPost('alt_text'),
        'caption'    => getPost('caption'),
        'category'   => getPost('category') ?: 'general',
        'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        'sort_order' => getPostInt('sort_order'),
    ];

    if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $data['image_path'] = handleUpload($_FILES['image_file'], 'images');
    }

    if (!$data['image_path']) {
        setFlash('error', 'Image is required.');
    } else {
        if ($isEdit) { updateRow('photo_gallery', $data, ['id' => $id]); setFlash('success', 'Photo updated!'); }
        else { insertRow('photo_gallery', $data); setFlash('success', 'Photo added!'); }
        redirect('photos.php');
    }
}

$pageTitle = $isEdit ? 'Edit Photo' : 'Add Photo';

// Sections available when tagging a photo: the standard set plus every section
// already used in the gallery, so the dropdown always matches what is on the site.
$standardSections = [ 'relief', 'wash', 'health', 'education', 'meetings', 'partnerships', 'community', 'general' ];
$categories = [];
foreach ( array_merge( $standardSections, array_column( getRows( 'photo_gallery', [], 'id ASC' ), 'category' ) ) as $key ) {
    $key = strtolower( trim( ( string ) $key ) );
    if ( $key === '' || isset( $categories[ $key ] ) ) {
        continue;
    }
    $categories[ $key ] = $key === 'wash' ? 'WASH' : ucfirst( $key );
}
ksort( $categories, SORT_NATURAL | SORT_FLAG_CASE );
include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?= $pageTitle ?></h1>
    <p class="text-sm text-slate-500 mt-1"><?= $isEdit ? 'Update photo details' : 'Add a new photo to the gallery' ?></p>
  </div>
  <a href="photos.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors"><i class="fa-solid fa-arrow-left text-xs"></i> Back</a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Photo Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <?php uploadWidget('image_path', 'Photo Image *', $photo['image_path'] ?? '', 'image', 'photo_image_upload'); ?>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Alt Text</label>
        <input type="text" name="alt_text" value="<?= htmlspecialchars($photo['alt_text'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Caption</label>
        <input type="text" name="caption" value="<?= htmlspecialchars($photo['caption'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Section</label>
        <select name="category" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <?php foreach ($categories as $k => $v): ?>
          <option value="<?= $k ?>" <?= strtolower((string)($photo['category'] ?? 'general')) === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <p class="text-[11px] text-slate-400 mt-1.5">Photos are grouped by section in the gallery, and the section name is shown on and below every image.</p>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= $photo['sort_order'] ?? 0 ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <label class="flex items-center gap-2 cursor-pointer">
      <input type="checkbox" name="is_active" value="1" <?= ($photo['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
      <span class="text-sm font-semibold text-slate-700">Active</span>
    </label>
  </div>

  <div class="flex items-center gap-3">
    <button type="submit" class="inline-flex items-center gap-2 bg-brandRed text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
      <i class="fa-solid fa-check text-xs"></i> <?= $isEdit ? 'Update Photo' : 'Add Photo' ?>
    </button>
    <a href="photos.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Cancel</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
