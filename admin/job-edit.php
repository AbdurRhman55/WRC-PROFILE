<?php
require __DIR__ . '/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$job = $isEdit ? getRow('job_listings', ['id' => $id]) : null;

if ($isEdit && !$job) {
    setFlash('error', 'Job listing not found.');
    header('Location: jobs.php');
    exit;
}

$types = ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'           => getPost('title'),
        'department'      => getPost('department'),
        'location'        => getPost('location'),
        'employment_type' => getPost('employment_type'),
        'description'     => getPost('description'),
        'requirements'    => getPost('requirements'),
        'is_active'       => isset($_POST['is_active']) ? 1 : 0,
        'deadline'        => getPost('deadline') ?: null,
    ];

    if (!$data['title'] || !$data['employment_type']) {
        setFlash('error', 'Title and employment type are required.');
    } else {
        if ($isEdit) {
            updateRow('job_listings', $data, ['id' => $id]);
            setFlash('success', 'Job listing updated!');
        } else {
            insertRow('job_listings', $data);
            setFlash('success', 'Job listing created!');
        }
        header('Location: jobs.php');
        exit;
    }
}

$pageTitle = $isEdit ? 'Edit Job' : 'New Job Listing';
include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?= $pageTitle ?></h1>
    <p class="text-sm text-slate-500 mt-1"><?= $isEdit ? 'Update job listing details' : 'Post a new career opportunity' ?></p>
  </div>
  <a href="jobs.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Job Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Job Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($job['title'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Department</label>
        <input type="text" name="department" value="<?= htmlspecialchars($job['department'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($job['location'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Employment Type *</label>
        <select name="employment_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required>
          <?php foreach ($types as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($job['employment_type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Application Deadline</label>
        <input type="date" name="deadline" value="<?= $job['deadline'] ?? '' ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
        <textarea name="description" rows="5" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($job['description'] ?? '') ?></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Requirements</label>
        <textarea name="requirements" rows="5" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($job['requirements'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <label class="flex items-center gap-2 cursor-pointer">
      <input type="checkbox" name="is_active" value="1" <?= ($job['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
      <span class="text-sm font-semibold text-slate-700">Active — visible on careers page</span>
    </label>
  </div>

  <div class="flex items-center gap-3">
    <button type="submit" class="inline-flex items-center gap-2 bg-brandRed text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
      <i class="fa-solid fa-check text-xs"></i> <?= $isEdit ? 'Update Listing' : 'Post Listing' ?>
    </button>
    <a href="jobs.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Cancel</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
