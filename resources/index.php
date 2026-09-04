<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = '../';
$pageTitle = 'Resources & Publications | WRC';
$metaDescription = 'Access WRC annual reports, financial statements, policies, and downloadable materials. Transparency and accountability at every level.';

// Fetch homepage_sections for resources_hero
$homepageSections = getRows('homepage_sections', 'is_active = 1 ORDER BY sort_order ASC');
$resourcesHeroSection = null;
$heroData = [];
foreach ($homepageSections as $section) {
    if ($section['section_key'] === 'resources_hero') {
        $resourcesHeroSection = $section;
        $heroData = json_decode($section['content'] ?? '{}', true);
        break;
    }
}

// Override with dedicated table data
$heroRow = getRow('resources_hero');
if ($heroRow) {
    $decoded = json_decode($heroRow['content'] ?? '{}', true);
    $resourcesHeroSection = ['section_key' => 'resources_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'content' => $heroRow['content']];
    $heroData = is_array($decoded) ? $decoded : [];
}

// Fetch active resources
$resources = getRows('resources', 'is_active = 1 ORDER BY sort_order ASC');

// Filter by category
$policies = array_filter($resources, fn($r) => ($r['category'] ?? '') === 'policy');
$mediaKits = array_filter($resources, fn($r) => ($r['category'] ?? '') === 'media_kit');
$downloads = array_filter($resources, fn($r) => ($r['category'] ?? '') === 'download');

include dirname(__DIR__) . '/includes/header.php';
?>

<style>
    .tab-btn.active { background-color: #ca0000; color: #fff; }
    .resource-card { transition: opacity 0.3s ease, transform 0.3s ease; }
    .resource-card.hidden-card { opacity: 0; transform: scale(0.95); position: absolute; pointer-events: none; }
    .resource-card.visible-card { opacity: 1; transform: scale(1); position: relative; pointer-events: auto; }
    .filter-grid { position: relative; }
</style>

<main class="flex-grow">

    <!-- ==================== HERO SECTION ==================== -->
    <section class="relative w-full bg-ink-900 text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero.jpg' ?>');"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-ink-900 via-ink-900/95 to-brandRed/20"></div>
        </div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
            <div class="flex items-center gap-3 mb-6">
                <span class="inline-block w-12 h-1 bg-brandRed rounded-full"></span>
                <span class="text-brandRed text-xs font-bold uppercase tracking-[0.25em]"><?= $heroData['tagline'] ?? 'Knowledge Hub' ?></span>
            </div>
            <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-5">
                <?= $heroData['title'] ?? 'Resources &<br><span class="text-brandRed">Publications</span>' ?>
            </h1>
            <p class="text-slate-300 text-base sm:text-lg max-w-2xl leading-relaxed mb-8">
                <?= $heroData['subtitle'] ?? 'Access our annual reports, financial statements, policies, and downloadable materials. Transparency and accountability at every level.' ?>
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="<?= $base ?><?= $heroData['button1_link'] ?? '#publications' ?>" class="inline-flex items-center gap-2 bg-brandRed hover:bg-brandRed-hover text-white font-semibold text-sm px-6 py-3 rounded-xl transition-all shadow-lg shadow-brandRed/20">
                    <i class="fa-solid fa-book text-xs"></i> <?= $heroData['button1_text'] ?? 'Browse Publications' ?>
                </a>
                <a href="<?= $base ?><?= $heroData['button2_link'] ?? '#downloads' ?>" class="inline-flex items-center gap-2 border border-white/30 text-white font-semibold text-sm px-6 py-3 rounded-xl hover:bg-white/10 transition-all">
                    <i class="fa-solid fa-download text-xs"></i> <?= $heroData['button2_text'] ?? 'Quick Downloads' ?>
                </a>
            </div>
            <?php
            $stats = $heroData['stats'] ?? [
                ['value' => '12+', 'label' => 'Publications'],
                ['value' => '5', 'label' => 'Policy Documents'],
                ['value' => '4', 'label' => 'Report Categories'],
            ];
            ?>
            <div class="flex flex-wrap items-center gap-6 mt-12 pt-8 border-t border-white/10">
                <?php foreach ($stats as $stat): ?>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white"><?= $stat['value'] ?? '' ?></div>
                    <div class="text-xs text-slate-400"><?= $stat['label'] ?? '' ?></div>
                </div>
                <?php if ($stat !== end($stats)): ?>
                <div class="w-px h-10 bg-white/10"></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- ==================== TABBED RESOURCE NAVIGATOR ==================== -->
    <section id="publications" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12">
                <span class="inline-block text-brandRed text-xs font-bold uppercase tracking-[0.2em] mb-3">Document Library</span>
                <h2 class="font-display text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Browse Our Reports & Documents</h2>
                <p class="text-slate-500 max-w-xl mx-auto">Filter by category to find exactly what you need. All documents are available for free download.</p>
            </div>

            <!-- TAB BUTTONS -->
            <div class="flex flex-wrap justify-center gap-2 mb-12" id="filter-tabs">
                <button class="tab-btn active px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-brandRed text-white transition-all hover:shadow-md" data-filter="all">
                    <i class="fa-solid fa-layer-group mr-1.5 text-xs"></i> All
                </button>
                <button class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-600 transition-all hover:border-brandRed hover:text-brandRed hover:shadow-md" data-filter="annual-report">
                    <i class="fa-solid fa-chart-line mr-1.5 text-xs"></i> Annual Reports
                </button>
                <button class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-600 transition-all hover:border-brandRed hover:text-brandRed hover:shadow-md" data-filter="financial-report">
                    <i class="fa-solid fa-coins mr-1.5 text-xs"></i> Financial Reports
                </button>
                <button class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-600 transition-all hover:border-brandRed hover:text-brandRed hover:shadow-md" data-filter="audit-report">
                    <i class="fa-solid fa-magnifying-glass-chart mr-1.5 text-xs"></i> Audit Reports
                </button>
                <button class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-600 transition-all hover:border-brandRed hover:text-brandRed hover:shadow-md" data-filter="policy">
                    <i class="fa-solid fa-shield-halved mr-1.5 text-xs"></i> Policies
                </button>
                <button class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-600 transition-all hover:border-brandRed hover:text-brandRed hover:shadow-md" data-filter="download">
                    <i class="fa-solid fa-download mr-1.5 text-xs"></i> Downloads
                </button>
                <button class="tab-btn px-5 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-600 transition-all hover:border-brandRed hover:text-brandRed hover:shadow-md" data-filter="media-kit">
                    <i class="fa-solid fa-photo-film mr-1.5 text-xs"></i> Media Kit
                </button>
            </div>

            <!-- DOCUMENT CARDS GRID -->
            <div class="filter-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="resource-grid">
                <?php
                // Icon/category color maps
                $iconMap = [
                    'annual-report' => ['icon' => 'fa-chart-line', 'bg' => 'bg-brandRed/10', 'hoverBg' => 'group-hover:bg-brandRed/20', 'textColor' => 'text-brandRed'],
                    'financial-report' => ['icon' => 'fa-coins', 'bg' => 'bg-emerald-50', 'hoverBg' => 'group-hover:bg-emerald-100', 'textColor' => 'text-emerald-600'],
                    'audit-report' => ['icon' => 'fa-magnifying-glass-chart', 'bg' => 'bg-amber-50', 'hoverBg' => 'group-hover:bg-amber-100', 'textColor' => 'text-amber-600'],
                    'policy' => ['icon' => 'fa-shield-halved', 'bg' => 'bg-violet-50', 'hoverBg' => 'group-hover:bg-violet-100', 'textColor' => 'text-violet-600'],
                    'download' => ['icon' => 'fa-file-lines', 'bg' => 'bg-cyan-50', 'hoverBg' => 'group-hover:bg-cyan-100', 'textColor' => 'text-cyan-600'],
                    'media-kit' => ['icon' => 'fa-image', 'bg' => 'bg-rose-50', 'hoverBg' => 'group-hover:bg-rose-100', 'textColor' => 'text-rose-600'],
                ];

                if (!empty($resources)):
                    foreach ($resources as $resource):
                        $category = $resource['category'] ?? 'download';
                        $iconStyle = $iconMap[$category] ?? $iconMap['download'];
                        $fileFormat = strtoupper($resource['file_format'] ?? 'PDF');
                        $formatBadgeClass = $fileFormat === 'ZIP' ? 'bg-rose-100 text-rose-600' : 'bg-brandRed/10 text-brandRed';
                        $fileSize = $resource['file_size'] ?? '';
                        $downloadUrl = !empty($resource['file_path']) ? $base . $resource['file_path'] : '#';
                ?>
                <div class="resource-card visible-card" data-category="<?= sanitize($category) ?>">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-xl hover:border-brandRed/30 transition-all duration-300 group h-full flex flex-col">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-14 h-14 rounded-xl <?= $iconStyle['bg'] ?> flex items-center justify-center shrink-0 <?= $iconStyle['hoverBg'] ?> transition-colors">
                                <i class="fa-solid <?= $iconStyle['icon'] ?> <?= $iconStyle['textColor'] ?> text-xl"></i>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full <?= $formatBadgeClass ?> text-[10px] font-bold uppercase tracking-wider"><?= $fileFormat ?></span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2 group-hover:text-brandRed transition-colors"><?= sanitize($resource['title'] ?? '') ?></h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-4 flex-1"><?= sanitize($resource['description'] ?? '') ?></p>
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <span class="text-xs text-slate-400"><i class="fa-solid fa-file mr-1"></i> <?= sanitize($fileSize) ?></span>
                            <a href="<?= $downloadUrl ?>" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-brandRed text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">
                                <i class="fa-solid fa-download text-[10px]"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                    endforeach;
                else:
                ?>
                <div class="col-span-full">
                    <div class="text-center py-16">
                        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-folder-open text-slate-300 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-700 mb-2">No documents available</h3>
                        <p class="text-slate-500 text-sm">Check back later for new publications.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- NO RESULTS MESSAGE (hidden by default) -->
            <div id="no-results" class="hidden text-center py-16">
                <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-folder-open text-slate-300 text-3xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-2">No documents found</h3>
                <p class="text-slate-500 text-sm">Try selecting a different category tab.</p>
            </div>

        </div>
    </section>


    <!-- ==================== POLICIES LIBRARY ==================== -->
    <section id="policies" class="py-16 lg:py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12">
                <span class="inline-block text-brandRed text-xs font-bold uppercase tracking-[0.2em] mb-3">Governance</span>
                <h2 class="font-display text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Policies Library</h2>
                <p class="text-slate-500 max-w-xl mx-auto">Download our core governance and compliance policies. These documents define our commitments to ethical operations.</p>
            </div>

            <?php
            $policyIcons = [
                'child' => 'fa-child-reaching',
                'whistleblower' => 'fa-bell',
                'gender' => 'fa-venus',
                'anti-corruption' => 'fa-scale-balanced',
                'code' => 'fa-book-open',
            ];
            $policyDescs = [
                'Child Protection Policy' => 'Safeguarding children from harm through prevention, reporting, and response protocols.',
                'Whistleblower Policy' => 'Confidential reporting channels for fraud, corruption, and organizational misconduct.',
                'Gender Equality Policy' => 'Promoting gender equity and women\'s empowerment across all humanitarian programs.',
                'Anti-Corruption Policy' => 'Zero-tolerance framework against bribery, fraud, and corrupt practices.',
                'Code of Conduct' => 'Ethical standards and behavioral expectations for all staff, partners, and volunteers.',
            ];
            $policyFallbackIcons = ['fa-child-reaching', 'fa-bell', 'fa-venus', 'fa-scale-balanced', 'fa-book-open'];
            $policyIndex = 0;
            ?>

            <?php if (!empty($policies)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($policies as $policy):
                    $title = $policy['title'] ?? '';
                    $policyIcon = $policyIcons[strtolower($title)] ?? $policyFallbackIcons[$policyIndex % count($policyFallbackIcons)];
                    $desc = $policyDescs[$title] ?? ($policy['description'] ?? '');
                    $fileFormat = strtoupper($policy['file_format'] ?? 'PDF');
                    $fileSize = $policy['file_size'] ?? '';
                    $downloadUrl = !empty($policy['file_path']) ? $base . $policy['file_path'] : '#';
                    $policyIndex++;
                ?>
                <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg hover:border-brandRed/30 transition-all duration-300 group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-brandRed/10 flex items-center justify-center shrink-0 group-hover:bg-brandRed/20 transition-colors">
                            <i class="fa-solid <?= $policyIcon ?> text-brandRed text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm group-hover:text-brandRed transition-colors"><?= sanitize($title) ?></h3>
                            <span class="text-[11px] text-slate-400"><?= $fileFormat ?> &bull; <?= sanitize($fileSize) ?></span>
                        </div>
                    </div>
                    <p class="text-slate-500 text-xs leading-relaxed mb-4"><?= sanitize($desc) ?></p>
                    <a href="<?= $downloadUrl ?>" class="inline-flex items-center gap-1.5 text-brandRed text-xs font-semibold hover:underline">
                        <i class="fa-solid fa-download text-[10px]"></i> Download Policy
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <p class="text-slate-500 text-sm">Policy documents are being uploaded. Please check back soon.</p>
            </div>
            <?php endif; ?>

        </div>
    </section>


    <!-- ==================== MEDIA KIT ==================== -->
    <section id="media-kit" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12">
                <span class="inline-block text-brandRed text-xs font-bold uppercase tracking-[0.2em] mb-3">For Media & Partners</span>
                <h2 class="font-display text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Media Kit & Brand Assets</h2>
                <p class="text-slate-500 max-w-xl mx-auto">Everything you need to represent WRC in publications, broadcasts, and digital content. All assets are free to use with proper attribution.</p>
            </div>

            <?php
            $mediaKitCards = [
                [
                    'icon' => 'fa-image',
                    'title' => 'Logo Downloads',
                    'desc' => 'Official WRC logos in PNG, SVG, and EPS formats. Includes color, mono, and reversed versions.',
                    'fileSize' => 'ZIP file &bull; 8.6 MB',
                ],
                [
                    'icon' => 'fa-palette',
                    'title' => 'Brand Guidelines',
                    'desc' => 'Complete visual identity guide &mdash; colors, typography, spacing rules, and co-branding requirements.',
                    'fileSize' => 'PDF document &bull; 5.3 MB',
                ],
                [
                    'icon' => 'fa-camera',
                    'title' => 'High-Resolution Photos',
                    'desc' => 'Curated field photos from WRC operations, events, and humanitarian activities for media use.',
                    'fileSize' => 'ZIP archive &bull; 42.5 MB',
                ],
            ];
            ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                $mkIndex = 0;
                foreach ($mediaKitCards as $mk):
                    $mkResource = null;
                    if (isset($mediaKits[$mkIndex])) {
                        $mkResource = $mediaKits[$mkIndex];
                    }
                    $downloadUrl = ($mkResource && !empty($mkResource['file_path'])) ? $base . $mkResource['file_path'] : '#';
                ?>
                <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl border border-slate-200 p-8 text-center hover:shadow-xl hover:border-brandRed/30 transition-all duration-300 group">
                    <div class="w-16 h-16 rounded-2xl bg-brandRed/10 flex items-center justify-center mx-auto mb-5 group-hover:bg-brandRed/20 transition-colors">
                        <i class="fa-solid <?= $mk['icon'] ?> text-brandRed text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-lg mb-2"><?= $mk['title'] ?></h3>
                    <p class="text-slate-500 text-sm mb-6 leading-relaxed"><?= $mk['desc'] ?></p>
                    <a href="<?= $downloadUrl ?>" class="inline-flex items-center gap-2 bg-brandRed hover:bg-brandRed-hover text-white font-semibold text-sm px-6 py-3 rounded-xl transition-all shadow-md shadow-brandRed/20">
                        <i class="fa-solid fa-download text-xs"></i> Download <?= $mk['title'] ?>
                    </a>
                    <p class="text-[11px] text-slate-400 mt-3"><?= $mk['fileSize'] ?></p>
                </div>
                <?php
                    $mkIndex++;
                endforeach;
                ?>
            </div>

            <!-- Media Contact -->
            <div class="mt-12 bg-ink-900 rounded-2xl p-8 lg:p-10 text-center">
                <h3 class="font-display text-xl font-bold text-white mb-3">Need Custom Assets?</h3>
                <p class="text-slate-400 text-sm mb-6 max-w-lg mx-auto">For press inquiries, custom logo requests, or high-resolution image needs, contact our communications team directly.</p>
                <a href="<?= $base ?>contact/index.html" class="inline-flex items-center gap-2 bg-white text-ink-900 font-semibold text-sm px-6 py-3 rounded-xl hover:bg-slate-100 transition-all">
                    <i class="fa-solid fa-envelope text-xs"></i> Contact Communications Team
                </a>
            </div>

        </div>
    </section>


    <!-- ==================== DOWNLOADS SECTION ==================== -->
    <section id="downloads" class="py-16 lg:py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12">
                <span class="inline-block text-brandRed text-xs font-bold uppercase tracking-[0.2em] mb-3">Free Downloads</span>
                <h2 class="font-display text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Brochures, Factsheets & Infographics</h2>
                <p class="text-slate-500 max-w-xl mx-auto">Shareable materials to help spread awareness about WRC's work. Download, print, and distribute freely.</p>
            </div>

            <?php
            $downloadGradients = [
                'from-brandRed/5 to-brandRed/10',
                'from-emerald-50 to-emerald-100',
                'from-amber-50 to-amber-100',
                'from-violet-50 to-violet-100',
            ];
            $downloadIcons = [
                'fa-file-lines',
                'fa-file-lines',
                'fa-newspaper',
                'fa-chart-bar',
            ];
            $downloadIconColors = [
                'text-brandRed/40 group-hover:text-brandRed/60',
                'text-emerald-400/60 group-hover:text-emerald-600/80',
                'text-amber-400/60 group-hover:text-amber-600/80',
                'text-violet-400/60 group-hover:text-violet-600/80',
            ];
            $downloadBadgeStyles = [
                'bg-brandRed/10 text-brandRed',
                'bg-emerald-100 text-emerald-700',
                'bg-amber-100 text-amber-700',
                'bg-violet-100 text-violet-700',
            ];
            $downloadLabels = ['Brochure', 'Brochure', 'Factsheet', 'Infographic'];
            ?>

            <?php if (!empty($downloads)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php
                $dlIndex = 0;
                foreach ($downloads as $download):
                    $gradient = $downloadGradients[$dlIndex % count($downloadGradients)];
                    $icon = $downloadIcons[$dlIndex % count($downloadIcons)];
                    $iconColor = $downloadIconColors[$dlIndex % count($downloadIconColors)];
                    $badge = $downloadBadgeStyles[$dlIndex % count($downloadBadgeStyles)];
                    $label = $downloadLabels[$dlIndex % count($downloadLabels)];
                    $fileFormat = strtoupper($download['file_format'] ?? 'PDF');
                    $fileSize = $download['file_size'] ?? '';
                    $downloadUrl = !empty($download['file_path']) ? $base . $download['file_path'] : '#';
                    $dlIndex++;
                ?>
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg hover:border-brandRed/30 transition-all duration-300 group">
                    <div class="h-40 bg-gradient-to-br <?= $gradient ?> flex items-center justify-center">
                        <i class="fa-solid <?= $icon ?> <?= $iconColor ?> text-5xl transition-colors"></i>
                    </div>
                    <div class="p-5">
                        <span class="inline-block px-2 py-0.5 rounded-full <?= $badge ?> text-[10px] font-bold uppercase tracking-wider mb-2"><?= $label ?></span>
                        <h3 class="font-bold text-slate-900 text-sm mb-1 group-hover:text-brandRed transition-colors"><?= sanitize($download['title'] ?? '') ?></h3>
                        <p class="text-slate-500 text-xs mb-3"><?= sanitize($download['description'] ?? '') ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] text-slate-400"><?= $fileFormat ?> &bull; <?= sanitize($fileSize) ?></span>
                            <a href="<?= $downloadUrl ?>" class="text-brandRed text-xs font-semibold hover:underline flex items-center gap-1"><i class="fa-solid fa-download text-[10px]"></i> Get</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <p class="text-slate-500 text-sm">Downloadable materials are being prepared. Please check back soon.</p>
            </div>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php
$extraScripts = '
<script>
document.addEventListener("DOMContentLoaded", function () {
    var tabs = document.querySelectorAll("#filter-tabs .tab-btn");
    var cards = document.querySelectorAll("#resource-grid .resource-card");
    var noResults = document.getElementById("no-results");

    tabs.forEach(function (tab) {
        tab.addEventListener("click", function () {
            tabs.forEach(function (t) {
                t.classList.remove("active", "bg-brandRed", "text-white", "border-brandRed");
                t.classList.add("bg-white", "text-slate-600");
            });
            this.classList.add("active", "bg-brandRed", "text-white", "border-brandRed");
            this.classList.remove("bg-white", "text-slate-600");

            var filter = this.getAttribute("data-filter");
            var visibleCount = 0;

            cards.forEach(function (card) {
                var category = card.getAttribute("data-category");
                if (filter === "all" || category === filter) {
                    card.classList.remove("hidden-card");
                    card.classList.add("visible-card");
                    visibleCount++;
                } else {
                    card.classList.remove("visible-card");
                    card.classList.add("hidden-card");
                }
            });

            if (visibleCount === 0) {
                noResults.classList.remove("hidden");
            } else {
                noResults.classList.add("hidden");
            }
        });
    });
});
</script>
';
include dirname(__DIR__) . '/includes/footer.php';
?>
