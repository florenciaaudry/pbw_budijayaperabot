<?php
// ============================================================
//  admin/orders.php — Kelola Pesanan + Konfirmasi Bukti Bayar
// ============================================================
require_once __DIR__ . '/auth_admin.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_verify();

$pdo     = db();
$msg     = '';
$msgType = 'success';

// ── UPDATE STATUS PESANAN ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action  = $_POST['action'];
    $orderId = (int)($_POST['order_id'] ?? 0);

    if ($action === 'update_status') {
        $validStatuses = ['pending','confirmed','processing','delivered','cancelled'];
        $newStatus     = $_POST['status'] ?? '';
        if (in_array($newStatus, $validStatuses) && $orderId > 0) {
            $pdo->prepare('UPDATE orders SET status=?, updated_at=NOW() WHERE id=?')
                ->execute([$newStatus, $orderId]);
            $msg = 'Status pesanan berhasil diperbarui.';
        }
    }

    // ── KONFIRMASI PEMBAYARAN ──────────────────────────────────
    if ($action === 'confirm_payment' && $orderId > 0) {
        $pdo->prepare(
            "UPDATE orders SET payment_status='paid', status='confirmed', updated_at=NOW() WHERE id=?"
        )->execute([$orderId]);
        $msg = '✅ Pembayaran dikonfirmasi dan status pesanan diubah ke Dikonfirmasi.';
    }

    // ── TOLAK PEMBAYARAN ───────────────────────────────────────
    if ($action === 'reject_payment' && $orderId > 0) {
        $pdo->prepare(
            "UPDATE orders SET payment_status='rejected', updated_at=NOW() WHERE id=?"
        )->execute([$orderId]);
        $msg     = '❌ Pembayaran ditolak. Customer perlu upload ulang bukti.';
        $msgType = 'error';
    }

    // ── HAPUS PESANAN ──────────────────────────────────────────
    if ($action === 'delete_order' && $orderId > 0) {
        // Hapus file bukti bayar jika ada
        $row = $pdo->prepare('SELECT payment_proof FROM orders WHERE id=?');
        $row->execute([$orderId]);
        $r = $row->fetch();
        if ($r && $r['payment_proof']) {
            $path = __DIR__ . '/../' . $r['payment_proof'];
            if (file_exists($path)) @unlink($path);
        }
        $pdo->prepare('DELETE FROM orders WHERE id=?')->execute([$orderId]);
        $msg     = 'Pesanan dihapus.';
        $msgType = 'error';
        // redirect ke list
        header('Location: orders.php?msg=' . urlencode($msg));
        exit;
    }
}

// ── DETAIL VIEW ───────────────────────────────────────────────
$viewOrder = null;
$viewItems = [];
if (isset($_GET['id'])) {
    $s = $pdo->prepare('SELECT * FROM orders WHERE id=?');
    $s->execute([(int)$_GET['id']]);
    $viewOrder = $s->fetch();
    if ($viewOrder) {
        $si = $pdo->prepare('SELECT * FROM order_items WHERE order_id=?');
        $si->execute([$viewOrder['id']]);
        $viewItems = $si->fetchAll();
    }
}

// ── FILTER & LIST ──────────────────────────────────────────────
$search      = trim($_GET['q']       ?? '');
$filterStat  = trim($_GET['status']  ?? '');
$filterPay   = trim($_GET['payment'] ?? '');
$page        = max(1, (int)($_GET['p'] ?? 1));
$limit       = 15;
$offset      = ($page - 1) * $limit;

$conds  = [];
$params = [];
if ($search)      { $conds[] = '(o.order_code LIKE ? OR o.customer_name LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($filterStat)  { $conds[] = 'o.status = ?';          $params[] = $filterStat; }
if ($filterPay)   { $conds[] = 'o.payment_status = ?';  $params[] = $filterPay; }
$where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

$cnt = $pdo->prepare("SELECT COUNT(*) FROM orders o $where");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $limit));

