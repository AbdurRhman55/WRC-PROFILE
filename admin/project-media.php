<?php
/**
 * WRC Admin - Project gallery media manager.
 *
 * One project per page: upload several photographs at once, give each one a
 * caption and a context note, reorder them, hide/show them, or promote a photo
 * to the project cover.
 */
require_once __DIR__ . '/config.php';
requireLogin();

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    setFlash('error', 'Choose a project to manage its gallery.');
    redirect('gallery.php');
}

global $pdo;
$project = getRow('projects', ['slug' => $slug]);
if (!$project) {
    setFlash('error', 'Project not found.');
    redirect('gallery.php');
}

$hasMediaTable = wrcTableExists('project_media');
$partners = wrcProjectPartners($slug);

if (isPost()) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect('project-media.php?slug=' . rawurlencode($slug));
    }

    if (!$hasMediaTable) {
        setFlash('error', 'The project_media table is missing. Run database/seed_media_gallery.php first.');
        redirect('project-media.php?slug=' . rawurlencode($slug));
    }

    $action = getPost('action');

    if ($action === 'add_images') {
        $files = $_FILES['upload_files'] ?? null;
        $added = 0;
        $failed = [];

        if (is_array($files) && isset($files['name']) && is_array($files['name'])) {
            $nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM project_media WHERE project_slug = ' . $pdo->quote($slug))->fetchColumn() + 1;

            foreach ($files['name'] as $index => $originalName) {
                $errorCode = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
                if ($errorCode === UPLOAD_ERR_NO_FILE || trim((string) $originalName) === '') {
                    continue;
                }

                $file = [
                    'name'     => (string) $originalName,
                    'type'     => (string) ($files['type'][$index] ?? ''),
                    'tmp_name' => (string) ($files['tmp_name'][$index] ?? ''),
                    'error'    => $errorCode,
                    'size'     => (int) ($files['size'][$index] ?? 0),
                ];

                $path = $errorCode === UPLOAD_ERR_OK ? handleUpload($file, 'images') : null;
                if (!$path) {
                    $failed[] = (string) $originalName;
                    continue;
                }

                $caption = trim((string) ($_POST['new_caption'][$index] ?? ''));
                if ($caption === '') {
                    $caption = (string) $project['title'];
                }
                $context = trim((string) ($_POST['new_context'][$index] ?? ''));

                $stmt = $pdo->prepare(
                    'INSERT INTO project_media (project_slug, image_path, caption, context, sort_order, is_active)
                     VALUES (?, ?, ?, ?, ?, 1)'
                );
                $stmt->execute([$slug, $path, mb_substr($caption, 0, 255), $context, $nextOrder++]);
                $added++;
            }
        }

        if ($added > 0) {
            setFlash('success', $added . ' photograph' . ($added === 1 ? '' : 's') . ' uploaded.');
        }
        if ($failed) {
            setFlash('error', 'Could not upload: ' . implode(', ', array_slice($failed, 0, 5)) . '. Use a JPG, PNG, GIF, WebP or SVG under 5 MB.');
        }
        if ($added === 0 && !$failed) {
            setFlash('error', 'Choose at least one photograph to upload.');
        }
        redirect('project-media.php?slug=' . rawurlencode($slug));
    }

    if ($action === 'update_images') {
        $captions = (array) ($_POST['caption'] ?? []);
        $contexts = (array) ($_POST['context'] ?? []);
        $orders   = (array) ($_POST['sort_order'] ?? []);
        $actives  = (array) ($_POST['is_active'] ?? []);

        $update = $pdo->prepare(
            'UPDATE project_media
                SET caption = ?, context = ?, sort_order = ?, is_active = ?
              WHERE id = ? AND project_slug = ?'
        );

        $saved = 0;
        foreach ($captions as $id => $caption) {
            $id = (int) $id;
            if ($id < 1) continue;
            $update->execute([
                mb_substr(trim((string) $caption), 0, 255),
                trim((string) ($contexts[$id] ?? '')),
                (int) ($orders[$id] ?? 0),
                isset($actives[$id]) ? 1 : 0,
                $id,
                $slug,
            ]);
            $saved++;
        }

        setFlash('success', $saved . ' photograph' . ($saved === 1 ? '' : 's') . ' updated.');
        redirect('project-media.php?slug=' . rawurlencode($slug));
    }

    if ($action === 'delete_image') {
        $id = getPostInt('media_id');
        $stmt = $pdo->prepare('SELECT * FROM project_media WHERE id = ? AND project_slug = ?');
        $stmt->execute([$id, $slug]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($media) {
            $pdo->prepare('DELETE FROM project_media WHERE id = ? AND project_slug = ?')->execute([$id, $slug]);

            // Remove the uploaded file only when nothing else still points at it.
            $path = (string) $media['image_path'];
            if (strpos($path, 'uploads/') === 0) {
                $references = 0;
                foreach ([
                    ['project_media', 'image_path'],
                    ['projects', 'hero_image'],
                    ['partners', 'logo_path'],
                    ['partners', 'cover_image'],
                ] as [$table, $column]) {
                    if (!wrcTableExists($table) || !tableHasColumn($table, $column)) continue;
                    $count = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?");
                    $count->execute([$path]);
                    $references += (int) $count->fetchColumn();
                }
                if ($references === 0 && is_file(dirname(__DIR__) . '/' . ltrim($path, '/'))) {
                    @unlink(dirname(__DIR__) . '/' . ltrim($path, '/'));
                }
            }

            setFlash('success', 'Photograph removed from this gallery.');
        } else {
            setFlash('error', 'Photograph not found.');
        }
        redirect('project-media.php?slug=' . rawurlencode($slug));
    }

    if ($action === 'make_cover') {
        $id = getPostInt('media_id');
        $stmt = $pdo->prepare('SELECT image_path FROM project_media WHERE id = ? AND project_slug = ?');
        $stmt->execute([$id, $slug]);
        $path = (string) ($stmt->fetchColumn() ?: '');

        if ($path !== '') {
            $pdo->prepare('UPDATE projects SET hero_image = ? WHERE slug = ?')->execute([$path, $slug]);
            setFlash('success', 'Project cover updated. The same photograph still belongs to this gallery.');
        } else {
            setFlash('error', 'Photograph not found.');
        }
        redirect('project-media.php?slug=' . rawurlencode($slug));
    }
}

