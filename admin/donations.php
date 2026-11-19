<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Donations';
$base = siteBaseUrl();

global $pdo;

$statusOptions = [
    'new'       => 'New',
    'verified'  => 'Verified',
    'completed' => 'Completed',
    'rejected'  => 'Rejected',
    'refunded'  => 'Refunded',
];

$statusStyles = [
    'new'       => 'bg-amber-50 text-amber-700 border-amber-200',
    'verified'  => 'bg-sky-50 text-sky-700 border-sky-200',
    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
    'refunded'  => 'bg-slate-100 text-slate-600 border-slate-200',
];

$paymentIcons = [
    'bank'     => 'fa-building-columns',
    'easy'     => 'fa-mobile-screen-button',
    'payoneer' => 'fa-globe',
    'taptap'   => 'fa-paper-plane',
];

// ------------------------------------------------------------------ actions --
if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('donations.php');
    }

    if (isset($_POST['save_donation'])) {
        $id = (int) ($_POST['donation_id'] ?? 0);
        $newStatus = (string) ($_POST['status'] ?? 'new');
        $adminNote = trim((string) ($_POST['admin_note'] ?? ''));

        if ($id <= 0 || !isset($statusOptions[$newStatus])) {
            setFlash('error', 'Could not update this donation.');
            redirect('donations.php');
        }

        try {
            $stmt = $pdo->prepare('UPDATE donations SET status = ?, admin_note = ? WHERE id = ?');
            $stmt->execute([$newStatus, $adminNote !== '' ? $adminNote : null, $id]);
            setFlash('success', 'Donation #' . $id . ' updated to ' . $statusOptions[$newStatus] . '.');
        } catch (Throwable $e) {
            setFlash('error', 'Could not update the donation: ' . $e->getMessage());
        }
        redirect('donations.php');
    }
}

// ------------------------------------------------------------------ filters --
$search   = trim((string) ($_GET['q'] ?? ''));
$filterStatus   = (string) ($_GET['status'] ?? '');
$filterCurrency = (string) ($_GET['currency'] ?? '');
$filterPayment  = (string) ($_GET['payment'] ?? '');
$filterCause    = (string) ($_GET['cause'] ?? '');
$filterFrom     = (string) ($_GET['from'] ?? '');
$filterTo       = (string) ($_GET['to'] ?? '');

$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR payment_reference LIKE ? OR project_title LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if (isset($statusOptions[$filterStatus])) {
    $conditions[] = 'status = ?';
    $params[] = $filterStatus;
}
if (in_array($filterCurrency, ['PKR', 'USD'], true)) {
    $conditions[] = 'currency = ?';
    $params[] = $filterCurrency;
}
if (array_key_exists($filterPayment, $paymentIcons)) {
    $conditions[] = 'payment_method = ?';
    $params[] = $filterPayment;
}
if (array_key_exists($filterCause, wrcDonationCauses())) {
    $conditions[] = 'sector_key = ?';
    $params[] = $filterCause;
}
if ($filterFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterFrom)) {
    $conditions[] = 'created_at >= ?';
    $params[] = $filterFrom . ' 00:00:00';
}
if ($filterTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterTo)) {
    $conditions[] = 'created_at <= ?';
    $params[] = $filterTo . ' 23:59:59';
}

$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$filterQuery = $_GET;
unset($filterQuery['export'], $filterQuery['page']);

