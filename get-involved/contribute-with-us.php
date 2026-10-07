<?php
/**
 * WRC Website - Contribute With Us (Dynamic)
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();
$pageTitle = 'Contribute With Us | WRC';

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

// Fetch bank accounts
$bankAccounts = getRows('bank_accounts', 'is_active = 1 ORDER BY sort_order ASC');

include __DIR__ . '/../includes/header.php';
?>

<!-- HERO -->
<section class="relative bg-slate-900 py-16 md:py-20 overflow-hidden min-h-[480px] md:min-h-[540px] lg:min-h-[580px] flex items-center">
  <div class="absolute inset-0 opacity-50 bg-cover bg-center" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/contribute-hero.jpg' ?>');"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-slate-950/70 to-slate-900/50"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-brandRed/20 text-red-300 border border-brandRed/30 mb-4">
      <i class="fa-solid fa-handshake"></i> <?= sanitize($heroData['badge'] ?? 'Make a Difference') ?>
    </span>
    <h1 class="text-4xl sm:text-5xl font-extrabold text-white mb-4 font-heading">
      <?= sanitize($heroData['title'] ?? 'Contribute With Us') ?>
    </h1>
    <p class="text-slate-300 text-base max-w-2xl mx-auto">
      <?= sanitize($heroData['subtitle'] ?? 'Every donation brings clean water, food, education, and hope to families in need. Join us in rebuilding lives across Pakistan.') ?>
    </p>
  </div>
</section>

<!-- DONATION IMPACT -->
<section class="py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2">
        <?= sanitize($sections['contribute_impact']['section_subtitle'] ?? 'Your Impact') ?>
      </p>
      <h2 class="text-3xl font-extrabold text-slate-900">
        <?= sanitize($sections['contribute_impact']['section_title'] ?? 'What Your Donation Achieves') ?>
      </h2>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $impactItems = $impactData['items'] ?? [];
      $defaultImpact = [
        ['icon' => 'fa-solid fa-droplet', 'color' => 'blue', 'amount' => 'PKR 5,000', 'description' => 'Provides clean water to one family for a full month'],
        ['icon' => 'fa-solid fa-book', 'color' => 'green', 'amount' => 'PKR 10,000', 'description' => 'Supplies a complete school kit for 5 children'],
        ['icon' => 'fa-solid fa-bowl-food', 'color' => 'amber', 'amount' => 'PKR 25,000', 'description' => 'Emergency food ration pack for 10 families'],
        ['icon' => 'fa-solid fa-solar-panel', 'color' => 'purple', 'amount' => 'PKR 50,000', 'description' => 'Installs a solar-powered water filtration unit'],
      ];
      $items = !empty($impactItems) ? $impactItems : $defaultImpact;
      foreach ($items as $item):
        $color = $item['color'] ?? 'blue';
        $colorMap = [
          'blue'   => ['bg' => 'bg-blue-50',   'text' => 'text-blue-500'],
          'green'  => ['bg' => 'bg-green-50',  'text' => 'text-green-500'],
          'amber'  => ['bg' => 'bg-amber-50',  'text' => 'text-amber-500'],
          'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-500'],
        ];
        $c = $colorMap[$color] ?? $colorMap['blue'];
      ?>
      <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 text-center hover:shadow-lg transition-all group">
        <div class="w-14 h-14 rounded-full <?= $c['bg'] ?> flex items-center justify-center mx-auto mb-4 group-hover:bg-brandRed/10 transition-all">
          <i class="<?= sanitize($item['icon'] ?? 'fa-solid fa-heart') ?> <?= $c['text'] ?> text-xl group-hover:text-brandRed transition-all"></i>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 mb-1"><?= sanitize($item['amount'] ?? '') ?></div>
        <p class="text-sm text-slate-600"><?= sanitize($item['description'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- DONATION FORM -->
<section class="py-16 bg-slate-50">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10">
      <p class="text-brandRed text-xs uppercase tracking-[0.35em] font-semibold mb-2">Donate Now</p>
      <h2 class="text-3xl font-extrabold text-slate-900">Make a Donation</h2>
    </div>
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
      <form id="donation-form" onsubmit="event.preventDefault(); handleDonationSubmit(this);">

        <!-- Amount -->
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Donation Amount (PKR) <span class="text-brandRed">*</span></label>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-4">
          <?php if (!empty($tiers)): ?>
            <?php foreach ($tiers as $tier): ?>
              <button type="button" onclick="setAmount(<?= (int)$tier['amount'] ?>)" class="amount-btn py-3 rounded-xl border-2 border-slate-200 font-bold text-sm text-slate-700 hover:border-brandRed hover:text-brandRed transition-all">
                <?= number_format((int)$tier['amount']) ?>
              </button>
            <?php endforeach; ?>
          <?php else: ?>
            <?php foreach ([1000, 2500, 5000, 10000, 25000, 50000] as $amt): ?>
              <button type="button" onclick="setAmount(<?= $amt ?>)" class="amount-btn py-3 rounded-xl border-2 border-slate-200 font-bold text-sm text-slate-700 hover:border-brandRed hover:text-brandRed transition-all">
                <?= number_format($amt) ?>
              </button>
            <?php endforeach; ?>
          <?php endif; ?>
          <input type="number" id="custom-amount" placeholder="Custom" class="py-3 rounded-xl border-2 border-slate-200 text-sm text-center font-bold focus:border-brandRed focus:ring-0 outline-none transition-all" oninput="clearAmountBtns()">
        </div>
        <input type="hidden" id="donation-amount" name="donation_amount" required>

        <!-- Frequency -->
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 mt-6">Donation Frequency <span class="text-brandRed">*</span></label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
          <label class="frequency-option flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-brandRed/50 transition-all has-[:checked]:border-brandRed has-[:checked]:bg-brandRed/5">
            <input type="radio" name="frequency" value="one-time" checked class="w-4 h-4 text-brandRed border-slate-300 focus:ring-brandRed">
            <span class="text-sm font-semibold text-slate-700">One-Time</span>
          </label>
          <label class="frequency-option flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-brandRed/50 transition-all has-[:checked]:border-brandRed has-[:checked]:bg-brandRed/5">
            <input type="radio" name="frequency" value="monthly" class="w-4 h-4 text-brandRed border-slate-300 focus:ring-brandRed">
            <span class="text-sm font-semibold text-slate-700">Monthly</span>
          </label>
          <label class="frequency-option flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-brandRed/50 transition-all has-[:checked]:border-brandRed has-[:checked]:bg-brandRed/5">
            <input type="radio" name="frequency" value="quarterly" class="w-4 h-4 text-brandRed border-slate-300 focus:ring-brandRed">
            <span class="text-sm font-semibold text-slate-700">Quarterly</span>
          </label>
          <label class="frequency-option flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-brandRed/50 transition-all has-[:checked]:border-brandRed has-[:checked]:bg-brandRed/5">
            <input type="radio" name="frequency" value="annually" class="w-4 h-4 text-brandRed border-slate-300 focus:ring-brandRed">
            <span class="text-sm font-semibold text-slate-700">Annually</span>
          </label>
        </div>

        <!-- Donor Info -->
        <div class="grid sm:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name <span class="text-brandRed">*</span></label>
            <input type="text" name="full_name" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed outline-none transition-all" placeholder="Your name">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email <span class="text-brandRed">*</span></label>
            <input type="email" name="email" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed outline-none transition-all" placeholder="you@email.com">
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone</label>
            <input type="tel" name="phone" class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brandRed outline-none transition-all" placeholder="Optional">
          </div>
        </div>

        <!-- Payment Method -->
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Payment Method <span class="text-brandRed">*</span></label>
        <div class="grid sm:grid-cols-2 gap-3 mb-6">
          <label class="payment-option flex items-center gap-3 p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-brandRed/50 transition-all has-[:checked]:border-brandRed has-[:checked]:bg-brandRed/5">
            <input type="radio" name="payment" value="bank" checked class="w-4 h-4 text-brandRed border-slate-300 focus:ring-brandRed">
            <div><i class="fa-solid fa-building-columns text-brandRed mr-1"></i><span class="text-sm font-semibold text-slate-700">Bank Transfer</span></div>
          </label>
          <label class="payment-option flex items-center gap-3 p-4 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-brandRed/50 transition-all has-[:checked]:border-brandRed has-[:checked]:bg-brandRed/5">
            <input type="radio" name="payment" value="easy" class="w-4 h-4 text-brandRed border-slate-300 focus:ring-brandRed">
            <div><i class="fa-solid fa-mobile-screen text-brandRed mr-1"></i><span class="text-sm font-semibold text-slate-700">EasyPaisa/JazzCash</span></div>
          </label>
        </div>

        <!-- Payment Details: Dynamic from bank_accounts -->
        <?php
        $bankAccts = array_filter($bankAccounts, fn($a) => ($a['account_type'] ?? '') === 'bank');
        $mobileAccts = array_filter($bankAccounts, fn($a) => ($a['account_type'] ?? '') === 'mobile');
        $bankAcct = !empty($bankAccts) ? reset($bankAccts) : null;
        $mobileAcct = !empty($mobileAccts) ? reset($mobileAccts) : null;
        ?>

        <!-- Bank Transfer Details -->
        <div id="bank-details" class="payment-details bg-slate-50 rounded-xl p-5 border border-slate-200 mb-6">
          <h4 class="font-bold text-slate-900 text-sm mb-3"><i class="fa-solid fa-building-columns text-brandRed mr-2"></i>Bank Transfer Details</h4>
          <?php if ($bankAcct): ?>
            <div class="grid sm:grid-cols-2 gap-3 text-sm">
              <div class="flex justify-between bg-white px-3 py-2 rounded-lg border border-slate-100">
                <span class="text-slate-500">Bank:</span>
                <span class="font-semibold text-slate-900"><?= sanitize($bankAcct['bank_name'] ?? '') ?></span>
              </div>
              <div class="flex justify-between bg-white px-3 py-2 rounded-lg border border-slate-100">
                <span class="text-slate-500">Account Title:</span>
                <span class="font-semibold text-slate-900"><?= sanitize($bankAcct['account_title'] ?? '') ?></span>
              </div>
              <div class="flex justify-between bg-white px-3 py-2 rounded-lg border border-slate-100 col-span-full">
                <span class="text-slate-500">Account #:</span>
                <span class="font-semibold text-slate-900 font-mono"><?= sanitize($bankAcct['account_number'] ?? '') ?></span>
              </div>
              <div class="flex justify-between bg-white px-3 py-2 rounded-lg border border-slate-100">
                <span class="text-slate-500">IBAN:</span>
                <span class="font-semibold text-slate-900 font-mono text-xs"><?= sanitize($bankAcct['iban'] ?? '') ?></span>
              </div>
              <?php if (!empty($bankAcct['swift_code'])): ?>
              <div class="flex justify-between bg-white px-3 py-2 rounded-lg border border-slate-100">
                <span class="text-slate-500">Swift:</span>
                <span class="font-semibold text-slate-900 font-mono"><?= sanitize($bankAcct['swift_code'] ?? '') ?></span>
              </div>
              <?php endif; ?>
            </div>
            <?php if (!empty($bankAcct['iban'])): ?>
            <button type="button" onclick="copyToClipboard('<?= sanitize($bankAcct['iban']) ?>')" class="mt-3 text-xs text-brandRed font-semibold hover:underline">
              <i class="fa-solid fa-copy mr-1"></i>Copy IBAN
            </button>
            <?php endif; ?>
          <?php else: ?>
            <p class="text-sm text-slate-500">Bank details will be provided upon request.</p>
          <?php endif; ?>
        </div>

        <!-- EasyPaisa / JazzCash Details -->
        <div id="easy-details" class="payment-details hidden bg-slate-50 rounded-xl p-5 border border-slate-200 mb-6">
          <h4 class="font-bold text-slate-900 text-sm mb-3"><i class="fa-solid fa-mobile-screen text-brandRed mr-2"></i>EasyPaisa / JazzCash</h4>
          <?php if ($mobileAcct): ?>
            <div class="bg-white px-4 py-3 rounded-lg border border-slate-100 text-sm flex justify-between">
              <span class="text-slate-500">Mobile #:</span>
              <span class="font-semibold text-slate-900 font-mono"><?= sanitize($mobileAcct['account_number'] ?? '') ?></span>
            </div>
            <p class="text-xs text-slate-500 mt-2">Send your donation to the above number. Account Title: <?= sanitize($mobileAcct['account_title'] ?? 'WRC Foundation') ?>.</p>
            <?php if (!empty($mobileAcct['iban'])): ?>
            <button type="button" onclick="copyToClipboard('<?= sanitize($mobileAcct['iban']) ?>')" class="mt-3 text-xs text-brandRed font-semibold hover:underline">
              <i class="fa-solid fa-copy mr-1"></i>Copy Account Number
            </button>
            <?php endif; ?>
          <?php else: ?>
            <div class="bg-white px-4 py-3 rounded-lg border border-slate-100 text-sm flex justify-between">
              <span class="text-slate-500">Mobile #:</span>
              <span class="font-semibold text-slate-900 font-mono">0300-1234567</span>
            </div>
            <p class="text-xs text-slate-500 mt-2">Send your donation to the above number. Account Title: WRC Foundation.</p>
          <?php endif; ?>
        </div>

        <button type="submit" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold py-4 rounded-xl transition-colors shadow-lg shadow-brandRed/20 text-sm uppercase tracking-wider">
          <i class="fa-solid fa-hand-holding-heart mr-2"></i>Complete Donation
        </button>
        <div id="donate-success" class="hidden mt-4 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl text-sm font-medium">
          <i class="fa-solid fa-check-circle mr-2"></i>Thank you for your generous donation! A confirmation email will be sent to you shortly.
        </div>
      </form>
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
          $defaultStats = [
            ['value' => '45+', 'label' => 'Projects Completed'],
            ['value' => '8+', 'label' => 'Active Partners'],
            ['value' => '150K+', 'label' => 'Lives Impacted'],
            ['value' => '4', 'label' => 'Provinces Covered'],
          ];
          $statItems = $whyData['stats'] ?? $defaultStats;
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
          <label for="privacy" class="text-xs text-slate-500">I agree to the <a href="<?= $base ?>about/policies-compliance.html" class="text-brandRed hover:underline">Privacy Policy</a> and consent to being contacted regarding this collaboration inquiry.</label>
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
function setAmount(amt) {
  document.getElementById('donation-amount').value = amt;
  document.getElementById('custom-amount').value = '';
  document.querySelectorAll('.amount-btn').forEach(b => { b.classList.remove('border-brandRed','text-brandRed','bg-brandRed/5'); });
  event.target.classList.add('border-brandRed','text-brandRed','bg-brandRed/5');
}

function clearAmountBtns() {
  document.getElementById('donation-amount').value = document.getElementById('custom-amount').value;
  document.querySelectorAll('.amount-btn').forEach(b => { b.classList.remove('border-brandRed','text-brandRed','bg-brandRed/5'); });
}

document.querySelectorAll('input[name="payment"]').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.payment-details').forEach(d => d.classList.add('hidden'));
    document.getElementById(r.value + '-details').classList.remove('hidden');
  });
});

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Copied!';
    setTimeout(() => { btn.innerHTML = originalText; }, 2000);
  }).catch(() => {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    alert('Copied to clipboard!');
  });
}

function handleDonationSubmit(form) {
  var amount = document.getElementById('donation-amount').value;
  if (!amount) {
    WRCModal.error('Amount Required', 'Please select or enter a donation amount before you continue.');
    return;
  }
  var btn = form.querySelector('button[type="submit"]');
  var originalText = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...'; }

  var formData = new FormData(form);
  formData.append('form_type', 'donation');
  fetch(wrcSiteBase() + 'includes/form-submit.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
      if (data.success) {
        document.getElementById('donate-success').classList.remove('hidden');
        WRCModal.success(data.title, data.message, function() {
          window.scrollTo({top: document.getElementById('donate-success').offsetTop - 100, behavior: 'smooth'});
        });
      } else {
        WRCModal.error('Donation Failed', data.message || 'Something went wrong. Please try again.');
      }
    }).catch(function() {
      if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
      WRCModal.error('Network Error', 'We could not reach the server. Please check your connection and try again.');
    });
}

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
