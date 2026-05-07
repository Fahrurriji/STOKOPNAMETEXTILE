<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';

$page_title = 'Laporan Transaksi Barang';

// Ambil parameter filter
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
$jenis = $_GET['jenis'] ?? 'semua';

// Query menggunakan UNION untuk menggabungkan data masuk dan keluar
$query = "";
$masuk = "SELECT 'Masuk' as tipe, no_transaksi, tanggal, total_nilai as total, id 
          FROM transaksi_masuk 
          WHERE tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
          
$keluar = "SELECT 'Keluar' as tipe, no_transaksi, tanggal, total_bayar as total, id 
           FROM transaksi_keluar 
           WHERE tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'";

if ($jenis == 'masuk') {
    $query = $masuk . " ORDER BY tanggal DESC";
} elseif ($jenis == 'keluar') {
    $query = $keluar . " ORDER BY tanggal DESC";
} else {
    $query = "($masuk) UNION ($keluar) ORDER BY tanggal DESC";
}

$data = $conn->query($query);

include '../header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon" style="background:#f1f5f9;color:#475569">
            <i class="fas fa-history"></i>
        </div>
        <div>
            <h3>Laporan Riwayat Transaksi</h3>
            <p>Periode: <strong><?= date('d/m/Y', strtotime($tgl_mulai)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($tgl_selesai)) ?></strong></p>
        </div>
        <div style="margin-left:auto">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <div class="page-toolbar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:12px;display:block;margin-bottom:5px">Mulai Tanggal</label>
                <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai ?>">
            </div>
            <div>
                <label style="font-size:12px;display:block;margin-bottom:5px">Sampai Tanggal</label>
                <input type="date" name="tgl_selesai" class="form-control" value="<?= $tgl_selesai ?>">
            </div>
            <div>
                <label style="font-size:12px;display:block;margin-bottom:5px">Jenis Transaksi</label>
                <select name="jenis" class="form-control">
                    <option value="semua" <?= $jenis == 'semua' ? 'selected' : '' ?>>Semua Transaksi</option>
                    <option value="masuk" <?= $jenis == 'masuk' ? 'selected' : '' ?>>Barang Masuk</option>
                    <option value="keluar" <?= $jenis == 'keluar' ? 'selected' : '' ?>>Barang Keluar</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-search"></i> Cari
            </button>
        </form>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Tanggal</th>
                    <th>No. Transaksi</th>
                    <th style="text-align:center">Tipe</th>
                    <th style="text-align:right">Total Nilai</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_seluruh = 0;
                if($data && $data->num_rows > 0): 
                    $no=1; while($r = $data->fetch_assoc()): 
                    $total_seluruh += $r['total'];
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                    <td><strong><?= $r['no_transaksi'] ?></strong></td>
                    <td style="text-align:center">
                        <span class="badge <?= $r['tipe'] == 'Masuk' ? 'badge-success' : 'badge-danger' ?>">
                            <?= $r['tipe'] ?>
                        </span>
                    </td>
                    <td style="text-align:right"><?= rupiah($r['total']) ?></td>
                    <td style="text-align:center">
                        <?php 
                            $link = ($r['tipe'] == 'Masuk') ? '../transaksi/detail_masuk.php' : '../transaksi/detail_keluar.php';
                        ?>
                        <a href="<?= $link ?>?id=<?= $r['id'] ?>" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr style="background:#f8fafc; font-weight:bold">
                    <td colspan="4" style="text-align:right">TOTAL PERIODE INI :</td>
                    <td style="text-align:right; color:var(--success)"><?= rupiah($total_seluruh) ?></td>
                    <td></td>
                </tr>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;padding:30px">Tidak ada transaksi pada periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar, .page-toolbar, .btn, .card-header-icon { display: none !important; }
    .content { margin: 0; padding: 0; }
    .card { border: none; }
    .table th, .table td { border: 1px solid #ddd; }
}
</style>

<?php include '../footer.php'; ?>