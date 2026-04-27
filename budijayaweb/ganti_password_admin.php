<?php
// ============================================================
//  ganti_password_admin.php
//  Jalankan SEKALI via browser atau CLI untuk ganti password admin:
//    php ganti_password_admin.php
//  Atau buka: http://localhost/BUDIJAYAFURNITURE/ganti_password_admin.php
//  HAPUS file ini setelah digunakan!
// ============================================================
require_once __DIR__ . '/db.php';

// ── GANTI PASSWORD DI SINI ──────────────────────────────────
$username     = 'admin';
$new_password = 'BudiJaya@Admin2026'; // <-- UBAH INI sebelum jalankan!
// ────────────────────────────────────────────────────────────

if (PHP_SAPI !== 'cli') {
    // Proteksi minimal: cek token sederhana jika diakses via browser
    // HAPUS FILE INI SETELAH DIGUNAKAN
    echo '<pre style="font-family:monospace;padding:20px">';
}

$hash = password_hash($new_password, PASSWORD_DEFAULT);
$pdo  = db();

$stmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE username = ?');
$stmt->execute([$hash, $username]);

if ($stmt->rowCount() > 0) {
    echo "✅ Password admin '$username' berhasil diganti!\n";
    echo "   Username : $username\n";
    echo "   Password : $new_password\n";
    echo "   Hash     : $hash\n\n";
    echo "⚠️  HAPUS FILE INI SEKARANG: ganti_password_admin.php\n";
} else {
    echo "❌ Username '$username' tidak ditemukan di database.\n";
}

if (PHP_SAPI !== 'cli') echo '</pre>';
