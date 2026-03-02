// ==========================
// DATA PRODUK
// ==========================

var PRODUCTS = [
  { id: 1,  name: "Kursi Plastik",            price: 90000,   cat: "Kursi",  desc: "Kursi plastik serbaguna, cocok untuk indoor maupun outdoor.", img: "kursiplastik" },
  { id: 2,  name: "Kursi Santai",             price: 245000,  cat: "Kursi",  desc: "Kursi santai dengan sandaran nyaman untuk ruang tamu atau teras.", img: "kursisantai" },
  { id: 3,  name: "Lemari Pakaian 2 Pintu",    price: 799000,  cat: "Lemari", desc: "Lemari pakaian 2 pintu dengan ruang gantung dan rak lipat.", img: "lemaripakaian2p" },
  { id: 4,  name: "Lemari Pakaian 3 Pintu",    price: 1199000, cat: "Lemari", desc: "Lemari pakaian 3 pintu kapasitas besar, cocok untuk keluarga.", img: "lemaripakaian3p" },
  { id: 5,  name: "Meja Belajar Minimalis",    price: 330000,  cat: "Meja",   desc: "Meja belajar minimalis dengan ruang untuk buku dan perlengkapan tulis.", img: "mejabelajarminimalis" },
  { id: 6,  name: "Meja Lipat Serbaguna",      price: 120000,  cat: "Meja",   desc: "Meja lipat praktis, mudah disimpan dan dipindahkan.", img: "mejalipat" },
  { id: 7,  name: "Meja Makan Set 4 Kursi",    price: 1850000, cat: "Meja",   desc: "Satu set meja makan dengan 4 kursi, cocok untuk ruang makan keluarga.", img: "mejamakan4" },
  { id: 8,  name: "Meja TV Minimalis",         price: 359000,  cat: "Meja",   desc: "Meja TV minimalis dengan rak penyimpanan di bawah.", img: "mejatvminimalis" },
  { id: 9,  name: "Rak Buku",                  price: 259000,  cat: "Rak",    desc: "Rak buku sederhana untuk kamar atau ruang kerja.", img: "rakbuku" },
  { id: 10, name: "Rak Sepatu Kecil",          price: 149000,  cat: "Rak",    desc: "Rak sepatu hemat tempat, cocok diletakkan dekat pintu masuk.", img: "raksepatukecil" },
  { id: 11, name: "Rak Serbaguna 4 Susun",     price: 189000,  cat: "Rak",    desc: "Rak serbaguna 4 susun untuk dapur, kamar mandi, atau ruang keluarga.", img: "rakserbaguna4susun" },
  { id: 12, name: "Sofa L Minimalis",          price: 1999000, cat: "Sofa",   desc: "Sofa L minimalis yang nyaman untuk ruang keluarga.", img: "sofaL" }
];


var WA_NUMBER = "6285260835353"; 

// ==========================
// STORAGE
// ==========================

var LS_FAV  = "pbw_fav";
var LS_CART = "pbw_cart";
var LS_PREF = "pbw_pref";

function safeParse(json, fallback) {
  try { return JSON.parse(json); } catch (e) { return fallback; }
}

function loadState() {
  var fav = safeParse(localStorage.getItem(LS_FAV), []);
  var cart = safeParse(localStorage.getItem(LS_CART), {});
  var pref = safeParse(localStorage.getItem(LS_PREF), {});

  if (!Array.isArray(fav)) fav = [];
  if (!cart || typeof cart !== "object") cart = {};
  if (!pref || typeof pref !== "object") pref = {};

  state.fav = fav;
  state.cart = cart;

  // preferensi UI
  if (typeof pref.sort === "string") state.sort = pref.sort;
  if (Array.isArray(pref.categories)) state.categories = pref.categories;
  if (typeof pref.page === "number") state.page = pref.page;
}

function saveFav() {
  localStorage.setItem(LS_FAV, JSON.stringify(state.fav));
}

function saveCart() {
  localStorage.setItem(LS_CART, JSON.stringify(state.cart));
}

function savePref() {
  localStorage.setItem(LS_PREF, JSON.stringify({
    sort: state.sort,
    categories: state.categories,
    page: state.page
  }));
}

