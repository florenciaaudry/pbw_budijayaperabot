// ==========================
// DATA PRODUK
// ==========================

var PRODUCTS = [
  {
    id: 1,
    name: "Kursi Plastik",
    price: 90000,
    cat: "Kursi",
    desc: "Kursi plastik serbaguna, cocok untuk indoor maupun outdoor.",
    img: "kursiplastik"
  },
  {
    id: 2,
    name: "Kursi Santai",
    price: 245000,
    cat: "Kursi",
    desc: "Kursi santai dengan sandaran nyaman untuk ruang tamu atau teras.",
    img: "kursisantai"
  },
  {
    id: 3,
    name: "Lemari Pakaian 2 Pintu",
    price: 799000,
    cat: "Lemari",
    desc: "Lemari pakaian 2 pintu dengan ruang gantung dan rak lipat.",
    img: "lemaripakaian2p"
  },
  {
    id: 4,
    name: "Lemari Pakaian 3 Pintu",
    price: 1199000,
    cat: "Lemari",
    desc: "Lemari pakaian 3 pintu kapasitas besar, cocok untuk keluarga.",
    img: "lemaripakaian3p"
  },
  {
    id: 5,
    name: "Meja Belajar Minimalis",
    price: 330000,
    cat: "Meja",
    desc: "Meja belajar minimalis dengan ruang untuk buku dan perlengkapan tulis.",
    img: "mejabelajarminimalis"
  },
  {
    id: 6,
    name: "Meja Lipat Serbaguna",
    price: 120000,
    cat: "Meja",
    desc: "Meja lipat praktis, mudah disimpan dan dipindahkan.",
    img: "mejalipat"
  },
  {
    id: 7,
    name: "Meja Makan Set 4 Kursi",
    price: 1850000,
    cat: "Meja",
    desc: "Satu set meja makan dengan 4 kursi, cocok untuk ruang makan keluarga.",
    img: "mejamakan4"
  },
  {
    id: 8,
    name: "Meja TV Minimalis",
    price: 359000,
    cat: "Meja",
    desc: "Meja TV minimalis dengan rak penyimpanan di bawah.",
    img: "mejatvminimalis"
  },
  {
    id: 9,
    name: "Rak Buku",
    price: 259000,
    cat: "Rak",
    desc: "Rak buku sederhana untuk kamar atau ruang kerja.",
    img: "rakbuku"
  },
  {
    id: 10,
    name: "Rak Sepatu Kecil",
    price: 149000,
    cat: "Rak",
    desc: "Rak sepatu hemat tempat, cocok diletakkan dekat pintu masuk.",
    img: "raksepatukecil"
  },
  {
    id: 11,
    name: "Rak Serbaguna 4 Susun",
    price: 189000,
    cat: "Rak",
    desc: "Rak serbaguna 4 susun untuk dapur, kamar mandi, atau ruang keluarga.",
    img: "rakserbaguna4susun"
  },
  {
    id: 12,
    name: "Sofa L Minimalis",
    price: 1999000,
    cat: "Sofa",
    desc: "Sofa L minimalis yang nyaman untuk ruang keluarga.",
    img: "sofaL"
  }
];

// nomor WhatsApp toko (format 62... tanpa + dan tanpa 0 depan)
var WA_NUMBER = "62812xxxxxxx"; // GANTI dengan nomor toko beneran

// ==========================
// STATE
// ==========================

var state = {
  search: "",
  category: "all",
  fav: [],   // array id
  cart: {}   // { id: { qty, product } }
};

// ==========================
// DOM ELEMENTS
// ==========================

var grid          = document.getElementById("koleksi");
var searchInput   = document.getElementById("q");
var subtotalEl    = document.getElementById("subtotal");
var cartDrawer    = document.getElementById("cart-drawer");
var favDrawer     = document.getElementById("fav-drawer");
var detailModal   = document.getElementById("detail-modal");
var cartListEl    = document.getElementById("cart-list");
var favListEl     = document.getElementById("fav-list");
var btnCart       = document.getElementById("btn-cart");
var btnFav        = document.getElementById("btn-fav");
var detailBody    = document.getElementById("detail-body");
var yearSpan      = document.getElementById("year");
var contactForm   = document.getElementById("contact-form");
var checkoutBtn   = document.getElementById("checkout");
var ctaBtn        = document.getElementById("cta-jelajah");
var cartCountEl   = document.getElementById("cart-count");
var favCountEl    = document.getElementById("fav-count");

