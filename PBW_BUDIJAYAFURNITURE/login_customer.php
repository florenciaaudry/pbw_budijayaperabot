<?php
require_once "auth_customer.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = strtolower(trim($_POST["email"] ?? ""));
  $password = $_POST["password"] ?? "";

  $users = load_users();
  $found = null;
  foreach ($users as $u) {
    if (($u["email"] ?? "") === $email) { $found = $u; break; }
  }

  if (!$found || !password_verify($password, $found["password_hash"] ?? "")) {
    $error = "Email atau password salah.";
  } else {
    // simpan session customer (tanpa password)
    $_SESSION["customer"] = [
      "id" => $found["id"],
      "name" => $found["name"],
      "email" => $found["email"],
    ];
    header("Location: profile.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Customer</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="auth.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-card">
      <h2 class="auth-title">Login Customer</h2>
      <p class="auth-subtitle">Masuk untuk lanjut belanja.</p>

      <?php if ($error): ?><div class="auth-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

      <form method="POST" class="auth-form">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn-primary full-btn">Login</button>
      </form>

      <div class="auth-footer">
        Belum punya akun? <a href="register.php">Daftar</a><br>
        <a href="index.php">← Kembali ke Landing</a>
      </div>
    </div>
  </div>
</body>
</html>