// ============================================================
//  checkout_patch.js — Checkout QRIS Only
//  Tambahkan <script src="checkout_patch.js"></script>
//  di footer.php SETELAH <script src="script.js"></script>
// ============================================================

var QRIS_IMAGE_URL = 'gambar/qris.png';

// ── MODAL HTML ────────────────────────────────────────────────────────────────
var checkoutModalHTML = `
<style>
  #checkout-modal *, #payment-modal *, #success-modal * { box-sizing: border-box; }
  .bjf-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 9000;
    align-items: center; justify-content: center; padding: 16px;
  }
  .bjf-modal-overlay.open { display: flex; }
  .bjf-modal-box {
    background: #FDFBF8; border-radius: 20px; padding: 28px 28px 24px;
    width: 100%; max-width: 480px; max-height: 92vh; overflow-y: auto;
    box-shadow: 0 32px 80px rgba(0,0,0,.3);
  }
  .bjf-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 20px;
  }
  .bjf-modal-title {
    font-family: 'DM Serif Display', serif; font-size: 20px; color: #1C1C1A;
  }
  .bjf-close-btn {
    background: none; border: none; cursor: pointer;
    font-size: 20px; color: #767370; padding: 4px 8px; border-radius: 6px;
  }
  .bjf-close-btn:hover { background: #F0EDE8; }
  .bjf-order-summary {
    background: #F7F4EF; border-radius: 12px; padding: 14px;
    margin-bottom: 20px; font-size: 13px;
  }
  .bjf-order-row {
    display: flex; justify-content: space-between; margin-bottom: 6px; color: #1C1C1A;
  }
  .bjf-order-total {
    border-top: 1px solid #E4E0D8; margin-top: 10px; padding-top: 10px;
    display: flex; justify-content: space-between; font-weight: 700;
  }
  .bjf-form-label {
    display: block; font-size: 11px; font-weight: 700;
    letter-spacing: .08em; text-transform: uppercase; margin-bottom: 6px;
    color: #1C1C1A;
  }
  .bjf-form-group { margin-bottom: 14px; }
  .bjf-input {
    width: 100%; padding: 10px 13px; border: 1.5px solid #E4E0D8;
    border-radius: 8px; font-size: 14px; outline: none;
    font-family: inherit; background: #fff; transition: border-color .15s;
    color: #1C1C1A;
  }
  .bjf-input:focus { border-color: #A34E22; }
  .bjf-textarea { resize: vertical; min-height: 72px; }
  .bjf-error-box {
    display: none; background: #fee2e2; color: #991b1b;
    border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 14px;
  }
  .bjf-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 13px 20px; border: none; border-radius: 10px;
    font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit;
    transition: opacity .15s; width: 100%;
  }
  .bjf-btn:disabled { opacity: .55; cursor: not-allowed; }
  .bjf-btn-dark { background: #1C1C1A; color: #fff; }
  .bjf-btn-dark:hover:not(:disabled) { background: #2E2E2B; }
  .bjf-btn-accent { background: #A34E22; color: #fff; }
  .bjf-btn-accent:hover:not(:disabled) { background: #8B4019; }
  .bjf-hint { font-size: 11px; color: #767370; text-align: center; margin-top: 8px; }
  .bjf-step-indicator {
    display: flex; align-items: center; gap: 8px; margin-bottom: 20px;
    font-size: 12px; color: #767370;
  }
  .bjf-step { display: flex; align-items: center; gap: 4px; }
  .bjf-step-num {
    width: 22px; height: 22px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center; font-size: 11px; font-weight: 700;
  }
  .bjf-step-num.active { background: #A34E22; color: #fff; }
  .bjf-step-num.done { background: #16a34a; color: #fff; }
  .bjf-step-num.idle { background: #E4E0D8; color: #767370; }
  .bjf-step-line { flex: 1; height: 1px; background: #E4E0D8; }
  .bjf-qris-wrap {
    background: #fff; border: 1.5px solid #E4E0D8; border-radius: 12px;
    padding: 16px; text-align: center; margin-bottom: 16px;
  }
  .bjf-qris-wrap img {
    max-width: 220px; width: 100%; border-radius: 8px; display: block; margin: 0 auto 10px;
  }
  .bjf-amount-badge {
    display: inline-block; background: #A34E22; color: #fff;
    border-radius: 8px; padding: 6px 14px; font-size: 16px; font-weight: 700;
  }
  .bjf-upload-area {
    border: 2px dashed #E4E0D8; border-radius: 10px; padding: 20px;
    text-align: center; cursor: pointer; transition: all .15s; margin-bottom: 14px;
  }
  .bjf-upload-area:hover { border-color: #A34E22; background: #FDF5F0; }
  .bjf-upload-area .upload-icon { font-size: 28px; margin-bottom: 6px; }
  .bjf-upload-area .upload-text { font-size: 13px; font-weight: 600; color: #1C1C1A; }
  .bjf-upload-area .upload-sub { font-size: 11px; color: #767370; margin-top: 2px; }
  .bjf-preview-img {
    display: none; max-width: 100%; max-height: 180px; border-radius: 8px;
    border: 1.5px solid #E4E0D8; margin-bottom: 14px; object-fit: contain;
  }
  .bjf-success-box { text-align: center; padding: 8px 0; }
  .bjf-success-icon { font-size: 48px; margin-bottom: 12px; }
  .bjf-success-code {
    background: #F7F4EF; border-radius: 10px; padding: 12px;
    font-size: 18px; font-weight: 700; letter-spacing: .04em;
    color: #A34E22; margin: 12px 0 8px;
  }
  .bjf-info-box {
    background: #FFF7ED; border: 1.5px solid #FED7AA; border-radius: 10px;
    padding: 12px 14px; margin-bottom: 16px; font-size: 12px; color: #92400E;
  }
</style>

<!-- MODAL 1: Form Checkout -->
<div id="checkout-modal" class="bjf-modal-overlay">
  <div class="bjf-modal-box">
    <div class="bjf-modal-header">
      <span class="bjf-modal-title">Checkout</span>
      <button class="bjf-close-btn" id="close-checkout-modal">✕</button>
    </div>

    <div class="bjf-step-indicator">
      <div class="bjf-step"><span class="bjf-step-num active">1</span><span>Data Pengiriman</span></div>
      <div class="bjf-step-line"></div>
      <div class="bjf-step"><span class="bjf-step-num idle">2</span><span>Bayar QRIS</span></div>
      <div class="bjf-step-line"></div>
      <div class="bjf-step"><span class="bjf-step-num idle">3</span><span>Selesai</span></div>
    </div>

    <div id="checkout-summary" class="bjf-order-summary"></div>

    <div class="bjf-form-group">
      <label class="bjf-form-label">Nama Lengkap *</label>
      <input id="co-name" type="text" placeholder="Nama penerima" class="bjf-input">
    </div>
    <div class="bjf-form-group">
      <label class="bjf-form-label">No. Telepon *</label>
      <input id="co-phone" type="tel" placeholder="08xx-xxxx-xxxx" class="bjf-input">
    </div>
    <div class="bjf-form-group">
      <label class="bjf-form-label">Alamat Pengiriman *</label>
      <textarea id="co-address" class="bjf-input bjf-textarea" placeholder="Alamat lengkap..."></textarea>
    </div>
    <div class="bjf-form-group">
      <label class="bjf-form-label">Catatan (Opsional)</label>
      <input id="co-notes" type="text" placeholder="Misal: warna tertentu, lantai berapa, dll" class="bjf-input">
    </div>

    <div class="bjf-info-box">
      📱 Pembayaran dilakukan via <strong>QRIS</strong> — scan dengan aplikasi bank atau e-wallet (GoPay, OVO, DANA, dll).
    </div>

    <div id="checkout-error" class="bjf-error-box"></div>

    <button id="submit-checkout" class="bjf-btn bjf-btn-dark">
      Lanjut ke Pembayaran QRIS →
    </button>
    <p class="bjf-hint">Pesanan disimpan aman di database kami.</p>
  </div>
</div>

<!-- MODAL 2: QRIS + Upload Bukti -->
<div id="payment-modal" class="bjf-modal-overlay">
  <div class="bjf-modal-box">
    <div class="bjf-modal-header">
      <span class="bjf-modal-title">Pembayaran QRIS</span>
      <button class="bjf-close-btn" id="close-payment-modal">✕</button>
    </div>

    <div class="bjf-step-indicator">
      <div class="bjf-step"><span class="bjf-step-num done">✓</span><span>Data</span></div>
      <div class="bjf-step-line"></div>
      <div class="bjf-step"><span class="bjf-step-num active">2</span><span>Bayar</span></div>
      <div class="bjf-step-line"></div>
      <div class="bjf-step"><span class="bjf-step-num idle">3</span><span>Selesai</span></div>
    </div>

    <p style="font-size:13px;color:#767370;margin-bottom:12px;">
      Scan QR di bawah menggunakan aplikasi perbankan / e-wallet:
    </p>

    <div class="bjf-qris-wrap">
      <img src="" id="qris-img" alt="QRIS Budi Jaya Furniture">
      <div class="bjf-amount-badge" id="qris-amount">Rp 0</div>
      <p style="font-size:11px;color:#767370;margin-top:6px;">Transfer tepat sesuai nominal</p>
    </div>

    <div class="bjf-info-box">
      ⏱ Setelah transfer, upload bukti bayar di bawah. Admin akan konfirmasi dalam 1×24 jam.
    </div>

    <p style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px;color:#1C1C1A;">
      Upload Bukti Pembayaran *
    </p>

    <div class="bjf-upload-area" id="upload-area">
      <input type="file" id="proof-file" accept="image/*" style="display:none">
      <div class="upload-icon">📸</div>
      <div class="upload-text">Klik atau seret foto bukti transfer</div>
      <div class="upload-sub">JPG, PNG, WEBP — maks. 5 MB</div>
    </div>

    <img id="proof-preview" class="bjf-preview-img" alt="Preview bukti bayar">

    <div id="payment-error" class="bjf-error-box"></div>

    <button id="submit-proof" class="bjf-btn bjf-btn-accent" disabled>
      📤 Kirim Bukti Pembayaran
    </button>
    <p class="bjf-hint">Kode pesanan: <strong id="order-code-display"></strong></p>
  </div>
</div>

<!-- MODAL 3: Sukses -->
<div id="success-modal" class="bjf-modal-overlay">
  <div class="bjf-modal-box" style="max-width:400px;">
    <div class="bjf-success-box">
      <div class="bjf-success-icon">✅</div>
      <div class="bjf-modal-title" style="font-size:22px;">Bukti Terkirim!</div>
      <p style="font-size:13px;color:#767370;margin-top:8px;">
        Bukti pembayaran kamu sudah diterima. Admin akan mengkonfirmasi dalam 1×24 jam.
      </p>
      <div class="bjf-success-code" id="success-order-code"></div>
      <p style="font-size:12px;color:#767370;margin-bottom:16px;">
        Simpan kode pesanan ini untuk cek status di halaman profil.
      </p>
      <button class="bjf-btn bjf-btn-dark" id="close-success-modal" style="max-width:200px;margin:0 auto;">
        Selesai
      </button>
    </div>
  </div>
</div>
`;