// ==========================
// HELPERS
// ==========================

function formatRupiah(n) {
  return "Rp" + n.toLocaleString("id-ID");
}

function findProduct(id) {
  for (var i = 0; i < PRODUCTS.length; i++) {
    if (PRODUCTS[i].id === id) return PRODUCTS[i];
  }
  return null;
}

function isFavorite(id) {
  return state.fav.indexOf(id) !== -1;
}

function toggleFavorite(id) {
  var index = state.fav.indexOf(id);
  if (index === -1) {
    state.fav.push(id);
  } else {
    state.fav.splice(index, 1);
  }
}

function updateCounters() {
  if (cartCountEl) {
    var keys = Object.keys(state.cart);
    var totalQty = 0;
    for (var i = 0; i < keys.length; i++) {
      var item = state.cart[keys[i]];
      if (!item) continue;
      totalQty += item.qty;
    }
    cartCountEl.textContent = totalQty;
  }

  if (favCountEl) {
    favCountEl.textContent = state.fav.length;
  }
}

// ==========================
// RENDER PRODUCTS
// ==========================

function renderProducts() {
  if (!grid) return;

  var html = "";
  var keyword = state.search.trim().toLowerCase();
  var count = 0;

  for (var i = 0; i < PRODUCTS.length; i++) {
    var p = PRODUCTS[i];

    if (state.category !== "all" && p.cat !== state.category) continue;

    var searchable = (p.name + " " + p.cat).toLowerCase();
    if (keyword !== "" && searchable.indexOf(keyword) === -1) continue;

    count++;

    var favActive = isFavorite(p.id) ? "true" : "false";

    html +=
      '<article class="card product-card" data-id="' + p.id + '" tabindex="0" aria-label="' + p.name + '">' +
        '<div class="thumb" aria-hidden="true" ' +
             'style="background-image:url(\'gambar/' + p.img + '.jpg\');">' +
          '<button class="fav" aria-pressed="' + favActive + '" aria-label="Favoritkan ' + p.name + '" data-fav="' + p.id + '">❤</button>' +
        '</div>' +
        '<div class="info">' +
          '<div class="name">' + p.name + '</div>' +
          '<div class="meta">' +
            '<span>' + p.cat + '</span>' +
            '<strong>' + formatRupiah(p.price) + '</strong>' +
          '</div>' +
          '<button class="link-detail" type="button" data-detail="' + p.id + '">Lihat selengkapnya</button>' +
        '</div>' +
        '<div class="buy">' +
          '<div class="qty" data-qty="' + p.id + '">' +
            '<button data-dec="' + p.id + '" aria-label="Kurangi">−</button>' +
            '<input type="text" value="1" inputmode="numeric" aria-label="Jumlah" />' +
            '<button data-inc="' + p.id + '" aria-label="Tambah">+</button>' +
          '</div>' +
          '<button class="btn primary add" data-add="' + p.id + '">Tambah</button>' +
        '</div>' +
      '</article>';
  }

  if (count === 0) {
    html =
      '<div class="empty-state">' +
        '<strong>Tidak ada produk yang cocok.</strong>' +
        '<div style="margin-top:4px;">Coba hapus filter kategori atau ubah kata kunci pencarian.</div>' +
      '</div>';
  }

  grid.innerHTML = html;
}

// ==========================
// RENDER FAVORITES
// ==========================

