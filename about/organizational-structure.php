<?php
require_once dirname( __DIR__ ) . '/includes/config.php';
require_once dirname( __DIR__ ) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Organizational Structure | WRC About';
$metaDescription = "WRC's organizational structure establishes clear roles, responsibilities, and reporting relationships across governance, executive leadership, and functional departments.";

// Fetch homepage sections
$homepageSections = getRows( 'homepage_sections', [], 'sort_order ASC' );
$pageSections = [];
foreach ( $homepageSections as $section ) {
    $key = $section[ 'section_key' ] ?? '';
    if ( in_array( $key, [ 'org_structure_hero', 'org_structure_content' ] ) ) {
        $pageSections[ $key ] = $section;
        $pageSections[ $key ][ 'parsed_content' ] = json_decode( $section[ 'content' ] ?? '{}', true );
    }
}

// Override with dedicated table data if present
$heroRow = getRow( 'org_structure_hero' );
if ( $heroRow ) {
    $pageSections[ 'org_structure_hero' ] = [
        'section_key' => 'org_structure_hero',
        'section_title' => $heroRow[ 'section_title' ] ?? '',
        'section_subtitle' => $heroRow[ 'section_subtitle' ] ?? '',
        'parsed_content' => $heroRow
    ];
}

$hero = $pageSections[ 'org_structure_hero' ] ?? null;
$heroData = $hero[ 'parsed_content' ] ?? [];

include dirname( __DIR__ ) . '/includes/header.php';
?>

<main class="flex-grow">

<!-- HERO SECTION -->
<section class="relative bg-slate-900 text-white min-h-[460px] flex items-center py-12 overflow-hidden">
  <div class="absolute inset-0 z-0">
    <img src="<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/about-hero.jpg' ?>" alt="WRC Structure" class="w-full h-full object-cover opacity-25 mix-blend-luminosity">
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-900/60"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-900/70 to-transparent"></div>
  </div>

  <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2">
      <a href="<?= $base ?>index.php" class="hover:text-white transition">Home</a>
      <i class="fa-solid fa-chevron-right text-[8px]"></i>
      <a href="<?= $base ?>about/index.php" class="hover:text-white transition">About Us</a>
      <i class="fa-solid fa-chevron-right text-[8px]"></i>
      <span class="text-white">Organizational Structure</span>
    </nav>

    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-brandRed/20 text-brandRed text-xs font-bold uppercase tracking-widest border border-brandRed/30 mb-4">
      <i class="fa-solid fa-sitemap text-xs"></i> Institutional Framework
    </div>

    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold font-heading text-white mb-4 leading-tight max-w-4xl">
      Organizational Structure
    </h1>
    <p class="text-brandRed-light text-lg md:text-xl font-semibold mb-4 max-w-3xl">
      A Framework for Governance, Leadership and Effective Implementation
    </p>
    <p class="text-slate-300 text-base md:text-lg max-w-3xl leading-relaxed">
      WRC's organizational structure establishes clear roles, responsibilities, and reporting relationships to support effective governance, institutional accountability, and efficient programme delivery. It connects strategic oversight with executive management and field-level implementation, ensuring coordinated operations and responsible service delivery.
    </p>
  </div>
</section>

