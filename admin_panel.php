<?php
// ============================================================
//  admin_panel.php — Satu halaman untuk semua kendali admin
// ============================================================
require_once __DIR__ . '/admin_auth.php';
require_admin_login();

$pdo = db();
$msg = '';

// ── PROSES AKSI (POST) ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // 1. Konfirmasi Pembayaran
    if (isset($_POST['action']) && $_POST['action'] === 'confirm_payment') {
        $oid = (int)$_POST['order_id'];
        $pdo->prepare("UPDATE orders SET payment_status='paid', status='confirmed' WHERE id=?")->execute([$oid]);
        $msg = "Pesanan #$oid berhasil dikonfirmasi lunas.";
    }

    // 2. Update Status Pengiriman
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $oid = (int)$_POST['order_id'];
        $newStatus = $_POST['new_status'];
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$newStatus, $oid]);
        $msg = "Status pesanan #$oid diubah menjadi $newStatus.";
    }

    // 3. Update Stok Produk
    if (isset($_POST['action']) && $_POST['action'] === 'update_stock') {
        $pid = (int)$_POST['product_id'];
        $stock = (int)$_POST['stock'];
        $price = (int)$_POST['price'];
        $pdo->prepare("UPDATE products SET stock=?, price=? WHERE id=?")->execute([$stock, $price, $pid]);
        $msg = "Produk #$pid berhasil diperbarui.";
    }
}

// ── AMBIL DATA ──────────────────────────────────────────────
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
$products = $pdo->query("SELECT * FROM products ORDER BY category, name ASC")->fetchAll();

