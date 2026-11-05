<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Policies & Compliance | WRC';
$pageDesc = 'Upholding Integrity, Transparency and Accountability - Welfare & Relief Committee (WRC) Policies & Compliance Framework';

$homepageSections = getRows( 'homepage_sections', 'is_active = 1 ORDER BY sort_order ASC' );
$pageSections = [];
foreach ( $homepageSections as $section ) {
    $key = $section[ 'section_key' ] ?? '';
    if ( in_array( $key, [ 'policies_hero', 'policies_content' ] ) ) {
        $pageSections[ $key ] = $section;
        $pageSections[ $key ][ 'parsed_content' ] = json_decode( $section[ 'content' ] ?? '{}', true );
    }
}

// Override with dedicated table data if present
$heroRow = getRow( 'policies_hero' );
if ( $heroRow ) {
    $pageSections[ 'policies_hero' ] = [
        'section_key' => 'policies_hero',
        'section_title' => $heroRow[ 'section_title' ] ?? '',
        'section_subtitle' => $heroRow[ 'section_subtitle' ] ?? '',
        'parsed_content' => $heroRow
    ];
}
$contentRow = getRow( 'policies_content' );
if ( $contentRow ) {
    $decoded = json_decode( $contentRow[ 'content' ] ?? '{}', true );
    $pageSections[ 'policies_content' ] = [
        'section_key' => 'policies_content',
        'section_title' => $contentRow[ 'section_title' ] ?? '',
        'section_subtitle' => $contentRow[ 'section_subtitle' ] ?? '',
        'parsed_content' => is_array( $decoded ) ? $decoded : []
    ];
}

$hero = $pageSections[ 'policies_hero' ] ?? null;
$heroData = $hero[ 'parsed_content' ] ?? [];

// 4 Specific Standard Policy Categories with Descriptions & Icons
$categories = [
    [
        'id' => 'governance-ethics',
        'num' => '1',
        'name' => 'Governance & Ethics',
        'icon' => 'fa-solid fa-landmark',
        'tag' => 'Oversight & Accountability',
        'desc' => 'Foundational structures, leadership standards, and ethical rules ensuring responsible decision-making and institutional integrity.',
        'policies' => [
            [
                'title' => 'Governance Policy',
                'desc' => 'Establishes the principles, structures, and procedures that guide organizational governance, leadership, oversight, and decision-making.',
                'badge' => 'Governance'
            ],
            [
                'title' => 'Code of Conduct',
                'desc' => 'Defines the ethical standards and professional behaviour expected from employees, representatives, consultants, and other individuals acting on behalf of WRC.',
                'badge' => 'Ethics'
            ],
            [
                'title' => 'Conflict of Interest Policy',
                'desc' => 'Provides guidance on identifying, disclosing, assessing, and managing actual, potential, or perceived conflicts of interest.',
                'badge' => 'Integrity'
            ],
        ]
    ],
    [
        'id' => 'safeguarding-protection',
        'num' => '2',
        'name' => 'Safeguarding & Protection',
        'icon' => 'fa-solid fa-shield-halved',
        'tag' => 'Zero Tolerance to Harm',
        'desc' => 'Commitments, protocols, and protective frameworks ensuring the safety, dignity, and wellbeing of vulnerable groups and communities.',
        'policies' => [
            [
                'title' => 'Child Safeguarding Policy',
                'desc' => 'Sets out measures to protect children from abuse, exploitation, neglect, and other forms of harm in connection with WRC\'s activities.',
                'badge' => 'Child Protection'
            ],
            [
                'title' => 'Protection from Sexual Exploitation and Abuse (PSEA) Policy',
                'desc' => 'Establishes standards and procedures to prevent and respond to sexual exploitation and abuse involving personnel or representatives associated with WRC.',
                'badge' => 'PSEA'
            ],
            [
                'title' => 'General Safeguarding Policy',
                'desc' => 'Outlines the organization\'s commitment to protecting individuals from harm, abuse, exploitation, harassment, and inappropriate conduct in its programmes and operations.',
                'badge' => 'Safeguarding'
            ],
        ]
    ],
    [
        'id' => 'hr-workplace-standards',
        'num' => '3',
        'name' => 'Human Resources & Workplace Standards',
        'icon' => 'fa-solid fa-users-gear',
        'tag' => 'Fairness & Professionalism',
        'desc' => 'Standards governing recruitment, employment equity, professional growth, workplace safety, and mutual respect.',
        'policies' => [
            [
                'title' => 'Human Resources Policy',
                'desc' => 'Provides a framework for recruitment, employment practices, staff development, performance management, workplace responsibilities, and staff welfare.',
                'badge' => 'HR Standards'
            ],
            [
                'title' => 'Workplace Policy',
                'desc' => 'Promotes a professional, safe, respectful, and inclusive working environment, free from discrimination, harassment, and inappropriate behaviour.',
                'badge' => 'Workplace'
            ],
        ]
    ],
    [
        'id' => 'financial-management',
        'num' => '4',
        'name' => 'Financial Management & Accountability',
        'icon' => 'fa-solid fa-file-invoice-dollar',
        'tag' => 'Stewardship & Compliance',
        'desc' => 'Rigorous fiscal governance, competitive procurement rules, and strict anti-corruption controls safeguarding donor and public funds.',
        'policies' => [
            [
                'title' => 'Financial Management Policy',
                'desc' => 'Defines standards for budgeting, financial planning, accounting, internal controls, financial reporting, and responsible use of organizational resources.',
                'badge' => 'Fiscal Control'
            ],
            [
                'title' => 'Procurement Policy',
                'desc' => 'Establishes transparent and fair procurement procedures to support value for money, impartial supplier selection, competition, and appropriate documentation.',
                'badge' => 'Procurement'
            ],
            [
                'title' => 'Anti-Fraud & Anti-Corruption Policy',
                'desc' => 'Sets out WRC\'s approach to preventing, identifying, reporting, and addressing fraud, bribery, corruption, and other financial misconduct.',
                'badge' => 'Anti-Corruption'
            ],
        ]
    ]
];

