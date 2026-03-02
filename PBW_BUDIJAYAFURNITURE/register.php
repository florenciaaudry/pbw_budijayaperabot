<?php
require_once "auth_customer.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST["name"] ?? "");
  $email = strtolower(trim($_POST["email"] ?? ""));
  $password = $_POST["password"] ?? "";

  if ($name === "" || $email === "" || $password === "") {
    $error = "Semua field wajib diisi.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Email tidak valid.";
  } elseif (strlen($password) < 6) {
    $error = "Password minimal 6 karakter.";
  } else {
    $users = load_users();
    foreach ($users as $u) {
      if (($u["email"] ?? "") === $email) {
        $error = "Email sudah terdaftar. Silakan login.";
        break;
      }
    }

    if ($error === "") {
      $users[] = [
        "id" => uniqid("u_", true),
        "name" => $name,
        "email" => $email,
        "password_hash" => password_hash($password, PASSWORD_DEFAULT),
        "created_at" => date("c"),
      ];
      save_users($users);
      $success = "Daftar berhasil. Silakan login.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Customer</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="auth.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-card">
      <h2 class="auth-title">Daftar Customer</h2>
      <p class="auth-subtitle">Buat akun untuk memudahkan pemesanan.</p>

      <?php if ($error): ?><div class="auth-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?>
        <div class="auth-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" class="auth-form">
        <div class="form-group">
          <label>Nama</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($_POST["name"] ?? "") ?>">
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
          <small style="opacity:.7">Minimal 6 karakter.</small>
        </div>

        <button type="submit" class="btn-primary full-btn">Daftar</button>
      </form>

      <div class="auth-footer">
        Sudah punya akun? <a href="login_customer.php">Login</a><br>
        <a href="index.php">← Kembali ke Landing</a>
      </div>
    </div>
  </div>
</body>
</html>