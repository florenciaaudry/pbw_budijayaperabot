<?php
// ============================================================
//  admin/auth_admin.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../csrf.php';

function admin_is_logged_in(): bool {
    return isset($_SESSION['admin']) && is_array($_SESSION['admin']);
}

function require_admin_login(): void {
    if (!admin_is_logged_in()) {
        header('Location: login.php'); exit;
    }
}

function admin_login(string $username, string $password): bool {
    $key = 'admin_' . md5(strtolower(trim($username)));

    // Rate limit check
    $locked_until = $_SESSION['login_locked'][$key] ?? 0;
    if ($locked_until > time()) return false;
    if ($locked_until && $locked_until <= time()) {
        unset($_SESSION['login_attempts'][$key], $_SESSION['login_locked'][$key]);
    }

    $pdo  = db();
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([trim($username)]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        // Record failed attempt
        $_SESSION['login_attempts'][$key] = ($_SESSION['login_attempts'][$key] ?? 0) + 1;
        if ($_SESSION['login_attempts'][$key] >= 5) {
            $_SESSION['login_locked'][$key] = time() + 900;
        }
        return false;
    }

    // Success — reset & regenerate
    unset($_SESSION['login_attempts'][$key], $_SESSION['login_locked'][$key]);
    session_regenerate_id(true);
    $_SESSION['admin'] = ['id' => $admin['id'], 'username' => $admin['username']];
    return true;
}

function admin_login_locked_seconds(string $username): int {
    $key   = 'admin_' . md5(strtolower(trim($username)));
    $until = $_SESSION['login_locked'][$key] ?? 0;
    return max(0, $until - time());
}
