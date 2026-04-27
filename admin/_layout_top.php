<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> – Budi Jaya Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --sand: #F7F4EF; --cream: #FDFBF8; --charcoal: #1C1C1A; --charcoal2: #2E2E2B;
      --accent: #A34E22; --accent-soft: #F5EDE6; --border: #E4E0D8;
      --muted: #767370; --warm-gray: #6B6860; --white: #fff;
      --success: #16a34a; --error: #dc2626; --warning: #d97706; --info: #2563eb;
      --sidebar-w: 240px;
      --font-body: 'DM Sans', sans-serif;
      --font-display: 'DM Serif Display', serif;
      --radius-sm: 8px; --radius-md: 14px; --radius-lg: 20px;
      --shadow-sm: 0 1px 4px rgba(28,28,26,.08);
      --shadow-md: 0 4px 16px rgba(28,28,26,.10);
    }
    body { font-family: var(--font-body); background: var(--sand); color: var(--charcoal); min-height: 100vh; display: flex; }

    /* ---- SIDEBAR ---- */
    .sidebar {
      width: var(--sidebar-w); min-height: 100vh; background: var(--charcoal);
      display: flex; flex-direction: column; flex-shrink: 0; position: sticky; top: 0; height: 100vh;
    }
    .sidebar-brand {
      padding: 24px 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.08);
      display: flex; align-items: center; gap: 10px;
    }
    .sidebar-brand svg { width: 26px; height: 26px; stroke: var(--accent); fill: none; stroke-width: 2; flex-shrink: 0; }
    .sidebar-brand-text .title { font-family: var(--font-display); font-size: 16px; color: #fff; }
    .sidebar-brand-text .sub { font-size: 10px; color: rgba(255,255,255,.4); letter-spacing: .08em; text-transform: uppercase; }
    .sidebar-nav { flex: 1; padding: 16px 12px; display: flex; flex-direction: column; gap: 4px; }
    .sidebar-nav a {
      display: flex; align-items: center; gap: 10px; padding: 10px 12px;
      border-radius: var(--radius-sm); color: rgba(255,255,255,.55);
      font-size: 13px; font-weight: 500; text-decoration: none; transition: all .15s;
    }
    .sidebar-nav a svg { width: 17px; height: 17px; flex-shrink: 0; }
    .sidebar-nav a:hover { background: rgba(255,255,255,.07); color: #fff; }
    .sidebar-nav a.active { background: var(--accent); color: #fff; }
    .sidebar-section-label {
      font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
      color: rgba(255,255,255,.25); padding: 14px 12px 6px;
    }
    .sidebar-footer {
      padding: 16px 12px; border-top: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-footer a {
      display: flex; align-items: center; gap: 8px; padding: 9px 12px;
      border-radius: var(--radius-sm); color: rgba(255,255,255,.45);
      font-size: 12px; text-decoration: none; transition: all .15s;
    }
    .sidebar-footer a:hover { background: rgba(255,255,255,.07); color: #fff; }
    .sidebar-footer a svg { width: 15px; height: 15px; }

    /* ---- MAIN ---- */
    .main-wrap { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .topbar {
      height: 60px; background: var(--cream); border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 28px; flex-shrink: 0;
    }
    .topbar-title { font-family: var(--font-display); font-size: 20px; color: var(--charcoal); }
    .topbar-user { font-size: 13px; color: var(--muted); }
    .content { flex: 1; padding: 28px; overflow-y: auto; }

    /* ---- COMPONENTS ---- */
    .card {
      background: var(--cream); border: 1.5px solid var(--border);
      border-radius: var(--radius-md); overflow: hidden;
    }
    .card-header {
      padding: 18px 24px; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    }
    .card-header h2 { font-size: 16px; font-weight: 700; }
    .card-body { padding: 24px; }

    /* Stat cards */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 16px; margin-bottom: 28px; }
    .stat-card {
      background: var(--cream); border: 1.5px solid var(--border);
      border-radius: var(--radius-md); padding: 20px 22px;
    }
    .stat-label { font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
    .stat-value { font-family: var(--font-display); font-size: 30px; color: var(--charcoal); }
    .stat-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }

    /* Table */
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { text-align: left; font-size: 11px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase;
      color: var(--muted); padding: 10px 16px; border-bottom: 1.5px solid var(--border); white-space: nowrap; }
    td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--sand); }

    /* Badge */
    .badge {
      display: inline-block; padding: 3px 10px; border-radius: 999px;
      font-size: 11px; font-weight: 600;
    }
    .badge-pending    { background: #fef3c7; color: #92400e; }
    .badge-confirmed  { background: #dbeafe; color: #1e40af; }
    .badge-processing { background: #ede9fe; color: #5b21b6; }
    .badge-delivered  { background: #dcfce7; color: #14532d; }
    .badge-cancelled  { background: #fee2e2; color: #991b1b; }
    .badge-active     { background: #dcfce7; color: #14532d; }
    .badge-inactive   { background: #fee2e2; color: #991b1b; }

    /* Buttons */
    .btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 8px 16px; border-radius: var(--radius-sm);
      font-size: 13px; font-weight: 600; cursor: pointer; border: 1.5px solid transparent;
      font-family: var(--font-body); text-decoration: none; transition: all .15s; white-space: nowrap;
    }
    .btn svg { width: 15px; height: 15px; }
    .btn-primary { background: var(--charcoal); color: #fff; border-color: var(--charcoal); }
    .btn-primary:hover { background: var(--charcoal2); }
    .btn-accent { background: var(--accent); color: #fff; border-color: var(--accent); }
    .btn-accent:hover { background: #8F3D14; }
    .btn-outline { background: transparent; color: var(--charcoal); border-color: var(--border); }
    .btn-outline:hover { border-color: var(--charcoal); background: var(--sand); }
    .btn-danger { background: #fee2e2; color: var(--error); border-color: #fecaca; }
    .btn-danger:hover { background: #fecaca; }
    .btn-sm { padding: 5px 10px; font-size: 12px; }

    /* Form */
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 12px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--charcoal); margin-bottom: 7px; }
    .form-control {
      width: 100%; padding: 10px 13px; border: 1.5px solid var(--border);
      border-radius: var(--radius-sm); font-size: 14px; font-family: var(--font-body);
      background: var(--cream); color: var(--charcoal); outline: none; transition: border-color .15s;
    }
    .form-control:focus { border-color: var(--accent); }
    select.form-control { cursor: pointer; }
    textarea.form-control { resize: vertical; min-height: 90px; }

    /* Alert */
    .alert { padding: 12px 16px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 20px; border: 1px solid transparent; }
    .alert-success { background: #dcfce7; color: #14532d; border-color: #bbf7d0; }
    .alert-error   { background: #fee2e2; color: #991b1b; border-color: #fecaca; }

    /* Modal */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 200; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--cream); border-radius: var(--radius-lg); padding: 32px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 32px 80px rgba(0,0,0,.3); }
    .modal h3 { font-family: var(--font-display); font-size: 22px; margin-bottom: 20px; }
    .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }

    /* Pagination */
    .pagination { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 20px; }
    .pagination a, .pagination span {
      display: inline-flex; align-items: center; justify-content: center;
      min-width: 34px; height: 34px; padding: 0 10px;
      border-radius: var(--radius-sm); font-size: 13px; font-weight: 500;
      border: 1.5px solid var(--border); text-decoration: none; color: var(--charcoal); transition: all .15s;
    }
    .pagination a:hover { background: var(--sand-dark); border-color: var(--charcoal); }
    .pagination .current { background: var(--charcoal); color: #fff; border-color: var(--charcoal); }

    /* Search bar */
    .search-bar {
      display: flex; align-items: center; gap: 8px;
      border: 1.5px solid var(--border); border-radius: var(--radius-sm);
      padding: 8px 13px; background: var(--cream); max-width: 260px;
    }
    .search-bar svg { width: 15px; height: 15px; color: var(--muted); flex-shrink: 0; }
    .search-bar input { border: none; outline: none; font-size: 13px; background: transparent; width: 100%; }

    @media (max-width: 768px) {
      .sidebar { display: none; }
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <svg viewBox="0 0 24 24"><path d="M3 12l9-8 9 8v8a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2v-8z"/></svg>
    <div class="sidebar-brand-text">
      <div class="title">Budi Jaya</div>
      <div class="sub">Admin Panel</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Menu</div>
    <a href="index.php" class="<?= ($activePage??'')==='dashboard' ? 'active':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="products.php" class="<?= ($activePage??'')==='products' ? 'active':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l6 3.46a2 2 0 0 0 2 0l6-3.46A2 2 0 0 0 21 16z"/></svg>
      Produk
    </a>
    <a href="orders.php" class="<?= ($activePage??'')==='orders' ? 'active':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17H7A5 5 0 0 1 7 7h2"/><path d="M15 7h2a5 5 0 0 1 0 10h-2"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
      Pesanan
    </a>
    <a href="customers.php" class="<?= ($activePage??'')==='customers' ? 'active':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Customer
    </a>

    <div class="sidebar-section-label" style="margin-top:8px">Toko</div>
    <a href="../index.php" target="_blank">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Lihat Toko
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="logout.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout (<?= htmlspecialchars($_SESSION['admin']['username'] ?? '') ?>)
    </a>
  </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
  <div class="topbar">
    <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
    <span class="topbar-user">Admin: <?= htmlspecialchars($_SESSION['admin']['username'] ?? '') ?></span>
  </div>
  <div class="content">
