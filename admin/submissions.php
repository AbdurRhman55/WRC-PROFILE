<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Contact Submissions';

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('submissions.php');
    }

    global $pdo;

    if (isset($_POST['update_status'])) {
        $id = (int) $_POST['submission_id'];
        $newStatus = $_POST['new_status'];
        $allowed = ['new', 'read', 'replied', 'archived'];
        if (in_array($newStatus, $allowed)) {
            $stmt = $pdo->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);
            setFlash('success', "Submission #$id marked as {$newStatus}.");
        } else {
            setFlash('error', 'Invalid status value.');
        }
        redirect('submissions.php');
    }
}

$filterStatus = $_GET['status'] ?? '';
$filterType = $_GET['inquiry_type'] ?? '';

$conditions = [];
$params = [];
$where = [];

if ($filterStatus !== '' && in_array($filterStatus, ['new', 'read', 'replied', 'archived'])) {
    $where[] = "`status` = ?";
    $params[] = $filterStatus;
}
if ($filterType !== '' && in_array($filterType, ['general', 'program', 'donation', 'partnership', 'media', 'career', 'volunteer', 'membership'])) {
    $where[] = "`inquiry_type` = ?";
    $params[] = $filterType;
}

global $pdo;
$sql = "SELECT * FROM contact_submissions";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY submitted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

$statusColors = [
    'new'      => 'bg-green-50 text-green-600 border border-green-200',
    'read'     => 'bg-blue-50 text-blue-600 border border-blue-200',
    'replied'  => 'bg-purple-50 text-purple-600 border border-purple-200',
    'archived' => 'bg-slate-100 text-slate-500 border border-slate-200',
];

