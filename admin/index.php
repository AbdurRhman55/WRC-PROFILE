<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Dashboard';

$totalProjects = count(getRows('projects'));
$totalNews = count(getRows('news_articles', [], '', 0));
$totalResources = count(getRows('resources'));
$totalTeam = count(getRows('team_members'));
$totalCampaigns = count(getRows('campaigns'));
$totalPartners = count(getRows('partners'));
$totalSliders = count(getRows('hero_sliders'));
$totalSubmissions = count(getRows('contact_submissions', [], '', 0));
$totalSubscribers = count(getRows('newsletter_subscribers', [], '', 0));
$newSubmissions = count(getRows('contact_submissions', ['status' => 'new'], '', 0));

$recentNews = getRows('news_articles', [], 'created_at DESC', 5);
$recentProjects = getRows('projects', [], 'created_at DESC', 5);
$recentSubmissions = getRows('contact_submissions', [], 'submitted_at DESC', 5);

include __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div class="relative bg-gradient-to-r from-slate-900 via-slate-900 to-ink-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-8 overflow-hidden border border-slate-800">
  <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-brandRed/10 rounded-full blur-3xl pointer-events-none"></div>
  <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
      <div class="flex items-center gap-2 mb-2">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs text-emerald-400 font-semibold tracking-wider uppercase font-mono">System Live</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-extrabold font-display text-white tracking-tight">
        Welcome back, <?= htmlspecialchars($adminName) ?> 👋
      </h1>
      <p class="text-slate-300 text-sm mt-1.5 max-w-xl">
        Here is your core management summary. You have <span class="text-brandRed font-semibold"><?= $newSubmissions ?> new contact <?= $newSubmissions === 1 ? 'submission' : 'submissions' ?></span> awaiting review.
      </p>
    </div>
    <div class="flex items-center gap-3 shrink-0">
      <a href="pages.php" class="px-5 py-2.5 rounded-xl bg-brandRed hover:bg-brandRed-hover text-white text-xs font-bold uppercase tracking-wider shadow-lg shadow-brandRed/30 transition-all flex items-center gap-2">
        <i class="fa-solid fa-layer-group"></i> Manage Pages
      </a>
      <a href="../index.php" target="_blank" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-md border border-white/15 transition-all flex items-center gap-2">
        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> View Site
      </a>
    </div>
  </div>
</div>