// -------------------------------------------------------------- csv export --
if (($_GET['export'] ?? '') === 'csv') {
    try {
        $stmt = $pdo->prepare('SELECT * FROM donations' . $where . ' ORDER BY created_at DESC');
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $rows = [];
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="wrc-donations-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Date', 'Donor', 'Email', 'Phone', 'Amount', 'Currency', 'Amount (PKR)', 'Frequency', 'Cause', 'Project', 'Payment Method', 'Reference', 'Status', 'Screenshot', 'Message', 'Admin Note']);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['id'] ?? '',
            $row['created_at'] ?? '',
            $row['full_name'] ?? '',
            $row['email'] ?? '',
            $row['phone'] ?? '',
            $row['amount'] ?? '',
            $row['currency'] ?? '',
            $row['amount_pkr'] ?? '',
            $row['frequency'] ?? '',
            $row['sector_name'] ?? '',
            $row['project_title'] ?? '',
            wrcPaymentMethodLabel((string) ($row['payment_method'] ?? '')),
            $row['payment_reference'] ?? '',
            $statusOptions[$row['status'] ?? 'new'] ?? ($row['status'] ?? ''),
            $row['screenshot_path'] ?? '',
            str_replace(["\r", "\n"], ' ', (string) ($row['message'] ?? '')),
            str_replace(["\r", "\n"], ' ', (string) ($row['admin_note'] ?? '')),
        ]);
    }
    fclose($out);
    exit;
}

// -------------------------------------------------------------------- stats --
$stats = [
    'count'      => 0,
    'raised_pkr' => 0.0,
    'month_pkr'  => 0.0,
    'pending'    => 0,
    'usd_amount' => 0.0,
];
$tableReady = true;

try {
    $row = $pdo->query(
        "SELECT
            COUNT(*) AS total_count,
            COALESCE(SUM(CASE WHEN status <> 'rejected' THEN amount_pkr END), 0) AS raised_pkr,
            COALESCE(SUM(CASE WHEN status <> 'rejected' AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN amount_pkr END), 0) AS month_pkr,
            COALESCE(SUM(CASE WHEN status = 'new' THEN 1 END), 0) AS pending,
            COALESCE(SUM(CASE WHEN currency = 'USD' AND status <> 'rejected' THEN amount END), 0) AS usd_amount
         FROM donations"
    )->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $stats = [
            'count'      => (int) $row['total_count'],
            'raised_pkr' => (float) $row['raised_pkr'],
            'month_pkr'  => (float) $row['month_pkr'],
            'pending'    => (int) $row['pending'],
            'usd_amount' => (float) $row['usd_amount'],
        ];
    }
} catch (Throwable $e) {
    $tableReady = false;
}

// ---------------------------------------------------------------- pagination --
$perPage = 20;
$page = max(1, (int) ($_GET['page'] ?? 1));
$donations = [];
$totalRows = 0;

if ($tableReady) {
    try {
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM donations' . $where);
        $countStmt->execute($params);
        $totalRows = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare('SELECT * FROM donations' . $where . ' ORDER BY created_at DESC, id DESC LIMIT ' . $perPage . ' OFFSET ' . $offset);
        $stmt->execute($params);
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $tableReady = false;
    }
}
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$activeFilters = array_filter([
    'q' => $search,
    'status' => $filterStatus,
    'currency' => $filterCurrency,
    'payment' => $filterPayment,
    'cause' => $filterCause,
    'from' => $filterFrom,
    'to' => $filterTo,
], static fn($value) => $value !== '');

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h1 class="font-heading text-2xl font-bold text-slate-900">Donations</h1>
    <p class="mt-1 text-sm text-slate-500">Every pledge from the public donation form, with payment proof and receipting status.</p>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <a href="donations.php?<?= htmlspecialchars(http_build_query(array_merge($filterQuery, ['export' => 'csv']))) ?>"
       class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-brandRed hover:text-brandRed">
      <i class="fa-solid fa-file-csv"></i> Export CSV
    </a>
    <?php if ($activeFilters): ?>
    <a href="donations.php" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-white">
      <i class="fa-solid fa-xmark"></i> Clear filters
    </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!$tableReady): ?>
<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
  <p class="font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Donation ledger not ready</p>
  <p class="mt-1">The <code class="rounded bg-white px-1">donations</code> table is missing. Run <code class="rounded bg-white px-1">php database/seed_donations.php</code> to create it, then reload this page.</p>
