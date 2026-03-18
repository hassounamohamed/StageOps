<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StageOps - Stage Management System</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
	theme: {
		extend: {
			fontFamily: {
				sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui'],
			},
			keyframes: {
				fadeInUp: {
					'0%': { opacity: '0', transform: 'translateY(18px)' },
					'100%': { opacity: '1', transform: 'translateY(0)' },
				},
			},
			animation: {
				fadeInUp: 'fadeInUp .7s ease-out both',
			},
		},
	},
};
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100">
<div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,#0ea5e9_0%,transparent_35%),radial-gradient(circle_at_80%_0%,#10b981_0%,transparent_30%),linear-gradient(160deg,#020617,#0f172a_50%,#111827)]"></div>

<main class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-12 sm:px-6">
	<section class="grid w-full gap-8 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30 backdrop-blur-xl md:grid-cols-2 md:p-10">
		<div class="space-y-4 animate-fadeInUp">
			<p class="inline-flex rounded-full bg-cyan-400/15 px-3 py-1 text-xs font-semibold text-cyan-200 ring-1 ring-cyan-300/20">StageOps Platform</p>
			<h1 class="text-3xl font-extrabold leading-tight text-white sm:text-4xl">Manage Internship Requests, Tasks, and Team Workflow in One Place.</h1>
			<p class="text-slate-300">A cleaner and faster stage management experience for admins and trainees with secure authentication and role-based access.</p>
		</div>

		<div class="animate-fadeInUp rounded-2xl border border-white/10 bg-slate-900/50 p-6 shadow-xl">
			<h2 class="mb-5 text-xl font-bold text-white">Get Started</h2>
			<div class="space-y-3">
				<a href="login.php" class="block rounded-xl bg-cyan-500 px-4 py-3 text-center font-semibold text-white transition hover:bg-cyan-400">Login</a>
				<a href="register.php" class="block rounded-xl bg-white/10 px-4 py-3 text-center font-semibold text-slate-100 transition hover:bg-white/20">Create Account</a>
			</div>
		</div>
	</section>
</main>
</body>
</html>