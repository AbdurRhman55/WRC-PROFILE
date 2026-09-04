<?php
$activeFile = basename($_SERVER['PHP_SELF']);
$activePageKey = $_GET['page'] ?? '';
$isPagesSection = ($activeFile === 'pages.php');

$navGroups = [
    ['label' => '', 'items' => [
        ['file' => 'index.php', 'label' => 'Dashboard', 'icon' => 'fa-gauge'],
    ]],
    ['label' => 'Content', 'items' => [
        ['file' => 'projects.php', 'label' => 'Projects', 'icon' => 'fa-folder-open'],
        ['file' => 'news.php', 'label' => 'News & Media', 'icon' => 'fa-newspaper'],
        ['file' => 'success-stories.php', 'label' => 'Success Stories', 'icon' => 'fa-quote-left'],
        ['file' => 'resources.php', 'label' => 'Resources', 'icon' => 'fa-book'],
        ['file' => 'hero-sliders.php', 'label' => 'Hero Sliders', 'icon' => 'fa-images'],
    ]],
    ['label' => 'Media', 'items' => [
        ['file' => 'events.php', 'label' => 'Events', 'icon' => 'fa-calendar-days'],
        ['file' => 'photos.php', 'label' => 'Photo Gallery', 'icon' => 'fa-camera-retro'],
        ['file' => 'videos.php', 'label' => 'Video Gallery', 'icon' => 'fa-video'],
    ]],
    ['label' => 'People', 'items' => [
        ['file' => 'team.php', 'label' => 'Team Members', 'icon' => 'fa-users'],
        ['file' => 'partners.php', 'label' => 'Partners', 'icon' => 'fa-handshake'],
        ['file' => 'jobs.php', 'label' => 'Job Listings', 'icon' => 'fa-briefcase'],
    ]],
    ['label' => 'Impact', 'items' => [
        ['file' => 'sdg-goals.php', 'label' => 'SDG Goals', 'icon' => 'fa-globe'],
    ]],
    ['label' => 'Locations', 'items' => [
        ['file' => 'provinces.php', 'label' => 'Provinces', 'icon' => 'fa-map-location-dot'],
    ]],
    ['label' => 'Fundraising', 'items' => [
        ['file' => 'campaigns.php', 'label' => 'Campaigns', 'icon' => 'fa-hand-holding-heart'],
        ['file' => 'tiers.php', 'label' => 'Donation Tiers', 'icon' => 'fa-coins'],
        ['file' => 'bank-accounts.php', 'label' => 'Bank Accounts', 'icon' => 'fa-building-columns'],
    ]],
    ['label' => 'Engagement', 'items' => [
        ['file' => 'submissions.php', 'label' => 'Contact Submissions', 'icon' => 'fa-envelope-open-text'],
        ['file' => 'subscribers.php', 'label' => 'Newsletter', 'icon' => 'fa-mail-bulk'],
    ]],
    ['label' => 'Config', 'items' => [
        ['file' => 'settings.php', 'label' => 'Site Settings', 'icon' => 'fa-gear'],
    ]],
];

$pagesList = [
    'homepage'       => ['label' => 'Homepage', 'sections' => 3],
    'our-work'       => ['label' => 'Our Work', 'sections' => 6],
    'projects'       => ['label' => 'Projects Listing', 'sections' => 3],
    'causes'         => ['label' => 'Causes / Donate', 'sections' => 2],
    'project-detail' => ['label' => 'Project Detail', 'sections' => 1],
    'about'          => ['label' => 'About', 'sections' => 5],
    'who-we-are'     => ['label' => 'Who We Are', 'sections' => 3],
    'history'        => ['label' => 'History / Timeline', 'sections' => 3],
    'team'           => ['label' => 'Team', 'sections' => 1],
    'leadership'     => ['label' => 'Leadership', 'sections' => 1],
    'vision-mission-values' => ['label' => 'Vision, Mission & Values', 'sections' => 2],
    'organizational-structure' => ['label' => 'Organizational Structure', 'sections' => 2],
    'policies-compliance' => ['label' => 'Policies & Compliance', 'sections' => 2],
    'annual-reports' => ['label' => 'Annual Reports', 'sections' => 2],
    'news'           => ['label' => 'News & Media', 'sections' => 1],
    'resources'      => ['label' => 'Resources', 'sections' => 1],
    'get-involved'   => ['label' => 'Get Involved', 'sections' => 2],
    'contribute'     => ['label' => 'Contribute With Us', 'sections' => 4],
    'contact'        => ['label' => 'Contact Us', 'sections' => 2],
    'impact'         => ['label' => 'Impact', 'sections' => 2],
    'where-we-work'  => ['label' => 'Where We Work', 'sections' => 2],
];
?>

