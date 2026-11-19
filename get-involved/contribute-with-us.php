<?php
/**
 * WRC Website - Let’s Work Together (Dynamic)
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Let’s Build Impact Together - Partner With WRC | WRC';
$metaDescription = 'Are you looking to collaborate on a development initiative, explore a funding opportunity, or engage technical expertise? Partner with WRC to create lasting change.';

// Fetch homepage sections
$homepageSections = getRows('homepage_sections', 'is_active = 1 ORDER BY sort_order ASC');
$sections = [];
foreach ($homepageSections as $s) $sections[$s['section_key']] = $s;

// Override with dedicated table data
$heroRow = getRow('contribute_hero');
if ($heroRow) $sections['contribute_hero'] = ['section_key' => 'contribute_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'content' => json_encode($heroRow)];
$impRow = getRow('contribute_impact');
if ($impRow) {
    $items = getRows('contribute_impact_items', ['impact_id' => $impRow['id']], 'sort_order ASC');
    $sections['contribute_impact'] = ['section_key' => 'contribute_impact', 'section_title' => $impRow['section_title'], 'section_subtitle' => $impRow['section_subtitle'], 'content' => json_encode(['items' => $items])];
}
$transRow = getRow('contribute_transparency');
if ($transRow) {
    $items = getRows('contribute_transparency_items', ['transparency_id' => $transRow['id']], 'sort_order ASC');
    $sections['contribute_transparency'] = ['section_key' => 'contribute_transparency', 'section_title' => $transRow['section_title'], 'section_subtitle' => $transRow['section_subtitle'], 'content' => json_encode(['items' => $items])];
}
$whyRow = getRow('contribute_why');
if ($whyRow) {
    $statsRows = getRows('contribute_why_stats', ['why_id' => $whyRow['id']], 'sort_order ASC');
    $stats = array_map(function($s) { return ['label' => $s['stat_label'], 'value' => $s['stat_value']]; }, $statsRows);
    $sections['contribute_why'] = ['section_key' => 'contribute_why', 'section_title' => $whyRow['section_title'], 'section_subtitle' => $whyRow['section_subtitle'] ?? '', 'content' => json_encode(['stats' => $stats, 'description' => $whyRow['description']])];
}

$heroData       = isset($sections['contribute_hero']) ? json_decode($sections['contribute_hero']['content'], true) : [];
$impactData     = isset($sections['contribute_impact']) ? json_decode($sections['contribute_impact']['content'], true) : [];
$transparencyData = isset($sections['contribute_transparency']) ? json_decode($sections['contribute_transparency']['content'], true) : [];
$whyData        = isset($sections['contribute_why']) ? json_decode($sections['contribute_why']['content'], true) : [];

// Fetch donation tiers
$tiers = getRows('donation_tiers', 'is_active = 1 ORDER BY sort_order ASC');

// Fetch bank accounts and payment channels
$bankAccounts = getRows('bank_accounts', 'is_active = 1 ORDER BY sort_order ASC');

// Donation form data: the six causes, the live portfolio behind each cause, the
// two supported currencies and the USD conversion shown in the form.
$donationCauses     = wrcDonationCauses();
$donationCurrencies = wrcDonationCurrencies();
$donationUsdRate    = wrcDonationUsdRate();

global $pdo;
$donationProjectRows = [];
try {
    $donationProjectRows = $pdo->query(
        'SELECT id, title, slug, category, district FROM projects WHERE is_active = 1 ORDER BY title ASC'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $donationProjectRows = [];
}

$sectorCauseMap = [
    'Health'                     => 'health',
    'WASH'                       => 'water',
    'Food & Relief Distribution' => 'food',
    'Skill Development'          => 'skills',
];
$causeProjects = [];
foreach ($donationCauses as $causeKey => $causeDef) {
    $causeProjects[$causeKey] = [];
}
foreach ($donationProjectRows as $projectRow) {
    $causeKey = 'general';

    // A cause can point at one specific project (hospital solarization).
    foreach ($donationCauses as $candidateKey => $candidate) {
        if (!empty($candidate['project_slug']) && $candidate['project_slug'] === ($projectRow['slug'] ?? '')) {
            $causeKey = $candidateKey;
            break;
        }
    }

    if ($causeKey === 'general') {
        $causeKey = $sectorCauseMap[wrcProgrammeSector((string) ($projectRow['category'] ?? ''))] ?? 'general';
    }

    $causeProjects[$causeKey][] = [
        'id'       => (int) $projectRow['id'],
        'title'    => (string) $projectRow['title'],
        'district' => (string) ($projectRow['district'] ?? ''),
    ];
}

// Payment channels are grouped by type so the form can show the right details.
$bankChannels = [];
$mobileChannels = [];
$payoneerChannels = [];
$taptapChannels = [];
foreach ($bankAccounts as $account) {
    $type = strtolower(trim((string) ($account['account_type'] ?? 'bank')));
    if ($type === '') {
        $type = 'bank';
    }
    if (in_array($type, ['mobile', 'easy'], true)) {
        $mobileChannels[] = $account;
    } elseif ($type === 'payoneer') {
        $payoneerChannels[] = $account;
    } elseif ($type === 'taptap') {
        $taptapChannels[] = $account;
    } else {
        $bankChannels[] = $account;
    }
}

$donationFormData = [
    'causes'     => array_map(static function ($cause) {
        return [
            'label'       => $cause['label'],
            'sector'      => $cause['sector'],
            'icon'        => $cause['icon'],
            'image'       => $cause['image'],
            'tagline'     => $cause['tagline'],
            'impact'      => $cause['impact'],
        ];
    }, $donationCauses),
    'projects'   => $causeProjects,
    'currencies' => $donationCurrencies,
    'usdRate'    => $donationUsdRate,
];

include __DIR__ . '/../includes/header.php';
?>

<!-- HERO -->
<section class="relative bg-slate-900 py-16 md:py-24 overflow-hidden min-h-[480px] md:min-h-[540px] lg:min-h-[580px] flex items-center">
  <div class="absolute inset-0 opacity-50 bg-cover bg-center" style="background-image: url('<?= $base ?><?= !empty($heroData['hero_image']) ? $heroData['hero_image'] : 'assets/images/contribute-hero.jpg' ?>');"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/70 to-slate-950/80"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
    <?php
    $heroBadge = !empty($heroData['badge']) ? $heroData['badge'] : "Let's Work Together";
    $heroTitle = !empty($heroData['title']) ? $heroData['title'] : "Let's Build Impact Together - Partner With WRC";
    $heroSubtitle = !empty($heroData['subtitle']) ? $heroData['subtitle'] : "Are you looking to collaborate on a development initiative, explore a funding opportunity, or engage technical expertise for a community-focused project? We welcome partnerships with government institutions, funding agencies, development organizations, and like-minded stakeholders to turn shared goals into meaningful and sustainable outcomes.\n\nLet's work together to create lasting change.";
    $paragraphs = preg_split('/\r\n\r\n|\n\n/', trim((string) $heroSubtitle));
    ?>
    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-brandRed/20 text-red-300 border border-brandRed/30 mb-5">
      <i class="fa-solid fa-handshake"></i> <?= sanitize($heroBadge) ?>
    </span>
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white mb-6 font-heading max-w-4xl mx-auto leading-tight">
      <?= sanitize($heroTitle) ?>
    </h1>
    <div class="text-slate-300 text-sm sm:text-base md:text-lg max-w-3xl mx-auto leading-relaxed space-y-4">
      <?php foreach ($paragraphs as $para): ?>
        <p><?= sanitize(trim($para)) ?></p>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<!-- DONATION IMPACT -->
<section id="donation-impact" class="relative overflow-hidden scroll-mt-28 bg-white py-20">
  <div class="pointer-events-none absolute -top-24 right-0 h-72 w-72 rounded-full bg-brandRed/5 blur-3xl"></div>
  <div class="pointer-events-none absolute bottom-0 left-0 h-72 w-72 rounded-full bg-sky-100/60 blur-3xl"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mx-auto mb-14 max-w-3xl text-center">
      <span class="mb-3 inline-flex items-center gap-2 rounded-full border border-brandRed/20 bg-brandRed/5 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.25em] text-brandRed">
        <i class="fa-solid fa-heart"></i> <?= sanitize($sections['contribute_impact']['section_subtitle'] ?? 'Your Impact') ?>
      </span>
      <h2 class="font-heading text-3xl font-extrabold text-slate-900 sm:text-4xl">
        <?= sanitize($sections['contribute_impact']['section_title'] ?? 'Choose the Change You Want to Fund') ?>
      </h2>
      <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">
        Every gift is tied to a real WRC project. Pick a cause, choose the community, and give with confidence — we will show you exactly where your support goes.
      </p>
    </div>

    <?php
    $causeStyles = [
      'health'  => ['tile' => 'from-rose-500 to-red-500', 'soft' => 'bg-rose-50 text-rose-600', 'ring' => 'hover:border-rose-200'],
      'water'   => ['tile' => 'from-sky-500 to-cyan-500', 'soft' => 'bg-sky-50 text-sky-600', 'ring' => 'hover:border-sky-200'],
      'food'    => ['tile' => 'from-amber-500 to-orange-500', 'soft' => 'bg-amber-50 text-amber-600', 'ring' => 'hover:border-amber-200'],
      'skills'  => ['tile' => 'from-violet-500 to-purple-500', 'soft' => 'bg-violet-50 text-violet-600', 'ring' => 'hover:border-violet-200'],
      'solar'   => ['tile' => 'from-emerald-500 to-teal-500', 'soft' => 'bg-emerald-50 text-emerald-600', 'ring' => 'hover:border-emerald-200'],
      'general' => ['tile' => 'from-slate-700 to-slate-900', 'soft' => 'bg-slate-100 text-slate-700', 'ring' => 'hover:border-slate-300'],
    ];
    ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($donationCauses as $causeKey => $cause): ?>
      <?php $style = $causeStyles[$causeKey] ?? $causeStyles['general']; ?>
      <article class="donation-cause-card group relative flex cursor-pointer flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 text-left shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-brandRed focus-visible:ring-offset-2 <?= $style['ring'] ?>"
               data-cause="<?= sanitize($causeKey) ?>"
               role="button" tabindex="0"
               aria-label="Donate to <?= sanitize($cause['label']) ?>"
               onclick="selectCause('<?= sanitize($causeKey) ?>')"
               onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); selectCause('<?= sanitize($causeKey) ?>'); }">
        <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r <?= $style['tile'] ?> opacity-0 transition-opacity group-hover:opacity-100"></span>
        <div class="mb-5 flex items-start justify-between gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br <?= $style['tile'] ?> text-white shadow-lg shadow-slate-900/10">
            <i class="fa-solid <?= sanitize($cause['icon']) ?> text-lg"></i>
          </div>
          <span class="rounded-full <?= $style['soft'] ?> px-3 py-1 text-[10px] font-bold uppercase tracking-wider">
            <?= sanitize($cause['sector'] !== '' ? $cause['sector'] : 'Greatest need') ?>
          </span>
        </div>
        <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= sanitize($cause['tagline']) ?></p>
        <h3 class="mb-2 font-heading text-xl font-extrabold text-slate-900"><?= sanitize($cause['label']) ?></h3>
        <p class="mb-5 flex-1 text-sm leading-relaxed text-slate-600"><?= sanitize($cause['description']) ?></p>
        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
          <?php $linkedProjects = count($causeProjects[$causeKey] ?? []); ?>
          <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
            <i class="fa-solid <?= $linkedProjects > 0 ? 'fa-circle-check text-emerald-500' : 'fa-hand-holding-heart text-brandRed' ?>"></i>
            <?= $linkedProjects > 0 ? $linkedProjects . ' project' . ($linkedProjects === 1 ? '' : 's') . ' linked' : 'Flexible giving' ?>
          </span>
          <span class="inline-flex items-center gap-1.5 text-sm font-bold text-brandRed transition-transform group-hover:translate-x-0.5">
            Donate Now <i class="fa-solid fa-arrow-right text-[10px]"></i>
          </span>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- DONATION FORM -->
<section id="donate" class="relative overflow-hidden bg-slate-50 py-20 scroll-mt-24">
  <div class="pointer-events-none absolute -left-32 top-10 h-96 w-96 rounded-full bg-brandRed/5 blur-3xl"></div>
  <div class="pointer-events-none absolute -right-24 bottom-0 h-96 w-96 rounded-full bg-sky-100/70 blur-3xl"></div>

  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mx-auto mb-12 max-w-3xl text-center">
      <span class="mb-3 inline-flex items-center gap-2 rounded-full border border-brandRed/20 bg-white px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.25em] text-brandRed shadow-sm">
        <i class="fa-solid fa-lock"></i> Secure Donation
      </span>
      <h2 class="font-heading text-3xl font-extrabold text-slate-900 sm:text-4xl">Make a Donation</h2>
      <p class="mt-4 text-sm leading-relaxed text-slate-600 sm:text-base">
        Choose a cause, pick a project and give in the currency that suits you. Every donation is recorded and receipted by our team.
      </p>
    </div>

    <div class="grid items-start gap-8 lg:grid-cols-[0.82fr_1.18fr]">

      <!-- ---------------------------------------------------------- visual -->
      <aside class="space-y-6">
        <div class="group relative overflow-hidden rounded-3xl bg-slate-900 shadow-2xl shadow-slate-900/20">
          <img id="donation-cause-image"
               src="<?= $base . sanitize($donationCauses['general']['image']) ?>"
               alt="WRC field team supporting families in Pakistan"
               class="h-[320px] w-full object-cover opacity-90 transition-transform duration-700 group-hover:scale-105 sm:h-[380px] lg:h-[420px]">
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
          <div class="absolute inset-x-0 bottom-0 p-6 sm:p-7">
            <span id="donation-cause-sector" class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-white backdrop-blur">
              <i class="fa-solid fa-hand-holding-heart"></i> Greatest need
            </span>
            <h3 id="donation-cause-title" class="font-heading text-2xl font-extrabold text-white sm:text-[26px]">Where Most Needed</h3>
            <p id="donation-cause-impact" class="mt-2 max-w-sm text-sm leading-relaxed text-white/80">
              Flexible giving reaches needs as soon as they appear.
            </p>
          </div>
        </div>

        <!-- live summary -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="mb-4 flex items-center justify-between">
            <h4 class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">Your Gift at a Glance</h4>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-600">
              <i class="fa-solid fa-shield-halved"></i> Verified
            </span>
          </div>
          <div class="flex items-end gap-2 border-b border-dashed border-slate-200 pb-4">
            <span id="summary-amount" class="font-heading text-3xl font-extrabold text-slate-900">PKR 5,000</span>
          </div>
          <dl class="mt-4 space-y-3 text-sm">
            <div class="flex items-start justify-between gap-4">
              <dt class="text-slate-500">Cause</dt>
              <dd id="summary-cause" class="text-right font-semibold text-slate-900">Where Most Needed</dd>
            </div>
            <div class="flex items-start justify-between gap-4">
              <dt class="text-slate-500">Project</dt>
              <dd id="summary-project" class="max-w-[60%] text-right font-semibold text-slate-900">Any project in this cause</dd>
            </div>
            <div class="flex items-start justify-between gap-4">
              <dt class="text-slate-500">Payment</dt>
              <dd id="summary-payment" class="text-right font-semibold text-slate-900">Bank Transfer</dd>
            </div>
            <div id="summary-conversion-row" class="hidden flex items-start justify-between gap-4">
              <dt class="text-slate-500">Approx. in PKR</dt>
              <dd id="summary-conversion" class="text-right font-semibold text-slate-900">—</dd>
            </div>
          </dl>
          <p class="mt-5 rounded-xl bg-slate-50 px-3 py-2.5 text-[11px] leading-relaxed text-slate-500">
            <i class="fa-solid fa-circle-info mr-1 text-brandRed"></i>
            A receipt is emailed within 2 working days after your payment is verified.
          </p>
        </div>

        <div class="grid grid-cols-3 gap-3 text-center">
          <div class="rounded-2xl border border-slate-200 bg-white px-2 py-3">
            <i class="fa-solid fa-building-columns mb-1.5 block text-brandRed"></i>
            <p class="text-[10px] font-semibold leading-tight text-slate-600">Bank &amp; Wallet</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white px-2 py-3">
            <i class="fa-solid fa-globe mb-1.5 block text-brandRed"></i>
            <p class="text-[10px] font-semibold leading-tight text-slate-600">PKR &amp; USD</p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white px-2 py-3">
            <i class="fa-solid fa-receipt mb-1.5 block text-brandRed"></i>
            <p class="text-[10px] font-semibold leading-tight text-slate-600">Receipt Issued</p>
          </div>
        </div>
      </aside>

      <!-- ------------------------------------------------------------ form -->
      <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/5 sm:p-8">
        <form id="donation-form" onsubmit="event.preventDefault(); handleDonationSubmit(this);">
          <input type="hidden" name="cause" id="donation-cause" value="general">
          <input type="hidden" name="donation_amount" id="donation-amount" value="">

          <!-- selected cause -->
          <div class="mb-8 rounded-2xl border border-brandRed/15 bg-brandRed/[0.03] p-4 sm:p-5">
            <div class="flex items-start gap-3">
              <span id="form-cause-icon" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brandRed/10 text-brandRed">
                <i class="fa-solid fa-hand-holding-heart"></i>
              </span>
              <div class="min-w-0 flex-1">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">You are supporting</p>
                <p id="form-cause-name" class="font-heading text-lg font-extrabold leading-tight text-slate-900">Where Most Needed</p>
                <p id="form-cause-note" class="mt-0.5 text-[11px] leading-relaxed text-slate-500">Choose a cause above to link your donation to a specific project.</p>
              </div>
              <a href="#donation-impact" class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[10px] font-bold text-slate-500 transition hover:border-brandRed hover:text-brandRed">
                <i class="fa-solid fa-arrow-up text-[9px]"></i> Change
              </a>
            </div>

            <div class="mt-4">
              <label for="donation-project" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Project / Area</label>
              <div class="relative">
                <select id="donation-project" name="project_id"
                        class="w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-3 pr-10 text-sm font-medium text-slate-800 outline-none transition-all focus:border-brandRed focus:ring-2 focus:ring-brandRed/20">
                  <option value="">Any project in this cause</option>
                </select>
                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
              </div>
              <p id="project-hint" class="mt-1.5 text-[11px] text-slate-400">Your gift will support the most urgent work in this cause.</p>
            </div>
          </div>

          <div class="mb-8 border-t border-slate-100 pt-8">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brandRed/10 text-[11px] font-bold text-brandRed">1</span>
                <h3 class="font-heading text-base font-bold text-slate-900">Donation amount</h3>
              </div>
              <div class="inline-flex rounded-xl bg-slate-100 p-1">
                <?php foreach ($donationCurrencies as $code => $currency): ?>
                <button type="button" data-currency="<?= sanitize($code) ?>" onclick="setCurrency('<?= sanitize($code) ?>')"
                        class="currency-tab rounded-lg px-4 py-1.5 text-xs font-bold transition-all <?= $code === 'PKR' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' ?>">
                  <?= sanitize($currency['symbol']) ?>
                </button>
                <?php endforeach; ?>
              </div>
            </div>

            <div id="amount-presets" class="mb-3 grid grid-cols-3 gap-2.5 sm:grid-cols-6"></div>
            <div class="relative">
              <span id="amount-symbol" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">PKR</span>
              <input type="number" id="custom-amount" min="1" step="1" inputmode="decimal"
                     placeholder="Enter a custom amount"
                     class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 py-3.5 pl-14 pr-4 text-sm font-bold text-slate-900 outline-none transition-all placeholder:font-normal placeholder:text-slate-400 focus:border-brandRed focus:bg-white focus:ring-2 focus:ring-brandRed/20"
                     oninput="clearAmountBtns()">
            </div>
            <p id="conversion-hint" class="mt-2 hidden text-[11px] font-medium text-slate-500"></p>
          </div>

          <div class="mb-8 border-t border-slate-100 pt-8">
            <div class="mb-3 flex items-center gap-3">
              <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brandRed/10 text-[11px] font-bold text-brandRed">2</span>
              <h3 class="font-heading text-base font-bold text-slate-900">Your details</h3>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="sm:col-span-2">
                <label for="donor-name" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Full Name <span class="text-brandRed">*</span></label>
                <input id="donor-name" type="text" name="full_name" required autocomplete="name"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition-all focus:border-brandRed focus:bg-white focus:ring-2 focus:ring-brandRed/20"
                       placeholder="e.g. Ahmed Khan">
              </div>
              <div>
                <label for="donor-email" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Email <span class="text-brandRed">*</span></label>
                <input id="donor-email" type="email" name="email" required autocomplete="email"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition-all focus:border-brandRed focus:bg-white focus:ring-2 focus:ring-brandRed/20"
                       placeholder="you@email.com">
              </div>
              <div>
                <label for="donor-phone" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Phone / WhatsApp</label>
                <input id="donor-phone" type="tel" name="phone" autocomplete="tel"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition-all focus:border-brandRed focus:bg-white focus:ring-2 focus:ring-brandRed/20"
                       placeholder="+92 300 0000000">
              </div>
            </div>
          </div>

          <div class="mb-8 border-t border-slate-100 pt-8">
            <div class="mb-3 flex items-center gap-3">
              <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brandRed/10 text-[11px] font-bold text-brandRed">3</span>
              <h3 class="font-heading text-base font-bold text-slate-900">Payment method</h3>
            </div>

            <?php
            $paymentTabs = [
              ['key' => 'bank',     'label' => 'Bank Transfer',      'icon' => 'fa-building-columns', 'hint' => 'All Pakistani banks'],
              ['key' => 'easy',     'label' => 'Mobile Wallet',      'icon' => 'fa-mobile-screen-button', 'hint' => 'EasyPaisa / JazzCash'],
              ['key' => 'payoneer', 'label' => 'Payoneer',           'icon' => 'fa-globe', 'hint' => 'For international donors'],
              ['key' => 'taptap',   'label' => 'TapTap Send',        'icon' => 'fa-paper-plane', 'hint' => 'Remit to Pakistan'],
            ];
            ?>
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
              <?php foreach ($paymentTabs as $tab): ?>
              <label class="payment-tab flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border-2 border-slate-200 px-2 py-3 text-center transition-all hover:border-brandRed/40 has-[:checked]:border-brandRed has-[:checked]:bg-brandRed/5">
                <input type="radio" name="payment" value="<?= $tab['key'] ?>" <?= $tab['key'] === 'bank' ? 'checked' : '' ?>
                       onchange="selectPayment('<?= $tab['key'] ?>')" class="sr-only">
                <i class="fa-solid <?= $tab['icon'] ?> text-base text-slate-500"></i>
                <span class="text-[11px] font-bold leading-tight text-slate-800"><?= $tab['label'] ?></span>
                <span class="text-[9px] leading-tight text-slate-400"><?= $tab['hint'] ?></span>
              </label>
              <?php endforeach; ?>
            </div>

            <!-- bank -->
            <div id="payment-panel-bank" class="payment-panel mt-4 space-y-3">
              <?php if (!empty($bankChannels)): ?>
                <?php foreach ($bankChannels as $channel): ?>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <div class="mb-2 flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-slate-900"><?= sanitize($channel['bank_name'] ?? '') ?></p>
                    <span class="rounded-full bg-white px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-slate-500"><?= sanitize($channel['currency'] ?? 'PKR') ?></span>
                  </div>
                  <dl class="space-y-1.5 text-xs">
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Account Title</dt><dd class="text-right font-semibold text-slate-800"><?= sanitize($channel['account_title'] ?? '') ?></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Account Number</dt><dd class="text-right font-mono font-semibold text-slate-800"><?= sanitize($channel['account_number'] ?? '') ?></dd></div>
                    <?php if (!empty($channel['iban'])): ?>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">IBAN</dt><dd class="text-right font-mono text-[11px] font-semibold text-slate-800"><?= sanitize($channel['iban']) ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($channel['branch'])): ?>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Branch</dt><dd class="text-right font-semibold text-slate-800"><?= sanitize($channel['branch']) ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($channel['swift_code'])): ?>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Swift</dt><dd class="text-right font-mono font-semibold text-slate-800"><?= sanitize($channel['swift_code']) ?></dd></div>
                    <?php endif; ?>
                  </dl>
                  <?php if (!empty($channel['iban']) || !empty($channel['account_number'])): ?>
                  <button type="button" data-copy="<?= sanitize($channel['iban'] ?: $channel['account_number']) ?>" onclick="copyText(this)"
                          class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-bold text-brandRed hover:underline">
                    <i class="fa-regular fa-copy"></i> Copy account details
                  </button>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="rounded-xl bg-slate-50 px-4 py-3 text-xs text-slate-500">Bank details will be shared by our team on request.</p>
              <?php endif; ?>
            </div>

            <!-- mobile wallet -->
            <div id="payment-panel-easy" class="payment-panel mt-4 hidden">
              <?php $walletChannels = !empty($mobileChannels) ? $mobileChannels : []; ?>
              <?php foreach ($walletChannels as $channel): ?>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="mb-2 flex items-center justify-between gap-3">
                  <p class="text-sm font-bold text-slate-900"><?= sanitize($channel['bank_name'] ?? 'EasyPaisa / JazzCash') ?></p>
                  <span class="rounded-full bg-white px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-slate-500"><?= sanitize($channel['currency'] ?? 'PKR') ?></span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5">
                  <div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-400">Mobile Number</p>
                    <p class="font-mono text-sm font-bold text-slate-900"><?= sanitize($channel['account_number'] ?? '') ?></p>
                  </div>
                  <button type="button" data-copy="<?= sanitize($channel['account_number'] ?? '') ?>" onclick="copyText(this)"
                          class="inline-flex items-center gap-1 text-[11px] font-bold text-brandRed hover:underline">
                    <i class="fa-regular fa-copy"></i> Copy
                  </button>
                </div>
                <p class="mt-2 text-[11px] text-slate-500">Account Title: <span class="font-semibold text-slate-700"><?= sanitize($channel['account_title'] ?? 'Welfare & Relief Committee') ?></span></p>
                <?php if (!empty($channel['notes'])): ?>
                <p class="mt-1 text-[11px] text-slate-500"><?= sanitize($channel['notes']) ?></p>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
              <?php if (empty($walletChannels)): ?>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5">
                  <div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-400">Mobile Number</p>
                    <p class="font-mono text-sm font-bold text-slate-900">0300-1234567</p>
                  </div>
                  <button type="button" data-copy="0300-1234567" onclick="copyText(this)" class="inline-flex items-center gap-1 text-[11px] font-bold text-brandRed hover:underline">
                    <i class="fa-regular fa-copy"></i> Copy
                  </button>
                </div>
                <p class="mt-2 text-[11px] text-slate-500">Send from your EasyPaisa or JazzCash app to this number, then upload the screenshot below.</p>
              </div>
              <?php endif; ?>
            </div>

            <!-- payoneer -->
            <div id="payment-panel-payoneer" class="payment-panel mt-4 hidden">
              <?php $payoneerChannel = !empty($payoneerChannels) ? reset($payoneerChannels) : null; ?>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="mb-2 text-sm font-bold text-slate-900">Payoneer (International)</p>
                <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5">
                  <div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-400">Payoneer Email</p>
                    <p class="break-all font-mono text-sm font-bold text-slate-900"><?= sanitize($payoneerChannel['account_number'] ?? 'donations@wrc.org.pk') ?></p>
                  </div>
                  <button type="button" data-copy="<?= sanitize($payoneerChannel['account_number'] ?? 'donations@wrc.org.pk') ?>" onclick="copyText(this)"
                          class="ml-3 inline-flex shrink-0 items-center gap-1 text-[11px] font-bold text-brandRed hover:underline">
                    <i class="fa-regular fa-copy"></i> Copy
                  </button>
                </div>
                <p class="mt-2 text-[11px] leading-relaxed text-slate-500">
                  Send from your Payoneer balance in USD, then upload the payment screenshot so our finance team can verify and receipt it.
                </p>
              </div>
            </div>

            <!-- taptap -->
            <div id="payment-panel-taptap" class="payment-panel mt-4 hidden">
              <?php $taptapChannel = !empty($taptapChannels) ? reset($taptapChannels) : null; ?>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="mb-2 text-sm font-bold text-slate-900">TapTap Send (International)</p>
                <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5">
                  <div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-400">Mobile Number (Pakistan)</p>
                    <p class="font-mono text-sm font-bold text-slate-900"><?= sanitize($taptapChannel['account_number'] ?? '0300-1234567') ?></p>
                  </div>
                  <button type="button" data-copy="<?= sanitize($taptapChannel['account_number'] ?? '0300-1234567') ?>" onclick="copyText(this)"
                          class="ml-3 inline-flex shrink-0 items-center gap-1 text-[11px] font-bold text-brandRed hover:underline">
                    <i class="fa-regular fa-copy"></i> Copy
                  </button>
                </div>
                <p class="mt-2 text-[11px] leading-relaxed text-slate-500">
                  Use TapTap Send to remit to this number in Pakistan, then upload the transfer screenshot below.
                </p>
              </div>
            </div>

            <!-- proof + reference -->
            <div class="mt-4">
              <label class="mb-1.5 flex items-center justify-between gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <span>Payment Screenshot <span id="proof-required" class="hidden text-brandRed">*</span></span>
                <span class="text-[9px] font-semibold normal-case tracking-normal text-slate-400">JPG, PNG or PDF · max 20MB</span>
              </label>
              <label for="payment-proof" id="proof-drop"
                     class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition-all hover:border-brandRed/50 hover:bg-brandRed/5">
                <i class="fa-solid fa-cloud-arrow-up text-xl text-slate-400"></i>
                <span class="text-xs font-semibold text-slate-600">Click to upload your payment screenshot</span>
                <span id="proof-filename" class="text-[11px] text-slate-400">Required for mobile wallet, Payoneer and TapTap Send</span>
              </label>
              <input id="payment-proof" type="file" name="payment_proof" accept="image/jpeg,image/png,application/pdf" class="sr-only" onchange="previewProof(this)">
              <div id="proof-preview" class="mt-3 hidden items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <img id="proof-preview-img" src="" alt="Payment screenshot preview" class="h-14 w-14 rounded-lg object-cover">
                <div class="min-w-0 flex-1">
                  <p class="truncate text-xs font-bold text-emerald-800" id="proof-preview-name">screenshot.png</p>
                  <p class="text-[10px] text-emerald-600">Attached — ready to send</p>
                </div>
                <button type="button" onclick="clearProof()" class="text-[11px] font-bold text-emerald-700 hover:underline">Remove</button>
              </div>
            </div>

            <div class="mt-4">
              <label for="payment-reference" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Transaction ID / Reference <span class="font-normal normal-case text-slate-400">(optional)</span></label>
              <input id="payment-reference" type="text" name="payment_reference"
                     class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition-all focus:border-brandRed focus:bg-white focus:ring-2 focus:ring-brandRed/20"
                     placeholder="e.g. TXN123456789">
            </div>
          </div>

          <div class="mb-6 border-t border-slate-100 pt-6">
            <label for="donation-message" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500">Message <span class="font-normal normal-case text-slate-400">(optional)</span></label>
            <textarea id="donation-message" name="donation_message" rows="3"
                      class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition-all focus:border-brandRed focus:bg-white focus:ring-2 focus:ring-brandRed/20"
                      placeholder="Dedicate this donation or leave a note for our team."></textarea>
          </div>

          <label class="mb-5 flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 p-3.5">
            <input type="checkbox" name="donation_consent" required class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brandRed focus:ring-brandRed">
            <span class="text-[11px] leading-relaxed text-slate-600">
              I confirm the details above are correct and agree that WRC may contact me about this donation and its receipt.
            </span>
          </label>

          <button type="submit" id="donation-submit"
                  class="w-full rounded-xl bg-brandRed py-4 text-sm font-bold uppercase tracking-wider text-white shadow-lg shadow-brandRed/25 transition-all hover:bg-brandRed-hover active:scale-[0.99]">
            <i class="fa-solid fa-hand-holding-heart mr-2"></i>Complete Donation
          </button>

          <div id="donate-success" class="mt-5 hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <div class="flex items-start gap-3">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white"><i class="fa-solid fa-check"></i></span>
              <div class="min-w-0">
                <h4 class="font-heading text-base font-bold text-emerald-900">Thank you for your generosity!</h4>
                <p class="mt-1 text-xs leading-relaxed text-emerald-700" id="donate-success-text">
                  Your donation has been recorded. Our team will verify your payment and email a receipt.
                </p>
              </div>
            </div>
            <button type="button" onclick="resetDonationForm()" class="mt-4 inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-800 hover:underline">
              <i class="fa-solid fa-rotate-left"></i> Make another donation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- TRANSPARENCY STRIP -->
<section class="py-12 bg-white border-t border-slate-200">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid sm:grid-cols-3 gap-6 text-center">
      <?php
      $defaultTransparency = [
        ['icon' => 'fa-solid fa-check-double', 'color' => 'green', 'title' => '100% Direct Aid', 'description' => 'Every rupee goes directly to community programs'],
        ['icon' => 'fa-solid fa-file-shield', 'color' => 'blue', 'title' => 'Fully Audited', 'description' => 'Annual external audits by independent firms'],
        ['icon' => 'fa-solid fa-award', 'color' => 'purple', 'title' => 'Registered NGO', 'description' => 'SECP registered, tax-exempt charitable organization'],
      ];
      $transItems = $transparencyData['items'] ?? $defaultTransparency;
      foreach ($transItems as $item):
        $color = $item['color'] ?? 'green';
        $colorMap = [
          'green'  => ['bg' => 'bg-green-50',  'text' => 'text-green-500'],
          'blue'   => ['bg' => 'bg-blue-50',   'text' => 'text-blue-500'],
          'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-500'],
        ];
        $c = $colorMap[$color] ?? $colorMap['green'];
      ?>
      <div class="flex flex-col items-center">
        <div class="w-12 h-12 rounded-full <?= $c['bg'] ?> flex items-center justify-center mb-3">
          <i class="<?= sanitize($item['icon'] ?? 'fa-solid fa-check') ?> <?= $c['text'] ?> text-lg"></i>
        </div>
        <h4 class="font-bold text-slate-900 text-sm mb-1"><?= sanitize($item['title'] ?? '') ?></h4>
        <p class="text-xs text-slate-500"><?= sanitize($item['description'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- WHY PARTNER WITH WRC -->
<section class="py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-2 items-center">
      <!-- Left: Value Propositions -->
      <div>
        <span class="text-brandRed text-xs font-semibold uppercase tracking-[0.35em] mb-3 block">
          <?= sanitize($sections['contribute_why']['section_subtitle'] ?? 'Why Partner With WRC') ?>
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-6">
          <?= sanitize($sections['contribute_why']['section_title'] ?? 'A Professional Approach to Community Impact') ?>
        </h2>
        <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
          <?= sanitize($whyData['description'] ?? 'WRC operates through a public-private partnership model, working with government bodies, international organizations and institutional partners to deliver projects that create measurable and sustainable outcomes.') ?>
        </p>
        <div class="space-y-4">
          <?php
          $defaultWhy = [
            ['title' => 'Project-Based Collaboration', 'description' => 'All initiatives are structured as formal projects with clear objectives, timelines and accountability.'],
            ['title' => 'Transparent Operations', 'description' => 'Fully audited financials, compliance policies and regular reporting to all stakeholders.'],
            ['title' => 'Measurable Outcomes', 'description' => 'Monitoring & evaluation frameworks track real impact on communities served.'],
            ['title' => 'Institutional Partnerships', 'description' => 'Working alongside government departments, UN agencies and development organizations.'],
          ];
          $whyItems = $whyData['values'] ?? $defaultWhy;
          foreach ($whyItems as $item):
          ?>
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-brandRed/10 flex items-center justify-center shrink-0 mt-0.5">
              <i class="fa-solid fa-check text-brandRed text-xs"></i>
            </div>
            <div>
              <h4 class="font-semibold text-slate-900 text-sm"><?= sanitize($item['title'] ?? '') ?></h4>
              <p class="text-slate-500 text-xs mt-1"><?= sanitize($item['description'] ?? '') ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right: Stats Grid -->
      <div class="bg-slate-50 rounded-3xl border border-slate-200 p-8 lg:p-10">
        <div class="grid grid-cols-2 gap-4">
          <?php
          // Coverage counters follow the live portfolio; only the labels are
          // fixed so a dashboard edit and the dashboard's own numbers agree.
          $coverage = wrcProjectImpactSummary();
          $defaultStats = [
            ['value' => (string) $coverage['projects'], 'label' => 'Projects Completed'],
            ['value' => '8+', 'label' => 'Active Partners'],
            ['value' => wrcFormatBeneficiaries(wrcBeneficiaryTotal()), 'label' => 'Beneficiaries Reached'],
            ['value' => (string) $coverage['provinces'], 'label' => 'Provinces Covered'],
          ];
          $statItems = $whyData['stats'] ?? $defaultStats;
          $coverageLabels = [
            'projects completed'    => 'projects',
            'projects delivered'    => 'projects',
            'total projects'        => 'projects',
            'provinces covered'     => 'provinces',
            'districts covered'     => 'districts',
            'beneficiaries reached' => 'beneficiaries',
          ];
          foreach ($statItems as &$coverageStat) {
            $coverageKey = strtolower(trim((string) ($coverageStat['label'] ?? '')));
            if (isset($coverageLabels[$coverageKey])) {
              $coverageStat['value'] = number_format((int) $coverage[$coverageLabels[$coverageKey]]);
            }
          }
          unset($coverageStat);
          foreach ($statItems as $stat):
          ?>
          <div class="bg-white rounded-2xl p-5 border border-slate-100 text-center">
            <div class="text-2xl font-extrabold text-brandRed mb-1 font-heading"><?= sanitize($stat['value'] ?? '') ?></div>
            <p class="text-xs text-slate-500 font-medium"><?= sanitize($stat['label'] ?? '') ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- COLLABORATION INQUIRY FORM -->
<section id="contact-form" class="py-16 bg-slate-50">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <span class="text-brandRed text-xs font-semibold uppercase tracking-[0.35em] mb-3 block">Get Started</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Explore Collaboration With WRC</h2>
      <p class="mt-4 text-slate-600 text-sm sm:text-base max-w-2xl mx-auto">Complete the inquiry form below and our partnerships team will review your submission and respond within 5 business days.</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-10">
      <form id="partner-collab-form" onsubmit="event.preventDefault(); handlePartnerSubmit(this);">
        <div class="grid sm:grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Name <span class="text-brandRed">*</span></label>
            <input type="text" name="collab_name" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Your full name" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Organization <span class="text-brandRed">*</span></label>
            <input type="text" name="collab_org" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Your organization name" />
          </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Designation</label>
            <input type="text" name="collab_designation" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Your role / title" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Address <span class="text-brandRed">*</span></label>
            <input type="email" name="collab_email" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="your@email.com" />
          </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-6 mb-6">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Phone Number</label>
            <input type="tel" name="collab_phone" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all" placeholder="Optional" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Area of Interest <span class="text-brandRed">*</span></label>
            <select name="collab_interest" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all text-slate-700">
              <option value="">Select an area...</option>
              <option>Project Partnership</option>
              <option>Technical Expertise</option>
              <option>Institutional Collaboration</option>
              <option>Resources / Services</option>
              <option>Knowledge Sharing</option>
              <option>Corporate Engagement</option>
              <option>Other</option>
            </select>
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Message <span class="text-brandRed">*</span></label>
          <textarea name="collab_message" required rows="5" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed focus:border-brandRed outline-none transition-all resize-none" placeholder="Describe how you would like to collaborate with WRC..."></textarea>
        </div>

        <div class="flex items-center gap-3 mb-6">
          <input type="checkbox" id="privacy" required class="w-4 h-4 text-brandRed rounded border-slate-300 focus:ring-brandRed">
          <label for="privacy" class="text-xs text-slate-500">I agree to the <a href="<?= $base ?>about/policies-compliance.php" class="text-brandRed hover:underline">Privacy Policy</a> and consent to being contacted regarding this collaboration inquiry.</label>
        </div>

        <button type="submit" class="w-full bg-slate-900 hover:bg-brandRed text-white font-bold py-4 rounded-xl transition-colors shadow-md text-sm uppercase tracking-wider">
          <i class="fa-solid fa-paper-plane mr-2"></i> Submit Inquiry
        </button>
      </form>
    </div>
  </div>
</section>

<!-- FINAL CTA -->
<section class="py-20 bg-brandRed text-center relative overflow-hidden">
  <div class="absolute inset-0 bg-cover bg-center mix-blend-multiply opacity-20" style="background-image: url('<?= $base ?>assets/images/hero/contribute-hero.jpg');"></div>
  <div class="container relative z-10 mx-auto px-4">
    <h2 class="text-3xl md:text-4xl font-display font-bold text-white mb-5 max-w-3xl mx-auto leading-tight">
      Together, we can turn community needs into meaningful action.
    </h2>
    <p class="text-white/80 text-sm max-w-xl mx-auto mb-8">Explore our projects and discover how your organization can contribute to sustainable development outcomes across Pakistan.</p>
    <div class="flex flex-col sm:flex-row justify-center gap-4">
      <a href="<?= $base ?>projects/index.php" class="px-8 py-4 rounded-xl bg-white text-brandRed font-bold text-sm shadow-lg hover:bg-sand-50 transition-colors uppercase tracking-wider">
        Explore Our Projects
      </a>
      <a href="<?= $base ?>contact/index.php" class="px-8 py-4 rounded-xl bg-transparent border-2 border-white text-white font-bold text-sm hover:bg-white/10 transition-colors uppercase tracking-wider">
        Contact Us
      </a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
const WRC_DONATION = <?= json_encode($donationFormData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

const donationState = {
  cause: 'general',
  currency: 'PKR',
  amount: 0,
  payment: 'bank'
};

function donationFormatMoney(value, currency) {
  const rounded = Math.round(Number(value) || 0);
  if (currency === 'USD') {
    return '$' + rounded.toLocaleString('en-US');
  }
  return 'PKR ' + rounded.toLocaleString('en-US');
}

function donationPresetValue(value, currency) {
  return currency === 'USD' ? '$' + value.toLocaleString('en-US') : value.toLocaleString('en-US');
}

function renderAmountPresets() {
  const wrap = document.getElementById('amount-presets');
  if (!wrap) return;
  const currency = WRC_DONATION.currencies[donationState.currency];
  wrap.innerHTML = '';
  (currency.presets || []).forEach(function (value) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'amount-btn rounded-xl border-2 border-slate-200 px-2 py-2.5 text-xs font-bold text-slate-700 transition-all hover:border-brandRed/50 hover:text-brandRed';
    btn.textContent = donationPresetValue(value, donationState.currency);
    btn.dataset.amount = value;
    btn.addEventListener('click', function () { setAmount(value); });
    wrap.appendChild(btn);
  });

  const symbol = document.getElementById('amount-symbol');
  if (symbol) symbol.textContent = currency.symbol;
}

function setAmount(value) {
  const input = document.getElementById('donation-amount');
  const custom = document.getElementById('custom-amount');
  if (input) input.value = value;
  if (custom) custom.value = '';
  document.querySelectorAll('.amount-btn').forEach(function (btn) {
    const isActive = Number(btn.dataset.amount) === Number(value);
    btn.classList.toggle('border-brandRed', isActive);
    btn.classList.toggle('text-brandRed', isActive);
    btn.classList.toggle('bg-brandRed/5', isActive);
  });
  updateSummary();
}

function clearAmountBtns() {
  const custom = document.getElementById('custom-amount');
  const hidden = document.getElementById('donation-amount');
  const value = custom ? Number(custom.value) : 0;
  if (hidden) hidden.value = value > 0 ? custom.value : '';
  document.querySelectorAll('.amount-btn').forEach(function (btn) {
    btn.classList.remove('border-brandRed', 'text-brandRed', 'bg-brandRed/5');
  });
  updateSummary();
}

function setCurrency(code) {
  if (!WRC_DONATION.currencies[code]) return;
  donationState.currency = code;
  document.querySelectorAll('.currency-tab').forEach(function (tab) {
    const active = tab.dataset.currency === code;
    tab.classList.toggle('bg-white', active);
    tab.classList.toggle('text-slate-900', active);
    tab.classList.toggle('shadow-sm', active);
    tab.classList.toggle('text-slate-500', !active);
  });
  renderAmountPresets();
  setAmount(0);
  updateSummary();
}

function selectCause(key, options) {
  options = options || {};
  const cause = WRC_DONATION.causes[key];
  if (!cause) return;

  donationState.cause = key;
  const hidden = document.getElementById('donation-cause');
  if (hidden) hidden.value = key;

  const image = document.getElementById('donation-cause-image');
  if (image && cause.image) {
    image.style.opacity = '0';
    const nextSrc = wrcSiteBase() + cause.image;
    const preload = new Image();
    preload.onload = function () {
      image.src = nextSrc;
      image.style.opacity = '0.9';
    };
    preload.onerror = function () { image.style.opacity = '0.9'; };
    preload.src = nextSrc;
  }
  const title = document.getElementById('donation-cause-title');
  if (title) title.textContent = cause.label;
  const sector = document.getElementById('donation-cause-sector');
  if (sector) sector.innerHTML = '<i class="fa-solid ' + cause.icon + '"></i> ' + (cause.sector || 'Greatest need');
  const impact = document.getElementById('donation-cause-impact');
  if (impact) impact.textContent = cause.impact || '';

  const formIcon = document.getElementById('form-cause-icon');
  if (formIcon) formIcon.innerHTML = '<i class="fa-solid ' + cause.icon + '"></i>';
  const formName = document.getElementById('form-cause-name');
  if (formName) formName.textContent = cause.label;
  const formNote = document.getElementById('form-cause-note');
  if (formNote) formNote.textContent = (cause.sector ? cause.sector + ' · ' : '') + (cause.tagline || '');

  renderProjects(key);
  updateSummary();

  if (options.scroll !== false) {
    const section = document.getElementById('donate');
    if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    const form = document.getElementById('donation-form');
    if (form) {
      const nameInput = form.querySelector('#donor-name');
      if (nameInput) setTimeout(function () { try { nameInput.focus({ preventScroll: true }); } catch (e) {} }, 500);
    }
  }
}

function renderProjects(key) {
  const select = document.getElementById('donation-project');
  const hint = document.getElementById('project-hint');
  if (!select) return;

  const projects = (WRC_DONATION.projects && WRC_DONATION.projects[key]) || [];
  select.innerHTML = '<option value="">Any project in this cause</option>';
  projects.forEach(function (project) {
    const option = document.createElement('option');
    option.value = project.id;
    option.textContent = project.district ? project.title + ' — ' + project.district : project.title;
    option.dataset.title = project.title;
    select.appendChild(option);
  });

  // A focused cause such as hospital solarization has one natural project.
  if (projects.length === 1) {
    select.value = String(projects[0].id);
  }

  if (hint) {
    hint.textContent = projects.length
      ? projects.length + ' project' + (projects.length === 1 ? '' : 's') + ' linked to this cause. Leave it on the default to reach the most urgent one.'
      : 'Your gift will support the most urgent work in this cause.';
  }
  updateSummary();
}

function selectedProjectTitle() {
  const select = document.getElementById('donation-project');
  if (!select || !select.value) return 'Any project in this cause';
  const option = select.options[select.selectedIndex];
  if (!option) return 'Any project in this cause';
  return option.dataset.title || option.textContent;
}

function currentAmount() {
  const hidden = document.getElementById('donation-amount');
  const custom = document.getElementById('custom-amount');
  let value = hidden && hidden.value ? Number(hidden.value) : 0;
  if ((!value || value <= 0) && custom && custom.value) value = Number(custom.value);
  return value > 0 ? value : 0;
}

function updateSummary() {
  const amount = currentAmount();
  donationState.amount = amount;

  const amountEl = document.getElementById('summary-amount');
  if (amountEl) amountEl.textContent = amount > 0 ? donationFormatMoney(amount, donationState.currency) : 'Choose an amount';

  const causeEl = document.getElementById('summary-cause');
  if (causeEl) causeEl.textContent = (WRC_DONATION.causes[donationState.cause] || {}).label || 'Where Most Needed';

  const projectEl = document.getElementById('summary-project');
  if (projectEl) projectEl.textContent = selectedProjectTitle();

  const paymentEl = document.getElementById('summary-payment');
  if (paymentEl) {
    const labels = { bank: 'Bank Transfer', easy: 'Mobile Wallet', payoneer: 'Payoneer', taptap: 'TapTap Send' };
    paymentEl.textContent = labels[donationState.payment] || 'Bank Transfer';
  }

  const conversionRow = document.getElementById('summary-conversion-row');
  const conversionEl = document.getElementById('summary-conversion');
  const conversionHint = document.getElementById('conversion-hint');
  if (donationState.currency === 'USD') {
    if (amount > 0) {
      const pkr = Math.round(amount * (WRC_DONATION.usdRate || 280));
      if (conversionEl) conversionEl.textContent = 'PKR ' + pkr.toLocaleString('en-US');
      if (conversionRow) conversionRow.classList.remove('hidden');
      if (conversionHint) {
        conversionHint.textContent = 'Conversion uses the WRC reference rate of PKR ' + (WRC_DONATION.usdRate || 280) + ' per $1 for receipting.';
        conversionHint.classList.remove('hidden');
      }
    } else {
      if (conversionRow) conversionRow.classList.add('hidden');
      if (conversionHint) conversionHint.classList.add('hidden');
    }
  } else {
    if (conversionRow) conversionRow.classList.add('hidden');
    if (conversionHint) conversionHint.classList.add('hidden');
  }
}

function selectPayment(key) {
  donationState.payment = key;
  document.querySelectorAll('.payment-panel').forEach(function (panel) {
    panel.classList.add('hidden');
  });
  const panel = document.getElementById('payment-panel-' + key);
  if (panel) panel.classList.remove('hidden');

  const input = document.getElementById('payment-proof');
  const requiredMark = document.getElementById('proof-required');
  const requiresProof = ['easy', 'payoneer', 'taptap'].indexOf(key) !== -1;
  if (input) {
    if (requiresProof) {
      input.setAttribute('required', 'required');
    } else {
      input.removeAttribute('required');
    }
  }
  if (requiredMark) requiredMark.classList.toggle('hidden', !requiresProof);

  document.querySelectorAll('.payment-tab').forEach(function (tab) {
    const radio = tab.querySelector('input[name="payment"]');
    const active = radio && radio.value === key;
    tab.classList.toggle('border-brandRed', active);
    tab.classList.toggle('bg-brandRed/5', active);
  });

  updateSummary();
}

function previewProof(input) {
  const file = input.files && input.files[0];
  if (!file) return;
  const wrapper = document.getElementById('proof-preview');
  const img = document.getElementById('proof-preview-img');
  const name = document.getElementById('proof-preview-name');
  const dropText = document.getElementById('proof-filename');

  if (name) name.textContent = file.name;
  if (dropText) dropText.textContent = 'Screenshot attached';
  if (wrapper) {
    wrapper.classList.remove('hidden');
    wrapper.classList.add('flex');
  }
  if (img) {
    if (file.type.indexOf('image/') === 0) {
      const reader = new FileReader();
      reader.onload = function (event) { img.src = event.target.result; img.classList.remove('hidden'); };
      reader.readAsDataURL(file);
    } else {
      img.removeAttribute('src');
      img.classList.add('hidden');
    }
  }
}

function clearProof() {
  const input = document.getElementById('payment-proof');
  if (input) input.value = '';
  const wrapper = document.getElementById('proof-preview');
  if (wrapper) {
    wrapper.classList.add('hidden');
    wrapper.classList.remove('flex');
  }
  const dropText = document.getElementById('proof-filename');
  if (dropText) dropText.textContent = 'Required for mobile wallet, Payoneer and TapTap Send';
}

function copyText(button) {
  const text = button.getAttribute('data-copy') || '';
  const done = function () {
    const original = button.innerHTML;
    button.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
    setTimeout(function () { button.innerHTML = original; }, 1800);
  };
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
  } else {
    fallbackCopy(text, done);
  }
}

function fallbackCopy(text, done) {
  const area = document.createElement('textarea');
  area.value = text;
  document.body.appendChild(area);
  area.select();
  try { document.execCommand('copy'); done(); } catch (e) {}
  document.body.removeChild(area);
}

function handleDonationSubmit(form) {
  const amount = currentAmount();
  if (!amount || amount <= 0) {
    WRCModal.error('Amount Required', 'Please choose or enter a donation amount before you continue.');
    return;
  }

  const consent = form.querySelector('input[name="donation_consent"]');
  if (consent && !consent.checked) {
    WRCModal.error('Consent Required', 'Please confirm the donation details before you continue.');
    return;
  }

  const btn = document.getElementById('donation-submit');
  const originalText = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...'; }

  const formData = new FormData(form);
  formData.set('form_type', 'donation');
  formData.set('donation_amount', String(amount));
  formData.set('currency', donationState.currency);
  formData.set('cause', donationState.cause);

  fetch(wrcSiteBase() + 'includes/form-submit.php', { method: 'POST', body: formData })
    .then(function (response) { return response.json(); })
    .then(function (data) {
      if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
      if (!data.success) {
        WRCModal.error(data.title || 'Donation Failed', data.message || 'Something went wrong. Please try again.');
        return;
      }

      const success = document.getElementById('donate-success');
      const successText = document.getElementById('donate-success-text');
      if (successText) {
        const donation = data.donation || {};
        const summary = donation.amount
          ? ' We have recorded ' + (donation.currency === 'USD' ? '$' : 'PKR ') + donation.amount + ' for ' + (donation.cause || 'your chosen cause') + '.'
          : '';
        successText.textContent = (data.message || 'Your donation has been recorded.') + summary;
      }
      if (success) {
        success.classList.remove('hidden');
        success.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      WRCModal.success(data.title || 'Thank You!', data.message || 'Your donation has been recorded.');
    })
    .catch(function () {
      if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
      WRCModal.error('Network Error', 'We could not reach the server. Please check your connection and try again.');
    });
}

function resetDonationForm() {
  const form = document.getElementById('donation-form');
  if (form) form.reset();
  clearProof();
  clearAmountBtns();
  const success = document.getElementById('donate-success');
  if (success) success.classList.add('hidden');
  selectCause('general', { scroll: false });
  setCurrency('PKR');
  selectPayment('bank');
  const section = document.getElementById('donate');
  if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

document.addEventListener('DOMContentLoaded', function () {
  if (!document.getElementById('donation-form')) return;
  selectCause('general', { scroll: false });
  setCurrency('PKR');
  selectPayment('bank');
  const projectSelect = document.getElementById('donation-project');
  if (projectSelect) projectSelect.addEventListener('change', updateSummary);
});
function handlePartnerSubmit(form) {
  var btn = form.querySelector('button[type="submit"]');
  var originalText = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Sending...'; }

  var formData = new FormData(form);
  formData.append('form_type', 'partner');
  formData.append('name', form.querySelector('[name="collab_name"]').value);
  formData.append('email', form.querySelector('[name="collab_email"]').value);
  formData.append('organization', form.querySelector('[name="collab_org"]').value);
  formData.append('role', form.querySelector('[name="collab_designation"]')?.value || '');
  formData.append('interest', form.querySelector('[name="collab_interest"]')?.value || '');
  formData.append('message', form.querySelector('[name="collab_message"]')?.value || '');
  fetch(wrcSiteBase() + 'includes/form-submit.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
      if (data.success) {
        form.reset();
        WRCModal.success(data.title, data.message);
      } else {
        WRCModal.error('Inquiry Failed', data.message || 'Something went wrong. Please try again.');
      }
    }).catch(function() {
      if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
      WRCModal.error('Network Error', 'We could not reach the server. Please check your connection and try again.');
    });
}
</script>
