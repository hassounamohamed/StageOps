<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'user') {
	redirect_to('../admin/dashboard.php');
}

$userId = current_user_id();

$userStmt = $conn->prepare('SELECT name, email, photo, sexe, nationalite, ville, adresse, telephone, universite FROM users WHERE id = ? LIMIT 1');
$user = null;
if ($userStmt) {
	$userStmt->bind_param('i', $userId);
	$userStmt->execute();
	$res = $userStmt->get_result();
	$user = $res ? $res->fetch_assoc() : null;
	$userStmt->close();
}

$demandeStmt = $conn->prepare('SELECT university FROM demandes WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$demande = null;
if ($demandeStmt) {
	$demandeStmt->bind_param('i', $userId);
	$demandeStmt->execute();
	$res = $demandeStmt->get_result();
	$demande = $res ? $res->fetch_assoc() : null;
	$demandeStmt->close();
}

$photo = $user['photo'] ?? '';
$universite = $demande['university'] ?? ($user['universite'] ?? '');

render_layout_start('My Profile', 'profile');
?>

<section class="mx-auto max-w-2xl rounded-2xl border border-white/10 bg-white/5 p-6">
	<div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:text-left">
		<img src="../<?php echo e($photo ?: 'https://ui-avatars.com/api/?name=' . urlencode((string) ($user['name'] ?? 'User')) . '&background=0f172a&color=e2e8f0'); ?>" alt="profile" class="h-28 w-28 rounded-full border border-white/20 object-cover">
		<div>
			<h1 class="text-2xl font-bold text-white"><?php echo e($user['name'] ?? 'User'); ?></h1>
			<p class="text-slate-300"><?php echo e($user['email'] ?? ''); ?></p>
		</div>
	</div>

	<div class="mt-6 grid gap-3 rounded-xl border border-white/10 bg-slate-900/40 p-4 text-sm text-slate-200 sm:grid-cols-2">
		<p>Sexe: <?php echo e($user['sexe'] ?? '-'); ?></p>
		<p>Nationalite: <?php echo e($user['nationalite'] ?? '-'); ?></p>
		<p>Ville: <?php echo e($user['ville'] ?? '-'); ?></p>
		<p>Telephone: <?php echo e($user['telephone'] ?? '-'); ?></p>
		<p class="sm:col-span-2">Adresse: <?php echo e($user['adresse'] ?? '-'); ?></p>
		<p class="sm:col-span-2">Universite: <?php echo e($universite ?: '-'); ?></p>
	</div>

	<a href="edit_profile.php" class="mt-5 inline-flex rounded-xl bg-cyan-500 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-400">Edit Profile</a>
</section>

<?php render_layout_end(); ?>