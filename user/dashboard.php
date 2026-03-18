<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'user') {
    redirect_to('../admin/dashboard.php');
}

$uid = current_user_id();

$stats = [
    'tasks_total' => 0,
    'tasks_done' => 0,
    'pending_notif' => 0,
];

$taskTotalStmt = $conn->prepare('SELECT COUNT(*) AS total FROM tasks WHERE user_id = ?');
if ($taskTotalStmt) {
    $taskTotalStmt->bind_param('i', $uid);
    $taskTotalStmt->execute();
    $res = $taskTotalStmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stats['tasks_total'] = (int) ($row['total'] ?? 0);
    $taskTotalStmt->close();
}

$taskDoneStmt = $conn->prepare("SELECT COUNT(*) AS total FROM tasks WHERE user_id = ? AND status = 'done'");
if ($taskDoneStmt) {
    $taskDoneStmt->bind_param('i', $uid);
    $taskDoneStmt->execute();
    $res = $taskDoneStmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stats['tasks_done'] = (int) ($row['total'] ?? 0);
    $taskDoneStmt->close();
}

$notifStmt = $conn->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0');
if ($notifStmt) {
    $notifStmt->bind_param('i', $uid);
    $notifStmt->execute();
    $res = $notifStmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stats['pending_notif'] = (int) ($row['total'] ?? 0);
    $notifStmt->close();
}

$demandeStmt = $conn->prepare('SELECT status, date_fin FROM demandes WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$demande = null;
if ($demandeStmt) {
    $demandeStmt->bind_param('i', $uid);
    $demandeStmt->execute();
    $res = $demandeStmt->get_result();
    $demande = $res ? $res->fetch_assoc() : null;
    $demandeStmt->close();
}

render_layout_start('User Dashboard', 'dashboard');
?>

<section class="grid gap-4 sm:grid-cols-3">
    <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
        <p class="text-sm text-slate-300">Total Tasks</p>
        <h2 class="mt-2 text-3xl font-bold text-white"><?php echo e((string) $stats['tasks_total']); ?></h2>
    </article>
    <article class="rounded-2xl border border-emerald-300/20 bg-emerald-400/10 p-5">
        <p class="text-sm text-emerald-200">Completed Tasks</p>
        <h2 class="mt-2 text-3xl font-bold text-emerald-100"><?php echo e((string) $stats['tasks_done']); ?></h2>
    </article>
    <article class="rounded-2xl border border-cyan-300/20 bg-cyan-400/10 p-5">
        <p class="text-sm text-cyan-200">Unread Notifications</p>
        <h2 class="mt-2 text-3xl font-bold text-cyan-100"><?php echo e((string) $stats['pending_notif']); ?></h2>
    </article>
</section>

<section class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-5">
    <h3 class="text-lg font-semibold text-white">Latest Demande</h3>
    <?php if ($demande): ?>
        <p class="mt-2 text-slate-200">Status: <span class="font-semibold"><?php echo e($demande['status']); ?></span></p>
        <p class="text-sm text-slate-400">End date: <?php echo e($demande['date_fin']); ?></p>
    <?php else: ?>
        <p class="mt-2 text-slate-300">No demande submitted yet. <a href="demande.php" class="text-cyan-300 hover:text-cyan-200">Create one now</a>.</p>
    <?php endif; ?>
</section>

<?php render_layout_end(); ?>
