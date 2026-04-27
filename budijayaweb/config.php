<?php
// ============================================================
//  config.php — Konfigurasi aplikasi
//  JANGAN di-commit ke git. Tambahkan ke .gitignore.
//  Letakkan file ini satu level DI ATAS web root jika bisa,
//  atau pastikan file ini tidak bisa diakses langsung via browser.
// ============================================================

// Database
define('DB_HOST',    'sql112.infinityfree.com');
define('DB_NAME',    'if0_41760043_budijaya');
define('DB_USER',    'if0_41760043');        // Ganti dengan user MySQL hosting kamu
define('DB_PASS',    'budijaya2026');            // Ganti dengan password MySQL kamu
define('DB_CHARSET', 'utf8mb4');

// Aplikasi
define('APP_NAME',   'Budi Jaya Furniture');
define('WA_ADMIN',   '6285260835353');  // Nomor WA admin (format internasional)

// Keamanan
define('CSRF_SECRET', 'ganti_dengan_string_acak_panjang_minimal_32_karakter');
