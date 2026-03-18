<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'user') {
	redirect_to('../admin/dashboard.php');
}

$userId = current_user_id();
$message = '';
$error = '';

$activeStmt = $conn->prepare("SELECT id FROM demandes WHERE user_id = ? AND status = 'accepted' AND date_fin >= CURDATE() LIMIT 1");
if ($activeStmt) {
	$activeStmt->bind_param('i', $userId);
	$activeStmt->execute();
	$activeRes = $activeStmt->get_result();
	$hasActive = $activeRes && $activeRes->num_rows > 0;
	$activeStmt->close();
	if ($hasActive) {
		redirect_to('dashboard.php');
	}
}

if (isset($_POST['submit'])) {
	if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
		$error = 'Session expired. Please refresh and try again.';
	}

	$university = trim((string) ($_POST['university'] ?? ''));
	$enterprise = trim((string) ($_POST['enterprise'] ?? ''));
	$dateDebut = (string) ($_POST['date_debut'] ?? '');
	$dateFin = (string) ($_POST['date_fin'] ?? '');

	if ($error === '' && ($university === '' || $enterprise === '' || $dateDebut === '' || $dateFin === '')) {
		$error = 'All fields are required.';
	} elseif ($error === '' && $dateDebut > $dateFin) {
		$error = 'Start date must be before end date.';
	} elseif ($error === '') {
		$cvPath = isset($_FILES['cv']) ? save_uploaded_file($_FILES['cv'], dirname(__DIR__)) : null;

		$insert = $conn->prepare("INSERT INTO demandes (user_id, university, enterprise, date_debut, date_fin, cv, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
		if ($insert) {
			$insert->bind_param('isssss', $userId, $university, $enterprise, $dateDebut, $dateFin, $cvPath);
			$insert->execute();
			$insert->close();
		}

		$updateUser = $conn->prepare('UPDATE users SET universite = ? WHERE id = ?');
		if ($updateUser) {
			$updateUser->bind_param('si', $university, $userId);
			$updateUser->execute();
			$updateUser->close();
		}

		$message = 'Demande submitted successfully.';
	}
}

render_layout_start('Submit Demande', 'demandes');
?>

<section class="mx-auto max-w-xl rounded-2xl border border-white/10 bg-white/5 p-6">
	<h1 class="text-xl font-bold text-white">Internship Demande</h1>
	<?php if ($error !== ''): ?>
		<p class="mt-3 rounded-xl border border-rose-300/20 bg-rose-400/10 px-3 py-2 text-sm text-rose-100"><?php echo e($error); ?></p>
	<?php endif; ?>
	<?php if ($message !== ''): ?>
		<p class="mt-3 rounded-xl border border-emerald-300/20 bg-emerald-400/10 px-3 py-2 text-sm text-emerald-100"><?php echo e($message); ?></p>
	<?php endif; ?>

	<form method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
		<?php echo csrf_input(); ?>
		<input type="text" name="university" placeholder="University" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
		<input type="text" name="enterprise" placeholder="Enterprise" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
		<div class="grid gap-3 sm:grid-cols-2">
			<input type="date" name="date_debut" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
			<input type="date" name="date_fin" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
		</div>
		<input type="file" name="cv" required class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-500 file:px-3 file:py-2 file:font-semibold file:text-white hover:file:bg-cyan-400">
		<button name="submit" type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-400">Submit</button>
	</form>
</section>

<?php render_layout_end(); ?>