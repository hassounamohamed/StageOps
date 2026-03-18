<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = '';

if (isset($_POST['login'])) {
	if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
		$error = 'Session expired. Please try again.';
	}

	$email = trim((string) ($_POST['email'] ?? ''));
	$password = (string) ($_POST['password'] ?? '');

	if ($error === '' && ($email === '' || $password === '')) {
		$error = 'Please provide both email and password.';
	} elseif ($error === '') {
		$stmt = $conn->prepare('SELECT id, role, password, is_accepted FROM users WHERE email = ? LIMIT 1');
		if ($stmt) {
			$stmt->bind_param('s', $email);
			$stmt->execute();
			$result = $stmt->get_result();
			$user = $result ? $result->fetch_assoc() : null;
			$stmt->close();

			if (!$user || !password_verify($password, (string) $user['password'])) {
				$error = 'Invalid credentials.';
			} elseif ((int) $user['is_accepted'] === 0) {
				$error = 'Your account is pending approval.';
			} else {
				$_SESSION['user_id'] = (int) $user['id'];
				$_SESSION['role'] = (string) $user['role'];

				if ($user['role'] === 'admin') {
					redirect_to('admin/dashboard.php');
				}

				$uid = (int) $user['id'];
				$check = $conn->prepare("SELECT id FROM demandes WHERE user_id = ? AND date_fin >= CURDATE() AND status = 'accepted' LIMIT 1");
				if ($check) {
					$check->bind_param('i', $uid);
					$check->execute();
					$activeResult = $check->get_result();
					$hasActive = $activeResult && $activeResult->num_rows > 0;
					$check->close();
					redirect_to($hasActive ? 'user/dashboard.php' : 'user/demande.php');
				}

				redirect_to('user/demande.php');
			}
		} else {
			$error = 'Unexpected database error.';
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | StageOps</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui}</style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,#0ea5e9_0%,transparent_35%),radial-gradient(circle_at_80%_0%,#14b8a6_0%,transparent_30%),linear-gradient(145deg,#020617,#0f172a_45%,#111827)]"></div>

<main class="mx-auto flex min-h-screen w-full max-w-md items-center px-4 py-10">
	<section class="w-full rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-8">
		<h1 class="text-2xl font-extrabold text-white">Login</h1>
		<p class="mt-1 text-sm text-slate-300">Access your StageOps account.</p>

		<?php if ($error !== ''): ?>
			<div class="mt-4 rounded-xl border border-rose-300/30 bg-rose-500/15 px-3 py-2 text-sm text-rose-100"><?php echo e($error); ?></div>
		<?php endif; ?>

		<form method="POST" class="mt-5 space-y-4">
			<?php echo csrf_input(); ?>
			<div>
				<label class="mb-1 block text-sm text-slate-200">Email</label>
				<input type="email" name="email" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none transition focus:border-cyan-300/60 focus:ring-2 focus:ring-cyan-400/30" placeholder="you@example.com">
			</div>
			<div>
				<label class="mb-1 block text-sm text-slate-200">Password</label>
				<input type="password" name="password" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none transition focus:border-cyan-300/60 focus:ring-2 focus:ring-cyan-400/30" placeholder="Your password">
			</div>
			<button name="login" type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-400">Login</button>
		</form>

		<p class="mt-4 text-sm text-slate-300">No account? <a href="register.php" class="font-semibold text-cyan-300 hover:text-cyan-200">Register</a></p>
	</section>
</main>
</body>
</html>