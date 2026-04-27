<?php
require_once __DIR__ . '/admin_auth.php';

if (admin_is_logged_in()) {
    header('Location: admin_panel.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (admin_login($user, $pass)) {
        header('Location: admin_panel.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Budi Jaya Furniture</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="auth.css">
  <style>
    .auth-logo { background: #1C1C1A; } /* Warna beda untuk admin */
  </style>
</head>
<body>
<div class="auth-container">
  <div class="auth-card">
    <div class="auth-logo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 15V17M12 7V11M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke-linecap="round"/>
      </svg>
    </div>
    <h2 class="auth-title">Admin Panel</h2>
    <p class="auth-subtitle">Masuk untuk mengelola toko.</p>

    <?php if ($error): ?>
      <div class="auth-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn-primary full-btn">Masuk ke Panel</button>
    </form>

    <div class="auth-footer">
      <a href="index.php">← Kembali ke Toko</a>
    </div>
  </div>
</div>
</body>
</html>