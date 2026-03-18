<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (current_role() !== 'user') {
    redirect_to('../admin/dashboard.php');
}

$userId = current_user_id();
$message = '';

if (isset($_POST['update'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $message = 'Session expired. Please refresh and try again.';
    } else {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $sexe = trim((string) ($_POST['sexe'] ?? ''));
    $nationalite = trim((string) ($_POST['nationalite'] ?? ''));
    $ville = trim((string) ($_POST['ville'] ?? ''));
    $adresse = trim((string) ($_POST['adresse'] ?? ''));
    $telephone = trim((string) ($_POST['telephone'] ?? ''));
    $universite = trim((string) ($_POST['universite'] ?? ''));

    $photo = isset($_FILES['photo']) ? save_uploaded_file($_FILES['photo'], dirname(__DIR__)) : null;

    if ($photo !== null) {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, sexe = ?, nationalite = ?, ville = ?, adresse = ?, telephone = ?, universite = ?, photo = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('sssssssssi', $name, $email, $sexe, $nationalite, $ville, $adresse, $telephone, $universite, $photo, $userId);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, sexe = ?, nationalite = ?, ville = ?, adresse = ?, telephone = ?, universite = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('ssssssssi', $name, $email, $sexe, $nationalite, $ville, $adresse, $telephone, $universite, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    $message = 'Profile updated successfully.';
	}
}

$stmt = $conn->prepare('SELECT name, email, sexe, nationalite, ville, adresse, telephone, universite, photo FROM users WHERE id = ? LIMIT 1');
$user = ['name' => '', 'email' => '', 'sexe' => '', 'nationalite' => '', 'ville' => '', 'adresse' => '', 'telephone' => '', 'universite' => '', 'photo' => ''];
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res ? $res->fetch_assoc() : null;
    if ($data) {
        $user = $data;
    }
    $stmt->close();
}

render_layout_start('Edit Profile', 'profile');
?>

<section class="mx-auto max-w-xl rounded-2xl border border-white/10 bg-white/5 p-6">
    <h1 class="text-xl font-bold text-white">Edit Profile</h1>
    <?php if ($message !== ''): ?>
        <p class="mt-3 rounded-xl border border-emerald-300/20 bg-emerald-400/10 px-3 py-2 text-sm text-emerald-100"><?php echo e($message); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
		<?php echo csrf_input(); ?>
        <div class="text-center">
            <img src="../<?php echo e($user['photo'] ?: 'https://ui-avatars.com/api/?name=' . urlencode((string) ($user['name'] ?? 'User')) . '&background=0f172a&color=e2e8f0'); ?>" class="mx-auto h-24 w-24 rounded-full border border-white/20 object-cover" alt="avatar">
            <input type="file" name="photo" class="mt-3 block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-500 file:px-3 file:py-2 file:font-semibold file:text-white hover:file:bg-cyan-400">
        </div>

        <input type="text" name="name" value="<?php echo e($user['name']); ?>" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
        <input type="email" name="email" value="<?php echo e($user['email']); ?>" required class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">

        <select name="sexe" class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
            <option value="">Sexe</option>
            <option value="Homme" <?php echo ($user['sexe'] === 'Homme') ? 'selected' : ''; ?>>Homme</option>
            <option value="Femme" <?php echo ($user['sexe'] === 'Femme') ? 'selected' : ''; ?>>Femme</option>
        </select>

        <input type="text" name="nationalite" value="<?php echo e($user['nationalite']); ?>" placeholder="Nationalite" class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
        <input type="text" name="ville" value="<?php echo e($user['ville']); ?>" placeholder="Ville" class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
        <textarea name="adresse" rows="3" placeholder="Adresse" class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40"><?php echo e($user['adresse']); ?></textarea>
        <input type="text" name="telephone" value="<?php echo e($user['telephone']); ?>" placeholder="Telephone" class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">
        <input type="text" name="universite" value="<?php echo e($user['universite']); ?>" placeholder="Universite" class="w-full rounded-xl border border-white/15 bg-slate-900/70 px-3 py-2.5 text-white outline-none focus:ring-2 focus:ring-cyan-400/40">

        <button name="update" type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-2.5 font-semibold text-white transition hover:bg-cyan-400">Save Changes</button>
    </form>
</section>

<?php render_layout_end(); ?>