<!-- Primary Key Metric Cards (4 Core Important Cards) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

  <!-- Projects Card -->
  <a href="projects.php" class="bg-white rounded-3xl border border-slate-200/80 p-6 hover:shadow-xl hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between relative overflow-hidden">
    <div class="h-1 w-full bg-emerald-500 absolute top-0 left-0"></div>
    <div class="flex items-center justify-between mb-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
        <i class="fa-solid fa-folder-open text-lg"></i>
      </div>
      <span class="text-xs text-slate-400 group-hover:text-emerald-600 font-medium flex items-center gap-1 transition-colors">
        Manage <i class="fa-solid fa-chevron-right text-[9px]"></i>
      </span>
    </div>
    <div>
      <p class="text-3xl font-extrabold font-display text-slate-900 tracking-tight"><?= $totalProjects ?></p>
      <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wider">Active Projects</p>
    </div>
  </a>

  <!-- Campaigns Card -->
  <a href="campaigns.php" class="bg-white rounded-3xl border border-slate-200/80 p-6 hover:shadow-xl hover:shadow-rose-500/10 hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between relative overflow-hidden">
    <div class="h-1 w-full bg-rose-500 absolute top-0 left-0"></div>
    <div class="flex items-center justify-between mb-4">
      <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors duration-300">
        <i class="fa-solid fa-hand-holding-heart text-lg"></i>
      </div>
      <span class="text-xs text-slate-400 group-hover:text-rose-600 font-medium flex items-center gap-1 transition-colors">
        Manage <i class="fa-solid fa-chevron-right text-[9px]"></i>
      </span>
    </div>
    <div>
      <p class="text-3xl font-extrabold font-display text-slate-900 tracking-tight"><?= $totalCampaigns ?></p>
      <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wider">Campaigns</p>
    </div>
  </a>

  <!-- News Articles Card -->
  <a href="news.php" class="bg-white rounded-3xl border border-slate-200/80 p-6 hover:shadow-xl hover:shadow-purple-500/10 hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between relative overflow-hidden">
    <div class="h-1 w-full bg-purple-500 absolute top-0 left-0"></div>
    <div class="flex items-center justify-between mb-4">
      <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
        <i class="fa-solid fa-newspaper text-lg"></i>
      </div>
      <span class="text-xs text-slate-400 group-hover:text-purple-600 font-medium flex items-center gap-1 transition-colors">
        Manage <i class="fa-solid fa-chevron-right text-[9px]"></i>
      </span>
    </div>
    <div>
      <p class="text-3xl font-extrabold font-display text-slate-900 tracking-tight"><?= $totalNews ?></p>
      <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wider">News Articles</p>
    </div>
  </a>

  <!-- Inquiries / Submissions Card -->
  <a href="submissions.php" class="bg-white rounded-3xl border border-slate-200/80 p-6 hover:shadow-xl hover:shadow-amber-500/10 hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between relative overflow-hidden">
    <div class="h-1 w-full bg-amber-500 absolute top-0 left-0"></div>
    <div class="flex items-center justify-between mb-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
        <i class="fa-solid fa-envelope-open-text text-lg"></i>
      </div>
      <?php if ($newSubmissions > 0): ?>
        <span class="px-2.5 py-0.5 rounded-full bg-brandRed text-white text-[10px] font-extrabold uppercase tracking-wider animate-pulse shadow-sm">
          <?= $newSubmissions ?> New
        </span>
      <?php else: ?>
        <span class="text-xs text-slate-400 group-hover:text-amber-600 font-medium flex items-center gap-1 transition-colors">
          Manage <i class="fa-solid fa-chevron-right text-[9px]"></i>
        </span>
      <?php endif; ?>
    </div>
    <div>
      <p class="text-3xl font-extrabold font-display text-slate-900 tracking-tight"><?= $totalSubmissions ?></p>
      <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wider">Contact Inquiries</p>
    </div>
  </a>

</div>

<!-- Consolidated Secondary Overview Bar -->
<div class="bg-white rounded-3xl border border-slate-200/80 p-5 mb-8 shadow-sm">
  <div class="flex flex-wrap items-center justify-between gap-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
    <a href="team.php" class="flex-1 min-w-[140px] pt-3 sm:pt-0 text-center hover:opacity-80 transition-opacity">
      <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Team Members</p>
      <p class="text-xl font-bold text-slate-900 mt-0.5 flex items-center justify-center gap-1.5">
        <i class="fa-solid fa-users text-brandRed text-sm"></i> <?= $totalTeam ?>
      </p>
    </a>
    <a href="resources.php" class="flex-1 min-w-[140px] pt-3 sm:pt-0 text-center hover:opacity-80 transition-opacity">
      <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Resources</p>
      <p class="text-xl font-bold text-slate-900 mt-0.5 flex items-center justify-center gap-1.5">
        <i class="fa-solid fa-book text-amber-500 text-sm"></i> <?= $totalResources ?>
      </p>
    </a>
    <a href="hero-sliders.php" class="flex-1 min-w-[140px] pt-3 sm:pt-0 text-center hover:opacity-80 transition-opacity">
      <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Hero Sliders</p>
      <p class="text-xl font-bold text-slate-900 mt-0.5 flex items-center justify-center gap-1.5">
        <i class="fa-solid fa-images text-blue-500 text-sm"></i> <?= $totalSliders ?>
      </p>
    </a>
    <a href="partners.php" class="flex-1 min-w-[140px] pt-3 sm:pt-0 text-center hover:opacity-80 transition-opacity">
      <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Partners</p>
      <p class="text-xl font-bold text-slate-900 mt-0.5 flex items-center justify-center gap-1.5">
        <i class="fa-solid fa-handshake text-teal-500 text-sm"></i> <?= $totalPartners ?>
      </p>
    </a>
    <a href="subscribers.php" class="flex-1 min-w-[140px] pt-3 sm:pt-0 text-center hover:opacity-80 transition-opacity">
      <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Subscribers</p>
      <p class="text-xl font-bold text-slate-900 mt-0.5 flex items-center justify-center gap-1.5">
        <i class="fa-solid fa-mail-bulk text-indigo-500 text-sm"></i> <?= $totalSubscribers ?>
      </p>
    </a>
  </div>
