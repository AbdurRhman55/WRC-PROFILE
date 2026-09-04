<?php
require __DIR__ . '/config.php';
requireAdmin();

$jobs = getRows('job_listings', [], 'posted_at DESC');
$types = ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (int) $_POST['delete_id'];
        $pdo->prepare("DELETE FROM job_listings WHERE id = ?")->execute([$id]);
        setFlash('success', 'Job listing deleted.');
    }
    header('Location: jobs.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Job Listings</h1>
    <p class="text-sm text-slate-500 mt-1">Manage career opportunities and internships</p>
  </div>
  <a href="job-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
    <i class="fa-solid fa-plus text-xs"></i> Add Job
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <?php if (empty($jobs)): ?>
  <div class="text-center py-16">
    <i class="fa-solid fa-briefcase text-4xl text-slate-200 mb-4"></i>
    <p class="text-slate-400 text-sm">No job listings yet.</p>
    <a href="job-edit.php" class="mt-3 inline-block text-sm text-brandRed font-semibold hover:underline">Post your first job &rarr;</a>
  </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <th class="px-4 py-3 text-slate-500 font-semibold">Position</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Department</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Type</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Deadline</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Status</th>
          <th class="px-4 py-3 text-slate-500 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($jobs as $job): ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3">
            <p class="font-semibold text-slate-900"><?= htmlspecialchars($job['title']) ?></p>
            <p class="text-xs text-slate-400"><?= htmlspecialchars($job['location'] ?? '') ?></p>
          </td>
          <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($job['department'] ?? '—') ?></td>
          <td class="px-4 py-3">
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full bg-blue-50 text-blue-700"><?= $types[$job['employment_type']] ?? $job['employment_type'] ?></span>
          </td>
          <td class="px-4 py-3 text-xs text-slate-500"><?= $job['deadline'] ? date('M d, Y', strtotime($job['deadline'])) : '—' ?></td>
          <td class="px-4 py-3">
            <span class="w-2 h-2 rounded-full inline-block <?= $job['is_active'] ? 'bg-green-400' : 'bg-slate-300' ?>"></span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="job-edit.php?id=<?= $job['id'] ?>" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">Edit</a>
              <form method="POST" onsubmit="return confirm('Delete this job listing?')">
                <input type="hidden" name="delete_id" value="<?= $job['id'] ?>">
                <button class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