<!-- GOVERNANCE & MANAGEMENT FRAMEWORK OVERVIEW -->
<section class="py-10 bg-slate-50 border-b border-slate-200/80">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-6">
      <span class="px-4 py-1.5 rounded-full bg-brandRed/10 text-brandRed text-xs font-bold uppercase tracking-widest inline-block mb-3">
        Governance &amp; Management Framework
      </span>
      <h2 class="text-2xl sm:text-3xl md:text-4xl font-display font-extrabold text-slate-900 tracking-tight">
        Clear Distinction of Roles &amp; Responsibilities
      </h2>
      <p class="mt-4 text-slate-600 text-base leading-relaxed">
        WRC's structure is designed to maintain a clear distinction between governance, executive management, departmental functions, and field operations.
      </p>
    </div>

    <!-- 6 Pillar Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- 1. Board of Directors -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-lg mb-4">
          <i class="fa-solid fa-users-rectangle"></i>
        </div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pillar 01</div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">Board of Directors</h3>
        <p class="text-sm font-semibold text-brandRed mb-2">Strategic Governance &amp; Institutional Oversight</p>
        <p class="text-xs text-slate-600 leading-relaxed">
          Provides highest-level fiduciary oversight, policy formulation, ethical stewardship, and strategic guidance for long-term viability.
        </p>
      </div>

      <!-- 2. Chairman -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-slate-800 text-white flex items-center justify-center text-lg mb-4">
          <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pillar 02</div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">Chairman</h3>
        <p class="text-sm font-semibold text-brandRed mb-2">Board Leadership &amp; Institutional Representation</p>
        <p class="text-xs text-slate-600 leading-relaxed">
          Leads the Board of Directors, presides over governance sessions, guides strategic direction, and represents WRC with partners and authorities.
        </p>
      </div>

      <!-- 3. Executive Director -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-brandRed text-white flex items-center justify-center text-lg mb-4">
          <i class="fa-solid fa-briefcase"></i>
        </div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pillar 03</div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">Executive Director</h3>
        <p class="text-sm font-semibold text-brandRed mb-2">Executive Management &amp; Organizational Leadership</p>
        <p class="text-xs text-slate-600 leading-relaxed">
          Leads executive administration, directs operational leadership, supervises departmental heads, and translates board policies into action.
        </p>
      </div>

      <!-- 4. Finance & Administration -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center text-lg mb-4">
          <i class="fa-solid fa-calculator text-brandRed"></i>
        </div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pillar 04</div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">Finance &amp; Administration</h3>
        <p class="text-sm font-semibold text-brandRed mb-2">Financial Management &amp; Administrative Oversight</p>
        <p class="text-xs text-slate-600 leading-relaxed">
          Oversees budgeting, financial accounting, donor compliance, external audits, human resources, and general institutional administration.
        </p>
      </div>

      <!-- 5. Programmes & Development -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center text-lg mb-4">
          <i class="fa-solid fa-diagram-project text-brandRed"></i>
        </div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pillar 05</div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">Programmes &amp; Development</h3>
        <p class="text-sm font-semibold text-brandRed mb-2">Programme Planning, Development &amp; Implementation</p>
        <p class="text-xs text-slate-600 leading-relaxed">
          Leads evidence-based program design, funding proposal preparation, multi-sector humanitarian interventions, and M&amp;E frameworks.
        </p>
      </div>

      <!-- 6. Operations & Logistics -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center text-lg mb-4">
          <i class="fa-solid fa-truck-fast text-brandRed"></i>
        </div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pillar 06</div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">Operations &amp; Logistics</h3>
        <p class="text-sm font-semibold text-brandRed mb-2">Operations, Supply Chain &amp; Logistics Support</p>
        <p class="text-xs text-slate-600 leading-relaxed">
          Directs procurement, warehousing, emergency supply chain deployment, IT infrastructure, asset tracking, and security protocols.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- SUPPORTING DEPARTMENTS -->
<section class="py-12 bg-slate-50 border-t border-slate-200" id="departments">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-8">
      <span class="px-4 py-1.5 rounded-full bg-brandRed/10 text-brandRed text-xs font-bold uppercase tracking-widest inline-block mb-3">
        Departments
      </span>
      <h2 class="text-3xl md:text-4xl font-display font-bold text-slate-900">
        Supporting Departments
      </h2>
      <p class="mt-4 text-slate-600 text-sm sm:text-base">
        The functional departments that support WRC's programmes, governance, and day-to-day operations.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <!-- Human Resources -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-user-group"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Human Resources</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Recruitment, staff development, performance management, and workplace welfare.</p>
      </div>

      <!-- Security -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-slate-900 text-white flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Security</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Staff safety, field security protocols, asset protection, and duty-of-care checks.</p>
      </div>

      <!-- Risk Management -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Risk Management</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Risk registers, fraud prevention, safeguarding, and internal controls across projects.</p>
      </div>

      <!-- Finance & Administration -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-calculator"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Finance &amp; Administration</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Budgeting, accounting, donor compliance, audits, and general administration.</p>
      </div>

      <!-- Programmes & Development -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-diagram-project"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Programmes &amp; Development</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Proposal design, sector programming, partnerships, and grant management.</p>
      </div>

      <!-- Operations & Logistics -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-truck-fast"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Operations &amp; Logistics</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Procurement, warehousing, fleet, and emergency supply chain deployment.</p>
      </div>

      <!-- Monitoring & Evaluation -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-chart-line"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Monitoring &amp; Evaluation</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Indicator tracking, field monitoring, quality assurance, and results reporting.</p>
      </div>

      <!-- IT & Digital -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-laptop-code"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">IT &amp; Digital</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Networks, devices, data security, and digital tools for offices and field teams.</p>
      </div>

      <!-- Legal & Compliance -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-scale-balanced"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Legal &amp; Compliance</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Registrations, contracts, regulatory filings, and policy adherence.</p>
      </div>

      <!-- Field & Community Implementation -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-people-roof"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Field Implementation</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Regional offices, district project teams, and community mobilization.</p>
      </div>

      <!-- Communications -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-bullhorn"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Communications &amp; Media</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Publications, press relations, social media, and brand reporting.</p>
      </div>

      <!-- Procurement -->
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300 transition-all">
        <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base mb-3">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900 mb-1">Procurement</h3>
        <p class="text-xs text-slate-600 leading-relaxed">Vendor management, tendering, and value-for-money purchasing.</p>
      </div>
    </div>
  </div>