$statusText  = ['pending'=>'Menunggu','confirmed'=>'Dikonfirmasi','processing'=>'Diproses','delivered'=>'Terkirim','cancelled'=>'Dibatalkan'];
$payText     = ['unpaid'=>'Belum Lunas','paid'=>'Lunas','rejected'=>'Ditolak'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel — Budi Jaya Furniture</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <style>
    :root { --accent: #A34E22; --charcoal: #1C1C1A; --border: #E4E0D8; --bg: #F7F4EF; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--charcoal); display: flex; min-height: 100vh; }

    /* Sidebar */
    .sidebar { width: 240px; background: var(--charcoal); color: white; padding: 30px 20px; display: flex; flex-direction: column; gap: 30px; position: fixed; height: 100vh; }
    .sidebar h2 { font-family: 'DM Serif Display', serif; font-size: 20px; }
    .nav-btn { background: none; border: none; color: rgba(255,255,255,0.6); text-align: left; font-size: 14px; font-weight: 600; cursor: pointer; padding: 10px 0; transition: .2s; }
    .nav-btn:hover, .nav-btn.active { color: white; }

    /* Main Content */
    .main { flex: 1; margin-left: 240px; padding: 40px; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .card { background: white; border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-bottom: 24px; }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
    th { text-align: left; padding: 12px; border-bottom: 2px solid var(--bg); color: var(--accent); text-transform: uppercase; letter-spacing: 0.05em; font-size: 11px; }
    td { padding: 12px; border-bottom: 1px solid var(--bg); vertical-align: middle; }

    .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
    .badge-unpaid { background: #FFF7ED; color: #9A3412; }
    .badge-paid { background: #F0FDF4; color: #166534; }

    .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 6px; border: 1px solid var(--border); background: white; cursor: pointer; }
    .btn-sm:hover { background: var(--bg); }
    .btn-primary { background: var(--charcoal); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; }

    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .modal-content { background: white; padding: 30px; border-radius: 16px; max-width: 500px; width: 90%; position: relative; }
    .close-modal { position: absolute; right: 20px; top: 20px; cursor: pointer; font-size: 20px; }

    .proof-img { max-width: 100%; border-radius: 8px; margin: 15px 0; border: 1px solid var(--border); }
    .input-edit { padding: 6px; border: 1px solid var(--border); border-radius: 4px; width: 80px; }

    .alert { background: #D1FAE5; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }

    @media (max-width: 800px) {
      .sidebar { width: 60px; padding: 20px 10px; }
      .sidebar h2, .nav-btn span { display: none; }
      .main { margin-left: 60px; padding: 20px; }
    }
  </style>
</head>
<body>

<aside class="sidebar">
  <h2>BJF Admin</h2>
  <nav style="display:flex; flex-direction:column;">
    <button class="nav-btn active" onclick="showSection('orders')">📦 <span>Pesanan</span></button>
    <button class="nav-btn" onclick="showSection('products')">🪑 <span>Produk & Stok</span></button>
    <a href="logout_customer.php" class="nav-btn" style="text-decoration:none; margin-top: auto; color: #ef4444;">🚪 <span>Logout</span></a>
  </nav>
</aside>

<main class="main">
  <div class="header">
    <h1 style="font-family:'DM Serif Display',serif;">Dashboard Admin</h1>
    <p style="color:var(--accent); font-weight:600;">Hari ini: <?= date('d M Y') ?></p>
  </div>

  <?php if ($msg): ?>
    <div class="alert">✅ <?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <!-- SEKSI PESANAN -->
  <section id="section-orders" class="admin-section">
    <div class="card">
      <h3 style="margin-bottom:15px;">Daftar Pesanan Terbaru</h3>
      <div style="overflow-x:auto;">
        <table>
          <thead>
            <tr>
              <th>Kode</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Pembayaran</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
              <td><strong><?= $o['order_code'] ?></strong></td>
              <td><?= htmlspecialchars($o['customer_name']) ?><br><small style="color:#767370;"><?= $o['customer_phone'] ?></small></td>
              <td>Rp <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
              <td>
                <span class="badge badge-<?= $o['payment_status'] ?>">
                  <?= $payText[$o['payment_status']] ?>
                </span>
              </td>
              <td><?= $statusText[$o['status']] ?></td>
              <td>
                <button class="btn-sm" onclick="viewOrder(<?= htmlspecialchars(json_encode($o)) ?>)">Detail / Bukti</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- SEKSI PRODUK -->
  <section id="section-products" class="admin-section" style="display:none;">
    <div class="card">
      <h3 style="margin-bottom:15px;">Kelola Stok & Harga</h3>
      <table>
        <thead>
          <tr>
            <th>Produk</th>
            <th>Kategori</th>
            <th>Harga (Rp)</th>
            <th>Stok</th>
            <th>Update</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr>
            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
            <td><?= $p['category'] ?></td>
            <form method="POST">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update_stock">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <td><input type="number" name="price" value="<?= $p['price'] ?>" class="input-edit" style="width:100px;"></td>
              <td><input type="number" name="stock" value="<?= $p['stock'] ?>" class="input-edit"></td>
              <td><button type="submit" class="btn-sm">Simpan</button></td>
            </form>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<!-- MODAL DETAIL PESANAN -->
<div id="orderModal" class="modal">
  <div class="modal-content">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <h2 id="m-title" style="font-family:'DM Serif Display',serif; margin-bottom:10px;">Detail Pesanan</h2>
    <div id="m-body" style="font-size:14px; line-height:1.6;">
       <!-- Diisi via JS -->
    </div>
    <hr style="margin:20px 0; border:0; border-top:1px solid var(--border);">

    <div id="m-proof-area">
      <p><strong>Bukti Transfer:</strong></p>
      <img id="m-img" src="" class="proof-img" alt="Belum ada bukti upload">
    </div>

    <div style="display:flex; gap:10px; margin-top:20px;">
      <form method="POST" id="form-confirm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="confirm_payment">
        <input type="hidden" name="order_id" id="m-oid-confirm">
        <button type="submit" class="btn-primary" id="btn-conf-pay">Konfirmasi Lunas</button>
      </form>

      <form method="POST" id="form-status">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" id="m-oid-status">
        <select name="new_status" class="input-edit" style="width:120px; height:35px;" onchange="this.form.submit()">
          <option value="">Ubah Status...</option>
          <option value="confirmed">Dikonfirmasi</option>
          <option value="processing">Diproses</option>
          <option value="delivered">Terkirim</option>
          <option value="cancelled">Dibatalkan</option>
        </select>
      </form>
    </div>
  </div>
</div>

<script>
function showSection(name) {
  document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('section-' + name).style.display = 'block';
  event.currentTarget.classList.add('active');
}

function viewOrder(order) {
  document.getElementById('m-title').textContent = 'Pesanan ' + order.order_code;
  document.getElementById('m-body').innerHTML = `
    <p><strong>Nama:</strong> ${order.customer_name}</p>
    <p><strong>Alamat:</strong> ${order.delivery_address}</p>
    <p><strong>Catatan:</strong> ${order.notes || '-'}</p>
    <p><strong>Total:</strong> Rp ${parseInt(order.total_amount).toLocaleString('id-ID')}</p>
  `;

  const img = document.getElementById('m-img');
  if (order.payment_proof) {
    img.src = order.payment_proof;
    img.style.display = 'block';
  } else {
    img.style.display = 'none';
  }

  document.getElementById('m-oid-confirm').value = order.id;
  document.getElementById('m-oid-status').value = order.id;

  // Sembunyikan tombol konfirmasi jika sudah lunas
  document.getElementById('btn-conf-pay').style.display = (order.payment_status === 'paid') ? 'none' : 'block';

  document.getElementById('orderModal').style.display = 'flex';
}

function closeModal() {
  document.getElementById('orderModal').style.display = 'none';
}

window.onclick = function(event) {
  if (event.target == document.getElementById('orderModal')) closeModal();
}
</script>

</body>
</html>