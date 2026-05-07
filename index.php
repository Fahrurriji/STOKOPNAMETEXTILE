<?php
require_once 'config/auth.php';
require_once 'config/koneksi.php';

$page_title = 'Dashboard';

// === STATISTIK ===
// Total produk & Nilai Inventaris
$r = $conn->query("SELECT COUNT(*) as jml, SUM(stok * harga_jual) as nilai FROM barang WHERE status='aktif'");
$barang = $r->fetch_assoc();

// Total kategori
$r2 = $conn->query("SELECT COUNT(*) as jml FROM kategori");
$kategori = $r2->fetch_assoc();

// Total pemasok
$r3 = $conn->query("SELECT COUNT(*) as jml FROM pemasok");
$pemasok = $r3->fetch_assoc();

// Total pelanggan
$r4 = $conn->query("SELECT COUNT(*) as jml FROM pelanggan");
$pelanggan = $r4->fetch_assoc();

// Stok minim - NAMA VARIABEL DIGANTI AGAR TIDAK BENTROK DENGAN HEADER
$r5 = $conn->query("SELECT COUNT(*) as jml FROM barang WHERE stok <= stok_minimum AND status='aktif'");
$data_stok_minim = $r5->fetch_assoc();

// Transaksi bulan ini
$bulan_ini = date('Y-m');
$r6 = $conn->query("SELECT COALESCE(SUM(total_nilai),0) as masuk FROM transaksi_masuk WHERE DATE_FORMAT(tanggal,'%Y-%m')='$bulan_ini'");
$t_masuk = $r6->fetch_assoc();

$r7 = $conn->query("SELECT COALESCE(SUM(total_bayar),0) as keluar FROM transaksi_keluar WHERE DATE_FORMAT(tanggal,'%Y-%m')='$bulan_ini'");
$t_keluar = $r7->fetch_assoc();

// Data List
$stok_rendah = $conn->query("SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori=k.id WHERE b.status='aktif' ORDER BY b.stok ASC LIMIT 8");
$trans_masuk = $conn->query("SELECT tm.*, p.nama_pemasok FROM transaksi_masuk tm LEFT JOIN pemasok p ON tm.id_pemasok=p.id ORDER BY tm.created_at DESC LIMIT 5");
$trans_keluar = $conn->query("SELECT tk.*, pl.nama_pelanggan FROM transaksi_keluar tk LEFT JOIN pelanggan pl ON tk.id_pelanggan=pl.id ORDER BY tk.created_at DESC LIMIT 5");

include 'header.php';
?>

<!-- STATS GRID -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-boxes"></i></div>
        <div class="stat-info">
            <div class="si-value"><?= number_format($barang['jml'] ?? 0) ?></div>
            <div class="si-label">Total Produk Aktif</div>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon"><i class="fas fa-tags"></i></div>
        <div class="stat-info">
            <div class="si-value"><?= $kategori['jml'] ?? 0 ?></div>
            <div class="si-label">Total Kategori</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-info">
            <div class="si-value"><?= rupiah($t_masuk['masuk'] ?? 0) ?></div>
            <div class="si-label">Pembelian Bulan Ini</div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-info">
            <div class="si-value"><?= rupiah($t_keluar['keluar'] ?? 0) ?></div>
            <div class="si-label">Penjualan Bulan Ini</div>
        </div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <!-- MENGGUNAKAN VARIABEL BARU -->
            <div class="si-value"><?= $data_stok_minim['jml'] ?? 0 ?></div>
            <div class="si-label">Stok Minim (Perlu Restock)</div>
            <?php if (($data_stok_minim['jml'] ?? 0) > 0): ?>
                <div class="si-change down">⚠️ Perlu perhatian</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ROW GRID -->
