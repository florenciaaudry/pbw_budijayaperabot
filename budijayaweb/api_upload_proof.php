<?php
// ============================================================
//  api_upload_proof.php
//  POST multipart/form-data: order_id, order_code, proof_image
//  → simpan file → update tabel orders
// ============================================================
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$orderId   = (int)($_POST['order_id']   ?? 0);
$orderCode = trim($_POST['order_code'] ?? '');

if ($orderId < 1 || $orderCode === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'order_id dan order_code wajib diisi.']);
    exit;
}

// Cek order ada dan belum upload
$pdo  = db();
$stmt = $pdo->prepare('SELECT id, payment_status, payment_proof FROM orders WHERE id=? AND order_code=?');
$stmt->execute([$orderId, $orderCode]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Pesanan tidak ditemukan.']);
    exit;
}

if ($order['payment_proof'] !== null && $order['payment_status'] === 'paid') {
    echo json_encode(['ok' => false, 'message' => 'Pembayaran sudah dikonfirmasi.']);
    exit;
}

// Validasi file upload
if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
    $errMap = [
        UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
        UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar.',
        UPLOAD_ERR_PARTIAL    => 'File upload tidak lengkap.',
        UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara tidak ditemukan.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menyimpan file.',
    ];
    $errCode = $_FILES['proof_image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $errMsg  = $errMap[$errCode] ?? 'Terjadi kesalahan upload.';
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => $errMsg]);
    exit;
}

$file     = $_FILES['proof_image'];
$maxSize  = 5 * 1024 * 1024; // 5 MB

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Ukuran file maksimal 5 MB.']);
    exit;
}

// Validasi tipe via finfo (bukan ekstensi, aman dari spoofing)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mime     = $finfo->file($file['tmp_name']);
$allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.']);
    exit;
}

$ext     = $allowed[$mime];
$dir     = __DIR__ . '/uploads/payment_proofs/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

// Hapus file lama jika ada
if ($order['payment_proof'] && file_exists(__DIR__ . '/' . $order['payment_proof'])) {
    @unlink(__DIR__ . '/' . $order['payment_proof']);
}

$filename  = 'proof_' . $orderCode . '_' . time() . '.' . $ext;
$savePath  = $dir . $filename;
$dbPath    = 'uploads/payment_proofs/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Gagal menyimpan file. Periksa izin folder.']);
    exit;
}

// Update database
$pdo->prepare(
    "UPDATE orders SET payment_proof=?, payment_proof_at=NOW(), payment_status='unpaid', updated_at=NOW() WHERE id=?"
)->execute([$dbPath, $orderId]);

echo json_encode([
    'ok'      => true,
    'message' => 'Bukti pembayaran berhasil dikirim! Admin akan segera mengkonfirmasi.',
]);
