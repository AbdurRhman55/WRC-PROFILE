<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Bank Account';
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
    $sort_order = getPostInt('sort_order');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($bank_name)) $errors[] = 'Bank name is required.';
    if (empty($account_title)) $errors[] = 'Account title is required.';
    if (empty($account_number)) $errors[] = 'Account number is required.';

    if (empty($errors)) {
        global $pdo;

        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE bank_accounts SET bank_name=?, account_title=?, account_number=?, iban=?, branch=?, swift_code=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$bank_name, $account_title, $account_number, $iban, $branch, $swift_code, $is_active, $sort_order, $account['id']]);
            setFlash('success', 'Bank account updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO bank_accounts (bank_name, account_title, account_number, iban, branch, swift_code, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$bank_name, $account_title, $account_number, $iban, $branch, $swift_code, $is_active, $sort_order]);
            setFlash('success', 'Bank account created successfully.');
        }
        redirect('bank-accounts.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Bank Account' : 'Add New Bank Account' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update bank account details.' : 'Create a new bank account entry.' ?></p>
  </div>
  <a href="bank-accounts.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Bank Accounts
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
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Account Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Bank Name *</label>
        <input type="text" name="bank_name" value="<?= htmlspecialchars($account['bank_name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Account Title *</label>
        <input type="text" name="account_title" value="<?= htmlspecialchars($account['account_title'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Account Number *</label>
        <input type="text" name="account_number" value="<?= htmlspecialchars($account['account_number'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
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
