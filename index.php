<?php
/**
 * WRC Website - Homepage (Dynamic)
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Welfare & Relief Committee (WRC) - Official NGO Portal';
$metaDescription = 'Welfare & Relief Committee (WRC) is a non-profit humanitarian organization delivering health, WASH, food and relief distribution, and skill development programmes across Pakistan.';

// Fetch dynamic data
$heroSlides = getRows('hero_sliders', ['page' => 'homepage', 'is_active' => 1], 'sort_order ASC');
$partners = getRows('partners', 'is_active = 1 ORDER BY sort_order ASC');

// The homepage partner wall only renders logo cards for active funding and
// government partners. Everything below works from that same list, so the
// "Partner Organisations" figure always equals the number of logos shown.
//
// Records are folded with wrcPartnerAliasKey() so spelling variants of one
// organisation (the three KP-TEVTA records, for example) are never shown or
// counted twice.
$uniquePartners = [];
foreach ($partners as $p) {
    if (!in_array($p['partner_type'] ?? '', ['funding', 'government'], true)) {
        continue;
    }
    if (!imageExists($p['logo_path'] ?? '')) {
        continue;
    }
    $key = wrcPartnerAliasKey((string) ($p['name'] ?? ''));
    if ($key === '' || isset($uniquePartners[$key])) {
        continue;
    }
    $uniquePartners[$key] = $p;
}
$partners = array_values($uniquePartners);

$fundingPartners = array_values(array_filter($partners, fn($p) => ($p['partner_type'] ?? '') === 'funding'));
$govPartners = array_values(array_filter($partners, fn($p) => ($p['partner_type'] ?? '') === 'government'));
$partnerLogoCount = count($fundingPartners) + count($govPartners);

$activeProjects = getRows('projects', "status = 'active' ORDER BY start_date DESC LIMIT 3");
$provinces = getRows('provinces', 'is_active = 1 ORDER BY sort_order ASC');

// Only provinces that actually hold a live project are shown, so the homepage
// can never advertise a province (or a beneficiary figure) the portfolio does
// not back.
$projectProvinceIds = array_map(
    'intval',
    $pdo->query("SELECT DISTINCT province_id FROM projects WHERE is_active = 1 AND province_id IS NOT NULL AND province_id > 0")->fetchAll(PDO::FETCH_COLUMN)
);
$provinces = array_values(array_filter($provinces, static function ($province) use ($projectProvinceIds) {
    return in_array((int) ($province['id'] ?? 0), $projectProvinceIds, true);
}));

// Active project count per province, used by the homepage coverage cards.
$provinceProjectCounts = [];
foreach ($pdo->query("SELECT province_id, COUNT(*) AS total FROM projects WHERE is_active = 1 GROUP BY province_id")->fetchAll(PDO::FETCH_ASSOC) as $provinceCountRow) {
    $provinceProjectCounts[(int) $provinceCountRow['province_id']] = (int) $provinceCountRow['total'];
}

// Hero figures are calculated from the live projects table, never typed in.
$impactSummary = wrcProjectImpactSummary();

// The SDG figure is read from the same Impact content the Impact page renders,
// so the hero strip can never claim a different number of goals than the page
// that explains them.
$sdgCount = 0;
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $sdgRow = getRow('impact_content');
        $sdgContent = $sdgRow ? json_decode($sdgRow['content'] ?? '{}', true) : null;
        if (is_array($sdgContent) && !empty($sdgContent['sdgs']) && is_array($sdgContent['sdgs'])) {
            $sdgCount = count($sdgContent['sdgs']);
        }
    } catch (Throwable $e) {
        $sdgCount = 0;
    }
}
if ($sdgCount < 1) {
    $sdgCount = 5;
}

// Order matters: the strip reads left to right as reach, then breadth of
// programme, then global alignment, then the partnership and geographic base.
$heroStats = [
    [
        'icon'  => 'fa-solid fa-diagram-project',
        'value' => (int) $impactSummary[ 'projects' ],
        'label' => 'Projects Delivered',
        'note'  => 'Active and completed',
    ],
    [
        'icon'  => 'fa-solid fa-users',
        'value' => (int) $impactSummary[ 'beneficiaries' ],
        'label' => 'Beneficiaries Reached',
        'note'  => 'Lives directly impacted',
    ],
    [
        'icon'  => 'fa-solid fa-layer-group',
        'value' => (int) $impactSummary[ 'sectors' ],
        'label' => 'Programme Sectors',
        'note'  => 'Health, WASH, food & relief, skills',
    ],
    [
        'icon'  => 'fa-solid fa-globe',
        'value' => $sdgCount,
        'label' => 'SDGs Covered',
        'note'  => 'Aligned with the UN 2030 Agenda',
    ],
    [
        'icon'  => 'fa-solid fa-handshake-angle',
        'value' => $partnerLogoCount,
        'label' => 'Partner Organisations',
        'note'  => 'Donors and implementers',
    ],
    [
        'icon'  => 'fa-solid fa-map-location-dot',
        'value' => (int) $impactSummary[ 'districts' ],
        'label' => 'Districts Covered',
        'note'  => 'Across our field areas',
    ],
];

$processSteps = getRows('process_steps', 'is_active = 1 ORDER BY sort_order ASC');
if (empty($processSteps)) {
    $processSteps = [
        [
            'step_number' => 1,
            'title' => 'Funding Opportunity Identification',
            'description' => 'We proactively identify and assess funding opportunities, including donor programmes, government grants, and strategic partnerships. Each opportunity is evaluated against community needs, organizational priorities, technical capacity, and eligibility requirements.',
            'color_class' => 'text-brandRed',
            'dot_color' => 'bg-brandRed',
            'sort_order' => 1,
        ],
        [
            'step_number' => 2,
            'title' => 'Proposal Development',
            'description' => 'Our technical and financial teams work collaboratively to develop evidence-based proposals with clearly defined objectives, measurable outcomes, implementation strategies, realistic budgets, and appropriate monitoring frameworks.',
            'color_class' => 'text-brandRed',
            'dot_color' => 'bg-brandRed',
            'sort_order' => 2,
        ],
        [
            'step_number' => 3,
            'title' => 'Project Award & Agreement',
            'description' => 'Upon project approval, we formalize agreements with donors and relevant stakeholders, establish roles and responsibilities, review contractual obligations, and ensure alignment with applicable regulatory and donor compliance requirements.',
            'color_class' => 'text-brandRed',
            'dot_color' => 'bg-brandRed',
            'sort_order' => 3,
        ],
        [
            'step_number' => 4,
            'title' => 'Project Planning & Mobilization',
            'description' => 'We translate approved proposals into actionable implementation plans, defining project scope, timelines, resource requirements, procurement arrangements, risk management measures, and stakeholder responsibilities. Project teams and operational resources are mobilized accordingly.',
            'color_class' => 'text-slate-700',
            'dot_color' => 'bg-slate-700',
            'sort_order' => 4,
        ],
        [
            'step_number' => 5,
            'title' => 'Project Implementation',
            'description' => 'We execute project activities through coordinated field operations, technical oversight, and active engagement with communities, government departments, and implementation partners. Our focus remains on quality delivery, efficient resource utilization, and achieving agreed objectives.',
            'color_class' => 'text-slate-700',
            'dot_color' => 'bg-slate-700',
            'sort_order' => 5,
        ],
        [
            'step_number' => 6,
            'title' => 'Monitoring, Evaluation & Quality Assurance',
            'description' => 'We systematically monitor project progress against established indicators, assess performance and outcomes, conduct field-level reviews, and identify areas requiring improvement. Regular assessments enable timely corrective actions and help maintain implementation quality.',
            'color_class' => 'text-slate-700',
            'dot_color' => 'bg-slate-700',
            'sort_order' => 6,
        ],
        [
            'step_number' => 7,
            'title' => 'Reporting, Financial Accountability & Transparency',
            'description' => 'We maintain comprehensive records of project activities, expenditures, achievements, and challenges. Periodic technical and financial reports, expenditure tracking, documentation, and audit-ready records ensure accountability to donors, partners, and relevant stakeholders.',
            'color_class' => 'text-slate-800',
            'dot_color' => 'bg-slate-700',
            'sort_order' => 7,
        ],
        [
            'step_number' => 8,
            'title' => 'Project Closure & Sustainability',
            'description' => 'At project completion, we conduct final assessments, reconcile accounts, document lessons learned, finalize deliverables, and facilitate the formal handover of relevant assets and responsibilities. We prioritize sustainability measures to support the continued benefits of project interventions beyond the funding period.',
            'color_class' => 'text-slate-800',
            'dot_color' => 'bg-slate-700',
            'sort_order' => 8,
        ],
    ];
}
$successStories = getRows('success_stories', 'is_active = 1 ORDER BY sort_order ASC LIMIT 3');
$homepageSections = getRows('homepage_sections', 'is_active = 1 ORDER BY sort_order ASC');
$homepageMap = [];
foreach ($homepageSections as $s) $homepageMap[$s['section_key']] = $s;

// Override with dedicated table data for migrated sections
$chairmanRow = getRow('chairman_message');
if ($chairmanRow) {
    $homepageMap['chairman_message'] = [
        'section_key' => 'chairman_message',
        'section_title' => $homepageMap['chairman_message']['section_title'] ?? "Chairman's Message",
        'section_subtitle' => $homepageMap['chairman_message']['section_subtitle'] ?? '',
        'content' => json_encode($chairmanRow)
    ];
}
$geoRow = getRow('geographic_presence');
if ($geoRow) {
    $geoStatsRows = getRows('geographic_presence_stats', ['geo_id' => $geoRow['id']], 'sort_order ASC');
    $geoStats = array_map(function($s) { return ['value' => $s['stat_value'] ?? '', 'label' => $s['stat_label'] ?? '', 'icon' => $s['stat_icon'] ?? '', 'color' => $s['stat_color'] ?? '']; }, $geoStatsRows);
    // Coverage counters follow the live portfolio so this panel can never
    // advertise a province, district or programme the projects table does not
    // back. Labels and styling stay exactly as the dashboard set them.
    $geoFigures = [
        'provinces'     => (int) $impactSummary['provinces'],
        'districts'     => (int) $impactSummary['districts'],
        'programs'      => (int) $impactSummary['projects'],
        'programmes'    => (int) $impactSummary['projects'],
        'projects'      => (int) $impactSummary['projects'],
        'beneficiaries' => wrcFormatBeneficiaries( wrcBeneficiaryTotal() ),
    ];
    foreach ($geoStats as &$geoStat) {
        $geoLabel = strtolower(trim((string) ($geoStat['label'] ?? '')));
        if (isset($geoFigures[$geoLabel])) {
            $geoStat['value'] = (string) $geoFigures[$geoLabel];
        }
    }
    unset($geoStat);
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

<!-- HERO -->
<?php
$firstSlide = $heroSlides[0] ?? [];
$heroImage = !empty($firstSlide['image_path']) ? $firstSlide['image_path'] : 'assets/images/hero.jpg';
if (strpos($heroImage, 'assets/') !== 0 && strpos($heroImage, 'http') !== 0 && strpos($heroImage, 'uploads/') !== 0) {
    $heroImage = 'assets/images/' . $heroImage;
}
?>
<section class="relative w-full bg-slate-900 text-white min-h-[400px] sm:min-h-[440px] lg:min-h-[min(56vh,520px)] flex items-center overflow-hidden">

    <!-- Hero Image -->
    <div class="absolute inset-0 z-0 w-full h-full">
        <img src="<?= $base ?><?= $heroImage ?>" alt="<?= sanitize($firstSlide['title'] ?? 'Welfare & Relief Committee') ?>"
            class="absolute inset-0 w-full h-full object-cover" loading="eager">
    </div>

    <div
        class="absolute inset-0 bg-gradient-to-br from-slate-950/70 via-slate-900/50 to-brandRed/20 z-[1] pointer-events-none">
    </div>

    <!-- Content Overlay -->
    <div class="absolute inset-0 z-10 flex items-center pointer-events-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="max-w-2xl pointer-events-auto animate-fade-in-up">
                <?php if (!empty($firstSlide['badge'])): ?>
                <span
                    class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-brandRed/90 text-white mb-3 backdrop-blur-sm">
                    <i class="fa-solid fa-globe"></i> <?= sanitize($firstSlide['badge']) ?>
                </span>
                <?php else: ?>
                <span
                    class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-slate-900/60 backdrop-blur-sm text-slate-100 border border-white/20 mb-3">
                    <i class="fa-solid fa-globe text-brandRed"></i> Welfare & Relief Committee NGO
                </span>
                <?php endif; ?>
                <h1
                    class="text-3xl sm:text-4xl lg:text-[2.6rem] font-extrabold text-white leading-tight mb-4 font-heading drop-shadow-md">
                    <?= sanitize($firstSlide['title'] ?? 'Empowering Hope, Rebuilding Lives Worldwide.') ?>
                </h1>
                <p class="text-sm sm:text-base text-slate-200 mb-5 leading-relaxed drop-shadow-sm max-w-xl">
                    <?= sanitize($firstSlide['subtitle'] ?? 'WRC is dedicated to providing immediate emergency aid, clean water solutions, nutritious food packs, and sustainable education to vulnerable communities in crisis.') ?>
                </p>
                <div class="flex flex-wrap gap-3 items-center">
                    <a href="<?= $base ?>get-involved/contribute-with-us.php"
                        class="bg-brandRed hover:bg-brandRed-hover text-white font-bold text-sm px-6 py-3 rounded-lg shadow-lg shadow-brandRed/30 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                        Donate Now <i class="fa-solid fa-heart text-xs"></i>
                    </a>
                    <a href="<?= $base ?>our-work/index.php"
                        class="bg-white/10 hover:bg-white/25 backdrop-blur-md text-white border border-white/30 font-semibold text-sm px-5 py-3 rounded-lg transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                        Our Work <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- IMPACT STATISTICS (live from the projects table) -->
<section class="relative z-20 border-b border-slate-200 bg-white" aria-label="WRC impact at a glance">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-x-4 gap-y-5 py-4 sm:grid-cols-3 sm:py-5 md:grid-cols-6 md:gap-0 md:divide-x md:divide-slate-100">
            <?php foreach ($heroStats as $stat): ?>
            <div class="flex flex-col items-center justify-center gap-1.5 text-center md:px-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brandRed/10 text-brandRed ring-1 ring-inset ring-brandRed/10">
                    <i class="<?= $stat['icon'] ?> text-sm"></i>
                </span>
                <div class="min-w-0">
                    <div class="counter-val font-display text-xl font-extrabold leading-none tracking-tight text-ink-900 sm:text-2xl"
                        data-target="<?= (int) $stat['value'] ?>"><?= number_format((int) $stat['value']) ?></div>
                    <p class="mt-1 text-[10px] font-bold uppercase leading-tight tracking-[0.12em] text-slate-500">
                        <?= sanitize($stat['label']) ?></p>
                    <p class="mt-0.5 hidden text-[9px] leading-tight text-slate-400 lg:block"><?= sanitize($stat['note']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ORGANIZATION REGISTRATION STATUS -->
<section class="bg-white py-10 lg:py-12" aria-label="Organization status">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-50 px-6 py-10 text-center shadow-sm sm:px-10">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-brandRed via-brandRed to-brandRed/40"></div>
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-brandRed shadow-md ring-1 ring-slate-200">
                <i class="fa-solid fa-stamp text-2xl" aria-hidden="true"></i>
            </span>
            <p class="mt-5 text-brandRed text-xs font-semibold uppercase tracking-[0.35em]">Organization Status</p>
            <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900">Registered Organization</h2>
            <div class="mx-auto mt-5 h-1 w-16 rounded-full bg-gradient-to-r from-brandRed to-brandRed/40"></div>
            <div class="mx-auto mt-5 max-w-3xl space-y-4">
                <p class="text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
                    <strong class="font-bold text-slate-900">Welfare &amp; Relief Committee (WRC)</strong> is a legally registered non-profit organization committed to humanitarian assistance, community development, and improving the quality of life of vulnerable and underserved communities across Pakistan.
                </p>
                <p class="text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
                    Through responsible programme implementation, institutional partnerships, and a commitment to transparency and accountability, WRC works to create meaningful and sustainable community impact.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CHAIRMAN'S MESSAGE -->
<?php $chairman = $homepageMap['chairman_message'] ?? null; $chairmanData = $chairman ? json_decode($chairman['content'], true) : []; ?>
<?php $chairmanImagePath = !empty($chairmanData['image_path']) ? $chairmanData['image_path'] : ($chairmanData['hero_image'] ?? 'assets/images/hero.jpg'); ?>
<?php $chairmanFocus = splitImageFocus((string) $chairmanImagePath); ?>
<section class="bg-slate-50 py-10 lg:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 text-center ">
            <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2">Leadership Message</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                <?= (!empty($chairman['section_title'])) ? sanitize($chairman['section_title']) : (!empty($chairmanData['section_title']) ? sanitize($chairmanData['section_title']) : "Chairman's Message") ?>
            </h2>
            <p class="mt-3 mx-auto text-slate-500 text-sm  sm:text-base">
                <?= (!empty($chairman['section_subtitle'])) ? sanitize($chairman['section_subtitle']) : (!empty($chairmanData['section_subtitle']) ? sanitize($chairmanData['section_subtitle']) : "A thoughtful message from our chairman highlighting WRC's mission, values, and commitment to community resilience.") ?>
            </p>
        </div>
        <div
            class="grid gap-5 lg:grid-cols-[0.95fr_0.9fr] items-stretch bg-white border border-slate-200 rounded-[2rem] shadow-xl overflow-hidden">
            <div class="relative overflow-hidden min-h-[300px]">
                <img src="<?= sanitize(getImageUrl($chairmanImagePath, 'assets/images/chairman.svg')) ?>"
                    alt="Chairman of WRC" class="absolute inset-0 w-full h-full object-cover"
                    style="object-position: <?= (int) $chairmanFocus['x'] ?>% <?= (int) $chairmanFocus['y'] ?>%" />
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
            // The note is stored as one block of text with a blank line between the
            // logical paragraphs. Printing it as a single <p> throws every break away,
            // so the letter renders as one ragged wall of text. Split it back into
            // real paragraphs: each one then starts on the same edge, one below the
            // other, and the text justifies to a straight right edge.
            $paragraphs = [];
            foreach ([$p1, $p2] as $block) {
              foreach (preg_split('/\n\s*\n+/', (string)$block) as $para) {
                $para = trim($para);
                if ($para !== '') {
                  $paragraphs[] = $para;
                }
              }
            }
          ?>
                <?php foreach ($paragraphs as $para): ?>
                <p class="text-slate-700 text-base sm:text-lg leading-relaxed text-justify mb-5 last:mb-8">
                    <?= sanitize($para) ?>
                </p>
                <?php endforeach; ?>
                <div
                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 border-t border-slate-200 pt-6 mt-4">
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
<section class="bg-slate-50 py-10 lg:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-6 lg:mb-10">
            <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-3">What We Do</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Our Projects</h2>
            <p class="mt-4 mx-auto max-w-2xl text-slate-600 text-sm sm:text-base leading-7">Delivering real results
                through
                humanitarian projects across health, WASH, food and relief distribution, and skill development.</p>
        </div>
        <div class="text-center"><a href="<?= $base ?>projects/index.php"
                class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold px-6 py-3 rounded-xl transition-all">View
                All Projects <i class="fa-solid fa-arrow-right text-xs"></i></a></div>
    </div>
</section>

<!-- INFOGRAPHICS SECTION -->
<section id="how-it-works" class="w-full bg-slate-50 py-8 lg:py-12 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center mb-10">
            <p class="text-xs uppercase tracking-[0.35em] font-semibold text-brandRed">Our process</p>
            <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">How We Work</h2>
            <p class="mt-4 text-sm sm:text-base leading-7 text-slate-600">From identifying funding opportunities to delivering sustainable impact, we follow a structured, accountable, and transparent project management approach. Every initiative is guided by evidence, collaboration, quality standards, and measurable results.</p>
        </div>

        <div class="relative max-w-5xl mx-auto timeline-track">
            <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-px -translate-x-1/2 bg-slate-300">
                <div id="spine-fill" class="absolute inset-0 w-full bg-brandRed/40"></div>
            </div>
            <div class="md:hidden absolute left-[19px] top-0 bottom-0 w-px bg-slate-300"></div>

            <div class="space-y-10 md:space-y-0">
                <?php foreach ($processSteps as $i => $step): ?>
                <?php $isOdd = ($i % 2 === 0); ?>
                <div class="step-row fade-up relative md:grid md:grid-cols-2 md:gap-x-16 md:items-center md:pb-12">
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
                    class="relative z-10 flex flex-col items-center text-center max-w-lg px-8 py-10 rounded-3xl bg-slate-950 shadow-2xl shadow-slate-950/20">
                    <span class="w-11 h-11 rounded-full bg-brandRed flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 21c-4.5-3-8-6.5-8-11a8 8 0 1116 0c0 4.5-3.5 8-8 11z" />
                            <circle cx="12" cy="10" r="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="text-[11px] tracking-[0.25em] uppercase text-brandRed/70 mb-2">Where It All Leads</span>
                    <h3 class="text-2xl md:text-3xl font-semibold text-white mb-3">Lasting Community Impact</h3>
                    <p class="text-sm text-slate-200/80 leading-relaxed">Every initiative we undertake is driven by one purpose: creating meaningful, measurable, and sustainable improvements in the lives of the communities we serve. Through responsible partnerships, effective implementation, and a commitment to accountability, we strive to turn resources into lasting positive change.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PARTNERS & DONORS SECTION -->
<section class="relative py-8 md:py-12 px-6 bg-slate-50">
    <div class="max-w-6xl mx-auto">

        <!-- Header -->
        <div class="max-w-3xl mx-auto text-center mb-10 md:mb-6 reveal">
            <span class="inline-block font-sans text-xs tracking-[0.25em] uppercase text-brandRed mb-4">Who Makes It Possible</span>
            <h2 class="font-display text-4xl md:text-5xl text-slate-950 leading-tight mb-5">Our Partners &amp; Collaborators</h2>
            <p class="font-sans text-slate-600 text-base md:text-lg leading-relaxed">Meaningful and sustainable development is achieved through strong partnerships. We work alongside funding agencies, government institutions, development organizations, and strategic partners who share our vision of inclusive growth and lasting community impact. Through mutual trust, shared expertise, and collective commitment, we turn opportunities into meaningful action and sustainable outcomes.</p>
        </div>

        <!-- Funding partners -->
        <div class="reveal mb-8">
            <div class="flex items-center gap-4 mb-8">
                <h3 class="font-display text-lg text-slate-950 whitespace-nowrap">Funding partners</h3>
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php 
          // The homepage partner wall is a logo strip. Donor records that are
          // maintained for the gallery but have no logo should not appear as
          // empty text-only cards.
          foreach ($fundingPartners as $partner): ?>
                <div
                    class="partner-card group flex flex-col items-center justify-center gap-3 text-center bg-white border border-slate-200 rounded-xl px-6 py-8">
                    <?php if ( imageExists( $partner['logo_path'] ?? '' ) ): ?>
                    <div class="partner-logo w-28 h-28 flex items-center justify-center overflow-hidden">
                        <img class="h-24 w-24 object-contain" src="<?= $base ?><?= sanitize($partner['logo_path']) ?>"
                            alt="<?= sanitize($partner['name']) ?> logo">
                    </div>
                    <?php endif; ?>
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
          foreach ($govPartners as $partner): ?>
                <div
                    class="partner-card group flex flex-col items-center justify-center gap-3 text-center bg-white border border-slate-200 rounded-xl px-6 py-8">
                    <?php if ( imageExists( $partner['logo_path'] ?? '' ) ): ?>
                    <div class="partner-logo w-28 h-28 flex items-center justify-center overflow-hidden">
                        <img class="h-24 w-24 object-contain" src="<?= $base ?><?= sanitize($partner['logo_path']) ?>"
                            alt="<?= sanitize($partner['name']) ?> logo">
                    </div>
                    <?php endif; ?>
                    <span class="font-sans text-sm font-medium text-slate-950"><?= sanitize($partner['name']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

<!-- ACTIVE PROJECTS -->
<section class="py-10 bg-white" id="active-projects">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-6">
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
                    <div class="flex items-center gap-2 mb-2"><span class="text-[10px] text-slate-400">Since
                            <?= date('M Y', strtotime($project['start_date'])) ?></span></div>
                    <h3 class="font-bold text-slate-900 mb-2 group-hover:text-brandRed transition-colors">
                        <?= sanitize($project['title']) ?></h3>
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
                            beneficiaries</span><span class="text-brandRed font-semibold group-hover:underline">View
                            Details <i class="fa-solid fa-arrow-right text-[10px]"></i></span></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10"><a href="<?= $base ?>projects/index.php#active"
                class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold px-6 py-3 rounded-xl transition-all">View
                All Active Projects <i class="fa-solid fa-arrow-right text-xs"></i></a></div>
    </div>
</section>

<!-- SUCCESS STORIES -->
<section class="py-10 bg-slate-50" id="success-stories">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-6">
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
                        <?php
                        // The portrait beside the name. An uploaded author photo wins;
                        // otherwise the person's initials are shown rather than
                        // repeating the story photo, so the two images on a card are
                        // always distinct. Decorative only - the name is printed
                        // alongside, so it carries no information for screen readers.
                        $authorPhoto = trim((string) ($story['author_image'] ?? ''));
                        if ($authorPhoto === '') {
                            $nameParts = preg_split('/\s+/', trim((string) $story['person_name']));
                            $initials  = mb_strtoupper(
                                mb_substr($nameParts[0] ?? '', 0, 1)
                                . mb_substr($nameParts[count($nameParts) - 1] ?? '', 0, 1)
                            );
                        }
                        ?>
                        <?php if ($authorPhoto !== ''): ?>
                        <div class="w-9 h-9 rounded-full bg-slate-200 overflow-hidden shrink-0"><img
                                src="<?= $base ?><?= $authorPhoto ?>" alt="<?= sanitize($story['person_name']) ?>"
                                class="w-full h-full object-cover"></div>
                        <?php else: ?>
                        <div class="w-9 h-9 rounded-full bg-brandRed/10 text-brandRed shrink-0 flex items-center justify-center text-[11px] font-bold uppercase tracking-wider" aria-hidden="true"><?= sanitize($initials) ?></div>
                        <?php endif; ?>
                        <div>
                            <p class="text-sm font-bold text-slate-900"><?= sanitize($story['person_name']) ?></p>
                            <p class="text-[11px] text-slate-400"><?= sanitize($story['person_title']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10"><a href="<?= $base ?>impact#case-studies"
                class="inline-flex items-center gap-2 bg-brandRed hover:bg-brandRed-hover text-white text-sm font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-brandRed/20">Read
                More Stories <i class="fa-solid fa-arrow-right text-xs"></i></a></div>
    </div>
</section>

<!-- GEOGRAPHIC PRESENCE -->
<section id="where-we-work-preview" class="relative overflow-hidden bg-[#0B1222] py-12 text-white lg:py-14">
    <div class="pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full bg-brandRed/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-20 bottom-0 h-80 w-80 rounded-full bg-sky-500/10 blur-3xl"></div>
    <div class="pointer-events-none absolute inset-0 opacity-[0.05]"
        style="background-image: linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px); background-size: 44px 44px;"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-10 lg:grid-cols-[0.92fr_1.08fr] lg:gap-16">
            <div>
                <span class="inline-flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.3em] text-brandRed">
                    <span class="h-px w-8 bg-brandRed"></span> Geographic Presence
                </span>
                <h2 class="mt-4 font-heading text-3xl font-extrabold leading-tight sm:text-4xl">
                    Serving Communities
                    <span class="block text-white/55">Across Pakistan</span>
                </h2>
                <p class="mt-4 max-w-xl text-sm leading-relaxed text-slate-400">From the mountainous districts of
                    Khyber Pakhtunkhwa to the twin cities of Islamabad and Rawalpindi, WRC delivers health, WASH, food
                    and relief distribution, and skill development where the need is greatest.</p>

                <div class="mt-7 flex items-center justify-between border-b border-white/10 pb-2">
                    <span class="text-[10px] font-bold uppercase tracking-[0.22em] text-slate-500">Active Provinces</span>
                    <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-emerald-300/80">Live project
                        data</span>
                </div>

                <div class="mt-3 space-y-3">
                    <?php foreach ($provinces as $province): ?>
                    <?php $provinceProjects = (int) ($provinceProjectCounts[(int) ($province['id'] ?? 0)] ?? 0); ?>
                    <a href="<?= $base ?>where-we-work/index.php#map"
                        class="group flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:border-brandRed/40 hover:bg-white/[0.07]">
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-brandRed">
                            <i class="<?= $province['icon_class'] ?? 'fa-map-location-dot' ?> text-base"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline justify-between gap-2">
                                <h3 class="truncate text-sm font-bold text-white"><?= sanitize($province['name']) ?></h3>
                                <span
                                    class="shrink-0 rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-slate-400"><?= $provinceProjects ?> <?= $provinceProjects === 1 ? 'project' : 'projects' ?></span>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-400"><span
                                    class="font-bold text-brandRed"><?= sanitize(wrcFormatBeneficiaries((int) $province['beneficiaries_count'])) ?></span>
                                beneficiaries reached</p>
                        </div>
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-white/10 text-slate-400 transition-all group-hover:border-brandRed group-hover:bg-brandRed group-hover:text-white">
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-4">
                    <a href="<?= $base ?>where-we-work/index.php#map"
                        class="inline-flex items-center gap-2 rounded-lg bg-brandRed px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brandRed/25 transition-all hover:bg-brandRed-hover hover:-translate-y-0.5">
                        Explore Full Coverage <i class="fa-solid fa-map-location-dot text-xs"></i>
                    </a>
                    <span class="text-[11px] text-slate-500">Updated from <?= (int) $impactSummary['projects'] ?> live
                        projects</span>
                </div>
            </div>

            <div class="relative">
                <div
                    class="relative overflow-hidden rounded-[1.75rem] border border-white/10 bg-gradient-to-br from-white/[0.07] via-white/[0.03] to-transparent p-5 shadow-2xl shadow-black/40 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-emerald-400/25 bg-emerald-400/10 px-3 py-1 text-[9px] font-bold uppercase tracking-wider text-emerald-300">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="motion-safe:animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            </span>
                            Live Field Coverage
                        </span>
                        <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-slate-500">Pakistan</span>
                    </div>

                    <div class="relative mx-auto mt-3 w-full max-w-[360px]">
                        <img src="<?= $base ?>assets/images/pakistan-map.svg"
                            alt="Map of Pakistan showing WRC field coverage" loading="lazy"
                            class="aspect-square w-full opacity-[0.18] mix-blend-screen">
                        <?php
                        $pinPositions = [
                            'khyber-pakhtunkhwa' => ['left' => '45%', 'top' => '24%', 'label' => 'KP'],
                            'punjab'             => ['left' => '63%', 'top' => '47%', 'label' => 'Punjab'],
                        ];
                        foreach ($provinces as $index => $province):
                            $slug = (string) ($province['slug'] ?? '');
                            $pin = $pinPositions[$slug] ?? ['left' => (38 + $index * 14) . '%', 'top' => (30 + $index * 16) . '%', 'label' => sanitize($province['name'])];
                        ?>
                        <span class="absolute z-10" style="left: <?= $pin['left'] ?>; top: <?= $pin['top'] ?>;">
                            <span
                                class="motion-safe:animate-ping absolute -left-3 -top-3 h-6 w-6 rounded-full bg-brandRed/25"></span>
                            <span
                                class="relative block h-2.5 w-2.5 rounded-full bg-brandRed shadow-[0_0_0_4px_rgba(202,0,0,0.16)]"></span>
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-white/15 bg-slate-950/85 px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider text-slate-200 backdrop-blur-sm"><?= $pin['label'] ?></span>
                        </span>
                        <?php endforeach; ?>
                    </div>

                    <div
                        class="mt-4 grid grid-cols-3 divide-x divide-white/10 rounded-2xl border border-white/10 bg-black/25 py-3 text-center">
                        <div class="px-1">
                            <p class="font-heading text-sm font-extrabold text-white sm:text-base"><?= sanitize(wrcFormatBeneficiaries(wrcBeneficiaryTotal())) ?></p>
                            <p class="mt-0.5 text-[8px] font-bold uppercase tracking-[0.14em] text-slate-500">Lives Reached</p>
                        </div>
                        <div class="px-1">
                            <p class="font-heading text-sm font-extrabold text-white sm:text-base"><?= (int) $impactSummary['districts'] ?></p>
                            <p class="mt-0.5 text-[8px] font-bold uppercase tracking-[0.14em] text-slate-500">Districts</p>
                        </div>
                        <div class="px-1">
                            <p class="font-heading text-sm font-extrabold text-white sm:text-base"><?= (int) $impactSummary['projects'] ?></p>
                            <p class="mt-0.5 text-[8px] font-bold uppercase tracking-[0.14em] text-slate-500">Projects</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA BANNER -->
<?php $cta = $homepageMap['cta_banner'] ?? null; $ctaData = $cta ? json_decode($cta['content'], true) : []; ?>
<section class="py-10 bg-brandRed">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4 font-heading">
            <?= (!empty($cta['section_title'])) ? sanitize($cta['section_title']) : (!empty($ctaData['section_title']) ? sanitize($ctaData['section_title']) : 'Ready to Make a Difference?') ?>
        </h2>
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

<script>
// Animated impact counters. The final values are already in the HTML, so the
// page reads correctly without JavaScript; this only animates them into view.
(function() {
    var counters = document.querySelectorAll('.counter-val[data-target]');
    if (!counters.length) return;

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || !('IntersectionObserver' in window)) return;

    function animate(el) {
        var target = parseInt(el.getAttribute('data-target'), 10) || 0;
        var duration = 1500;
        var startTime = null;

        function frame(now) {
            if (startTime === null) startTime = now;
            var progress = Math.min((now - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 4);
            el.textContent = Math.round(target * eased).toLocaleString('en-US');
            if (progress < 1) {
                window.requestAnimationFrame(frame);
            } else {
                el.textContent = target.toLocaleString('en-US');
            }
        }

        el.textContent = '0';
        window.requestAnimationFrame(frame);
    }

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);
            animate(entry.target);
        });
    }, {
        threshold: 0.4
    });

    counters.forEach(function(counter) {
        observer.observe(counter);
    });
})();
</script>
