<?php
require_once 'auth_customer.php';

if (customer_is_logged_in()) { header('Location: profile.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $key     = 'cust_' . md5(strtolower($email));
    $locked  = login_locked_seconds($key);
    if ($locked > 0) {
        $mnt = ceil($locked / 60);
        $error = "Terlalu banyak percobaan. Coba lagi dalam {$mnt} menit.";
    } elseif (!$email || !$password) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $user = customer_login_by_credentials($email, $password);
        if (!$user) {
            $attempts = $_SESSION['login_attempts']['cust_' . md5(strtolower($email))] ?? 0;
            $sisa     = max(0, 5 - $attempts);
            $error    = 'Email atau password salah.' . ($sisa > 0 ? " Sisa percobaan: $sisa." : ' Akun terkunci 15 menit.');
        } else {
            customer_set_session($user);
            $redirect = $_SESSION['login_redirect'] ?? 'profile.php';
            unset($_SESSION['login_redirect']);
            header('Location: ' . $redirect); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – Budi Jaya Furniture</title>
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

    <h2 class="auth-title">Welcome Back</h2>
    <p class="auth-subtitle">Masuk untuk melanjutkan ke akunmu.</p>

    <?php if ($error): ?>
      <div class="auth-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required autocomplete="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-primary full-btn">Login</button>
    </form>

    <div class="auth-footer">
      Belum punya akun? <a href="register.php">Daftar sekarang</a>
      <div class="auth-divider"></div>
      <a href="index.php">← Kembali ke toko</a>
      <span style="margin: 0 10px; color: #E4E0D8;">|</span>
      <a href="admin_login.php" style="color: #767370; font-weight: 500;">Masuk sebagai Admin</a>
    </div>

  </div>
</div>
</body>
</html>