$stmt = $pdo->prepare(
    "SELECT o.*,
            GROUP_CONCAT(oi.product_name ORDER BY oi.id SEPARATOR ', ') AS items_summary
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     $where
     GROUP BY o.id
     ORDER BY
       CASE WHEN o.payment_proof IS NOT NULL AND o.payment_status='unpaid' THEN 0 ELSE 1 END,
       o.created_at DESC
     LIMIT $limit OFFSET $offset"
);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Hitung pending bukti
$pendingProof = (int)$pdo->query(
    "SELECT COUNT(*) FROM orders WHERE payment_proof IS NOT NULL AND payment_status='unpaid'"
)->fetchColumn();

$statuses   = ['pending','confirmed','processing','delivered','cancelled'];
$statusText = [
    'pending'    => 'Menunggu',
    'confirmed'  => 'Dikonfirmasi',
    'processing' => 'Diproses',
    'delivered'  => 'Terkirim',
    'cancelled'  => 'Dibatalkan',
];
$statusBadge = [
    'pending'    => 'badge-pending',
    'confirmed'  => 'badge-confirmed',
    'processing' => 'badge-processing',
    'delivered'  => 'badge-delivered',
    'cancelled'  => 'badge-cancelled',
];
$payText  = ['unpaid' => 'Belum Bayar', 'paid' => 'Lunas', 'rejected' => 'Ditolak'];
$payBadge = ['unpaid' => 'badge-pending', 'paid' => 'badge-delivered', 'rejected' => 'badge-cancelled'];

$pageTitle  = 'Pesanan';
$activePage = 'orders';
include '_layout_top.php';
?>