$totalPolicies = 0;
foreach ( $categories as $c ) {
    $totalPolicies += count( $c['policies'] );
}

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class="flex-grow bg-slate-50">

    <!-- HERO SECTION -->
    <section class="relative bg-slate-900 text-white min-h-[440px] flex items-center py-20 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>" 
                 alt="WRC Policies & Compliance" 
                 class="w-full h-full object-cover opacity-20 mix-blend-luminosity">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/85 to-slate-900/60"></div>
        </div>
        
        <!-- Subtle Pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(#ffffff0a_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 max-w-6xl">
            <!-- Breadcrumbs -->
            <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2 flex-wrap" aria-label="Breadcrumb">
                <a href="<?= $base ?>index.php" class="hover:text-white transition-colors">Home</a>
                <i class="fa-solid fa-chevron-right text-[8px] text-slate-500"></i>
                <a href="<?= $base ?>about/index.php" class="hover:text-white transition-colors">About Us</a>
                <i class="fa-solid fa-chevron-right text-[8px] text-slate-500"></i>
                <span class="text-brandRed font-medium">Policies & Compliance</span>
            </nav>

            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-brandRed/20 text-brandRed border border-brandRed/30 mb-4">
                    <i class="fa-solid fa-scale-balanced text-[11px]"></i> Institutional Governance
                </span>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold font-heading text-white tracking-tight leading-tight mb-4">
                    Policies <span class="text-brandRed">&</span> Compliance
                </h1>
                
                <p class="text-xl sm:text-2xl font-light text-slate-200 mb-6 leading-relaxed">
                    Upholding Integrity, Transparency and Accountability
                </p>

                <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                    At the Welfare &amp; Relief Committee (WRC), we recognize that effective humanitarian and development work requires strong institutional governance, ethical conduct, and responsible management. Our policies and compliance framework guide our decisions, define organizational standards, and promote accountability across all levels of our operations.
                </p>
                <p class="text-slate-300 text-sm sm:text-base leading-relaxed mt-3">
                    We are committed to maintaining a safe, respectful, inclusive, and transparent working environment while ensuring that the rights, dignity, and wellbeing of the communities we serve remain central to our work.
                </p>
            </div>
        </div>
    </section>

    <!-- PROFESSIONAL FILTER TABS COMPONENT -->
    <section class="py-12 md:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold uppercase tracking-wider text-brandRed bg-brandRed/10 px-3 py-1 rounded-full inline-block mb-3">
                    Policy Directory
                </span>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight font-heading">
                    Our Policy Framework
                </h2>
                <div class="w-16 h-1 bg-brandRed mx-auto mt-4 rounded-full"></div>
                <p class="text-sm text-slate-600 mt-4 leading-relaxed">
                    Filter by institutional pillar to explore approved policies, compliance mandates, and operational guidelines.
                </p>
            </div>

            <!-- INTERACTIVE FILTER TABS -->
            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 mb-10" id="policyFilterContainer">
                <!-- All Filter Button -->
                <button type="button" 
                        data-filter="all"
                        class="policy-filter-btn active inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 shadow-sm border bg-brandRed text-white border-brandRed">
                    <i class="fa-solid fa-shapes text-xs"></i>
                    <span>All Policies</span>
                    <span class="filter-count ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white">
                        <?= $totalPolicies ?>
                    </span>
                </button>

                <?php foreach ( $categories as $cat ): ?>
                <button type="button" 
                        data-filter="<?= $cat['id'] ?>"
                        class="policy-filter-btn inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 shadow-sm border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 hover:border-slate-300">
                    <i class="<?= $cat['icon'] ?> text-xs text-brandRed"></i>
                    <span><?= $cat['num'] ?>. <?= $cat['name'] ?></span>
                    <span class="filter-count ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        <?= count( $cat['policies'] ) ?>
                    </span>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- FILTERED SECTIONS -->
            <div class="space-y-10" id="policyPillarsWrapper">
                <?php foreach ( $categories as $idx => $cat ): ?>
                <div class="policy-pillar-block bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden transition-all duration-300"
                     data-category="<?= $cat['id'] ?>">
                    
                    <!-- PILLAR HEADER -->
                    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white px-6 py-4 sm:px-8 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-700/60">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-lg bg-brandRed text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-brandRed/20">
                                <i class="<?= $cat['icon'] ?> text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-brandRed bg-white/10 px-2 py-0.5 rounded-full">
                                        Pillar <?= $cat['num'] ?>
                                    </span>
                                    <span class="text-xs text-slate-400">•</span>
                                    <span class="text-xs text-slate-300 font-medium"><?= $cat['tag'] ?></span>
                                </div>
                                <h3 class="text-lg sm:text-xl font-bold font-heading text-white">
                                    <?= $cat['num'] ?>. <?= $cat['name'] ?>
                                </h3>
                            </div>
                        </div>
                        <p class="text-xs text-slate-300 sm:text-right max-w-sm sm:border-l sm:border-slate-700/80 sm:pl-4">
                            <?= $cat['desc'] ?>
                        </p>
                    </div>

                    <!-- POLICY CARDS -->
                    <div class="p-6 sm:p-8">
                        <div class="grid grid-cols-1 <?= count( $cat['policies'] ) === 2 ? 'md:grid-cols-2' : 'md:grid-cols-3' ?> gap-6">
                            <?php foreach ( $cat['policies'] as $policy ): ?>
                            <div class="group relative flex flex-col justify-between bg-slate-50/70 hover:bg-white rounded-xl p-5 border border-slate-200 hover:border-brandRed/40 transition-all duration-300 hover:shadow-md">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="inline-block text-[11px] font-semibold text-slate-600 bg-white group-hover:bg-brandRed/10 group-hover:text-brandRed px-2.5 py-1 rounded-md border border-slate-200/80 group-hover:border-brandRed/30 transition-colors">
                                            <?= $policy['badge'] ?>
                                        </span>
                                        <i class="fa-solid fa-shield-check text-slate-300 group-hover:text-brandRed text-sm transition-colors"></i>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-900 group-hover:text-brandRed transition-colors mb-2 leading-snug">
                                        <?= $policy['title'] ?>
                                    </h4>
                                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                        <?= $policy['desc'] ?>
                                    </p>
                                </div>

                                <div class="mt-5 pt-3.5 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1.5 font-medium text-slate-600 text-[11px]">
                                        <i class="fa-solid fa-circle-check text-emerald-600 text-[10px]"></i> Active Institutional Standard
                                    </span>
                                    <span class="text-slate-400 group-hover:text-brandRed transition-colors">
                                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

            <!-- COMMITMENT & WHISTLEBLOWING CARD -->
            <div class="mt-16 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-8 sm:p-12 border border-slate-700/60 shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-brandRed/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-4xl mx-auto">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-brandRed/20 border border-brandRed/40 text-brandRed flex items-center justify-center">
                            <i class="fa-solid fa-handshake-angle text-lg"></i>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-brandRed">Our Commitment</span>
                    </div>

                    <h3 class="text-2xl sm:text-3xl font-extrabold font-heading text-white mb-4">
                        Institutional Integrity & Continuous Improvement
                    </h3>

                    <div class="space-y-4 text-slate-300 text-sm sm:text-base leading-relaxed">
                        <p>
                            WRC is committed to strengthening its policies, procedures, and institutional systems in line with applicable legal requirements and recognized humanitarian standards. We aim to promote a culture of integrity, accountability, transparency, and continuous improvement across our organization.
                        </p>
                        <p>
                            We encourage responsible reporting of misconduct and concerns through appropriate reporting channels, with due regard for confidentiality, protection from retaliation, and fair investigation procedures.
                        </p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-700/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 text-xs sm:text-sm text-slate-300">
                            <i class="fa-solid fa-lock text-brandRed text-base"></i>
                            <span>Confidentiality &amp; Whistleblower Protection Guaranteed</span>
                        </div>
                        <a href="<?= $base ?>contact.php" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-brandRed hover:bg-red-700 text-white text-xs sm:text-sm font-semibold transition-all shadow-md shadow-brandRed/20">
                            <i class="fa-solid fa-envelope"></i>
                            <span>Report a Concern / Contact Us</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- RELATED NAVIGATION -->
            <div class="mt-12 flex flex-wrap items-center justify-between gap-4 text-xs sm:text-sm text-slate-600 pt-6 border-t border-slate-200">
                <div class="flex items-center gap-2">
                    <span class="text-slate-400">Related Governance:</span>
                    <a href="<?= $base ?>about/organizational-structure.php" class="text-brandRed hover:underline font-medium">Organizational Structure</a>
                    <span class="text-slate-300">•</span>
                    <a href="<?= $base ?>about/leadership.php" class="text-brandRed hover:underline font-medium">Leadership &amp; Board</a>
                    <span class="text-slate-300">•</span>
                    <a href="<?= $base ?>about/who-we-are.php" class="text-brandRed hover:underline font-medium">Who We Are</a>
                </div>
            </div>

        </div>
    </section>

</main>

<!-- JAVASCRIPT FILTERING SCRIPT (Clean, no scroll jitter) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterButtons = document.querySelectorAll('.policy-filter-btn');
    const policyBlocks = document.querySelectorAll('.policy-pillar-block');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.getAttribute('data-filter');

            // Reset all button styles
            filterButtons.forEach(b => {
                b.classList.remove('active', 'bg-brandRed', 'text-white', 'border-brandRed');
                b.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
                const countBadge = b.querySelector('.filter-count');
                if (countBadge) {
                    countBadge.classList.remove('bg-white/20', 'text-white');
                    countBadge.classList.add('bg-slate-100', 'text-slate-600', 'border', 'border-slate-200');
                }
            });

            // Activate clicked button
            this.classList.add('active', 'bg-brandRed', 'text-white', 'border-brandRed');
            this.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            const activeBadge = this.querySelector('.filter-count');
            if (activeBadge) {
                activeBadge.classList.add('bg-white/20', 'text-white');
                activeBadge.classList.remove('bg-slate-100', 'text-slate-600', 'border', 'border-slate-200');
            }

            // Filter blocks with smooth fade
            policyBlocks.forEach(block => {
                const category = block.getAttribute('data-category');
                if (filter === 'all' || category === filter) {
                    block.style.display = 'block';
                    block.classList.remove('opacity-0', 'translate-y-2');
                    block.classList.add('opacity-100', 'translate-y-0');
                } else {
                    block.style.display = 'none';
                    block.classList.remove('opacity-100', 'translate-y-0');
                    block.classList.add('opacity-0', 'translate-y-2');
                }
            });
        });
    });
});
</script>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
