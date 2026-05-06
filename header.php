<?php
// 1. Jalankan session & cek login
include_once 'config/auth.php'; 

// 2. Panggil koneksi database agar variabel $conn tersedia
include_once 'config/koneksi.php';

// Tentukan judul halaman default
if (!isset($page_title)) $page_title = 'Dashboard';

// Tentukan active menu
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));

function is_active($file_or_dir, $current_file, $current_dir) {
    if (strpos($file_or_dir, '/') !== false) {
        $parts = explode('/', $file_or_dir);
        return $current_dir == $parts[0] ? 'active' : '';
    }
    return ($current_file == $file_or_dir) ? 'active' : '';
}

// Hitung notifikasi stok minim
$stok_minim = 0;
if (isset($conn)) {
    $res_notif = $conn->query("SELECT COUNT(*) as jml FROM barang WHERE stok <= stok_minimum AND status='aktif'");
    if ($res_notif) {
        $row_notif = $res_notif->fetch_assoc();
        $stok_minim = $row_notif['jml'];
    }
}

// Logika depth untuk file di dalam sub-folder
$depth = ($current_file !== 'index.php' && $current_dir !== 'STOKOPNAMETEXTILE') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Stock Opname Textile</title>
    <!-- Memanggil style.css dengan Cache Busting -->
    <link rel="stylesheet" href="<?= $depth ?>style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Page Loader -->
<div class="page-loader" id="pageLoader">
    <div class="loader-spinner"></div>
    <p style="color:rgba(255,255,255,.6);font-size:13px;">Memuat data...</p>
</div>

<div class="app-wrapper">
<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">🧵</div>
        <div class="brand-text">
            <h2>Textile Gudang</h2>
            <p>Stock Opname System</p>
        </div>
    </div>

    <nav class="sidebar-menu">
        <div class="sidebar-label">Utama</div>

        <a href="<?= $depth ?>index.php" class="sidebar-item <?= is_active('index.php', $current_file, $current_dir) ?>">
            <i class="fas fa-th-large si-icon"></i>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-label">Inventaris</div>

        <a href="<?= $depth ?>kategori/kelola_kategori.php" class="sidebar-item <?= ($current_dir == 'kategori') ? 'active' : '' ?>">
            <i class="fas fa-tags si-icon"></i>
            <span>Kategori</span>
        </a>

        <a href="<?= $depth ?>barang/kelola_barang.php" class="sidebar-item <?= ($current_dir == 'barang') ? 'active' : '' ?>">
            <i class="fas fa-boxes si-icon"></i>
            <span>Produk / Barang</span>
            <?php if ($stok_minim > 0): ?>
                <span class="si-badge"><?= $stok_minim ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= $depth ?>pemasok/kelola_pemasok.php" class="sidebar-item <?= ($current_dir == 'pemasok') ? 'active' : '' ?>">
            <i class="fas fa-truck si-icon"></i>
            <span>Pemasok</span>
        </a>

        <a href="<?= $depth ?>pelanggan/kelola_pelanggan.php" class="sidebar-item <?= ($current_dir == 'pelanggan') ? 'active' : '' ?>">
            <i class="fas fa-users si-icon"></i>
            <span>Pelanggan</span>
        </a>

        <div class="sidebar-label">Transaksi</div>

        <a href="<?= $depth ?>transaksi/transaksi_masuk.php" class="sidebar-item <?= ($current_dir == 'transaksi' && (strpos($current_file,'masuk') !== false)) ? 'active' : '' ?>">
            <i class="fas fa-arrow-circle-down si-icon" style="color:#22c55e"></i>
            <span>Barang Masuk</span>
        </a>

        <a href="<?= $depth ?>transaksi/transaksi_keluar.php" class="sidebar-item <?= ($current_dir == 'transaksi' && (strpos($current_file,'keluar') !== false)) ? 'active' : '' ?>">
            <i class="fas fa-arrow-circle-up si-icon" style="color:#ef4444"></i>
            <span>Barang Keluar</span>
        </a>

        <div class="sidebar-label">Stock Opname</div>

        <a href="<?= $depth ?>transaksi/stock_opname.php" class="sidebar-item <?= ($current_dir == 'transaksi' && strpos($current_file,'opname') !== false) ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list si-icon"></i>
            <span>Stock Opname</span>
        </a>

        <div class="sidebar-label">Laporan</div>

        <a href="<?= $depth ?>laporan/laporan_stok.php" class="sidebar-item <?= ($current_dir == 'laporan' && strpos($current_file,'stok') !== false) ? 'active' : '' ?>">
            <i class="fas fa-chart-bar si-icon"></i>
            <span>Laporan Stok</span>
        </a>

        <a href="<?= $depth ?>laporan/laporan_transaksi.php" class="sidebar-item <?= ($current_dir == 'laporan' && strpos($current_file,'transaksi') !== false) ? 'active' : '' ?>">
            <i class="fas fa-file-invoice si-icon"></i>
            <span>Laporan Transaksi</span>
        </a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="sidebar-label">Admin</div>
        <a href="<?= $depth ?>pengguna/kelola_pengguna.php" class="sidebar-item <?= ($current_dir == 'pengguna') ? 'active' : '' ?>">
            <i class="fas fa-user-shield si-icon"></i>
            <span>Kelola Pengguna</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <img src="<?= $depth ?>gambar/<?= htmlspecialchars($_SESSION['foto'] ?? 'default.png') ?>" onerror="this.src='<?= $depth ?>gambar/default.png'" alt="foto">
            <div class="sidebar-user-info">
                <div class="su-name"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></div>
                <div class="su-role"><?= ucfirst($_SESSION['role'] ?? 'user') ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">
    <header class="topbar">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div>
            <div class="topbar-title"><?= $page_title ?></div>
        </div>
        <div class="topbar-actions">
            <?php if ($stok_minim > 0): ?>
            <a href="<?= $depth ?>barang/kelola_barang.php?filter=minim" class="topbar-btn" title="<?= $stok_minim ?> produk stok minim">
                <i class="fas fa-bell"></i>
                <span class="notif-dot"></span>
            </a>
            <?php endif; ?>
            <a href="<?= $depth ?>pengguna/profile.php" class="topbar-user">
                <img src="<?= $depth ?>gambar/<?= htmlspecialchars($_SESSION['foto'] ?? 'default.png') ?>" onerror="this.src='<?= $depth ?>gambar/default.png'" alt="foto">
                <span><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User') ?></span>
            </a>
            <a href="<?= $depth ?>logout.php" class="topbar-btn" title="Logout" style="color:#ef4444">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>
    <main class="page-content">