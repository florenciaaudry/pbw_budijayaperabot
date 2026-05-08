<?php
// ============================================================
//  config.php — Konfigurasi aplikasi
//  JANGAN di-commit ke git. Tambahkan ke .gitignore.
//  Letakkan file ini satu level DI ATAS web root jika bisa,
//  atau pastikan file ini tidak bisa diakses langsung via browser.
// ============================================================

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'budijaya_furniture');  
define('DB_USER', 'root');
define('DB_PASS', '');  // 
define('DB_CHARSET', 'utf8mb4');

// Aplikasi
define('APP_NAME',   'Budi Jaya Furniture');
define('WA_ADMIN',   '6285260835353');  

// Keamanan
define('CSRF_SECRET', 'BudiJaya$Xk92mNpQ7vLw3rT8uZ5aE1cF6hY4j');
