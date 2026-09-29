<?php
require_once __DIR__ . '/config.php';
requireLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$video = $isEdit ? getRow('video_gallery', ['id' => $id]) : null;

if ($isEdit && !$video) { setFlash('error', 'Video not found.'); redirect('videos.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('videos.php'); }

    $data = [
        'title'          => getPost('title'),
        'description'    => getPost('description'),
        'thumbnail_path' => getPost('thumbnail_path'),
        'video_url'      => getPost('video_url'),
        'duration'       => getPost('duration'),
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'     => getPostInt('sort_order'),
    ];

    if (!empty($_FILES['thumbnail_file']['name']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
        $data['thumbnail_path'] = handleUpload($_FILES['thumbnail_file'], 'images');
    }

    if (!$data['title']) {
        setFlash('error', 'Title is required.');
    } else {
        if ($isEdit) { updateRow('video_gallery', $data, ['id' => $id]); setFlash('success', 'Video updated!'); }
        else { insertRow('video_gallery', $data); setFlash('success', 'Video added!'); }
        redirect('videos.php');
    }
}

$pageTitle = $isEdit ? 'Edit Video' : 'Add Video';
include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?= $pageTitle ?></h1>
    <p class="text-sm text-slate-500 mt-1"><?= $isEdit ? 'Update video details' : 'Add a new video to the gallery' ?></p>
  </div>
  <a href="videos.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors"><i class="fa-solid fa-arrow-left text-xs"></i> Back</a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Video Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($video['title'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($video['description'] ?? '') ?></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Video URL</label>
        <input type="text" name="video_url" value="<?= htmlspecialchars($video['video_url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <?php uploadWidget('thumbnail_path', 'Thumbnail Image', $video['thumbnail_path'] ?? '', 'image', 'video_thumbnail_upload'); ?>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Duration</label>
        <input type="text" name="duration" value="<?= htmlspecialchars($video['duration'] ?? '') ?>" placeholder="3:42" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= $video['sort_order'] ?? 0 ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <label class="flex items-center gap-2 cursor-pointer">
      <input type="checkbox" name="is_active" value="1" <?= ($video['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
      <span class="text-sm font-semibold text-slate-700">Active</span>
    </label>
  </div>

  <div class="flex items-center gap-3">
    <button type="submit" class="inline-flex items-center gap-2 bg-brandRed text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
      <i class="fa-solid fa-check text-xs"></i> <?= $isEdit ? 'Update Video' : 'Add Video' ?>
    </button>
    <a href="videos.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Cancel</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