</div>

<!-- Bottom Activity Grid (3 Column UI) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

  <!-- Quick Actions Panel -->
  <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
          <i class="fa-solid fa-bolt text-amber-500"></i> Quick Actions
        </h2>
        <span class="text-[11px] text-slate-400 font-medium">Shortcuts</span>
      </div>
      <div class="space-y-3">
        <a href="projects.php" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50/80 hover:bg-emerald-50/80 border border-slate-100 hover:border-emerald-200/80 transition-all group">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100/70 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-plus text-xs"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-slate-900">Add Project</p>
              <p class="text-[11px] text-slate-400">Create operational project</p>
            </div>
          </div>
          <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all"></i>
        </a>

        <a href="news.php" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50/80 hover:bg-purple-50/80 border border-slate-100 hover:border-purple-200/80 transition-all group">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100/70 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-plus text-xs"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-slate-900">Publish Article</p>
              <p class="text-[11px] text-slate-400">Add latest news & story</p>
            </div>
          </div>
          <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-purple-500 group-hover:translate-x-1 transition-all"></i>
        </a>

        <a href="settings.php" class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50/80 hover:bg-slate-100 border border-slate-100 transition-all group">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-200/70 text-slate-700 flex items-center justify-center group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-gear text-xs"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-slate-900">Site Settings</p>
              <p class="text-[11px] text-slate-400">Global site configuration</p>
            </div>
          </div>
          <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-slate-600 group-hover:translate-x-1 transition-all"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- Recent Submissions Panel -->
  <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
          <i class="fa-solid fa-inbox text-amber-500"></i> Recent Inquiries
        </h2>
        <a href="submissions.php" class="text-xs text-brandRed font-bold hover:underline flex items-center gap-1">
          View All <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
      </div>
      <?php if (empty($recentSubmissions)): ?>
        <div class="text-center py-10">
          <i class="fa-solid fa-envelope-open text-3xl text-slate-300 mb-2"></i>
          <p class="text-xs text-slate-400">No contact submissions yet.</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($recentSubmissions as $sub): ?>
            <div class="flex items-start gap-3 p-3 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
              <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-user text-xs"></i>
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-sm font-bold text-slate-900 truncate"><?= htmlspecialchars($sub['full_name']) ?></p>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider <?= $sub['status'] === 'new' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' ?>">
                    <?= htmlspecialchars($sub['status']) ?>
                  </span>
                </div>
                <p class="text-xs text-slate-500 truncate mt-0.5"><?= htmlspecialchars($sub['subject'] ?: 'No subject') ?></p>
                <p class="text-[10px] text-slate-400 mt-1"><?= date('M d, Y • h:i A', strtotime($sub['submitted_at'] ?? $sub['created_at'] ?? 'now')) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent News Panel -->
  <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
          <i class="fa-solid fa-newspaper text-purple-500"></i> Recent News
        </h2>
        <a href="news.php" class="text-xs text-brandRed font-bold hover:underline flex items-center gap-1">
          View All <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
      </div>
      <?php if (empty($recentNews)): ?>
        <div class="text-center py-10">
          <i class="fa-solid fa-newspaper text-3xl text-slate-300 mb-2"></i>
          <p class="text-xs text-slate-400">No news articles published yet.</p>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($recentNews as $article): ?>
            <div class="flex items-start gap-3 p-3 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
              <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-file-lines text-xs"></i>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-slate-900 truncate"><?= htmlspecialchars($article['title']) ?></p>
                <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                  <i class="fa-regular fa-clock text-[10px]"></i>
                  <?= date('M d, Y', strtotime($article['created_at'])) ?>
                </p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
