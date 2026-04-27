<?php
require_once __DIR__ . '/auth_admin.php';
require_admin_login();

$pdo = db();

// Stats
$totalProduk    = $pdo->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
$totalCustomer  = $pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$totalPesanan   = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalRevenue   = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$pendingOrders  = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();

// Pesanan terbaru
$recentOrders = $pdo->query(
    "SELECT o.*, 
            GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', ') AS items_summary
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     GROUP BY o.id
     ORDER BY o.created_at DESC LIMIT 8"
)->fetchAll();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

$statusLabel = [
    'pending'    => 'badge-pending',
    'confirmed'  => 'badge-confirmed',
    'processing' => 'badge-processing',
    'delivered'  => 'badge-delivered',
    'cancelled'  => 'badge-cancelled',
];
$statusText = [
    'pending'    => 'Menunggu',
    'confirmed'  => 'Dikonfirmasi',
    'processing' => 'Diproses',
    'delivered'  => 'Terkirim',
    'cancelled'  => 'Dibatalkan',
];

include '_layout_top.php';
?>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Total Produk Aktif</div>
    <div class="stat-value"><?= number_format($totalProduk) ?></div>
    <div class="stat-sub">produk tersedia di toko</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Customer</div>
    <div class="stat-value"><?= number_format($totalCustomer) ?></div>
    <div class="stat-sub">akun terdaftar</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Pesanan</div>
    <div class="stat-value"><?= number_format($totalPesanan) ?></div>
    <div class="stat-sub" style="color:<?= $pendingOrders > 0 ? '#d97706' : 'inherit' ?>">
      <?= $pendingOrders ?> menunggu konfirmasi
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Pendapatan</div>
    <div class="stat-value" style="font-size:22px;">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></div>
    <div class="stat-sub">dari pesanan terkonfirmasi</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h2>Pesanan Terbaru</h2>
    <a href="orders.php" class="btn btn-outline btn-sm">Lihat Semua</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Kode</th>
          <th>Customer</th>
          <th>Item</th>
          <th>Total</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentOrders)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:32px;">Belum ada pesanan.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td><strong><?= htmlspecialchars($o['order_code']) ?></strong></td>
          <td><?= htmlspecialchars($o['customer_name']) ?></td>
          <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--muted);">
            <?= htmlspecialchars($o['items_summary'] ?? '-') ?>
          </td>
          <td>Rp <?= number_format($o['total_amount'],0,',','.') ?></td>
          <td>
            <span class="badge <?= $statusLabel[$o['status']] ?? '' ?>">
              <?= $statusText[$o['status']] ?? $o['status'] ?>
            </span>
          </td>
          <td style="color:var(--muted);"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">Detail</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '_layout_bot.php'; ?>
