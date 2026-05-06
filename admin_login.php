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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="auth.css">
  <style>
    .auth-card { border-top: 3px solid #1C1C1A; }
    .btn-primary { background: #A34E22; }
    .btn-primary:hover { background: #8B3E18; }
    .auth-form input:focus { border-color: #1C1C1A; box-shadow: 0 0 0 3px rgba(28,28,26,.08); }
    .admin-badge {
      display: inline-block;
      background: #1C1C1A;
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 20px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
<div class="auth-container">
  <div class="auth-card">

    <div class="auth-logo-wrap">
      <img src="gambar/logobudijaya.png" alt="Perabot Budi Jaya" class="auth-logo-img">
    </div>

    <div style="text-align:center;">
      <span class="admin-badge">Admin Area</span>
    </div>

    <h2 class="auth-title">Admin Login</h2>
    <p class="auth-subtitle">Masuk untuk melanjutkan ke akunmu.</p>

    <?php if ($error): ?>
      <div class="auth-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required autofocus autocomplete="username">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-primary full-btn">Masuk ke Panel</button>
    </form>

    <div class="auth-footer">
      <a href="index.php">← Kembali ke Toko</a>
      <span style="margin: 0 10px; color: #E4E0D8;">|</span>
      <a href="login_customer.php" style="color:#767370; font-weight:500;">Login Customer</a>
    </div>

  </div>
</div>
</body>
</html>