$mediaRows = [];
if ($hasMediaTable) {
    $stmt = $pdo->prepare('SELECT * FROM project_media WHERE project_slug = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$slug]);
    $mediaRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$activeMedia = 0;
foreach ($mediaRows as $row) {
    if ((int) $row['is_active'] === 1) $activeMedia++;
}

$statusMeta = wrcProjectStatusMeta((string) ($project['status'] ?? ''));
$yearLabel = wrcProjectYearLabel($project);
$place = trim((string) ($project['district'] ?? ''));
if ($place === '') $place = trim((string) ($project['location_details'] ?? ''));
$partnerNames = array_map(static function ($partner) { return (string) $partner['name']; }, $partners);
$partnerLabel = $partnerNames ? implode(', ', $partnerNames) : 'No partner linked yet';

$pageTitle = 'Project Gallery: ' . $project['title'];

include __DIR__ . '/includes/header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">
      <a href="gallery.php" class="hover:text-brandRed transition-colors">Project Galleries</a>
      <i class="fa-solid fa-chevron-right text-[7px]"></i>
      <span class="text-slate-600 normal-case tracking-normal"><?= htmlspecialchars((string) $project['title']) ?></span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">Manage Project Photographs</h1>
    <p class="text-slate-500 text-sm mt-1">Upload photographs, write the caption and context shown to visitors, then drag the order into a story.</p>
  </div>
  <div class="flex items-center gap-3 shrink-0">
    <a href="partner-edit.php?id=<?= (int) ($partners[0]['id'] ?? 0) ?>" class="<?= empty($partners[0]['id']) ? 'hidden' : '' ?> inline-flex items-center gap-2 text-slate-500 hover:text-brandRed text-sm font-medium transition-colors">
      <i class="fa-solid fa-handshake text-xs"></i> Partner
    </a>
    <a href="../news/project-gallery.php?slug=<?= rawurlencode($slug) ?>" target="_blank" class="inline-flex items-center gap-2 text-slate-500 hover:text-brandRed text-sm font-medium transition-colors">
      <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> Preview
    </a>
    <a href="gallery.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
      <i class="fa-solid fa-arrow-left text-xs"></i> Back
    </a>
  </div>
</div>

<!-- PROJECT SUMMARY -->
<div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
  <div class="flex flex-col lg:flex-row lg:items-center gap-5">
    <img src="<?= htmlspecialchars(getImageUrl((string) $project['hero_image']), ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full lg:w-44 h-32 rounded-xl object-cover border border-slate-200 shrink-0">
    <div class="min-w-0 flex-1">
      <div class="flex flex-wrap items-center gap-2 mb-1.5">
        <span class="inline-flex items-center gap-1.5 rounded-full border <?= $statusMeta['classes'] ?> px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"><span class="w-1.5 h-1.5 rounded-full <?= $statusMeta['dot'] ?>"></span><?= htmlspecialchars($statusMeta['label']) ?></span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 border border-slate-200 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500"><?= htmlspecialchars((string) ($project['category'] ?? 'Project')) ?></span>
      </div>
      <h2 class="text-lg font-bold text-slate-900 leading-snug"><?= htmlspecialchars((string) $project['title']) ?></h2>
      <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2 text-[11px] text-slate-500">
        <?php if ($place !== ''): ?><span><i class="fa-solid fa-location-dot text-brandRed/60 mr-1"></i><?= htmlspecialchars($place) ?></span><?php endif; ?>
        <?php if ($yearLabel !== ''): ?><span><i class="fa-solid fa-calendar-days text-brandRed/60 mr-1"></i><?= htmlspecialchars($yearLabel) ?></span><?php endif; ?>
        <span><i class="fa-solid fa-handshake text-brandRed/60 mr-1"></i><?= htmlspecialchars($partnerLabel) ?></span>
      </div>
    </div>
    <div class="grid grid-cols-3 gap-3 shrink-0">
      <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-center">
        <p class="text-xl font-bold text-slate-900"><?= count($mediaRows) ?></p>
        <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Total</p>
      </div>
      <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-center">
        <p class="text-xl font-bold text-emerald-700"><?= $activeMedia ?></p>
        <p class="text-[9px] font-bold uppercase tracking-wider text-emerald-500">Live</p>
      </div>
      <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-center">
        <p class="text-xl font-bold text-slate-900"><?= max(0, count($mediaRows) - $activeMedia) ?></p>
        <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Hidden</p>
      </div>
    </div>
  </div>
</div>

<?php if (!$hasMediaTable): ?>
<div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
  <i class="fa-solid fa-triangle-exclamation mr-1"></i>
  The <code class="font-mono text-xs bg-amber-100 px-1.5 py-0.5 rounded">project_media</code> table is missing. Run <code class="font-mono text-xs bg-amber-100 px-1.5 py-0.5 rounded">database/seed_media_gallery.php</code> to create it.
</div>
<?php endif; ?>

<!-- UPLOAD PANEL -->
<?php if ($hasMediaTable): ?>
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-6">
  <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg bg-brandRed/10 text-brandRed flex items-center justify-center"><i class="fa-solid fa-cloud-arrow-up text-xs"></i></span>
      <div>
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Add Photographs</h2>
        <p class="text-[11px] text-slate-400 mt-0.5">Upload up to 10 at a time. JPG, PNG, GIF, WebP or SVG; 5 MB each.</p>
      </div>
    </div>
    <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
      <i class="fa-solid fa-images text-brandRed text-[9px]"></i> <?= count($mediaRows) ?> in gallery
    </span>
  </div>

  <form method="POST" enctype="multipart/form-data" class="p-5" id="uploadForm">
    <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
    <input type="hidden" name="action" value="add_images">

    <div id="uploadRows" class="space-y-3">
      <div class="upload-row rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 p-4">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="lg:w-64 shrink-0">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Photograph</label>
            <input type="file" name="upload_files[]" accept="image/*" class="upload-file block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-brandRed/10 file:px-3 file:py-2 file:text-brandRed file:text-xs file:font-semibold hover:file:bg-brandRed/15">
            <p class="file-name text-[10px] text-slate-400 truncate mt-1">&nbsp;</p>
          </div>
          <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Caption</label>
              <input type="text" name="new_caption[]" placeholder="e.g. Free medical camp in Bajaur" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
            </div>
            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Context</label>
              <textarea name="new_context[]" rows="1" placeholder="What does this photograph show?" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed resize-y"></textarea>
            </div>
          </div>
          <div class="lg:w-10 flex items-start justify-end">
            <button type="button" class="remove-upload-row w-9 h-9 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors" title="Remove row" aria-label="Remove upload row">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4">
      <button type="button" id="addUploadRow" class="inline-flex items-center gap-2 text-xs font-semibold text-brandRed hover:text-brandRed-hover transition-colors">
        <span class="w-7 h-7 rounded-lg bg-brandRed/10 flex items-center justify-center"><i class="fa-solid fa-plus text-[10px]"></i></span>
        Add another photograph
      </button>
      <button type="submit" id="uploadSubmit" class="inline-flex items-center justify-center gap-2 bg-brandRed text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-brandRed-hover transition-colors shadow-sm">
        <i class="fa-solid fa-cloud-arrow-up text-xs"></i> Upload photographs
      </button>
    </div>
  </form>
</div>

<!-- EXISTING PHOTOGRAPHS -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div>
      <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Gallery Photographs</h2>
      <p class="text-[11px] text-slate-400 mt-0.5">Captions and context appear beside every public image. Untick "Live" to hide one without deleting it.</p>
    </div>
    <?php if ($mediaRows): ?>
    <button type="submit" form="mediaUpdateForm" class="inline-flex items-center gap-2 bg-ink-900 text-white text-xs font-semibold px-4 py-2 rounded-xl hover:bg-brandRed transition-colors">
      <i class="fa-solid fa-floppy-disk text-[10px]"></i> Save all changes
    </button>
    <?php endif; ?>
  </div>

  <?php if (empty($mediaRows)): ?>
  <div class="p-10 text-center">
    <span class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mb-4"><i class="fa-solid fa-camera-retro text-xl"></i></span>
    <h3 class="text-base font-bold text-slate-800">No gallery photographs yet</h3>
    <p class="text-sm text-slate-400 mt-1 max-w-md mx-auto">The project cover is shown as a fallback on the public page. Upload the first photograph above to start the gallery.</p>
  </div>
  <?php else: ?>
  <form method="POST" id="mediaUpdateForm" class="p-5">
    <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
    <input type="hidden" name="action" value="update_images">

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
      <?php foreach ($mediaRows as $index => $media): ?>
      <?php
        $mediaId = (int) $media['id'];
        $path = (string) $media['image_path'];
        $isActive = (int) $media['is_active'] === 1;
        $isCover = trim((string) $project['hero_image']) === trim($path);
      ?>
      <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-50/50">
        <div class="relative aspect-[16/9] bg-slate-200 overflow-hidden">
          <img src="<?= htmlspecialchars(getImageUrl($path), ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover" loading="lazy">
          <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-700 shadow-sm">
            <i class="fa-solid fa-camera-retro text-brandRed text-[9px]"></i><?= sprintf('%02d', $index + 1) ?>
          </span>
          <?php if ($isCover): ?>
          <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 rounded-full bg-ink-900/90 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">
            <i class="fa-solid fa-star text-amber-400 text-[9px]"></i> Project cover
          </span>
          <?php elseif (!$isActive): ?>
          <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 rounded-full bg-slate-900/80 backdrop-blur px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">Hidden</span>
          <?php endif; ?>
        </div>

        <div class="p-4 space-y-3">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Caption</label>
            <input type="text" name="caption[<?= $mediaId ?>]" value="<?= htmlspecialchars((string) ($media['caption'] ?? '')) ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Context — what does this photograph show?</label>
            <textarea name="context[<?= $mediaId ?>]" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed resize-y"><?= htmlspecialchars((string) ($media['context'] ?? '')) ?></textarea>
          </div>
          <div class="flex flex-wrap items-end gap-3">
            <div class="w-24">
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Order</label>
              <input type="number" name="sort_order[<?= $mediaId ?>]" value="<?= (int) $media['sort_order'] ?>" min="0" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-center focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
            </div>
            <label class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-white cursor-pointer">
              <input type="checkbox" name="is_active[<?= $mediaId ?>]" value="1" <?= $isActive ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
              <span class="text-xs font-semibold text-slate-600">Live</span>
            </label>
            <div class="ml-auto flex items-center gap-2">
              <?php if (!$isCover): ?>
              <button type="submit" form="makeCover<?= $mediaId ?>" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 hover:text-brandRed transition-colors px-2 py-2" title="Use this photograph as the project cover">
                <i class="fa-solid fa-star text-[10px]"></i> Make cover
              </button>
              <?php endif; ?>
              <button type="submit" form="deleteMedia<?= $mediaId ?>" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 hover:text-red-500 transition-colors px-2 py-2" onclick="return confirm('Remove this photograph from the gallery?')">
                <i class="fa-solid fa-trash text-[10px]"></i> Delete
              </button>
            </div>
          </div>
          <p class="text-[10px] text-slate-400 font-mono truncate" title="<?= htmlspecialchars($path) ?>"><?= htmlspecialchars($path) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="flex items-center justify-end gap-3 mt-5 pt-4 border-t border-slate-100">
      <a href="gallery.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Back to galleries</a>
      <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
        <i class="fa-solid fa-floppy-disk text-xs mr-1"></i> Save all changes
      </button>
    </div>
  </form>

  <?php foreach ($mediaRows as $media): ?>
    <?php $mediaId = (int) $media['id']; ?>
    <form method="POST" id="deleteMedia<?= $mediaId ?>" class="hidden">
      <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
      <input type="hidden" name="action" value="delete_image">
      <input type="hidden" name="media_id" value="<?= $mediaId ?>">
    </form>
    <form method="POST" id="makeCover<?= $mediaId ?>" class="hidden">
      <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
      <input type="hidden" name="action" value="make_cover">
      <input type="hidden" name="media_id" value="<?= $mediaId ?>">
    </form>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<script>
(function () {
  var rows = document.getElementById('uploadRows');
  var addButton = document.getElementById('addUploadRow');
  var form = document.getElementById('uploadForm');
  if (!rows || !addButton || !form) return;

  function refreshNames() {
    rows.querySelectorAll('.upload-row').forEach(function (row) {
      var input = row.querySelector('.upload-file');
      var label = row.querySelector('.file-name');
      if (!input || !label) return;
      label.textContent = input.files && input.files[0] ? input.files[0].name : '\u00a0';
    });
  }

  rows.addEventListener('change', function (event) {
    if (event.target.classList.contains('upload-file')) refreshNames();
  });

  addButton.addEventListener('click', function () {
    var rowsList = rows.querySelectorAll('.upload-row');
    if (rowsList.length >= 10) {
      alert('You can upload up to 10 photographs at a time.');
      return;
    }
    var clone = rowsList[rowsList.length - 1].cloneNode(true);
    clone.querySelectorAll('input[type="text"], textarea').forEach(function (field) { field.value = ''; });
    var file = clone.querySelector('input[type="file"]');
    if (file) file.value = '';
    var name = clone.querySelector('.file-name');
    if (name) name.innerHTML = '&nbsp;';
    rows.appendChild(clone);
  });

  rows.addEventListener('click', function (event) {
    var remove = event.target.closest('.remove-upload-row');
    if (!remove) return;
    var row = remove.closest('.upload-row');
    if (rows.querySelectorAll('.upload-row').length === 1) {
      row.querySelectorAll('input[type="text"], textarea, input[type="file"]').forEach(function (field) { field.value = ''; });
      refreshNames();
      return;
    }
    row.remove();
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