</section>

<!-- ORGANIZATIONAL HIERARCHY FLOWCHART -->
<section class="py-12 bg-white relative overflow-hidden" id="chart">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-8">
      <span class="px-4 py-1.5 rounded-full bg-slate-900 text-white text-xs font-bold uppercase tracking-widest inline-block mb-3 shadow-sm">
        Organizational Hierarchy &amp; Reporting Lines
      </span>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
        Governance &amp; Executive Flowchart
      </h2>
      <p class="mt-4 text-slate-600 text-base md:text-lg">
        An interconnected hierarchy linking strategic governance with executive leadership and operational frontline delivery.
      </p>
    </div>

    <!-- FLOWCHART CANVAS -->
    <div class="max-w-7xl mx-auto">

      <!-- ========================================================= -->
      <!-- TIER 1: GOVERNANCE & BOARD (Chairman leads the Board) -->
      <!-- ========================================================= -->
      <div class="relative z-10 max-w-3xl mx-auto">
        <div class="bg-slate-950 text-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 text-center relative overflow-hidden">
          <!-- Subtle top accent glow -->
          <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-brandRed via-amber-500 to-brandRed"></div>
          <div class="absolute top-0 right-0 w-48 h-48 bg-brandRed/10 rounded-full blur-3xl pointer-events-none"></div>

          <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 text-[11px] font-bold uppercase tracking-widest text-slate-200 border border-white/15 mb-4">
            <i class="fa-solid fa-shield-halved text-brandRed"></i> Tier 1 • Strategic Governance &amp; Institutional Oversight
          </div>

          <h3 class="text-2xl sm:text-3xl font-display font-bold text-white mb-1">
            Board of Directors
          </h3>
          <p class="text-brandRed text-sm font-semibold uppercase tracking-wider mb-6">
            Led by the Chairman of the Board
          </p>

          <!-- Inside Governance: Chairman leading Board Members -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
            <!-- Chairman Card -->
            <div class="bg-white/5 rounded-2xl p-5 border border-white/10 hover:border-brandRed/40 transition-colors">
              <div class="flex items-center gap-3 mb-2.5">
                <div class="w-10 h-10 rounded-xl bg-brandRed/20 text-brandRed flex items-center justify-center text-lg shrink-0 border border-brandRed/30">
                  <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                  <h4 class="text-base font-bold text-white leading-tight">Chairman</h4>
                  <p class="text-[10px] text-brandRed font-semibold uppercase tracking-wider">Leads the Board of Directors</p>
                </div>
              </div>
              <p class="text-xs text-slate-300 leading-relaxed">
                Board leadership, institutional representation, presiding over governance sessions, and guiding strategic direction.
              </p>
            </div>

            <!-- Board Members Card -->
            <div class="bg-white/5 rounded-2xl p-5 border border-white/10 hover:border-white/20 transition-colors">
              <div class="flex items-center gap-3 mb-2.5">
                <div class="w-10 h-10 rounded-xl bg-white/10 text-white flex items-center justify-center text-lg shrink-0 border border-white/15">
                  <i class="fa-solid fa-users-rectangle"></i>
                </div>
                <div>
                  <h4 class="text-base font-bold text-white leading-tight">Board Members</h4>
                  <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Strategic Governance</p>
                </div>
              </div>
              <p class="text-xs text-slate-300 leading-relaxed">
                Strategic oversight, approving organizational policies and budgets, ensuring fiduciary accountability and ethical compliance.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================= -->
      <!-- CONNECTOR 1: Board to Executive Director -->
      <!-- ========================================================= -->
      <div class="flex justify-center" aria-hidden="true">
        <div class="relative h-16 w-0.5 bg-gradient-to-b from-slate-200 via-slate-300 to-slate-400">
          <i class="fa-solid fa-chevron-down absolute left-1/2 top-full -translate-x-1/2 -translate-y-1/2 text-[9px] text-slate-400"></i>
        </div>
      </div>

      <!-- ========================================================= -->
      <!-- TIER 2: EXECUTIVE MANAGEMENT (Executive Director) -->
      <!-- ========================================================= -->
      <div class="relative z-10 max-w-xl mx-auto">
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xl border-2 border-slate-200/90 hover:border-brandRed/40 transition-colors text-center relative overflow-hidden">
          <div class="absolute top-0 inset-x-0 h-1 bg-brandRed"></div>

          <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-brandRed/10 text-brandRed text-[10px] font-bold uppercase tracking-widest border border-brandRed/20 mb-3">
            <i class="fa-solid fa-briefcase text-[10px]"></i> Tier 2 • Executive Management
          </div>

          <h3 class="text-2xl font-display font-bold text-slate-900 mb-1">
            Executive Director
          </h3>
          <p class="text-brandRed text-xs font-semibold uppercase tracking-wider mb-3">
            Executive Management &amp; Organizational Leadership
          </p>
          <p class="text-xs text-slate-600 leading-relaxed max-w-md mx-auto">
            Responsible for overall operational leadership, translating board directives into program execution, supervising departmental directors, and managing donor compliance.
          </p>
        </div>
      </div>

      <!-- ========================================================= -->
      <!-- CONNECTOR 2: Executive Director to the four functional areas -->
      <!-- ========================================================= -->
      <div class="flex justify-center" aria-hidden="true">
        <div class="relative h-12 w-0.5 bg-gradient-to-b from-slate-200 via-slate-300 to-slate-400">
          <i class="fa-solid fa-chevron-down absolute left-1/2 top-full -translate-x-1/2 -translate-y-1/2 text-[9px] text-slate-400 lg:hidden"></i>
        </div>
      </div>

      <!-- Desktop 4-column rail, aligned to the card grid below -->
      <div class="hidden lg:grid grid-cols-4 gap-6 max-w-7xl mx-auto" aria-hidden="true">
        <!-- Column 1: rail from the column centre out to the first gap -->
        <div class="relative h-9">
          <div class="absolute top-0 left-1/2 -right-3 h-0.5 bg-slate-300"></div>
          <div class="absolute top-0 left-1/2 h-6 w-0.5 -translate-x-1/2 bg-slate-300"></div>
          <i class="fa-solid fa-chevron-down absolute left-1/2 top-6 -translate-x-1/2 -translate-y-1/2 text-[9px] text-slate-400"></i>
        </div>

        <!-- Column 2 -->
        <div class="relative h-9">
          <div class="absolute top-0 -left-3 -right-3 h-0.5 bg-slate-300"></div>
          <div class="absolute top-0 left-1/2 h-6 w-0.5 -translate-x-1/2 bg-slate-300"></div>
          <i class="fa-solid fa-chevron-down absolute left-1/2 top-6 -translate-x-1/2 -translate-y-1/2 text-[9px] text-slate-400"></i>
        </div>

        <!-- Column 3 -->
        <div class="relative h-9">
          <div class="absolute top-0 -left-3 -right-3 h-0.5 bg-slate-300"></div>
          <div class="absolute top-0 left-1/2 h-6 w-0.5 -translate-x-1/2 bg-slate-300"></div>
          <i class="fa-solid fa-chevron-down absolute left-1/2 top-6 -translate-x-1/2 -translate-y-1/2 text-[9px] text-slate-400"></i>
        </div>

        <!-- Column 4: rail from the last gap back to the column centre -->
        <div class="relative h-9">
          <div class="absolute top-0 -left-3 right-1/2 h-0.5 bg-slate-300"></div>
          <div class="absolute top-0 left-1/2 h-6 w-0.5 -translate-x-1/2 bg-slate-300"></div>
          <i class="fa-solid fa-chevron-down absolute left-1/2 top-6 -translate-x-1/2 -translate-y-1/2 text-[9px] text-slate-400"></i>
        </div>
      </div>

      <!-- ========================================================= -->
      <!-- TIER 3: FOUR FUNCTIONAL & OPERATIONAL AREAS -->
      <!-- ========================================================= -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">

        <!-- Area 1: Finance & Administration -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 hover:shadow-md hover:border-slate-300 transition-all flex flex-col h-full relative">
          <div class="h-1.5 w-full bg-slate-700 rounded-t-2xl absolute top-0 left-0 right-0"></div>
          <div class="flex items-center gap-2 mb-3 mt-1">
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 text-sm shrink-0">
              <i class="fa-solid fa-calculator"></i>
            </div>
            <div>
              <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Functional Area 01</span>
              <h4 class="text-sm font-bold text-slate-900 leading-tight">Finance &amp; Admin</h4>
            </div>
          </div>
          <p class="text-xs font-semibold text-brandRed mb-3">
            Financial Management &amp; Administrative Oversight
          </p>
          <ul class="text-xs text-slate-600 space-y-2 border-t border-slate-100 pt-3 mt-auto">
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-slate-400 mt-0.5 shrink-0"></i>
              <span>Financial accounting, budgeting &amp; treasury</span>
            </li>
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-slate-400 mt-0.5 shrink-0"></i>
              <span>Donor compliance &amp; statutory external audits</span>
            </li>
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-slate-400 mt-0.5 shrink-0"></i>
              <span>Human resource management &amp; legal admin</span>
            </li>
          </ul>
        </div>

        <!-- Area 2: Programmes & Development -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 hover:shadow-md hover:border-slate-300 transition-all flex flex-col h-full relative">
          <div class="h-1.5 w-full bg-brandRed rounded-t-2xl absolute top-0 left-0 right-0"></div>
          <div class="flex items-center gap-2 mb-3 mt-1">
            <div class="w-9 h-9 rounded-xl bg-brandRed/10 flex items-center justify-center text-brandRed text-sm shrink-0">
              <i class="fa-solid fa-diagram-project"></i>
            </div>
            <div>
              <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Functional Area 02</span>
              <h4 class="text-sm font-bold text-slate-900 leading-tight">Programmes &amp; Development</h4>
            </div>
          </div>
          <p class="text-xs font-semibold text-brandRed mb-3">
            Programme Planning, Development &amp; Implementation
          </p>
          <ul class="text-xs text-slate-600 space-y-2 border-t border-slate-100 pt-3 mt-auto">
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-brandRed/60 mt-0.5 shrink-0"></i>
              <span>Evidence-based project proposals &amp; donor liaison</span>
            </li>
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-brandRed/60 mt-0.5 shrink-0"></i>
              <span>Integrated sector programs (Health, WASH, Food)</span>
            </li>
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-brandRed/60 mt-0.5 shrink-0"></i>
              <span>Monitoring, Evaluation &amp; Learning (MEL)</span>
            </li>
          </ul>
        </div>

        <!-- Area 3: Operations & Logistics -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 hover:shadow-md hover:border-slate-300 transition-all flex flex-col h-full relative">
          <div class="h-1.5 w-full bg-slate-700 rounded-t-2xl absolute top-0 left-0 right-0"></div>
          <div class="flex items-center gap-2 mb-3 mt-1">
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 text-sm shrink-0">
              <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div>
              <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Functional Area 03</span>
              <h4 class="text-sm font-bold text-slate-900 leading-tight">Operations &amp; Logistics</h4>
            </div>
          </div>
          <p class="text-xs font-semibold text-brandRed mb-3">
            Operations, Supply Chain &amp; Logistics Management
          </p>
          <ul class="text-xs text-slate-600 space-y-2 border-t border-slate-100 pt-3 mt-auto">
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-slate-400 mt-0.5 shrink-0"></i>
              <span>Procurement, supply chain &amp; warehousing</span>
            </li>
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-slate-400 mt-0.5 shrink-0"></i>
              <span>Emergency relief distribution logistics</span>
            </li>
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-slate-400 mt-0.5 shrink-0"></i>
              <span>IT infrastructure, assets &amp; fleet security</span>
            </li>
          </ul>
        </div>

        <!-- Area 4: Field & Community Implementation -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 hover:shadow-md hover:border-slate-300 transition-all flex flex-col h-full relative">
          <div class="h-1.5 w-full bg-brandRed rounded-t-2xl absolute top-0 left-0 right-0"></div>
          <div class="flex items-center gap-2 mb-3 mt-1">
            <div class="w-9 h-9 rounded-xl bg-brandRed/10 flex items-center justify-center text-brandRed text-sm shrink-0">
              <i class="fa-solid fa-people-roof"></i>
            </div>
            <div>
              <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Functional Area 04</span>
              <h4 class="text-sm font-bold text-slate-900 leading-tight">Field Implementation</h4>
            </div>
          </div>
          <p class="text-xs font-semibold text-brandRed mb-3">
            Community Mobilization &amp; Frontline Execution
          </p>
          <ul class="text-xs text-slate-600 space-y-2 border-t border-slate-100 pt-3 mt-auto">
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-brandRed/60 mt-0.5 shrink-0"></i>
              <span>Regional offices &amp; district project teams</span>
            </li>
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-brandRed/60 mt-0.5 shrink-0"></i>
              <span>Community mobilization &amp; beneficiary feedback</span>
            </li>
            <li class="flex items-start gap-1.5">
              <i class="fa-solid fa-circle-check text-[11px] text-brandRed/60 mt-0.5 shrink-0"></i>
              <span>District line department coordination</span>
            </li>
          </ul>
        </div>

      </div>

      <!-- Coordination Footer Note -->
      <div class="mt-6 bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center max-w-4xl mx-auto shadow-xs">
        <p class="text-xs text-slate-600 leading-relaxed">
          <strong class="text-slate-900">Inter-Departmental Synergy:</strong> Field &amp; Community Implementation coordinates directly with Programmes &amp; Development for technical delivery, and with Operations &amp; Logistics for material supply chain, reporting to executive leadership under the Executive Director.
        </p>
      </div>

    </div>
  </div>