document.body.insertAdjacentHTML('beforeend', checkoutModalHTML);

// ── STATE ─────────────────────────────────────────────────────────────────────
var bjfState = {
  orderId: null,
  orderCode: null,
  total: 0,
  proofFile: null,
};

// ── ELEMENT REFS ──────────────────────────────────────────────────────────────
var checkoutModal  = document.getElementById('checkout-modal');
var paymentModal   = document.getElementById('payment-modal');
var successModal   = document.getElementById('success-modal');
var uploadArea     = document.getElementById('upload-area');
var proofFileInput = document.getElementById('proof-file');
var proofPreview   = document.getElementById('proof-preview');
var submitProofBtn = document.getElementById('submit-proof');
var paymentErrEl   = document.getElementById('payment-error');

// ── HELPER ────────────────────────────────────────────────────────────────────
function fmt(n) {
  return 'Rp ' + parseInt(n).toLocaleString('id-ID');
}

function showError(el, msg) {
  el.style.display = 'block';
  el.textContent = msg;
}

function hideError(el) {
  el.style.display = 'none';
  el.textContent = '';
}

// ── BUKA CHECKOUT ─────────────────────────────────────────────────────────────
var checkoutBtn = document.getElementById('checkout');
if (checkoutBtn) {
  checkoutBtn.addEventListener('click', function() {
    // Ambil cart dari state global script.js
    var cart = (typeof window.getCartItems === 'function') ? window.getCartItems() : [];

    if (!cart || cart.length === 0) {
      alert('Keranjang kamu masih kosong!');
      return;
    }

    // Bangun summary
    var summaryHtml = '';
    var total = 0;
    cart.forEach(function(item) {
      var sub = item.price * item.qty;
      total += sub;
      summaryHtml += '<div class="bjf-order-row"><span>' + item.name + ' x' + item.qty + '</span><span>' + fmt(sub) + '</span></div>';
    });
    summaryHtml += '<div class="bjf-order-total"><span>Total</span><span>' + fmt(total) + '</span></div>';
    document.getElementById('checkout-summary').innerHTML = summaryHtml;
    bjfState.total = total;

    checkoutModal.classList.add('open');
    document.body.style.overflow = 'hidden';
  });
}

