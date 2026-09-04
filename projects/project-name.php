<?php
/**
 * WRC Single Project Detail (Dynamic)
 * Usage: project-name.php?slug=project-slug
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    echo '<!DOCTYPE html><html><head><title>Project Not Found</title></head><body style="font-family:sans-serif;text-align:center;padding:80px 20px;"><h1>Project Not Found</h1><p>No project slug was provided. Please return to the <a href="index.php">projects page</a>.</p></body></html>';
    exit;
}

$project = getProjectBySlug($slug);
if (!$project) {
    echo '<!DOCTYPE html><html><head><title>Project Not Found</title></head><body style="font-family:sans-serif;text-align:center;padding:80px 20px;"><h1>Project Not Found</h1><p>The project you are looking for does not exist or is no longer available. Please return to the <a href="index.php">projects page</a>.</p></body></html>';
    exit;
}

$base = '../';

// Parse JSON fields
$objectives = json_decode($project['objectives'], true) ?? [];
$targetGroups = json_decode($project['target_groups'], true) ?? [];
$timeline = json_decode($project['timeline'], true) ?? [];
$gallery = json_decode($project['gallery_images'], true) ?? [];
$relatedProjects = json_decode($project['related_projects'], true) ?? [];

// Fetch related data from dedicated tables
$donors = getRows('project_donors', ['project_id' => $project['id']], 'sort_order ASC');

// Fetch related projects
$relProjects = [];
if (!empty($relatedProjects)) {
    foreach ($relatedProjects as $relId) {
        $rel = getRow('projects', ['id' => (int)$relId]);
        if ($rel) $relProjects[] = $rel;
    }
}
// If no related projects specified, get other active projects
if (empty($relProjects)) {
    $relProjects = getRows('projects', "id != {$project['id']} AND is_active = 1 ORDER BY sort_order ASC LIMIT 3");
}

// Fetch project resources
$resources = getRows('resources', ['project_id' => $project['id']], 'sort_order ASC');

$pageTitle = sanitize($project['title']) . ' | WRC Projects';
$metaDescription = sanitize($project['description']);

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

  <!-- BREADCRUMBS -->
  <div class="bg-slate-100 border-b border-slate-200/60 py-3">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <nav class="flex items-center gap-2 text-xs text-slate-500">
        <a href="<?= $base ?>index.php" class="hover:text-brandRed transition">Home</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <a href="index.php" class="hover:text-brandRed transition">Projects</a>
        <i class="fa-solid fa-chevron-right text-[8px]"></i>
        <span class="text-slate-900 font-medium"><?= sanitize($project['title']) ?></span>
      </nav>
    </div>
  </div>

  <!-- PROJECT HERO -->
  <section class="relative bg-slate-900 text-white min-h-[420px] flex items-end py-12 overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?= $base ?><?= $project['hero_image'] ?>');"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/70 to-slate-900/30"></div>
    <div class="absolute inset-0 bg-brandRed/10 mix-blend-overlay"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pb-8">
      <div class="max-w-4xl">
        <div class="flex flex-wrap items-center gap-2.5 mb-5">
          <span class="bg-emerald-500 text-white font-bold text-[10px] uppercase tracking-wider px-3 py-1 rounded-full flex items-center gap-1.5 shadow-lg shadow-emerald-500/30">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> <?= ucfirst($project['status']) ?>
          </span>
          <span class="text-white/90 bg-white/10 backdrop-blur-md border border-white/20 font-medium text-[10px] uppercase tracking-wider px-3 py-1 rounded-full flex items-center gap-1.5">
            <i class="fa-solid fa-location-dot"></i> <?= sanitize($project['location_details']) ?>
          </span>
        </div>
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold font-display text-white mb-4 leading-tight max-w-3xl">
          <?= sanitize($project['title']) ?>
        </h1>
        <p class="text-slate-300 text-sm md:text-base max-w-2xl leading-relaxed">
          <?= sanitize($project['description']) ?>
        </p>
      </div>
    </div>
  </section>

  <!-- PROJECT SNAPSHOT GRID -->
  <section class="bg-white border-b border-slate-200/80 py-0">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-slate-100">
        <div class="px-6 py-6 text-center group hover:bg-slate-50 transition-colors">
          <div class="w-11 h-11 rounded-xl bg-red-50 text-brandRed flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-calendar-days text-lg"></i>
          </div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Duration</span>
          <span class="text-sm font-bold text-slate-900"><?= sanitize($project['start_date'] ?? 'N/A') ?> - <?= sanitize($project['end_date'] ?? 'N/A') ?></span>
        </div>
        <div class="px-6 py-6 text-center group hover:bg-slate-50 transition-colors">
          <div class="w-11 h-11 rounded-xl bg-red-50 text-brandRed flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-users text-lg"></i>
          </div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Beneficiaries</span>
          <span class="text-sm font-bold text-slate-900"><?= number_format($project['beneficiaries_count']) ?></span>
        </div>
        <div class="px-6 py-6 text-center group hover:bg-slate-50 transition-colors">
          <div class="w-11 h-11 rounded-xl bg-red-50 text-brandRed flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-coins text-lg"></i>
          </div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Budget</span>
          <span class="text-sm font-bold text-slate-900"><?= sanitize($project['budget_currency'] ?? 'PKR') ?> <?= number_format($project['budget_amount'] ?? 0) ?></span>
        </div>
        <div class="px-6 py-6 text-center group hover:bg-slate-50 transition-colors">
          <div class="w-11 h-11 rounded-xl bg-red-50 text-brandRed flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
            <i class="fa-solid fa-handshake text-lg"></i>
          </div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Donors</span>
          <span class="text-sm font-bold text-slate-900"><?= !empty($donors) ? count($donors) : sanitize($project['donors_count'] ?? 'N/A') ?></span>
        </div>
      </div>
    </div>
  </section>

  <!-- PROJECT BODY -->
  <section class="py-14 lg:py-20 bg-slate-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14">

        <!-- Left Main Content -->
        <div class="lg:col-span-8 space-y-10">

          <!-- Project Overview -->
          <div class="space-y-6">
            <div>
              <span class="text-brandRed text-[11px] font-bold uppercase tracking-wider">Project Overview</span>
              <h2 class="text-2xl md:text-3xl font-extrabold font-display text-slate-900 mt-1">About This Project</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
              <div class="space-y-4 text-sm text-slate-600 leading-relaxed">
                <?= nl2br(sanitize($project['overview'] ?? $project['description'])) ?>
              </div>
              <?php if (!empty($project['overview_image'])): ?>
              <div class="rounded-2xl overflow-hidden shadow-lg border border-slate-200/60">
                <img src="<?= $base ?><?= $project['overview_image'] ?>" alt="<?= sanitize($project['title']) ?>" class="w-full h-64 md:h-full object-cover">
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Objectives -->
          <?php if (!empty($objectives)): ?>
          <div class="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-xl bg-brandRed text-white flex items-center justify-center">
                <i class="fa-solid fa-bullseye text-sm"></i>
              </div>
              <h3 class="text-xl font-bold font-display text-slate-900">Project Objectives</h3>
            </div>
            <div class="space-y-5">
              <?php foreach ($objectives as $i => $obj): ?>
              <div class="flex items-start gap-4 group">
                <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center text-sm font-bold font-sans shrink-0 group-hover:bg-brandRed transition-colors"><?= $i + 1 ?></div>
                <div>
                  <h4 class="font-bold text-slate-900 text-sm mb-1"><?= sanitize($obj['title'] ?? '') ?></h4>
                  <p class="text-xs text-slate-600 leading-relaxed"><?= sanitize($obj['description'] ?? '') ?></p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Target Groups -->
          <?php if (!empty($targetGroups)): ?>
          <div class="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-xl bg-brandRed text-white flex items-center justify-center">
                <i class="fa-solid fa-people-group text-sm"></i>
              </div>
              <h3 class="text-xl font-bold font-display text-slate-900">Target Groups</h3>
            </div>
            <div class="flex flex-wrap gap-3">
              <?php foreach ($targetGroups as $group): ?>
              <span class="inline-flex items-center gap-2 bg-red-50 border border-brandRed/15 text-brandRed text-xs font-semibold px-4 py-2.5 rounded-xl">
                <i class="<?= $group['icon'] ?? 'fa-solid fa-users' ?>"></i> <?= sanitize($group['name'] ?? $group) ?>
              </span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Timeline -->
          <?php if (!empty($timeline)): ?>
          <div class="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-3 mb-8">
              <div class="w-10 h-10 rounded-xl bg-brandRed text-white flex items-center justify-center">
                <i class="fa-solid fa-timeline text-sm"></i>
              </div>
              <h3 class="text-xl font-bold font-display text-slate-900">Project Timeline</h3>
            </div>
            <div class="relative">
              <div class="absolute left-5 top-2 bottom-2 w-0.5 bg-slate-200"></div>
              <?php foreach ($timeline as $i => $milestone): ?>
              <div class="relative flex items-start gap-5 mb-8 last:mb-0">
                <div class="w-10 h-10 rounded-full <?= $i === 0 ? 'bg-brandRed text-white shadow-lg shadow-brandRed/30' : 'bg-slate-900 text-white shadow-lg' ?> flex items-center justify-center text-xs font-bold shrink-0 z-10">
                  <i class="<?= $milestone['icon'] ?? 'fa-solid fa-circle' ?>"></i>
                </div>
                <div class="pt-1">
                  <span class="text-[10px] font-bold uppercase tracking-wider <?= $i === 0 ? 'text-brandRed' : 'text-slate-400' ?>"><?= sanitize($milestone['date'] ?? '') ?></span>
                  <h4 class="font-bold text-slate-900 text-sm mt-0.5"><?= sanitize($milestone['title'] ?? '') ?></h4>
                  <p class="text-xs text-slate-500 mt-1 leading-relaxed"><?= sanitize($milestone['description'] ?? '') ?></p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Gallery -->
          <?php if (!empty($gallery)): ?>
          <div class="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-xl bg-brandRed text-white flex items-center justify-center">
                <i class="fa-solid fa-images text-sm"></i>
              </div>
              <h3 class="text-xl font-bold font-display text-slate-900">Project Gallery</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3" id="project-gallery">
              <?php foreach ($gallery as $img): ?>
              <div class="gallery-item relative overflow-hidden rounded-xl cursor-pointer group aspect-[4/3]" data-src="<?= $base ?><?= $img['path'] ?>" data-caption="<?= sanitize($img['caption'] ?? '') ?>">
                <img src="<?= $base ?><?= $img['path'] ?>" alt="<?= sanitize($img['caption'] ?? 'Project image') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/40 transition-colors flex items-center justify-center">
                  <i class="fa-solid fa-expand text-white text-lg opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

        </div>

        <!-- Right Sidebar -->
        <div class="lg:col-span-4 space-y-6">

          <!-- Resource Downloads -->
          <?php if (!empty($resources)): ?>
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 mb-2">
              <i class="fa-solid fa-file-pdf text-brandRed"></i>
              <h4 class="font-bold text-slate-900 text-sm font-display">Resource Downloads</h4>
            </div>
            <div class="space-y-3">
              <?php foreach ($resources as $res): ?>
              <a href="<?= $base ?><?= $res['file_path'] ?>" class="flex items-center justify-between p-3.5 bg-red-50 hover:bg-red-100 border border-brandRed/10 rounded-xl transition text-xs font-semibold text-slate-700 group" target="_blank">
                <span class="flex items-center gap-2.5"><i class="fa-solid fa-file-pdf text-brandRed"></i> <?= sanitize($res['title']) ?></span>
                <i class="fa-solid fa-download text-slate-400 group-hover:text-brandRed transition-colors"></i>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Project Donors -->
          <?php if (!empty($donors)): ?>
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 mb-2">
              <i class="fa-solid fa-handshake text-brandRed"></i>
              <h4 class="font-bold text-slate-900 text-sm font-display">Project Donors</h4>
            </div>
            <div class="space-y-3">
              <?php foreach ($donors as $donor): ?>
              <div class="flex items-center gap-3 p-3 bg-red-50 border border-brandRed/10 rounded-xl">
                <div class="w-10 h-10 rounded-lg bg-brandRed/10 flex items-center justify-center"><i class="fa-solid fa-building text-brandRed text-sm"></i></div>
                <div class="flex-1 min-w-0">
                  <span class="text-xs font-bold text-slate-900 block truncate"><?= sanitize($donor['donor_name']) ?></span>
                  <?php if (!empty($donor['donor_type'])): ?>
                  <span class="text-[10px] text-slate-500"><?= sanitize($donor['donor_type']) ?></span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($donor['amount'])): ?>
                <span class="text-[10px] font-bold text-green-700 whitespace-nowrap">$<?= number_format($donor['amount'], 0) ?></span>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Contact Project Office -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h4 class="font-bold text-slate-900 text-sm font-display flex items-center gap-2">
              <i class="fa-solid fa-circle-question text-slate-400"></i> Have Questions?
            </h4>
            <p class="text-slate-600 text-xs leading-relaxed">
              For partnership inquiries, media requests, or reporting related to this project, please reach out to our project coordination team.
            </p>
            <a href="<?= $base ?>contact/index.php" class="block w-full text-center border-2 border-slate-200 hover:border-brandRed hover:bg-red-50 text-slate-700 text-xs font-bold py-3 rounded-xl transition uppercase tracking-wider">
              Contact Project Office
            </a>
          </div>

        </div>

      </div>
    </div>
  </section>

  <!-- RELATED PROJECTS -->
  <?php if (!empty($relProjects)): ?>
  <section class="py-16 bg-white border-t border-slate-200/60">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <span class="text-brandRed text-[11px] font-bold uppercase tracking-wider">More Projects</span>
        <h2 class="text-2xl md:text-3xl font-extrabold font-display text-slate-900 mt-1">Related Projects</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($relProjects as $rel): ?>
        <a href="project-name.php?slug=<?= $rel['slug'] ?>" class="group bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
          <div class="relative h-48 overflow-hidden">
            <img src="<?= $base ?><?= $rel['hero_image'] ?>" alt="<?= sanitize($rel['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <div class="absolute top-3 left-3 flex gap-2">
              <span class="bg-emerald-500 text-white text-[9px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full"><?= ucfirst($rel['status']) ?></span>
            </div>
          </div>
          <div class="p-5">
            <div class="flex items-center gap-2 mb-2">
              <i class="fa-solid fa-folder text-brandRed text-[11px]"></i>
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Project</span>
            </div>
            <h3 class="font-bold text-slate-900 text-sm mb-2 group-hover:text-brandRed transition-colors"><?= sanitize($rel['title']) ?></h3>
            <p class="text-xs text-slate-500 leading-relaxed line-clamp-2"><?= sanitize($rel['description']) ?></p>
            <div class="mt-4 flex items-center gap-1 text-brandRed text-xs font-semibold">
              View Project <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>

<!-- LIGHTBOX MODAL -->
<div id="lightbox-modal" class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm hidden items-center justify-center p-4" role="dialog" aria-modal="true">
  <button id="lightbox-close" class="absolute top-4 right-4 text-white/70 hover:text-white text-2xl z-10 transition-colors" aria-label="Close lightbox">
    <i class="fa-solid fa-xmark"></i>
  </button>
  <button id="lightbox-prev" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white text-2xl z-10 transition-colors" aria-label="Previous image">
    <i class="fa-solid fa-chevron-left"></i>
  </button>
  <button id="lightbox-next" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white text-2xl z-10 transition-colors" aria-label="Next image">
    <i class="fa-solid fa-chevron-right"></i>
  </button>
  <div class="max-w-5xl max-h-[85vh] flex flex-col items-center gap-4">
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[75vh] object-contain rounded-lg">
    <p id="lightbox-caption" class="text-white/80 text-sm text-center"></p>
  </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
  (function(){
    var modal = document.getElementById('lightbox-modal');
    var img = document.getElementById('lightbox-img');
    var caption = document.getElementById('lightbox-caption');
    var items = document.querySelectorAll('.gallery-item');
    var current = 0;

    function openLightbox(index) {
      current = index;
      var item = items[current];
      img.src = item.dataset.src || '';
      img.alt = item.querySelector('img') ? item.querySelector('img').alt : '';
      caption.textContent = item.dataset.caption || '';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = '';
    }

    items.forEach(function(item, i) {
      item.addEventListener('click', function() { openLightbox(i); });
    });

    document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
    document.getElementById('lightbox-prev').addEventListener('click', function() { current = (current - 1 + items.length) % items.length; openLightbox(current); });
    document.getElementById('lightbox-next').addEventListener('click', function() { current = (current + 1) % items.length; openLightbox(current); });
    modal.addEventListener('click', function(e) { if (e.target === modal) closeLightbox(); });
    document.addEventListener('keydown', function(e) {
      if (modal.classList.contains('hidden')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft') { current = (current - 1 + items.length) % items.length; openLightbox(current); }
      if (e.key === 'ArrowRight') { current = (current + 1) % items.length; openLightbox(current); }
    });
  })();
</script>
