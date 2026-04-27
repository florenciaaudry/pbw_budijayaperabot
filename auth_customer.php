<?php
// ============================================================
//  auth_customer.php — autentikasi customer
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

function customer_is_logged_in(): bool {
    return isset($_SESSION['customer']) && is_array($_SESSION['customer']);
}

function require_customer_login(): void {
    if (!customer_is_logged_in()) {
        $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
        header('Location: login_customer.php'); exit;
    }
}

function customer_get(): ?array {
    return $_SESSION['customer'] ?? null;
}

// ── Rate limiting login ─────────────────────────────────────
function login_check_rate(string $key): bool {
    // true = boleh lanjut, false = terkunci
    $attempts = $_SESSION['login_attempts'][$key] ?? 0;
    $locked_until = $_SESSION['login_locked'][$key] ?? 0;
    if ($locked_until > time()) return false;
    if ($locked_until && $locked_until <= time()) {
        // Reset setelah waktu lock habis
        unset($_SESSION['login_attempts'][$key], $_SESSION['login_locked'][$key]);
    }
    return true;
}

function login_fail(string $key): void {
    $_SESSION['login_attempts'][$key] = ($_SESSION['login_attempts'][$key] ?? 0) + 1;
    if ($_SESSION['login_attempts'][$key] >= 5) {
        $_SESSION['login_locked'][$key] = time() + 900; // 15 menit
    }
}

function login_success(string $key): void {
    unset($_SESSION['login_attempts'][$key], $_SESSION['login_locked'][$key]);
}

function login_locked_seconds(string $key): int {
    $until = $_SESSION['login_locked'][$key] ?? 0;
    return max(0, $until - time());
}

// ── Login ───────────────────────────────────────────────────
function customer_login_by_credentials(string $email, string $password): array|false {
    $key = 'cust_' . md5(strtolower(trim($email)));
    if (!login_check_rate($key)) return false;

    $pdo  = db();
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        login_fail($key);
        return false;
    }

    login_success($key);
    return $user;
}

// ── Register ────────────────────────────────────────────────
function customer_register(string $name, string $email, string $password): string {
    $pdo   = db();
    $email = strtolower(trim($email));

    $check = $pdo->prepare('SELECT id FROM customers WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) return 'Email sudah terdaftar. Silakan login.';

    $pdo->prepare('INSERT INTO customers (name, email, password_hash) VALUES (?, ?, ?)')
        ->execute([trim($name), $email, password_hash($password, PASSWORD_DEFAULT)]);
    return '';
}

// ── Set session ─────────────────────────────────────────────
function customer_set_session(array $user): void {
    session_regenerate_id(true); // Cegah session fixation
    $_SESSION['customer'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
    ];
}

// ── Orders ──────────────────────────────────────────────────
function customer_get_orders(): array {
    if (!customer_is_logged_in()) return [];
    $pdo  = db();
    $stmt = $pdo->prepare(
        'SELECT o.*,
            GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ", ") AS items_summary
         FROM orders o
         LEFT JOIN order_items oi ON oi.order_id = o.id
         WHERE o.customer_id = ?
         GROUP BY o.id
         ORDER BY o.created_at DESC'
    );
    $stmt->execute([$_SESSION['customer']['id']]);
    return $stmt->fetchAll();
}