</section>

<!-- REPORTING INTEGRITY & GOVERNANCE PRINCIPLES -->
<section class="py-12 bg-slate-50 border-t border-slate-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-8">
      <span class="px-4 py-1.5 rounded-full bg-brandRed/10 text-brandRed text-xs font-bold uppercase tracking-widest inline-block mb-3">
        Institutional Integrity
      </span>
      <h2 class="text-3xl md:text-4xl font-display font-bold text-slate-900">
        Principles of Our Organizational Structure
      </h2>
      <p class="text-slate-600 mt-4 text-sm sm:text-base">
        How our structure protects stakeholder trust, enhances programme effectiveness, and maintains strict institutional accountability.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <div class="w-10 h-10 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center mb-4">
          <i class="fa-solid fa-scale-balanced text-sm"></i>
        </div>
        <h3 class="font-bold text-slate-900 text-base mb-2">Independent Oversight</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          The Board of Directors maintains independent governance and fiduciary oversight, separate from day-to-day administrative execution.
        </p>
      </div>

      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <div class="w-10 h-10 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center mb-4">
          <i class="fa-solid fa-arrows-split-up-and-left text-sm"></i>
        </div>
        <h3 class="font-bold text-slate-900 text-base mb-2">Clear Reporting Lines</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          Each functional department has defined lines of communication and authority, preventing operational overlap and bottlenecks.
        </p>
      </div>

      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <div class="w-10 h-10 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center mb-4">
          <i class="fa-solid fa-hand-holding-hand text-sm"></i>
        </div>
        <h3 class="font-bold text-slate-900 text-base mb-2">Community Centric</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          Field and community implementation teams possess direct channels to leadership, ensuring community needs drive institutional priorities.
        </p>
      </div>

      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <div class="w-10 h-10 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center mb-4">
          <i class="fa-solid fa-shield-check text-sm"></i>
        </div>
        <h3 class="font-bold text-slate-900 text-base mb-2">Fiduciary Accountability</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          Finance and Administration reports directly to executive management with independent audit reporting directly to the Board.
        </p>
      </div>
    </div>

    <!-- Quick Navigation Links -->
    <div class="mt-8 pt-10 border-t border-slate-200 flex flex-wrap items-center justify-center gap-4">
      <a href="<?= $base ?>about/leadership.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white border border-slate-300 text-slate-800 font-bold text-xs uppercase tracking-wider hover:bg-slate-100 transition-colors shadow-xs">
        <i class="fa-solid fa-user-tie text-brandRed"></i> View Leadership Team
      </a>
      <a href="<?= $base ?>about/policies-compliance.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white border border-slate-300 text-slate-800 font-bold text-xs uppercase tracking-wider hover:bg-slate-100 transition-colors shadow-xs">
        <i class="fa-solid fa-shield-halved text-brandRed"></i> Policies &amp; Compliance
      </a>
      <a href="<?= $base ?>about/team.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brandRed text-white font-bold text-xs uppercase tracking-wider hover:bg-brandRed-hover transition-colors shadow-sm">
        <i class="fa-solid fa-users text-xs"></i> Meet Our Full Team
      </a>
    </div>

  </div>
</section>

</main>

<?php include dirname( __DIR__ ) . '/includes/footer.php'; ?>
