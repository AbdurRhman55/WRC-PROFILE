<?php
/**
 * WRC Website - Homepage (Dynamic)
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'World Relief Committee (WRC) - Official NGO Portal';
$metaDescription = 'World Relief Committee (WRC) is a non-profit humanitarian organization delivering health, education, WASH, food security, and emergency disaster relief globally.';

// Fetch dynamic data
$heroSlides = getRows('hero_sliders', ['page' => 'homepage', 'is_active' => 1], 'sort_order ASC');
$impactStats = getRows('impact_stats', "display_page = 'home_hero' ORDER BY sort_order ASC");
$partners = getRows('partners', 'is_active = 1 ORDER BY sort_order ASC');
$activeProjects = getRows('projects', "status = 'active' ORDER BY start_date DESC LIMIT 3");
$provinces = getRows('provinces', 'is_active = 1 ORDER BY sort_order ASC');
$kpStats = getRows('impact_stats', "display_page = 'home_kpi' ORDER BY sort_order ASC");
$processSteps = getRows('process_steps', 'is_active = 1 ORDER BY sort_order ASC');
$successStories = getRows('success_stories', 'is_active = 1 ORDER BY sort_order ASC');
$homepageSections = getRows('homepage_sections', 'is_active = 1 ORDER BY sort_order ASC');
$homepageMap = [];
foreach ($homepageSections as $s) $homepageMap[$s['section_key']] = $s;

// Override with dedicated table data for migrated sections
$chairmanRow = getRow('chairman_message');
if ($chairmanRow) {
    $homepageMap['chairman_message'] = [
        'section_key' => 'chairman_message',
        'section_title' => $chairmanRow['section_title'] ?? "Chairman's Message",
        'section_subtitle' => $chairmanRow['section_subtitle'] ?? '',
        'content' => json_encode($chairmanRow)
    ];
}
$geoRow = getRow('geographic_presence');
if ($geoRow) {
    $geoStatsRows = getRows('geographic_presence_stats', ['geo_id' => $geoRow['id']], 'sort_order ASC');
    $geoStats = array_map(function($s) { return ['value' => $s['stat_value'] ?? '', 'label' => $s['stat_label'] ?? '', 'icon' => $s['stat_icon'] ?? '', 'color' => $s['stat_color'] ?? '']; }, $geoStatsRows);
    $homepageMap['geographic_presence'] = [
        'section_key' => 'geographic_presence',
        'section_title' => $geoRow['section_title'] ?? '',
        'section_subtitle' => $geoRow['section_subtitle'] ?? '',
        'content' => json_encode(['stats' => $geoStats])
    ];
}
$ctaRow = getRow('cta_banner');
if ($ctaRow) {
    $homepageMap['cta_banner'] = [
        'section_key' => 'cta_banner',
        'section_title' => $ctaRow['section_title'] ?? 'Ready to Make a Difference?',
        'section_subtitle' => $ctaRow['section_subtitle'] ?? '',
        'content' => json_encode($ctaRow)
    ];
}

include __DIR__ . '/includes/header.php';
?>

<!-- HERO SLIDER -->
<section class="relative w-full bg-slate-900 text-white min-h-[560px] flex items-center overflow-hidden group">

    <!-- Slider Wrapper -->
    <div id="hero-slider"
        class="absolute inset-0 z-0 w-full h-full transition-transform duration-1000 ease-in-out flex">
        <?php foreach ($heroSlides as $i => $slide): ?>
        <?php
            $slideImage = !empty($slide['image_path']) ? $slide['image_path'] : 'assets/images/hero.jpg';
            if (strpos($slideImage, 'assets/') !== 0 && strpos($slideImage, 'http') !== 0 && strpos($slideImage, 'uploads/') !== 0) {
                $slideImage = 'assets/images/' . $slideImage;
            }
        ?>
        <div class="w-full h-full flex-shrink-0 relative">
            <img src="<?= $base ?><?= $slideImage ?>" alt="<?= sanitize($slide['title'] ?? 'Slide') ?>" class="absolute inset-0 w-full h-full object-cover" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
        </div>
        <?php endforeach; ?>
    </div>

    <div class="absolute inset-0 bg-gradient-to-br from-slate-950/70 via-slate-900/50 to-brandRed/20 z-[1] pointer-events-none"></div>

    <!-- Slider Navigation (Arrows) -->
    <button id="slider-prev"
        class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-20 w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-brandRed/90 backdrop-blur-md border border-white/20 text-white opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-lg">
        <i class="fa-solid fa-chevron-left text-sm sm:text-lg"></i>
    </button>
    <button id="slider-next"
        class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-20 w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-brandRed/90 backdrop-blur-md border border-white/20 text-white opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-lg">
        <i class="fa-solid fa-chevron-right text-sm sm:text-lg"></i>
    </button>

    <!-- Slider Pagination (Dots) -->
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2" id="slider-dots">
        <?php foreach ($heroSlides as $i => $slide): ?>
        <button
            class="<?= $i === 0 ? 'w-4 h-3 rounded-full bg-brandRed shadow-md' : 'w-3 h-3 rounded-full bg-white/50 hover:bg-white' ?> transition-all duration-300"
            data-index="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>

    <!-- Dynamic Content Overlay -->
    <div class="absolute inset-0 z-10 flex items-center pointer-events-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div id="hero-content" class="max-w-3xl pointer-events-auto animate-fade-in-up">
                <?php $firstSlide = $heroSlides[0] ?? []; ?>
                <?php if (!empty($firstSlide['badge'])): ?>
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest bg-brandRed/90 text-white mb-4 backdrop-blur-sm">
                    <i class="fa-solid fa-globe"></i> <?= sanitize($firstSlide['badge']) ?>
                </span>
                <?php else: ?>
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest bg-slate-900/60 backdrop-blur-sm text-slate-100 border border-white/20 mb-6">
                    <i class="fa-solid fa-globe text-brandRed"></i> World Relief Committee NGO
                </span>
                <?php endif; ?>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6 font-heading drop-shadow-md">
                    <?= sanitize($firstSlide['title'] ?? 'Empowering Hope, Rebuilding Lives Worldwide.') ?>
                </h1>
                <p class="text-base sm:text-lg text-slate-200 mb-8 leading-relaxed drop-shadow-sm max-w-2xl">
                    <?= sanitize($firstSlide['subtitle'] ?? 'WRC is dedicated to providing immediate emergency aid, clean water solutions, nutritious food packs, and sustainable education to vulnerable communities in crisis.') ?>
                </p>
                <div class="flex flex-wrap gap-4 items-center">
                    <a href="<?= $base ?><?= $firstSlide['cta_link'] ?? 'our-work/index.php' ?>"
                        class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm sm:text-base px-7 py-3.5 rounded-xl shadow-lg shadow-brandRed/30 transition-all flex items-center gap-2 transform hover:-translate-y-1">
                        <?= sanitize($firstSlide['cta_text'] ?? 'Explore Our Work') ?> <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                    <a href="<?= $base ?>get-involved/contribute-with-us.php"
                        class="bg-white/10 hover:bg-white/25 backdrop-blur-md text-white border border-white/30 font-semibold text-sm sm:text-base px-6 py-3.5 rounded-xl transition-all flex items-center gap-2 transform hover:-translate-y-1">
                        Contribute With Us <i class="fa-solid fa-handshake text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- IMPACT STATISTICS -->
<section class="bg-white border-b border-slate-200 py-8 sm:py-10">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center text-[10px] sm:text-[11px] md:text-sm">
            <?php foreach ($impactStats as $stat): ?>
            <div class="p-3 sm:p-4 border-r border-slate-100 last:border-0">
                <div class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 mb-1 leading-none font-heading counter-val"
                    data-target="<?= $stat['stat_value'] ?>"><?= $stat['stat_value'] ?></div>
                <p class="text-slate-600 text-[10px] sm:text-[11px] md:text-sm font-medium leading-snug">
                    <?= $stat['stat_label'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- CHAIRMAN'S MESSAGE -->
<?php $chairman = $homepageMap['chairman_message'] ?? null; $chairmanData = $chairman ? json_decode($chairman['content'], true) : []; ?>
<section class="bg-slate-50 py-10 lg:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 text-center ">
            <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2">Leadership Message</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                <?= (!empty($chairman['section_title'])) ? sanitize($chairman['section_title']) : (!empty($chairmanData['section_title']) ? sanitize($chairmanData['section_title']) : "Chairman's Message") ?></h2>
            <p class="mt-3 mx-auto text-slate-500 text-sm  sm:text-base">
                <?= (!empty($chairman['section_subtitle'])) ? sanitize($chairman['section_subtitle']) : (!empty($chairmanData['section_subtitle']) ? sanitize($chairmanData['section_subtitle']) : "A thoughtful message from our chairman highlighting WRC's mission, values, and commitment to community resilience.") ?></p>
        </div>
        <div
            class="grid gap-5 lg:grid-cols-[0.95fr_0.9fr] items-stretch bg-white border border-slate-200 rounded-[2rem] shadow-xl overflow-hidden">
            <div class="relative overflow-hidden">
                <img src="<?= $base ?><?= $chairmanData['image_path'] ?? $chairmanData['hero_image'] ?? 'assets/images/hero.jpg' ?>"
                    alt="Chairman of WRC" class="w-full h-full object-cover min-h-[280px]" />
                <div class="absolute inset-0  mix-blend-multiply"></div>
                <div class="absolute bottom-4 left-4 right-4 text-white">
                    <p class="text-[11px] uppercase tracking-[0.3em] text-white/80 mb-2">Message from the Chairman</p>
                    <h2 class="text-2xl sm:text-[2.5rem] font-extrabold leading-tight">
                        <?= $chairmanData['overlay_title'] ?? 'Leading with compassion, transparency, and bold action.' ?>
                    </h2>
                </div>
            </div>
            <div class="p-4 sm:p-5 lg:p-6">
                <span
                    class="inline-flex items-center gap-2 text-brandRed text-xs font-semibold uppercase tracking-[0.35em] mb-5"><?= $chairmanData['note_label'] ?? "Chairman's Note" ?></span>
                <?php
            $p1 = $chairmanData['paragraph1'] ?? '';
            $p2 = $chairmanData['paragraph2'] ?? '';
            if (!$p1 && !$p2 && !empty($chairmanData['message'])) {
              $msg = $chairmanData['message'];
              $mid = (int)(strlen($msg) / 2);
              $break = strrpos(substr($msg, 0, $mid + 50), ' ');
              $p1 = trim(substr($msg, 0, $break));
              $p2 = trim(substr($msg, $break + 1));
            }
          ?>
                <p class="text-slate-700 text-base sm:text-lg leading-relaxed mb-6">
                    <?= sanitize($p1) ?>
                </p>
                <p class="text-slate-700 text-base sm:text-lg leading-relaxed mb-8">
                    <?= sanitize($p2) ?>
                </p>
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 border-t border-slate-200 pt-6 mt-4">
                    <div>
                        <p class="text-red-500 font-bold text-lg leading-tight whitespace-nowrap">
                            <?= $chairmanData['person_name'] ?? '' ?></p>
                        <p class="text-slate-500 text-xs uppercase tracking-[0.25em] mt-1">
                            <?= $chairmanData['person_title'] ?? '' ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-[0.3em] text-slate-400">
                            <?= $chairmanData['vision_label'] ?? 'Vision' ?></p>
                        <p class="mt-1 text-sm text-slate-600"><?= $chairmanData['vision_text'] ?? '' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- OUR PROJECTS -->
<section class="bg-slate-50 py-14 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-6 lg:mb-10">
            <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-3">What We Do</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Our Projects</h2>
            <p class="mt-4 mx-auto max-w-2xl text-slate-600 text-sm sm:text-base leading-7">Delivering real results through
                humanitarian projects across health, education, WASH, food security, and emergency response.</p>
        </div>
        <div class="text-center"><a href="<?= $base ?>projects/index.php"
                class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold px-6 py-3 rounded-xl transition-all">View
                All Projects <i class="fa-solid fa-arrow-right text-xs"></i></a></div>
    </div>
</section>

<!-- INFOGRAPHICS SECTION -->
<section id="how-it-works" class="w-full bg-slate-50 py-8 lg:py-12 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center mb-20">
            <p class="text-xs uppercase tracking-[0.35em] font-semibold text-brandRed">Our process</p>
            <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">How we work</h2>
            <p class="mt-4 text-sm sm:text-base leading-7 text-slate-600">From the first funding lead to lasting change
                on the ground — a disciplined, transparent path that every project moves through, start to finish.</p>
        </div>

        <div class="relative max-w-5xl mx-auto timeline-track">
            <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-px -translate-x-1/2 bg-slate-300">
                <div id="spine-fill" class="absolute inset-0 w-full bg-brandRed/40"></div>
            </div>
            <div class="md:hidden absolute left-[19px] top-0 bottom-0 w-px bg-slate-300"></div>

            <div class="space-y-10 md:space-y-0">
                <?php foreach ($processSteps as $i => $step): ?>
                <?php $isOdd = ($i % 2 === 0); ?>
                <div class="step-row fade-up relative md:grid md:grid-cols-2 md:gap-x-16 md:items-center md:pb-20">
                    <?php if ($isOdd): ?>
                    <div class="pl-14 md:pl-0 md:pr-16 md:text-right">
                        <span
                            class="text-sm uppercase tracking-[0.28em] font-semibold <?= $step['color_class'] ?>"><?= str_pad($step['step_number'], 2, '0', STR_PAD_LEFT) ?></span>
                        <h3 class="mt-2 text-xl md:text-2xl font-semibold text-slate-900">
                            <?= sanitize($step['title']) ?></h3>
                        <p class="mt-4 text-sm md:text-[15px] text-slate-600 leading-relaxed">
                            <?= sanitize($step['description']) ?></p>
                    </div>
                    <div class="hidden md:block"></div>
                    <?php else: ?>
                    <div class="hidden md:block"></div>
                    <div class="pl-14 md:pl-16">
                        <span
                            class="text-sm uppercase tracking-[0.28em] font-semibold <?= $step['color_class'] ?>"><?= str_pad($step['step_number'], 2, '0', STR_PAD_LEFT) ?></span>
                        <h3 class="mt-2 text-xl md:text-2xl font-semibold text-slate-900">
                            <?= sanitize($step['title']) ?></h3>
                        <p class="mt-4 text-sm md:text-[15px] text-slate-600 leading-relaxed">
                            <?= sanitize($step['description']) ?></p>
                    </div>
                    <?php endif; ?>
                    <span
                        class="node-dot absolute left-[13px] md:left-1/2 top-1 md:top-1/2 -translate-x-1/2 md:-translate-y-1/2 w-3.5 h-3.5 rounded-full <?= $step['dot_color'] ?> ring-1 ring-brandRed/30"></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="fade-up relative flex justify-center mt-10">
                <div
                    class="relative z-10 flex flex-col items-center text-center max-w-md px-8 py-10 rounded-3xl bg-slate-950 shadow-2xl shadow-slate-950/20">
                    <span class="w-11 h-11 rounded-full bg-brandRed flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 21c-4.5-3-8-6.5-8-11a8 8 0 1116 0c0 4.5-3.5 8-8 11z" />
                            <circle cx="12" cy="10" r="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="text-[11px] tracking-[0.25em] uppercase text-brandRed/70 mb-2">Where it all
                        leads</span>
                    <h3 class="text-2xl md:text-3xl font-semibold text-white mb-3">Community impact</h3>
                    <p class="text-sm text-slate-200/80 leading-relaxed">Every stage exists to serve this outcome —
                        measurable, lasting improvement in the lives of the people we work with.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PARTNERS & DONORS SECTION -->
<section class="relative py-8 md:py-12 px-6 bg-slate-50">
    <div class="max-w-6xl mx-auto">

        <!-- Header -->
        <div class="max-w-2xl mx-auto text-center mb-10 md:mb-12 reveal">
            <span class="inline-block font-sans text-xs tracking-[0.25em] uppercase text-brandRed mb-4">Who makes it
                possible</span>
            <h2 class="font-display text-4xl md:text-5xl text-slate-950 leading-tight mb-5">Partners &amp; Collaborators
            </h2>
            <p class="font-sans text-slate-600 text-base md:text-lg leading-relaxed">Our work is made possible through
                the trust and support of funding partners, government bodies, and institutions who share our commitment
                to lasting community impact.</p>
        </div>

        <!-- Funding partners -->
        <div class="reveal mb-16">
            <div class="flex items-center gap-4 mb-8">
                <h3 class="font-display text-lg text-slate-950 whitespace-nowrap">Funding partners</h3>
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php 
          $fundingPartners = array_filter($partners, fn($p) => $p['partner_type'] === 'funding');
          foreach ($fundingPartners as $partner): ?>
                <div
                    class="partner-card group flex flex-col items-center justify-center gap-3 text-center bg-white border border-slate-200 rounded-xl px-6 py-8">
                    <div class="partner-logo w-28 h-28 flex items-center justify-center overflow-hidden">
                        <img class="h-24 w-24 object-contain" src="<?= $base ?><?= $partner['logo_path'] ?>"
                            alt="<?= sanitize($partner['name']) ?> logo">
                    </div>
                    <span class="font-sans text-sm font-medium text-slate-950"><?= sanitize($partner['name']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Government & implementation partners -->
        <div class="reveal">
            <div class="flex items-center gap-4 mb-8">
                <h3 class="font-display text-lg text-ink-900 whitespace-nowrap">Government &amp; implementation partners
                </h3>
                <span class="h-px flex-1 bg-ink-900/10"></span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php 
          $govPartners = array_filter($partners, fn($p) => $p['partner_type'] === 'government');
          foreach ($govPartners as $partner): ?>
                <div
                    class="partner-card group flex flex-col items-center justify-center gap-3 text-center bg-white border border-slate-200 rounded-xl px-6 py-8">
                    <div class="partner-logo w-28 h-28 flex items-center justify-center overflow-hidden">
                        <img class="h-24 w-24 object-contain" src="<?= $base ?><?= $partner['logo_path'] ?>"
                            alt="<?= sanitize($partner['name']) ?> logo">
                    </div>
                    <span class="font-sans text-sm font-medium text-slate-950"><?= sanitize($partner['name']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Trust strip -->
        <div
            class="reveal mt-16 pt-10 border-t border-ink-900/10 flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
            <p class="font-sans text-sm text-ink-700/60 max-w-md">Interested in partnering with us on a funded
                initiative or technical assessment? We welcome collaboration with institutions, government bodies, and
                mission-aligned organizations.</p>
            <a href="#contact"
                class="shrink-0 inline-flex items-center gap-2 font-sans text-sm font-medium text-ink-900 border border-ink-900/20 rounded-full px-6 py-3 hover:bg-ink-900 hover:text-sand-50 hover:border-ink-900 transition-colors">Become
                a partner<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg></a>
        </div>

    </div>
</section>

<!-- ACTIVE PROJECTS -->
<section class="py-16 bg-white" id="active-projects">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2">Current Initiatives</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Active Projects</h2>
            <p class="mt-3 max-w-2xl mx-auto text-slate-500 text-sm">Real-time overview of our ongoing humanitarian
                projects across Pakistan.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($activeProjects as $project): ?>
            <a href="<?= $base ?>projects/project-name.php?slug=<?= urlencode($project['slug']) ?>"
                class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300">
                <div class="relative h-48 overflow-hidden">
                    <img src="<?= $base ?><?= $project['hero_image'] ?? 'assets/images/water.jpg' ?>"
                        alt="<?= sanitize($project['title']) ?>"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3"><span
                            class="bg-brandRed text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full">Active</span>
                    </div>
                    <div class="absolute top-3 right-3"><span
                            class="bg-slate-900/70 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-1 rounded-full"><i
                                class="fa-solid fa-location-dot mr-1"></i><?= sanitize($project['location_details']) ?></span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-2"><span
                            class="text-[10px] text-slate-400">Since
                            <?= date('M Y', strtotime($project['start_date'])) ?></span></div>
                    <h3 class="font-bold text-slate-900 mb-2 group-hover:text-brandRed transition-colors"><?= sanitize($project['title']) ?></h3>
                    <p class="text-slate-500 text-xs leading-relaxed mb-4"><?= sanitize($project['description']) ?></p>
                    <div class="mb-3">
                        <div class="flex justify-between text-xs mb-1"><span class="text-slate-500">Progress</span><span
                                class="font-bold text-brandRed"><?= $project['progress_percent'] ?>%</span></div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-brandRed rounded-full h-2 animate-progress"
                                style="width:<?= $project['progress_percent'] ?>%"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-400"><span><i
                                class="fa-solid fa-users mr-1"></i><?= number_format($project['beneficiaries_count']) ?>
                            beneficiaries</span><span
                            class="text-brandRed font-semibold group-hover:underline">View Details <i class="fa-solid fa-arrow-right text-[10px]"></i></span></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10"><a href="<?= $base ?>projects/index.php#active"
                class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold px-6 py-3 rounded-xl transition-all">View
                All Active Projects <i class="fa-solid fa-arrow-right text-xs"></i></a></div>
    </div>
</section>

<!-- KPI DASHBOARD -->
<section class="py-16 bg-slate-900 text-white" id="kpi-dashboard">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2">Performance Metrics</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Project KPI Dashboard</h2>
            <p class="mt-3 max-w-2xl mx-auto text-slate-400 text-sm">Key performance indicators tracked across all
                active projects.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($kpStats as $kpi): ?>
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center">
                <div class="w-12 h-12 rounded-full bg-brandRed/20 flex items-center justify-center mx-auto mb-3"><i
                        class="fa-solid <?= $kpi['icon_class'] ?? 'fa-chart-line' ?> text-brandRed text-lg"></i></div>
                <div class="text-3xl font-extrabold text-white mb-1 font-heading"><?= $kpi['stat_value'] ?></div>
                <p class="text-xs text-slate-400"><?= $kpi['stat_label'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SUCCESS STORIES -->
<section class="py-16 bg-slate-50" id="success-stories">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2">Real Impact</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Success Stories</h2>
            <p class="mt-3 max-w-2xl mx-auto text-slate-500 text-sm">Hear from the communities whose lives have been
                transformed by your support.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach ($successStories as $story): ?>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition-all">
                <div class="relative h-52 overflow-hidden">
                    <img src="<?= $base ?><?= $story['image_path'] ?>" alt="<?= sanitize($story['person_name']) ?>"
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                    <div class="absolute bottom-3 left-3"><span
                            class="bg-brandRed text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full"><?= sanitize($story['sector_badge']) ?></span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-full bg-brandRed/10 flex items-center justify-center"><i
                                class="fa-solid fa-quote-left text-brandRed text-xs"></i></div><span
                            class="text-xs text-slate-400"><?= sanitize($story['location']) ?></span>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed italic mb-4">"<?= sanitize($story['quote']) ?>"</p>
                    <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                        <div class="w-9 h-9 rounded-full bg-slate-200 overflow-hidden"><img
                                src="<?= $base ?>assets/images/hero.jpg" alt="<?= sanitize($story['person_name']) ?>"
                                class="w-full h-full object-cover"></div>
                        <div>
                            <p class="text-sm font-bold text-slate-900"><?= sanitize($story['person_name']) ?></p>
                            <p class="text-[11px] text-slate-400"><?= sanitize($story['person_title']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10"><a href="<?= $base ?>impact/index.html#case-studies"
                class="inline-flex items-center gap-2 bg-brandRed hover:bg-brandRed-hover text-white text-sm font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-brandRed/20">Read
                More Stories <i class="fa-solid fa-arrow-right text-xs"></i></a></div>
    </div>
</section>

<!-- GEOGRAPHIC PRESENCE -->
<section class="py-16 bg-white" id="where-we-work-preview">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2">Geographic Presence</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-4">Serving Communities Across Pakistan
                </h2>
                <p class="text-slate-600 text-sm leading-relaxed mb-6">From the mountainous regions of KP to the
                    floodplains of Sindh, WRC operates in the areas of greatest need across all four provinces.</p>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <?php foreach ($provinces as $province): ?>
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0"><i
                                class="fa-solid <?= $province['icon_class'] ?? 'fa-map' ?> text-blue-500"></i></div>
                        <div>
                            <p class="text-sm font-bold text-slate-900"><?= sanitize($province['name']) ?></p>
                            <p class="text-[11px] text-slate-400">
                                <?= number_format($province['beneficiaries_count']) ?>+ beneficiaries</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?= $base ?>where-we-work/index.html"
                    class="inline-flex items-center gap-2 bg-brandRed hover:bg-brandRed-hover text-white text-sm font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-brandRed/20">Explore
                    Full Map <i class="fa-solid fa-arrow-right text-xs"></i></a>
            </div>
            <div
                class="relative bg-slate-50 rounded-3xl border border-slate-200 p-8 flex items-center justify-center min-h-[400px]">
                <div class="text-center">
                    <?php
                    $geoMap = $homepageMap['geographic_presence'] ?? null;
                    $geoMapData = $geoMap ? json_decode($geoMap['content'], true) : [];
                    $geoStats = $geoMapData['stats'] ?? [];
                    foreach ($geoStats as $gs):
                    ?>
                    <div class="mb-4">
                        <div class="w-20 h-20 rounded-full bg-brandRed/10 flex items-center justify-center mx-auto mb-4"><i
                                class="fa-solid <?= $gs['icon'] ?? 'fa-map-location-dot' ?> text-brandRed text-3xl"></i></div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2"><?= sanitize($gs['value'] ?? '') ?></h3>
                        <p class="text-slate-500 text-sm mb-1"><?= sanitize($gs['label'] ?? '') ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($geoStats)): ?>
                    <div class="w-20 h-20 rounded-full bg-brandRed/10 flex items-center justify-center mx-auto mb-4"><i
                            class="fa-solid fa-map-location-dot text-brandRed text-3xl"></i></div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">4 Provinces</h3>
                    <p class="text-slate-500 text-sm mb-1">46+ Districts Covered</p>
                    <p class="text-slate-500 text-sm mb-4">150,000+ Lives Impacted</p>
                    <?php endif; ?>
                    <a href="<?= $base ?>where-we-work/index.php#map"
                        class="inline-flex items-center gap-2 text-brandRed text-sm font-bold hover:underline">Interactive
                        Map <i class="fa-solid fa-arrow-right text-xs"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA BANNER -->
<?php $cta = $homepageMap['cta_banner'] ?? null; $ctaData = $cta ? json_decode($cta['content'], true) : []; ?>
<section class="py-16 bg-brandRed">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4 font-heading">
            <?= (!empty($cta['section_title'])) ? sanitize($cta['section_title']) : (!empty($ctaData['section_title']) ? sanitize($ctaData['section_title']) : 'Ready to Make a Difference?') ?></h2>
        <p class="text-white/80 text-sm sm:text-base max-w-2xl mx-auto mb-8"><?= $ctaData['description'] ?? '' ?></p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="<?= $base ?><?= $ctaData['button1_link'] ?? 'get-involved/contribute-with-us.php' ?>"
                class="bg-white text-brandRed hover:bg-slate-100 font-bold text-sm px-7 py-3.5 rounded-xl shadow-lg transition-all flex items-center gap-2"><i
                    class="<?= $ctaData['button1_icon'] ?? 'fa-solid fa-hand-holding-heart' ?>"></i>
                <?= $ctaData['button1_text'] ?? 'Donate Now' ?></a>
            <a href="<?= $base ?><?= $ctaData['button2_link'] ?? 'get-involved/index.php#volunteer' ?>"
                class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white border border-white/30 font-semibold text-sm px-6 py-3.5 rounded-xl transition-all flex items-center gap-2"><i
                    class="<?= $ctaData['button2_icon'] ?? 'fa-solid fa-hands-holding-child' ?>"></i>
                <?= $ctaData['button2_text'] ?? 'Volunteer' ?></a>
        </div>
    </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
// Hero slider JS (dynamic slide count)
$slideCount = count($heroSlides);
?>
<script>
// Hero Slider Logic
const slider = document.getElementById('hero-slider');
const prevBtn = document.getElementById('slider-prev');
const nextBtn = document.getElementById('slider-next');
const dots = document.querySelectorAll('#slider-dots button');
const contentEl = document.getElementById('hero-content');
let currentSlide = 0;
const slideCount = <?= $slideCount ?>;
let slideInterval;

const slideContents = <?= json_encode(array_map(function($s) {
    return [
        'badge' => $s['badge'] ?? '',
        'title' => $s['title'] ?? '',
        'subtitle' => $s['subtitle'] ?? '',
        'cta_text' => $s['cta_text'] ?? 'Explore Our Work',
        'cta_link' => $s['cta_link'] ?? 'our-work/index.php',
    ];
}, $heroSlides)) ?>;

function renderContent(index) {
    if (!contentEl || !slideContents[index]) return;
    var s = slideContents[index];
    contentEl.style.opacity = '0';
    contentEl.style.transform = 'translateY(15px)';
    setTimeout(function() {
        var badgeHtml = s.badge
            ? '<span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest bg-brandRed/90 text-white mb-4 backdrop-blur-sm"><i class="fa-solid fa-globe"></i> ' + s.badge + '</span>'
            : '<span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest bg-slate-900/60 backdrop-blur-sm text-slate-100 border border-white/20 mb-6"><i class="fa-solid fa-globe text-brandRed"></i> World Relief Committee NGO</span>';
        contentEl.innerHTML = badgeHtml +
            '<h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tail mb-6 font-heading drop-shadow-md">' + s.title + '</h1>' +
            '<p class="text-base sm:text-lg text-slate-200 mb-8 leading-relaxed drop-shadow-sm max-w-2xl">' + s.subtitle + '</p>' +
            '<div class="flex flex-wrap gap-4 items-center">' +
            '<a href="<?= $base ?>' + s.cta_link + '" class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm sm:text-base px-7 py-3.5 rounded-xl shadow-lg shadow-brandRed/30 transition-all flex items-center gap-2 transform hover:-translate-y-1">' + s.cta_text + ' <i class="fa-solid fa-arrow-right text-xs"></i></a>' +
            '<a href="<?= $base ?>get-involved/contribute-with-us.php" class="bg-white/10 hover:bg-white/25 backdrop-blur-md text-white border border-white/30 font-semibold text-sm sm:text-base px-6 py-3.5 rounded-xl transition-all flex items-center gap-2 transform hover:-translate-y-1">Contribute With Us <i class="fa-solid fa-handshake text-xs"></i></a>' +
            '</div>';
        contentEl.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        contentEl.style.opacity = '1';
        contentEl.style.transform = 'translateY(0)';
    }, 200);
}

function updateSlider() {
    if (slider) {
        slider.style.transform = 'translateX(-' + currentSlide * 100 + '%)';
        dots.forEach(function(dot, index) {
            dot.className = index === currentSlide
                ? 'w-4 h-3 rounded-full bg-brandRed shadow-md transition-all duration-300'
                : 'w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-all duration-300';
        });
        renderContent(currentSlide);
    }
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slideCount;
    updateSlider();
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + slideCount) % slideCount;
    updateSlider();
}

function startSlider() {
    slideInterval = setInterval(nextSlide, 5000);
}

function resetSlider() {
    clearInterval(slideInterval);
    startSlider();
}

if (nextBtn && prevBtn && dots.length > 0) {
    nextBtn.addEventListener('click', function() { nextSlide(); resetSlider(); });
    prevBtn.addEventListener('click', function() { prevSlide(); resetSlider(); });
    dots.forEach(function(dot) {
        dot.addEventListener('click', function(e) {
            currentSlide = parseInt(e.target.getAttribute('data-index'));
            updateSlider();
            resetSlider();
        });
    });
    startSlider();
}

// Touch/Swipe support
var touchStartX = 0;
var touchEndX = 0;
var heroSection = document.getElementById('hero-slider');
if (heroSection) {
    heroSection.addEventListener('touchstart', function(e) { touchStartX = e.changedTouches[0].screenX; }, {passive: true});
    heroSection.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        if (touchStartX - touchEndX > 50) { nextSlide(); resetSlider(); }
        else if (touchEndX - touchStartX > 50) { prevSlide(); resetSlider(); }
    }, {passive: true});
}
</script>
</script>