function renderFavorites() {
  if (!favListEl) return;

  if (state.fav.length === 0) {
    favListEl.innerHTML =
      '<p style="color:#6b7280;font-size:14px;">Belum ada produk yang difavoritkan. Klik ikon ❤ di card produk untuk menambahkannya.</p>';
    return;
  }

  var html = "";

  for (var i = 0; i < state.fav.length; i++) {
    var id = state.fav[i];
    var p = findProduct(id);
    if (!p) continue;

    html +=
      '<div class="cart-item">' +
        '<div class="thumb" aria-hidden="true" ' +
          'style="background-image:url(\'gambar/' + p.img + '.jpg\');"></div>' +
        '<div>' +
          '<strong style="font-size:15px;">' + p.name + '</strong>' +
          '<div class="meta">' + p.cat + ' • ' + formatRupiah(p.price) + '</div>' +
          '<p style="margin-top:6px;font-size:13px;color:#6b7280;">' + p.desc + '</p>' +
          '<div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">' +
            '<button class="btn outline" data-jump="' + p.id + '">Lihat di Produk</button>' +
          '</div>' +
        '</div>' +
      '</div>';
  }

  favListEl.innerHTML = html;
}

// ==========================
// RENDER CART
// ==========================

function renderCart() {
  if (!cartListEl || !subtotalEl) return;

  var keys = Object.keys(state.cart);

  if (keys.length === 0) {
    cartListEl.innerHTML = "<p>Keranjang masih kosong.</p>";
    subtotalEl.textContent = formatRupiah(0);
    updateCounters();
    return;
  }

  var html = "";
  var subtotal = 0;

  for (var i = 0; i < keys.length; i++) {
    var id = parseInt(keys[i], 10);
    var item = state.cart[id];
    if (!item) continue;

    var p = item.product;
    var qty = item.qty;
    var total = p.price * qty;
    subtotal += total;

    html +=
      '<div class="cart-item" data-id="' + p.id + '">' +
        '<div class="thumb" aria-hidden="true" ' +
          'style="background-image:url(\'gambar/' + p.img + '.jpg\');"></div>' +
        '<div>' +
          '<strong style="font-size:15px;">' + p.name + '</strong>' +
          '<div class="meta">' + p.cat + ' • ' + formatRupiah(p.price) + '</div>' +
          '<div style="margin-top:6px;font-size:13px;color:#6b7280;">Qty: ' + qty + '</div>' +
        '</div>' +
        '<div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;margin-top:4px;">' +
          '<div class="qty" data-cart-qty="' + p.id + '">' +
            '<button data-cart-dec="' + p.id + '">−</button>' +
            '<input type="text" value="' + qty + '" />' +
            '<button data-cart-inc="' + p.id + '">+</button>' +
          '</div>' +
          '<button class="btn outline" data-cart-remove="' + p.id + '">Hapus</button>' +
        '</div>' +
      '</div>';
  }

  cartListEl.innerHTML = html;
  subtotalEl.textContent = formatRupiah(subtotal);
  updateCounters();
}

// ==========================
// DETAIL MODAL
// ==========================