</div>
<?php endif; ?>

<!-- KPIs -->
<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Raised</p>
        <p class="mt-2 font-heading text-2xl font-extrabold text-slate-900">PKR <?= number_format($stats['raised_pkr']) ?></p>
      </div>
      <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-hand-holding-dollar"></i></span>
    </div>
    <p class="mt-2 text-[11px] text-slate-400">All recorded donations except rejections</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">This Month</p>
        <p class="mt-2 font-heading text-2xl font-extrabold text-slate-900">PKR <?= number_format($stats['month_pkr']) ?></p>
      </div>
      <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fa-solid fa-calendar-days"></i></span>
    </div>
    <p class="mt-2 text-[11px] text-slate-400"><?= date('F Y') ?></p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Donations</p>
        <p class="mt-2 font-heading text-2xl font-extrabold text-slate-900"><?= number_format($stats['count']) ?></p>
      </div>
      <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fa-solid fa-receipt"></i></span>
    </div>
    <p class="mt-2 text-[11px] text-slate-400">Lifetime pledges recorded</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Awaiting Review</p>
        <p class="mt-2 font-heading text-2xl font-extrabold text-slate-900"><?= number_format($stats['pending']) ?></p>
      </div>
      <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-clock"></i></span>
    </div>
    <p class="mt-2 text-[11px] text-slate-400">New pledges to verify</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">International</p>
        <p class="mt-2 font-heading text-2xl font-extrabold text-slate-900">$<?= number_format($stats['usd_amount'], 2) ?></p>
      </div>
      <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brandRed/10 text-brandRed"><i class="fa-solid fa-globe"></i></span>
    </div>
    <p class="mt-2 text-[11px] text-slate-400">USD donations received</p>
  </div>
</div>