$typeColors = [
    'general'     => 'bg-slate-50 text-slate-600 border border-slate-200',
    'program'     => 'bg-blue-50 text-blue-600 border border-blue-200',
    'donation'    => 'bg-green-50 text-green-600 border border-green-200',
    'partnership' => 'bg-amber-50 text-amber-600 border border-amber-200',
    'media'       => 'bg-purple-50 text-purple-600 border border-purple-200',
    'career'      => 'bg-red-50 text-red-600 border border-red-200',
    'volunteer'   => 'bg-teal-50 text-teal-600 border border-teal-200',
    'membership'  => 'bg-indigo-50 text-indigo-600 border border-indigo-200',
];

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading">Contact Submissions</h1>
    <p class="text-slate-500 text-sm mt-1">View and manage messages received through the contact form.</p>
  </div>
  <div class="flex items-center gap-3 text-sm text-slate-500">
    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-4 py-2 font-semibold">
      <i class="fa-solid fa-envelope text-brandRed"></i>
      <?= count($submissions) ?> total
    </span>
  </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
  <form method="GET" class="flex flex-col sm:flex-row gap-3">
    <div class="flex-1">
      <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
      <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
        <option value="">All Statuses</option>
        <option value="new" <?= $filterStatus === 'new' ? 'selected' : '' ?>>New</option>
        <option value="read" <?= $filterStatus === 'read' ? 'selected' : '' ?>>Read</option>
        <option value="replied" <?= $filterStatus === 'replied' ? 'selected' : '' ?>>Replied</option>
        <option value="archived" <?= $filterStatus === 'archived' ? 'selected' : '' ?>>Archived</option>
      </select>
    </div>
    <div class="flex-1">
      <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Inquiry Type</label>
      <select name="inquiry_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed transition-colors">
        <option value="">All Types</option>
        <option value="general" <?= $filterType === 'general' ? 'selected' : '' ?>>General</option>
        <option value="program" <?= $filterType === 'program' ? 'selected' : '' ?>>Program</option>
        <option value="donation" <?= $filterType === 'donation' ? 'selected' : '' ?>>Donation</option>
        <option value="partnership" <?= $filterType === 'partnership' ? 'selected' : '' ?>>Partnership</option>
        <option value="media" <?= $filterType === 'media' ? 'selected' : '' ?>>Media</option>
        <option value="career" <?= $filterType === 'career' ? 'selected' : '' ?>>Career / Jobs</option>
        <option value="volunteer" <?= $filterType === 'volunteer' ? 'selected' : '' ?>>Volunteer</option>
        <option value="membership" <?= $filterType === 'membership' ? 'selected' : '' ?>>Membership</option>
      </select>
    </div>
    <div class="flex items-end gap-2">
      <button type="submit" class="bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
        <i class="fa-solid fa-filter mr-1"></i> Filter
      </button>
      <?php if ($filterStatus !== '' || $filterType !== ''): ?>
      <a href="submissions.php" class="bg-slate-100 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
        Clear
      </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">ID</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Name</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Email</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Subject</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Type</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
          <th class="text-left px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Submitted</th>
          <th class="text-right px-5 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <?php if (empty($submissions)): ?>
        <tr>
          <td colspan="8" class="px-5 py-12 text-center text-slate-400">
            <i class="fa-solid fa-inbox text-3xl mb-3 block text-slate-300"></i>
            No submissions found.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($submissions as $i => $sub): ?>
        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer submission-row <?= $i % 2 === 0 ? '' : 'bg-slate-50/50' ?>" data-id="<?= $sub['id'] ?>">
          <td class="px-5 py-3 text-slate-500 font-mono text-xs"><?= $sub['id'] ?></td>
          <td class="px-5 py-3 font-semibold text-slate-900"><?= htmlspecialchars($sub['full_name']) ?></td>
          <td class="px-5 py-3 text-slate-500 text-xs"><?= htmlspecialchars($sub['email']) ?></td>
          <td class="px-5 py-3 text-slate-700 max-w-[200px] truncate" title="<?= htmlspecialchars($sub['subject']) ?>"><?= htmlspecialchars($sub['subject']) ?></td>
          <td class="px-5 py-3">
            <span class="inline-flex text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full <?= $typeColors[$sub['inquiry_type']] ?? 'bg-slate-50 text-slate-600' ?>"><?= htmlspecialchars($sub['inquiry_type']) ?></span>
          </td>
          <td class="px-5 py-3">
            <span class="inline-flex text-[11px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full <?= $statusColors[$sub['status']] ?? 'bg-slate-50 text-slate-600' ?>"><?= htmlspecialchars($sub['status']) ?></span>
          </td>
          <td class="px-5 py-3 text-slate-500 text-xs"><?= date('M d, Y g:i A', strtotime($sub['submitted_at'])) ?></td>
          <td class="px-5 py-3 text-right" onclick="event.stopPropagation()">
            <div class="flex items-center justify-end gap-1">
              <?php if ($sub['status'] !== 'read'): ?>
              <form method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                <input type="hidden" name="new_status" value="read">
                <button type="submit" class="text-slate-400 hover:text-blue-500 transition-colors p-1.5 rounded-lg hover:bg-blue-50" title="Mark as Read">
                  <i class="fa-solid fa-envelope-open text-xs"></i>
                </button>
              </form>
              <?php endif; ?>
              <?php if ($sub['status'] !== 'replied'): ?>
              <form method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                <input type="hidden" name="new_status" value="replied">
                <button type="submit" class="text-slate-400 hover:text-purple-500 transition-colors p-1.5 rounded-lg hover:bg-purple-50" title="Mark as Replied">
                  <i class="fa-solid fa-reply text-xs"></i>
                </button>
              </form>
              <?php endif; ?>
              <?php if ($sub['status'] !== 'archived'): ?>
              <form method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                <input type="hidden" name="new_status" value="archived">
                <button type="submit" class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 rounded-lg hover:bg-slate-100" title="Archive">
                  <i class="fa-solid fa-box-archive text-xs"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <!-- Detail row (hidden by default) -->
        <tr class="detail-row hidden" id="detail-<?= $sub['id'] ?>">
          <td colspan="8" class="px-5 py-5 bg-slate-50 border-b border-slate-200">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
              <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Phone</span>
                <p class="text-slate-700 mt-1"><?= !empty($sub['phone']) ? htmlspecialchars($sub['phone']) : '<span class="text-slate-300">—</span>' ?></p>
              </div>
              <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Volunteer Interest</span>
                <p class="text-slate-700 mt-1"><?= !empty($sub['volunteer_interest']) ? '<span class="text-green-600 font-semibold">Yes</span>' : 'No' ?></p>
              </div>
              <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Submitted At</span>
                <p class="text-slate-700 mt-1"><?= date('F d, Y \a\t g:i:s A', strtotime($sub['submitted_at'])) ?></p>
              </div>
            </div>
            <?php if (!empty($sub['attachment'])): ?>
            <div class="mt-4">
              <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Attachment</span>
              <p class="mt-1.5">
                <a href="../<?= htmlspecialchars($sub['attachment']) ?>" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-semibold text-brandRed hover:bg-brandRed hover:text-white transition-colors">
                  <i class="fa-solid fa-paperclip text-xs"></i> <?= htmlspecialchars(basename($sub['attachment'])) ?>
                </a>
              </p>
            </div>
            <?php endif; ?>
            <div class="mt-4">
              <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Message</span>
              <div class="mt-1.5 p-4 bg-white rounded-xl border border-slate-200 text-slate-700 text-sm leading-relaxed whitespace-pre-wrap"><?= nl2br(htmlspecialchars($sub['message'])) ?></div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.querySelectorAll('.submission-row').forEach(function(row) {
  row.addEventListener('click', function() {
    var id = this.dataset.id;
    var detail = document.getElementById('detail-' + id);
    if (detail) {
      detail.classList.toggle('hidden');
    }
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