// ==========================
// STATE
// ==========================

var state = {
  search: "",
  categories: [],        // multi-select (array kategori)
  sort: "default",      // default | price_asc | price_desc
  page: 1,
  pageSize: 8,
  fav: [],               // array id
  cart: {}               // { id: qty }
};

// ==========================
// DOM ELEMENTS
// ==========================

var grid          = document.getElementById("koleksi");
var pagerEl       = document.getElementById("pager");
var searchInput   = document.getElementById("q");
var sortSelect    = document.getElementById("sort");
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
  if (index === -1) state.fav.push(id);
  else state.fav.splice(index, 1);
  saveFav();
}

function inSelectedCategories(cat) {
  if (!state.categories || state.categories.length === 0) return true;
  return state.categories.indexOf(cat) !== -1;
}

function updateCounters() {
  if (cartCountEl) {
    var keys = Object.keys(state.cart);
    var totalQty = 0;
    for (var i = 0; i < keys.length; i++) {
      var qty = parseInt(state.cart[keys[i]] || 0, 10);
      if (!isNaN(qty)) totalQty += qty;
    }
    cartCountEl.textContent = totalQty;
  }
  if (favCountEl) favCountEl.textContent = state.fav.length;
}

function clamp(n, min, max) {
  return Math.max(min, Math.min(max, n));
}

// ==========================
// FILTER + SORT + PAGINATION
// ==========================

function getFilteredSortedList() {
  var keyword = (state.search || "").trim().toLowerCase();
  var list = [];

  for (var i = 0; i < PRODUCTS.length; i++) {
    var p = PRODUCTS[i];
    if (!inSelectedCategories(p.cat)) continue;

    if (keyword) {
      var searchable = (p.name + " " + p.cat).toLowerCase();
      if (searchable.indexOf(keyword) === -1) continue;
    }

    list.push(p);
  }

  if (state.sort === "price_asc") {
    list.sort(function (a, b) { return a.price - b.price; });
  } else if (state.sort === "price_desc") {
    list.sort(function (a, b) { return b.price - a.price; });
  }

  return list;
}

function getPageSlice(list) {
  var total = list.length;
  var totalPages = Math.max(1, Math.ceil(total / state.pageSize));
  state.page = clamp(state.page, 1, totalPages);

  var start = (state.page - 1) * state.pageSize;
  var end = start + state.pageSize;

  return {
    items: list.slice(start, end),
    total: total,
    totalPages: totalPages
  };
}

// ==========================
// RENDER PRODUCTS
// ==========================

