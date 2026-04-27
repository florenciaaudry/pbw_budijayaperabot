<?php
// ============================================================
//  profile.php — Profil Customer + History Pesanan
// ============================================================
require_once __DIR__ . '/auth_customer.php';
require_customer_login();

$pdo     = db();
$cid     = (int)$_SESSION['customer']['id'];
$msg     = '';
$msgType = 'success';

// ── UPDATE PROFIL ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    csrf_verify();
    $name    = trim($_POST['name']    ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $address = trim($_POST['address'] ?? '');
    $newPass = trim($_POST['new_password'] ?? '');
    $conPass = trim($_POST['confirm_password'] ?? '');

    if ($name === '') {
        $msg = 'Nama tidak boleh kosong.'; $msgType = 'error';
    } else {
        if ($newPass !== '') {
            if ($newPass !== $conPass) {
                $msg = 'Password baru tidak cocok.'; $msgType = 'error';
            } elseif (strlen($newPass) < 6) {
                $msg = 'Password minimal 6 karakter.'; $msgType = 'error';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE customers SET name=?, phone=?, address=?, password_hash=? WHERE id=?')
                    ->execute([$name, $phone, $address, $hash, $cid]);
                $msg = 'Profil dan password berhasil diperbarui.';
            }
        } else {
            $pdo->prepare('UPDATE customers SET name=?, phone=?, address=? WHERE id=?')
                ->execute([$name, $phone, $address, $cid]);
            $msg = 'Profil berhasil diperbarui.';
        }
        if ($msgType === 'success') $_SESSION['customer']['name'] = $name;
    }
}

// ── AMBIL DATA CUSTOMER ───────────────────────────────────────
$stmt = $pdo->prepare('SELECT * FROM customers WHERE id=?');
$stmt->execute([$cid]);
$customer = $stmt->fetch();

// ── AMBIL HISTORY PESANAN ─────────────────────────────────────
$ordersStmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id=? ORDER BY created_at DESC LIMIT 20');
$ordersStmt->execute([$cid]);
$myOrders = $ordersStmt->fetchAll();

