<?php
require_once "auth_customer.php";

if (!customer_is_logged_in()) {
  header("Location: login_customer.php");
  exit;
}

$user = $_SESSION["customer"];
$firstName = explode(" ", trim($user["name"]))[0];
$initial = strtoupper(substr(trim($user["name"]), 0, 1));
?>

<?php include "header.php"; ?>

<section class="section">
  <div class="container">

    <div class="profile-card">

      <!-- HEADER -->
      <div class="profile-header">
        <div class="profile-avatar"><?= htmlspecialchars($initial) ?></div>

        <div>
          <h2>Halo, <?= htmlspecialchars($firstName) ?> 👋</h2>
          <p class="profile-muted"><?= htmlspecialchars($user["email"]) ?></p>
        </div>
      </div>

      <!-- GRID INFO -->
      <div class="profile-grid">

        <div class="profile-box">
          <h3>Data Akun</h3>
          <p><b>Nama:</b> <?= htmlspecialchars($user["name"]) ?></p>
          <p><b>Email:</b> <?= htmlspecialchars($user["email"]) ?></p>
        </div>

        <div class="profile-box">
          <h3>Alamat Pengiriman</h3>
          <p>Belum ditambahkan.</p>
          <button class="btn outline">Tambah Alamat</button>
        </div>

        <div class="profile-box">
          <h3>Ringkasan Belanja</h3>
          <p>Item di Keranjang: <b id="cartTotalItem">0</b></p>
          <p>Produk Favorit: <b id="favTotalItem">0</b></p>
        </div>

        <div class="profile-box">
          <h3>Aksi Cepat</h3>
          <div class="profile-actions">
            <a href="index.php#produk" class="btn outline">Lanjut Belanja</a>
            <button id="goCart" class="btn primary">Buka Keranjang</button>
          </div>
        </div>

      </div>

      <div style="margin-top:20px;">
        <a href="logout_customer.php" class="btn outline" style="color:#b91c1c;border-color:#fecaca;">
          Logout
        </a>
      </div>

    </div>

  </div>
</section>

<script>
  // ambil jumlah cart dari localStorage
  const cart = JSON.parse(localStorage.getItem("cart") || "[]");
  const fav = JSON.parse(localStorage.getItem("favorites") || "[]");

  document.getElementById("cartTotalItem").textContent = cart.length;
  document.getElementById("favTotalItem").textContent = fav.length;

  document.getElementById("goCart").addEventListener("click", () => {
    const btn = document.getElementById("cartBtn");
    if(btn) btn.click();
  });
</script>

<?php include "footer.php"; ?>