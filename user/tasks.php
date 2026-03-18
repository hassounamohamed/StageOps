<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'user') {
	redirect_to('../admin/dashboard.php');
}

$userId = current_user_id();

if (isset($_POST['done_task_id'])) {
	if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
		redirect_to('tasks.php');
	}

	$taskId = (int) $_POST['done_task_id'];

	$update = $conn->prepare("UPDATE tasks SET status = 'done' WHERE id = ? AND user_id = ?");
	if ($update) {
		$update->bind_param('ii', $taskId, $userId);
		$update->execute();
		$update->close();
	}

	$admins = $conn->query("SELECT id FROM users WHERE role = 'admin'");
	if ($admins) {
		while ($admin = $admins->fetch_assoc()) {
			$msg = 'A user completed a task.';
			$notif = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
			if ($notif) {
				$adminId = (int) $admin['id'];
				$notif->bind_param('is', $adminId, $msg);
				$notif->execute();
				$notif->close();
			}
		}
	}

	redirect_to('tasks.php');
}

$taskStmt = $conn->prepare('SELECT id, title, description, status FROM tasks WHERE user_id = ? ORDER BY id DESC');
$tasks = null;
if ($taskStmt) {
	$taskStmt->bind_param('i', $userId);
	$taskStmt->execute();
	$tasks = $taskStmt->get_result();
}

render_layout_start('My Tasks', 'tasks');
?>

<section class="rounded-2xl border border-white/10 bg-white/5 p-5">
	<h1 class="text-xl font-bold text-white">My Tasks</h1>
	<div class="mt-4 space-y-3">
		<?php if ($tasks && $tasks->num_rows > 0): while ($t = $tasks->fetch_assoc()): ?>
			<article class="rounded-xl border border-white/10 bg-slate-900/40 p-4">
				<h2 class="text-lg font-semibold text-white"><?php echo e($t['title']); ?></h2>
				<p class="mt-1 text-sm text-slate-300"><?php echo e($t['description']); ?></p>
				<div class="mt-3 flex items-center justify-between gap-3">
					<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo $t['status'] === 'done' ? 'bg-emerald-400/20 text-emerald-100' : 'bg-amber-400/20 text-amber-100'; ?>"><?php echo e($t['status']); ?></span>
					<?php if ($t['status'] === 'pending'): ?>
						<form method="POST">
							<?php echo csrf_input(); ?>
							<input type="hidden" name="done_task_id" value="<?php echo e((string) $t['id']); ?>">
							<button type="submit" class="rounded-lg bg-cyan-500 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-cyan-400">Mark Done</button>
						</form>
					<?php endif; ?>
				</div>
			</article>
		<?php endwhile; else: ?>
			<p class="text-slate-300">No tasks assigned yet.</p>
		<?php endif; ?>
	</div>
</section>

<?php
if ($taskStmt) {
	$taskStmt->close();
}
render_layout_end();
?>