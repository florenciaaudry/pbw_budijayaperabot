<?php
require_once __DIR__ . '/auth_admin.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_verify();

$pdo    = db();
$msg    = '';
$msgType = 'success';

// ── HANDLE ACTIONS ──────────────────────────────────────────
$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'edit') {
    $name    = trim($_POST['name'] ?? '');
    $cat     = $_POST['category'] ?? '';
    $price   = (int)($_POST['price'] ?? 0);
    $stock   = (int)($_POST['stock'] ?? 0);
    $desc    = trim($_POST['description'] ?? '');
    $imgKey  = trim($_POST['img_key'] ?? '');
    $cats    = ['Sofa','Kursi','Meja','Lemari','Rak'];

    if ($name === '' || !in_array($cat, $cats) || $price < 0) {
        $msg = 'Data tidak lengkap atau tidak valid.'; $msgType = 'error';
    } elseif ($action === 'add') {
        $pdo->prepare(
            'INSERT INTO products (name, category, price, description, img_key, stock) VALUES (?,?,?,?,?,?)'
        )->execute([$name, $cat, $price, $desc, $imgKey, $stock]);
        $msg = "Produk \"$name\" berhasil ditambahkan.";
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare(
            'UPDATE products SET name=?, category=?, price=?, description=?, img_key=?, stock=? WHERE id=?'
        )->execute([$name, $cat, $price, $desc, $imgKey, $stock, $id]);
        $msg = "Produk berhasil diperbarui.";
    }
}

if ($action === 'toggle') {
    $id  = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE products SET is_active = IF(is_active=1,0,1) WHERE id=?')->execute([$id]);
    $msg = 'Status produk diperbarui.';
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
    $msg = 'Produk dihapus.';
}

// ── FETCH ────────────────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['p'] ?? 1));
$limit  = 12;
$offset = ($page - 1) * $limit;

$where  = $search ? 'WHERE name LIKE ?' : '';
$params = $search ? ["%$search%"] : [];

$total   = $pdo->prepare("SELECT COUNT(*) FROM products $where");
$total->execute($params);
$total   = (int)$total->fetchColumn();
$pages   = max(1, (int)ceil($total / $limit));

$stmt = $pdo->prepare("SELECT * FROM products $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Edit target
$editProduct = null;
if (isset($_GET['edit'])) {
    $es = $pdo->prepare('SELECT * FROM products WHERE id=?');
    $es->execute([(int)$_GET['edit']]);
    $editProduct = $es->fetch();
}

$cats       = ['Sofa','Kursi','Meja','Lemari','Rak'];
$pageTitle  = 'Produk';
$activePage = 'products';
include '_layout_top.php';
?>

<?php if ($msg): ?>
  <div class="alert alert-<?= $msgType ==='error' ? 'error':'success' ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

  <!-- TABLE -->
  <div class="card">
    <div class="card-header">
      <h2>Semua Produk <span style="font-size:13px;color:var(--muted);font-family:var(--font-body);">(<?= $total ?>)</span></h2>
      <form method="GET" style="display:flex;gap:8px;align-items:center;">
        <div class="search-bar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari produk...">
        </div>
        <button type="submit" class="btn btn-outline btn-sm">Cari</button>
        <?php if ($search): ?><a href="products.php" class="btn btn-outline btn-sm">Reset</a><?php endif; ?>
      </form>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th><th>Nama</th><th>Kategori</th><th>Harga</th>
            <th>Stok</th><th>Status</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($products)): ?>
            <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--muted);">Tidak ada produk.</td></tr>
          <?php endif; ?>
          <?php foreach ($products as $p): ?>
          <tr>
            <td style="color:var(--muted);"><?= $p['id'] ?></td>
            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
            <td><?= htmlspecialchars($p['category']) ?></td>
            <td>Rp <?= number_format($p['price'],0,',','.') ?></td>
            <td><?= $p['stock'] ?></td>
            <td>
              <span class="badge <?= $p['is_active'] ? 'badge-active':'badge-inactive' ?>">
                <?= $p['is_active'] ? 'Aktif':'Nonaktif' ?>
              </span>
            </td>
            <td>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <a href="?edit=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                <form method="POST" style="display:inline;">
        <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm">
                    <?= $p['is_active'] ? 'Nonaktifkan':'Aktifkan' ?>
                  </button>
                </form>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('Hapus produk ini? Data tidak bisa dikembalikan.')">
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

  <!-- FORM TAMBAH / EDIT -->
  <div class="card">
    <div class="card-header">
      <h2><?= $editProduct ? 'Edit Produk' : 'Tambah Produk' ?></h2>
      <?php if ($editProduct): ?>
        <a href="products.php" class="btn btn-outline btn-sm">+ Tambah Baru</a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $editProduct ? 'edit':'add' ?>">
        <?php if ($editProduct): ?>
          <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Nama Produk *</label>
          <input class="form-control" name="name" required
                 value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Kategori *</label>
          <select class="form-control" name="category" required>
            <option value="">-- Pilih kategori --</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= $c ?>" <?= ($editProduct['category']??'')===$c ? 'selected':'' ?>>
                <?= $c ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Harga (Rp) *</label>
            <input class="form-control" name="price" type="number" min="0" required
                   value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Stok</label>
            <input class="form-control" name="stock" type="number" min="0"
                   value="<?= htmlspecialchars($editProduct['stock'] ?? '0') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Deskripsi</label>
          <textarea class="form-control" name="description"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Image Key <small style="text-transform:none;letter-spacing:0;opacity:.6;">(nama file tanpa ekstensi, di folder gambar/)</small></label>
          <input class="form-control" name="img_key" placeholder="contoh: kursikayu"
                 value="<?= htmlspecialchars($editProduct['img_key'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-accent" style="width:100%;">
          <?= $editProduct ? 'Simpan Perubahan' : 'Tambah Produk' ?>
        </button>
      </form>
    </div>
  </div>

</div>

<?php include '_layout_bot.php'; ?>
