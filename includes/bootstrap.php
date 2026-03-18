<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function redirect_to(string $path): void
{
    header('Location: ' . $path);
    exit();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
    return $sessionToken !== '' && is_string($token) && hash_equals($sessionToken, $token);
}

function require_auth(): void
{
    if (empty($_SESSION['user_id'])) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $isNested = str_contains($script, '/admin/') || str_contains($script, '/user/');
        redirect_to($isNested ? '../login.php' : 'login.php');
    }
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function current_role(): string
{
    return (string) ($_SESSION['role'] ?? 'user');
}

function ensure_uploads_dir(string $projectRoot): void
{
    $uploads = $projectRoot . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploads)) {
        mkdir($uploads, 0775, true);
    }
}

function save_uploaded_file(array $file, string $projectRoot): ?string
{
    if (!isset($file['name']) || $file['name'] === '' || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    ensure_uploads_dir($projectRoot);

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return null;
    }

    $newName = uniqid('upload_', true) . '.' . $ext;
    $relativePath = 'uploads/' . $newName;
    $absolutePath = $projectRoot . DIRECTORY_SEPARATOR . $relativePath;

    if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
        return str_replace('\\', '/', $relativePath);
    }

    return null;
}
