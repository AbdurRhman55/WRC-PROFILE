<?php
/**
 * WRC Causes / Campaigns Page (Dynamic)
 * ALL content from database
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$base = siteBaseUrl();

// Fetch sections
$allSections = getRows('homepage_sections', '', '');
$causesSections = [];
foreach ($allSections as $s) {
    if (str_starts_with($s['section_key'], 'causes_')) {
        $causesSections[$s['section_key']] = $s;
    }
}

// Override with dedicated table data
$heroRow = getRow('causes_hero');
if ($heroRow) $causesSections['causes_hero'] = ['section_key' => 'causes_hero', 'section_title' => $heroRow['section_title'] ?? '', 'section_subtitle' => $heroRow['section_subtitle'] ?? '', 'content' => json_encode($heroRow)];

$impRow = getRow('causes_impact');
if ($impRow) {
    $statsRows = getRows('causes_impact_stats', ['impact_id' => $impRow['id']], 'sort_order ASC');
    $stats = array_map(function($s) { return ['icon' => $s['stat_icon'], 'label' => $s['stat_label'], 'value' => $s['stat_value']]; }, $statsRows);
    $causesSections['causes_impact'] = ['section_key' => 'causes_impact', 'section_title' => $impRow['section_title'], 'section_subtitle' => $impRow['section_subtitle'] ?? '', 'content' => json_encode(['stats' => $stats])];
}

$heroData = isset($causesSections['causes_hero']) ? json_decode($causesSections['causes_hero']['content'], true) : [];
$impactData = isset($causesSections['causes_impact']) ? json_decode($causesSections['causes_impact']['content'], true) : [];

// Fetch campaigns
$campaigns = getRows('campaigns', 'is_active = 1 ORDER BY sort_order ASC');

// Fetch bank accounts for donation modal
$bankAccounts = getRows('bank_accounts', 'is_active = 1 ORDER BY sort_order ASC');

$pageTitle = 'Contribute With Us | WRC Campaigns';
$metaDescription = 'Support WRC campaigns to bring clean water, food security, and education to communities in need.';

include dirname(__DIR__) . '/includes/header.php';
?>

<style>
  .animate-progress-fill { animation: progressFill 1.5s ease-out forwards; }
  @keyframes progressFill { from { width: 0%; } }
</style>

<main class="flex-grow">

  <!-- HERO SECTION -->
  <section class="relative w-full min-h-[520px] flex items-center overflow-hidden">
    <div class="absolute inset-0 z-0">
      <div class="absolute inset-0 opacity-40 mix-blend-luminosity bg-cover bg-center" style="background-image: url('<?= $base ?><?= $heroData['hero_image'] ?? 'assets/images/hero/projects-hero.jpg' ?>');"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/80 to-slate-900/40"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
      <div class="max-w-2xl">
        <div class="inline-flex items-center gap-2 bg-brandRed/20 border border-brandRed/40 text-brandRed text-xs font-bold uppercase tracking-wider px-4 py-2 rounded-full mb-6">
          <span class="w-2 h-2 bg-brandRed rounded-full animate-pulse"></span>
          <?= $causesSections['causes_hero']['section_subtitle'] ?? 'Live Campaigns' ?>
        </div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-6">
          <?= $causesSections['causes_hero']['section_title'] ?? '' ?>
          <span class="text-brandRed">Changes Lives</span>
        </h1>
        <p class="text-lg text-slate-300 leading-relaxed mb-8 max-w-xl">
          <?= sanitize($heroData['subtitle'] ?? '') ?>
        </p>
        <div class="flex flex-wrap gap-4">
          <a href="<?= $heroData['button1_link'] ?? '#campaigns' ?>" class="inline-flex items-center gap-2 bg-brandRed hover:bg-brandRed-hover text-white font-bold px-8 py-3.5 rounded-xl shadow-lg shadow-brandRed/25 transition-all">
            <i class="<?= $heroData['button1_icon'] ?? 'fa-solid fa-heart' ?> text-sm"></i> <?= sanitize($heroData['button1_text'] ?? 'Donate Now') ?>
          </a>
          <a href="<?= $heroData['button2_link'] ?? '#impact' ?>" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold px-8 py-3.5 rounded-xl transition-all">
            <i class="<?= $heroData['button2_icon'] ?? 'fa-solid fa-chart-line' ?> text-sm"></i> <?= sanitize($heroData['button2_text'] ?? 'See Our Impact') ?>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- CAMPAIGN CARDS SECTION -->
  <section id="campaigns" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-14">
        <span class="inline-block text-brandRed text-xs font-bold uppercase tracking-widest mb-3">Fundraising</span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-slate-900 mb-4">Active Campaigns</h2>
        <p class="text-slate-500 max-w-2xl mx-auto">Support our ongoing campaigns to bring clean water, food security, and education to communities in need.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($campaigns as $campaign):
          $raised = $campaign['raised_amount'];
          $target = $campaign['target_amount'];
          $percent = $target > 0 ? round(($raised / $target) * 100) : 0;
        ?>
        <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group">
          <div class="relative h-56 overflow-hidden">
            <img src="<?= $base ?><?= $campaign['image_path'] ?>" alt="<?= sanitize($campaign['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <span class="absolute top-4 left-4 bg-brandRed text-white text-[11px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wide shadow-lg">
              <i class="<?= $campaign['icon_class'] ?> mr-1"></i> <?= sanitize($campaign['category']) ?>
            </span>
            <span class="absolute top-4 right-4 bg-emerald-500 text-white text-[11px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wide shadow-lg">
              <?= ucfirst($campaign['status']) ?>
            </span>
          </div>
          <div class="p-6 flex-1 flex flex-col">
            <h3 class="text-xl font-bold text-slate-900 mb-2 font-display"><?= sanitize($campaign['title']) ?></h3>
            <p class="text-slate-500 text-sm mb-5 leading-relaxed"><?= sanitize($campaign['description']) ?></p>

            <div class="mb-4">
              <div class="flex justify-between text-xs font-bold text-slate-600 mb-2">
                <span class="text-brandRed">PKR <?= number_format($raised) ?> raised</span>
                <span class="text-slate-400">PKR <?= number_format($target) ?></span>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-brandRed to-red-500 h-3 rounded-full animate-progress-fill" style="width: <?= $percent ?>%"></div>
              </div>
              <div class="flex justify-between items-center mt-2">
                <span class="text-xs font-bold text-brandRed"><?= $percent ?>% Funded</span>
                <span class="text-[11px] text-slate-400"><i class="fa-solid fa-users mr-1"></i> <?= number_format($campaign['beneficiaries_count']) ?> <?= sanitize($campaign['beneficiary_type'] ?? 'beneficiaries') ?></span>
              </div>
            </div>

            <button onclick="openDonationModal('<?= addslashes($campaign['title']) ?>', <?= $target ?>)" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold py-3 rounded-xl text-sm transition-all flex items-center justify-center gap-2 mt-auto">
              <i class="fa-solid fa-hand-holding-heart"></i> Donate Now
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- IMPACT STATS SECTION -->
  <section id="impact" class="py-20 bg-gradient-to-br from-ink-900 via-slate-900 to-ink-800 text-white relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-brandRed/5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-brandRed/5 rounded-full translate-x-1/2 translate-y-1/2 blur-3xl"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-14">
        <span class="inline-block text-brandRed text-xs font-bold uppercase tracking-widest mb-3"><?= $causesSections['causes_impact']['section_subtitle'] ?? 'Our Reach' ?></span>
        <h2 class="text-3xl sm:text-4xl font-display font-bold text-white mb-4"><?= $causesSections['causes_impact']['section_title'] ?? '' ?></h2>
        <p class="text-slate-400 max-w-xl mx-auto"><?= sanitize($impactData['subtitle'] ?? '') ?></p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <?php foreach ($impactData['stats'] ?? [] as $stat): ?>
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 text-center hover:bg-white/10 transition-all">
          <div class="w-14 h-14 bg-brandRed/15 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="<?= $stat['icon'] ?> text-brandRed text-xl"></i>
          </div>
          <div class="text-3xl sm:text-4xl font-display font-bold text-white mb-1"><?= $stat['value'] ?></div>
          <div class="text-slate-400 text-sm"><?= $stat['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<!-- DONATION MODAL / DRAWER -->
<div id="donation-modal" class="fixed inset-0 z-[200] hidden">
  <div id="donation-overlay" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeDonationModal()"></div>
  <div id="donation-panel" class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 overflow-y-auto">
    <div class="min-h-full flex flex-col">

      <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-10">
        <div>
          <h3 class="text-lg font-bold text-slate-900 font-display">Quick Donation</h3>
          <p id="modal-campaign-name" class="text-sm text-slate-500"></p>
        </div>
        <button onclick="closeDonationModal()" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>

      <div class="flex-1 p-6 space-y-6">
        <div>
          <label class="text-sm font-bold text-slate-700 mb-3 block">Select Amount</label>
          <div class="grid grid-cols-3 gap-3 mb-3">
            <button onclick="selectAmount(5000)" class="donation-amount-btn border-2 border-slate-200 hover:border-brandRed rounded-xl py-3 text-sm font-bold text-slate-700 transition-all hover:text-brandRed" data-amount="5000">PKR 5,000</button>
            <button onclick="selectAmount(10000)" class="donation-amount-btn border-2 border-slate-200 hover:border-brandRed rounded-xl py-3 text-sm font-bold text-slate-700 transition-all hover:text-brandRed" data-amount="10000">PKR 10,000</button>
            <button onclick="selectAmount(25000)" class="donation-amount-btn border-2 border-slate-200 hover:border-brandRed rounded-xl py-3 text-sm font-bold text-slate-700 transition-all hover:text-brandRed" data-amount="25000">PKR 25,000</button>
            <button onclick="selectAmount(50000)" class="donation-amount-btn border-2 border-slate-200 hover:border-brandRed rounded-xl py-3 text-sm font-bold text-slate-700 transition-all hover:text-brandRed" data-amount="50000">PKR 50,000</button>
            <button onclick="selectAmount(100000)" class="donation-amount-btn border-2 border-slate-200 hover:border-brandRed rounded-xl py-3 text-sm font-bold text-slate-700 transition-all hover:text-brandRed" data-amount="100000">PKR 1,00,000</button>
            <button onclick="selectAmount(250000)" class="donation-amount-btn border-2 border-slate-200 hover:border-brandRed rounded-xl py-3 text-sm font-bold text-slate-700 transition-all hover:text-brandRed" data-amount="250000">PKR 2,50,000</button>
          </div>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">PKR</span>
            <input type="number" id="custom-amount" placeholder="Custom amount" class="w-full border-2 border-slate-200 focus:border-brandRed rounded-xl py-3 pl-14 pr-4 text-sm text-slate-700 outline-none transition-colors" oninput="clearAmountSelection()">
          </div>
        </div>

        <div>
          <label class="text-sm font-bold text-slate-700 mb-3 block">Payment Method</label>
          <div class="space-y-2">
            <label class="flex items-center gap-3 border-2 border-slate-200 hover:border-brandRed/40 rounded-xl p-4 cursor-pointer transition-all payment-method-option">
              <input type="radio" name="payment-method" value="bank" class="w-4 h-4 text-brandRed accent-brandRed" checked>
              <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-building-columns text-blue-600"></i>
              </div>
              <div class="flex-1">
                <span class="text-sm font-bold text-slate-700 block">Bank Transfer</span>
                <span class="text-[11px] text-slate-400">Direct bank deposit</span>
              </div>
              <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
            </label>
            <label class="flex items-center gap-3 border-2 border-slate-200 hover:border-brandRed/40 rounded-xl p-4 cursor-pointer transition-all payment-method-option">
              <input type="radio" name="payment-method" value="mobile" class="w-4 h-4 text-brandRed accent-brandRed">
              <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-mobile-screen text-green-600"></i>
              </div>
              <div class="flex-1">
                <span class="text-sm font-bold text-slate-700 block">EasyPaisa / JazzCash</span>
                <span class="text-[11px] text-slate-400">Mobile wallet transfer</span>
              </div>
              <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
            </label>
          </div>
        </div>

        <!-- Dynamic Bank Details -->
        <?php foreach ($bankAccounts as $acct): ?>
        <div id="details-<?= $acct['id'] ?>" class="bg-blue-50 border border-blue-200 rounded-xl p-5 space-y-2 <?= $acct['account_type'] !== 'bank' ? 'hidden' : '' ?>" data-type="<?= $acct['account_type'] ?>">
          <div class="flex items-center gap-2 mb-3">
            <i class="<?= $acct['icon_class'] ?> text-blue-600"></i>
            <span class="text-sm font-bold text-slate-800"><?= sanitize($acct['account_type_label']) ?></span>
          </div>
          <?php
          $details = json_decode($acct['details'], true) ?? [];
          foreach ($details as $key => $val):
          ?>
          <div class="flex justify-between text-sm"><span class="text-slate-500"><?= sanitize($key) ?></span><span class="font-bold text-slate-800"><?= sanitize($val) ?></span></div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <div>
          <label class="text-sm font-bold text-slate-700 mb-3 block">Your Information</label>
          <div class="space-y-3">
            <div>
              <input type="text" id="donor-name" placeholder="Full Name *" class="w-full border-2 border-slate-200 focus:border-brandRed rounded-xl py-3 px-4 text-sm text-slate-700 outline-none transition-colors">
              <span id="name-error" class="text-red-500 text-[11px] mt-1 hidden">Please enter your name</span>
            </div>
            <div>
              <input type="email" id="donor-email" placeholder="Email Address *" class="w-full border-2 border-slate-200 focus:border-brandRed rounded-xl py-3 px-4 text-sm text-slate-700 outline-none transition-colors">
              <span id="email-error" class="text-red-500 text-[11px] mt-1 hidden">Please enter a valid email</span>
            </div>
            <div>
              <input type="tel" id="donor-phone" placeholder="Phone Number" class="w-full border-2 border-slate-200 focus:border-brandRed rounded-xl py-3 px-4 text-sm text-slate-700 outline-none transition-colors">
            </div>
            <div>
              <textarea id="donor-message" rows="2" placeholder="Dedication message (optional)" class="w-full border-2 border-slate-200 focus:border-brandRed rounded-xl py-3 px-4 text-sm text-slate-700 outline-none transition-colors resize-none"></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="sticky bottom-0 bg-white border-t border-slate-200 px-6 py-4">
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm text-slate-500">Total Donation</span>
          <span id="modal-total-amount" class="text-xl font-bold text-brandRed font-display">PKR 0</span>
        </div>
        <button id="submit-donation-btn" onclick="submitDonation()" class="w-full bg-brandRed hover:bg-brandRed-hover text-white font-bold py-4 rounded-xl text-sm transition-all flex items-center justify-center gap-2 shadow-lg shadow-brandRed/20">
          <i class="fa-solid fa-lock text-xs"></i> Confirm Donation
        </button>
        <p class="text-center text-[11px] text-slate-400 mt-3"><i class="fa-solid fa-shield-halved mr-1"></i> Your donation is secure and tax-deductible</p>
      </div>

    </div>
  </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

<script>
  var selectedAmount = 0;
  var currentCampaign = '';

  function openDonationModal(campaignName, target) {
    currentCampaign = campaignName;
    selectedAmount = 0;
    document.getElementById('modal-campaign-name').textContent = campaignName;
    document.getElementById('custom-amount').value = '';
    document.getElementById('modal-total-amount').textContent = 'PKR 0';
    document.getElementById('donor-name').value = '';
    document.getElementById('donor-email').value = '';
    document.getElementById('donor-phone').value = '';
    document.getElementById('donor-message').value = '';
    document.querySelectorAll('.donation-amount-btn').forEach(function(btn) {
      btn.classList.remove('border-brandRed', 'bg-brandRed/5', 'text-brandRed');
      btn.classList.add('border-slate-200');
    });
    document.querySelectorAll('[id$="-error"]').forEach(function(el) { el.classList.add('hidden'); });
    var modal = document.getElementById('donation-modal');
    var panel = document.getElementById('donation-panel');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(function() {
      panel.classList.remove('translate-x-full');
      panel.classList.add('translate-x-0');
    });
  }

  function closeDonationModal() {
    var panel = document.getElementById('donation-panel');
    panel.classList.remove('translate-x-0');
    panel.classList.add('translate-x-full');
    document.body.style.overflow = '';
    setTimeout(function() {
      document.getElementById('donation-modal').classList.add('hidden');
    }, 300);
  }

  function selectAmount(amount) {
    selectedAmount = amount;
    document.getElementById('custom-amount').value = '';
    document.getElementById('modal-total-amount').textContent = 'PKR ' + amount.toLocaleString();
    document.querySelectorAll('.donation-amount-btn').forEach(function(btn) {
      if (parseInt(btn.dataset.amount) === amount) {
        btn.classList.add('border-brandRed', 'bg-brandRed/5', 'text-brandRed');
        btn.classList.remove('border-slate-200');
      } else {
        btn.classList.remove('border-brandRed', 'bg-brandRed/5', 'text-brandRed');
        btn.classList.add('border-slate-200');
      }
    });
  }

  function clearAmountSelection() {
    selectedAmount = 0;
    document.querySelectorAll('.donation-amount-btn').forEach(function(btn) {
      btn.classList.remove('border-brandRed', 'bg-brandRed/5', 'text-brandRed');
      btn.classList.add('border-slate-200');
    });
    var customVal = parseInt(document.getElementById('custom-amount').value) || 0;
    document.getElementById('modal-total-amount').textContent = 'PKR ' + customVal.toLocaleString();
  }

  document.querySelectorAll('input[name="payment-method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      document.querySelectorAll('[id^="details-"]').forEach(function(el) { el.classList.add('hidden'); });
      var selected = this.value;
      document.querySelectorAll('[data-type]').forEach(function(el) {
        if (el.dataset.type === selected) el.classList.remove('hidden');
      });
    });
  });

  function submitDonation() {
    var name = document.getElementById('donor-name').value.trim();
    var email = document.getElementById('donor-email').value.trim();
    var customAmount = parseInt(document.getElementById('custom-amount').value) || 0;
    var total = selectedAmount || customAmount;
    var valid = true;
    document.querySelectorAll('[id$="-error"]').forEach(function(el) { el.classList.add('hidden'); });
    if (!name) { document.getElementById('name-error').classList.remove('hidden'); valid = false; }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { document.getElementById('email-error').classList.remove('hidden'); valid = false; }
    if (total <= 0) { alert('Please select or enter a donation amount.'); valid = false; }
    if (valid) {
      alert('Thank you, ' + name + '!\n\nDonation: PKR ' + total.toLocaleString() + '\nCampaign: ' + currentCampaign + '\n\nYou will receive a confirmation email with payment instructions.');
      closeDonationModal();
    }
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDonationModal();
  });
</script>
