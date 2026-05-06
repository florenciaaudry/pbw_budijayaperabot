<?php
// Wajib login admin sebelum bisa akses halaman ini
require_once __DIR__ . '/auth_admin.php';
require_admin_login();

// Verifikasi CSRF token untuk semua request POST (keamanan form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_verify();

$pdo     = db();
$msg     = '';
$msgType = 'success';

// Kategori produk yang diizinkan — sama dengan di database ENUM
$cats = ['Sofa', 'Kursi', 'Meja', 'Lemari', 'Rak'];

// ──────────────────────────────────────────────────────────────
//  HANDLE AKSI POST (tambah, edit, toggle status, hapus)
// ──────────────────────────────────────────────────────────────
$action = $_POST['action'] ?? '';

// --- TAMBAH atau EDIT produk ---
if ($action === 'add' || $action === 'edit') {
    // Ambil & bersihkan input dari form
    $name   = trim($_POST['name'] ?? '');
    $cat    = $_POST['category'] ?? '';
    $price  = (int)($_POST['price'] ?? 0);
    $stock  = (int)($_POST['stock'] ?? 0);
    $desc   = trim($_POST['description'] ?? '');
    $imgKey = trim($_POST['img_key'] ?? '');

    // Validasi: nama wajib diisi, kategori harus valid, harga tidak boleh negatif
    if ($name === '' || !in_array($cat, $cats) || $price < 0) {
        $msg     = 'Data tidak lengkap atau tidak valid.';
        $msgType = 'error';

    } elseif ($action === 'add') {
        // INSERT produk baru ke database
        $pdo->prepare(
            'INSERT INTO products (name, category, price, description, img_key, stock)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$name, $cat, $price, $desc, $imgKey, $stock]);

        $msg = 'Produk "' . htmlspecialchars($name) . '" berhasil ditambahkan.';

    } else {
        // UPDATE produk yang sudah ada berdasarkan ID
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare(
            'UPDATE products
             SET name=?, category=?, price=?, description=?, img_key=?, stock=?
             WHERE id=?'
        )->execute([$name, $cat, $price, $desc, $imgKey, $stock, $id]);

        $msg = 'Produk berhasil diperbarui.';
    }
}

// --- TOGGLE status aktif/nonaktif produk ---
if ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    // Flip nilai is_active: kalau 1 jadi 0, kalau 0 jadi 1
    $pdo->prepare('UPDATE products SET is_active = IF(is_active=1, 0, 1) WHERE id=?')
        ->execute([$id]);
    $msg = 'Status produk diperbarui.';
}

// --- HAPUS produk ---
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
    $msg = 'Produk berhasil dihapus.';
}

// ──────────────────────────────────────────────────────────────
//  AMBIL DATA PRODUK DENGAN PAGINATION
// ──────────────────────────────────────────────────────────────

// Parameter pencarian dan halaman dari URL (?q=... &p=...)
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['p'] ?? 1));
$limit  = 10;                        // 10 produk per halaman
$offset = ($page - 1) * $limit;     // hitung baris awal

// Jika ada pencarian, tambahkan klausa WHERE
$where  = $search ? 'WHERE name LIKE ?' : '';
$params = $search ? ["%$search%"] : [];

// Hitung total produk (untuk keperluan pagination)
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM products $where");
$stmtCount->execute($params);
$total = (int)$stmtCount->fetchColumn();
$pages = max(1, (int)ceil($total / $limit)); // total halaman

// Ambil produk untuk halaman saat ini
$stmt = $pdo->prepare(
    "SELECT * FROM products $where ORDER BY id DESC LIMIT $limit OFFSET $offset"
);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ──────────────────────────────────────────────────────────────
//  CEK APAKAH ADA PRODUK YANG SEDANG DIEDIT (?edit=ID)
// ──────────────────────────────────────────────────────────────
$editProduct = null;
if (isset($_GET['edit'])) {
    $es = $pdo->prepare('SELECT * FROM products WHERE id=?');
    $es->execute([(int)$_GET['edit']]);
    $editProduct = $es->fetch(); // null jika ID tidak ditemukan
}

// ──────────────────────────────────────────────────────────────
//  RENDER HALAMAN
// ──────────────────────────────────────────────────────────────
$pageTitle  = 'Produk';
$activePage = 'products';
include '_layout_top.php';
?>

