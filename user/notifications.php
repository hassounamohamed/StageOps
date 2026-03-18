<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'user') {
    redirect_to('../admin/dashboard.php');
}

$userId = current_user_id();

$readStmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
if ($readStmt) {
    $readStmt->bind_param('i', $userId);
    $readStmt->execute();
    $readStmt->close();
}

$notifStmt = $conn->prepare('SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY id DESC');
$notif = null;
if ($notifStmt) {
    $notifStmt->bind_param('i', $userId);
    $notifStmt->execute();
    $notif = $notifStmt->get_result();
}

render_layout_start('Notifications', 'notifications');
?>

<section class="rounded-2xl border border-white/10 bg-white/5 p-5">
    <h1 class="text-xl font-bold text-white">Notifications</h1>
    <div class="mt-4 space-y-3">
        <?php if ($notif && $notif->num_rows > 0): while ($n = $notif->fetch_assoc()): ?>
            <article class="rounded-xl border border-white/10 bg-slate-900/40 p-4">
                <p class="text-slate-100"><?php echo e($n['message']); ?></p>
                <p class="mt-1 text-xs text-slate-400"><?php echo e($n['created_at']); ?></p>
            </article>
        <?php endwhile; else: ?>
            <p class="text-slate-300">No notifications yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php
if ($notifStmt) {
    $notifStmt->close();
}
render_layout_end();
?>