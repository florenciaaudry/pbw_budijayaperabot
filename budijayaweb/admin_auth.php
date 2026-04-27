<?php
// ============================================================
//  admin_auth.php — Proteksi halaman admin
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

function admin_is_logged_in(): bool {
    return isset($_SESSION['admin']) && is_array($_SESSION['admin']);
}

function require_admin_login(): void {
    if (!admin_is_logged_in()) {
        header('Location: admin_login.php');
        exit;
    }
}

function admin_login(string $username, string $password): bool {
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id'       => $admin['id'],
            'username' => $admin['username']
        ];
        return true;
    }
    return false;
}

function admin_logout(): void {
    unset($_SESSION['admin']);
}
