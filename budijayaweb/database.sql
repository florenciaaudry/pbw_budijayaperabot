-- ============================================================
--  BUDI JAYA FURNITURE — Database Schema (QRIS Only Edition)
--  Database: budijaya_furniture
--  Import file ini di phpMyAdmin:
--  Database → Import → pilih file ini → Go
-- ============================================================

CREATE DATABASE IF NOT EXISTS budijaya_furniture
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE budijaya_furniture;

-- -------------------------------------------------------
-- 1. ADMIN
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username     VARCHAR(60)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default: username=admin, password=BudiJaya@Admin2026
-- Hash bcrypt cost 12 — VALID, langsung bisa login
INSERT INTO admins (username, password_hash) VALUES
  ('admin', '$2y$12$t/qnUzDbKPTRSHLlHXAlB.57CoRifDb8pVdEW4rKZNhHrf1m1OAbW');
-- Untuk ganti password: jalankan ganti_password_admin.php

-- -------------------------------------------------------
-- 2. CUSTOMERS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(120) NOT NULL,
  email        VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone        VARCHAR(30)  DEFAULT NULL,
  address      TEXT         DEFAULT NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 3. PRODUK
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(180) NOT NULL,
  category     ENUM('Sofa','Kursi','Meja','Lemari','Rak') NOT NULL,
  price        INT UNSIGNED NOT NULL,
  description  TEXT         DEFAULT NULL,
  img_key      VARCHAR(120) DEFAULT NULL,
  stock        INT          NOT NULL DEFAULT 1,
  is_active    TINYINT(1)   NOT NULL DEFAULT 1,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (name, category, price, description, img_key, stock) VALUES
  ('Kursi Plastik',           'Kursi',  90000,   'Kursi plastik serbaguna, cocok untuk indoor maupun outdoor.',                 'kursiplastik',         50),
  ('Kursi Santai',            'Kursi',  245000,  'Kursi santai dengan sandaran nyaman untuk ruang tamu atau teras.',            'kursisantai',          20),
  ('Lemari Pakaian 2 Pintu',  'Lemari', 799000,  'Lemari pakaian 2 pintu dengan ruang gantung dan rak lipat.',                 'lemaripakaian2p',      15),
  ('Lemari Pakaian 3 Pintu',  'Lemari', 1199000, 'Lemari pakaian 3 pintu kapasitas besar, cocok untuk keluarga.',              'lemaripakaian3p',      10),
  ('Meja Belajar Minimalis',  'Meja',   330000,  'Meja belajar minimalis dengan ruang untuk buku dan perlengkapan tulis.',     'mejabelajarminimalis', 25),
  ('Meja Lipat Serbaguna',    'Meja',   120000,  'Meja lipat praktis, mudah disimpan dan dipindahkan.',                        'mejalipat',            30),
  ('Meja Makan Set 4 Kursi',  'Meja',   1850000, 'Satu set meja makan dengan 4 kursi, cocok untuk ruang makan keluarga.',      'mejamakan4',           8),
  ('Meja TV Minimalis',       'Meja',   359000,  'Meja TV minimalis dengan rak penyimpanan di bawah.',                         'mejatvminimalis',      18),
  ('Rak Buku',                'Rak',    259000,  'Rak buku sederhana untuk kamar atau ruang kerja.',                           'rakbuku',              22),
  ('Rak Sepatu Kecil',        'Rak',    149000,  'Rak sepatu hemat tempat, cocok diletakkan dekat pintu masuk.',               'raksepatukecil',       35),
  ('Rak Serbaguna 4 Susun',   'Rak',    189000,  'Rak serbaguna 4 susun untuk dapur, kamar mandi, atau ruang keluarga.',      'rakserbaguna4susun',   28),
  ('Sofa L Minimalis',        'Sofa',   1999000, 'Sofa L minimalis yang nyaman untuk ruang keluarga.',                         'sofaL',                5);

-- -------------------------------------------------------
-- 4. ORDERS (QRIS Only)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_code       VARCHAR(30)  NOT NULL UNIQUE,
  customer_id      INT UNSIGNED DEFAULT NULL,
  customer_name    VARCHAR(120) NOT NULL,
  customer_phone   VARCHAR(30)  NOT NULL,
  delivery_address TEXT         NOT NULL,
  notes            TEXT         DEFAULT NULL,
  payment_method   ENUM('qris') NOT NULL DEFAULT 'qris',
  payment_status   ENUM('unpaid','paid','rejected') NOT NULL DEFAULT 'unpaid',
  payment_proof    VARCHAR(255) DEFAULT NULL,
  payment_proof_at DATETIME     DEFAULT NULL,
  status           ENUM('pending','confirmed','processing','delivered','cancelled')
                               NOT NULL DEFAULT 'pending',
  total_amount     INT UNSIGNED NOT NULL DEFAULT 0,
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- 5. ORDER ITEMS
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id     INT UNSIGNED NOT NULL,
  product_id   INT UNSIGNED DEFAULT NULL,
  product_name VARCHAR(180) NOT NULL,
  unit_price   INT UNSIGNED NOT NULL,
  qty          INT UNSIGNED NOT NULL DEFAULT 1,
  subtotal     INT UNSIGNED NOT NULL,
  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
