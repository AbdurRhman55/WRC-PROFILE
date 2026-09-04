<?php
require_once __DIR__ . '/config.php';
requireLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$event = $isEdit ? getRow('events', ['id' => $id]) : null;

if ($isEdit && !$event) { setFlash('error', 'Event not found.'); redirect('events.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('events.php'); }

    $data = [
        'title'        => getPost('title'),
        'description'  => getPost('description'),
        'event_date'   => getPost('event_date'),
        'event_time'   => getPost('event_time'),
        'location'     => getPost('location'),
        'icon_class'   => getPost('icon_class') ?: 'fa-solid fa-calendar-days',
        'gradient_from'=> getPost('gradient_from') ?: '#ca0000',
        'gradient_to'  => getPost('gradient_to') ?: '#8b0000',
        'register_link'=> getPost('register_link'),
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'   => getPostInt('sort_order'),
    ];

    if (!$data['title'] || !$data['event_date']) {
        setFlash('error', 'Title and date are required.');
    } else {
        if ($isEdit) { updateRow('events', $data, ['id' => $id]); setFlash('success', 'Event updated!'); }
        else { insertRow('events', $data); setFlash('success', 'Event created!'); }
        redirect('events.php');
    }
}

$pageTitle = $isEdit ? 'Edit Event' : 'New Event';
include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?= $pageTitle ?></h1>
    <p class="text-sm text-slate-500 mt-1"><?= $isEdit ? 'Update event details' : 'Add a new event' ?></p>
  </div>
  <a href="events.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors"><i class="fa-solid fa-arrow-left text-xs"></i> Back</a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<form method="POST" class="space-y-6">
  <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Event Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($event['title'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Date *</label>
        <input type="date" name="event_date" value="<?= $event['event_date'] ?? '' ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed" required>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Time</label>
        <input type="text" name="event_time" value="<?= htmlspecialchars($event['event_time'] ?? '') ?>" placeholder="09:00 AM - 05:00 PM" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($event['location'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Appearance</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Icon Class</label>
        <input type="text" name="icon_class" value="<?= htmlspecialchars($event['icon_class'] ?? 'fa-solid fa-calendar-days') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Gradient Start</label>
        <input type="color" name="gradient_from" value="<?= htmlspecialchars($event['gradient_from'] ?? '#ca0000') ?>" class="w-full h-10 rounded-xl border border-slate-200 cursor-pointer">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Gradient End</label>
        <input type="color" name="gradient_to" value="<?= htmlspecialchars($event['gradient_to'] ?? '#8b0000') ?>" class="w-full h-10 rounded-xl border border-slate-200 cursor-pointer">
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Register Link</label>
        <input type="text" name="register_link" value="<?= htmlspecialchars($event['register_link'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Sort Order</label>
        <input type="number" name="sort_order" value="<?= $event['sort_order'] ?? 0 ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brandRed/20 focus:border-brandRed">
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-6">
    <label class="flex items-center gap-2 cursor-pointer">
      <input type="checkbox" name="is_active" value="1" <?= ($event['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-brandRed focus:ring-brandRed/20">
      <span class="text-sm font-semibold text-slate-700">Active</span>
    </label>
  </div>

  <div class="flex items-center gap-3">
    <button type="submit" class="inline-flex items-center gap-2 bg-brandRed text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
      <i class="fa-solid fa-check text-xs"></i> <?= $isEdit ? 'Update Event' : 'Create Event' ?>
    </button>
    <a href="events.php" class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Cancel</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