function renderProducts() {
  if (!grid) return;

  var list = getFilteredSortedList();
  var page = getPageSlice(list);

  var items = page.items;
  var html = "";

  if (items.length === 0) {
    html =
      '<div class="empty-state">' +
        '<strong>Tidak ada produk yang cocok.</strong>' +
        '<div style="margin-top:4px;">Coba hapus filter kategori atau ubah kata kunci pencarian.</div>' +
      '</div>';

    grid.innerHTML = html;
    renderPager(page.totalPages);
    return;
  }

  for (var i = 0; i < items.length; i++) {
    var p = items[i];
    var favActive = isFavorite(p.id) ? "true" : "false";

    html +=
      '<article class="card product-card" data-id="' + p.id + '" tabindex="0" aria-label="' + p.name + '">' +
        '<div class="thumb" aria-hidden="true" style="background-image:url(\'gambar/' + p.img + '.jpg\');">' +
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

  grid.innerHTML = html;
  renderPager(page.totalPages);
}

function renderPager(totalPages) {
  if (!pagerEl) return;

  // sembunyikan pager kalau 1 halaman
  if (totalPages <= 1) {
    pagerEl.innerHTML = "";
    savePref();
    return;
  }

  var html = "";

  function btn(label, page, disabled, active) {
    return (
      '<button type="button" ' +
        (disabled ? 'disabled ' : '') +
        (active ? 'class="active" ' : '') +
        'data-page="' + page + '">' +
        label +
      '</button>'
    );
  }

  html += btn("Prev", state.page - 1, state.page === 1, false);

  var windowSize = 5;
  var start = Math.max(1, state.page - Math.floor(windowSize / 2));
  var end = Math.min(totalPages, start + windowSize - 1);
  if (end - start + 1 < windowSize) start = Math.max(1, end - windowSize + 1);

  for (var p = start; p <= end; p++) {
    html += btn(String(p), p, false, p === state.page);
  }

  html += btn("Next", state.page + 1, state.page === totalPages, false);

  pagerEl.innerHTML = html;
  savePref();
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
        '<div class="thumb" aria-hidden="true" style="background-image:url(\'gambar/' + p.img + '.jpg\');"></div>' +
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
    var qty = parseInt(state.cart[keys[i]] || 0, 10);
    if (isNaN(qty) || qty < 1) continue;

    var p = findProduct(id);
    if (!p) continue;

    var total = p.price * qty;
    subtotal += total;

    html +=
      '<div class="cart-item" data-id="' + p.id + '">' +
        '<div class="thumb" aria-hidden="true" style="background-image:url(\'gambar/' + p.img + '.jpg\');"></div>' +
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
  saveCart();
}

// ==========================
// DETAIL MODAL (plus qty +/-)
// ==========================

function openDetail(id) {
  if (!detailModal || !detailBody) return;
  var p = findProduct(id);
  if (!p) return;

  var html =
    '<div class="detail-wrapper">' +
      '<div class="detail-thumb" aria-hidden="true" style="background-image:url(\'gambar/' + p.img + '.jpg\');"></div>' +
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
        '<div class="detail-actions" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">' +
          '<div class="qty" data-detail-qty="' + p.id + '">' +
            '<button data-detail-dec="' + p.id + '" aria-label="Kurangi">−</button>' +
            '<input type="text" value="1" inputmode="numeric" aria-label="Jumlah" />' +
            '<button data-detail-inc="' + p.id + '" aria-label="Tambah">+</button>' +
          '</div>' +
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
  if (produkSection) produkSection.scrollIntoView({ behavior: "smooth", block: "start" });

  setTimeout(function () {
    var cards = document.querySelectorAll(".product-card");
    for (var i = 0; i < cards.length; i++) cards[i].classList.remove("highlight");

    var card = document.querySelector('.product-card[data-id="' + id + '"]');
    if (card) {
      card.classList.add("highlight");
      card.scrollIntoView({ behavior: "smooth", block: "center" });
      setTimeout(function () { card.classList.remove("highlight"); }, 2000);
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
      if (favDrawer && favDrawer.classList.contains("open")) renderFavorites();
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

      var cur = parseInt(state.cart[id] || 0, 10);
      if (isNaN(cur) || cur < 0) cur = 0;
      state.cart[id] = cur + qtyVal;

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
// PAGER EVENTS
// ==========================

if (pagerEl) {
  pagerEl.addEventListener("click", function (e) {
    var btn = e.target.closest("[data-page]");
    if (!btn) return;
    var page = parseInt(btn.getAttribute("data-page"), 10);
    if (isNaN(page)) return;
    state.page = page;
    renderProducts();

    var produkSection = document.getElementById("produk");
    if (produkSection) {
      var top = produkSection.getBoundingClientRect().top + window.scrollY - 80;
      window.scrollTo({ top: top, behavior: "smooth" });
    }
  });
}

// ==========================
// SEARCH & FILTER & SORT
// ==========================

if (searchInput) {
  searchInput.addEventListener("input", function () {
    state.search = searchInput.value;
    state.page = 1;
    renderProducts();
  });
}

if (sortSelect) {
  sortSelect.addEventListener("change", function () {
    state.sort = sortSelect.value || "default";
    state.page = 1;
    renderProducts();
  });
}

var chips = document.querySelectorAll(".chip");
chips.forEach(function (chip) {
  chip.addEventListener("click", function () {
    var cat = chip.getAttribute("data-cat");

    if (cat === "all") {
      // reset
      state.categories = [];
      chips.forEach(function (c) { c.classList.remove("active"); });
      chip.classList.add("active");
      state.page = 1;
      renderProducts();
      return;
    }

    // toggle multi-select
    var idx = state.categories.indexOf(cat);
    if (idx === -1) {
      state.categories.push(cat);
      chip.classList.add("active");
    } else {
      state.categories.splice(idx, 1);
      chip.classList.remove("active");
    }

    // kalau ada pilihan kategori, matikan "Semua"
    var allChip = document.querySelector('.chip[data-cat="all"]');
    if (allChip) allChip.classList.remove("active");

    // kalau kosong lagi, balik ke "Semua"
    if (state.categories.length === 0 && allChip) {
      allChip.classList.add("active");
    }

    state.page = 1;
    renderProducts();
  });
});

function syncChipUIFromState() {
  // set active chip berdasarkan state.categories
  var allChip = document.querySelector('.chip[data-cat="all"]');
  if (allChip) allChip.classList.remove("active");

  if (!state.categories || state.categories.length === 0) {
    if (allChip) allChip.classList.add("active");
  }

  chips.forEach(function (chip) {
    var cat = chip.getAttribute("data-cat");
    if (cat === "all") return;
    if (state.categories.indexOf(cat) !== -1) chip.classList.add("active");
    else chip.classList.remove("active");
  });
}

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
    if (e.target.hasAttribute("data-close")) closeCart();
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
      var qty = parseInt(state.cart[cid] || 0, 10);
      if (isNaN(qty) || qty < 1) qty = 1;
      if (inc) qty++;
      if (dec && qty > 1) qty--;
      state.cart[cid] = qty;
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
// MODAL DETAIL EVENTS
// ==========================

if (detailModal) {
  detailModal.addEventListener("click", function (e) {
    if (e.target.hasAttribute("data-close")) {
      closeDetail();
      return;
    }

    // qty di detail
    var inc = e.target.closest("[data-detail-inc]");
    var dec = e.target.closest("[data-detail-dec]");
    if (inc || dec) {
      var id = parseInt((inc || dec).getAttribute(inc ? "data-detail-inc" : "data-detail-dec"), 10);
      var wrapper = detailModal.querySelector('[data-detail-qty="' + id + '"]');
      if (!wrapper) return;
      var input = wrapper.querySelector("input");
      var val = parseInt(input.value || "1", 10);
      if (isNaN(val) || val < 1) val = 1;
      if (inc) val++;
      if (dec && val > 1) val--;
      input.value = val;
      return;
    }

    var addDet = e.target.closest("[data-add-detail]");
    if (addDet) {
      var pid = parseInt(addDet.getAttribute("data-add-detail"), 10);
      var p = findProduct(pid);
      if (!p) return;

      var w = detailModal.querySelector('[data-detail-qty="' + pid + '"] input');
      var qtyVal = parseInt(w && w.value ? w.value : "1", 10);
      if (isNaN(qtyVal) || qtyVal < 1) qtyVal = 1;

      var cur = parseInt(state.cart[pid] || 0, 10);
      if (isNaN(cur) || cur < 0) cur = 0;
      state.cart[pid] = cur + qtyVal;

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
      var qty = parseInt(state.cart[keys[i]] || 0, 10);
      if (isNaN(qty) || qty < 1) continue;

      var p = findProduct(id);
      if (!p) continue;

      var totalItem = p.price * qty;
      subtotal += totalItem;

      text += "- " + p.name + " (" + qty + " x " + formatRupiah(p.price) + ") = " + formatRupiah(totalItem) + "\n";
    }

    text += "\nTotal sementara: " + formatRupiah(subtotal) + "\n\n";
    text += "Nama:\nAlamat lengkap:\nCatatan tambahan:\n";

    var url = "https://wa.me/" + WA_NUMBER + "?text=" + encodeURIComponent(text);
    window.open(url, "_blank");
  });
}

// ==========================
// INIT
// ==========================

if (yearSpan) yearSpan.textContent = new Date().getFullYear();

loadState();

// sync UI controls from saved pref
if (sortSelect && state.sort) sortSelect.value = state.sort;
syncChipUIFromState();

renderProducts();
renderCart();
updateCounters();
