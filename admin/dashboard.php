<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'admin') {
    redirect_to('../user/demande.php');
}

$stats = [
    'users' => 0,
    'pending_demandes' => 0,
    'tasks' => 0,
    'unread_notifications' => 0,
];

$queries = [
    'users' => 'SELECT COUNT(*) AS total FROM users WHERE role = "user"',
    'pending_demandes' => "SELECT COUNT(*) AS total FROM demandes WHERE status = 'pending'",
    'tasks' => 'SELECT COUNT(*) AS total FROM tasks',
    'unread_notifications' => 'SELECT COUNT(*) AS total FROM notifications WHERE is_read = 0',
];

foreach ($queries as $key => $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $stats[$key] = (int) ($row['total'] ?? 0);
    }
}

render_layout_start('Admin Dashboard', 'dashboard');
?>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-white/10 bg-white/5 p-5">
        <p class="text-sm text-slate-300">Users</p>
        <h2 class="mt-2 text-3xl font-bold text-white"><?php echo e((string) $stats['users']); ?></h2>
    </article>
    <article class="rounded-2xl border border-amber-300/20 bg-amber-400/10 p-5">
        <p class="text-sm text-amber-200">Pending Demandes</p>
        <h2 class="mt-2 text-3xl font-bold text-amber-100"><?php echo e((string) $stats['pending_demandes']); ?></h2>
    </article>
    <article class="rounded-2xl border border-cyan-300/20 bg-cyan-400/10 p-5">
        <p class="text-sm text-cyan-200">Tasks</p>
        <h2 class="mt-2 text-3xl font-bold text-cyan-100"><?php echo e((string) $stats['tasks']); ?></h2>
    </article>
    <article class="rounded-2xl border border-rose-300/20 bg-rose-400/10 p-5">
        <p class="text-sm text-rose-200">Unread Notifications</p>
        <h2 class="mt-2 text-3xl font-bold text-rose-100"><?php echo e((string) $stats['unread_notifications']); ?></h2>
    </article>
</section>

<section class="mt-6 grid gap-4 md:grid-cols-2">
    <a href="tasks.php" class="rounded-2xl border border-white/10 bg-white/5 p-5 transition hover:bg-white/10">
        <h3 class="text-lg font-semibold text-white">Manage Tasks</h3>
        <p class="mt-1 text-sm text-slate-300">Assign and track trainee tasks.</p>
    </a>
    <a href="demandes.php" class="rounded-2xl border border-white/10 bg-white/5 p-5 transition hover:bg-white/10">
        <h3 class="text-lg font-semibold text-white">Review Demandes</h3>
        <p class="mt-1 text-sm text-slate-300">Accept or refuse internship requests.</p>
    </a>
</section>

<?php render_layout_end(); ?>
