<?php
// 1. Inisialisasi dan Proteksi
require_once '../config/auth.php';
require_once '../config/koneksi.php';

$page_title = 'Transaksi Barang Masuk';

// Ambil pesan dari session jika ada (untuk notifikasi sukses/gagal hapus)
$pesan = $_SESSION['pesan'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['pesan'], $_SESSION['error']);

// 2. Filter Tanggal
$tgl_awal  = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

// 3. Query Data (Menggabungkan tabel transaksi, pemasok, dan admin)
$query = "SELECT tm.*, p.nama_pemasok, a.nama_lengkap as nama_admin
          FROM transaksi_masuk tm
          LEFT JOIN pemasok p ON tm.id_pemasok = p.id
          LEFT JOIN admin a ON tm.id_admin = a.id
          WHERE tm.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'
          ORDER BY tm.id DESC";
$data = $conn->query($query);

include '../header.php';
?>

<?php if ($pesan): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pesan ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon" style="background:#dcfce7;color:#16a34a">
            <i class="fas fa-arrow-circle-down"></i>
        </div>
        <div>
            <h3>Transaksi Barang Masuk</h3>
            <p>Pembelian & penerimaan kain dari pemasok</p>
        </div>
        <a href="tambah_masuk.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah Transaksi</a>
    </div>

    <div class="page-toolbar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <div class="form-group" style="margin:0">
                <label style="font-size:11px;margin-bottom:3px;display:block">Dari Tanggal</label>
                <input type="date" name="tgl_awal" class="form-control no-icon" style="padding:8px 12px;width:160px" value="<?= $tgl_awal ?>">
            </div>
            <div class="form-group" style="margin:0">
                <label style="font-size:11px;margin-bottom:3px;display:block">Sampai Tanggal</label>
                <input type="date" name="tgl_akhir" class="form-control no-icon" style="padding:8px 12px;width:160px" value="<?= $tgl_akhir ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:16px"><i class="fas fa-filter"></i> Filter</button>
        </form>
        <span style="font-size:13px;color:var(--text-muted);margin-left:auto"><?= $data->num_rows ?> transaksi ditemukan</span>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Pemasok</th>
                    <th>Total Item</th>
                    <th>Total Nilai</th>
                    <th>Admin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data->num_rows === 0): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="es-icon">📦</div>
                                <h4>Tidak ada transaksi</h4>
                                <p>Silakan tambah transaksi baru atau ubah filter tanggal.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1; 
                    $grand_total = 0; 
                    while ($r = $data->fetch_assoc()): 
                        $grand_total += $r['total_nilai']; 
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($r['no_transaksi']) ?></span></td>
                            <td><?= tgl_indo($r['tanggal']) ?></td>
                            <td><?= htmlspecialchars($r['nama_pemasok'] ?? 'Tanpa Pemasok') ?></td>
                            <td><span class="badge badge-primary"><?= $r['total_item'] ?> item</span></td>
                            <td><strong><?= rupiah($r['total_nilai']) ?></strong></td>
                            <td style="font-size:12px"><?= htmlspecialchars($r['nama_admin']) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="detail_masuk.php?id=<?= $r['id'] ?>" class="btn btn-primary btn-sm" data-tooltip="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="javascript:void(0)" 
                                       onclick="confirmDelete('hapus_masuk.php?id=<?= $r['id'] ?>', '<?= htmlspecialchars($r['no_transaksi']) ?>')" 
                                       class="btn btn-danger btn-sm" data-tooltip="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <tr style="background:#f8fafc;font-weight:700">
                        <td colspan="5" style="text-align:right">TOTAL PERIODE:</td>
                        <td style="color:var(--success)"><?= rupiah($grand_total) ?></td>
                        <td colspan="2"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(url, no_transaksi) {
    if (confirm("Apakah Anda yakin ingin menghapus transaksi " + no_transaksi + "?\n\nPERHATIAN: Stok barang akan dikurangi kembali otomatis.")) {
        window.location.href = url;
    }
}
</script>

<?php include '../footer.php'; ?>