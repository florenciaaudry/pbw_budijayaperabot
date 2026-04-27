<?php
require_once 'auth_customer.php';

if (customer_is_logged_in()) { header('Location: profile.php'); exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $error = customer_register($name, $email, $password);
        if ($error === '') $success = 'Pendaftaran berhasil! Silakan login.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar – Budi Jaya Furniture</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="auth.css">
</head>
<body>
<div class="auth-container">
  <div class="auth-card">

    <div class="auth-logo-wrap">
      <img src="gambar/logobudijaya.png" alt="Perabot Budi Jaya" class="auth-logo-img">
    </div>

    <h2 class="auth-title">Buat Akun Baru</h2>
    <p class="auth-subtitle">Masuk untuk melanjutkan ke akunmu.</p>

    <?php if ($error): ?>
      <div class="auth-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="auth-success"><?= htmlspecialchars($success) ?>
        <a href="login_customer.php" style="font-weight:700;margin-left:4px;">Login →</a>
      </div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="name" required autocomplete="name"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required autocomplete="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required autocomplete="new-password">
        <small style="display:block;margin-top:5px;font-size:12px;color:#767370;">Minimal 6 karakter.</small>
      </div>
      <button type="submit" class="btn-primary full-btn">Daftar Sekarang</button>
    </form>

    <div class="auth-footer">
      Sudah punya akun? <a href="login_customer.php">Login</a>
      <div class="auth-divider"></div>
      <a href="index.php">← Kembali ke toko</a>
    </div>

  </div>
</div>
</body>
</html>
