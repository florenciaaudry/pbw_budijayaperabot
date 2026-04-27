<?php
require_once "auth_customer.php";
?>

<!-- LINKS -->
<nav class="nav-links">
  <a href="#home" class="active">Home</a>
  <a href="#tentang">Tentang</a>
  <a href="#produk">Produk</a>
  <a href="#kontak">Kontak</a>
</nav>

<!-- ACTIONS -->
<div class="nav-actions">
  <button type="button" class="btn ghost" id="btn-fav">
    ❤ Favorit <span id="fav-count" class="badge-counter">0</span>
  </button>

  <button type="button" class="btn primary" id="btn-cart">
    🛒 Keranjang <span id="cart-count" class="badge-counter">0</span>
  </button>

  <?php if (customer_is_logged_in()): ?>
    <a class="btn outline" href="profile.php">
      Hi, <?= htmlspecialchars($_SESSION["customer"]["name"]) ?>
    </a>
    <a class="btn outline btn-logout" href="logout_customer.php">
      Logout
    </a>
  <?php else: ?>
    <a class="btn outline" href="login_customer.php">Login</a>
    <a class="btn primary" href="register.php">Daftar</a>
  <?php endif; ?>
</div>