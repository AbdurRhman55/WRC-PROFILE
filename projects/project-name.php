<?php
/**
 * WRC Single Project Detail (Premium Design)
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

$base = siteBaseUrl();

// Parse JSON fields (handle null values)
$objectives = json_decode($project['objectives'] ?? '', true) ?? [];
$targetGroups = json_decode($project['target_groups'] ?? '', true) ?? [];
$timeline = json_decode($project['timeline'] ?? '', true) ?? [];
$gallery = json_decode($project['gallery_images'] ?? '', true) ?? [];
$relatedProjects = json_decode($project['related_projects'] ?? '', true) ?? [];

// Fetch related data from dedicated tables
$donors = getRows('project_donors', ['project_id' => $project['id']], 'id ASC');

// Fetch related projects
$relProjects = [];
if (!empty($relatedProjects)) {
    foreach ($relatedProjects as $relId) {
        $rel = getRow('projects', ['id' => (int)$relId]);
        if ($rel) $relProjects[] = $rel;
    }
}
if (empty($relProjects)) {
    $relProjects = getRows('projects', "id != {$project['id']} AND is_active = 1 ORDER BY sort_order ASC LIMIT 3");
}

// Fetch project resources
$resources = getRows('resources', ['project_id' => $project['id']], 'sort_order ASC');

// Format dates
$startDate = !empty($project['start_date']) ? date('Y', strtotime($project['start_date'])) : 'N/A';
$endDate = !empty($project['end_date']) ? date('Y', strtotime($project['end_date'])) : 'Ongoing';
$projectPartner = !empty($donors) ? implode(', ', array_column($donors, 'donor_name')) : 'Welfare & Relief Committee';

// Status colors
$statusConfig = [
    'active' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-500', 'label' => 'Active'],
    'completed' => ['bg' => 'bg-slate-500', 'text' => 'text-slate-500', 'label' => 'Completed'],
    'upcoming' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-500', 'label' => 'Upcoming'],
];
$status = $statusConfig[strtolower($project['status'] ?? 'active')] ?? $statusConfig['active'];

$pageTitle = sanitize($project['title']) . ' | WRC Projects';
$metaDescription = sanitize($project['description']);

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="flex-grow">

    <!-- HERO SECTION - Cinematic Full-Screen -->
    <section class="relative min-h-[70vh] lg:min-h-[85vh] flex items-center overflow-hidden">
        <!-- Background Image with Parallax -->
        <div class="absolute inset-0 hero-parallax"
            style="background-image: url('<?= $base ?><?= $project['hero_image'] ?>');"></div>

        <!-- Multi-layer Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/40"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/60 to-transparent"></div>
        <div class="absolute inset-0 bg-brandRed/5 mix-blend-overlay"></div>

        <!-- Animated Particles (Decorative) -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-brandRed/30 rounded-full animate-float"></div>
            <div class="absolute top-1/3 right-1/3 w-1.5 h-1.5 bg-white/20 rounded-full animate-float-delayed"></div>
            <div class="absolute bottom-1/3 left-1/5 w-1 h-1 bg-brandRed/20 rounded-full animate-float"></div>
        </div>

        <!-- Content -->
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-12 lg:py-16">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-white/60 mb-6 animate-fade-in">
                <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <a href="index.php" class="hover:text-white transition-colors">Projects</a>
                <i class="fa-solid fa-chevron-right text-[8px]"></i>
                <span class="text-white font-medium"><?= sanitize($project['title']) ?></span>
            </nav>

            <div class="max-w-5xl">
                <!-- Status & Location Tags -->
                <div class="flex flex-wrap items-center gap-3 mb-4 animate-slide-up">
                    <span
                        class="<?= $status['bg'] ?> text-white font-bold text-[11px] uppercase tracking-wider px-4 py-1.5 rounded-full flex items-center gap-2 shadow-lg">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        <?= $status['label'] ?>
                    </span>
                    <?php if (!empty($project['category'])): ?>
                    <span
                        class="text-white/90 bg-brandRed/30 backdrop-blur-md border border-brandRed/40 font-medium text-[11px] uppercase tracking-wider px-4 py-1.5 rounded-full flex items-center gap-2">
                        <i class="fa-solid fa-folder text-[10px]"></i>
                        <?= sanitize($project['category']) ?>
                    </span>
                    <?php endif; ?>
                    <span
                        class="text-white/90 bg-white/10 backdrop-blur-md border border-white/20 font-medium text-[11px] uppercase tracking-wider px-4 py-1.5 rounded-full flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-[10px]"></i>
                        <?= sanitize($project['location_details']) ?>
                    </span>
                </div>

                <!-- Title -->
                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-extrabold font-display text-white mb-4 leading-[1.1] max-w-4xl animate-slide-up-delay">
                    <?= sanitize($project['title']) ?>
                </h1>

                <!-- Description -->
                <p class="text-slate-300 text-base md:text-lg max-w-3xl leading-relaxed mb-6 animate-fade-in-delay">
                    <?= sanitize($project['description']) ?>
                </p>

            </div>
        </div>
    </section>

    <!-- QUICK FACTS BAR - Sticky -->
    <section class="bg-white border-b border-slate-200/80 sticky top-20 z-40 quick-facts-bar">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-3 divide-x divide-slate-100">
                <div class="px-4 lg:px-6 py-5 text-center group hover:bg-slate-50/80 transition-colors cursor-default">
                    <div
                        class="w-11 h-11 rounded-xl bg-gradient-to-br from-red-50 to-red-100 text-brandRed flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-calendar-days text-lg"></i>
                    </div>
                    <span
                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Duration</span>
                    <span class="text-sm font-bold text-slate-900"><?= $startDate ?> — <?= $endDate ?></span>
                </div>
                <div class="px-4 lg:px-6 py-5 text-center group hover:bg-slate-50/80 transition-colors cursor-default">
                    <div
                        class="w-11 h-11 rounded-xl bg-gradient-to-br from-red-50 to-red-100 text-brandRed flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>
                    <span
                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Beneficiaries</span>
                    <span class="text-sm font-bold text-slate-900 counter-number"
                        data-target="<?= $project['beneficiaries_count'] ?? 0 ?>">0</span>
                </div>
                <div class="px-4 lg:px-6 py-5 text-center group hover:bg-slate-50/80 transition-colors cursor-default">
                    <div
                        class="w-11 h-11 rounded-xl bg-gradient-to-br from-red-50 to-red-100 text-brandRed flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-handshake text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Project
                        Partner</span>
                    <span class="text-sm font-bold text-slate-900"><?= sanitize($projectPartner) ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="py-16 lg:py-24 bg-slate-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">

                <!-- Left Main Content -->
                <div class="lg:col-span-8 space-y-12">

                    <!-- Project Overview - Magazine Layout -->
                    <div class="reveal-section">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-1 h-8 bg-brandRed rounded-full"></div>
                            <span class="text-brandRed text-[11px] font-bold uppercase tracking-widest">Project
                                Overview</span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-extrabold font-display text-slate-900 mb-8">About This
                            Project</h2>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-8 items-start">
                            <div class="md:col-span-3 space-y-5">
                                <div class="text-slate-600 leading-relaxed text-[15px] project-overview-text relative"
                                    id="overview-container">
                                    <div id="overview-text" class="line-clamp-3">
                                        <?= nl2br(sanitize($project['overview'] ?? $project['description'])) ?>
                                    </div>
                                    <button onclick="toggleOverview()" id="overview-toggle"
                                        class="mt-3 text-brandRed text-sm font-bold hover:text-brandRed-hover transition-colors inline-flex items-center gap-1.5">
                                        <span id="toggle-label">Read More</span>
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform"
                                            id="toggle-icon"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if (!empty($project['overview_image'])): ?>
                            <div class="md:col-span-2">
                                <div
                                    class="rounded-2xl overflow-hidden shadow-xl border border-slate-200/60 sticky top-32">
                                    <img src="<?= $base ?><?= $project['overview_image'] ?>"
                                        alt="<?= sanitize($project['title']) ?>"
                                        class="w-full h-64 md:h-80 object-cover">
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Progress Ring & Impact Stats -->
                    <?php if ($project['status'] === 'active'): ?>
                    <div
                        class="bg-gradient-to-br from-ink-900 to-ink-800 p-8 lg:p-10 rounded-3xl text-white reveal-section">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-1 h-8 bg-brandRed rounded-full"></div>
                            <span class="text-brandRed text-[11px] font-bold uppercase tracking-widest">Project
                                Impact</span>
                        </div>

                        <div class="grid grid-cols-1 gap-8 items-center">
                            <!-- Impact Numbers -->
                            <div class="grid grid-cols-2 gap-6">
                                <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-5 rounded-2xl">
                                    <i class="fa-solid fa-users text-brandRed text-xl mb-3"></i>
                                    <div class="text-2xl font-extrabold counter-number"
                                        data-target="<?= $project['beneficiaries_count'] ?? 0 ?>">0</div>
                                    <div class="text-white/60 text-xs uppercase tracking-wider mt-1">Beneficiaries</div>
                                </div>
                                <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-5 rounded-2xl">
                                    <i class="fa-solid fa-calendar-check text-brandRed text-xl mb-3"></i>
                                    <div class="text-2xl font-extrabold"><?= $startDate ?></div>
                                    <div class="text-white/60 text-xs uppercase tracking-wider mt-1">Started</div>
                                </div>
                                <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-5 rounded-2xl">
                                    <i class="fa-solid fa-map-marker-alt text-brandRed text-xl mb-3"></i>
                                    <div class="text-lg font-bold leading-tight">
                                        <?= sanitize($project['location_details'] ?? 'Pakistan') ?></div>
                                    <div class="text-white/60 text-xs uppercase tracking-wider mt-1">Location</div>
                                </div>
                                <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-5 rounded-2xl">
                                    <i class="fa-solid fa-clock text-brandRed text-xl mb-3"></i>
                                    <div class="text-lg font-bold"><?= $endDate ?></div>
                                    <div class="text-white/60 text-xs uppercase tracking-wider mt-1">Expected End</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Objectives - Numbered Cards -->
                    <?php if (!empty($objectives)): ?>
                    <div class="reveal-section">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-1 h-8 bg-brandRed rounded-full"></div>
                            <span class="text-brandRed text-[11px] font-bold uppercase tracking-widest">Project
                                Objectives</span>
                        </div>
                        <h2 class="text-3xl font-extrabold font-display text-slate-900 mb-8">What We Aim to Achieve</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <?php foreach ($objectives as $i => $obj): ?>
                            <div
                                class="group bg-white p-6 rounded-2xl border border-slate-200/80 hover:border-brandRed/30 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                                <div
                                    class="absolute top-0 left-0 w-1 h-full bg-brandRed scale-y-0 group-hover:scale-y-100 transition-transform origin-top">
                                </div>
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-11 h-11 rounded-xl bg-slate-900 group-hover:bg-brandRed text-white flex items-center justify-center text-sm font-bold font-sans shrink-0 transition-colors shadow-md">
                                        <?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?>
                                    </div>
                                    <div>
                                        <h4
                                            class="font-bold text-slate-900 group-hover:text-brandRed text-base mb-2 transition-colors">
                                            <?= sanitize($obj['title'] ?? '') ?></h4>
                                        <p class="text-sm text-slate-500 leading-relaxed">
                                            <?= sanitize($obj['description'] ?? '') ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Target Groups - Modern Pills -->
                    <?php if (!empty($targetGroups)): ?>
                    <div class="reveal-section">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-1 h-8 bg-brandRed rounded-full"></div>
                            <span class="text-brandRed text-[11px] font-bold uppercase tracking-widest">Target
                                Groups</span>
                        </div>
                        <h2 class="text-3xl font-extrabold font-display text-slate-900 mb-8">Who Benefits</h2>

                        <div class="flex flex-wrap gap-3">
                            <?php foreach ($targetGroups as $group): ?>
                            <span
                                class="group inline-flex items-center gap-3 bg-white border border-slate-200 hover:border-brandRed/30 text-slate-700 hover:text-brandRed text-sm font-semibold px-5 py-3 rounded-2xl transition-all duration-300 hover:shadow-md">
                                <span
                                    class="w-10 h-10 rounded-xl bg-red-50 group-hover:bg-brandRed/10 flex items-center justify-center transition-colors">
                                    <i class="<?= $group['icon'] ?? 'fa-solid fa-users' ?> text-brandRed text-sm"></i>
                                </span>
                                <?= sanitize($group['name'] ?? $group) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Timeline - Interactive Vertical -->
                    <?php if (!empty($timeline)): ?>
                    <div class="reveal-section">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-1 h-8 bg-brandRed rounded-full"></div>
                            <span class="text-brandRed text-[11px] font-bold uppercase tracking-widest">Project
                                Timeline</span>
                        </div>
                        <h2 class="text-3xl font-extrabold font-display text-slate-900 mb-8">Our Journey</h2>

                        <div class="relative">
                            <!-- Animated Line -->
                            <div
                                class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-brandRed via-brandRed/50 to-slate-200 timeline-line">
                            </div>

                            <?php foreach ($timeline as $i => $milestone): ?>
                            <div class="relative flex items-start gap-6 mb-10 last:mb-0 group timeline-item">
                                <!-- Node -->
                                <div class="relative z-10">
                                    <div
                                        class="w-12 h-12 rounded-full <?= $i === 0 ? 'bg-brandRed text-white shadow-xl shadow-brandRed/30' : 'bg-white text-slate-900 border-4 border-slate-200 group-hover:border-brandRed/30' ?> flex items-center justify-center text-sm font-bold shrink-0 transition-all duration-300 group-hover:scale-110">
                                        <i
                                            class="<?= $milestone['icon'] ?? 'fa-solid fa-circle' ?> <?= $i === 0 ? '' : 'text-slate-400 group-hover:text-brandRed' ?> transition-colors"></i>
                                    </div>
                                    <?php if ($i < count($timeline) - 1): ?>
                                    <div class="absolute top-12 left-1/2 -translate-x-1/2 w-0.5 h-10 bg-slate-100">
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Content -->
                                <div class="pt-1 pb-2 flex-1">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider <?= $i === 0 ? 'text-brandRed' : 'text-slate-400' ?> block mb-1"><?= sanitize($milestone['date'] ?? '') ?></span>
                                    <h4
                                        class="font-bold text-slate-900 text-lg mb-2 group-hover:text-brandRed transition-colors">
                                        <?= sanitize($milestone['title'] ?? '') ?></h4>
                                    <p class="text-sm text-slate-500 leading-relaxed">
                                        <?= sanitize($milestone['description'] ?? '') ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Gallery - Masonry Grid -->
                    <?php if (!empty($gallery)): ?>
                    <div class="reveal-section">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-1 h-8 bg-brandRed rounded-full"></div>
                            <span class="text-brandRed text-[11px] font-bold uppercase tracking-widest">Project
                                Gallery</span>
                        </div>
                        <h2 class="text-3xl font-extrabold font-display text-slate-900 mb-8">Visual Documentation</h2>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3" id="project-gallery">
                            <?php foreach ($gallery as $i => $img): ?>
                            <div class="gallery-item relative overflow-hidden rounded-2xl cursor-pointer group <?= ($i % 5 === 0) ? 'col-span-2 row-span-2' : 'aspect-[4/3]' ?>"
                                data-src="<?= $base ?><?= $img['path'] ?>"
                                data-caption="<?= sanitize($img['caption'] ?? '') ?>">
                                <img src="<?= $base ?><?= $img['path'] ?>"
                                    alt="<?= sanitize($img['caption'] ?? 'Project image') ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-5">
                                    <div>
                                        <i class="fa-solid fa-expand text-white text-sm mb-2"></i>
                                        <?php if (!empty($img['caption'])): ?>
                                        <p class="text-white text-sm font-medium"><?= sanitize($img['caption']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-slate-400 text-xs mt-4 text-center"><i class="fa-solid fa-images mr-1"></i> Click
                            any image to view in full size</p>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Right Sidebar - Sticky -->
                <div class="lg:col-span-4">
                    <div class="space-y-6 lg:sticky lg:top-32">

                        <!-- Resource Downloads -->
                        <?php if (!empty($resources)): ?>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm reveal-section">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                                    <i class="fa-solid fa-file-pdf text-brandRed"></i>
                                </div>
                                <h4 class="font-bold text-slate-900 text-sm">Resource Downloads</h4>
                            </div>
                            <div class="space-y-3">
                                <?php foreach ($resources as $res): ?>
                                <a href="<?= $base ?><?= $res['file_path'] ?>"
                                    class="flex items-center justify-between p-4 bg-slate-50 hover:bg-red-50 border border-slate-100 hover:border-brandRed/20 rounded-xl transition-all duration-300 group"
                                    target="_blank">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-white group-hover:bg-brandRed/10 flex items-center justify-center shadow-sm transition-colors">
                                            <i class="fa-solid fa-file-pdf text-brandRed text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <span
                                                class="text-xs font-bold text-slate-900 block truncate"><?= sanitize($res['title']) ?></span>
                                            <?php if (!empty($res['file_size'])): ?>
                                            <span
                                                class="text-[10px] text-slate-400"><?= sanitize($res['file_size']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <i
                                        class="fa-solid fa-download text-slate-300 group-hover:text-brandRed transition-colors shrink-0"></i>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Project Partners -->
                        <?php if (!empty($donors)): ?>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm reveal-section">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                                    <i class="fa-solid fa-handshake text-brandRed"></i>
                                </div>
                                <h4 class="font-bold text-slate-900 text-sm">Project Partners</h4>
                            </div>
                            <div class="space-y-3">
                                <?php foreach ($donors as $donor): ?>
                                <div
                                    class="flex items-center gap-4 p-4 bg-slate-50 border border-slate-100 rounded-xl hover:border-brandRed/20 transition-colors">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center shadow-sm">
                                        <i class="fa-solid fa-building text-brandRed"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span
                                            class="text-sm font-bold text-slate-900 block truncate"><?= sanitize($donor['donor_name']) ?></span>
                                        <?php if (!empty($donor['donor_type'])): ?>
                                        <span
                                            class="text-[11px] text-slate-500"><?= sanitize($donor['donor_type']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Share Project -->
                        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm reveal-section">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                                    <i class="fa-solid fa-share-nodes text-brandRed"></i>
                                </div>
                                <h4 class="font-bold text-slate-900 text-sm">Share This Project</h4>
                            </div>
                            <div class="flex gap-3">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                    target="_blank"
                                    class="flex-1 flex items-center justify-center gap-2 p-3 bg-slate-50 hover:bg-blue-50 border border-slate-100 hover:border-blue-200 rounded-xl transition-all text-slate-600 hover:text-blue-600">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($project['title']) ?>"
                                    target="_blank"
                                    class="flex-1 flex items-center justify-center gap-2 p-3 bg-slate-50 hover:bg-sky-50 border border-slate-100 hover:border-sky-200 rounded-xl transition-all text-slate-600 hover:text-sky-600">
                                    <i class="fa-brands fa-twitter"></i>
                                </a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                    target="_blank"
                                    class="flex-1 flex items-center justify-center gap-2 p-3 bg-slate-50 hover:bg-blue-50 border border-slate-100 hover:border-blue-300 rounded-xl transition-all text-slate-600 hover:text-blue-700">
                                    <i class="fa-brands fa-linkedin-in"></i>
                                </a>
                                <a href="https://wa.me/?text=<?= urlencode($project['title'] . ' - ' . $_SERVER['REQUEST_URI']) ?>"
                                    target="_blank"
                                    class="flex-1 flex items-center justify-center gap-2 p-3 bg-slate-50 hover:bg-green-50 border border-slate-100 hover:border-green-200 rounded-xl transition-all text-slate-600 hover:text-green-600">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Contact CTA -->
                        <div
                            class="bg-gradient-to-br from-ink-900 to-ink-800 p-6 rounded-2xl text-white reveal-section">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center mb-4">
                                <i class="fa-solid fa-headset text-white text-lg"></i>
                            </div>
                            <h4 class="font-bold text-white text-base mb-2">Have Questions?</h4>
                            <p class="text-white/60 text-sm leading-relaxed mb-5">
                                For partnership inquiries, media requests, or reporting related to this project, reach
                                out to our coordination team.
                            </p>
                            <a href="<?= $base ?>contact/index.php"
                                class="block w-full text-center bg-brandRed hover:bg-brandRed-hover text-white text-sm font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-brandRed/20 uppercase tracking-wider">
                                Contact Us
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- RELATED PROJECTS -->
    <?php if (!empty($relProjects)): ?>
    <section class="py-20 bg-white border-t border-slate-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal-section">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <div class="w-12 h-0.5 bg-brandRed"></div>
                    <span class="text-brandRed text-[11px] font-bold uppercase tracking-widest">More Projects</span>
                    <div class="w-12 h-0.5 bg-brandRed"></div>
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold font-display text-slate-900">Related Projects</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($relProjects as $rel): ?>
                <a href="project-name.php?slug=<?= $rel['slug'] ?>"
                    class="group bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 reveal-section">
                    <div class="relative h-56 overflow-hidden">
                        <img src="<?= $base ?><?= $rel['hero_image'] ?>" alt="<?= sanitize($rel['title']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent">
                        </div>
                        <div class="absolute top-4 left-4 flex gap-2">
                            <span
                                class="<?= ($rel['status'] === 'active' ? 'bg-emerald-500' : ($rel['status'] === 'completed' ? 'bg-slate-500' : 'bg-blue-500')) ?> text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full shadow-lg"><?= ucfirst($rel['status']) ?></span>
                            <?php if (!empty($rel['category'])): ?>
                            <span
                                class="bg-white/90 backdrop-blur-sm text-slate-700 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1.5 rounded-full shadow-lg"><?= sanitize($rel['category']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <h3
                                class="font-bold text-white text-lg mb-1 group-hover:text-brandRed-light transition-colors">
                                <?= sanitize($rel['title']) ?></h3>
                            <p class="text-white/70 text-xs line-clamp-2"><?= sanitize($rel['description']) ?></p>
                        </div>
                    </div>
                    <div class="p-5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-slate-400 text-xs"></i>
                            <span
                                class="text-xs text-slate-500"><?= sanitize($rel['location_details'] ?? 'Pakistan') ?></span>
                        </div>
                        <span
                            class="text-brandRed text-xs font-bold flex items-center gap-1.5 group-hover:gap-2.5 transition-all">
                            View Project <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA SECTION -->
    <section class="py-20 bg-gradient-to-br from-ink-900 via-ink-800 to-ink-900 text-white relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full"
                style="background-image: url('data:image/svg+xml,%3Csvg width=\" 60\" height=\"60\" viewBox=\"0 0 60
                60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg
                fill=\"%23ca0000\" fill-opacity=\"0.4\"%3E%3Cpath d=\"M36
                34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6
                4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-brandRed/20 rounded-full blur-3xl">
        </div>

        <div class="container relative z-10 mx-auto px-4 text-center reveal-section">
            <h2
                class="text-3xl md:text-4xl lg:text-5xl font-display font-bold text-white mb-6 max-w-3xl mx-auto leading-tight">
                Together, we can turn compassion into meaningful action.
            </h2>
            <p class="text-white/60 text-lg mb-10 max-w-xl mx-auto">Join us in creating lasting change for communities
                that need it most.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="<?= $base ?>get-involved/contribute-with-us.php"
                    class="px-8 py-4 rounded-xl bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm shadow-xl shadow-brandRed/30 transition-all uppercase tracking-wider hover:-translate-y-1">
                    <i class="fa-solid fa-handshake mr-2"></i> Contribute Now
                </a>
                <a href="<?= $base ?>contact/index.php"
                    class="px-8 py-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm transition-all uppercase tracking-wider hover:-translate-y-1">
                    <i class="fa-solid fa-envelope mr-2"></i> Contact Us
                </a>
            </div>
        </div>
    </section>

</main>

<!-- LIGHTBOX MODAL -->
<div id="lightbox-modal"
    class="fixed inset-0 z-[100] bg-black/95 backdrop-blur-sm hidden items-center justify-center p-4" role="dialog"
    aria-modal="true">
    <button id="lightbox-close"
        class="absolute top-6 right-6 text-white/50 hover:text-white text-3xl z-10 transition-colors w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20"
        aria-label="Close lightbox">
        <i class="fa-solid fa-xmark"></i>
    </button>
    <button id="lightbox-prev"
        class="absolute left-4 lg:left-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white text-2xl z-10 transition-all w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20"
        aria-label="Previous image">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button id="lightbox-next"
        class="absolute right-4 lg:right-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white text-2xl z-10 transition-all w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20"
        aria-label="Next image">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
    <div class="max-w-5xl max-h-[85vh] flex flex-col items-center gap-4">
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[75vh] object-contain rounded-xl">
        <div class="flex items-center gap-4">
            <p id="lightbox-caption" class="text-white/70 text-sm text-center"></p>
            <span id="lightbox-counter" class="text-white/40 text-xs font-mono"></span>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
(function() {
    // Lightbox
    var modal = document.getElementById('lightbox-modal');
    var img = document.getElementById('lightbox-img');
    var caption = document.getElementById('lightbox-caption');
    var counter = document.getElementById('lightbox-counter');
    var items = document.querySelectorAll('.gallery-item');
    var current = 0;

    function openLightbox(index) {
        current = index;
        var item = items[current];
        img.src = item.dataset.src || '';
        img.alt = item.querySelector('img') ? item.querySelector('img').alt : '';
        caption.textContent = item.dataset.caption || '';
        counter.textContent = (current + 1) + ' / ' + items.length;
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
        item.addEventListener('click', function() {
            openLightbox(i);
        });
    });

    document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
    document.getElementById('lightbox-prev').addEventListener('click', function() {
        current = (current - 1 + items.length) % items.length;
        openLightbox(current);
    });
    document.getElementById('lightbox-next').addEventListener('click', function() {
        current = (current + 1) % items.length;
        openLightbox(current);
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeLightbox();
    });
    document.addEventListener('keydown', function(e) {
        if (modal.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') {
            current = (current - 1 + items.length) % items.length;
            openLightbox(current);
        }
        if (e.key === 'ArrowRight') {
            current = (current + 1) % items.length;
            openLightbox(current);
        }
    });

    // Counter Animation
    function animateCounters() {
        var counters = document.querySelectorAll('.counter-number');
        counters.forEach(function(counter) {
            if (counter.dataset.animated) return;
            var target = parseInt(counter.dataset.target) || 0;
            if (target === 0) {
                counter.textContent = '0';
                return;
            }
            var duration = 2000;
            var start = 0;
            var startTime = null;

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                counter.textContent = Math.floor(eased * target).toLocaleString();
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    counter.textContent = target.toLocaleString();
                    counter.dataset.animated = 'true';
                }
            }
            requestAnimationFrame(step);
        });
    }

    // Scroll Reveal
    var revealElements = document.querySelectorAll('.reveal-section');
    var revealObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                if (entry.target.querySelectorAll('.counter-number').length) {
                    animateCounters();
                }
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    revealElements.forEach(function(el) {
        revealObserver.observe(el);
    });

    // Progress Bar Animation
    var progressBar = document.querySelector('.progress-bar-fill');
    if (progressBar) {
        var progressObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.width = entry.target.style.getPropertyValue(
                        '--progress-width');
                }
            });
        }, {
            threshold: 0.5
        });
        progressBar.style.width = '0%';
        progressObserver.observe(progressBar);
    }

    // Initial counter animation for hero counters
    setTimeout(animateCounters, 500);
})();
</script>
<script>
function toggleOverview() {
    var text = document.getElementById('overview-text');
    var label = document.getElementById('toggle-label');
    var icon = document.getElementById('toggle-icon');

    if (text.classList.contains('line-clamp-3')) {
        text.classList.remove('line-clamp-3');
        label.textContent = 'Read Less';
        icon.style.transform = 'rotate(180deg)';
    } else {
        text.classList.add('line-clamp-3');
        label.textContent = 'Read More';
        icon.style.transform = 'rotate(0deg)';
    }
}
</script>