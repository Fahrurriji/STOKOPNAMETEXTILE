<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Laporan Stok Inventaris';

$filter_kat    = (int)($_GET['kategori'] ?? 0);
$filter_status = $_GET['status'] ?? 'semua';

$kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");

$where = "WHERE b.status = 'aktif'";
if ($filter_kat)  $where .= " AND b.id_kategori = $filter_kat";
if ($filter_status === 'minim')  $where .= " AND b.stok <= b.stok_minimum AND b.stok > 0";
if ($filter_status === 'habis')  $where .= " AND b.stok = 0";
if ($filter_status === 'normal') $where .= " AND b.stok > b.stok_minimum";

$data = $conn->query("SELECT b.*, k.nama_kategori, p.nama_pemasok
    FROM barang b
    LEFT JOIN kategori k ON b.id_kategori = k.id
    LEFT JOIN pemasok p ON b.id_pemasok = p.id
    $where
    ORDER BY k.nama_kategori, b.nama_barang");

if (!$data) {
    die("Error pada query data: " . $conn->error);
}

// Hitung total nilai
$q_total = $conn->query("SELECT SUM(b.stok * b.harga_jual) as total_jual, SUM(b.stok * b.harga_beli) as total_beli, COUNT(*) as jml FROM barang b $where");

if (!$q_total) {
    die("Error pada query total: " . $conn->error);
}

$total = $q_total->fetch_assoc() ?? ['jml' => 0, 'total_beli' => 0, 'total_jual' => 0];

include '../header.php';
?>

<div class="card no-print" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-header-icon"><i class="fas fa-chart-bar"></i></div>
        <div><h3>Laporan Stok Inventaris</h3><p>Filter dan cetak laporan persediaan kain</p></div>
        <button onclick="window.print()" class="btn btn-primary btn-sm no-print">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
    </div>
    <div class="card-body">
        <form method="GET" style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">Kategori</label>
                <div class="input-wrap">
                    <i class="fas fa-tags input-icon"></i>
                    <select name="kategori" class="form-control" style="width:200px" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php $kategori_list->data_seek(0); while ($kl = $kategori_list->fetch_assoc()): ?>
                        <option value="<?= $kl['id'] ?>" <?= $filter_kat == $kl['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kl['nama_kategori']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">Status Stok</label>
                <div class="input-wrap">
                    <i class="fas fa-filter input-icon"></i>
                    <select name="status" class="form-control" style="width:180px" onchange="this.form.submit()">
                        <option value="semua"  <?= $filter_status === 'semua'  ? 'selected' : '' ?>>Semua Status</option>
                        <option value="normal" <?= $filter_status === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="minim"  <?= $filter_status === 'minim'  ? 'selected' : '' ?>>Stok Minim</option>
                        <option value="habis"  <?= $filter_status === 'habis'  ? 'selected' : '' ?>>Stok Habis</option>
                    </select>
                </div>
            </div>
            <a href="laporan_stok.php" class="btn btn-secondary btn-sm" style="margin-bottom:2px">
                <i class="fas fa-times"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- PRINT HEADER -->
<div class="print-only report-header" style="display:none">
    <h2>LAPORAN STOK INVENTARIS GUDANG TEXTILE</h2>
    <p>Tanggal Cetak: <?= tgl_indo(date('Y-m-d')) ?> | Dicetak oleh: <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
</div>

<!-- SUMMARY CARDS -->
<div class="stats-grid no-print" style="margin-bottom:20px">
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-boxes"></i></div>
        <div class="stat-info">
            <div class="si-value"><?= $total['jml'] ?></div>
            <div class="si-label">Total Produk Ditampilkan</div>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <div class="si-value" style="font-size:18px"><?= rupiah($total['total_beli'] ?? 0) ?></div>
            <div class="si-label">Total Nilai Modal</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <div class="si-value" style="font-size:18px"><?= rupiah($total['total_jual'] ?? 0) ?></div>
            <div class="si-label">Total Nilai Jual</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table" id="tblLaporan">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Pemasok</th>
                    <th>Satuan</th>
                    <th>Stok</th>
                    <th>Min</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Nilai Stok</th>
                    <th>Status</th>
                    <th>Lokasi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($data->num_rows === 0): ?>
            <tr><td colspan="13">
                <div class="empty-state"><div class="es-icon">📦</div>
                    <h4>Tidak ada data</h4><p>Coba ubah filter pencarian</p>
                </div>
            </td></tr>
            <?php else:
                $no = 1; $kat_sebelumnya = '';
                while ($row = $data->fetch_assoc()):
                    // Group header per kategori
                    if ($row['nama_kategori'] !== $kat_sebelumnya):
                        $kat_sebelumnya = $row['nama_kategori'];
            ?>
            <tr style="background:linear-gradient(90deg,var(--primary-dark),var(--primary));color:#fff">
                <td colspan="13" style="padding:10px 16px;font-weight:700;font-size:12px;letter-spacing:.8px;text-transform:uppercase">
                    <i class="fas fa-folder-open"></i> <?= htmlspecialchars($row['nama_kategori']) ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><code style="font-size:11px;background:#f8fafc;padding:2px 5px;border-radius:4px"><?= htmlspecialchars($row['kode_barang']) ?></code></td>
                <td>
                    <strong><?= htmlspecialchars($row['nama_barang']) ?></strong>
                    <?php if ($row['warna'] || $row['motif']): ?>
                    <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($row['warna'] ?? '') ?> <?= $row['motif'] ? '· '.$row['motif'] : '' ?></div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                <td style="font-size:12px"><?= htmlspecialchars($row['nama_pemasok'] ?? '-') ?></td>
                <td><?= $row['satuan'] ?></td>
                <td style="font-weight:700;font-size:15px"><?= $row['stok'] ?></td>
                <td style="color:var(--text-muted)"><?= $row['stok_minimum'] ?></td>
                <td style="font-size:12px"><?= rupiah($row['harga_beli']) ?></td>
                <td style="font-size:12px;font-weight:600;color:var(--success)"><?= rupiah($row['harga_jual']) ?></td>
                <td style="font-weight:700"><?= rupiah($row['stok'] * $row['harga_jual']) ?></td>
                <td>
                    <?php if ($row['stok'] == 0): ?>
                        <span class="badge badge-danger">Habis</span>
                    <?php elseif ($row['stok'] <= $row['stok_minimum']): ?>
                        <span class="badge badge-warning">Minim</span>
                    <?php else: ?>
                        <span class="badge badge-success">Normal</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($row['lokasi_gudang'] ?? '-') ?></td>
            </tr>
            <?php endwhile; endif; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;font-weight:700">
                    <td colspan="10" style="text-align:right;padding:14px 16px">TOTAL NILAI STOK (HARGA JUAL):</td>
                    <td style="color:var(--success);font-size:15px;padding:14px 16px"><?= rupiah($total['total_jual'] ?? 0) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .sidebar, .topbar, .main-content > header { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .page-content { padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    body { font-size: 11px !important; }
}
</style>

<?php include '../footer.php'; ?>