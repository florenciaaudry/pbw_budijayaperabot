<?php
// ============================================================
//  api_order.php — menerima pesanan dari checkout JS
//  POST JSON → simpan ke database → return JSON
//  Payment method: QRIS only
// ============================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_customer.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Data tidak valid.']);
    exit;
}

// Validasi field wajib
$name    = trim($data['name']    ?? '');
$phone   = trim($data['phone']   ?? '');
$address = trim($data['address'] ?? '');
$items   = $data['items']        ?? [];

// Sanitasi & validasi nomor telepon
$phone = preg_replace('/[^0-9+\-\s()]/', '', $phone);
if (strlen($phone) < 8 || strlen($phone) > 20) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Nomor telepon tidak valid (8-20 karakter angka).']);
    exit;
}

if ($name === '' || $phone === '' || $address === '' || empty($items)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Nama, telepon, alamat, dan item wajib diisi.']);
    exit;
}

// Ambil harga produk dari DB (jangan percaya harga dari client)
$pdo = db();
$ids = array_map(fn($i) => (int)($i['id'] ?? 0), $items);
$ids = array_filter($ids, fn($id) => $id > 0);

if (empty($ids)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Produk tidak ditemukan.']);
    exit;
}

$in   = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($in) AND is_active = 1");
$stmt->execute(array_values($ids));
$prods = [];
foreach ($stmt->fetchAll() as $p) {
    $prods[$p['id']] = $p;
}

// Hitung total & bangun order items
$orderItems  = [];
$totalAmount = 0;

foreach ($items as $item) {
    $pid = (int)($item['id'] ?? 0);
    $qty = max(1, (int)($item['qty'] ?? 1));
    if (!isset($prods[$pid])) continue;

    $price        = (int)$prods[$pid]['price'];
    $sub          = $price * $qty;
    $totalAmount += $sub;
    $orderItems[] = [
        'product_id'   => $pid,
        'product_name' => $prods[$pid]['name'],
        'unit_price'   => $price,
        'qty'          => $qty,
        'subtotal'     => $sub,
    ];
}

if (empty($orderItems)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Tidak ada produk yang valid.']);
    exit;
}

// Buat order code: BJF-YYYYMMDD-XXXX
$datePart  = date('Ymd');
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
$countStmt->execute();
$todayCount = (int)$countStmt->fetchColumn() + 1;
$orderCode  = sprintf('BJF-%s-%04d', $datePart, $todayCount);

// Customer ID jika login
$customerId = customer_is_logged_in() ? (int)$_SESSION['customer']['id'] : null;

// Simpan ke DB dalam transaksi
try {
    $pdo->beginTransaction();

    $pdo->prepare(
        'INSERT INTO orders (order_code, customer_id, customer_name, customer_phone,
          delivery_address, notes, payment_method, payment_status, total_amount)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $orderCode,
        $customerId,
        $name,
        $phone,
        $address,
        trim($data['notes'] ?? ''),
        'qris',
        'unpaid',
        $totalAmount,
    ]);

    $orderId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, unit_price, qty, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($orderItems as $oi) {
        $itemStmt->execute([
            $orderId,
            $oi['product_id'],
            $oi['product_name'],
            $oi['unit_price'],
            $oi['qty'],
            $oi['subtotal'],
        ]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Gagal menyimpan pesanan. Coba lagi.']);
    exit;
}

echo json_encode([
    'ok'          => true,
    'order_code'  => $orderCode,
    'order_id'    => $orderId,
    'total'       => $totalAmount,
]);
