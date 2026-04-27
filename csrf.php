<?php
// ============================================================
//  csrf.php — CSRF token helper
//  Include di setiap halaman yang butuh form protection
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Permintaan tidak valid (CSRF). Silakan refresh halaman.');
    }
}
