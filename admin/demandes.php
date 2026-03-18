<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'admin') {
	redirect_to('../user/demande.php');
}

if (isset($_POST['action'], $_POST['demande_id'])) {
	if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
		redirect_to('demandes.php');
	}

	$demandeId = (int) $_POST['demande_id'];
	$action = $_POST['action'] === 'accept' ? 'accepted' : 'refused';

	$update = $conn->prepare('UPDATE demandes SET status = ? WHERE id = ?');
	if ($update) {
		$update->bind_param('si', $action, $demandeId);
		$update->execute();
		$update->close();
	}

	$userStmt = $conn->prepare('SELECT user_id FROM demandes WHERE id = ? LIMIT 1');
	if ($userStmt) {
		$userStmt->bind_param('i', $demandeId);
		$userStmt->execute();
		$userRes = $userStmt->get_result();
		$target = $userRes ? $userRes->fetch_assoc() : null;
		$userStmt->close();

		if ($target) {
			$message = $action === 'accepted' ? 'Your stage request is accepted.' : 'Your stage request is refused.';
			$notif = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
			if ($notif) {
				$uid = (int) $target['user_id'];
				$notif->bind_param('is', $uid, $message);
				$notif->execute();
				$notif->close();
			}
		}
	}

	redirect_to('demandes.php');
}

$sql = "SELECT d.id, d.university, d.enterprise, d.date_debut, d.date_fin, d.cv, d.status, u.name, u.email
		FROM demandes d
		JOIN users u ON d.user_id = u.id
		ORDER BY d.id DESC";
$res = $conn->query($sql);

render_layout_start('Demandes Review', 'demandes');
?>

<section class="rounded-2xl border border-white/10 bg-white/5 p-5">
	<h1 class="text-xl font-bold text-white">Internship Demandes</h1>
	<div class="mt-4 space-y-3">
		<?php if ($res && $res->num_rows > 0): while ($d = $res->fetch_assoc()): ?>
			<article class="rounded-xl border border-white/10 bg-slate-900/40 p-4">
				<div class="flex flex-wrap items-start justify-between gap-3">
					<div>
						<h2 class="text-lg font-semibold text-white"><?php echo e($d['name']); ?></h2>
						<p class="text-sm text-slate-300"><?php echo e($d['email']); ?></p>
						<p class="mt-1 text-sm text-slate-200">University: <?php echo e($d['university']); ?></p>
						<p class="text-sm text-slate-200">Enterprise: <?php echo e($d['enterprise']); ?></p>
						<p class="text-xs text-slate-400">From <?php echo e($d['date_debut']); ?> to <?php echo e($d['date_fin']); ?></p>
						<?php if (!empty($d['cv'])): ?>
							<a href="../<?php echo e($d['cv']); ?>" target="_blank" class="mt-2 inline-block text-sm font-medium text-cyan-300 hover:text-cyan-200">Open CV</a>
						<?php endif; ?>
					</div>
					<div class="text-right">
						<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?php echo $d['status'] === 'accepted' ? 'bg-emerald-400/20 text-emerald-200' : ($d['status'] === 'refused' ? 'bg-rose-400/20 text-rose-200' : 'bg-amber-400/20 text-amber-100'); ?>">
							<?php echo e($d['status']); ?>
						</span>
						<?php if ($d['status'] === 'pending'): ?>
							<form method="POST" class="mt-3 flex gap-2">
								<?php echo csrf_input(); ?>
								<input type="hidden" name="demande_id" value="<?php echo e((string) $d['id']); ?>">
								<button name="action" value="accept" class="rounded-lg bg-emerald-500 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-emerald-400">Accept</button>
								<button name="action" value="refuse" class="rounded-lg bg-rose-500 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-rose-400">Refuse</button>
							</form>
						<?php endif; ?>
					</div>
				</div>
			</article>
		<?php endwhile; else: ?>
			<p class="text-slate-300">No demandes available.</p>
		<?php endif; ?>
	</div>
</section>

<?php render_layout_end(); ?>