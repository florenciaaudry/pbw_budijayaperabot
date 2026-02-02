<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Perabot Budi Jaya Marelan</title>
  <meta name="description" content="Toko perabot & furnitur rumah di Medan Marelan. Lemari, kursi, meja, sofa dan rak untuk kebutuhan rumah.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- HEADER -->
  <header class="site-header">
    <div class="container nav">
      <div class="brand" aria-label="Beranda">
        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M3 12l9-8 9 8v8a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2v-8z"/>
        </svg>
        <div class="brand-text">
          <span class="brand-title">Perabot Budi Jaya</span>
          <span class="brand-sub">Marelan, Medan</span>
        </div>
      </div>

      <nav class="nav-links" aria-label="Navigasi utama">
        <a href="#home" class="active">Home</a>
        <a href="#about">Tentang</a>
        <a href="#produk">Produk</a>
        <a href="#contact">Kontak</a>
      </nav>

      <div class="nav-actions">
        <button class="btn ghost" id="btn-fav">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 21s-8-4.438-8-11a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 6.562-8 11-8 11z"/>
          </svg>
          <span>Favorit</span>
          <span class="badge-counter" id="fav-count">0</span>
        </button>

        <button class="btn primary" id="btn-cart">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M7 4h-2l-1 2h2l3.6 7.59-1.35 2.44A2 2 0 0 0 10 19h9v-2h-8.42a.25.25 0 0 1-.22-.37L11 14h6a2 2 0 0 0 1.8-1.1l3.6-7.2H7z"/>
          </svg>
          <span>Keranjang</span>
          <span class="badge-counter" id="cart-count">0</span>
        </button>
      </div>
    </div>
  </header>