<?php
// =====================================================
// HEADER & SIDEBAR - STOK OPNAME INVENTARIS TEXTILE
// Role-based sidebar: Admin vs User
// =====================================================
if (!isset($page_title)) $page_title = 'Dashboard';

// BASE URL
$script_url   = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$parts        = explode('/', trim($script_url, '/'));
$base_index   = array_search('STOKOPNAMETEXTILE', $parts);
$BASE         = ($base_index !== false) ? '/' . implode('/', array_slice($parts, 0, $base_index + 1)) . '/' : '/STOKOPNAMETEXTILE/';

// Active menu helpers
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));

// Role
$is_admin = (($_SESSION['role'] ?? 'user') === 'admin');

// Notifikasi stok minim (hanya untuk admin — user tidak perlu lihat ini)
$stok_minim = 0;
if ($is_admin && isset($conn)) {
    $res_notif = $conn->query("SELECT COUNT(*) as jml FROM barang WHERE stok <= stok_minimum AND status='aktif'");
    if ($res_notif) {
        $row_notif = $res_notif->fetch_assoc();
        $stok_minim = $row_notif['jml'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Stock Opname Textile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $BASE ?>assets/style.css">
    <style>
    /* Badge role di sidebar footer */
    .role-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 99px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
    }
    .role-badge.admin { background: rgba(200,151,58,.25); color: #e8b84e; }
    .role-badge.user  { background: rgba(37,99,168,.25); color: #93c5fd; }
    </style>
</head>
<body>

<div class="page-loader" id="pageLoader">
    <div class="loader-spinner"></div>
    <p style="color:rgba(255,255,255,.6);font-size:13px;">Memuat data...</p>
</div>

<div class="app-wrapper">

<!-- ==================== SIDEBAR ==================== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">🧵</div>
        <div class="brand-text">
            <h2>Textile Gudang</h2>
            <p>Stock Opname System</p>
        </div>
    </div>

    <nav class="sidebar-menu">

        <!-- ── MENU BERSAMA (Admin & User) ── -->
        <div class="sidebar-label">Utama</div>
        <a href="<?= $BASE ?>index.php" class="sidebar-item <?= ($current_file === 'index.php') ? 'active' : '' ?>">
            <i class="fas fa-th-large si-icon"></i><span>Dashboard</span>
        </a>

        <!-- ── MENU KHUSUS ADMIN: Master Data ── -->
        <?php if ($is_admin): ?>
        <div class="sidebar-label">
            <i class="fas fa-crown" style="color:var(--accent);margin-right:4px;font-size:9px"></i>
            Master Data (Admin)
        </div>
        <a href="<?= $BASE ?>kategori/kelola_kategori.php" class="sidebar-item <?= ($current_dir === 'kategori') ? 'active' : '' ?>">
            <i class="fas fa-tags si-icon"></i><span>Kategori</span>
        </a>
        <a href="<?= $BASE ?>barang/kelola_barang.php" class="sidebar-item <?= ($current_dir === 'barang') ? 'active' : '' ?>">
            <i class="fas fa-boxes si-icon"></i><span>Produk / Barang</span>
            <?php if ($stok_minim > 0): ?><span class="si-badge"><?= $stok_minim ?></span><?php endif; ?>
        </a>
        <a href="<?= $BASE ?>pemasok/kelola_pemasok.php" class="sidebar-item <?= ($current_dir === 'pemasok') ? 'active' : '' ?>">
            <i class="fas fa-truck si-icon"></i><span>Pemasok</span>
        </a>
        <a href="<?= $BASE ?>pelanggan/kelola_pelanggan.php" class="sidebar-item <?= ($current_dir === 'pelanggan') ? 'active' : '' ?>">
            <i class="fas fa-users si-icon"></i><span>Pelanggan</span>
        </a>
        <?php endif; ?>

        <!-- ── MENU BERSAMA: Transaksi ── -->
        <div class="sidebar-label">Transaksi</div>
        <a href="<?= $BASE ?>transaksi/transaksi_masuk.php"
           class="sidebar-item <?= ($current_dir === 'transaksi' && strpos($current_file,'masuk') !== false) ? 'active' : '' ?>">
            <i class="fas fa-arrow-circle-down si-icon" style="color:#22c55e"></i><span>Barang Masuk</span>
        </a>
        <a href="<?= $BASE ?>transaksi/transaksi_keluar.php"
           class="sidebar-item <?= ($current_dir === 'transaksi' && strpos($current_file,'keluar') !== false) ? 'active' : '' ?>">
            <i class="fas fa-arrow-circle-up si-icon" style="color:#ef4444"></i><span>Barang Keluar</span>
        </a>

        <!-- ── MENU BERSAMA: Stock Opname ── -->
        <div class="sidebar-label">Stock Opname</div>
        <a href="<?= $BASE ?>transaksi/stock_opname.php"
           class="sidebar-item <?= ($current_dir === 'transaksi' && strpos($current_file,'opname') !== false) ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list si-icon"></i><span>Stock Opname</span>
        </a>

        <!-- ── MENU BERSAMA: Laporan ── -->
        <div class="sidebar-label">Laporan</div>
        <a href="<?= $BASE ?>laporan/laporan_stok.php"
           class="sidebar-item <?= ($current_dir === 'laporan' && strpos($current_file,'stok') !== false) ? 'active' : '' ?>">
            <i class="fas fa-chart-bar si-icon"></i><span>Laporan Stok</span>
        </a>
        <a href="<?= $BASE ?>laporan/laporan_transaksi.php"
           class="sidebar-item <?= ($current_dir === 'laporan' && strpos($current_file,'transaksi') !== false) ? 'active' : '' ?>">
            <i class="fas fa-file-invoice si-icon"></i><span>Laporan Transaksi</span>
        </a>

        <!-- ── MENU KHUSUS ADMIN: Kelola Pengguna ── -->
        <?php if ($is_admin): ?>
        <div class="sidebar-label">
            <i class="fas fa-crown" style="color:var(--accent);margin-right:4px;font-size:9px"></i>
            Manajemen (Admin)
        </div>
        <a href="<?= $BASE ?>pengguna/kelola_pengguna.php"
           class="sidebar-item <?= ($current_dir === 'pengguna' && strpos($current_file,'kelola') !== false) ? 'active' : '' ?>">
            <i class="fas fa-user-shield si-icon"></i><span>Kelola Pengguna</span>
        </a>
        <?php endif; ?>

    </nav>

    <!-- Sidebar Footer: info user -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <img src="<?= $BASE ?>gambar/<?= htmlspecialchars($_SESSION['foto'] ?? 'default.png') ?>"
                 onerror="this.src='<?= $BASE ?>gambar/default.png'" alt="foto">
            <div class="sidebar-user-info">
                <div class="su-name"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></div>
                <div class="su-role">
                    <span class="role-badge <?= $is_admin ? 'admin' : 'user' ?>">
                        <?= $is_admin ? '👑 Admin' : '👤 Staff' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- ==================== MAIN CONTENT ==================== -->
<div class="main-content">
    <header class="topbar">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div>
            <div class="topbar-title"><?= htmlspecialchars($page_title) ?></div>
        </div>
        <div class="topbar-actions">
            <?php if ($is_admin && $stok_minim > 0): ?>
            <a href="<?= $BASE ?>barang/kelola_barang.php?filter=minim"
               class="topbar-btn" data-tooltip="<?= $stok_minim ?> produk stok minim">
                <i class="fas fa-bell"></i><span class="notif-dot"></span>
            </a>
            <?php endif; ?>
            <a href="<?= $BASE ?>pengguna/profile.php" class="topbar-user">
                <img src="<?= $BASE ?>gambar/<?= htmlspecialchars($_SESSION['foto'] ?? 'default.png') ?>"
                     onerror="this.src='<?= $BASE ?>gambar/default.png'" alt="foto">
                <span><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></span>
            </a>
            <a href="<?= $BASE ?>logout.php" class="topbar-btn" data-tooltip="Logout" style="color:var(--danger)">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>
    <main class="page-content">
PHPEOF
echo "Header selesai"