<style>
.pages-dropdown-toggle{cursor:pointer;transition:all .15s}
.pages-dropdown-toggle .chevron{transition:transform .2s ease;font-size:10px;color:#94a3b8}
.pages-dropdown-toggle.open .chevron{transform:rotate(180deg)}
.pages-submenu{overflow:hidden;transition:max-height .25s ease,opacity .2s ease;max-height:0;opacity:0}
.pages-submenu.open{max-height:600px;opacity:1}
.pages-sub-item{display:flex;align-items:center;gap:8px;padding:6px 12px 6px 40px;border-radius:8px;font-size:12px;font-weight:500;color:#64748b;transition:all .15s;text-decoration:none;position:relative}
.pages-sub-item:hover{background:#f1f5f9;color:#0e1f27}
.pages-sub-item.active{background:rgba(202,0,0,.06);color:#ca0000;font-weight:600}
.pages-sub-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:18px;background:#ca0000;border-radius:0 4px 4px 0}
.pages-sub-item .badge{margin-left:auto;font-size:10px;padding:1px 6px;border-radius:9999px;background:#f1f5f9;color:#94a3b8;font-weight:600}
.pages-sub-item.active .badge{background:rgba(202,0,0,.1);color:#ca0000}
</style>

<aside id="sidebar" class="fixed top-16 left-0 bottom-0 w-64 bg-white border-r border-slate-200 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto">
  <nav class="p-3 space-y-4">
    <?php foreach ($navGroups as $group): ?>
    <div>
      <?php if ($group['label']): ?>
      <p class="px-3 mb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= $group['label'] ?></p>
      <?php endif; ?>
      <div class="space-y-0.5">
        <?php foreach ($group['items'] as $item): ?>
        <?php $active = ($activeFile === $item['file']); ?>
        <a href="<?= $item['file'] ?>"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all <?= $active ? 'bg-brandRed text-white shadow-md shadow-brandRed/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
          <i class="fa-solid <?= $item['icon'] ?> w-4 text-center text-[12px] <?= $active ? 'text-white' : 'text-slate-400' ?>"></i>
          <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- PAGES DROPDOWN -->
    <div>
      <p class="px-3 mb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pages</p>
      <div class="space-y-0.5">
        <button onclick="togglePagesDropdown()" id="pagesToggle"
           class="pages-dropdown-toggle w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all <?= $isPagesSection ? 'bg-brandRed text-white shadow-md shadow-brandRed/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
          <i class="fa-solid fa-layer-group w-4 text-center text-[12px] <?= $isPagesSection ? 'text-white' : 'text-slate-400' ?>"></i>
          <span>All Pages</span>
          <i class="fa-solid fa-chevron-down chevron ml-auto <?= $isPagesSection ? 'text-white/70' : '' ?>"></i>
        </button>
        <div id="pagesSubmenu" class="pages-submenu <?= $isPagesSection ? 'open' : '' ?>">
          <?php foreach ($pagesList as $pKey => $pDef): ?>
          <a href="pages.php?page=<?= $pKey ?>" class="pages-sub-item <?= ($activePageKey === $pKey && $isPagesSection) ? 'active' : '' ?>">
            <?= $pDef['label'] ?>
            <span class="badge"><?= $pDef['sections'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </nav>

  <div class="sticky bottom-0 p-3 border-t border-slate-100 bg-white">
    <a href="../index.php" target="_blank" class="flex items-center gap-2 text-xs text-slate-400 hover:text-brandRed transition-colors">
      <i class="fa-solid fa-external-link"></i> View Public Site
    </a>
  </div>
</aside>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  sidebar.classList.toggle('-translate-x-full');
  overlay.classList.toggle('hidden');
}
document.getElementById('sidebar-toggle')?.addEventListener('click', toggleSidebar);

function togglePagesDropdown() {
  const submenu = document.getElementById('pagesSubmenu');
  const toggle = document.getElementById('pagesToggle');
  submenu.classList.toggle('open');
  toggle.classList.toggle('open');
}
</script>
