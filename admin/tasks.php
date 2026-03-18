<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'admin') {
	redirect_to('../user/demande.php');
}

$message = '';

if (isset($_POST['add'])) {
	if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
		$message = 'Session expired. Please refresh and try again.';
	} else {
	$userId = (int) ($_POST['user_id'] ?? 0);
	$title = trim((string) ($_POST['title'] ?? ''));
	$desc = trim((string) ($_POST['desc'] ?? ''));

	if ($userId > 0 && $title !== '') {
		$insertTask = $conn->prepare("INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, 'pending')");
		if ($insertTask) {
			$insertTask->bind_param('iss', $userId, $title, $desc);
			$insertTask->execute();
			$insertTask->close();
		}

		$notifMessage = 'New task assigned: ' . $title;
		$insertNotif = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
		if ($insertNotif) {
			$insertNotif->bind_param('is', $userId, $notifMessage);
			$insertNotif->execute();
			$insertNotif->close();
		}

		$message = 'Task assigned successfully.';
	}
	}
}

$users = $conn->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name ASC");
$tasks = $conn->query('SELECT t.id, t.title, t.description, t.status, t.created_at, u.name FROM tasks t JOIN users u ON t.user_id = u.id ORDER BY t.id DESC');

render_layout_start('Admin Tasks', 'tasks');
?>

<section class="grid gap-6 lg:grid-cols-3">
	<article class="rounded-2xl border border-white/10 bg-white/5 p-5 lg:col-span-1">
		<h1 class="text-xl font-bold text-white">Assign Task</h1>
		<?php if ($message !== ''): ?>
			<p class="mt-3 rounded-xl border border-emerald-300/20 bg-emerald-400/10 px-3 py-2 text-sm text-emerald-100"><?php echo e($message); ?></p>
		<?php endif; ?>
		<form method="POST" class="mt-4 space-y-3">
			<?php echo csrf_input(); ?>
			<select name="user_id" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
				<option value="">Select user</option>
				<?php if ($users): while ($u = $users->fetch_assoc()): ?>
					<option value="<?php echo e((string) $u['id']); ?>"><?php echo e($u['name'] . ' (' . $u['email'] . ')'); ?></option>
				<?php endwhile; endif; ?>
			</select>
			<input type="text" name="title" required placeholder="Task title" class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
			<textarea name="desc" rows="4" placeholder="Description" class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40"></textarea>
			<button name="add" type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-400">Add Task</button>
		</form>
	</article>

	<article class="rounded-2xl border border-white/10 bg-white/5 p-5 lg:col-span-2">
		<h2 class="text-xl font-bold text-white">Recent Tasks</h2>
		<div class="mt-4 space-y-3">
			<?php if ($tasks && $tasks->num_rows > 0): while ($t = $tasks->fetch_assoc()): ?>
				<div class="rounded-xl border border-white/10 bg-slate-900/40 p-4">
					<p class="text-sm text-cyan-200">Assigned to: <?php echo e($t['name']); ?></p>
					<h3 class="mt-1 text-lg font-semibold text-white"><?php echo e($t['title']); ?></h3>
					<p class="mt-1 text-sm text-slate-300"><?php echo e($t['description']); ?></p>
					<p class="mt-2 text-xs text-slate-400">Status: <?php echo e($t['status']); ?></p>
				</div>
			<?php endwhile; else: ?>
				<p class="text-slate-300">No tasks found.</p>
			<?php endif; ?>
		</div>
	</article>
</section>

<?php render_layout_end(); ?>