function openDetail(id) {
  if (!detailModal || !detailBody) return;
  var p = findProduct(id);
  if (!p) return;

  var html =
    '<div class="detail-wrapper">' +
      '<div class="detail-thumb" aria-hidden="true" ' +
        'style="background-image:url(\'gambar/' + p.img + '.jpg\');"></div>' +
      '<div class="detail-info">' +
        '<h3 class="detail-name">' + p.name + '</h3>' +
        '<div class="detail-meta">' +
          '<span class="detail-cat">' + p.cat + '</span>' +
          '<span class="detail-price">' + formatRupiah(p.price) + '</span>' +
        '</div>' +
        '<p class="detail-desc">' + p.desc + '</p>' +
        '<ul class="detail-list">' +
          '<li>Cocok untuk rumah, kos, atau usaha kecil.</li>' +
          '<li>Detail ukuran & warna mengikuti stok di toko.</li>' +
          '<li>Pemesanan dan cek stok bisa melalui WhatsApp / Instagram.</li>' +
        '</ul>' +
        '<div class="detail-actions">' +
          '<button class="btn primary" data-add-detail="' + p.id + '">Tambah ke Keranjang</button>' +
        '</div>' +
      '</div>' +
    '</div>';

  detailBody.innerHTML = html;
  detailModal.classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeDetail() {
  if (!detailModal) return;
  detailModal.classList.remove("open");
  document.body.style.overflow = "";
}

// ==========================
// HIGHLIGHT PRODUCT
// ==========================

function highlightProduct(id) {
  var produkSection = document.getElementById("produk");
  if (produkSection) {
    produkSection.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  setTimeout(function () {
    var cards = document.querySelectorAll(".product-card");
    for (var i = 0; i < cards.length; i++) {
      cards[i].classList.remove("highlight");
    }

    var card = document.querySelector('.product-card[data-id="' + id + '"]');
    if (card) {
      card.classList.add("highlight");
      card.scrollIntoView({ behavior: "smooth", block: "center" });

      setTimeout(function () {
        card.classList.remove("highlight");
      }, 2000);
    }
  }, 250);
}

// ==========================
// GRID EVENTS
// ==========================

if (grid) {
  grid.addEventListener("click", function (e) {
    var target = e.target;

    // favorit
    var favBtn = target.closest("[data-fav]");
    if (favBtn) {
      var favId = parseInt(favBtn.getAttribute("data-fav"), 10);
      toggleFavorite(favId);
      renderProducts();
      updateCounters();
      if (favDrawer && favDrawer.classList.contains("open")) {
        renderFavorites();
      }
      return;
    }

    // qty di card
    var inc = target.closest("[data-inc]");
    var dec = target.closest("[data-dec]");
    if (inc || dec) {
      var qId = parseInt((inc || dec).getAttribute(inc ? "data-inc" : "data-dec"), 10);
      var wrapper = grid.querySelector('[data-qty="' + qId + '"]');
      if (!wrapper) return;
      var input = wrapper.querySelector("input");
      var val = parseInt(input.value || "1", 10);
      if (isNaN(val) || val < 1) val = 1;
      if (inc) val++;
      if (dec && val > 1) val--;
      input.value = val;
      return;
    }

    // tambah ke keranjang
    var addBtn = target.closest("[data-add]");
    if (addBtn) {
      var id = parseInt(addBtn.getAttribute("data-add"), 10);
      var prod = findProduct(id);
      if (!prod) return;

      var qtyInput = grid.querySelector('[data-qty="' + id + '"] input');
      var qtyVal = parseInt(qtyInput && qtyInput.value ? qtyInput.value : "1", 10);
      if (isNaN(qtyVal) || qtyVal < 1) qtyVal = 1;

      if (!state.cart[id]) {
        state.cart[id] = { qty: qtyVal, product: prod };
      } else {
        state.cart[id].qty += qtyVal;
      }

      renderCart();
      openCart();
      return;
    }

    // detail
    var detBtn = target.closest("[data-detail]");
    if (detBtn) {
      var did = parseInt(detBtn.getAttribute("data-detail"), 10);
      openDetail(did);
      return;
    }
  });
}

// ==========================
// SEARCH & FILTER
// ==========================

if (searchInput) {
  searchInput.addEventListener("input", function () {
    state.search = searchInput.value;
    renderProducts();
  });
}

var chips = document.querySelectorAll(".chip");
chips.forEach(function (chip) {
  chip.addEventListener("click", function () {
    chips.forEach(function (c) { c.classList.remove("active"); });
    chip.classList.add("active");
    state.category = chip.getAttribute("data-cat");
    renderProducts();
  });
});

// ==========================
// CART DRAWER
// ==========================

function openCart() {
  if (!cartDrawer) return;
  cartDrawer.classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeCart() {
  if (!cartDrawer) return;
  cartDrawer.classList.remove("open");
  document.body.style.overflow = "";
}

if (btnCart && cartDrawer) {
  btnCart.addEventListener("click", openCart);

  cartDrawer.addEventListener("click", function (e) {
    if (e.target.hasAttribute("data-close")) {
      closeCart();
    }
  });
}

if (cartListEl) {
  cartListEl.addEventListener("click", function (e) {
    var target = e.target;

    var rm = target.closest("[data-cart-remove]");
    if (rm) {
      var id = parseInt(rm.getAttribute("data-cart-remove"), 10);
      delete state.cart[id];
      renderCart();
      return;
    }

    var inc = target.closest("[data-cart-inc]");
    var dec = target.closest("[data-cart-dec]");
    if (inc || dec) {
      var cid = parseInt((inc || dec).getAttribute(inc ? "data-cart-inc" : "data-cart-dec"), 10);
      var item = state.cart[cid];
      if (!item) return;
      if (inc) item.qty++;
      if (dec && item.qty > 1) item.qty--;
      renderCart();
    }
  });
}

// ==========================
// FAVORITE DRAWER
// ==========================

if (btnFav && favDrawer) {
  btnFav.addEventListener("click", function () {
    renderFavorites();
    favDrawer.classList.add("open");
    document.body.style.overflow = "hidden";
  });

  favDrawer.addEventListener("click", function (e) {
    if (e.target.hasAttribute("data-close")) {
      favDrawer.classList.remove("open");
      document.body.style.overflow = "";
      return;
    }

    var jump = e.target.closest("[data-jump]");
    if (jump) {
      var id = parseInt(jump.getAttribute("data-jump"), 10);
      favDrawer.classList.remove("open");
      document.body.style.overflow = "";
      highlightProduct(id);
    }
  });
}

// ==========================
// MODAL DETAIL
// ==========================

if (detailModal) {
  detailModal.addEventListener("click", function (e) {
    if (e.target.hasAttribute("data-close")) {
      closeDetail();
      return;
    }

    var addDet = e.target.closest("[data-add-detail]");
    if (addDet) {
      var id = parseInt(addDet.getAttribute("data-add-detail"), 10);
      var p = findProduct(id);
      if (!p) return;

      if (!state.cart[id]) {
        state.cart[id] = { qty: 1, product: p };
      } else {
        state.cart[id].qty++;
      }

      renderCart();
      closeDetail();
      openCart();
    }
  });
}

// ==========================
// CTA SCROLL
// ==========================

if (ctaBtn) {
  ctaBtn.addEventListener("click", function () {
    var section = document.getElementById("produk");
    if (!section) return;
    var top = section.getBoundingClientRect().top + window.scrollY - 80;
    window.scrollTo({ top: top, behavior: "smooth" });
  });
}

// ==========================
// NAVBAR SCROLL
// ==========================

var navLinks = document.querySelectorAll(".nav-links a");
navLinks.forEach(function (link) {
  link.addEventListener("click", function (e) {
    e.preventDefault();
    var targetId = link.getAttribute("href").slice(1);
    var targetEl = document.getElementById(targetId);
    if (targetEl) {
      var top = targetEl.getBoundingClientRect().top + window.scrollY - 80;
      window.scrollTo({ top: top, behavior: "smooth" });
    }
    navLinks.forEach(function (l) { l.classList.remove("active"); });
    link.classList.add("active");
  });
});

// ==========================
// CONTACT FORM (DUMMY)
// ==========================

if (contactForm) {
  contactForm.addEventListener("submit", function (e) {
    e.preventDefault();
    var status = document.getElementById("contact-status");
    if (status) {
      status.textContent =
        "Terima kasih, pesan kamu sudah tercatat. Untuk respon cepat bisa hubungi kami lewat Instagram juga.";
    }
    contactForm.reset();
  });
}

// ==========================
// CHECKOUT WA
// ==========================

if (checkoutBtn) {
  checkoutBtn.addEventListener("click", function () {
    var keys = Object.keys(state.cart);
    if (keys.length === 0) {
      alert("Keranjang masih kosong.");
      return;
    }

    var text = "Halo, saya ingin pesan perabot dari website Perabot Budi Jaya Marelan.\n\n";
    var subtotal = 0;

    for (var i = 0; i < keys.length; i++) {
      var id = parseInt(keys[i], 10);
      var item = state.cart[id];
      if (!item) continue;

      var p = item.product;
      var qty = item.qty;
      var totalItem = p.price * qty;
      subtotal += totalItem;

      text += "- " + p.name + " (" + qty + " x " + formatRupiah(p.price) + ") = " + formatRupiah(totalItem) + "\n";
    }

    text += "\nTotal sementara: " + formatRupiah(subtotal) + "\n\n";
    text += "Nama:\nAlamat lengkap:\nCatatan tambahan:\n";

    var url = "https://wa.me/6285100661973?text=" + encodeURIComponent(text);
    window.open(url, "_blank");
  });
}

// ==========================
// INIT
// ==========================

if (yearSpan) {
  yearSpan.textContent = new Date().getFullYear();
}

renderProducts();
renderCart();
updateCounters();
