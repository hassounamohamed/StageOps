<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'stage_db';
$sqlFile = __DIR__ . '/stage_db.sql';

$messages = [];
$errors = [];

$conn = new mysqli($host, $user, $password);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$createDbSql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($createDbSql) !== true) {
    die('Could not create database: ' . $conn->error);
}

$conn->select_db($database);
$conn->set_charset('utf8mb4');
$messages[] = 'Database ready: ' . $database;

if (!file_exists($sqlFile)) {
    $errors[] = 'Missing stage_db.sql in project root.';
} else {
    $schemaSql = file_get_contents($sqlFile);
    if ($schemaSql === false) {
        $errors[] = 'Could not read stage_db.sql.';
    } elseif ($conn->multi_query($schemaSql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());

        if ($conn->errno) {
            $errors[] = 'Schema execution warning: ' . $conn->error;
        } else {
            $messages[] = 'Schema applied successfully.';
        }
    } else {
        $errors[] = 'Schema execution failed: ' . $conn->error;
    }
}

$seedStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$insertStmt = $conn->prepare('INSERT INTO users (name, email, password, role, is_accepted) VALUES (?, ?, ?, ?, 1)');

if (!$seedStmt || !$insertStmt) {
    $errors[] = 'Could not prepare seed statements.';
} else {
    $accounts = [
        ['Admin', 'admin@stageops.local', 'admin123', 'admin'],
        ['User', 'user@stageops.local', 'user123', 'user'],
    ];

    foreach ($accounts as $acc) {
        [$name, $email, $plainPassword, $role] = $acc;

        $seedStmt->bind_param('s', $email);
        $seedStmt->execute();
        $checkResult = $seedStmt->get_result();
        $exists = $checkResult && $checkResult->num_rows > 0;

        if (!$exists) {
            $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
            $insertStmt->bind_param('ssss', $name, $email, $hash, $role);
            if ($insertStmt->execute()) {
                $messages[] = 'Seed account created: ' . $email;
            } else {
                $errors[] = 'Could not create account ' . $email . ': ' . $insertStmt->error;
            }
        }
    }
}

if ($seedStmt) {
    $seedStmt->close();
}
if ($insertStmt) {
    $insertStmt->close();
}

$tableCount = 0;
$countResult = $conn->query("SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = '$database'");
if ($countResult) {
    $row = $countResult->fetch_assoc();
    $tableCount = (int) ($row['table_count'] ?? 0);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StageOps Setup</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<main class="mx-auto max-w-3xl px-4 py-10">
    <section class="rounded-2xl border border-white/10 bg-white/5 p-6">
        <h1 class="text-2xl font-bold">StageOps Database Setup</h1>
        <p class="mt-2 text-sm text-slate-300">Executed at <?php echo htmlspecialchars(date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8'); ?></p>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-white/10 bg-slate-900/50 p-4">
                <p class="text-sm text-slate-400">Database</p>
                <p class="mt-1 text-lg font-semibold"><?php echo htmlspecialchars($database, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="rounded-xl border border-white/10 bg-slate-900/50 p-4">
                <p class="text-sm text-slate-400">Tables</p>
                <p class="mt-1 text-lg font-semibold"><?php echo htmlspecialchars((string) $tableCount, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>

        <?php if (!empty($messages)): ?>
            <div class="mt-5 rounded-xl border border-emerald-300/20 bg-emerald-400/10 p-4">
                <h2 class="font-semibold text-emerald-100">Completed</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-emerald-100/90">
                    <?php foreach ($messages as $line): ?>
                        <li><?php echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mt-5 rounded-xl border border-rose-300/20 bg-rose-400/10 p-4">
                <h2 class="font-semibold text-rose-100">Warnings</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-100/90">
                    <?php foreach ($errors as $line): ?>
                        <li><?php echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mt-6 rounded-xl border border-cyan-300/20 bg-cyan-400/10 p-4 text-sm text-cyan-100">
            <p>Admin: admin@stageops.local / admin123</p>
            <p>User: user@stageops.local / user123</p>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="index.php" class="rounded-lg bg-cyan-500 px-4 py-2 font-semibold text-white hover:bg-cyan-400">Open Home</a>
            <a href="login.php" class="rounded-lg bg-white/10 px-4 py-2 font-semibold text-slate-100 hover:bg-white/20">Open Login</a>
            <a href="register.php" class="rounded-lg bg-white/10 px-4 py-2 font-semibold text-slate-100 hover:bg-white/20">Open Register</a>
        </div>
    </section>
</main>
</body>
</html>
