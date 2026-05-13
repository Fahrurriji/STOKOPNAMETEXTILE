<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);

// 1. Ambil data utama transaksi dan nama pemasok
$query_utama = "SELECT tm.*, p.nama_pemasok, a.nama_lengkap as nama_admin 
                FROM transaksi_masuk tm 
                LEFT JOIN pemasok p ON tm.id_pemasok = p.id 
                LEFT JOIN admin a ON tm.id_admin = a.id 
                WHERE tm.id = $id";
$result_utama = $conn->query($query_utama);
$data = $result_utama->fetch_assoc();

// Jika data tidak ditemukan, balikkan ke halaman utama
if (!$data) {
    header("Location: transaksi_masuk.php");
    exit;
}

$page_title = 'Detail Barang Masuk - ' . $data['no_transaksi'];

// 2. Ambil detail barang yang ada dalam transaksi tersebut
$query_detail = "SELECT dtm.*, b.nama_barang, b.kode_barang 
                 FROM detail_transaksi_masuk dtm 
                 JOIN barang b ON dtm.id_barang = b.id 
                 WHERE dtm.id_transaksi = $id";
$details = $conn->query($query_detail);

include '../header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon" style="background:#dcfce7;color:#16a34a">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div>
            <h3>Detail Transaksi Masuk</h3>
            <p>No. Transaksi: <strong><?= $data['no_transaksi'] ?></strong></p>
        </div>
        <a href="transaksi_masuk.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card-body" style="padding: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div>
                <table style="width: 100%; font-size: 14px;">
                    <tr><td width="120">Pemasok</td><td>: <strong><?= htmlspecialchars($data['nama_pemasok']) ?></strong></td></tr>
                    <tr><td>Tanggal</td><td>: <?= date('d F Y', strtotime($data['tanggal'])) ?></td></tr>
                </table>
            </div>
            <div>
                <table style="width: 100%; font-size: 14px;">
                    <tr><td width="120">Admin</td><td>: <?= htmlspecialchars($data['nama_admin']) ?></td></tr>
                    <tr><td>Total Nilai</td><td>: <strong style="color:var(--success)"><?= rupiah($data['total_bayar'] ?? $data['total_nilai']) ?></strong></td></tr>
                </table>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr style="background: #f8fafc;">
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th style="text-align: right;">Harga Beli</th>
                    <th style="text-align: center;">Jumlah (Qty)</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = $details->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-info"><?= $row['kode_barang'] ?></span></td>
                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td style="text-align: right;"><?= rupiah($row['harga_beli']) ?></td>
                    <td style="text-align: center;"><?= $row['jumlah'] ?></td>
                    <td style="text-align: right;"><strong><?= rupiah($row['harga_beli'] * $row['jumlah']) ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../footer.php'; ?>