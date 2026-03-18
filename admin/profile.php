<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'admin') {
    redirect_to('../user/demande.php');
}

$adminId = current_user_id();
$message = '';

if (isset($_POST['update'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $message = 'Session expired. Please refresh and try again.';
    } else {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    $photo = null;
    if (isset($_FILES['photo'])) {
        $photo = save_uploaded_file($_FILES['photo'], dirname(__DIR__));
    }

    if ($photo !== null) {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, photo = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('sssi', $name, $email, $photo, $adminId);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('ssi', $name, $email, $adminId);
            $stmt->execute();
            $stmt->close();
        }
    }

    $message = 'Profile updated.';
	}
}

$infoStmt = $conn->prepare('SELECT name, email, role, photo FROM users WHERE id = ? LIMIT 1');
$admin = ['name' => '', 'email' => '', 'role' => 'admin', 'photo' => ''];
if ($infoStmt) {
    $infoStmt->bind_param('i', $adminId);
    $infoStmt->execute();
    $res = $infoStmt->get_result();
    $data = $res ? $res->fetch_assoc() : null;
    if ($data) {
        $admin = $data;
    }
    $infoStmt->close();
}

render_layout_start('Admin Profile', 'profile');
?>

<section class="mx-auto max-w-xl rounded-2xl border border-white/10 bg-white/5 p-6">
    <h1 class="text-xl font-bold text-white">My Profile</h1>
    <?php if ($message !== ''): ?>
        <p class="mt-3 rounded-xl border border-emerald-300/20 bg-emerald-400/10 px-3 py-2 text-sm text-emerald-100"><?php echo e($message); ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
		<?php echo csrf_input(); ?>
        <div class="text-center">
            <img src="../<?php echo e($admin['photo'] ?: 'https://ui-avatars.com/api/?name=' . urlencode((string) ($admin['name'] ?? 'Admin')) . '&background=0f172a&color=e2e8f0'); ?>" class="mx-auto h-24 w-24 rounded-full border border-white/20 object-cover" alt="avatar">
            <input type="file" name="photo" class="mt-3 block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-500 file:px-3 file:py-2 file:font-semibold file:text-white hover:file:bg-cyan-400">
        </div>
        <input type="text" name="name" value="<?php echo e($admin['name']); ?>" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
        <input type="email" name="email" value="<?php echo e($admin['email']); ?>" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
        <input type="text" value="<?php echo e(ucfirst((string) $admin['role'])); ?>" readonly class="w-full rounded-xl border border-white/10 bg-slate-800/80 px-3 py-2.5 text-slate-300">
        <button name="update" type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-400">Update Profile</button>
    </form>
</section>

<?php render_layout_end(); ?>