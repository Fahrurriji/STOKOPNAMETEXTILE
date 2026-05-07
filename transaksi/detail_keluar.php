<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);

// 1. Ambil data utama transaksi keluar, nama pelanggan, dan admin
$query_utama = "SELECT tk.*, p.nama_pelanggan, p.alamat, a.nama_lengkap as nama_admin 
                FROM transaksi_keluar tk 
                LEFT JOIN pelanggan p ON tk.id_pelanggan = p.id 
                LEFT JOIN admin a ON tk.id_admin = a.id 
                WHERE tk.id = $id";
$result_utama = $conn->query($query_utama);
$data = $result_utama->fetch_assoc();

// Jika data tidak ditemukan, balikkan ke daftar transaksi
if (!$data) {
    header("Location: transaksi_keluar.php");
    exit;
}

$page_title = 'Detail Barang Keluar - ' . $data['no_transaksi'];

// 2. Ambil rincian barang yang terjual dalam transaksi ini
$query_detail = "SELECT dtk.*, b.nama_barang, b.kode_barang 
                 FROM detail_transaksi_keluar dtk 
                 JOIN barang b ON dtk.id_barang = b.id 
                 WHERE dtk.id_transaksi = $id";
$details = $conn->query($query_detail);

include '../header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon" style="background:#fee2e2;color:#dc2626">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div>
            <h3>Detail Transaksi Keluar</h3>
            <p>No. Transaksi: <strong style="color:#dc2626"><?= $data['no_transaksi'] ?></strong></p>
        </div>
        <a href="transaksi_keluar.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card-body" style="padding: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; background: #f8fafc; padding: 15px; border-radius: 8px;">
            <div>
                <table style="width: 100%; font-size: 14px; border-collapse: separate; border-spacing: 0 8px;">
                    <tr><td width="120" style="color: #64748b;">Pelanggan</td><td>: <strong><?= htmlspecialchars($data['nama_pelanggan'] ?? 'Umum') ?></strong></td></tr>
                    <tr><td style="color: #64748b;">Alamat</td><td>: <?= htmlspecialchars($data['alamat'] ?? '-') ?></td></tr>
                    <tr><td style="color: #64748b;">Tanggal</td><td>: <?= date('d F Y', strtotime($data['tanggal'])) ?></td></tr>
                </table>
            </div>
            <div>
                <table style="width: 100%; font-size: 14px; border-collapse: separate; border-spacing: 0 8px;">
                    <tr><td width="120" style="color: #64748b;">Admin</td><td>: <?= htmlspecialchars($data['nama_admin']) ?></td></tr>
                    <tr><td style="color: #64748b;">Diskon</td><td>: <?= $data['diskon'] > 0 ? $data['diskon'].'%' : '-' ?></td></tr>
                    <tr><td style="color: #64748b;">Total Bayar</td><td>: <strong style="color:var(--success); font-size: 16px;"><?= rupiah($data['total_bayar']) ?></strong></td></tr>
                </table>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th width="50">No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th style="text-align: right;">Harga Jual</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = $details->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-danger"><?= $row['kode_barang'] ?></span></td>
                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td style="text-align: right;"><?= rupiah($row['harga_jual'] ?? 0) ?></td>
                    <td style="text-align: center;"><?= $row['jumlah'] ?></td>
                    <td style="text-align: right;"><strong><?= rupiah(($row['harga_jual'] ?? 0) * $row['jumlah']) ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="5" style="text-align: right; padding: 15px;">TOTAL KESELURUHAN :</td>
                    <td style="text-align: right; color: var(--success);"><?= rupiah($data['total_bayar']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include '../footer.php'; ?>