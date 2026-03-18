<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = '';

if (isset($_POST['register'])) {
	if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
		$error = 'Session expired. Please try again.';
	}

	$name = trim((string) ($_POST['name'] ?? ''));
	$email = trim((string) ($_POST['email'] ?? ''));
	$rawPassword = (string) ($_POST['password'] ?? '');
	$role = (string) ($_POST['role'] ?? 'user');

	if ($error === '' && ($name === '' || $email === '' || $rawPassword === '')) {
		$error = 'All fields are required.';
	} elseif ($error === '' && !in_array($role, ['user', 'admin'], true)) {
		$error = 'Invalid role selected.';
	} elseif ($error === '') {
		$check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
		$exists = false;
		if ($check) {
			$check->bind_param('s', $email);
			$check->execute();
			$res = $check->get_result();
			$exists = $res && $res->num_rows > 0;
			$check->close();
		}

		if ($exists) {
			$error = 'This email is already registered.';
		} else {
			$password = password_hash($rawPassword, PASSWORD_DEFAULT);
			$accepted = $role === 'admin' ? 1 : 1;

			$insert = $conn->prepare('INSERT INTO users (name, email, password, role, is_accepted) VALUES (?, ?, ?, ?, ?)');
			if ($insert) {
				$insert->bind_param('ssssi', $name, $email, $password, $role, $accepted);
				$insert->execute();
				$insert->close();
				redirect_to('login.php');
			} else {
				$error = 'Could not create account right now.';
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | StageOps</title>
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
		<h1 class="text-2xl font-extrabold text-white">Create Account</h1>
		<p class="mt-1 text-sm text-slate-300">Register to use StageOps.</p>

		<?php if ($error !== ''): ?>
			<div class="mt-4 rounded-xl border border-rose-300/30 bg-rose-500/15 px-3 py-2 text-sm text-rose-100"><?php echo e($error); ?></div>
		<?php endif; ?>

		<form method="POST" class="mt-5 space-y-4">
			<?php echo csrf_input(); ?>
			<input type="text" name="name" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none transition focus:border-cyan-300/60 focus:ring-2 focus:ring-cyan-400/30" placeholder="Full name">
			<input type="email" name="email" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none transition focus:border-cyan-300/60 focus:ring-2 focus:ring-cyan-400/30" placeholder="Email">
			<input type="password" name="password" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none transition focus:border-cyan-300/60 focus:ring-2 focus:ring-cyan-400/30" placeholder="Password">
			<select name="role" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none transition focus:border-cyan-300/60 focus:ring-2 focus:ring-cyan-400/30">
				<option value="user">Stagiaire</option>
				<option value="admin">Admin</option>
			</select>
			<button name="register" type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-400">Register</button>
		</form>

		<p class="mt-4 text-sm text-slate-300">Already registered? <a href="login.php" class="font-semibold text-cyan-300 hover:text-cyan-200">Login</a></p>
	</section>
</main>
</body>
</html>