<style>
  .proof-thumb { max-width: 100%; max-height: 320px; border-radius: 10px; border: 1.5px solid var(--border); cursor: zoom-in; }
  .proof-lightbox {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.85);
    z-index: 9999; align-items: center; justify-content: center; padding: 16px;
  }
  .proof-lightbox img { max-width: 92vw; max-height: 88vh; border-radius: 12px; }
  .proof-lightbox.open { display: flex; }
  .badge-proof { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
  .notif-dot {
    display: inline-flex; align-items: center; justify-content: center;
    background: #dc2626; color: #fff; border-radius: 50%;
    width: 18px; height: 18px; font-size: 10px; font-weight: 700;
    margin-left: 6px; vertical-align: middle;
  }
  .action-group { display: flex; gap: 6px; flex-wrap: wrap; }
  .payment-section {
    background: #FFFBEB; border: 1.5px solid #FDE68A;
    border-radius: 12px; padding: 16px; margin-top: 20px;
  }
  .payment-section.paid { background: #F0FDF4; border-color: #86EFAC; }
  .payment-section.rejected { background: #FEF2F2; border-color: #FECACA; }
</style>

<!-- Lightbox bukti -->
<div class="proof-lightbox" id="proof-lightbox">
  <img id="lightbox-img" src="" alt="Bukti Bayar">
  <button onclick="document.getElementById('proof-lightbox').classList.remove('open')"
    style="position:fixed;top:20px;right:24px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:24px;cursor:pointer;border-radius:50%;width:40px;height:40px;">✕</button>
</div>

<?php if ($msg): ?>
  <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($pendingProof > 0 && !$viewOrder): ?>
<div class="alert alert-warning" style="background:#FEF3C7;border-color:#FDE68A;color:#92400E;">
  ⚠️ Ada <strong><?= $pendingProof ?> pesanan</strong> dengan bukti pembayaran yang menunggu konfirmasi.
  <a href="?payment=unpaid" style="color:#92400E;font-weight:700;margin-left:8px;">Lihat sekarang →</a>
</div>
<?php endif; ?>

<?php if ($viewOrder): ?>
<!-- ═══════════════════════════ DETAIL PANEL ═══════════════════════════ -->
<div class="card" style="margin-bottom:24px;">
  <div class="card-header">
    <h2>Detail Pesanan — <?= htmlspecialchars($viewOrder['order_code']) ?></h2>
    <div class="action-group">
      <a href="orders.php" class="btn btn-outline btn-sm">← Kembali</a>
      <form method="POST" onsubmit="return confirm('Hapus pesanan ini?')" style="display:inline;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete_order">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus</button>
      </form>
    </div>
  </div>

  <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

    <!-- Kiri: Info customer + items -->
    <div>
      <p style="font-size:12px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);font-weight:700;margin-bottom:12px;">Info Customer</p>
      <p><b>Nama:</b> <?= htmlspecialchars($viewOrder['customer_name']) ?></p>
      <p><b>Telepon:</b>
        <a href="https://wa.me/<?= preg_replace('/\D/','',$viewOrder['customer_phone']) ?>" target="_blank" style="color:var(--accent);">
          <?= htmlspecialchars($viewOrder['customer_phone']) ?>
        </a>
      </p>
      <p><b>Alamat:</b> <?= nl2br(htmlspecialchars($viewOrder['delivery_address'])) ?></p>
      <?php if ($viewOrder['notes']): ?>
        <p><b>Catatan:</b> <?= htmlspecialchars($viewOrder['notes']) ?></p>
      <?php endif; ?>
      <p style="margin-top:8px;"><b>Tanggal:</b> <?= date('d M Y H:i', strtotime($viewOrder['created_at'])) ?></p>
      <p><b>Metode Bayar:</b> 📱 QRIS</p>

      <p style="font-size:12px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);font-weight:700;margin:20px 0 8px;">Item Pesanan</p>
      <?php foreach ($viewItems as $item): ?>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;">
          <span><?= htmlspecialchars($item['product_name']) ?> <span style="color:var(--muted);">x<?= $item['qty'] ?></span></span>
          <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
        </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:space-between;padding:12px 0 0;font-weight:700;font-size:14px;">
        <span>Total</span>
        <span style="color:var(--accent);">Rp <?= number_format($viewOrder['total_amount'], 0, ',', '.') ?></span>
      </div>
    </div>

    <!-- Kanan: Update status + Bukti bayar -->
    <div>
      <p style="font-size:12px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);font-weight:700;margin-bottom:12px;">Update Status</p>
      <form method="POST" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <select name="status" class="form-control" style="max-width:200px;">
          <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>" <?= $viewOrder['status'] === $s ? 'selected' : '' ?>>
              <?= $statusText[$s] ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-accent">Simpan</button>
      </form>

      <!-- BUKTI PEMBAYARAN SECTION -->
      <?php
        $payClass = $viewOrder['payment_status'] === 'paid' ? 'paid' :
                    ($viewOrder['payment_status'] === 'rejected' ? 'rejected' : '');
      ?>
      <div class="payment-section <?= $payClass ?>" style="margin-top:20px;">
        <p style="font-size:12px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);font-weight:700;margin-bottom:12px;">
          Bukti Pembayaran
          <span class="badge <?= $payBadge[$viewOrder['payment_status']] ?? 'badge-pending' ?>" style="margin-left:8px;">
            <?= $payText[$viewOrder['payment_status']] ?? $viewOrder['payment_status'] ?>
          </span>
        </p>

        <?php if ($viewOrder['payment_proof']): ?>
          <?php $proofUrl = '../' . htmlspecialchars($viewOrder['payment_proof']); ?>
          <img src="<?= $proofUrl ?>" class="proof-thumb" alt="Bukti Bayar"
               onclick="document.getElementById('lightbox-img').src='<?= $proofUrl ?>';document.getElementById('proof-lightbox').classList.add('open')">
          <p style="font-size:11px;color:var(--muted);margin-top:8px;">
            Diupload: <?= $viewOrder['payment_proof_at'] ? date('d M Y H:i', strtotime($viewOrder['payment_proof_at'])) : '-' ?>
          </p>

          <?php if ($viewOrder['payment_status'] === 'unpaid'): ?>
            <div class="action-group" style="margin-top:12px;">
              <form method="POST" style="display:inline;" onsubmit="return confirm('Konfirmasi pembayaran ini?')">
        <?= csrf_field() ?>
                <input type="hidden" name="action" value="confirm_payment">
                <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="btn btn-accent">✅ Konfirmasi Lunas</button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Tolak bukti pembayaran ini? Customer harus upload ulang.')">
        <?= csrf_field() ?>
                <input type="hidden" name="action" value="reject_payment">
                <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">❌ Tolak</button>
              </form>
            </div>
          <?php elseif ($viewOrder['payment_status'] === 'paid'): ?>
            <p style="color:#16a34a;font-weight:700;font-size:13px;margin-top:10px;">✅ Pembayaran sudah dikonfirmasi.</p>
          <?php elseif ($viewOrder['payment_status'] === 'rejected'): ?>
            <p style="color:#dc2626;font-weight:700;font-size:13px;margin-top:10px;">❌ Pembayaran ditolak — customer perlu upload ulang.</p>
          <?php endif; ?>

        <?php else: ?>
          <p style="color:var(--muted);font-size:13px;">Belum ada bukti yang diupload.</p>
          <p style="font-size:12px;color:var(--muted);margin-top:4px;">Customer akan upload bukti setelah transfer QRIS.</p>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════ LIST PESANAN ═══════════════════════════ -->
<div class="card">
  <div class="card-header">
    <h2>
      Semua Pesanan <span style="font-size:13px;color:var(--muted);font-family:var(--font-body);">(<?= $total ?>)</span>
      <?php if ($pendingProof > 0): ?>
        <span class="notif-dot"><?= $pendingProof ?></span>
      <?php endif; ?>
    </h2>
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
      <div class="search-bar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Kode / nama customer...">
      </div>
      <select name="status" class="form-control" style="max-width:150px;">
        <option value="">Semua Status</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?= $s ?>" <?= $filterStat === $s ? 'selected' : '' ?>><?= $statusText[$s] ?></option>
        <?php endforeach; ?>
      </select>
      <select name="payment" class="form-control" style="max-width:150px;">
        <option value="">Semua Bayar</option>
        <option value="unpaid"   <?= $filterPay === 'unpaid'   ? 'selected' : '' ?>>Belum Bayar</option>
        <option value="paid"     <?= $filterPay === 'paid'     ? 'selected' : '' ?>>Lunas</option>
        <option value="rejected" <?= $filterPay === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
      </select>
      <button type="submit" class="btn btn-outline btn-sm">Filter</button>
      <?php if ($search || $filterStat || $filterPay): ?>
        <a href="orders.php" class="btn btn-outline btn-sm">Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Kode</th>
          <th>Customer</th>
          <th>Item</th>
          <th>Total</th>
          <th>Bayar</th>
          <th>Bukti</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--muted);">Belum ada pesanan.</td></tr>
        <?php endif; ?>
        <?php foreach ($orders as $o): ?>
        <tr <?= ($o['payment_proof'] && $o['payment_status'] === 'unpaid') ? 'style="background:#FFFBEB;"' : '' ?>>
          <td>
            <strong><?= htmlspecialchars($o['order_code']) ?></strong>
            <div style="font-size:10px;color:var(--muted);">📱 QRIS</div>
          </td>
          <td>
            <div><?= htmlspecialchars($o['customer_name']) ?></div>
            <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($o['customer_phone']) ?></div>
          </td>
          <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--muted);">
            <?= htmlspecialchars($o['items_summary'] ?? '-') ?>
          </td>
          <td>Rp <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
          <td>
            <span class="badge <?= $payBadge[$o['payment_status']] ?? '' ?>">
              <?= $payText[$o['payment_status']] ?? $o['payment_status'] ?>
            </span>
          </td>
          <td>
            <?php if ($o['payment_proof']): ?>
              <?php if ($o['payment_status'] === 'unpaid'): ?>
                <span class="badge badge-proof">⏳ Perlu cek</span>
              <?php elseif ($o['payment_status'] === 'paid'): ?>
                <span class="badge badge-delivered">✅ OK</span>
              <?php else: ?>
                <span class="badge badge-cancelled">❌ Ditolak</span>
              <?php endif; ?>
            <?php else: ?>
              <span style="color:var(--muted);font-size:12px;">—</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?= $statusBadge[$o['status']] ?? '' ?>">
              <?= $statusText[$o['status']] ?? $o['status'] ?>
            </span>
          </td>
          <td style="color:var(--muted);font-size:12px;"><?= date('d M Y\nH:i', strtotime($o['created_at'])) ?></td>
          <td>
            <a href="?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div style="padding:16px 24px;">
    <div class="pagination">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if ($i === $page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>&status=<?= urlencode($filterStat) ?>&payment=<?= urlencode($filterPay) ?>">
            <?= $i ?>
          </a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include '_layout_bot.php'; ?>
