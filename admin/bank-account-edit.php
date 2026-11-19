<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Payment Channel';
$errors = [];
$account = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $account = getById('bank_accounts', (int) $_GET['id']);
    if (!$account) {
        setFlash('error', 'Bank account not found.');
        redirect('bank-accounts.php');
    }
    $pageTitle = 'Edit Bank Account: ' . $account['bank_name'];
}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('bank-accounts.php');
    }

    $bank_name = getPost('bank_name');
    $account_title = getPost('account_title');
    $account_number = getPost('account_number');
    $iban = getPost('iban');
    $branch = getPost('branch');
    $swift_code = getPost('swift_code');
    $account_type = strtolower(getPost('account_type', 'bank'));
    $currency = strtoupper(getPost('currency', 'PKR'));
    $country = getPost('country', 'Pakistan');
    $notes = getPost('notes');
    $sort_order = getPostInt('sort_order');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $allowedTypes = ['bank', 'mobile', 'payoneer', 'taptap'];
    if (!in_array($account_type, $allowedTypes, true)) {
        $account_type = 'bank';
    }
    if (!in_array($currency, ['PKR', 'USD', 'EUR', 'GBP', 'AED'], true)) {
        $currency = 'PKR';
    }

    if (empty($bank_name)) $errors[] = 'Bank name is required.';
    if (empty($account_title)) $errors[] = 'Account title is required.';
    if (empty($account_number)) $errors[] = 'Account number is required.';

    if (empty($errors)) {
        global $pdo;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE bank_accounts SET bank_name=?, account_title=?, account_number=?, iban=?, branch=?, swift_code=?, account_type=?, currency=?, country=?, notes=?, is_active=?, sort_order=? WHERE id=?");
            $stmt->execute([$bank_name, $account_title, $account_number, $iban, $branch, $swift_code, $account_type, $currency, $country, $notes, $is_active, $sort_order, $account['id']]);
            setFlash('success', 'Payment channel updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO bank_accounts (bank_name, account_title, account_number, iban, branch, swift_code, account_type, currency, country, notes, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$bank_name, $account_title, $account_number, $iban, $branch, $swift_code, $account_type, $currency, $country, $notes, $is_active, $sort_order]);
            setFlash('success', 'Payment channel created successfully.');
        }
        redirect('bank-accounts.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Payment Channel' : 'Add New Payment Channel' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update the details shown on the donation form.' : 'Add a bank, mobile wallet or international channel.' ?></p>
  </div>
  <a href="bank-accounts.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Payment Channels
  </a>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
  <div class="flex items-center gap-2 mb-1"><i class="fa-solid fa-circle-exclamation"></i><span class="font-semibold text-sm">Please fix the following errors:</span></div>
  <ul class="list-disc list-inside text-sm mt-1">
    <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <!-- Account Info -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Channel Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Channel / Bank Name *</label>
        <input type="text" name="bank_name" value="<?= htmlspecialchars($account['bank_name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Channel Type *</label>
        <select name="account_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <?php foreach (['bank' => 'Pakistani Bank Transfer', 'mobile' => 'Mobile Wallet (EasyPaisa / JazzCash)', 'payoneer' => 'Payoneer (International)', 'taptap' => 'TapTap Send (International)'] as $typeKey => $typeLabel): ?>
          <option value="<?= $typeKey ?>" <?= ($account['account_type'] ?? 'bank') === $typeKey ? 'selected' : '' ?>><?= $typeLabel ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Account Title *</label>
        <input type="text" name="account_title" value="<?= htmlspecialchars($account['account_title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Account Number / IBAN / Email *</label>
        <input type="text" name="account_number" value="<?= htmlspecialchars($account['account_number'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
        <p class="mt-1 text-[11px] text-slate-400">For Payoneer use the Payoneer email; for TapTap Send use the receiving mobile number.</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">IBAN</label>
        <input type="text" name="iban" value="<?= htmlspecialchars($account['iban'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Branch</label>
        <input type="text" name="branch" value="<?= htmlspecialchars($account['branch'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Swift Code</label>
        <input type="text" name="swift_code" value="<?= htmlspecialchars($account['swift_code'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Currency</label>
        <select name="currency" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <?php foreach (['PKR' => 'PKR — Pakistani Rupee', 'USD' => 'USD — US Dollar', 'EUR' => 'EUR — Euro', 'GBP' => 'GBP — Pound Sterling', 'AED' => 'AED — UAE Dirham'] as $code => $label): ?>
          <option value="<?= $code ?>" <?= strtoupper((string) ($account['currency'] ?? 'PKR')) === $code ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Country / Region</label>
        <input type="text" name="country" value="<?= htmlspecialchars($account['country'] ?? 'Pakistan') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Public Instructions / Notes</label>
        <textarea name="notes" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" placeholder="Shown on the donation form, e.g. send to this number then upload the screenshot."><?= htmlspecialchars($account['notes'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($account['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($account['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="bank-accounts.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Account' : 'Create Account' ?>
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