<!-- Notifikasi sukses / error setelah aksi POST -->
<?php if ($msg): ?>
  <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>">
    <?= htmlspecialchars($msg) ?>
  </div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start;">

  <!-- ══════════════════════════════════════════════
       KIRI: Tabel daftar produk + pagination
       ══════════════════════════════════════════════ -->
  <div class="card">
    <div class="card-header">
      <h2>
        Semua Produk
        <span style="font-size:13px; color:var(--muted); font-family:var(--font-body);">
          (<?= $total ?> produk<?= $search ? ', filter aktif' : '' ?>)
        </span>
      </h2>

      <!-- Form pencarian produk -->
      <form method="GET" style="display:flex; gap:8px; align-items:center;">
        <div class="search-bar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama produk...">
        </div>
        <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        <?php if ($search): ?>
          <!-- Tombol reset muncul hanya saat ada filter pencarian -->
          <a href="products.php" class="btn btn-outline btn-sm">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Tabel produk -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($products)): ?>
            <!-- Tampilkan pesan kosong jika tidak ada data -->
            <tr>
              <td colspan="7" style="text-align:center; padding:40px; color:var(--muted);">
                <?= $search
                  ? 'Tidak ada produk yang cocok dengan "' . htmlspecialchars($search) . '".'
                  : 'Belum ada produk. Tambahkan melalui form di sebelah kanan.' ?>
              </td>
            </tr>
          <?php endif; ?>

          <?php foreach ($products as $p): ?>
          <tr>
            <td style="color:var(--muted); font-size:12px;"><?= $p['id'] ?></td>
            <td>
              <strong><?= htmlspecialchars($p['name']) ?></strong>
              <?php if ($p['description']): ?>
                <!-- Preview singkat deskripsi (maks 50 karakter) -->
                <div style="font-size:11px; color:var(--muted); margin-top:2px;">
                  <?= htmlspecialchars(mb_strimwidth($p['description'], 0, 50, '…')) ?>
                </div>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p['category']) ?></td>
            <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
            <td>
              <!-- Warnakan stok rendah (≤ 5) agar admin mudah tahu -->
              <span style="<?= $p['stock'] <= 5 ? 'color:var(--error); font-weight:600;' : '' ?>">
                <?= $p['stock'] ?>
              </span>
            </td>
            <td>
              <span class="badge <?= $p['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                <?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </td>
            <td>
              <div style="display:flex; gap:6px; flex-wrap:wrap;">

                <!-- Tombol Edit: bawa ke form edit di kanan (via GET), pertahankan halaman & pencarian -->
                <a href="?edit=<?= $p['id'] ?>&q=<?= urlencode($search) ?>&p=<?= $page ?>"
                   class="btn btn-outline btn-sm">Edit</a>

                <!-- Tombol Toggle Status: aktif jadi nonaktif, atau sebaliknya -->
                <form method="POST" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm">
                    <?= $p['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                  </button>
                </form>

                <!-- Tombol Hapus: minta konfirmasi dulu agar tidak tidak sengaja terhapus -->
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('Hapus produk ini?\nData tidak bisa dikembalikan.')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form>

              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ──── PAGINATION ────
         Tampil hanya jika total halaman lebih dari 1.
         Menampilkan halaman di sekitar halaman aktif (window ±2),
         plus titik-titik jika ada gap agar tidak terlalu panjang. -->
    <?php if ($pages > 1): ?>
    <div style="padding:16px 24px; border-top:1px solid var(--border);">

      <!-- Info berapa item yang sedang ditampilkan -->
      <div style="font-size:12px; color:var(--muted); margin-bottom:10px;">
        <?php
          $from = $offset + 1;
          $to   = min($offset + $limit, $total);
          echo "Menampilkan $from–$to dari $total produk &nbsp;·&nbsp; Halaman $page dari $pages";
        ?>
      </div>

      <div class="pagination">

        <!-- Tombol « Sebelumnya (disable di halaman pertama) -->
        <?php if ($page > 1): ?>
          <a href="?p=<?= $page - 1 ?>&q=<?= urlencode($search) ?>">&laquo;</a>
        <?php endif; ?>

        <?php
        // Tampilkan nomor halaman dengan window ±2 di sekitar halaman aktif
        $window = 2;
        for ($i = 1; $i <= $pages; $i++):
          $inWindow = abs($i - $page) <= $window || $i === 1 || $i === $pages;

          if (!$inWindow):
            // Tampilkan "..." hanya di ujung gap (bukan tiap halaman yang skip)
            if ($i === 2 || $i === $pages - 1):
              echo '<span style="border:none; color:var(--muted); min-width:20px;">…</span>';
            endif;
            continue;
          endif;
        ?>
          <?php if ($i === $page): ?>
            <span class="current"><?= $i ?></span>
          <?php else: ?>
            <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <!-- Tombol » Berikutnya (disable di halaman terakhir) -->
        <?php if ($page < $pages): ?>
          <a href="?p=<?= $page + 1 ?>&q=<?= urlencode($search) ?>">&raquo;</a>
        <?php endif; ?>

      </div>
    </div>
    <?php endif; ?>
  </div>
  <!-- /tabel produk -->


  <!-- ══════════════════════════════════════════════
       KANAN: Form tambah / edit produk
       Sticky agar tidak harus scroll saat tabel panjang
       ══════════════════════════════════════════════ -->
  <div class="card" style="position:sticky; top:20px;">
    <div class="card-header">
      <h2><?= $editProduct ? 'Edit Produk' : 'Tambah Produk' ?></h2>
      <?php if ($editProduct): ?>
        <!-- Tombol kembali ke mode tambah produk baru -->
        <a href="products.php" class="btn btn-outline btn-sm">+ Tambah Baru</a>
      <?php endif; ?>
    </div>

    <div class="card-body">
      <form method="POST">
        <?= csrf_field() ?>

        <!-- Tentukan aksi: 'add' untuk produk baru, 'edit' untuk update -->
        <input type="hidden" name="action" value="<?= $editProduct ? 'edit' : 'add' ?>">

        <?php if ($editProduct): ?>
          <!-- ID produk dibutuhkan agar query UPDATE menyasar baris yang benar -->
          <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
        <?php endif; ?>

        <!-- Nama produk -->
        <div class="form-group">
          <label class="form-label">
            Nama Produk <span style="color:var(--error)">*</span>
          </label>
          <input class="form-control" name="name" required maxlength="180"
                 placeholder="Misal: Kursi Santai Modern"
                 value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
        </div>

        <!-- Kategori (sesuai ENUM di database) -->
        <div class="form-group">
          <label class="form-label">
            Kategori <span style="color:var(--error)">*</span>
          </label>
          <select class="form-control" name="category" required>
            <option value="">-- Pilih kategori --</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= $c ?>"
                <?= ($editProduct['category'] ?? '') === $c ? 'selected' : '' ?>>
                <?= $c ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Harga & Stok dalam satu baris agar lebih ringkas -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
          <div class="form-group">
            <label class="form-label">
              Harga (Rp) <span style="color:var(--error)">*</span>
            </label>
            <input class="form-control" name="price" type="number" min="0" required
                   placeholder="Misal: 299000"
                   value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Stok</label>
            <input class="form-control" name="stock" type="number" min="0"
                   value="<?= htmlspecialchars($editProduct['stock'] ?? '0') ?>">
          </div>
        </div>

        <!-- Deskripsi / bio produk -->
        <div class="form-group">
          <label class="form-label">Deskripsi / Bio Produk</label>
          <textarea class="form-control" name="description"
                    placeholder="Tulis deskripsi singkat produk..."
                    style="min-height:80px;"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
        </div>

        <!-- Image Key: nama file gambar tanpa ekstensi, di folder gambar/ -->
        <div class="form-group">
          <label class="form-label">Image Key
            <small style="text-transform:none; letter-spacing:0; font-weight:400; opacity:.65;">
              (nama file di folder <code>gambar/</code>, tanpa ekstensi)
            </small>
          </label>
          <input class="form-control" name="img_key"
                 placeholder="Contoh: kursisantai"
                 value="<?= htmlspecialchars($editProduct['img_key'] ?? '') ?>">
          <div style="font-size:11px; color:var(--muted); margin-top:5px;">
            Contoh: file <code>gambar/kursisantai.jpg</code> → key: <code>kursisantai</code>
          </div>
        </div>

        <!-- Tombol submit — label & icon menyesuaikan mode tambah / edit -->
        <button type="submit" class="btn btn-accent" style="width:100%; margin-top:4px;">
          <?php if ($editProduct): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>
            Simpan Perubahan
          <?php else: ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Produk
          <?php endif; ?>
        </button>

        <?php if ($editProduct): ?>
          <!-- Tombol batal: kembali ke halaman yang sama tanpa parameter edit -->
          <a href="products.php?p=<?= $page ?>&q=<?= urlencode($search) ?>"
             class="btn btn-outline" style="width:100%; margin-top:8px; justify-content:center;">
            Batal Edit
          </a>
        <?php endif; ?>

      </form>
    </div>
  </div>
  <!-- /form tambah-edit -->

</div>

<?php include '_layout_bot.php'; ?>
