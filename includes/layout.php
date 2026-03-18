<?php
require_once __DIR__ . '/bootstrap.php';

function render_layout_start(string $title, string $active): void
{
    global $conn;

    $role = current_role();
    $userId = current_user_id();

    $unread = 0;
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0');
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $unread = (int) ($row['total'] ?? 0);
        $stmt->close();
    }

    $base = $role === 'admin' ? '../admin/' : '../user/';
    $demandLink = $role === 'admin' ? '../admin/demandes.php' : '../user/demande.php';

    $nav = [
        'dashboard' => $base . 'dashboard.php',
        'tasks' => $base . 'tasks.php',
        'profile' => $base . 'profile.php',
        'demandes' => $demandLink,
        'notifications' => $base . 'notifications.php',
    ];

    $labels = [
        'dashboard' => 'Dashboard',
        'tasks' => 'Tasks',
        'profile' => 'Profile',
        'demandes' => $role === 'admin' ? 'Demandes' : 'My Demande',
        'notifications' => 'Notifications',
    ];

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . e($title) . ' | StageOps</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script>';
    echo '<script>tailwind.config={theme:{extend:{fontFamily:{sans:["Plus Jakarta Sans","ui-sans-serif","system-ui"]},keyframes:{fadeInUp:{"0%":{opacity:"0",transform:"translateY(16px)"},"100%":{opacity:"1",transform:"translateY(0)"}}},animation:{fadeInUp:"fadeInUp .6s ease-out both"}}}}</script>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">';
    echo '</head>';
    echo '<body class="min-h-screen bg-slate-950 text-slate-100 font-sans">';
    echo '<div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_15%_20%,#0ea5e9_0%,transparent_35%),radial-gradient(circle_at_80%_0%,#14b8a6_0%,transparent_30%),linear-gradient(145deg,#020617,#0f172a_45%,#111827)]"></div>';
    echo '<header class="sticky top-0 z-30 border-b border-white/10 bg-slate-950/60 backdrop-blur-xl">';
    echo '<div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-3 px-4 py-3 sm:px-6">';
    echo '<a href="' . e($nav['dashboard']) . '" class="text-lg font-extrabold tracking-tight text-cyan-300">StageOps</a>';
    echo '<nav class="flex flex-wrap items-center gap-2 text-sm">';

    foreach ($nav as $key => $href) {
        $isActive = $active === $key;
        $baseClasses = 'rounded-lg px-3 py-2 transition';
        $activeClasses = $isActive
            ? ' bg-cyan-400/20 text-cyan-200 ring-1 ring-cyan-300/40'
            : ' text-slate-200 hover:bg-white/10 hover:text-white';

        $label = $labels[$key];
        if ($key === 'notifications' && $unread > 0) {
            $label .= ' (' . $unread . ')';
        }

        echo '<a href="' . e($href) . '" class="' . $baseClasses . $activeClasses . '">' . e($label) . '</a>';
    }

    echo '<a href="../logout.php" class="rounded-lg bg-rose-500/20 px-3 py-2 text-rose-200 transition hover:bg-rose-500/30">Logout</a>';
    echo '</nav>';
    echo '</div>';
    echo '</header>';
    echo '<main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6">';
}

function render_layout_end(): void
{
    echo '</main>';
    echo '<footer class="mx-auto w-full max-w-6xl px-4 pb-8 pt-2 text-sm text-slate-400 sm:px-6">';
    echo '<p>StageOps Stage Management System © ' . date('Y') . '</p>';
    echo '</footer>';
    echo '</body>';
    echo '</html>';
}