<!-- Filters -->
<form method="get" action="donations.php" class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
    <div class="relative md:col-span-2">
      <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
      <input type="text" name="q" value="<?= sanitize($search) ?>" placeholder="Search donor, email, reference or project"
             class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-brandRed focus:bg-white focus:ring-2 focus:ring-brandRed/20">
    </div>
    <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-brandRed focus:bg-white">
      <option value="">All statuses</option>
      <?php foreach ($statusOptions as $key => $label): ?>
      <option value="<?= $key ?>" <?= $filterStatus === $key ? 'selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
    <select name="payment" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-brandRed focus:bg-white">
      <option value="">All payment methods</option>
      <?php foreach ($paymentIcons as $key => $icon): ?>
      <option value="<?= $key ?>" <?= $filterPayment === $key ? 'selected' : '' ?>><?= sanitize(wrcPaymentMethodLabel($key)) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="cause" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-brandRed focus:bg-white">
      <option value="">All causes</option>
      <?php foreach (wrcDonationCauses() as $key => $cause): ?>
      <option value="<?= sanitize($key) ?>" <?= $filterCause === $key ? 'selected' : '' ?>><?= sanitize($cause['label']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="currency" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-brandRed focus:bg-white">
      <option value="">All currencies</option>
      <option value="PKR" <?= $filterCurrency === 'PKR' ? 'selected' : '' ?>>PKR</option>
      <option value="USD" <?= $filterCurrency === 'USD' ? 'selected' : '' ?>>USD</option>
    </select>
    <div class="grid grid-cols-2 gap-2">
      <input type="date" name="from" value="<?= sanitize($filterFrom) ?>" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-brandRed focus:bg-white" title="From date">
      <input type="date" name="to" value="<?= sanitize($filterTo) ?>" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none transition focus:border-brandRed focus:bg-white" title="To date">
    </div>
    <button type="submit" class="rounded-xl bg-brandRed px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brandRed-hover">
      <i class="fa-solid fa-filter mr-1.5"></i> Apply
    </button>
  </div>
</form>

<!-- Ledger -->
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
    <h2 class="text-sm font-bold text-slate-900">Donation Ledger</h2>
    <span class="text-xs text-slate-400"><?= number_format($totalRows) ?> record<?= $totalRows === 1 ? '' : 's' ?><?= $activeFilters ? ' (filtered)' : '' ?></span>
  </div>

  <?php if (!$donations): ?>
  <div class="px-6 py-16 text-center">
    <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fa-solid fa-inbox text-xl"></i></span>
    <p class="text-sm font-semibold text-slate-700">No donations found</p>
    <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500"><?= $activeFilters ? 'Try clearing the filters to see the full ledger.' : 'New donations from the public form will appear here automatically.' ?></p>
  </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full min-w-[980px] text-left">
      <thead>
        <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-bold uppercase tracking-wider text-slate-400">
          <th class="px-5 py-3">Donor</th>
          <th class="px-5 py-3">Amount</th>
          <th class="px-5 py-3">Cause / Project</th>
          <th class="px-5 py-3">Payment</th>
          <th class="px-5 py-3">Date</th>
          <th class="px-5 py-3">Status</th>
          <th class="px-3 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($donations as $donation): ?>
        <?php
          $donationId = (int) $donation['id'];
          $statusKey = (string) ($donation['status'] ?? 'new');
          $statusClass = $statusStyles[$statusKey] ?? $statusStyles['new'];
          $currency = strtoupper((string) ($donation['currency'] ?? 'PKR'));
          $amountLabel = ($currency === 'USD' ? '$' : 'PKR ') . number_format((float) ($donation['amount'] ?? 0), $currency === 'USD' ? 2 : 0);
          $causeLabel = (string) ($donation['sector_name'] ?? '');
          if ($causeLabel === '') {
              $causeMeta = wrcDonationCauses()[$donation['sector_key'] ?? ''] ?? null;
              $causeLabel = $causeMeta['label'] ?? 'General';
          }
          $screenshot = (string) ($donation['screenshot_path'] ?? '');
          $isImageProof = $screenshot !== '' && (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', $screenshot);
          $paymentKey = strtolower((string) ($donation['payment_method'] ?? 'bank'));
        ?>
        <tr class="align-top transition hover:bg-slate-50/60">
          <td class="px-5 py-4">
            <div class="flex items-start gap-3">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brandRed/10 text-xs font-bold uppercase text-brandRed">
                <?= sanitize(mb_substr((string) ($donation['full_name'] ?? '?'), 0, 1)) ?>
              </span>
              <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-900"><?= sanitize((string) ($donation['full_name'] ?? '')) ?></p>
                <p class="truncate text-[11px] text-slate-500"><?= sanitize((string) ($donation['email'] ?? '')) ?></p>
                <?php if (!empty($donation['phone'])): ?>
                <p class="text-[11px] text-slate-400"><?= sanitize((string) $donation['phone']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td class="px-5 py-4">
            <p class="text-sm font-extrabold text-slate-900"><?= sanitize($amountLabel) ?></p>
            <?php if ($currency === 'USD'): ?>
            <p class="text-[10px] text-slate-400">≈ PKR <?= number_format((float) ($donation['amount_pkr'] ?? 0)) ?></p>
            <?php endif; ?>
            <p class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-slate-500"><?= sanitize(ucfirst(str_replace('-', ' ', (string) ($donation['frequency'] ?? 'one-time')))) ?></p>
          </td>
          <td class="px-5 py-4">
            <p class="text-xs font-bold text-slate-800"><?= sanitize($causeLabel) ?></p>
            <p class="mt-0.5 max-w-[220px] truncate text-[11px] text-slate-500" title="<?= sanitize((string) ($donation['project_title'] ?? '')) ?>">
              <?= sanitize((string) ($donation['project_title'] ?? 'Any project in this cause')) ?>
            </p>
          </td>
          <td class="px-5 py-4">
            <p class="text-xs font-semibold text-slate-700"><i class="fa-solid <?= $paymentIcons[$paymentKey] ?? 'fa-building-columns' ?> mr-1.5 text-slate-400"></i><?= sanitize(wrcPaymentMethodLabel($paymentKey)) ?></p>
            <?php if (!empty($donation['payment_reference'])): ?>
            <p class="mt-0.5 font-mono text-[10px] text-slate-400"><?= sanitize((string) $donation['payment_reference']) ?></p>
            <?php endif; ?>
            <?php if ($screenshot !== ''): ?>
            <p class="mt-1 inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600"><i class="fa-solid fa-paperclip"></i> Proof attached</p>
            <?php endif; ?>
          </td>
          <td class="px-5 py-4">
            <p class="text-xs font-semibold text-slate-700"><?= sanitize(formatDate((string) ($donation['created_at'] ?? ''), 'M d, Y')) ?></p>
            <p class="text-[10px] text-slate-400"><?= sanitize(formatDate((string) ($donation['created_at'] ?? ''), 'h:i A')) ?></p>
          </td>
          <td class="px-5 py-4">
            <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider <?= $statusClass ?>">
              <?= sanitize($statusOptions[$statusKey] ?? $statusKey) ?>
            </span>
          </td>
          <td class="px-3 py-4 text-right">
            <button type="button" onclick="toggleDonation(<?= $donationId ?>)" aria-expanded="false" aria-controls="donation-detail-<?= $donationId ?>"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-brandRed hover:text-brandRed" title="View details">
              <i class="fa-solid fa-chevron-down text-[10px] transition-transform" id="donation-chevron-<?= $donationId ?>"></i>
            </button>
          </td>
        </tr>
        <tr id="donation-detail-<?= $donationId ?>" class="hidden bg-slate-50/70">
          <td colspan="7" class="px-5 py-5">
            <div class="grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
              <div class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                  <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Receipt Number</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">WRC-DON-<?= str_pad((string) $donationId, 5, '0', STR_PAD_LEFT) ?></p>
                  </div>
                  <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payment Proof</p>
                    <?php if ($screenshot === ''): ?>
                    <p class="mt-1 text-xs text-slate-400">No screenshot uploaded</p>
                    <?php elseif ($isImageProof): ?>
                    <a href="<?= $base . sanitize($screenshot) ?>" target="_blank" rel="noopener" class="mt-1 inline-flex items-center gap-2 text-xs font-bold text-brandRed hover:underline">
                      <img src="<?= $base . sanitize($screenshot) ?>" alt="Payment proof" class="h-10 w-10 rounded-lg border border-slate-200 object-cover">
                      View screenshot
                    </a>
                    <?php else: ?>
                    <a href="<?= $base . sanitize($screenshot) ?>" target="_blank" rel="noopener" class="mt-1 inline-flex items-center gap-1.5 text-xs font-bold text-brandRed hover:underline">
                      <i class="fa-solid fa-file-arrow-down"></i> Open proof file
                    </a>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if (!empty($donation['message'])): ?>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Donor Message</p>
                  <p class="mt-1 whitespace-pre-line text-xs leading-relaxed text-slate-600"><?= sanitize((string) $donation['message']) ?></p>
                </div>
                <?php endif; ?>

                <form method="post" action="donations.php" class="rounded-xl border border-slate-200 bg-white px-4 py-4">
                  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                  <input type="hidden" name="donation_id" value="<?= $donationId ?>">
                  <input type="hidden" name="save_donation" value="1">
                  <div class="grid gap-3 sm:grid-cols-[180px_1fr_auto] sm:items-end">
                    <div>
                      <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</label>
                      <select name="status" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold outline-none focus:border-brandRed focus:bg-white">
                        <?php foreach ($statusOptions as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $statusKey === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Internal note</label>
                      <input type="text" name="admin_note" value="<?= sanitize((string) ($donation['admin_note'] ?? '')) ?>" placeholder="Verification note, receipt number, follow-up…"
                             class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs outline-none focus:border-brandRed focus:bg-white">
                    </div>
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
                      <i class="fa-solid fa-floppy-disk mr-1"></i> Save
                    </button>
                  </div>
                </form>
              </div>

              <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Donation Summary</p>
                <dl class="mt-3 space-y-2.5 text-xs">
                  <div class="flex justify-between gap-3"><dt class="text-slate-500">Donor</dt><dd class="text-right font-semibold text-slate-800"><?= sanitize((string) ($donation['full_name'] ?? '')) ?></dd></div>
                  <div class="flex justify-between gap-3"><dt class="text-slate-500">Phone</dt><dd class="text-right font-semibold text-slate-800"><?= sanitize((string) ($donation['phone'] ?? '—')) ?></dd></div>
                  <div class="flex justify-between gap-3"><dt class="text-slate-500">Amount</dt><dd class="text-right font-bold text-slate-900"><?= sanitize($amountLabel) ?></dd></div>
                  <div class="flex justify-between gap-3"><dt class="text-slate-500">In PKR</dt><dd class="text-right font-semibold text-slate-800">PKR <?= number_format((float) ($donation['amount_pkr'] ?? 0)) ?></dd></div>
                  <div class="flex justify-between gap-3"><dt class="text-slate-500">Frequency</dt><dd class="text-right font-semibold text-slate-800"><?= sanitize(ucfirst(str_replace('-', ' ', (string) ($donation['frequency'] ?? 'one-time')))) ?></dd></div>
                  <div class="flex justify-between gap-3"><dt class="text-slate-500">Method</dt><dd class="text-right font-semibold text-slate-800"><?= sanitize(wrcPaymentMethodLabel($paymentKey)) ?></dd></div>
                  <div class="flex justify-between gap-3"><dt class="text-slate-500">Reference</dt><dd class="text-right font-mono text-[11px] font-semibold text-slate-800"><?= sanitize((string) ($donation['payment_reference'] ?? '—')) ?></dd></div>
                  <div class="flex justify-between gap-3"><dt class="text-slate-500">Received</dt><dd class="text-right font-semibold text-slate-800"><?= sanitize(formatDate((string) ($donation['created_at'] ?? ''), 'M d, Y h:i A')) ?></dd></div>
                </dl>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row">
    <p class="text-xs text-slate-400">Page <?= $page ?> of <?= $totalPages ?></p>
    <div class="flex items-center gap-1.5">
      <?php
      $pageLink = static function (int $target) use ($filterQuery) {
          return 'donations.php?' . htmlspecialchars(http_build_query(array_merge($filterQuery, ['page' => $target])));
      };
      ?>
      <a href="<?= $pageLink(max(1, $page - 1)) ?>" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold <?= $page <= 1 ? 'pointer-events-none opacity-40' : 'text-slate-600 hover:border-brandRed hover:text-brandRed' ?>">Previous</a>
      <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
      <a href="<?= $pageLink($p) ?>" class="rounded-lg px-3 py-1.5 text-xs font-bold <?= $p === $page ? 'bg-brandRed text-white' : 'border border-slate-200 text-slate-600 hover:border-brandRed hover:text-brandRed' ?>"><?= $p ?></a>
      <?php endfor; ?>
      <a href="<?= $pageLink(min($totalPages, $page + 1)) ?>" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold <?= $page >= $totalPages ? 'pointer-events-none opacity-40' : 'text-slate-600 hover:border-brandRed hover:text-brandRed' ?>">Next</a>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<script>
function toggleDonation(id) {
  const row = document.getElementById('donation-detail-' + id);
  const chevron = document.getElementById('donation-chevron-' + id);
  const button = chevron ? chevron.closest('button') : null;
  if (!row) return;
  const isHidden = row.classList.contains('hidden');
  row.classList.toggle('hidden', !isHidden);
  if (chevron) chevron.classList.toggle('rotate-180', isHidden);
  if (button) button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
