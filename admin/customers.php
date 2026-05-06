<?php
require_once __DIR__ . '/auth_admin.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_verify();

$pdo     = db();
$msg     = '';

// ── DELETE CUSTOMER ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM customers WHERE id=?')->execute([$id]);
    $msg = 'Customer dihapus.';
}

// ── LIST ──────────────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['p'] ?? 1));
$limit  = 15;
$offset = ($page-1)*$limit;

$where  = $search ? 'WHERE c.name LIKE ? OR c.email LIKE ?' : '';
$params = $search ? ["%$search%", "%$search%"] : [];

$cnt = $pdo->prepare("SELECT COUNT(*) FROM customers c $where");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total/$limit));

$stmt = $pdo->prepare(
    "SELECT c.*,
            COUNT(o.id) AS total_orders,
            COALESCE(SUM(o.total_amount),0) AS total_spent
     FROM customers c
     LEFT JOIN orders o ON o.customer_id = c.id AND o.status != 'cancelled'
     $where
     GROUP BY c.id
     ORDER BY c.created_at DESC
     LIMIT $limit OFFSET $offset"
);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// DETAIL
$viewCustomer     = null;
$customerOrders   = [];
if (isset($_GET['id'])) {
    $cs = $pdo->prepare('SELECT * FROM customers WHERE id=?');
    $cs->execute([(int)$_GET['id']]);
    $viewCustomer = $cs->fetch();
    if ($viewCustomer) {
        $co = $pdo->prepare(
            "SELECT o.*, GROUP_CONCAT(oi.product_name SEPARATOR ', ') AS items
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.customer_id=?
             GROUP BY o.id
             ORDER BY o.created_at DESC"
        );
        $co->execute([$viewCustomer['id']]);
        $customerOrders = $co->fetchAll();
    }
}

$statusBadge = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','processing'=>'badge-processing','delivered'=>'badge-delivered','cancelled'=>'badge-cancelled'];
$statusText  = ['pending'=>'Menunggu','confirmed'=>'Dikonfirmasi','processing'=>'Diproses','delivered'=>'Terkirim','cancelled'=>'Dibatalkan'];

$pageTitle  = 'Customer';
$activePage = 'customers';
include '_layout_top.php';
?>

<?php if ($msg): ?>
  <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($viewCustomer): ?>
<div class="card" style="margin-bottom:24px;">
  <div class="card-header">
    <h2><?= htmlspecialchars($viewCustomer['name']) ?></h2>
    <a href="customers.php" class="btn btn-outline btn-sm">← Kembali</a>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
      <div>
        <p><b>Email:</b> <?= htmlspecialchars($viewCustomer['email']) ?></p>
        <p><b>No. HP:</b> <?= htmlspecialchars($viewCustomer['phone'] ?? '-') ?></p>
        <p><b>Alamat:</b> <?= htmlspecialchars($viewCustomer['address'] ?? '-') ?></p>
        <p><b>Daftar:</b> <?= date('d M Y', strtotime($viewCustomer['created_at'])) ?></p>
      </div>
      <div>
        <p><b>Total Pesanan:</b> <?= count($customerOrders) ?></p>
        <p><b>Total Belanja:</b> Rp <?= number_format(array_sum(array_column($customerOrders,'total_amount')),0,',','.') ?></p>
      </div>
    </div>

    <p style="font-size:12px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);font-weight:700;margin-bottom:12px;">History Pesanan</p>
    <?php if (empty($customerOrders)): ?>
      <p style="color:var(--muted);">Belum ada pesanan.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Kode</th><th>Item</th><th>Total</th><th>Status</th><th>Tanggal</th></tr></thead>
          <tbody>
            <?php foreach ($customerOrders as $o): ?>
            <tr>
              <td><strong><?= htmlspecialchars($o['order_code']) ?></strong></td>
              <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--muted);"><?= htmlspecialchars($o['items']) ?></td>
              <td>Rp <?= number_format($o['total_amount'],0,',','.') ?></td>
              <td><span class="badge <?= $statusBadge[$o['status']] ?? '' ?>"><?= $statusText[$o['status']] ?? $o['status'] ?></span></td>
              <td style="color:var(--muted);font-size:12px;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Semua Customer <span style="font-size:13px;color:var(--muted);font-family:var(--font-body);">(<?= $total ?>)</span></h2>
    <form method="GET" style="display:flex;gap:8px;align-items:center;">
      <div class="search-bar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Nama atau email...">
      </div>
      <button type="submit" class="btn btn-outline btn-sm">Cari</button>
      <?php if ($search): ?><a href="customers.php" class="btn btn-outline btn-sm">Reset</a><?php endif; ?>
    </form>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>ID</th><th>Nama</th><th>Email</th><th>Pesanan</th><th>Total Belanja</th><th>Daftar</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (empty($customers)): ?>
          <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--muted);">Belum ada customer.</td></tr>
        <?php endif; ?>
        <?php foreach ($customers as $c): ?>
        <tr>
          <td style="color:var(--muted);"><?= $c['id'] ?></td>
          <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
          <td style="color:var(--muted);"><?= htmlspecialchars($c['email']) ?></td>
          <td><?= $c['total_orders'] ?> pesanan</td>
          <td>Rp <?= number_format($c['total_spent'],0,',','.') ?></td>
          <td style="color:var(--muted);font-size:12px;"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
              <form method="POST" onsubmit="return confirm('Hapus customer ini? Semua datanya akan terhapus.')">
        <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button class="btn btn-danger btn-sm">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div style="padding:16px 24px;">
    <div class="pagination">
      <?php for ($i=1; $i<=$pages; $i++): ?>
        <?php if ($i===$page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include '_layout_bot.php'; ?>