// Tutup modal checkout
document.getElementById('close-checkout-modal').addEventListener('click', function() {
  checkoutModal.classList.remove('open');
  document.body.style.overflow = '';
});
checkoutModal.addEventListener('click', function(e) {
  if (e.target === checkoutModal) {
    checkoutModal.classList.remove('open');
    document.body.style.overflow = '';
  }
});

// ── SUBMIT CHECKOUT ──────────────────────────────────────────────────────────
document.getElementById('submit-checkout').addEventListener('click', function() {
  var errEl = document.getElementById('checkout-error');
  hideError(errEl);

  var name    = document.getElementById('co-name').value.trim();
  var phone   = document.getElementById('co-phone').value.trim();
  var address = document.getElementById('co-address').value.trim();
  var notes   = document.getElementById('co-notes').value.trim();

  if (!name)    { showError(errEl, 'Nama lengkap wajib diisi.'); return; }
  if (!phone)   { showError(errEl, 'Nomor telepon wajib diisi.'); return; }
  if (!address) { showError(errEl, 'Alamat pengiriman wajib diisi.'); return; }

  var cart = (typeof window.getCartItems === 'function') ? window.getCartItems() : [];

  var items = cart.map(function(item) {
    return { id: item.id, qty: item.qty };
  });

  var btn = document.getElementById('submit-checkout');
  btn.disabled = true;
  btn.textContent = 'Memproses...';

  fetch('api_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name: name,
      phone: phone,
      address: address,
      notes: notes,
      items: items,
      payment_method: 'qris'
    })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    btn.disabled = false;
    btn.textContent = 'Lanjut ke Pembayaran QRIS →';

    if (!data.ok) {
      showError(errEl, data.message || 'Terjadi kesalahan. Coba lagi.');
      return;
    }

    bjfState.orderId   = data.order_id;
    bjfState.orderCode = data.order_code;
    bjfState.total     = data.total;

    // Tutup checkout, buka payment
    checkoutModal.classList.remove('open');
    openQrisModal();
  })
  .catch(function() {
    btn.disabled = false;
    btn.textContent = 'Lanjut ke Pembayaran QRIS →';
    showError(errEl, 'Koneksi gagal. Periksa internet dan coba lagi.');
  });
});

