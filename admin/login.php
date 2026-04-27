<?php
require_once __DIR__ . '/auth_admin.php';

if (admin_is_logged_in()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $locked   = admin_login_locked_seconds($username);
    if ($locked > 0) {
        $mnt   = ceil($locked / 60);
        $error = "Terlalu banyak percobaan. Coba lagi dalam {$mnt} menit.";
    } else {
        $ok = admin_login($username, $_POST['password'] ?? '');
        if (!$ok) {
            $attempts = $_SESSION['login_attempts']['admin_' . md5(strtolower($username))] ?? 0;
            $sisa     = max(0, 5 - $attempts);
            $error    = 'Username atau password salah.' . ($sisa > 0 ? " Sisa percobaan: $sisa." : ' Akun terkunci 15 menit.');
        } else {
            header('Location: index.php'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login – Budi Jaya Furniture</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--sand:#F7F4EF;--cream:#FDFBF8;--charcoal:#1C1C1A;--accent:#A34E22;--border:#E4E0D8;--muted:#767370;--error:#C0392B;--font-body:'DM Sans',sans-serif;--font-display:'DM Serif Display',serif}
    body{font-family:var(--font-body);background:var(--charcoal);min-height:100vh;display:flex;align-items:center;justify-content:center;background-image:radial-gradient(ellipse at 20% 50%,rgba(163,78,34,.15) 0%,transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(163,78,34,.08) 0%,transparent 50%)}
    .login-card{background:var(--cream);border-radius:20px;padding:48px 44px;width:100%;max-width:420px;box-shadow:0 32px 80px rgba(0,0,0,.4)}
    .login-logo{width:48px;height:48px;background:var(--accent);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:24px}
    .login-logo svg{width:26px;height:26px;stroke:#fff;fill:none;stroke-width:2}
    .login-title{font-family:var(--font-display);font-size:26px;color:var(--charcoal);margin-bottom:4px}
    .login-sub{font-size:13px;color:var(--muted);margin-bottom:28px}
    .form-group{margin-bottom:18px}
    label{display:block;font-size:12px;font-weight:600;color:var(--charcoal);letter-spacing:.06em;text-transform:uppercase;margin-bottom:7px}
    input{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;font-family:var(--font-body);background:var(--cream);color:var(--charcoal);outline:none;transition:border-color .15s}
    input:focus{border-color:var(--accent)}
    .error-msg{background:#fdf2f2;color:var(--error);border:1px solid #f5c6c0;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:18px}
    .btn-submit{width:100%;padding:13px;background:var(--charcoal);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:var(--font-body);transition:background .15s}
    .btn-submit:hover{background:#2E2E2B}
    .login-back{margin-top:20px;text-align:center;font-size:13px;color:var(--muted)}
    .login-back a{color:var(--accent);font-weight:600;text-decoration:none}
  </style>
</head>
<body>
<div class="login-card">
  <div class="login-logo">
    <svg viewBox="0 0 24 24"><path d="M3 12l9-8 9 8v8a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2v-8z"/></svg>
  </div>
  <h1 class="login-title">Panel Admin</h1>
  <p class="login-sub">Budi Jaya Furniture — Akses terbatas</p>

  <?php if ($error): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" required autocomplete="username"
             value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn-submit">Masuk ke Panel Admin</button>
  </form>
  <div class="login-back"><a href="../index.php">← Kembali ke toko</a></div>
</div>
</body>
</html>
