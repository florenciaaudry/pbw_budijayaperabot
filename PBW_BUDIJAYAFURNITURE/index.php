<?php include "header.php"; ?>

<main>

  <!-- HERO -->
  <section id="home" class="section hero-section">
    <div class="hero-bg"></div>
    <div class="container hero">
      <div class="hero-text">
        <p class="kicker">Perabot Harian Marelan</p>
        <h1 class="hero-title">Perabot sederhana untuk rumah, kos, dan ruko di Marelan.</h1>
        <p class="hero-lead">
          Lemari, meja, kursi, rak, dan sofa dengan ukuran realistis untuk
          ruangan di Medan Marelan. Fokus ke perabot harian supaya tetap ramah di kantong.
        </p>

        <div class="hero-actions">
          <button class="btn primary" id="cta-jelajah" type="button">Lihat Koleksi</button>
          <button class="btn outline" type="button"
            onclick="document.getElementById('kontak').scrollIntoView({behavior:'smooth',block:'start'})">
            Tanya stok & harga
          </button>
        </div>

        <div class="hero-stats">
          <div>
            <strong>50+</strong>
            <span>Jenis perabot</span>
          </div>
          <div>
            <strong>10+ tahun</strong>
            <span>Melayani Marelan</span>
          </div>
          <div>
            <strong>Fokus lokal</strong>
            <span>Showroom Rengas Pulau</span>
          </div>
        </div>
      </div>

      <div class="hero-media">
        <div class="hero-media-bg"></div>

        <article class="hero-product-card">
          <div class="hero-product-photo" aria-hidden="true"></div>

          <div class="hero-product-overlay">
            <div class="hero-product-text">
              <p class="hero-product-tag">Koleksi kursi plastik</p>
              <h2 class="hero-product-name">Kursi plastik harian</h2>
              <p class="hero-product-sub">Cocok untuk ruang makan, teras, acara keluarga, dan usaha kecil.</p>
            </div>

            <div class="hero-product-price">
              <span>Mulai</span>
              <strong>Rp90.000</strong>
            </div>
          </div>
        </article>

        <div class="hero-thumbs">
          <div class="hero-thumb">
            <span class="hero-thumb-title">Lemari</span>
            <span class="hero-thumb-sub">Pakaian & penyimpanan</span>
          </div>
          <div class="hero-thumb">
            <span class="hero-thumb-title">Meja & kursi</span>
            <span class="hero-thumb-sub">Belajar & ruang tamu</span>
          </div>
          <div class="hero-thumb">
            <span class="hero-thumb-title">Rak</span>
            <span class="hero-thumb-sub">Buku & serbaguna</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section id="tentang" class="section section-about">
    <div class="container about">
      <div class="about-text">
        <h2 class="section-title">Tentang Perabot Budi Jaya Marelan</h2>
        <p class="section-body">
          Perabot Budi Jaya Marelan adalah toko perabot lokal di Medan Marelan
          yang menyediakan lemari, kursi, meja, sofa, dan rak dengan harga yang
          masih terjangkau untuk keluarga, anak kos, maupun pelaku usaha kecil.
        </p>
        <ul class="about-list">
          <li>Cocok untuk rumah, kos, dan ruko.</li>
          <li>Model sederhana & kekinian, stok rutin diperbarui.</li>
          <li>Info stok dan warna lengkap melalui Instagram.</li>
        </ul>

        <div class="about-features">
          <div class="about-feature">
            <div class="about-icon">💳</div>
            <div>
              <strong>Pembelian fleksibel</strong>
              <p>Bisa beli satuan atau borongan sesuai kebutuhan.</p>
            </div>
          </div>
          <div class="about-feature">
            <div class="about-icon">🚚</div>
            <div>
              <strong>Pengantaran sekitar Marelan</strong>
              <p>Pengiriman diatur langsung dengan admin toko.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="about-media" aria-hidden="true">
        <div class="about-media-bg"></div>
        <div class="about-photo"></div>
      </div>
    </div>
  </section>

  <!-- PRODUK -->
  <section id="produk" class="section section-products">
    <div class="container">
      <div class="produk-header">
        <div>
          <h2 class="section-title">Produk Pilihan</h2>
          <p class="section-body">
            Contoh produk yang biasa ada di Perabot Budi Jaya Marelan.
            Untuk model lengkap dan update stok, silakan cek Instagram toko.
          </p>
        </div>
      </div>

      <div class="produk-toolbar">
        <div class="search" role="search">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M10 4a6 6 0 1 1 0 12 6 6 6 0 0 1 0-12zm8.7 13.3-3-3"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <input id="q" type="search" placeholder="Cari kursi, lemari, rak..." aria-label="Cari produk">
        </div>

        <div class="toolbar-right">
          <select id="sort" class="select" aria-label="Urutkan harga">
            <option value="default">Urutkan</option>
            <option value="price_asc">Harga: termurah</option>
            <option value="price_desc">Harga: termahal</option>
          </select>
        </div>

        <div class="chips" aria-label="Filter kategori (multi-select)">
          <button class="chip active" data-cat="all">Semua</button>
          <button class="chip" data-cat="Sofa">Sofa</button>
          <button class="chip" data-cat="Kursi">Kursi</button>
          <button class="chip" data-cat="Meja">Meja</button>
          <button class="chip" data-cat="Lemari">Lemari</button>
          <button class="chip" data-cat="Rak">Rak</button>
        </div>
      </div>

      <section id="koleksi" class="product-grid" aria-live="polite"></section>
      <div id="pager" class="pager" aria-label="Pagination"></div>

      <div class="info-strip">
        <span>✅ Harga dapat berubah mengikuti stok & promo di toko.</span>
        <span>📦 Detail ukuran & warna bisa ditanyakan lewat WhatsApp / Instagram.</span>
        <span>📍 Showroom: Jl. Marelan VII, Rengas Pulau – Medan Marelan.</span>
      </div>
    </div>
  </section>

  <!-- DRAWER KERANJANG -->
  <aside id="cart-drawer" class="drawer" role="dialog" aria-modal="true" aria-labelledby="cart-title">
    <div class="backdrop" data-close></div>
    <div class="panel">
      <header>
        <strong id="cart-title">Keranjang</strong>
        <button class="btn outline" type="button" data-close>Tutup</button>
      </header>

      <div id="cart-list" class="cart-list"></div>

      <footer>
        <div class="total-row">
          <span>Subtotal</span>
          <strong id="subtotal">Rp0</strong>
        </div>
        <button class="btn primary" id="checkout" type="button">Checkout via WhatsApp</button>
      </footer>
    </div>
  </aside>

  <!-- DRAWER FAVORIT -->
  <aside id="fav-drawer" class="drawer" role="dialog" aria-modal="true" aria-labelledby="fav-title">
    <div class="backdrop" data-close></div>
    <div class="panel">
      <header>
        <strong id="fav-title">Produk Favorit</strong>
        <button class="btn outline" type="button" data-close>Tutup</button>
      </header>

      <div id="fav-list" class="cart-list"></div>
    </div>
  </aside>

  <!-- MODAL DETAIL PRODUK -->
  <aside id="detail-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="detail-title">
    <div class="backdrop" data-close></div>
    <div class="modal-panel">
      <header>
        <strong id="detail-title">Detail Produk</strong>
        <button class="btn outline" type="button" data-close>Tutup</button>
      </header>
      <div id="detail-body" class="modal-body"></div>
    </div>
  </aside>

  <!-- CONTACT -->
  <section id="kontak" class="section section-contact">
    <div class="container contact">
      <form id="contact-form" class="contact-card">
        <h2 class="section-title">Hubungi Kami</h2>
        <p class="section-body">
          Mau tanya stok, harga paket, atau pesanan banyak? Tulis pesan singkat di sini.
        </p>

        <input class="input" id="name" name="name" placeholder="Nama" required>
        <input class="input" id="email" name="email" placeholder="Email (opsional)">
        <textarea class="input textarea" id="msg" name="msg" placeholder="Pesan..." required></textarea>

        <button class="btn primary" type="submit">Kirim</button>
        <p id="contact-status" class="contact-status"></p>

        <div class="steps-card">
          <h3>Cara pemesanan singkat</h3>
          <ol>
            <li>Pilih produk dan tambahkan ke keranjang.</li>
            <li>Klik <strong>Checkout via WhatsApp</strong>.</li>
            <li>Isi nama & alamat, lalu tunggu konfirmasi admin.</li>
          </ol>
        </div>
      </form>

      <div class="contact-info card">
        <strong>Showroom</strong>
        <p class="lead">Perabot Budi Jaya Marelan</p>
        <p class="lead">
          Jl. Marelan VII, Rengas Pulau<br>
          Medan Marelan, Sumatera Utara
        </p>
        <p class="lead">Jam buka: 10.00 – 21.00</p>
        <p class="lead">
          Instagram:
          <a href="https://www.instagram.com/budijayaperabot/" target="_blank" rel="noopener">
            @budijayaperabot
          </a>
        </p>

        <div class="map-placeholder showroom-photo" aria-hidden="true"></div>
      </div>
    </div>
  </section>

</main>


<?php include "footer.php"; ?>