<div class="stats-grid">

    <!-- Stok Rendah -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <h3>Produk Stok Rendah</h3>
                <p>Segera lakukan pembelian</p>
            </div>
            <a href="barang/kelola_barang.php?filter=minim" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Stok</th>
                        <th>Min</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($b = $stok_rendah->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($b['nama_barang']) ?></div>
                        <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($b['nama_kategori'] ?? 'Tanpa Kategori') ?></div>
                    </td>
                    <td><strong><?= $b['stok'] ?></strong> <?= $b['satuan'] ?></td>
                    <td><?= $b['stok_minimum'] ?></td>
                    <td>
                        <?php if ($b['stok'] == 0): ?>
                            <span class="badge badge-danger">Habis</span>
                        <?php elseif ($b['stok'] <= $b['stok_minimum']): ?>
                            <span class="badge badge-warning">Minim</span>
                        <?php else: ?>
                            <span class="badge badge-success">Normal</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Panel -->
    <div style="display:flex;flex-direction:column;gap:24px">
        <!-- Nilai Inventaris -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon"><i class="fas fa-warehouse"></i></div>
                <div>
                    <h3>Nilai Inventaris</h3>
                    <p>Total nilai stok gudang</p>
                </div>
            </div>
            <div class="card-body" style="text-align:center;padding:28px">
                <div style="font-size:36px;font-weight:800;color:var(--primary-dark);font-family:'Playfair Display',serif">
                    <?= rupiah($barang['nilai'] ?? 0) ?>
                </div>
                <p style="color:var(--text-muted);margin-top:8px">
                    <?= $barang['jml'] ?? 0 ?> produk &bull; <?= $pemasok['jml'] ?? 0 ?> pemasok &bull; <?= $pelanggan['jml'] ?? 0 ?> pelanggan
                </p>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <div class="card-header-icon"><i class="fas fa-bolt"></i></div>
                <div><h3>Aksi Cepat</h3></div>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <a href="barang/tambah_barang.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </a>
                    <a href="transaksi/tambah_masuk.php" class="btn btn-success">
                        <i class="fas fa-arrow-down"></i> Barang Masuk
                    </a>
                    <a href="transaksi/tambah_keluar.php" class="btn btn-danger">
                        <i class="fas fa-arrow-up"></i> Barang Keluar
                    </a>
                    <a href="transaksi/stock_opname.php" class="btn btn-accent">
                        <i class="fas fa-clipboard-check"></i> Stock Opname
                    </a>
                    <a href="laporan/laporan_stok.php" class="btn btn-secondary" style="grid-column:span 2">
                        <i class="fas fa-file-alt"></i> Cetak Laporan Stok
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaksi Terbaru -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <!-- Pembelian Terbaru -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-arrow-circle-down"></i></div>
            <div>
                <h3>Pembelian Terbaru</h3>
                <p>Barang masuk gudang</p>
            </div>
            <a href="transaksi/transaksi_masuk.php" class="btn btn-sm btn-secondary">Semua</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>No. Transaksi</th><th>Pemasok</th><th>Tanggal</th><th>Nilai</th></tr>
                </thead>
                <tbody>
                <?php if ($trans_masuk->num_rows === 0): ?>
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada transaksi</td></tr>
                <?php else: ?>
                    <?php while ($tm = $trans_masuk->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge badge-success"><?= $tm['no_transaksi'] ?></span></td>
                        <td><?= htmlspecialchars($tm['nama_pemasok'] ?? '-') ?></td>
                        <td><?= tgl_indo($tm['tanggal']) ?></td>
                        <td><strong><?= rupiah($tm['total_nilai']) ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Penjualan Terbaru -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-arrow-circle-up"></i></div>
            <div>
                <h3>Penjualan Terbaru</h3>
                <p>Barang keluar gudang</p>
            </div>
            <a href="transaksi/transaksi_keluar.php" class="btn btn-sm btn-secondary">Semua</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>No. Transaksi</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th></tr>
                </thead>
                <tbody>
                <?php if ($trans_keluar->num_rows === 0): ?>
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada transaksi</td></tr>
                <?php else: ?>
                    <?php while ($tk = $trans_keluar->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge badge-danger"><?= $tk['no_transaksi'] ?></span></td>
                        <td><?= htmlspecialchars($tk['nama_pelanggan'] ?? '-') ?></td>
                        <td><?= tgl_indo($tk['tanggal']) ?></td>
                        <td><strong><?= rupiah($tk['total_bayar']) ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>