$orderIds    = array_column($myOrders, 'id');
$itemsByOrder = [];
if (!empty($orderIds)) {
    $in = implode(',', array_fill(0, count($orderIds), '?'));
    $is = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($in) ORDER BY id");
    $is->execute($orderIds);
    foreach ($is->fetchAll() as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}

$statusText  = ['pending'=>'Menunggu','confirmed'=>'Dikonfirmasi','processing'=>'Diproses','delivered'=>'Terkirim','cancelled'=>'Dibatalkan'];
$statusColor = ['pending'=>'#d97706','confirmed'=>'#2563eb','processing'=>'#7c3aed','delivered'=>'#16a34a','cancelled'=>'#dc2626'];
$payText     = ['unpaid'=>'Belum Lunas','paid'=>'Lunas','rejected'=>'Ditolak'];
$payColor    = ['unpaid'=>'#d97706','paid'=>'#16a34a','rejected'=>'#dc2626'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil Saya — Budi Jaya Furniture</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'DM Sans',sans-serif;background:#F7F4EF;color:#1C1C1A;min-height:100vh}
    .container{max-width:860px;margin:0 auto;padding:32px 16px 64px}
    h1{font-family:'DM Serif Display',serif;font-size:28px;margin-bottom:6px}
    .sub{font-size:13px;color:#767370;margin-bottom:28px}
    .tabs{display:flex;gap:4px;border-bottom:1.5px solid #E4E0D8;margin-bottom:24px}
    .tab{padding:10px 18px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:600;color:#767370;font-family:inherit;border-bottom:2.5px solid transparent;margin-bottom:-1.5px;transition:all .15s}
    .tab.active{color:#A34E22;border-bottom-color:#A34E22}
    .tab-panel{display:none}
    .tab-panel.active{display:block}
    .card{background:#FDFBF8;border:1.5px solid #E4E0D8;border-radius:16px;padding:24px;margin-bottom:16px}
    label{display:block;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:6px;color:#1C1C1A}
    input,textarea{width:100%;padding:10px 13px;border:1.5px solid #E4E0D8;border-radius:8px;font-size:14px;outline:none;font-family:inherit;background:#fff;transition:border-color .15s;margin-bottom:14px}
    input:focus,textarea:focus{border-color:#A34E22}
    textarea{resize:vertical}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:11px 20px;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;transition:opacity .15s}
    .btn:disabled{opacity:.55;cursor:not-allowed}
    .btn-dark{background:#1C1C1A;color:#fff}
    .btn-dark:hover{background:#2E2E2B}
    .btn-outline{background:transparent;border:1.5px solid #E4E0D8;color:#1C1C1A}
    .btn-outline:hover{background:#F7F4EF}
    .btn-accent{background:#A34E22;color:#fff}
    .btn-accent:hover:not(:disabled){background:#8B4019}
    .alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px}
    .alert-success{background:#D1FAE5;color:#065F46}
    .alert-error{background:#FEE2E2;color:#991B1B}
    .order-card{background:#fff;border:1.5px solid #E4E0D8;border-radius:12px;margin-bottom:14px;overflow:hidden}
    .order-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;padding:14px 16px;border-bottom:1px solid #E4E0D8;background:#FAFAF8}
    .order-code{font-weight:700;font-size:14px}
    .order-date{font-size:11px;color:#767370}
    .status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
    .order-body{padding:14px 16px}
    .order-item-row{display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid #F0EDE8}
    .order-item-row:last-child{border-bottom:none}
    .order-total{display:flex;justify-content:space-between;font-weight:700;font-size:14px;padding:10px 0 0}
    .upload-proof-area{border:2px dashed #E4E0D8;border-radius:10px;padding:16px;text-align:center;cursor:pointer;transition:all .15s;background:#FAFAF8;margin-top:10px}
    .upload-proof-area:hover{border-color:#A34E22;background:#FDF5F0}
    .proof-preview{max-width:100%;max-height:200px;border-radius:8px;margin-top:8px;border:1.5px solid #E4E0D8;display:none}
    .nav-back{display:inline-flex;align-items:center;gap:6px;color:#767370;font-size:13px;text-decoration:none;margin-bottom:20px}
    .nav-back:hover{color:#1C1C1A}
  </style>
</head>
<body>
<div class="container">
  <a href="index.php" class="nav-back">← Kembali ke Toko</a>
  <h1>Profil Saya</h1>
  <p class="sub">Selamat datang, <strong><?= htmlspecialchars($customer['name']) ?></strong> · <?= htmlspecialchars($customer['email']) ?></p>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="tabs">
    <button class="tab active" onclick="switchTab('orders',this)">🛍 Pesanan Saya</button>
    <button class="tab" onclick="switchTab('profile',this)">👤 Edit Profil</button>
    <button class="tab" onclick="switchTab('security',this)">🔒 Password</button>
  </div>

  <!-- PESANAN -->
  <div id="tab-orders" class="tab-panel active">
    <?php if (empty($myOrders)): ?>
      <div class="card" style="text-align:center;color:#767370;padding:48px;">
        Belum ada pesanan. <a href="index.php" style="color:#A34E22;font-weight:700;">Mulai belanja →</a>
      </div>
    <?php endif; ?>
    <?php foreach ($myOrders as $order):
      $items    = $itemsByOrder[$order['id']] ?? [];
      $payStatus = $order['payment_status'];
      $stColor   = $statusColor[$order['status']] ?? '#767370';
      $pyColor   = $payColor[$payStatus]           ?? '#767370';
    ?>
    <div class="order-card">
      <div class="order-header">
        <div>
          <div class="order-code"><?= htmlspecialchars($order['order_code']) ?></div>
          <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
            · 📱 QRIS
          </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <span class="status-badge" style="background:<?= $stColor ?>22;color:<?= $stColor ?>;">
            <?= $statusText[$order['status']] ?? $order['status'] ?>
          </span>
          <span class="status-badge" style="background:<?= $pyColor ?>22;color:<?= $pyColor ?>;">
            💳 <?= $payText[$payStatus] ?? $payStatus ?>
          </span>
        </div>
      </div>

      <div class="order-body">
        <?php foreach ($items as $item): ?>
          <div class="order-item-row">
            <span><?= htmlspecialchars($item['product_name']) ?> <span style="color:#767370;">x<?= $item['qty'] ?></span></span>
            <span>Rp <?= number_format($item['subtotal'],0,',','.') ?></span>
          </div>
        <?php endforeach; ?>
        <div class="order-total">
          <span>Total</span>
          <span style="color:#A34E22;">Rp <?= number_format($order['total_amount'],0,',','.') ?></span>
        </div>
        <?php if ($order['notes']): ?>
          <p style="font-size:12px;color:#767370;margin-top:8px;">📝 <?= htmlspecialchars($order['notes']) ?></p>
        <?php endif; ?>

        <!-- UPLOAD BUKTI -->
        <?php if (in_array($payStatus, ['unpaid','rejected'])): ?>
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #E4E0D8;">
          <?php if ($payStatus === 'rejected'): ?>
            <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:10px 12px;font-size:12px;color:#991B1B;margin-bottom:10px;">
              ❌ Bukti pembayaran sebelumnya ditolak. Harap upload ulang bukti yang valid.
            </div>
          <?php elseif ($order['payment_proof']): ?>
            <p style="font-size:12px;color:#d97706;font-weight:600;margin-bottom:8px;">
              ⏳ Bukti sudah diupload — menunggu konfirmasi admin.
            </p>
          <?php else: ?>
            <p style="font-size:12px;color:#767370;margin-bottom:8px;">Sudah transfer? Upload bukti di sini:</p>
          <?php endif; ?>

          <?php if (!$order['payment_proof'] || $payStatus === 'rejected'): ?>
          <div class="upload-proof-area" onclick="document.getElementById('proof-<?= $order['id'] ?>').click()">
            <input type="file" id="proof-<?= $order['id'] ?>" accept="image/*" style="display:none"
                   onchange="handleProofUpload(this,<?= $order['id'] ?>,'<?= htmlspecialchars($order['order_code']) ?>')">
            <div style="font-size:24px;margin-bottom:4px;">📸</div>
            <div style="font-size:13px;font-weight:600;">Klik untuk pilih foto bukti transfer</div>
            <div style="font-size:11px;color:#767370;margin-top:2px;">JPG, PNG, WEBP — maks. 5 MB</div>
          </div>
          <img id="preview-<?= $order['id'] ?>" class="proof-preview" alt="preview">
          <div id="upload-msg-<?= $order['id'] ?>" style="display:none;margin-top:8px;font-size:13px;font-weight:600;"></div>
          <button id="upload-btn-<?= $order['id'] ?>" class="btn btn-accent" style="display:none;margin-top:10px;width:100%;"
                  onclick="submitProof(<?= $order['id'] ?>,'<?= htmlspecialchars($order['order_code']) ?>')">
            📤 Kirim Bukti Pembayaran
          </button>
          <?php endif; ?>
        </div>
        <?php elseif ($payStatus === 'paid'): ?>
          <p style="font-size:12px;color:#16a34a;font-weight:600;margin-top:10px;">✅ Pembayaran sudah dikonfirmasi admin.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- PROFIL -->
  <div id="tab-profile" class="tab-panel">
    <div class="card">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="update_profile" value="1">
        <label>Nama Lengkap</label>
        <input type="text" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required>
        <label>No. Telepon / WhatsApp</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($customer['phone']??'') ?>" placeholder="08xx-xxxx-xxxx">
        <label>Alamat Default</label>
        <textarea name="address" rows="3"><?= htmlspecialchars($customer['address']??'') ?></textarea>
        <input type="hidden" name="new_password" value="">
        <input type="hidden" name="confirm_password" value="">
        <button type="submit" class="btn btn-dark">Simpan Perubahan</button>
      </form>
    </div>
    <a href="logout_customer.php" class="btn btn-outline" style="margin-top:8px;">Keluar</a>
  </div>

  <!-- SECURITY -->
  <div id="tab-security" class="tab-panel">
    <div class="card">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="update_profile" value="1">
        <input type="hidden" name="name" value="<?= htmlspecialchars($customer['name']) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($customer['phone']??'') ?>">
        <input type="hidden" name="address" value="<?= htmlspecialchars($customer['address']??'') ?>">
        <label>Password Baru</label>
        <input type="password" name="new_password" placeholder="Min. 6 karakter">
        <label>Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" placeholder="Ulangi password baru">
        <button type="submit" class="btn btn-dark">Ganti Password</button>
      </form>
    </div>
  </div>

</div>

<script>
function switchTab(name, el) {
  document.querySelectorAll('.tab-panel').forEach(function(p){p.classList.remove('active');});
  document.querySelectorAll('.tab').forEach(function(t){t.classList.remove('active');});
  document.getElementById('tab-'+name).classList.add('active');
  el.classList.add('active');
}
var proofFiles = {};
function handleProofUpload(input, orderId, orderCode) {
  var file = input.files[0];
  if (!file) return;
  if (file.size > 5*1024*1024) { alert('Ukuran file maksimal 5 MB.'); return; }
  var allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  if (!allowed.includes(file.type)) { alert('Format tidak didukung.'); return; }
  proofFiles[orderId] = file;
  var reader = new FileReader();
  reader.onload = function(e) {
    var p = document.getElementById('preview-'+orderId);
    p.src = e.target.result; p.style.display = 'block';
  };
  reader.readAsDataURL(file);
  document.getElementById('upload-btn-'+orderId).style.display = 'flex';
}
function submitProof(orderId, orderCode) {
  var file = proofFiles[orderId];
  if (!file) { alert('Pilih file terlebih dahulu.'); return; }
  var fd = new FormData();
  fd.append('order_id', orderId);
  fd.append('order_code', orderCode);
  fd.append('proof_image', file);
  var btn = document.getElementById('upload-btn-'+orderId);
  var msgEl = document.getElementById('upload-msg-'+orderId);
  btn.disabled = true; btn.textContent = '⏳ Mengirim...';
  fetch('api_upload_proof.php', { method:'POST', body:fd })
  .then(function(r){return r.json();})
  .then(function(data){
    btn.disabled = false; btn.textContent = '📤 Kirim Bukti Pembayaran';
    msgEl.style.display = 'block';
    if (data.ok) {
      msgEl.style.color = '#16a34a';
      msgEl.textContent = '✅ '+data.message;
      btn.style.display = 'none';
      setTimeout(function(){ location.reload(); }, 1800);
    } else {
      msgEl.style.color = '#dc2626';
      msgEl.textContent = '❌ '+(data.message||'Gagal upload.');
    }
  })
  .catch(function(){
    btn.disabled = false; btn.textContent = '📤 Kirim Bukti Pembayaran';
    msgEl.style.display = 'block'; msgEl.style.color = '#dc2626';
    msgEl.textContent = '❌ Gagal terhubung ke server. Coba lagi.';
  });
}
</script>
</body>
</html>