// ── BUKA QRIS MODAL ──────────────────────────────────────────────────────────
function openQrisModal() {
  document.getElementById('qris-img').src = QRIS_IMAGE_URL;
  document.getElementById('qris-amount').textContent = fmt(bjfState.total);
  document.getElementById('order-code-display').textContent = bjfState.orderCode;

  // Reset upload area
  proofFileInput.value = '';
  proofPreview.style.display = 'none';
  bjfState.proofFile = null;
  submitProofBtn.disabled = true;
  hideError(paymentErrEl);

  paymentModal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

// Tutup modal payment
document.getElementById('close-payment-modal').addEventListener('click', function() {
  if (confirm('Tutup halaman pembayaran? Pesanan sudah tersimpan dengan kode: ' + bjfState.orderCode + '. Kamu masih bisa upload bukti nanti di halaman profil.')) {
    paymentModal.classList.remove('open');
    document.body.style.overflow = '';
  }
});

// ── UPLOAD BUKTI ──────────────────────────────────────────────────────────────
uploadArea.addEventListener('click', function() {
  proofFileInput.click();
});

uploadArea.addEventListener('dragover', function(e) {
  e.preventDefault();
  uploadArea.style.borderColor = '#A34E22';
});

uploadArea.addEventListener('dragleave', function() {
  uploadArea.style.borderColor = '#E4E0D8';
});

uploadArea.addEventListener('drop', function(e) {
  e.preventDefault();
  uploadArea.style.borderColor = '#E4E0D8';
  var file = e.dataTransfer.files[0];
  if (file) handleFile(file);
});

proofFileInput.addEventListener('change', function() {
  if (this.files[0]) handleFile(this.files[0]);
});

function handleFile(file) {
  var allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  if (!allowed.includes(file.type)) {
    showError(paymentErrEl, 'Format tidak didukung. Gunakan JPG, PNG, atau WEBP.');
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    showError(paymentErrEl, 'Ukuran file maksimal 5 MB.');
    return;
  }
  hideError(paymentErrEl);
  bjfState.proofFile = file;
  submitProofBtn.disabled = false;

  var reader = new FileReader();
  reader.onload = function(e) {
    proofPreview.src = e.target.result;
    proofPreview.style.display = 'block';
  };
  reader.readAsDataURL(file);
}

// ── KIRIM BUKTI ───────────────────────────────────────────────────────────────
submitProofBtn.addEventListener('click', function() {
  if (!bjfState.proofFile) {
    showError(paymentErrEl, 'Pilih foto bukti pembayaran terlebih dahulu.');
    return;
  }

  var fd = new FormData();
  fd.append('order_id',   bjfState.orderId);
  fd.append('order_code', bjfState.orderCode);
  fd.append('proof_image', bjfState.proofFile);

  submitProofBtn.disabled = true;
  submitProofBtn.textContent = '⏳ Mengirim...';
  hideError(paymentErrEl);

  fetch('api_upload_proof.php', { method: 'POST', body: fd })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    submitProofBtn.disabled = false;
    submitProofBtn.textContent = '📤 Kirim Bukti Pembayaran';

    if (!data.ok) {
      showError(paymentErrEl, data.message || 'Upload gagal. Coba lagi.');
      return;
    }

    // Tutup payment modal, buka sukses
    paymentModal.classList.remove('open');
    document.getElementById('success-order-code').textContent = bjfState.orderCode;
    successModal.classList.add('open');

    // Kosongkan cart
    if (typeof clearCart === 'function') clearCart();
    else if (typeof window.cart !== 'undefined') window.cart = [];
  })
  .catch(function() {
    submitProofBtn.disabled = false;
    submitProofBtn.textContent = '📤 Kirim Bukti Pembayaran';
    showError(paymentErrEl, 'Koneksi gagal. Coba lagi.');
  });
});

// ── TUTUP SUKSES ─────────────────────────────────────────────────────────────
document.getElementById('close-success-modal').addEventListener('click', function() {
  successModal.classList.remove('open');
  document.body.style.overflow = '';
  // Reload untuk refresh cart UI
  window.location.reload();
});
