<?php
require_once __DIR__ . '/config.php';
requireLogin();

$pageTitle = 'Events';

if (isPost() && isset($_POST['delete_id'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { redirect('events.php'); }
    global $pdo;
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([(int) $_POST['delete_id']]);
    setFlash('success', 'Event deleted.');
    redirect('events.php');
}

$events = getRows('events', [], 'event_date DESC');

include __DIR__ . '/includes/header.php';
?>

<div class="flex items-center justify-between mb-8">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Events</h1>
    <p class="text-sm text-slate-500 mt-1">Manage upcoming events and activities</p>
  </div>
  <a href="event-edit.php" class="inline-flex items-center gap-2 bg-brandRed text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
    <i class="fa-solid fa-plus text-xs"></i> Add Event
  </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium bg-<?= $flash['type'] === 'success' ? 'green-50 text-green-700 border border-green-200' : 'red-50 text-red-700 border border-red-200' ?>"><?= $flash['message'] ?></div>
<?php endif; ?>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
  <?php if (empty($events)): ?>
  <div class="text-center py-16">
    <i class="fa-solid fa-calendar-days text-4xl text-slate-200 mb-4"></i>
    <p class="text-slate-400 text-sm">No events yet.</p>
    <a href="event-edit.php" class="mt-3 inline-block text-sm text-brandRed font-semibold hover:underline">Add your first event &rarr;</a>
  </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <th class="px-4 py-3 text-slate-500 font-semibold">Event</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Date</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Location</th>
          <th class="px-4 py-3 text-slate-500 font-semibold">Status</th>
          <th class="px-4 py-3 text-slate-500 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($events as $evt): ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,<?= $evt['gradient_from'] ?>,<?= $evt['gradient_to'] ?>)">
                <i class="<?= htmlspecialchars($evt['icon_class']) ?> text-white text-xs"></i>
              </div>
              <div>
                <p class="font-semibold text-slate-900"><?= htmlspecialchars($evt['title']) ?></p>
                <p class="text-xs text-slate-400"><?= htmlspecialchars($evt['event_time'] ?? '') ?></p>
              </div>
            </div>
          </td>
          <td class="px-4 py-3 text-slate-600 text-xs font-semibold"><?= date('M d, Y', strtotime($evt['event_date'])) ?></td>
          <td class="px-4 py-3 text-xs text-slate-500"><?= htmlspecialchars($evt['location'] ?? '') ?></td>
          <td class="px-4 py-3">
            <?php $isPast = strtotime($evt['event_date']) < time(); ?>
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full <?= $isPast ? 'bg-slate-100 text-slate-500' : 'bg-green-50 text-green-600' ?>"><?= $isPast ? 'Past' : 'Upcoming' ?></span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-2">
              <a href="event-edit.php?id=<?= $evt['id'] ?>" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">Edit</a>
              <form method="POST" onsubmit="return confirm('Delete this event?')">
                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                <input type="hidden" name="delete_id" value="<?= $evt['id'] ?>">
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
