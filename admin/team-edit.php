<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Edit Team Member';
$errors = [];
$member = null;
$isEdit = isset($_GET['id']) && $_GET['id'] > 0;

if ($isEdit) {
    $member = getById('team_members', (int) $_GET['id']);
    if (!$member) {
        setFlash('error', 'Team member not found.');
        redirect('team.php');
    }
    $pageTitle = 'Edit Team Member: ' . $member['name'];
}

global $pdo;
$hasLinkedin = false;
try {
    $cols = $pdo->query("SHOW COLUMNS FROM team_members LIKE 'linkedin_url'")->fetchAll();
    $hasLinkedin = !empty($cols);
} catch (Exception $e) {}

if (isPost()) {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        setFlash('error', 'Invalid security token.');
        redirect('team.php');
    }

    $name = getPost('name');
    $title = getPost('title');
    $role_type = getPost('role_type');
    $bio = getPost('bio');
    $photo_path = getPost('photo_path');
    $email = getPost('email');
    $linkedin_url = getPost('linkedin_url');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int) getPost('sort_order');

    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($title)) $errors[] = 'Title/Role is required.';
    $validRoles = ['chairman', 'executive', 'leadership', 'staff'];
    if (!in_array($role_type, $validRoles)) $errors[] = 'Invalid role type.';

    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE team_members SET name=?, title=?, role_type=?, bio=?, photo_path=?, email=?, is_active=?, sort_order=?";
            $params = [$name, $title, $role_type, $bio, $photo_path, $email, $is_active, $sort_order];
            if ($hasLinkedin) {
                $sql .= ", linkedin_url=?";
                $params[] = $linkedin_url;
            }
            $sql .= " WHERE id=?";
            $params[] = $member['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            setFlash('success', 'Team member updated successfully.');
        } else {
            if ($hasLinkedin) {
                $stmt = $pdo->prepare("INSERT INTO team_members (name, title, role_type, bio, photo_path, email, linkedin_url, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $title, $role_type, $bio, $photo_path, $email, $linkedin_url, $is_active, $sort_order]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO team_members (name, title, role_type, bio, photo_path, email, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $title, $role_type, $bio, $photo_path, $email, $is_active, $sort_order]);
            }
            setFlash('success', 'Team member created successfully.');
        }
        redirect('team.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900 font-heading"><?= $isEdit ? 'Edit Team Member' : 'Add New Team Member' ?></h1>
    <p class="text-slate-500 text-sm mt-1"><?= $isEdit ? 'Update team member profile.' : 'Add a new team member.' ?></p>
  </div>
  <a href="team.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Team
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

  <!-- Basic Info -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Personal Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
        <input type="text" name="name" value="<?= htmlspecialchars($member['name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Title / Role *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($member['title'] ?? '') ?>" required placeholder="e.g. Executive Director" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Role Type *</label>
        <select name="role_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
          <?php
          $roles = ['chairman' => 'Chairman', 'executive' => 'Executive', 'leadership' => 'Leadership', 'staff' => 'Staff'];
          foreach ($roles as $val => $label):
          ?>
          <option value="<?= $val ?>" <?= ($member['role_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <?php if ($hasLinkedin): ?>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">LinkedIn URL</label>
        <input type="url" name="linkedin_url" value="<?= htmlspecialchars($member['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <?php endif; ?>
      <?php uploadWidget('photo_path', 'Profile Photo', $member['photo_path'] ?? '', 'image', 'team_photo_img'); ?>
    </div>
  </div>

  <!-- Bio -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Biography</h2>
    <textarea name="bio" rows="6" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed leading-relaxed"><?= htmlspecialchars($member['bio'] ?? '') ?></textarea>
  </div>

  <!-- Settings -->
  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Settings</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= (int) ($member['sort_order'] ?? 0) ?>" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= ($member['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <a href="team.php" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancel</a>
    <button type="submit" class="px-6 py-2.5 rounded-xl bg-brandRed text-white text-sm font-semibold hover:bg-brandRed-hover transition-colors shadow-sm">
      <i class="fa-solid fa-check text-xs mr-1"></i> <?= $isEdit ? 'Update Member' : 'Create Member' ?>
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
