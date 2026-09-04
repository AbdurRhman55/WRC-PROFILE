<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$pageTitle = 'Our History | WRC About';
$metaDescription = 'Explore the history of World Relief Committee (WRC) - our journey from 2005 to today, milestones, growth, and lasting impact in humanitarian aid.';

// Fetch dedicated table data
$heroRow = getRow('history_hero');
$statsRow = getRow('history_stats');
$ctaRow = getRow('history_cta');
// Fetch projects from the projects table (admin-managed) joined with programs
global $pdo;
$stmt = $pdo->query("
    SELECT p.id, p.slug, p.title, p.status, p.start_date, p.end_date,
           p.beneficiaries_count, p.location_details, p.description, p.hero_image
    FROM projects p
    WHERE p.is_active = 1 AND p.show_in_history = 1 AND p.status IN ('active', 'completed')
    ORDER BY p.start_date ASC
");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build timeline cards - one per project
$timelineCards = [];
foreach ($projects as $index => $p) {
    $year = !empty($p['start_date']) ? date('Y', strtotime($p['start_date'])) : '';
    if (empty($year)) continue;
    $statusColors = ['active' => '#16a34a', 'completed' => '#64748b', 'upcoming' => '#2563eb'];
    $tagColor = $statusColors[strtolower($p['status'] ?? 'active')] ?? '#ca0000';
    $tag = ucfirst($p['status'] ?? 'Active');
    $benefText = !empty($p['beneficiaries_count']) ? number_format($p['beneficiaries_count']) . '+ beneficiaries' : '';
    $timelineCards[] = [
        'year' => $year,
        'title' => $p['title'],
        'description' => $p['description'] ?? '',
        'image' => $p['hero_image'] ?? '',
        'tag' => $tag,
        'tag_color' => $tagColor,
        'stats_text' => $benefText,
        'sort_order' => $index,
        'location' => $p['location_details'] ?? '',
        'slug' => $p['slug'] ?? '',
    ];
}

// Build data arrays
$heroData = $heroRow ?: [];
$statsItems = [];
if ($statsRow) {
    $statsRows = getRows('history_stats_items', ['stats_id' => $statsRow['id']], 'sort_order ASC');
    $statsItems = array_map(function($s) { return ['icon' => $s['stat_icon'] ?? '', 'color' => $s['stat_color'] ?? '', 'label' => $s['stat_label'] ?? '', 'value' => $s['stat_value'] ?? '']; }, $statsRows);
}
$ctaData = $ctaRow ?: [];

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

    <!-- Hero Section -->
    <section class="relative w-full bg-ink-900 text-white min-h-[520px] flex items-center overflow-hidden">
      <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero.jpg' ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-ink-900 via-ink-900/80 to-ink-900/40"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-transparent to-ink-900/50"></div>
      </div>

      <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="max-w-7xl mx-auto">
          <!-- Breadcrumb -->
          <nav class="flex items-center gap-2 text-sm text-white/60 mb-8" aria-label="Breadcrumb">
            <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a href="<?= $base ?>about/index.php" class="hover:text-white transition-colors">About Us</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-white font-medium">Our History</span>
          </nav>

          <?php
            $rawTitle = $heroData['section_title'] ?? '';
            if (!empty($rawTitle)) {
                $words = explode(' ', $rawTitle);
                $lastWord = array_pop($words);
                $title = sanitize(implode(' ', $words)) . ' <span class="text-brandRed">' . sanitize($lastWord) . '</span>';
            } else {
                $title = 'Our History of <span class="text-brandRed">Impact</span>';
            }
          ?>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-6 max-w-3xl">
            <?= $title ?>
          </h1>
          <p class="text-lg md:text-xl text-sand-50/80 max-w-2xl mb-10 leading-relaxed">
            <?= sanitize($heroData['section_subtitle'] ?? $heroData['subtitle'] ?? 'Two decades of dedicated service, growth, and unwavering commitment to vulnerable communities worldwide.') ?>
          </p>
          <div class="flex flex-col sm:flex-row gap-4">
            <a href="<?= $base ?><?= $heroData['button1_link'] ?? 'about/who-we-are.php' ?>" class="px-8 py-4 rounded-xl bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm shadow-lg shadow-brandRed/30 transition-all uppercase tracking-wider text-center">
              <?= sanitize($heroData['button1_text'] ?? 'Who We Are') ?>
            </a>
            <a href="<?= $base ?><?= $heroData['button2_link'] ?? 'impact/index.php' ?>" class="px-8 py-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm transition-all uppercase tracking-wider text-center">
              <?= sanitize($heroData['button2_text'] ?? 'Our Impact') ?>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Timeline Section -->
    <section class="py-16 lg:py-24 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <span class="text-brandRed font-bold text-[10px] tracking-widest uppercase">Milestones</span>
          <h2 class="text-3xl lg:text-4xl font-display font-bold text-slate-900 mt-3 mb-4">Our Journey Through the Years</h2>
          <p class="text-slate-600">A timeline of key moments that have shaped who we are today.</p>
        </div>

        <div class="relative">
          <!-- Center Line -->
          <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-0.5 bg-slate-200 -translate-x-1/2"></div>

          <?php foreach ($timelineCards as $index => $milestone): ?>
            <?php
              $isLeft = ($milestone['sort_order'] % 2) !== 0;
              $isLast = ($index === count($timelineCards) - 1);
              $tagColor = !empty($milestone['tag_color']) ? $milestone['tag_color'] : '#ca0000';
            ?>
            <div class="relative flex flex-col md:flex-row items-center mb-12 md:mb-16 <?= $isLeft ? '' : 'md:flex-row-reverse' ?>">

              <!-- Content Card -->
              <div class="w-full md:w-[calc(50%-2rem)] <?= $isLeft ? 'md:text-right md:pr-12' : 'md:text-left md:pl-12' ?>">
                <div class="<?= $isLast ? 'bg-ink-900 text-white border-ink-700' : 'bg-white border-slate-100' ?> rounded-2xl border overflow-hidden hover:shadow-lg transition-all">

                  <?php if (!empty($milestone['image'])): ?>
                  <div class="relative h-48 overflow-hidden">
                    <img src="<?= $base ?><?= $milestone['image'] ?>" alt="<?= sanitize($milestone['title']) ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <?php if (!empty($milestone['tag'])): ?>
                    <span class="absolute top-4 <?= $isLeft ? 'right-4' : 'left-4' ?> px-3 py-1 rounded-full text-xs font-bold text-white shadow-lg" style="background-color: <?= $tagColor ?>;">
                      <?= sanitize($milestone['tag']) ?>
                    </span>
                    <?php endif; ?>
                    <span class="absolute bottom-4 <?= $isLeft ? 'right-4' : 'left-4' ?> <?= $isLast ? 'bg-brandGold text-ink-900' : 'bg-white text-slate-900' ?> px-4 py-1.5 rounded-full text-sm font-bold shadow-lg">
                      <?= sanitize($milestone['year']) ?>
                    </span>
                  </div>
                  <?php endif; ?>

                  <div class="p-6 lg:p-8">
                    <?php if (empty($milestone['image'])): ?>
                    <div class="flex items-center gap-3 mb-4 <?= $isLeft ? 'md:justify-end' : '' ?>">
                      <?php if (!empty($milestone['tag'])): ?>
                      <span class="px-3 py-1 rounded-full text-xs font-bold text-white" style="background-color: <?= $tagColor ?>;">
                        <?= sanitize($milestone['tag']) ?>
                      </span>
                      <?php endif; ?>
                      <span class="<?= $isLast ? 'bg-brandGold text-ink-900' : 'bg-brandRed/10 text-brandRed' ?> px-4 py-1.5 rounded-full text-sm font-bold">
                        <?= sanitize($milestone['year']) ?>
                      </span>
                    </div>
                    <?php endif; ?>

                    <h3 class="text-xl font-display font-bold <?= $isLast ? 'text-white' : 'text-slate-900' ?> mb-3">
                      <?= sanitize($milestone['title']) ?>
                    </h3>
                    <p class="<?= $isLast ? 'text-sand-50/70' : 'text-slate-600' ?> leading-relaxed text-sm mb-4">
                      <?= sanitize($milestone['description']) ?>
                    </p>
                    <?php if (!empty($milestone['location'])): ?>
                    <div class="flex items-center gap-1.5 text-xs <?= $isLast ? 'text-sand-50/60' : 'text-slate-500' ?> mb-4 <?= $isLeft ? 'md:justify-end' : '' ?>">
                      <i class="fa-solid fa-location-dot text-[10px]"></i>
                      <?= sanitize($milestone['location']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($milestone['stats_text'])): ?>
                    <div class="<?= $isLast ? 'bg-white/10 border-white/10' : 'bg-brandRed/5 border-brandRed/10' ?> rounded-lg px-4 py-3 border">
                      <p class="<?= $isLast ? 'text-brandGold' : 'text-brandRed' ?> text-sm font-semibold flex items-center gap-2 <?= $isLeft ? 'md:justify-end' : '' ?>">
                        <i class="fa-solid fa-chart-line text-xs"></i>
                        <?= sanitize($milestone['stats_text']) ?>
                      </p>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- Center Circle -->
              <div class="hidden md:flex absolute left-1/2 -translate-x-1/2 z-10">
                <div class="w-4 h-4 rounded-full <?= $isLast ? 'bg-brandGold' : 'bg-brandRed' ?> border-4 border-white shadow-md"></div>
              </div>

              <!-- Spacer for other side -->
              <div class="hidden md:block w-[calc(50%-2rem)]"></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Projects Chronological Table -->
    <?php if (!empty($projects)): ?>
    <section class="py-16 lg:py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
          <span class="text-brandRed font-bold text-[10px] tracking-widest uppercase">Our Projects</span>
          <h2 class="text-3xl lg:text-4xl font-display font-bold text-slate-900 mt-3 mb-4">Project Chronology</h2>
          <p class="text-slate-600">A chronological overview of our key projects and interventions across Pakistan.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
          <!-- Table Header -->
          <div class="hidden lg:grid lg:grid-cols-12 gap-4 px-6 py-4 bg-ink-900 text-white text-xs font-bold uppercase tracking-wider">
            <div class="col-span-1">Year</div>
            <div class="col-span-4">Project</div>
            <div class="col-span-3">Location</div>
            <div class="col-span-2">Beneficiaries</div>
            <div class="col-span-1">Status</div>
            <div class="col-span-1 text-right">View</div>
          </div>

          <!-- Table Rows -->
          <?php foreach ($projects as $idx => $project): ?>
            <?php
              $projectYear = !empty($project['start_date']) ? date('Y', strtotime($project['start_date'])) : '';
              $link = !empty($project['slug']) ? $base . 'projects/project-name.php?slug=' . urlencode($project['slug']) : '#';
              $projectStatus = ucfirst($project['status'] ?? 'active');
              $statusColor = match($projectStatus) {
                  'Active' => 'bg-green-100 text-green-700',
                  'Upcoming' => 'bg-blue-100 text-blue-700',
                  default => 'bg-slate-100 text-slate-600'
              };
              $benefText = !empty($project['beneficiaries_count']) ? number_format($project['beneficiaries_count']) . '+' : '';
            ?>
            <a href="<?= $link ?>" class="group block <?= $idx % 2 === 0 ? 'bg-white' : 'bg-slate-50' ?> hover:bg-brandRed/5 transition-colors border-b border-slate-100 last:border-b-0">
              <!-- Desktop Row -->
              <div class="hidden lg:grid lg:grid-cols-12 gap-4 px-6 py-5 items-center">
                <div class="col-span-1">
                  <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-ink-900 text-white font-display font-bold text-sm group-hover:bg-brandRed transition-colors">
                    <?= sanitize($projectYear) ?>
                  </span>
                </div>
                <div class="col-span-4">
                  <h4 class="font-display font-bold text-slate-900 group-hover:text-brandRed transition-colors text-sm">
                    <?= sanitize($project['title']) ?>
                  </h4>
                  <p class="text-xs text-slate-500 mt-1 line-clamp-1"><?= sanitize($project['description'] ?? '') ?></p>
                </div>
                <div class="col-span-3">
                  <span class="text-xs text-slate-600 flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot text-[10px] text-slate-400"></i>
                    <?= sanitize($project['location_details'] ?? '') ?>
                  </span>
                </div>
                <div class="col-span-2">
                  <span class="text-xs font-semibold text-slate-700"><?= sanitize($benefText) ?></span>
                </div>
                <div class="col-span-1">
                  <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold <?= $statusColor ?> uppercase tracking-wider">
                    <?= sanitize($projectStatus) ?>
                  </span>
                </div>
                <div class="col-span-1 text-right">
                  <i class="fa-solid fa-arrow-right text-slate-300 group-hover:text-brandRed group-hover:translate-x-1 transition-all"></i>
                </div>
              </div>

              <!-- Mobile Row -->
              <div class="lg:hidden px-4 py-4">
                <div class="flex items-start gap-4">
                  <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-ink-900 text-white font-display font-bold text-sm flex-shrink-0 group-hover:bg-brandRed transition-colors">
                    <?= sanitize($projectYear) ?>
                  </span>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <h4 class="font-display font-bold text-slate-900 text-sm truncate"><?= sanitize($project['title']) ?></h4>
                      <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold <?= $statusColor ?> uppercase tracking-wider flex-shrink-0">
                        <?= sanitize($projectStatus) ?>
                      </span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-500 mb-1">
                      <span class="flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-[10px] text-slate-400"></i>
                        <?= sanitize($project['location_details'] ?? '') ?>
                      </span>
                    </div>
                    <p class="text-xs text-slate-500 line-clamp-1"><?= sanitize($project['description'] ?? '') ?></p>
                    <div class="flex items-center justify-between mt-2">
                      <span class="text-xs font-semibold text-slate-700"><?= sanitize($benefText) ?></span>
                      <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 group-hover:text-brandRed transition-colors"></i>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- Key Stats Section -->
    <?php if (!empty($statsItems)): ?>
    <section class="py-16 lg:py-20 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
          <span class="text-brandRed font-bold text-[10px] tracking-widest uppercase">By the Numbers</span>
          <h2 class="text-3xl lg:text-4xl font-display font-bold text-slate-900 mt-3 mb-4">
            <?= sanitize($statsRow['section_title'] ?? 'Key Statistics') ?>
          </h2>
          <p class="text-slate-600">
            <?= sanitize($statsRow['section_subtitle'] ?? 'The measurable impact of our work across communities worldwide.') ?>
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <?php foreach ($statsItems as $stat): ?>
            <div class="text-center p-8 rounded-2xl bg-white border border-slate-100 hover:shadow-lg transition-all group">
              <div class="w-14 h-14 mx-auto mb-5 rounded-2xl <?= $stat['color'] ?? 'bg-brandRed/10' ?> flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fa-solid <?= $stat['icon'] ?? 'fa-chart-line' ?> text-xl text-brandRed"></i>
              </div>
              <h4 class="text-3xl lg:text-4xl font-display font-bold text-slate-900 mb-2">
                <?= sanitize($stat['value'] ?? '') ?>
              </h4>
              <p class="text-sm text-slate-500 font-medium">
                <?= sanitize($stat['label'] ?? '') ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- CTA Banner -->
    <section class="py-20 lg:py-28 bg-ink-900 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-[url('../assets/images/hero.jpg')] bg-cover bg-center mix-blend-multiply opacity-20"></div>
      <div class="container relative z-10 mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
          <span class="text-brandRed font-bold text-[10px] tracking-widest uppercase mb-6 inline-block">
            <?= sanitize($ctaData['badge'] ?? 'Get Involved') ?>
          </span>
          <h2 class="text-3xl md:text-5xl font-display font-bold text-white mb-6 leading-tight">
            <?= sanitize($ctaData['section_title'] ?? 'Be Part of Our Story') ?>
          </h2>
          <p class="text-sand-50/80 text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
            <?= sanitize($ctaData['section_subtitle'] ?? 'Join us in creating lasting change for communities around the world.') ?>
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="<?= $base ?><?= $ctaData['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>" class="px-8 py-4 rounded-xl bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm shadow-lg shadow-brandRed/30 transition-all uppercase tracking-wider">
              <?= sanitize($ctaData['button1_text'] ?? 'Donate Now') ?>
            </a>
            <a href="<?= $base ?>contact/index.php" class="px-8 py-4 rounded-xl bg-transparent border-2 border-white text-white font-bold text-sm hover:bg-white/10 transition-all uppercase tracking-wider">
              Contact Us
            </a>
          </div>
        </div>
      </div>
    </section>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
