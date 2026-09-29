<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Photo Gallery';

if (isPost() && isset($_POST['delete_id'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('photos.php'); }
    $photo = getRow('photo_gallery', ['id' => (int) $_POST['delete_id']]);
    if ($photo && $photo['image_path'] && file_exists(__DIR__ . '/../' . $photo['image_path'])) {
        unlink(__DIR__ . '/../' . $photo['image_path']);
    }
    global $pdo;
    $pdo->prepare("DELETE FROM photo_gallery WHERE id = ?")->execute([(int) $_POST['delete_id']]);
    setFlash('success', 'Photo deleted.');
    redirect('photos.php');
}

$photos = getRows('photo_gallery', [], 'sort_order ASC');
$categories = ['relief' => 'Relief', 'wash' => 'WASH', 'health' => 'Health', 'education' => 'Education', 'general' => 'General'];

include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Photo Gallery</h1>
    <p class="text-sm text-slate-500 mt-1">Manage gallery photos for the news page</p>
  </div>
  <a href="photo-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
    <i class="fa-solid fa-plus text-xs"></i> Add Photo
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <?php if (empty($photos)): ?>
  <div class="col-span-full text-center py-16">
    <i class="fa-solid fa-camera-retro text-4xl text-slate-200 mb-4"></i>
    <p class="text-slate-400 text-sm">No photos yet.</p>
    <a href="photo-edit.php" class="mt-3 inline-block text-sm text-brandRed font-semibold hover:underline">Add your first photo &rarr;</a>
  </div>
  <?php else: ?>
  <?php foreach ($photos as $photo): ?>
  <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden group">
    <div class="aspect-[4/3] bg-slate-100 overflow-hidden relative">
      <img src="/<?= htmlspecialchars($photo['image_path']) ?>" alt="<?= htmlspecialchars($photo['alt_text'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
      <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <a href="photo-edit.php?id=<?= $photo['id'] ?>" class="w-7 h-7 rounded-lg bg-white/90 text-slate-600 flex items-center justify-center hover:bg-white text-xs"><i class="fa-solid fa-pen"></i></a>
        <form method="POST" onsubmit="return confirm('Delete this photo?')">
          <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
          <input type="hidden" name="delete_id" value="<?= $photo['id'] ?>">
          <button class="w-7 h-7 rounded-lg bg-white/90 text-red-500 flex items-center justify-center hover:bg-white text-xs"><i class="fa-solid fa-trash"></i></button>
        </form>
      </div>
    </div>
    <div class="p-3">
      <p class="text-xs font-semibold text-slate-700 truncate"><?= htmlspecialchars($photo['caption'] ?? $photo['alt_text'] ?? '') ?></p>
      <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 mt-1 inline-block"><?= $categories[$photo['category']] ?? $photo['category'] ?></span>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
